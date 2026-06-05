<?php
/**
 * Este script deve rodar a cada 30 segundos (via cron job ou AJAX do Painel).
 */

require_once('sheep_core/config.php');

// Define cabeçalho JSON
header('Content-Type: application/json; charset=utf-8');

$ifood = new IfoodService();
$log_file = 'log_ifood.txt';

function log_ifood($msg) {
    global $log_file;
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] " . $msg . PHP_EOL, FILE_APPEND);
}

$retorno = [
    'sucesso' => false,
    'eventos_processados' => 0,
    'mensagem' => ''
];

try {
    $eventos = $ifood->getEvents();
    
    if (empty($eventos)) {
        $retorno['sucesso'] = true;
        $retorno['mensagem'] = 'Sem novos eventos na fila do iFood.';
        echo json_encode($retorno);
        exit;
    }

    log_ifood("Coletados " . count($eventos) . " eventos do iFood.");
    $eventosParaConfirmar = [];
    $novosPedidosGravados = 0;

    foreach ($eventos as $evento) {
        $orderId = $evento['orderId'];
        $eventType = $evento['code']; // PLC = Novo Pedido, CAN = Cancelado, etc.
        $eventosParaConfirmar[] = $evento['id'];

        log_ifood("Processando evento: {$eventType} para o Pedido ID: {$orderId}");

        if ($eventType === 'PLC') { // Placed - Novo Pedido
            
            // Puxa os detalhes completos do pedido
            $detalhes = $ifood->getOrderDetails($orderId);
            
            if ($detalhes) {
                // 1. Mapeia os dados do cliente
                $clienteNome = $detalhes['customer']['name'] ?? 'Cliente iFood';
                $clienteTelefone = $detalhes['customer']['phone']['number'] ?? '';
                
                // 2. Mapeia o endereço de entrega
                $enderecoText = "Retirada/Balcão";
                if (isset($detalhes['delivery']['deliveryAddress'])) {
                    $endereco = $detalhes['delivery']['deliveryAddress'];
                    $rua = $endereco['streetName'] ?? '';
                    $numero = $endereco['streetNumber'] ?? 'S/N';
                    $bairro = $endereco['neighborhood'] ?? '';
                    $cidade = $endereco['city'] ?? '';
                    $referencia = $endereco['reference'] ?? '';
                    $enderecoText = "Rua {$rua}, Nº {$numero} - {$bairro}, {$cidade}";
                    if (!empty($referencia)) {
                        $enderecoText .= " (Ref: {$referencia})";
                    }
                }

                // 3. Mapeia valores e formas de pagamento
                $subtotal = $detalhes['total']['subTotal'] ?? 0.00;
                $taxaEntrega = $detalhes['total']['deliveryFee'] ?? 0.00;
                $valorTotal = $detalhes['total']['orderAmount'] ?? 0.00;
                $ifoodCode = $detalhes['displayId'] ?? substr($orderId, -4);

                $metodoPagamento = 'iFood (Pago no App)';
                if (isset($detalhes['payments']['methods'][0])) {
                    $pm = $detalhes['payments']['methods'][0];
                    $metodoName = $pm['method'] ?? '';
                    $cardBrand = $pm['card']['brand'] ?? '';
                    $type = $pm['type'] ?? ''; // ONLINE ou OFFLINE
                    
                    $metodoPagamento = "iFood - {$metodoName} " . (!empty($cardBrand) ? "({$cardBrand})" : "");
                    if ($type === 'OFFLINE') {
                        $metodoPagamento .= " (Pagar na Entrega)";
                    }
                }

                // 4. Verifica se o pedido já existe para evitar duplicados
                $ler = new Ler();
                $ler->Leitura('pedidos', "WHERE ifood_order_id = :id LIMIT 1", "id={$orderId}");
                
                if (!$ler->getResultado()) {
                    // 5. Grava o pedido principal
                    $dadosPedido = [
                        'usuario_id' => null,
                        'cliente_nome' => $clienteNome,
                        'cliente_telefone' => $clienteTelefone,
                        'cliente_endereco' => $enderecoText,
                        'forma_pagamento' => $metodoPagamento,
                        'valor_total' => $valorTotal,
                        'status' => 'pendente', // 'pendente' entra direto no Kanban
                        'origem' => 'ifood',
                        'ifood_order_id' => $orderId,
                        'ifood_status' => 'PLACED',
                        'ifood_code' => "#" . $ifoodCode,
                        'dados_json' => json_encode($detalhes, JSON_UNESCAPED_UNICODE)
                    ];

                    $criar = new Criar();
                    $criar->Criacao('pedidos', $dadosPedido);
                    $pedidoId = $criar->getResultado();
                    
                    if ($pedidoId) {
                        $novosPedidosGravados++;
                        log_ifood("SUCESSO: Novo pedido gravado! ID Interno: {$pedidoId} | iFood Code: #{$ifoodCode}");

                        // 6. Grava os itens do pedido
                        if (isset($detalhes['items'])) {
                            foreach ($detalhes['items'] as $item) {
                                $nomeItem = $item['name'] ?? 'Produto';
                                $qtd = $item['quantity'] ?? 1;
                                $precoUnit = $item['unitPrice'] ?? 0.00;
                                
                                // Concatena opções e adicionais (Ex: borda recheada, sabores)
                                $detalhesItem = "";
                                if (isset($item['options'])) {
                                    $opcoes = [];
                                    foreach ($item['options'] as $opt) {
                                        $opcoes[] = $opt['name'] . (isset($opt['quantity']) ? " (x" . $opt['quantity'] . ")" : "");
                                    }
                                    $detalhesItem = implode(" | ", $opcoes);
                                }

                                $dadosItem = [
                                    'pedido_id' => $pedidoId,
                                    'produto_id' => 0, // 0 indica produto externo / customizado
                                    'quantidade' => $qtd,
                                    'preco_unitario' => $precoUnit,
                                    'detalhes' => "{$nomeItem}" . (!empty($detalhesItem) ? " [{$detalhesItem}]" : "")
                                ];
                                $criar->Criacao('itens_pedido', $dadosItem);
                            }
                        }
                    } else {
                        log_ifood("ERRO: Falha ao inserir o pedido iFood #{$ifoodCode} no banco de dados.");
                    }
                } else {
                    log_ifood("AVISO: Pedido #{$ifoodCode} já existia no banco. Ignorado.");
                }
            }
        } elseif ($eventType === 'CAN') { // Cancelamento
            $atualizar = new Atualizar();
            $atualizar->Atualizando('pedidos', [
                'status' => 'cancelado',
                'ifood_status' => 'CANCELLED'
            ], "WHERE ifood_order_id = :id", "id={$orderId}");

            if ($atualizar->getResultado()) {
                log_ifood("SUCESSO: Pedido iFood ID {$orderId} cancelado pelo cliente/sistema iFood.");
            }
        }
    }

    // 7. Acknowledgment (Confirmação para remover da fila)
    if (!empty($eventosParaConfirmar)) {
        $ack = $ifood->acknowledgeEvents($eventosParaConfirmar);
        if ($ack) {
            log_ifood("Eventos confirmados e removidos da fila do iFood.");
        } else {
            log_ifood("AVISO: Falha ao confirmar os eventos com o iFood.");
        }
    }

    $retorno['sucesso'] = true;
    $retorno['eventos_processados'] = count($eventos);
    $retorno['mensagem'] = "Sucesso. Coletados " . count($eventos) . " eventos. Novos pedidos: {$novosPedidosGravados}.";

} catch (Exception $e) {
    log_ifood("ERRO FATAL: " . $e->getMessage());
    $retorno['mensagem'] = 'Erro: ' . $e->getMessage();
}

echo json_encode($retorno);
exit;
?>
