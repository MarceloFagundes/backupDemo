<?php
ob_start();
session_start();
require('../../sheep_core/config.php');

// Define cabeçalho JSON
header('Content-Type: application/json; charset=utf-8');

try {
    // Recebe e decodifica o payload JSON ou POST convencional
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    // Coleta e valida dados principais do cliente
    $nome = strip_tags(trim($input['nome'] ?? ''));
    $telefone = strip_tags(trim($input['telefone'] ?? ''));
    $tipoEntrega = strip_tags(trim($input['tipo_entrega'] ?? 'retirada')); // retirada ou entrega
    $endereco = strip_tags(trim($input['endereco'] ?? ''));
    $taxaEntrega = (float)($input['taxa_entrega'] ?? 0.00);
    $formaPagamento = strip_tags(trim($input['forma_pagamento'] ?? 'Dinheiro'));
    $itens = $input['itens'] ?? [];

    if (empty($nome)) {
        throw new Exception("O nome do cliente é obrigatório.");
    }
    if (empty($telefone)) {
        throw new Exception("O telefone do cliente é obrigatório.");
    }
    if (empty($itens) || !is_array($itens)) {
        throw new Exception("O carrinho de compras não pode estar vazio.");
    }

    // Calcula o valor total no backend buscando os preços direto do banco para segurança (produtos fixos) ou calculando o preço montado enviado (pizzas personalizadas)
    $valorTotalItens = 0.00;
    $itensFinais = [];

    foreach ($itens as $item) {
        $produtoId = (int)($item['produto_id'] ?? 0);
        $qtd = (int)($item['quantidade'] ?? 1);
        $detalhes = strip_tags(trim($item['detalhes'] ?? 'Venda de Balcão'));

        if ($qtd <= 0) {
            continue;
        }

        if ($produtoId === 0) {
            // É uma pizza customizada de múltiplos sabores montada no balcão
            $precoUnitario = (float)($item['preco'] ?? 0.00);
            $valorTotalItens += ($precoUnitario * $qtd);

            $itensFinais[] = [
                'produto_id' => 0,
                'quantidade' => $qtd,
                'preco_unitario' => $precoUnitario,
                'nome' => strip_tags(trim($item['nome'] ?? 'Pizza Personalizada')),
                'detalhes' => $detalhes
            ];
        } else {
            // É um produto normal (bebida, pizza inteira cadastrada, etc.)
            // Busca o produto no banco
            $lerProd = new Ler();
            $lerProd->Leitura('produtos', "WHERE id = :id", "id={$produtoId}");
            $resProd = $lerProd->getResultado();

            if (!$resProd) {
                throw new Exception("Produto com ID {$produtoId} não encontrado no cardápio.");
            }

            $p = $resProd[0];
            $precoUnitario = (float)($p['preco_promocional'] ? $p['preco_promocional'] : $p['preco']);
            
            $valorTotalItens += ($precoUnitario * $qtd);

            $itensFinais[] = [
                'produto_id' => $produtoId,
                'quantidade' => $qtd,
                'preco_unitario' => $precoUnitario,
                'nome' => $p['nome'],
                'detalhes' => $detalhes
            ];
        }
    }

    if (empty($itensFinais)) {
        throw new Exception("Nenhum produto válido foi inserido no carrinho.");
    }

    // Define o endereço final baseado na modalidade
    $enderecoCompleto = "";
    if ($tipoEntrega === 'entrega') {
        if (empty($endereco)) {
            throw new Exception("O endereço é obrigatório para entrega.");
        }
        $enderecoCompleto = $endereco;
        if ($taxaEntrega > 0) {
            $enderecoCompleto .= " (Taxa de Entrega: R$ " . number_format($taxaEntrega, 2, ',', '.') . ")";
        }
    } else {
        $enderecoCompleto = "Retirada no Balcão";
    }

    $valorTotalFinal = $valorTotalItens + ($tipoEntrega === 'entrega' ? $taxaEntrega : 0.00);

    // Salvar pedido na tabela 'pedidos'
    $dadosPedido = [
        'usuario_id' => null, // venda manual pelo painel
        'cliente_nome' => $nome,
        'cliente_telefone' => $telefone,
        'cliente_endereco' => $enderecoCompleto,
        'forma_pagamento' => $formaPagamento,
        'valor_total' => $valorTotalFinal,
        'status' => 'em_producao', // vai direto para o Kanban de preparo
        'origem' => 'balcao',      // origem explícita
        'criado_em' => date('Y-m-d H:i:s')
    ];

    $criar = new Criar();
    $criar->Criacao('pedidos', $dadosPedido);
    $pedidoId = $criar->getResultado();

    if (!$pedidoId) {
        throw new Exception("Não foi possível registrar o pedido no banco de dados.");
    }

    // Salva os itens na tabela 'itens_pedido'
    foreach ($itensFinais as $itemFin) {
        $dadosItem = [
            'pedido_id' => $pedidoId,
            'produto_id' => $itemFin['produto_id'],
            'quantidade' => $itemFin['quantidade'],
            'preco_unitario' => $itemFin['preco_unitario'],
            'detalhes' => $itemFin['detalhes']
        ];
        $criar->Criacao('itens_pedido', $dadosItem);
    }

    // Sucesso! Retorna o ID do pedido e sucesso
    echo json_encode([
        'sucesso' => true,
        'mensagem' => "Pedido de balcão #{$pedidoId} cadastrado com sucesso!",
        'pedido_id' => $pedidoId
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false,
        'erro' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
ob_end_flush();
?>
