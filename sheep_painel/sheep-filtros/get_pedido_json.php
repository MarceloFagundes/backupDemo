<?php
ob_start();
session_start();
require('../../sheep_core/config.php');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID inválido']);
    exit;
}

$ler = new Ler();
$ler->Leitura('pedidos', "WHERE id = :id", "id={$id}");

if (!$ler->getResultado()) {
    echo json_encode(['sucesso' => false, 'erro' => 'Pedido não encontrado']);
    exit;
}

$pedido = $ler->getResultado()[0];

// Buscar itens do pedido no banco de dados para listar nomes das pizzas
$itensTexto = "";
$lerItM = new Ler();
$lerItM->Leitura('itens_pedido', "WHERE pedido_id = :pid", "pid={$id}");
if ($lerItM->getResultado()) {
    foreach ($lerItM->getResultado() as $itM) {
        if ($itM['produto_id'] == 0) {
            // É uma pizza customizada
            $pN = "Pizza Personalizada (" . $itM['detalhes'] . ")";
        } else {
            // É um produto normal
            $lerPrM = new Ler();
            $lerPrM->Leitura('produtos', "WHERE id = :id_prod", "id_prod={$itM['produto_id']}");
            $pN = $lerPrM->getResultado() ? $lerPrM->getResultado()[0]['nome'] : 'Produto';
        }
        $itensTexto .= "• {$itM['quantidade']}x {$pN}\n";
    }
}

// Formatar dados para resposta
$response = [
    'sucesso' => true,
    'id' => $pedido['id'],
    'cliente_nome' => $pedido['cliente_nome'],
    'cliente_telefone' => $pedido['cliente_telefone'],
    'cliente_endereco' => $pedido['cliente_endereco'],
    'referencia' => $pedido['cliente_referencia'] ?? '',
    'valor_total' => number_format($pedido['valor_total'], 2, ',', '.'),
    'forma_pagamento' => strtoupper($pedido['forma_pagamento']),
    'status' => $pedido['status'],
    'criado_em' => date('d/m/Y H:i', strtotime($pedido['criado_em'])),
    'detalhes' => nl2br(htmlspecialchars(!empty($itensTexto) ? $itensTexto : ($pedido['detalhes_pedido'] ?? 'Nenhum item')))
];

// Opcional: Conta fidelidade (pedidos do mesmo telefone)
if (!empty($pedido['cliente_telefone'])) {
    $lerFiel = new Ler();
    $lerFiel->Leitura('pedidos', "WHERE cliente_telefone = :t AND status != 'cancelado'", "t={$pedido['cliente_telefone']}");
    $response['total_pedidos'] = $lerFiel->getContaLinhas();
} else {
    $response['total_pedidos'] = 1;
}

// O itensTexto já foi gerado lá em cima!

$msgFinal = "🍕 *NOVO PEDIDO PARA ENTREGA*\n";
$msgFinal .= "*Pizzaria Modelo*\n\n";
$msgFinal .= "*Pedido:* #{$pedido['id']}\n";
$msgFinal .= "*Cliente:* {$pedido['cliente_nome']}\n";
$msgFinal .= "*WhatsApp:* {$pedido['cliente_telefone']}\n";
$msgFinal .= "*Endereço:* {$pedido['cliente_endereco']}\n\n";
$msgFinal .= "*ITENS:*\n{$itensTexto}\n";
$msgFinal .= "*TOTAL:* R$ " . number_format($pedido['valor_total'], 2, ',', '.') . "\n";
$msgFinal .= "*PAGAMENTO:* {$pedido['forma_pagamento']}\n\n";
$msgFinal .= "📍 _Por favor, confirme a entrega ao chegar._";

$response['whatsapp_motoboy'] = "https://api.whatsapp.com/send?text=" . urlencode($msgFinal);

header('Content-Type: application/json');
echo json_encode($response);
