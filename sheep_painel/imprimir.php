<?php
ob_start();
session_start();
require('../sheep_core/config.php');

// Verifica se o ID do pedido foi enviado
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    echo "Pedido não encontrado.";
    exit;
}

// Busca os dados do pedido
$ler = new Ler();
$ler->Leitura('pedidos', "WHERE id = :id", "id={$id}");
if (!$ler->getResultado()) {
    echo "Pedido não encontrado no banco de dados.";
    exit;
}
$pedido = $ler->getResultado()[0];

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Imprimir Pedido #<?= $pedido['id'] ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 14px; width: 80mm; margin: 0; padding: 10px; color: #000; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-bottom: 1px dashed #000; margin: 10px 0; }
        .items { width: 100%; border-collapse: collapse; }
        .items td { padding: 5px 0; }
        .total { font-size: 18px; margin-top: 10px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print();">

    <div class="center">
        <h2 style="margin: 0;">MONDINI PIZZARIA</h2>
        <p style="margin: 5px 0;">PEDIDO #<?= $pedido['id'] ?></p>
        <p><?= date('d/m/Y H:i', strtotime($pedido['criado_em'])) ?></p>
    </div>

    <div class="line"></div>

    <div class="bold">CLIENTE:</div>
    <div><?= $pedido['cliente_nome'] ?></div>
    <div>Tel: <?= $pedido['cliente_telefone'] ?></div>
    
    <div style="margin-top: 10px;" class="bold">ENDEREÇO DE ENTREGA:</div>
    <div><?= $pedido['cliente_endereco'] ?></div>
    <?php if(!empty($pedido['cliente_referencia'])): ?>
        <div>Ref: <?= $pedido['cliente_referencia'] ?></div>
    <?php endif; ?>

    <div class="line"></div>

    <table class="items">
        <tr class="bold">
            <td colspan="3">ITENS DO PEDIDO:</td>
        </tr>
        <?php
        $lerIt = new Ler();
        $lerIt->Leitura('itens_pedido', "WHERE pedido_id = :pid", "pid={$id}");
        if ($lerIt->getResultado()) {
            foreach ($lerIt->getResultado() as $item) {
                if ($item['produto_id'] == 0) {
                    // É uma pizza customizada
                    $prodNome = "Pizza Personalizada\n  (" . $item['detalhes'] . ")";
                } else {
                    // É um produto normal
                    $lerPr = new Ler();
                    $lerPr->Leitura('produtos', "WHERE id = :id_prod", "id_prod={$item['produto_id']}");
                    $prodNome = $lerPr->getResultado() ? $lerPr->getResultado()[0]['nome'] : 'Produto';
                }
                echo "<tr><td colspan='3'>• {$item['quantidade']}x " . nl2br($prodNome) . "</td></tr>";
            }
        } else {
            echo "<tr><td colspan='3'>".nl2br($pedido['detalhes_pedido'] ?? 'Nenhum item')."</td></tr>";
        }
        ?>
    </table>

    <div class="line"></div>

    <div class="bold">PAGAMENTO:</div>
    <div><?= strtoupper($pedido['forma_pagamento']) ?></div>
    <?php if(isset($pedido['troco']) && $pedido['troco'] > 0): ?>
        <div>Troco para: R$ <?= number_format($pedido['troco'], 2, ',', '.') ?></div>
    <?php endif; ?>

    <div class="total bold center">
        TOTAL: R$ <?= number_format($pedido['valor_total'], 2, ',', '.') ?>
    </div>

    <div class="line"></div>
    <div class="center">
        <p>Bom Apetite!</p>
    </div>

    <div class="center no-print" style="margin-top: 20px;">
        <button onclick="window.print();" style="padding: 10px 20px; cursor: pointer;">Imprimir Novamente</button>
        <button onclick="window.close();" style="padding: 10px 20px; cursor: pointer;">Fechar</button>
    </div>

</body>
</html>
