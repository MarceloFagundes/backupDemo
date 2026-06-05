<?php
ob_start();
session_start();
require('../../sheep_core/config.php');

// Define cabeçalho JSON
header('Content-Type: application/json; charset=utf-8');

// Filtra a data recebida por GET, caso contrário usa a data atual
$dataFiltro = filter_input(INPUT_GET, 'data', FILTER_SANITIZE_SPECIAL_CHARS);
if (!$dataFiltro) {
    $dataFiltro = date('Y-m-d');
}

try {
    $ler = new Ler();
    
    // Consulta para pedidos válidos (faturamento real)
    // Consideramos pedidos com status: entregue, pago, em_producao, saiu_para_entrega
    // Pedidos "cancelados", "pendentes" ou "aguardando_pagamento" não entram no faturamento ativo
    $ler->Leitura('pedidos', "WHERE DATE(criado_em) = :data AND status IN ('pago', 'em_producao', 'saiu_para_entrega', 'entregue') ORDER BY criado_em DESC", "data={$dataFiltro}");
    $pedidosFaturados = $ler->getResultado();
    
    $faturamentoTotal = 0.00;
    
    $totalSite = 0.00;
    $qtdSite = 0;
    
    $totalIfood = 0.00;
    $qtdIfood = 0;
    
    $totalBalcao = 0.00;
    $qtdBalcao = 0;
    
    $totalOutros = 0.00;
    $qtdOutros = 0;
    
    $qtdTotal = 0;
    $pagamentos = [];
    $pedidosLista = [];
    
    if ($pedidosFaturados) {
        foreach ($pedidosFaturados as $p) {
            $valor = (float)$p['valor_total'];
            $origem = strtolower($p['origem'] ?? 'site'); // Se for nulo, assume site por padrão
            $forma = trim($p['forma_pagamento'] ?? 'Não especificado');
            
            $faturamentoTotal += $valor;
            $qtdTotal++;
            
            // Agrupamento por origem
            if ($origem === 'site') {
                $totalSite += $valor;
                $qtdSite++;
            } elseif ($origem === 'ifood') {
                $totalIfood += $valor;
                $qtdIfood++;
            } elseif ($origem === 'balcao') {
                $totalBalcao += $valor;
                $qtdBalcao++;
            } else {
                $totalOutros += $valor;
                $qtdOutros++;
            }
            
            // Padronização bonita para formas de pagamento
            $formaChave = $forma;
            $lowerForma = strtolower($forma);
            
            if (strpos($lowerForma, 'ifood') !== false) {
                if (strpos($lowerForma, 'entrega') !== false) {
                    $formaChave = 'iFood (Pagar na Entrega)';
                } else {
                    $formaChave = 'iFood (Pago no App)';
                }
            } elseif (strpos($lowerForma, 'pix') !== false) {
                $formaChave = 'PIX';
            } elseif (strpos($lowerForma, 'dinheiro') !== false) {
                $formaChave = 'Dinheiro';
            } elseif (strpos($lowerForma, 'debito') !== false || strpos($lowerForma, 'débito') !== false) {
                $formaChave = 'Cartão de Débito';
            } elseif (strpos($lowerForma, 'credito') !== false || strpos($lowerForma, 'crédito') !== false || strpos($lowerForma, 'cartao') !== false || strpos($lowerForma, 'cartão') !== false) {
                $formaChave = 'Cartão de Crédito';
            }
            
            if (!isset($pagamentos[$formaChave])) {
                $pagamentos[$formaChave] = [
                    'nome' => $formaChave,
                    'valor' => 0.00,
                    'quantidade' => 0
                ];
            }
            $pagamentos[$formaChave]['valor'] += $valor;
            $pagamentos[$formaChave]['quantidade']++;
            
            // Detalhes simplificados para a lista do modal
            $pedidosLista[] = [
                'id' => $p['id'],
                'hora' => date('H:i', strtotime($p['criado_em'])),
                'cliente' => $p['cliente_nome'],
                'valor' => number_format($valor, 2, ',', '.'),
                'origem' => $origem,
                'pagamento' => $formaChave,
                'status' => $p['status']
            ];
        }
    }
    
    // Consulta para pegar métricas de pedidos cancelados e pendentes daquele dia
    $lerTodos = new Ler();
    $lerTodos->Leitura('pedidos', "WHERE DATE(criado_em) = :data", "data={$dataFiltro}");
    $todosPedidos = $lerTodos->getResultado();
    
    $totalCancelados = 0;
    $valorCancelados = 0.00;
    $totalPendentes = 0;
    
    if ($todosPedidos) {
        foreach ($todosPedidos as $p) {
            $st = $p['status'];
            if ($st === 'cancelado') {
                $totalCancelados++;
                $valorCancelados += (float)$p['valor_total'];
            } elseif ($st === 'pendente' || $st === 'aguardando_pagamento') {
                $totalPendentes++;
            }
        }
    }
    
    // Formata os pagamentos como um array numérico para facilitar o uso no JS
    $pagamentosFormatados = array_values($pagamentos);
    // Ordena por maior valor de faturamento
    usort($pagamentosFormatados, function($a, $b) {
        return $b['valor'] <=> $a['valor'];
    });
    
    // Formatação de valores para exibição direta
    echo json_encode([
        'sucesso' => true,
        'data_relatorio' => date('d/m/Y', strtotime($dataFiltro)),
        'data_banco' => $dataFiltro,
        'faturamento_total' => number_format($faturamentoTotal, 2, ',', '.'),
        'faturamento_total_raw' => $faturamentoTotal,
        'total_pedidos' => $qtdTotal,
        
        'site_faturamento' => number_format($totalSite, 2, ',', '.'),
        'site_faturamento_raw' => $totalSite,
        'site_pedidos' => $qtdSite,
        
        'ifood_faturamento' => number_format($totalIfood, 2, ',', '.'),
        'ifood_faturamento_raw' => $totalIfood,
        'ifood_pedidos' => $qtdIfood,
        
        'balcao_faturamento' => number_format($totalBalcao, 2, ',', '.'),
        'balcao_faturamento_raw' => $totalBalcao,
        'balcao_pedidos' => $qtdBalcao,
        
        'outros_faturamento' => number_format($totalOutros, 2, ',', '.'),
        'outros_faturamento_raw' => $totalOutros,
        'outros_pedidos' => $qtdOutros,
        
        'cancelados_total' => $totalCancelados,
        'cancelados_valor' => number_format($valorCancelados, 2, ',', '.'),
        'pendentes_total' => $totalPendentes,
        
        'pagamentos' => $pagamentosFormatados,
        'pedidos' => $pedidosLista
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false,
        'erro' => $e->getMessage()
    ]);
}
?>
