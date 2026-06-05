<?php
/**
 * SCRIPT DE RETORNO MERCADO PAGO (WEBHOOK) - PIZZARIA
 * Este script recebe as notificações do Mercado Pago e atualiza o status do pedido no banco de dados.
 */

require_once('sheep_core/config.php');

$log_file = 'log_mercadopago.txt';
function log_msg($msg) {
    global $log_file;
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] " . $msg . PHP_EOL, FILE_APPEND);
}

log_msg("Recebeu notificação do Mercado Pago.");

// O Mercado Pago envia os dados via POST (JSON) ou na URL
$json = file_get_contents('php://input');
$dados = json_decode($json, true);

log_msg("Payload recebido: " . $json);

$payment_id = null;

if (isset($_GET['data_id'])) {
    $payment_id = $_GET['data_id'];
} else if (isset($dados['data']['id'])) {
    $payment_id = $dados['data']['id'];
}

if ($payment_id) {
    // Consultar o status do pagamento na API do Mercado Pago
    $url = "https://api.mercadopago.com/v1/payments/" . $payment_id;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . MP_ACCESS_TOKEN
    ]);
    $resposta = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    log_msg("Consulta na API MP HTTP Code: {$http_code}");
    
    if ($http_code == 200 || $http_code == 201) {
        $payment_info = json_decode($resposta, true);
        
        $status_mp = $payment_info['status'] ?? '';
        $pedido_id = $payment_info['external_reference'] ?? '';
        
        log_msg("Status do pagamento: {$status_mp} | External Ref (Pedido ID): {$pedido_id}");
        
        if ($status_mp == 'approved' && !empty($pedido_id)) {
            $dadosUpdate = ['status' => 'pago'];
            $atualizar = new Atualizar();
            $atualizar->Atualizando('pedidos', $dadosUpdate, "WHERE id = :id", "id={$pedido_id}");
            
            if ($atualizar->getResultado()) {
                log_msg("SUCESSO: Status do Pedido #{$pedido_id} alterado para 'pago'.");
                
                // Creditar fidelidade (cashback + pontos) ao cliente
                try {
                    $lerPedido = new Ler();
                    $lerPedido->Leitura('pedidos', "WHERE id = :id", "id={$pedido_id}");
                    $dadosPedido = $lerPedido->getResultado();
                    if ($dadosPedido && !empty($dadosPedido[0]['usuario_id'])) {
                        $usuarioId   = (int)$dadosPedido[0]['usuario_id'];
                        $valorPedido = (float)$dadosPedido[0]['valor_total'];
                        $fidelidade  = new FidelidadeService();
                        if ($fidelidade->registrarGanho($usuarioId, (int)$pedido_id, $valorPedido)) {
                            log_msg("Clube Fidelidade: cashback e pontos creditados ao usuário #{$usuarioId} pelo Pedido #{$pedido_id}.");
                        }
                    }
                } catch (Exception $e) {
                    log_msg("AVISO Clube Fidelidade: " . $e->getMessage());
                }
            } else {
                log_msg("ERRO: Não foi possível atualizar o pedido #{$pedido_id} no banco.");
            }
        }
    } else {
        log_msg("ERRO: Falha ao consultar o pagamento na API do Mercado Pago. Resposta: " . $resposta);
    }
} else {
    log_msg("AVISO: Nenhum payment_id encontrado na notificação.");
}

// Retornar 200 OK para o Mercado Pago parar de enviar notificações
http_response_code(200);
echo "OK";
?>
