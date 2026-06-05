<?php
/**
 * Recebe notificações de captura de pagamento e atualiza o banco de dados.
 */

require_once('sheep_core/config.php');

// 1. Log de depuração (para você acompanhar o que chega)
$log_file = 'log_webhook_paypal.txt';
function log_webhook($msg) {
    global $log_file;
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] " . $msg . PHP_EOL, FILE_APPEND);
}

// 2. Receber o corpo da requisição (JSON)
$json = file_get_contents('php://input');
$data = json_decode($json);

if (!$data) {
    // Se acessar pelo navegador, cai aqui (o que é normal)
    echo "Serviço de Webhook Pizzaria Modelo Ativo.";
    exit;
}

log_webhook("Notificação recebida: " . $data->event_type);

// 3. Verificar o tipo de evento
// O evento 'PAYMENT.CAPTURE.COMPLETED' garante que o dinheiro caiu na conta
if ($data->event_type == 'PAYMENT.CAPTURE.COMPLETED') {
    
    $resource = $data->resource;
    $pedido_id = $resource->custom_id ?? null; // Pegamos o ID que enviamos no checkout
    
    if ($pedido_id) {
        log_webhook("Pagamento confirmado para o Pedido #{$pedido_id}");

        // 4. Atualizar o banco de dados
        $dados = ['status' => 'pago'];
        $atualizar = new Atualizar();
        $atualizar->Atualizando('pedidos', $dados, "WHERE id = :id", "id={$pedido_id}");

        if ($atualizar->getResultado()) {
            
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
                            log_webhook("Clube Fidelidade: cashback e pontos creditados ao usuário #{$usuarioId} pelo Pedido #{$pedido_id}.");
                        }
                    }
                } catch (Exception $e) {
                    log_webhook("AVISO Clube Fidelidade: " . $e->getMessage());
                }
        } else {
            log_webhook("ERRO: Falha ao atualizar pedido #{$pedido_id} no banco.");
        }
    } else {
        log_webhook("AVISO: Evento recebido mas 'custom_id' não encontrado.");
    }
}

// O PayPal espera um status 200 OK para saber que recebemos a mensagem
http_response_code(200);
?>
