<?php
ob_start();
session_start();
require('../../sheep_core/config.php');

$ler = new Ler();

// AUTO ACEITAR PEDIDOS NOVOS (Pendente ou Pago -> Em Produção)
$lerPendentes = new Ler();
$lerPendentes->Leitura('pedidos', "WHERE status IN ('pendente', 'pago')");
$autoPrintIds = [];
if ($lerPendentes->getResultado()) {
    $atualizar = new Atualizar();
    foreach ($lerPendentes->getResultado() as $p) {
        $atualizar->Atualizando('pedidos', ['status' => 'em_producao'], "WHERE id = :id", "id={$p['id']}");
        $autoPrintIds[] = $p['id'];
    }
}

// Pegar maior ID
$ler->Leitura('pedidos', "WHERE status != 'aguardando_pagamento' ORDER BY id DESC LIMIT 1");
$maxId = $ler->getResultado() ? $ler->getResultado()[0]['id'] : 0;

// Pegar totais para o Kanban
$ler->Leitura('pedidos', "WHERE status IN ('pendente', 'pago')");
$aguardando = $ler->getContaLinhas();

$ler->Leitura('pedidos', "WHERE status = 'em_producao'");
$preparando = $ler->getContaLinhas();

$ler->Leitura('pedidos', "WHERE status = 'saiu_para_entrega'");
$entrega = $ler->getContaLinhas();

// Gerar HTML da lista de pedidos (últimos 15)
$ler->Leitura('pedidos', "WHERE status != 'aguardando_pagamento' ORDER BY id DESC LIMIT 15");
$htmlLista = '';

if ($ler->getResultado()) {
    foreach ($ler->getResultado() as $pedido) {
        $statusClass = 'pendente';
        $statusText = 'AGUARDANDO';
        
        if ($pedido['status'] == 'pago') {
            $statusClass = 'pendente'; 
            $statusText = 'AGUARDANDO (PAGO ONLINE)';
        }
        
        if ($pedido['status'] == 'em_producao') { 
            $statusClass = 'preparando'; 
            $statusText = 'EM PREPARAÇÃO'; 
        }
        if ($pedido['status'] == 'saiu_para_entrega') { 
            $statusClass = 'info'; // Reutilizando a cor azul/info que usamos no CSS
            $statusText = 'SAIU P/ ENTREGA'; 
        }
        if ($pedido['status'] == 'entregue') { 
            $statusClass = 'success'; 
            $statusText = 'ENTREGUE'; 
        }
        if ($pedido['status'] == 'cancelado') { 
            $statusClass = 'danger'; 
            $statusText = 'CANCELADO'; 
        }
        
        $valorF = number_format($pedido['valor_total'], 2, ',', '.');
        $forma = strtoupper($pedido['forma_pagamento']);
        
        // Vamos tirar o active fixo para que não perca o click, o JS cuida de iluminar
        $htmlLista .= "
        <div class='pdv-order-card' data-id='{$pedido['id']}' onclick='carregarPedido({$pedido['id']}, this)'>
          <div class='pdv-card-top'>
            <span># {$pedido['id']}</span>
            <span style='color:var(--primary-color);'>R$ {$valorF}</span>
          </div>
          <div class='pdv-card-info'>
            {$pedido['cliente_nome']} • {$forma}
          </div>
          <div class='pdv-card-status {$statusClass}'>
            {$statusText}
          </div>
        </div>
        ";
    }
} else {
    $htmlLista = "<div style='text-align:center; padding: 20px; color:#999;'>Nenhum pedido hoje.</div>";
}

header('Content-Type: application/json');
echo json_encode([
    'sucesso' => true,
    'max_id' => (int)$maxId,
    'kanban_aguardando' => $aguardando,
    'kanban_preparando' => $preparando,
    'kanban_entrega' => $entrega,
    'html_lista' => $htmlLista,
    'auto_print_ids' => $autoPrintIds
]);
