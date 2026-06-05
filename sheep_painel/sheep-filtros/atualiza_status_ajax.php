<?php
header('Content-Type: application/json');
session_start();
require('../../sheep_core/config.php');
// Verificação de autenticação: apenas usuários logados (Admin ou Operador)
if (empty($_SESSION['sheep_user']) || !in_array(($_SESSION['sheep_user']['nivel'] ?? 'C'), ['M', 'O'], true)) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado.']);
    exit;
}


$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if ($id && $status) {
    // 1. Verificar se é pedido iFood antes de atualizar
    $ler = new Ler();
    $ler->Leitura('pedidos', "WHERE id = :id LIMIT 1", "id={$id}");
    $pedido = $ler->getResultado();

    $dados = ['status' => $status];
    if ($status === 'entregue' || $status === 'cancelado') {
        $dados['finalizado_em'] = date('Y-m-d H:i:s');
    }
    
    // Atualiza status do iFood se aplicável
    if ($pedido && $pedido[0]['origem'] === 'ifood' && !empty($pedido[0]['ifood_order_id'])) {
        $ifood = new IfoodService();
        $ifood_order_id = $pedido[0]['ifood_order_id'];
        
        if ($status === 'em_producao') {
            $ifood->confirmOrder($ifood_order_id);
            $ifood->startPreparation($ifood_order_id);
            $dados['ifood_status'] = 'CONFIRMED';
        } elseif ($status === 'saiu_para_entrega') {
            $ifood->dispatchOrder($ifood_order_id);
            $dados['ifood_status'] = 'DISPATCHED';
        } elseif ($status === 'cancelado') {
            $ifood->requestCancellation($ifood_order_id);
            $dados['ifood_status'] = 'CANCELLED';
        }
    }

    $atualizar = new Atualizar();
    $atualizar->Atualizando('pedidos', $dados, "WHERE id = :id", "id={$id}");

    // Credita cashback e pontos somente quando pedido for entregue (evita duplo crédito)
    if ($status === 'entregue') {
        try {
            $pedidoId = $id;
            $usuarioId = (int)$pedido[0]['usuario_id'];
            $valorPedido = (float)$pedido[0]['valor_total'];
            $fidelidade = new FidelidadeService();
            $fidelidade->registrarGanho($usuarioId, $pedidoId, $valorPedido);
            file_put_contents('log_fidelidade_admin.txt', "[" . date('Y-m-d H:i:s') . "] Credito AJAX pedido #{$pedidoId} para usuário #{$usuarioId}\n", FILE_APPEND);
        } catch (Exception $e) {
            file_put_contents('log_fidelidade_admin.txt', "[" . date('Y-m-d H:i:s') . "] ERRO AJAX credito: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }

    if ($atualizar->getResultado()) {
        // Envio do Firebase Push Notification caso o cliente tenha token
        if ($pedido && !empty($pedido[0]['fcm_token'])) {
            $fcmToken = $pedido[0]['fcm_token'];
            $titulo = "Pizzaria Modelo 🍕";
            $mensagem = "";
            
            switch ($status) {
                case 'pago':
                    $mensagem = "Seu pagamento foi confirmado! Sua pizza já entrou na nossa fila de produção. 👨‍🍳";
                    break;
                case 'em_producao':
                    $mensagem = "Seu pedido está sendo preparado! O pizzaiolo já está abrindo a massa fresca artesanal. 🔥";
                    break;
                case 'saiu_para_entrega':
                    $mensagem = "A caminho! O motoboy acabou de sair com sua pizza bem quentinha! 🏍️💨";
                    break;
                case 'entregue':
                    $mensagem = "Entregue com Sucesso! Bom apetite! Esperamos que ame cada fatia. 🎉🍕";
                    break;
                case 'cancelado':
                    $mensagem = "Infelizmente, seu pedido foi cancelado. Se tiver dúvidas, entre em contato via WhatsApp.";
                    break;
            }
            
            if (!empty($mensagem)) {
                FirebaseFCM::enviarPush($fcmToken, $titulo, $mensagem, $id);
            }
        }

        echo json_encode(['sucesso' => true]);
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar no banco']);
    }
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados inválidos']);
}

