<?php
// Protection to ensure file runs within admin context
if (!class_exists('Ler')) {
    exit('Acesso direto negado.');
}

// 1. Processamento de Filtros e Busca
$q = filter_input(INPUT_GET, 'q', FILTER_DEFAULT);
$status_filtro = filter_input(INPUT_GET, 'status_filtro', FILTER_DEFAULT) ?? 'finalizados';
$data_inicio = filter_input(INPUT_GET, 'data_inicio', FILTER_DEFAULT);
$data_fim = filter_input(INPUT_GET, 'data_fim', FILTER_DEFAULT);

$where = "WHERE 1=1";
$whereParams = [];

if ($status_filtro == 'finalizados') {
    $where .= " AND status IN ('entregue', 'cancelado')";
} elseif ($status_filtro == 'entregues') {
    $where .= " AND status = 'entregue'";
} elseif ($status_filtro == 'cancelados') {
    $where .= " AND status = 'cancelado'";
} elseif ($status_filtro == 'ativos') {
    $where .= " AND status NOT IN ('entregue', 'cancelado', 'aguardando_pagamento')";
}

if (!empty($q)) {
    if (is_numeric($q)) {
        $where .= " AND id = :busca_id";
        $whereParams['busca_id'] = (int)$q;
    } else {
        // Sanitiza para evitar SQL injection: remove caracteres perigosos
        $q_safe = preg_replace('/[^a-zA-Z0-9\s\-\_\.\,áéíóúãõâêîôûàèìòùÁÉÍÓÚÃÕÂÊÎÔÛÀÈÌÒÙçÇ]/', '', $q);
        $where .= " AND (cliente_nome LIKE :busca_nome OR cliente_telefone LIKE :busca_telefone)";
        $whereParams['busca_nome'] = '%' . $q_safe . '%';
        $whereParams['busca_telefone'] = '%' . $q_safe . '%';
    }
}

if (!empty($data_inicio)) {
    // Valida formato de data YYYY-MM-DD
    $data_inicio_safe = preg_replace('/[^0-9\-]/', '', $data_inicio);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_inicio_safe)) {
        $where .= " AND criado_em >= :data_inicio";
        $whereParams['data_inicio'] = $data_inicio_safe . " 00:00:00";
    }
}
if (!empty($data_fim)) {
    $data_fim_safe = preg_replace('/[^0-9\-]/', '', $data_fim);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_fim_safe)) {
        $where .= " AND criado_em <= :data_fim";
        $whereParams['data_fim'] = $data_fim_safe . " 23:59:59";
    }
}

$where .= " ORDER BY criado_em DESC";

// 2. Leitura dos Pedidos Filtrados
$lerPedidos = new Ler();
$lerPedidos->Leitura('pedidos', $where, http_build_query($whereParams));

// 3. Estatísticas Gerais (Com base em TODOS os pedidos entregues/cancelados na história)
$lerEstatistica = new Ler();
$lerEstatistica->Leitura('pedidos', "WHERE status = 'entregue'");
$faturamento = 0;
$concluidos = $lerEstatistica->getContaLinhas();
if ($lerEstatistica->getResultado()) {
    foreach ($lerEstatistica->getResultado() as $pEst) {
        $faturamento += $pEst['valor_total'];
    }
}
$ticketMedio = $concluidos > 0 ? $faturamento / $concluidos : 0;

$lerCancelados = new Ler();
$lerCancelados->Leitura('pedidos', "WHERE status = 'cancelado'");
$cancelados = $lerCancelados->getContaLinhas();
?>

<div class="main-content pedidos-page">

  <!-- INICIO NAVEGAÇÃO -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="sheep.php">Painel</a></li>
      <li class="breadcrumb-item active" aria-current="page">Histórico de Pedidos</li>
    </ol>
  </nav>
  <!-- FIM NAVEGAÇÃO -->

  <section class="section">
    <div class="section-body">

      <!-- BLOCKS DE ESTATÍSTICA PREMIUM -->
      <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
          <div class="card card-statistic-1" style="border-left: 5px solid #2ebd59; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.03);">
            <div class="card-icon bg-success" style="color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
              <i class="fa fa-usd" style="font-size: 24px;"></i>
            </div>
            <div class="card-wrap">
              <div class="card-header">
                <h4>Faturamento</h4>
              </div>
              <div class="card-body" style="font-size: 18px; font-weight: bold; color: #2d3748; padding-top: 5px;">
                R$ <?= number_format($faturamento, 2, ',', '.') ?>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
          <div class="card card-statistic-1" style="border-left: 5px solid #3182ce; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.03);">
            <div class="card-icon bg-primary" style="color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
              <i class="fa fa-shopping-bag" style="font-size: 24px;"></i>
            </div>
            <div class="card-wrap">
              <div class="card-header">
                <h4>Entregues</h4>
              </div>
              <div class="card-body" style="font-size: 18px; font-weight: bold; color: #2d3748; padding-top: 5px;">
                <?= $concluidos ?> pedidos
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
          <div class="card card-statistic-1" style="border-left: 5px solid #ed8936; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.03);">
            <div class="card-icon bg-warning" style="color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
              <i class="fa fa-line-chart" style="font-size: 24px;"></i>
            </div>
            <div class="card-wrap">
              <div class="card-header">
                <h4>Ticket Médio</h4>
              </div>
              <div class="card-body" style="font-size: 18px; font-weight: bold; color: #2d3748; padding-top: 5px;">
                R$ <?= number_format($ticketMedio, 2, ',', '.') ?>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
          <div class="card card-statistic-1" style="border-left: 5px solid #e53e3e; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.03);">
            <div class="card-icon bg-danger" style="color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
              <i class="fa fa-times-circle" style="font-size: 24px;"></i>
            </div>
            <div class="card-wrap">
              <div class="card-header">
                <h4>Cancelados</h4>
              </div>
              <div class="card-body" style="font-size: 18px; font-weight: bold; color: #2d3748; padding-top: 5px;">
                <?= $cancelados ?> pedidos
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- FILTROS E BUSCA AVANÇADA -->
      <div class="row">
        <div class="col-12">
          <div class="card" style="border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.03);">
            <div class="card-body" style="padding: 20px;">
              <form method="get" action="sheep.php">
                <input type="hidden" name="m" value="sheep-pedidos/index">
                <div class="row align-items-end">
                  <div class="col-lg-3 col-md-6 col-12 mb-3 mb-lg-0">
                    <label style="font-weight: 600; color: #4a5568;"><i class="fa fa-search mr-1"></i> Pesquisa Rápida</label>
                    <input type="text" name="q" class="form-control" placeholder="Cliente, Telefone ou ID" value="<?= htmlspecialchars($q ?? '') ?>" style="border-radius: 6px;">
                  </div>
                  <div class="col-lg-3 col-md-6 col-12 mb-3 mb-lg-0">
                    <label style="font-weight: 600; color: #4a5568;"><i class="fa fa-filter mr-1"></i> Exibir Status</label>
                    <select name="status_filtro" class="form-control" style="border-radius: 6px;">
                      <option value="finalizados" <?= $status_filtro == 'finalizados' ? 'selected' : '' ?>>Histórico (Entregues/Cancelados)</option>
                      <option value="entregues" <?= $status_filtro == 'entregues' ? 'selected' : '' ?>>Apenas Entregues</option>
                      <option value="cancelados" <?= $status_filtro == 'cancelados' ? 'selected' : '' ?>>Apenas Cancelados</option>
                      <option value="ativos" <?= $status_filtro == 'ativos' ? 'selected' : '' ?>>Apenas Ativos (Em Processo)</option>
                      <option value="todos" <?= $status_filtro == 'todos' ? 'selected' : '' ?>>Todos os Pedidos</option>
                    </select>
                  </div>
                  <div class="col-lg-2 col-md-6 col-12 mb-3 mb-lg-0">
                    <label style="font-weight: 600; color: #4a5568;"><i class="fa fa-calendar mr-1"></i> Data Inicial</label>
                    <input type="date" name="data_inicio" class="form-control" value="<?= htmlspecialchars($data_inicio ?? '') ?>" style="border-radius: 6px;">
                  </div>
                  <div class="col-lg-2 col-md-6 col-12 mb-3 mb-lg-0">
                    <label style="font-weight: 600; color: #4a5568;"><i class="fa fa-calendar mr-1"></i> Data Final</label>
                    <input type="date" name="data_fim" class="form-control" value="<?= htmlspecialchars($data_fim ?? '') ?>" style="border-radius: 6px;">
                  </div>
                  <div class="col-lg-2 col-md-12 col-12">
                    <button type="submit" class="btn btn-primary btn-block" style="height: 42px; font-weight: bold; border-radius: 6px;"><i class="fa fa-search"></i> Buscar</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- TABELA DE RESULTADOS DO HISTÓRICO -->
      <div class="row">
        <div class="col-12">
          <div class="card" style="border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.03);">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
              <h4 style="color: #2d3748;"><i class="fa fa-archive text-primary mr-2"></i> Histórico & Relatório de Pedidos</h4>
              <span class="badge badge-primary"><?= $lerPedidos->getContaLinhas() ?> registros encontrados</span>
            </div>
            <div class="card-body" style="padding-top: 10px;">
              <div class="table-responsive">
                <table class="table table-striped table-hover pedidos-table" id="save-stage" style="width:100%;">
                  <thead>
                    <tr>
                      <th>Nº</th>
                      <th>Data/Hora</th>
                      <th>Cliente</th>
                      <th>Valor Total</th>
                      <th>Pagamento</th>
                      <th>Status</th>
                      <th class="text-center">Ações</th>
                    </tr>
                  </thead>
                  <tbody>

                    <?php
                    if ($lerPedidos->getResultado()):
                      foreach ($lerPedidos->getResultado() as $pedido):
                        extract($pedido);
                        
                        // Status e Cores
                        $statusColor = 'secondary';
                        $statusLabel = str_replace('_', ' ', ucfirst($status));
                        if ($status == 'em_producao') $statusLabel = "Em Produção";
                        if ($status == 'saiu_para_entrega') $statusLabel = "Saiu para Entrega";
                        if ($status == 'aguardando_pagamento') $statusLabel = "Aguardando Pagamento";
                        
                        switch ($status) {
                            case 'pendente': $statusColor = 'light'; break;
                            case 'pago': $statusColor = 'info'; break;
                            case 'em_producao': $statusColor = 'info'; break;
                            case 'saiu_para_entrega': $statusColor = 'warning'; break;
                            case 'entregue': $statusColor = 'success'; break;
                            case 'cancelado': $statusColor = 'danger'; break;
                            case 'aguardando_pagamento': $statusColor = 'secondary'; break;
                        }

                        // WhatsApp Message to Motoboy
                        $lerItensMsg = new Ler();
                        $lerItensMsg->Leitura('itens_pedido', "WHERE pedido_id = :pid", "pid={$pedido['id']}");
                        $itensTexto = "";
                        if ($lerItensMsg->getResultado()) {
                            foreach ($lerItensMsg->getResultado() as $itemMsg) {
                                if ($itemMsg['produto_id'] == 0) {
                                    $nomeP = "Pizza Personalizada (" . $itemMsg['detalhes'] . ")";
                                } else {
                                    $lerProdMsg = new Ler();
                                    $lerProdMsg->Leitura('produtos', "WHERE id = :id", "id={$itemMsg['produto_id']}");
                                    $nomeP = $lerProdMsg->getResultado() ? $lerProdMsg->getResultado()[0]['nome'] : 'Produto';
                                }
                                $itensTexto .= "• {$nomeP} x {$itemMsg['quantidade']}\n";
                            }
                        }

                        $msgFinal = "🍕 *HISTÓRICO DE PEDIDO*\n";
                        $msgFinal .= "*Pizzaria Modelo*\n\n";
                        $msgFinal .= "*Pedido:* #{$pedido['id']}\n";
                        $msgFinal .= "*Cliente:* {$pedido['cliente_nome']}\n";
                        $msgFinal .= "*WhatsApp:* {$pedido['cliente_telefone']}\n";
                        $msgFinal .= "*Endereço:* {$pedido['cliente_endereco']}\n\n";
                        $msgFinal .= "*ITENS:*\n{$itensTexto}\n";
                        $msgFinal .= "*TOTAL:* R$ " . number_format($pedido['valor_total'], 2, ',', '.') . "\n";
                        $msgFinal .= "*PAGAMENTO:* {$pedido['forma_pagamento']}\n\n";
                        $msgFinal .= "Status do Pedido: " . strtoupper($statusLabel);
                        
                        $urlWhatsapp = "https://api.whatsapp.com/send?text=" . urlencode($msgFinal);
                    ?>

                        <tr>
                          <td>
                            <span class="badge badge-light" style="font-weight: bold; font-size: 0.95rem;">#<?= $pedido['id'] ?></span>
                          </td>
                          <td>
                            <div style="font-size: 0.9rem; color: #4a5568;">
                              <span><i class="fa fa-calendar mr-1"></i> <?= date('d/m/Y', strtotime($pedido['criado_em'])) ?></span><br>
                              <span><i class="fa fa-clock-o mr-1"></i> <?= date('H:i', strtotime($pedido['criado_em'])) ?></span>
                            </div>
                          </td>
                          <td>
                            <div style="font-weight: 600; color: #2d3748;"><?= $pedido['cliente_nome'] ?></div>
                            <small class="text-muted"><i class="fa fa-whatsapp text-success"></i> <?= $pedido['cliente_telefone'] ?></small>
                          </td>
                          <td style="font-weight: bold; color: #d32f2f;">
                            R$ <?= number_format($pedido['valor_total'], 2, ',', '.') ?>
                          </td>
                          <td>
                            <span class="badge badge-light" style="font-size: 0.85rem; border: 1px solid #e2e8f0; border-radius: 4px;"><?= strtoupper($pedido['forma_pagamento']) ?></span>
                          </td>
                          <td>
                            <span class="badge badge-<?= $statusColor ?>" style="font-size: 0.85rem; padding: 6px 12px; border-radius: 50px;">
                              <?= $statusLabel ?>
                            </span>
                          </td>
                          <td class="text-center">
                            <div class="pedido-actions" style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                              
                              <!-- Botão Ver Detalhes (Modal) -->
                              <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#detalhes-<?= $pedido['id'] ?>" title="Ver Detalhes do Pedido" style="border-radius: 4px; height: 32px; width: 32px; padding: 0;">
                                <i class="fa fa-eye"></i>
                              </button>

                              <!-- Botão Reimprimir Recibo -->
                              <a href="imprimir.php?id=<?= $pedido['id'] ?>" target="_blank" class="btn btn-sm btn-primary" title="Reimprimir Recibo de Venda" style="border-radius: 4px; height: 32px; width: 32px; padding: 0; line-height: 32px;">
                                <i class="fa fa-print"></i>
                              </a>

                              <!-- Botão Compartilhar WhatsApp -->
                              <a href="<?= $urlWhatsapp ?>" target="_blank" class="btn btn-sm btn-success" title="Enviar para WhatsApp" style="border-radius: 4px; height: 32px; width: 32px; padding: 0; line-height: 32px;">
                                <i class="fa fa-whatsapp"></i>
                              </a>

                            </div>
                          </td>
                        </tr>
                    <?php
                      endforeach;
                    else:
                    ?>
                      <tr>
                        <td colspan="7" class="text-center" style="padding: 40px; color: #a0aec0;">
                          <i class="fa fa-folder-open" style="font-size: 2.5rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                          Nenhum pedido finalizado ou cancelado no histórico para os filtros selecionados.
                        </td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- MODAIS DE DETALHE DOS PEDIDOS -->
  <?php
  if ($lerPedidos->getResultado()):
    foreach ($lerPedidos->getResultado() as $pM):
      $idM = $pM['id'];
      $stLabelM = str_replace('_', ' ', ucfirst($pM['status']));
      if ($pM['status'] == 'em_producao') $stLabelM = "Em Produção";
      if ($pM['status'] == 'saiu_para_entrega') $stLabelM = "Saiu para Entrega";
      if ($pM['status'] == 'aguardando_pagamento') $stLabelM = "Aguardando Pagamento";
  ?>
      <div class="modal fade" id="detalhes-<?= $idM ?>" tabindex="-1" role="dialog" aria-labelledby="modalLabel-<?= $idM ?>" aria-hidden="true" style="z-index: 9999 !important;">
          <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content" style="border-radius: 8px;">
                  <div class="modal-header" style="border-bottom: 1px solid #edf2f7;">
                      <h5 class="modal-title" id="modalLabel-<?= $idM ?>" style="font-weight: 700; color: #2d3748;">Detalhes do Pedido #<?= $idM ?></h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                      </button>
                  </div>
                  <div class="modal-body" style="padding: 25px;">
                      <div class="row">
                          <div class="col-md-6" style="border-right: 1px solid #edf2f7;">
                              <h6 style="text-transform: uppercase; letter-spacing: 0.5px; color: #718096; font-size: 0.8rem; font-weight: 700; margin-bottom: 15px;">Informações do Cliente</h6>
                              <p style="font-size: 0.95rem; line-height: 1.6; color: #2d3748;">
                                  <strong>Nome:</strong> <?= $pM['cliente_nome'] ?><br>
                                  <strong>WhatsApp:</strong> <?= $pM['cliente_telefone'] ?><br>
                                  <strong>Endereço:</strong> <?= $pM['cliente_endereco'] ?><br>
                                  <strong>Ponto de Referência:</strong> <?= !empty($pM['referencia']) ? $pM['referencia'] : 'Nenhum informado' ?>
                              </p>
                          </div>
                          <div class="col-md-6" style="padding-left: 25px;">
                              <h6 style="text-transform: uppercase; letter-spacing: 0.5px; color: #718096; font-size: 0.8rem; font-weight: 700; margin-bottom: 15px;">Informações do Pedido</h6>
                              <p style="font-size: 0.95rem; line-height: 1.6; color: #2d3748;">
                                  <strong>Data de Criação:</strong> <?= date('d/m/Y H:i', strtotime($pM['criado_em'])) ?><br>
                                  <strong>Forma de Pagamento:</strong> <?= strtoupper($pM['forma_pagamento']) ?><br>
                                  <strong>Status do Pedido:</strong> <?= $stLabelM ?><br>
                                  <strong>Troco p/ Dinheiro:</strong> <?= !empty($pM['troco']) ? 'R$ ' . number_format($pM['troco'], 2, ',', '.') : 'Não aplicável' ?>
                              </p>
                          </div>
                      </div>
                      <hr style="border-top: 1px solid #edf2f7; margin: 20px 0;">
                      <h6 style="text-transform: uppercase; letter-spacing: 0.5px; color: #718096; font-size: 0.8rem; font-weight: 700; margin-bottom: 15px;">Itens do Pedido</h6>
                      <div class="table-responsive">
                          <table class="table table-sm table-striped">
                              <thead>
                                  <tr>
                                      <th>Produto</th>
                                      <th class="text-center">Qtd</th>
                                      <th class="text-right">Preço Unit.</th>
                                      <th class="text-right">Subtotal</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <?php
                                  $lerItM = new Ler();
                                  $lerItM->Leitura('itens_pedido', "WHERE pedido_id = :pid", "pid={$idM}");
                                  if ($lerItM->getResultado()):
                                      foreach ($lerItM->getResultado() as $itM):
                                          if ($itM['produto_id'] == 0) {
                                              $pN = "<strong>Pizza Personalizada</strong><br><small style='font-size: 0.9rem; color: #666; font-style: italic; line-height: 1.4; display: block;'>" . $itM['detalhes'] . "</small>";
                                          } else {
                                              $lerPrM = new Ler();
                                              $lerPrM->Leitura('produtos', "WHERE id = :id", "id={$itM['produto_id']}");
                                              $pN = $lerPrM->getResultado() ? $lerPrM->getResultado()[0]['nome'] : 'Produto não encontrado';
                                          }
                                  ?>
                                          <tr>
                                              <td style="vertical-align: middle;"><?= $pN ?></td>
                                              <td class="text-center" style="vertical-align: middle;"><?= $itM['quantidade'] ?></td>
                                              <td class="text-right" style="vertical-align: middle;">R$ <?= number_format($itM['preco_unitario'], 2, ',', '.') ?></td>
                                              <td class="text-right" style="vertical-align: middle;">R$ <?= number_format($itM['quantidade'] * $itM['preco_unitario'], 2, ',', '.') ?></td>
                                          </tr>
                                  <?php
                                      endforeach;
                                  endif;
                                  ?>
                              </tbody>
                              <tfoot>
                                  <tr>
                                      <th colspan="3" class="text-right" style="font-size: 1rem;">Total do Pedido:</th>
                                      <th class="text-right" style="color: #d32f2f; font-size: 1.15rem;">R$ <?= number_format($pM['valor_total'], 2, ',', '.') ?></th>
                                  </tr>
                              </tfoot>
                          </table>
                      </div>
                  </div>
                  <div class="modal-footer" style="border-top: 1px solid #edf2f7; padding: 15px 20px;">
                      <a href="imprimir.php?id=<?= $idM ?>" target="_blank" class="btn btn-primary"><i class="fa fa-print"></i> Imprimir Recibo</a>
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                  </div>
              </div>
          </div>
      </div>
  <?php
    endforeach;
  endif;
  ?>
</div>
