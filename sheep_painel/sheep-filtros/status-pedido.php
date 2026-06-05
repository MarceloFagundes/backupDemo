<?php
require('../../sheep_core/config.php');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if ($id && $status) {
    // 1. Verificar se é pedido iFood antes de atualizar para poder pegar o id do iFood
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

    
        // Se o pedido foi marcado como pago ou entregue, creditar cashback e pontos
        if (in_array($status, ['pago', 'entregue'])) {
            try {
                $pedidoId = $id;
                $usuarioId = (int)$pedido[0]['usuario_id'];
                $valorPedido = (float)$pedido[0]['valor_total'];
                $fidelidade = new FidelidadeService();
                $fidelidade->registrarGanho($usuarioId, $pedidoId, $valorPedido);
                // Log para auditoria
                file_put_contents('log_fidelidade_admin.txt', "[" . date('Y-m-d H:i:s') . "] Credito admin pedido #{$pedidoId} para usuário #{$usuarioId}\n", FILE_APPEND);
            } catch (Exception $e) {
                file_put_contents('log_fidelidade_admin.txt', "[" . date('Y-m-d H:i:s') . "] ERRO admin credito: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }


    $atualizar = new Atualizar();
    $atualizar->Atualizando('pedidos', $dados, "WHERE id = :id", "id={$id}");

    if ($atualizar->getResultado()) {
        header("Location: ../sheep.php?m=sheep-pedidos/index&sucesso=true");
    } else {
        header("Location: ../sheep.php?m=sheep-pedidos/index&erro=true");
    }
} else {
    header("Location: ../sheep.php?m=sheep-pedidos/index&erro=true");
}
?>

