<?php
/**
 * Retorna o status atual do pedido em tempo real para o Pizza Tracker.
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
require_once('sheep_core/config.php');

$pedidoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($pedidoId <= 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID do pedido invalido.']);
    exit;
}

$ler = new Ler();
$ler->Leitura('pedidos', "WHERE id = :id", "id={$pedidoId}");

if ($ler->getResultado()) {
    $pedido = $ler->getResultado()[0];
    
    // Buscar itens para exibir no tracker também
    $lerItens = new Ler();
    $lerItens->Leitura('itens_pedido', "WHERE pedido_id = :pid", "pid={$pedidoId}");
    $itens = [];
    
    if ($lerItens->getResultado()) {
        foreach ($lerItens->getResultado() as $item) {
            if ($item['produto_id'] == 0) {
                $nomeProd = "Pizza Personalizada (" . $item['detalhes'] . ")";
            } else {
                $lerProd = new Ler();
                $lerProd->Leitura('produtos', "WHERE id = :id", "id={$item['produto_id']}");
                $nomeProd = $lerProd->getResultado() ? $lerProd->getResultado()[0]['nome'] : 'Produto';
            }
            $itens[] = [
                'nome' => $nomeProd,
                'quantidade' => $item['quantidade'],
                'preco' => $item['preco_unitario']
            ];
        }
    }

    $json = json_encode([
        'sucesso' => true,
        'pedido' => [
            'id' => (int)$pedido['id'],
            'status' => $pedido['status'],
            'valor_total' => (float)$pedido['valor_total'],
            'forma_pagamento' => $pedido['forma_pagamento'],
            'cliente_nome' => $pedido['cliente_nome'],
            'cliente_endereco' => $pedido['cliente_endereco'],
            'criado_em' => $pedido['criado_em'],
            'itens' => $itens
        ]
    ], JSON_UNESCAPED_UNICODE);

    if ($json === false) {
        // Se falhar devido a caracteres UTF-8 inválidos (comum em migrações com colisões de charset),
        // realiza a sanitização e conversão segura para UTF-8.
        $clienteNomeSafe = mb_convert_encoding($pedido['cliente_nome'], 'UTF-8', 'UTF-8,ISO-8859-1');
        $clienteEnderecoSafe = mb_convert_encoding($pedido['cliente_endereco'], 'UTF-8', 'UTF-8,ISO-8859-1');
        
        $safeItens = [];
        foreach ($itens as $item) {
            $safeItens[] = [
                'nome' => mb_convert_encoding($item['nome'], 'UTF-8', 'UTF-8,ISO-8859-1'),
                'quantidade' => (int)$item['quantidade'],
                'preco' => (float)$item['preco']
            ];
        }

        $json = json_encode([
            'sucesso' => true,
            'pedido' => [
                'id' => (int)$pedido['id'],
                'status' => $pedido['status'],
                'valor_total' => (float)$pedido['valor_total'],
                'forma_pagamento' => mb_convert_encoding($pedido['forma_pagamento'], 'UTF-8', 'UTF-8,ISO-8859-1'),
                'cliente_nome' => $clienteNomeSafe,
                'cliente_endereco' => $clienteEnderecoSafe,
                'criado_em' => $pedido['criado_em'],
                'itens' => $safeItens
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

    echo $json;
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Pedido nao encontrado.']);
}
?>
