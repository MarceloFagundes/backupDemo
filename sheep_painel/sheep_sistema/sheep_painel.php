<?php
$ler = new Ler();

// PG Aprovados
$ler->Leitura('pedidos', "WHERE status = 'pago'");
$totalAprovados = $ler->getContaLinhas();
$valorAprovados = 0;
if($ler->getResultado()){
    foreach($ler->getResultado() as $aprovado){
        $valorAprovados += $aprovado['valor_total'];
    }
}

// PG Pendentes
$ler->Leitura('pedidos', "WHERE status = 'pendente'");
$totalPendentes = $ler->getContaLinhas();
$valorPendentes = 0;
if($ler->getResultado()){
    foreach($ler->getResultado() as $pendente){
        $valorPendentes += $pendente['valor_total'];
    }
}

// Total de Produtos
$ler->Leitura('produtos');
$totalProdutos = $ler->getContaLinhas();

// Clientes (Aqui vou contar pedidos Ãºnicos por telefone como uma aproximaÃ§Ã£o se nÃ£o houver tabela de clientes dedicada)
$ler->Leitura('pedidos', "GROUP BY cliente_telefone");
$totalClientes = $ler->getContaLinhas();

// Status da loja
$ler->Leitura('configuracoes', "WHERE id = '1'");
$status_loja = 'aberta';
if($ler->getResultado()){
    $status_loja = $ler->getResultado()[0]['status_loja'] ?? 'aberta';
}

// Carrega todos os produtos do cardÃ¡pio para o BalcÃ£o
$ler->Leitura('produtos', "ORDER BY nome ASC");
$produtosCatalogo = [];
if ($ler->getResultado()) {
    foreach ($ler->getResultado() as $prod) {
        $produtosCatalogo[] = [
            'id' => (int)$prod['id'],
            'nome' => $prod['nome'],
            'preco' => (float)($prod['preco_promocional'] ? $prod['preco_promocional'] : $prod['preco']),
            'imagem' => $prod['imagem'],
            'categoria' => strtolower(isset($prod['categoria']) ? $prod['categoria'] : '')
        ];
    }
}
?>
<div class="main-content">
  <!-- TELA DO DASHBOARD PRINCIPAL -->
  <section class="section" id="pdvDashboardView">
    <div class="section-body">
      <!-- MÃ©tricas Principais em Cards Estilizados -->
      <div class="pdv-header-container">
        <div class="pdv-title">
          <h2><i class="fa fa-shopping-bag"></i></h2>
          <small>Gerencie seus pedidos em tempo real.</small>
        </div>
        <div class="pdv-controls">
            <a href="../" target="_blank" class="btn-pdv" style="text-decoration:none; color: var(--primary); border-color: var(--primary);"><i class="fa fa-external-link"></i> VER SITE</a>
            <button class="btn-pdv" id="btnNovoPedidoBalcao" style="background: #ff4b2b; color: white; border: none; font-weight: bold; box-shadow: 0 2px 4px rgba(255, 75, 43, 0.15); cursor: pointer;"><i class="fa fa-shopping-cart"></i> NOVO PEDIDO (BALCAO)</button>
            <button class="btn-pdv" id="btnFechamentoCaixa" style="background: #2ebd59; color: white; border: none; font-weight: bold; box-shadow: 0 2px 4px rgba(46, 189, 89, 0.15); cursor: pointer;"><i class="fa fa-calculator"></i> FECHAR CAIXA</button>
           <?php if($status_loja == 'fechada'): ?>
               <button class="btn-pdv btn-fechar-loja" id="btnToggleLoja" data-status="fechada" style="background:var(--danger); color:white; border-color:var(--danger);"><i class="fa fa-power-off"></i> ABRIR LOJA</button>
           <?php else: ?>
               <button class="btn-pdv btn-fechar-loja" id="btnToggleLoja" data-status="aberta"><i class="fa fa-power-off"></i> FECHAR LOJA</button>
           <?php endif; ?>
           <?php
           // 1. Calcula a mÃ©dia histÃ³rica das Ãºltimas 10 entregas finalizadas
           $lerMedias = new Ler();
           $lerMedias->Leitura('pedidos', "WHERE status = 'entregue' AND finalizado_em IS NOT NULL ORDER BY finalizado_em DESC LIMIT 10");
           
           $totalMinutosEntrega = 0;
           $qtdEntrega = 0;
           $totalMinutosRetirada = 0;
           $qtdRetirada = 0;
           
           if ($lerMedias->getResultado()) {
               foreach ($lerMedias->getResultado() as $pedMed) {
                   $inicio = strtotime($pedMed['criado_em']);
                   $fim = strtotime($pedMed['finalizado_em']);
                   $diffMinutos = round(($fim - $inicio) / 60);
                   
                   // Filtra variaÃ§Ãµes absurdas (ex: pedidos de teste finalizados em 1 minuto ou pedidos esquecidos abertos por dias)
                   if ($diffMinutos >= 5 && $diffMinutos <= 180) {
                       $enderecoLower = mb_strtolower($pedMed['cliente_endereco']);
                       if (strpos($enderecoLower, 'retirada') !== false) {
                           $totalMinutosRetirada += $diffMinutos;
                           $qtdRetirada++;
                       } else {
                           $totalMinutosEntrega += $diffMinutos;
                           $qtdEntrega++;
                       }
                   }
               }
           }
           
           // MÃ©dias histÃ³ricas ou fallback padrÃ£o
           $mediaEntrega = ($qtdEntrega > 0) ? round($totalMinutosEntrega / $qtdEntrega) : 40;
           $mediaRetirada = ($qtdRetirada > 0) ? round($totalMinutosRetirada / $qtdRetirada) : 25;
           
           // Garante limites mÃ­nimos realistas
           if ($mediaEntrega < 30) $mediaEntrega = 40;
           if ($mediaRetirada < 15) $mediaRetirada = 25;
           
           // 2. Fator fila de produÃ§Ã£o atual (pedidos ativos em preparaÃ§Ã£o)
           $lerAtivos = new Ler();
           $lerAtivos->Leitura('pedidos', "WHERE status = 'em_producao'");
           $totalFila = $lerAtivos->getResultado() ? count($lerAtivos->getResultado()) : 0;
           
           // Cada pedido ativo na cozinha adiciona 3 minutos de atraso
           $atrasoFila = $totalFila * 3;
           
           $tempoEntregaFinal = $mediaEntrega + $atrasoFila;
           $tempoRetiradaFinal = $mediaRetirada + $atrasoFila;
           
           // Formata o tempo de entrega como faixa (ex: "40 - 45 min")
           $entregaMin = round($tempoEntregaFinal / 5) * 5; // arredonda para mÃºltiplos de 5
           if ($entregaMin < 30) $entregaMin = 40;
           $entregaMax = $entregaMin + 5;
           $textoEntrega = "{$entregaMin} - {$entregaMax} min";
           
           // Formata o tempo de retirada (ex: "25 min")
           $textoRetirada = "{$tempoRetiradaFinal} min";
           ?>
           
           <div class="pdv-timer" title="Media de entrega com base na fila de producao (Fila atual: <?= $totalFila ?> pedidos | Media historica: <?= $mediaEntrega ?> min)">
              <span>Entrega <b><?= $textoEntrega ?></b> <i class="fa fa-history"></i></span>
           </div>
           
           <div class="pdv-timer" title="Media de retirada com base na fila de producao (Fila atual: <?= $totalFila ?> pedidos | Media historica: <?= $mediaRetirada ?> min)">
              <span>Retirada <b><?= $textoRetirada ?></b> <i class="fa fa-history"></i></span>
           </div>
           
           <button class="btn-pdv btn-sino active">SINO <i class="fa fa-bell"></i></button>
        </div>
      </div>
      <!-- FIM CABEÃ‡ALHO DE OPERAÃ‡ÃƒO DO PDV -->

      <!-- INICIO KANBAN DE STATUS -->
      <div class="pdv-kanban">
        <div class="kanban-card">
          <div class="k-title">AGUARDANDO APROVACAO</div>
          <div class="k-count" id="k-aguardando"><?= $totalPendentes ?></div>
          <i class="fa fa-hourglass-half k-icon"></i>
        </div>
        <div class="kanban-card">
          <div class="k-title">EM PREPARACAO</div>
          <div class="k-count" id="k-preparando"><?= $totalAprovados ?></div>
          <i class="fa fa-fire k-icon"></i>
        </div>
        <div class="kanban-card">
          <div class="k-title">SAIU PARA ENTREGA</div>
          <div class="k-count" id="k-entrega">0</div>
          <i class="fa fa-motorcycle k-icon"></i>
        </div>
      </div>
      <!-- FIM KANBAN DE STATUS -->

      <!-- INICIO SPLIT VIEW (LISTA + DETALHES) -->
      <div class="pdv-split-container">
        
        <!-- ESQUERDA: LISTA DE PEDIDOS -->
        <div class="pdv-split-left">
          <div class="pdv-list-header">
            <input type="text" placeholder="Pesquisar pedidos...">
          </div>
          <div class="pdv-order-list">
            
            <?php
            $ler->Leitura('pedidos', "ORDER BY criado_em DESC LIMIT 10");
            if ($ler->getResultado()):
              foreach ($ler->getResultado() as $pedido):
                $statusClass = 'pendente';
                $statusText = 'AGUARDANDO';
                if ($pedido['status'] == 'pago') { $statusClass = 'preparando'; $statusText = 'EM PREPARACAO'; }
                if ($pedido['status'] == 'em_producao') { $statusClass = 'preparando'; $statusText = 'EM PREPARACAO'; }
                if ($pedido['status'] == 'saiu_para_entrega') { $statusClass = 'preparando'; $statusText = 'SAIU PARA ENTREGA'; }
                if ($pedido['status'] == 'entregue') { $statusClass = 'entregue'; $statusText = 'ENTREGUE'; }
                if ($pedido['status'] == 'cancelado') { $statusClass = 'cancelado'; $statusText = 'CANCELADO'; }
                $cardClass = $pedido['status'] == 'pendente' ? 'active' : $statusClass;
            ?>
                <!-- Card do Pedido -->
                <div class="pdv-order-card <?= $cardClass ?>" data-id="<?= $pedido['id'] ?>" onclick="carregarPedido(<?= $pedido['id'] ?>, this)">
                  <div class="pdv-card-top">
                    <span># <?= $pedido['id'] ?></span>
                    <span style="color:var(--primary);">R$ <?= number_format($pedido['valor_total'], 2, ',', '.') ?></span>
                  </div>
                  <div class="pdv-card-info">
                    <?= $pedido['cliente_nome'] ?> â€¢ <?= strtoupper($pedido['forma_pagamento']) ?>
                  </div>
                  <div class="pdv-card-status <?= $statusClass ?>">
                    <?= $statusText ?>
                  </div>
                </div>
            <?php
              endforeach;
            else:
            ?>
               <div style="text-align:center; padding: 20px; color:#999;">Nenhum pedido hoje.</div>
            <?php endif; ?>

          </div>
        </div>

        <!-- DIREITA: DETALHES DO PEDIDO SELECIONADO -->
        <div class="pdv-split-right" id="painel-detalhes">
          
          <!-- Estado Vazio (Antes de clicar em um pedido) -->
          <div id="detalhe-vazio" style="text-align:center; padding: 50px; color:#999;">
             <i class="fa fa-hand-pointer-o" style="font-size: 3rem; margin-bottom:15px; color:#ddd;"></i>
             <p>Selecione um pedido na lista ao lado para ver os detalhes.</p>
          </div>

          <!-- Estado Preenchido -->
          <div id="detalhe-conteudo" style="display:none;">
            <div class="pdv-detail-header">
              <div>
                <h3 style="margin-bottom:5px;" id="detalhe-titulo">Detalhes do pedido # --</h3>
                <small style="color:#666;" id="detalhe-data">Criado em --/--/----</small>
              </div>
              <div class="pdv-detail-actions">
              </div>
            </div>

            <div class="pdv-detail-section">
              <h4><i class="fa fa-user"></i> Dados do Cliente</h4>
              <div class="pdv-detail-grid">
                <div>
                  <span>Nome</span>
                  <strong id="detalhe-nome">--</strong>
                </div>
                <div>
                  <span>Telefone</span>
                  <strong><i class="fa fa-whatsapp" style="color:#25D366;"></i> <span id="detalhe-tel">--</span></strong>
                </div>
                <div>
                  <span>Fidelidade</span>
                  <strong style="color:var(--primary);" id="detalhe-fidelidade">-- pedidos realizados</strong>
                </div>
                <div>
                  <span>Endereco de Entrega</span>
                  <strong id="detalhe-end">--</strong>
                </div>
                <div style="grid-column: span 2;">
                  <span>Ponto de Referencia</span>
                  <strong id="detalhe-ref">--</strong>
                </div>
              </div>
            </div>

            <div class="pdv-detail-section">
              <h4><i class="fa fa-list"></i> Itens do Pedido</h4>
              <div style="border:1px solid var(--border); border-radius:8px; padding:15px; background:white;">
                
                <div id="detalhe-itens-lista" style="font-size: 0.95rem; line-height: 1.6; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:10px;">
                   <!-- Itens via JS -->
                </div>
                
                <div style="display:flex; justify-content:space-between; margin-top:15px; font-size:1.2rem;">
                  <span>Total a Pagar <small id="detalhe-pagamento" style="font-size:0.7em; color:#666;"></small></span>
                  <strong style="color:var(--primary);" id="detalhe-total">R$ 0,00</strong>
                </div>
              </div>
            </div>

            <!-- ÃREA DE BOTÃ•ES DINÃ‚MICOS (Workflow passo a passo) -->
            <div id="acao-botoes" style="display:flex; flex-direction:column; gap:10px; margin-top:30px;">
              <!-- BotÃ£o Principal -->
              <button id="btn-acao-principal" style="padding:20px; border:none; background:var(--primary); color:white; border-radius:8px; font-weight:bold; font-size:1.1rem; cursor:pointer; width:100%;">
                 <i class="fa fa-play"></i> INICIAR FLUXO
              </button>
              
              <div style="display:flex; gap:10px;">
                 <!-- BotÃ£o SecundÃ¡rio (Imprimir ou Cancelar) -->
                 <button id="btn-acao-secundaria" style="flex:1; padding:15px; border:none; background:var(--danger); color:white; border-radius:8px; font-weight:bold; cursor:pointer;">
                    <i class="fa fa-times"></i> CANCELAR PEDIDO
                 </button>
              </div>
            </div>
          </div>

        </div>

      </div>
      <!-- FIM SPLIT VIEW -->

      <!-- SCRIPT PARA DINAMIZAR O SPLIT VIEW E O SINO -->
      <script>
        // ConfiguraÃ§Ã£o do Sino (Alarme de Pedido)
        const somSino = new Audio('assets/audio/sino.wav');
        somSino.loop = true; // Faz o Ã¡udio tocar repetidamente (recorrente)
        
        let maxIdAtual = 0;
        let isSinoLigado = true;
        let primeiraCarga = true;
        let playPromise = null;

        // FunÃ§Ã£o para PARAR o alarme de forma segura
        function pararAlarme() {
            if(playPromise !== null) {
                playPromise.then(_ => {
                    somSino.pause();
                    somSino.currentTime = 0;
                }).catch(error => { console.log(error); });
            } else {
                somSino.pause();
                somSino.currentTime = 0;
            }
            document.querySelector('.btn-sino').style.animation = ''; 
        }
        // Qualquer clique na tela para o alarme
        document.body.addEventListener('click', pararAlarme);

        // Toggle do botÃ£o de sino (Ligar/Desligar)
        document.querySelector('.btn-sino').addEventListener('click', function(e) {
            e.stopPropagation(); 
            this.classList.toggle('active');
            isSinoLigado = this.classList.contains('active');
            if(isSinoLigado) {
                this.innerHTML = 'SINO <i class="fa fa-bell"></i>';
                playPromise = somSino.play();
                pararAlarme();
            } else {
                this.innerHTML = 'SINO <i class="fa fa-bell-slash"></i>';
                pararAlarme();
            }
        });

        // FunÃ§Ã£o para carregar detalhes do pedido (Lado Direito)
        let pedidoAtualId = 0;
        function carregarPedido(id, elementoCard) {
            pedidoAtualId = id;
            document.querySelectorAll('.pdv-order-card').forEach(el => el.classList.remove('active'));
            if(elementoCard) elementoCard.classList.add('active');
            
            fetch(`sheep-filtros/get_pedido_json.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if(data.sucesso) {
                        document.getElementById('detalhe-vazio').style.display = 'none';
                        document.getElementById('detalhe-conteudo').style.display = 'block';

                        document.getElementById('detalhe-titulo').innerHTML = `Detalhes do pedido # ${data.id}`;
                        document.getElementById('detalhe-data').innerHTML = `Realizado em ${data.criado_em}`;
                        
                        document.getElementById('detalhe-nome').innerText = data.cliente_nome;
                        document.getElementById('detalhe-tel').innerText = data.cliente_telefone || 'Nao informado';
                        document.getElementById('detalhe-fidelidade').innerText = `${data.total_pedidos} pedidos realizados`;
                        document.getElementById('detalhe-end').innerText = data.cliente_endereco;
                        document.getElementById('detalhe-ref').innerText = data.referencia || 'Nenhuma referencia';
                        
                        document.getElementById('detalhe-itens-lista').innerHTML = data.detalhes;
                        
                        document.getElementById('detalhe-pagamento').innerText = `(${data.forma_pagamento})`;
                        document.getElementById('detalhe-total').innerText = `R$ ${data.valor_total}`;

                        // ConfiguraÃ§Ã£o do Workflow Passo-a-Passo
                        const btnPrincipal = document.getElementById('btn-acao-principal');
                        const btnSecundario = document.getElementById('btn-acao-secundaria');
                        
                        // Reseta visibilidade (O botÃ£o a de motoboy antigo foi removido da lÃ³gica separada)
                        btnPrincipal.style.display = 'block';
                        btnSecundario.style.display = 'block';

                        // AÃ§Ãµes baseadas no STATUS
                        if(data.status === 'pendente' || data.status === 'pago') {
                            btnPrincipal.innerHTML = '<i class="fa fa-fire"></i> ACEITAR E ENVIAR P/ COZINHA';
                            btnPrincipal.style.background = 'var(--warning)';
                            btnPrincipal.onclick = () => {
                                // Abre a impressÃ£o em nova aba e muda o status
                                window.open(`imprimir.php?id=${pedidoAtualId}`, '_blank');
                                mudarStatus('em_producao');
                            };
                            
                            btnSecundario.innerHTML = '<i class="fa fa-times"></i> RECUSAR / CANCELAR';
                            btnSecundario.onclick = () => { if(confirm("Recusar e cancelar este pedido?")) mudarStatus('cancelado'); };
                        }
                        else if(data.status === 'em_producao') {
                            const isRetirada = data.cliente_endereco && data.cliente_endereco.toLowerCase().includes('retirada');
                            
                            if (isRetirada) {
                                btnPrincipal.innerHTML = '<i class="fa fa-print"></i> IMPRIMIR COMANDA P/ COZINHA';
                                btnPrincipal.style.background = 'var(--warning)';
                                btnPrincipal.onclick = () => {
                                    window.open(`imprimir.php?id=${pedidoAtualId}`, '_blank');
                                    // ApÃ³s imprimir, troca o botÃ£o principal para "Pronto / Entregar"
                                    btnPrincipal.innerHTML = '<i class="fa fa-check-circle"></i> PRONTO / ENTREGAR NO BALCAO';
                                    btnPrincipal.style.background = 'var(--success)';
                                    btnPrincipal.onclick = () => {
                                        mudarStatus('entregue');
                                    };
                                };
                                
                                btnSecundario.innerHTML = '<i class="fa fa-check-circle"></i> PRONTO / ENTREGAR NO BALCAO';
                                btnSecundario.style.background = 'var(--success)';
                                btnSecundario.style.color = 'white';
                                btnSecundario.style.border = 'none';
                                btnSecundario.onclick = () => {
                                    mudarStatus('entregue');
                                };
                            } else {
                                btnPrincipal.innerHTML = '<i class="fa fa-whatsapp"></i> AVISAR MOTOBOY E DESPACHAR';
                                btnPrincipal.style.background = '#25D366'; // Cor do WhatsApp
                                btnPrincipal.onclick = () => {
                                    window.open(data.whatsapp_motoboy, '_blank');
                                    mudarStatus('saiu_para_entrega');
                                };
                            
                                btnSecundario.innerHTML = '<i class="fa fa-print"></i> REIMPRIMIR';
                                btnSecundario.onclick = () => { window.open(`imprimir.php?id=${pedidoAtualId}`, '_blank'); };
                            }
                        }
                        else if(data.status === 'saiu_para_entrega') {
                            btnPrincipal.innerHTML = '<i class="fa fa-home"></i> FINALIZAR (ENTREGUE E PAGO)';
                            btnPrincipal.style.background = 'var(--success)';
                            btnPrincipal.onclick = () => mudarStatus('entregue');
                            
                            btnSecundario.style.display = 'none'; // Esconde cancelar nesta fase
                        }
                        else {
                            // Cancelado ou Entregue (VisualizaÃ§Ã£o Apenas)
                            btnPrincipal.style.display = 'none';
                            btnSecundario.style.display = 'none';
                        }
                    }
                })
                .catch(err => console.error(err));
        }

        // FunÃ§Ã£o para alterar o status do pedido selecionado via AJAX
        function mudarStatus(novoStatus) {
            if(!pedidoAtualId) return;
            
            const formData = new FormData();
            formData.append('id', pedidoAtualId);
            formData.append('status', novoStatus);

            fetch('sheep-filtros/atualiza_status_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.sucesso) {
                    // ForÃ§a a atualizaÃ§Ã£o da tela imediatamente
                    checarNovosPedidos(true);
                    
                    // Se finalizou, cancelou ou entregou, esvazia a tela pra pegar o prÃ³ximo
                    if(novoStatus === 'entregue' || novoStatus === 'cancelado') {
                         document.getElementById('detalhe-conteudo').style.display = 'none';
                         document.getElementById('detalhe-vazio').style.display = 'block';
                         pedidoAtualId = 0;
                    } else {
                         // Se foi pra cozinha ou saiu pra entrega, recarrega o prÃ³prio pedido para atualizar botÃµes
                         carregarPedido(pedidoAtualId, document.querySelector(`.pdv-order-card[data-id='${pedidoAtualId}']`));
                    }
                } else {
                    alert('Erro ao atualizar: ' + data.erro);
                }
            })
            .catch(err => console.error(err));
        }

        // FunÃ§Ã£o para checar novos pedidos e atualizar Kanban
        function checarNovosPedidos(forcarAtualizacao = false) {
            fetch('sheep-filtros/get_status_pdv.php')
                .then(res => res.json())
                .then(data => {
                    if(data.sucesso) {
                        document.getElementById('k-aguardando').innerText = data.kanban_aguardando;
                        document.getElementById('k-preparando').innerText = data.kanban_preparando;
                        document.getElementById('k-entrega').innerText = data.kanban_entrega;

                        // Primeira carga
                        if(primeiraCarga) {
                            if (data.auto_print_ids && data.auto_print_ids.length > 0) {
                                data.auto_print_ids.forEach(id => {
                                    console.log("Imprimindo automaticamente o pedido #" + id);
                                    if (window.imprimirPedidoAutomatico) {
                                        window.imprimirPedidoAutomatico(id);
                                    } else {
                                        window.open('imprimir.php?id=' + id, '_blank');
                                    }
                                });
                            }

                            maxIdAtual = data.max_id;
                            document.querySelector('.pdv-order-list').innerHTML = data.html_lista;
                            primeiraCarga = false;
                            return;
                        }

                        // LÃ³gica de ImpressÃ£o AutomÃ¡tica e Auto-Aceite
                        let acionouAlarme = false;
                        if (data.auto_print_ids && data.auto_print_ids.length > 0) {
                            acionouAlarme = true;
                            data.auto_print_ids.forEach(id => {
                                console.log("Imprimindo automaticamente o pedido #" + id);
                                if (window.imprimirPedidoAutomatico) {
                                    window.imprimirPedidoAutomatico(id);
                                } else {
                                    window.open('imprimir.php?id=' + id, '_blank');
                                }
                            });
                        }

                        // Se tem pedido novo ou status atualizado
                        if(data.max_id > maxIdAtual || forcarAtualizacao || acionouAlarme) {
                            
                            // Atualiza a lista da esquerda
                            document.querySelector('.pdv-order-list').innerHTML = data.html_lista;
                            
                            // DISPARA O ALARME
                            if((data.max_id > maxIdAtual || acionouAlarme) && isSinoLigado) {
                                playPromise = somSino.play();
                                if (playPromise !== undefined) {
                                    playPromise.catch(e => {
                                        console.log("Ãudio bloqueado", e);
                                        const sinoBtn = document.querySelector('.btn-sino');
                                        sinoBtn.style.background = 'var(--danger)';
                                        setTimeout(() => sinoBtn.style.background = '', 2000);
                                    });
                                }
                            }
                            
                            maxIdAtual = data.max_id;
                        }
                    }
                })
                .catch(err => console.error('Erro no Auto-Refresh:', err));
        }

        // Inicia o checador
        checarNovosPedidos();
        setInterval(checarNovosPedidos, 10000);

        // SincronizaÃ§Ã£o Silenciosa com o iFood em Segundo Plano
        function sincronizarIfoodSilencioso() {
            fetch('../../polling_ifood.php')
                .then(res => res.json())
                .then(data => {
                    if (data.sucesso && data.eventos_processados > 0) {
                        console.log("iFood Sincronizado Silenciosamente: " + data.mensagem);
                        // ForÃ§a a atualizaÃ§Ã£o do painel local para disparar o alarme e listar os pedidos
                        checarNovosPedidos(true);
                    }
                })
                .catch(err => console.error("Erro na sincronizaÃ§Ã£o em segundo plano do iFood:", err));
        }

        // Executa a busca de novos pedidos no iFood a cada 20 segundos automaticamente
        sincronizarIfoodSilencioso();
        setInterval(sincronizarIfoodSilencioso, 20000);

        // LÃ³gica de Abrir/Fechar Loja
        document.getElementById('btnToggleLoja').addEventListener('click', function() {
            let currentStatus = this.getAttribute('data-status');
            let novoStatus = currentStatus === 'aberta' ? 'fechada' : 'aberta';
            let btn = this;

            if(!confirm(`Deseja realmente ${novoStatus === 'fechada' ? 'FECHAR' : 'ABRIR'} a loja?`)) return;

            const formData = new FormData();
            formData.append('status_loja', novoStatus);

            fetch('sheep-filtros/atualiza_status_loja.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.sucesso) {
                    btn.setAttribute('data-status', novoStatus);
                    if(novoStatus === 'fechada') {
                        btn.innerHTML = '<i class="fa fa-power-off"></i> ABRIR LOJA';
                        btn.style.background = 'var(--danger)';
                        btn.style.color = 'white';
                        btn.style.borderColor = 'var(--danger)';
                    } else {
                        btn.innerHTML = '<i class="fa fa-power-off"></i> FECHAR LOJA';
                        btn.style.background = '';
                        btn.style.color = '';
                        btn.style.borderColor = '';
                    }
                } else {
                    alert('Erro ao alterar status da loja: ' + data.erro);
                }
            })
            .catch(err => console.error(err));
        });
      </script>

    </div>
  </section>

  <!-- CSS ESTILIZADO DO MODAL DE FECHAMENTO DE CAIXA PREMIUM - LIGHT MODE CLEAN -->
  <style>
    .fechamento-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(5px);
        animation: fadeIn 0.25s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .fechamento-content {
        background: #ffffff; /* Fundo branco super limpo */
        color: #2d3748; /* Texto cinza escuro elegante */
        margin: 2% auto;
        padding: 30px;
        border: 1px solid #e2e8f0;
        width: 90%;
        max-width: 950px;
        border-radius: 12px; /* Cantos padrÃ£o confortÃ¡veis */
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        position: relative;
        animation: slideUp 0.25s ease-out;
        font-family: 'Outfit', 'Inter', sans-serif;
    }

    @keyframes slideUp {
        from { transform: translateY(30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .fechamento-close {
        color: #a0aec0;
        position: absolute;
        right: 18px;
        top: 12px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.2s;
        z-index: 10100; /* Garante que o botÃ£o fechar estÃ¡ sempre clicÃ¡vel e no topo */
    }

    .fechamento-close:hover {
        color: #4a5568;
    }

    /* CabeÃ§alho do modal */
    .fechamento-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #edf2f7;
        padding-bottom: 20px;
        margin-bottom: 25px;
    }

    .fechamento-title {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 700;
        color: #2d3748 !important; /* Cor estÃ¡vel e elegante */
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .fechamento-date-picker {
        background: #ffffff;
        border: 1px solid #cbd5e0;
        color: #2d3748;
        padding: 10px 15px;
        border-radius: 8px;
        outline: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: border-color 0.2s;
        margin-right: 40px; /* Evita conflitos com o botÃ£o 'X' de fechar */
    }

    .fechamento-date-picker:focus {
        border-color: #3182ce;
    }

    /* Grid de Cards EstatÃ­sticos */
    .fechamento-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 25px;
    }

    .fechamento-card {
        background: #ffffff !important;
        color: #2d3748 !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px;
        padding: 20px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02) !important;
        transition: transform 0.2s;
        border-left: 5px solid #a0aec0 !important; /* Borda esquerda sutil */
    }

    .fechamento-card:hover {
        transform: translateY(-1px);
    }

    .fechamento-card-total {
        border-left-color: #48bb78 !important; /* Verde */
    }

    .fechamento-card-site {
        border-left-color: #3182ce !important; /* Azul */
    }

    .fechamento-card-ifood {
        border-left-color: #e53e3e !important; /* Vermelho iFood */
    }
    
    .fechamento-card-balcao {
        border-left-color: #ed8936 !important; /* Laranja */
    }

    .fechamento-card-cancelados {
        border-left-color: #e53e3e !important;
        color: #e53e3e !important;
    }

    .fechamento-card h4 {
        margin: 0 0 8px 0;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.85;
        font-weight: 700;
        color: #718096 !important;
    }

    .fechamento-card .val {
        font-size: 1.5rem;
        font-weight: 800;
        margin: 0;
        color: #2d3748;
    }
    
    .fechamento-card-cancelados .val {
        color: #e53e3e !important;
    }

    .fechamento-card .desc {
        margin: 5px 0 0 0;
        font-size: 0.75rem;
        color: #718096;
        opacity: 0.8;
    }

    /* Grid do corpo */
    .fechamento-body-grid {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .fechamento-body-grid {
            grid-template-columns: 1fr;
        }
    }

    .fechamento-section {
        background: #f1f5f9; /* Slate 100 - cinza premium ligeiramente escurecido para Ã³timo destaque */
        border: 1px solid #cbd5e0; /* Gray 300 - borda destacada e nÃ­tida */
        border-radius: 8px;
        padding: 20px;
        color: #2d3748;
    }

    .fechamento-section h3 {
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 1rem;
        border-bottom: 1px solid #cbd5e0; /* Divisor mais destacado */
        padding-bottom: 8px;
        color: #2d3748;
        font-weight: 700;
    }

    /* Tabelas */
    .fechamento-table {
        width: 100%;
        border-collapse: collapse;
    }

    .fechamento-table th, .fechamento-table td {
        padding: 8px 10px;
        text-align: left;
        border-bottom: 1px solid #cbd5e0; /* Linha divisÃ³ria da tabela condizente com a borda */
        font-size: 0.85rem;
        color: #4a5568;
    }

    .fechamento-table th {
        font-weight: 700;
        color: #718096;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .fechamento-table tr:last-child td {
        border-bottom: none;
    }

    .fechamento-list-container {
        max-height: 250px;
        overflow-y: auto;
    }

    /* Custom Scrollbar */
    .fechamento-list-container::-webkit-scrollbar, 
    #fechamento-pagamentos-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .fechamento-list-container::-webkit-scrollbar-track,
    #fechamento-pagamentos-scroll::-webkit-scrollbar-track {
        background: #edf2f7;
    }
    .fechamento-list-container::-webkit-scrollbar-thumb,
    #fechamento-pagamentos-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 3px;
    }

    /* RodapÃ© do Modal */
    .fechamento-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #edf2f7;
        padding-top: 15px;
        margin-top: 15px;
    }

    .btn-fechamento-whatsapp {
        background: #25D366;
        color: white;
        border: none;
        padding: 10px 20px;
        font-weight: bold;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 8px rgba(37, 211, 102, 0.15);
    }

    .btn-fechamento-whatsapp:hover {
        background: #20ba59;
        transform: translateY(-1px);
        box-shadow: 0 5px 12px rgba(37, 211, 102, 0.25);
    }

    .btn-fechamento-fechar {
        background: #e2e8f0;
        color: #4a5568;
        border: 1px solid #cbd5e0;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .btn-fechamento-fechar:hover {
        background: #cbd5e0;
        color: #2d3748;
    }
  </style>

  <!-- ESTRUTURA HTML DO MODAL DE FECHAMENTO DE CAIXA -->
  <div id="modalFechamento" class="fechamento-modal">
      <div class="fechamento-content">
          <span class="fechamento-close" id="fechamentoClose">&times;</span>
          
          <div class="fechamento-header">
              <h2 class="fechamento-title"><i class="fa fa-calculator"></i> Fechamento de Caixa</h2>
              <input type="date" class="fechamento-date-picker" id="fechamentoData" value="<?= date('Y-m-d') ?>">
          </div>
          
          <!-- Cards Estatisticos -->
          <div class="fechamento-cards">
              <div class="fechamento-card fechamento-card-total">
                  <h4>Faturamento Total</h4>
                  <p class="val" id="faturam-total">R$ 0,00</p>
                  <p class="desc"><span id="faturam-qtd-total">0</span> pedidos consolidados</p>
              </div>
              <div class="fechamento-card fechamento-card-site">
                  <h4>Canal Loja/Site</h4>
                  <p class="val" id="faturam-site">R$ 0,00</p>
                  <p class="desc"><span id="faturam-qtd-site">0</span> pedidos finalizados</p>
              </div>
              <div class="fechamento-card fechamento-card-ifood">
                  <h4>Canal iFood</h4>
                  <p class="val" id="faturam-ifood">R$ 0,00</p>
                  <p class="desc"><span id="faturam-qtd-ifood">0</span> pedidos finalizados</p>
              </div>
               <div class="fechamento-card fechamento-card-balcao">
                  <h4>Vendas Balcao</h4>
                  <p class="val" id="faturam-balcao">R$ 0,00</p>
                  <p class="desc"><span id="faturam-qtd-balcao">0</span> pedidos presenciais</p>
               </div>
              <div class="fechamento-card fechamento-card-cancelados">
                  <h4>Cancelados hoje</h4>
                  <p class="val" id="faturam-cancelados">R$ 0,00</p>
                  <p class="desc"><span id="faturam-qtd-cancelados">0</span> pedidos cancelados</p>
              </div>
          </div>
          
          <!-- Grid Principal -->
          <div class="fechamento-body-grid">
              <!-- Coluna Esquerda: Formas de Pagamento -->
              <div class="fechamento-section">
                  <h3><i class="fa fa-credit-card"></i> Formas de Pagamento</h3>
                  <div id="fechamento-pagamentos-scroll" style="max-height:250px; overflow-y:auto; padding-right:5px;">
                      <table class="fechamento-table">
                          <thead>
                              <tr>
                                  <th>Metodo</th>
                                  <th>Qtd</th>
                                  <th style="text-align:right;">Subtotal</th>
                              </tr>
                          </thead>
                          <tbody id="fechamento-pagamentos-rows">
                              <!-- Inserido dinamicamente por JS -->
                          </tbody>
                      </table>
                  </div>
              </div>
              
              <!-- Coluna Direita: Pedidos Consolidados -->
              <div class="fechamento-section">
                  <h3><i class="fa fa-list"></i> Detalhe de Pedidos</h3>
                  <div class="fechamento-list-container" style="padding-right:5px;">
                      <table class="fechamento-table">
                          <thead>
                              <tr>
                                  <th>Pedido</th>
                                  <th>Cliente</th>
                                  <th>Canal</th>
                                  <th style="text-align:right;">Valor</th>
                              </tr>
                          </thead>
                          <tbody id="fechamento-pedidos-rows">
                              <!-- Inserido dinamicamente por JS -->
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
          
          <!-- RodapÃ© do Modal -->
          <div class="fechamento-footer">
              <div style="font-size:0.85rem; opacity:0.65; max-width:50%;">
                  *Apenas pedidos finalizados (entregue/pago/em produÃ§Ã£o/saiu para entrega) sÃ£o calculados no faturamento ativo.
              </div>
              <div style="display:flex; gap:10px;">
                  <button class="btn-fechamento-fechar" id="fechamentoBtnFechar">Fechar Janela</button>
                  <button class="btn-fechamento-whatsapp" id="fechamentoBtnWhatsapp">
                      <i class="fa fa-whatsapp"></i> Copiar RelatÃ³rio (WhatsApp)
                  </button>
              </div>
          </div>
          </div>
      </div>
  </div>
  <!-- SCRIPT JAVASCRIPT DO FECHAMENTO DE CAIXA -->
  <script>
    // InicializaÃ§Ã£o do Modal de Fechamento
    const modalFechamento = document.getElementById('modalFechamento');
    const btnFechamento = document.getElementById('btnFechamentoCaixa');
    const closeFechamentoX = document.getElementById('fechamentoClose');
    const closeFechamentoBtn = document.getElementById('fechamentoBtnFechar');
    const dateInputFechamento = document.getElementById('fechamentoData');
    const wppBtnFechamento = document.getElementById('fechamentoBtnWhatsapp');
    
    let fechamentoDataCache = null;

    // AÃ§Ãµes de Abrir/Fechar Modal
    btnFechamento.addEventListener('click', function(e) {
        e.stopPropagation();
        modalFechamento.style.display = 'block';
        consultarFechamentoCaixa(dateInputFechamento.value);
    });

    function fecharCaixaModal() {
        modalFechamento.style.display = 'none';
    }

    closeFechamentoX.addEventListener('click', fecharCaixaModal);
    closeFechamentoBtn.addEventListener('click', fecharCaixaModal);
    
    window.addEventListener('click', function(e) {
        if (e.target == modalFechamento) {
            fecharCaixaModal();
        }
    });

    // MudanÃ§a de Data
    dateInputFechamento.addEventListener('change', function() {
        consultarFechamentoCaixa(this.value);
    });

    // FunÃ§Ã£o de Consulta AJAX
    function consultarFechamentoCaixa(dataString) {
        // Exibe loader simulado
        document.getElementById('faturam-total').innerText = 'Calculando...';
        document.getElementById('faturam-site').innerText = '...';
        document.getElementById('faturam-ifood').innerText = '...';
        document.getElementById('faturam-balcao').innerText = '...';
        
        fetch(`sheep-filtros/fechamento_caixa_ajax.php?data=${dataString}`)
            .then(res => res.json())
            .then(data => {
                if (data.sucesso) {
                    fechamentoDataCache = data;
                    
                    // Preenche os Cards Superiores
                    document.getElementById('faturam-total').innerText = 'R$ ' + data.faturamento_total;
                    document.getElementById('faturam-qtd-total').innerText = data.total_pedidos;
                    
                    document.getElementById('faturam-site').innerText = 'R$ ' + data.site_faturamento;
                    document.getElementById('faturam-qtd-site').innerText = data.site_pedidos;
                    
                    document.getElementById('faturam-ifood').innerText = 'R$ ' + data.ifood_faturamento;
                    document.getElementById('faturam-qtd-ifood').innerText = data.ifood_pedidos;
                    
                    document.getElementById('faturam-balcao').innerText = 'R$ ' + data.balcao_faturamento;
                    document.getElementById('faturam-qtd-balcao').innerText = data.balcao_pedidos;
                    
                    document.getElementById('faturam-cancelados').innerText = 'R$ ' + data.cancelados_valor;
                    document.getElementById('faturam-qtd-cancelados').innerText = data.cancelados_total;
                    
                    // Tabela de MÃ©todos de Pagamento
                    const payBody = document.getElementById('fechamento-pagamentos-rows');
                    payBody.innerHTML = '';
                    if (data.pagamentos && data.pagamentos.length > 0) {
                        data.pagamentos.forEach(p => {
                            const valFormatado = parseFloat(p.valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                            payBody.innerHTML += `
                                <tr>
                                    <td><strong>${p.nome}</strong></td>
                                    <td>${p.formatting_qtd || p.quantidade}</td>
                                    <td style="text-align:right; font-weight:700; color:#2ebd59;">${valFormatado}</td>
                                </tr>
                            `;
                        });
                    } else {
                        payBody.innerHTML = `<tr><td colspan="3" style="text-align:center; opacity:0.5; padding: 25px 0;">Nenhum faturamento registrado.</td></tr>`;
                    }
                    
                    // Tabela de Pedidos
                    const pedBody = document.getElementById('fechamento-pedidos-rows');
                    pedBody.innerHTML = '';
                    if (data.pedidos && data.pedidos.length > 0) {
                        data.pedidos.forEach(p => {
                            let badgeTag = '';
                            if (p.origem === 'ifood') {
                                badgeTag = '<span class="badge" style="background:#ea1d2c; color:white; font-size:0.7rem; padding:2px 5px; border-radius:3px; font-weight:bold;">iFood</span>';
                            } else if (p.origem === 'balcao') {
                                badgeTag = '<span class="badge" style="background:#ff4b2b; color:white; font-size:0.7rem; padding:2px 5px; border-radius:3px; font-weight:bold;">Balcao</span>';
                            } else {
                                badgeTag = '<span class="badge" style="background:#11998e; color:white; font-size:0.7rem; padding:2px 5px; border-radius:3px; font-weight:bold;">Site</span>';
                            }
                            
                            pedBody.innerHTML += `
                                <tr>
                                    <td><span style="opacity:0.65; font-size:0.8rem;">${p.hora}</span> #<strong>${p.id}</strong></td>
                                    <td>${p.cliente.substring(0, 16)}${p.cliente.length > 16 ? '...' : ''}</td>
                                    <td>${badgeTag}</td>
                                    <td style="text-align:right; font-weight:600;">R$ ${p.valor}</td>
                                </tr>
                            `;
                        });
                    } else {
                        pedBody.innerHTML = `<tr><td colspan="4" style="text-align:center; opacity:0.5; padding: 25px 0;">Nenhum pedido finalizado nesta data.</td></tr>`;
                    }
                } else {
                    alert('Falha ao calcular o fechamento: ' + data.erro);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erro de comunicaÃ§Ã£o com o servidor.');
            });
    }

    // Copiar RelatÃ³rio no Clipboard e WhatsApp
    wppBtnFechamento.addEventListener('click', function(e) {
        e.stopPropagation();
        if (!fechamentoDataCache) {
            alert('Aguarde o fechamento carregar antes de enviar o relatorio.');
            return;
        }
        
        let msg = `*FECHAMENTO DE CAIXA - MONDINI PIZZARIA*\n`;
        msg += `*Data:* ${fechamentoDataCache.data_relatorio}\n`;
        msg += `-------------------------------------------\n\n`;
        msg += `*FATURAMENTO TOTAL: R$ ${fechamentoDataCache.faturamento_total}*\n`;
        msg += `*Total de Pedidos:* ${fechamentoDataCache.total_pedidos}\n\n`;
        
        msg += `*VENDAS DO SITE:*\n`;
        msg += `   - Faturamento: R$ ${fechamentoDataCache.site_faturamento}\n`;
        msg += `   - Pedidos: ${fechamentoDataCache.site_pedidos}\n\n`;
        
        msg += `*VENDAS DO IFOOD:*\n`;
        msg += `   - Faturamento: R$ ${fechamentoDataCache.ifood_faturamento}\n`;
        msg += `   - Pedidos: ${fechamentoDataCache.ifood_pedidos}\n\n`;
        
        msg += `*VENDAS DE BALCAO (PDV):*\n`;
        msg += `   - Faturamento: R$ ${fechamentoDataCache.balcao_faturamento}\n`;
        msg += `   - Pedidos: ${fechamentoDataCache.balcao_pedidos}\n\n`;
        
        if (fechamentoDataCache.pagamentos && fechamentoDataCache.pagamentos.length > 0) {
            msg += `*METODOS DE PAGAMENTO:*\n`;
            fechamentoDataCache.pagamentos.forEach(p => {
                const valFormatado = parseFloat(p.valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                msg += `   - ${p.nome}: ${valFormatado} (${p.quantidade} ped.)\n`;
            });
            msg += `\n`;
        }
        
        if (fechamentoDataCache.cancelados_total > 0) {
            msg += `*PEDIDOS CANCELADOS:*\n`;
            msg += `   - Quantidade: ${fechamentoDataCache.cancelados_total}\n`;
            msg += `   - Valor estornado: R$ ${fechamentoDataCache.cancelados_valor}\n\n`;
        }
        
        msg += `_Resumo gerado de forma segura pelo painel PDV Modelo._`;
        
        const telefoneDestino = prompt('Digite o WhatsApp do destinatario com DDD, ou deixe em branco para escolher a conversa no WhatsApp:');
        if (telefoneDestino === null) {
            return;
        }

        const telefoneLimpo = (telefoneDestino || '').replace(/\D/g, '');
        let telefoneFinal = telefoneLimpo;

        if (telefoneFinal.length === 10 || telefoneFinal.length === 11) {
            telefoneFinal = '55' + telefoneFinal;
        }

        if (telefoneFinal && (telefoneFinal.length < 12 || telefoneFinal.length > 13)) {
            alert('Numero invalido. Digite com DDD, por exemplo: 47996046719.');
            return;
        }

        const whatsappUrl = telefoneFinal
            ? `https://wa.me/${telefoneFinal}?text=${encodeURIComponent(msg)}`
            : `https://wa.me/?text=${encodeURIComponent(msg)}`;

        function abrirWhatsApp() {
            const janelaWhatsApp = window.open(whatsappUrl, '_blank', 'noopener');

            if (!janelaWhatsApp) {
                window.location.href = whatsappUrl;
            }
        }

        navigator.clipboard.writeText(msg).then(() => {
            abrirWhatsApp();

            const wppOriginalContent = wppBtnFechamento.innerHTML;
            wppBtnFechamento.style.background = '#11998e';
            wppBtnFechamento.innerHTML = '<i class="fa fa-check"></i> Copiado e aberto no WhatsApp';

            setTimeout(() => {
                wppBtnFechamento.style.background = '';
                wppBtnFechamento.innerHTML = wppOriginalContent;
            }, 1800);
        }).catch(err => {
            console.error(err);
            abrirWhatsApp();
            alert('Nao foi possivel copiar automaticamente, mas o WhatsApp foi aberto com o relatorio.');
        });
    });
  </script>

  <!-- TELA AJAX DE VENDA DE BALCAO (Manual) -->
  <section id="balcaoWorkspace" class="balcao-workspace" style="display: none; padding: 0;">
      <div class="fechamento-content">
          <span class="fechamento-close" id="balcaoClose">&times;</span>

           <div class="fechamento-header">
              <h2 class="fechamento-title"><i class="fa fa-shopping-cart"></i> Registro de Venda - Balcao / PDV</h2>
              <span style="font-size: 0.9rem; opacity: 0.7; font-weight: 600; color: #4a5568; margin-right: 40px;"><i class="fa fa-calendar"></i> <?= date('d/m/Y') ?></span>
           </div>

          <form id="formPedidoBalcao" onsubmit="event.preventDefault(); registrarVendaBalcao();">
              <div class="fechamento-body-grid" style="grid-template-columns: 1.1fr 1.3fr; gap: 20px;">

                  <!-- Coluna Esquerda: Informacoes do Cliente & Pagamento -->
                  <div class="fechamento-section">
                      <h3><i class="fa fa-user"></i> Informacoes da Venda</h3>

                      <div class="balcao-form-group">
                          <label>Nome do Cliente *</label>
                          <input type="text" id="balcaoClienteNome" required placeholder="Ex: Joao Silva" class="balcao-input">
                      </div>

                      <div class="balcao-form-group">
                          <label>Telefone / WhatsApp *</label>
                          <input type="text" id="balcaoClienteTel" required placeholder="Ex: (11) 99999-9999" class="balcao-input">
                      </div>

                      <div class="balcao-form-group">
                          <label>Modalidade de Atendimento</label>
                          <select id="balcaoModalidade" class="balcao-input" onchange="toggleBalcaoEndereco(this.value)">
                              <option value="retirada">Retirada no Balcao</option>
                              <option value="entrega">Entrega em Domicilio</option>
                          </select>
                      </div>

                      <!-- Campos especificos de entrega -->
                      <div id="balcaoCamposEntrega" style="display: none; border-left: 3px solid #ff4b2b; padding-left: 12px; margin-top: 15px;">
                          <div class="balcao-form-group">
                              <label>Endereco de Entrega *</label>
                              <textarea id="balcaoEndereco" placeholder="Rua, numero, bairro, complemento..." class="balcao-input" style="height: 70px; resize: none;"></textarea>
                          </div>
                          <div class="balcao-form-group">
                              <label>Taxa de Entrega (R$)</label>
                              <input type="number" step="0.01" min="0" id="balcaoTaxaEntrega" placeholder="0.00" value="0.00" class="balcao-input" oninput="recalcularTotalBalcao()">
                          </div>
                      </div>

                      <div class="balcao-form-group" style="margin-top: 15px;">
                          <label>Forma de Pagamento</label>
                          <select id="balcaoFormaPagamento" class="balcao-input">
                              <option value="Dinheiro">Dinheiro</option>
                              <option value="PIX">PIX</option>
                              <option value="Cartao de Credito">Cartao de Credito</option>
                              <option value="Cartao de Debito">Cartao de Debito</option>
                              <option value="iFood (Pago no App)">iFood (Pago no App)</option>
                          </select>
                      </div>
                  </div>

                  <!-- Coluna Direita: Catalogo e Carrinho -->
                  <div class="fechamento-section" style="display: flex; flex-direction: column;">
                      <h3><i class="fa fa-list"></i> Produtos e Carrinho</h3>

                      <!-- Abas -->
                      <div class="balcao-tabs" style="display: flex; gap: 5px; margin-bottom: 12px; background: #edf2f7; padding: 4px; border-radius: 8px;">
                          <button type="button" class="balcao-tab-btn active" id="btnTabMontar" onclick="switchBalcaoTab('montar')" style="flex: 1; padding: 8px; border: none; border-radius: 6px; background: #ffffff; color: #2d3748; cursor: pointer; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.04);"><i class="fa fa-cutlery"></i> MONTAR PIZZA</button>
                          <button type="button" class="balcao-tab-btn" id="btnTabProdutos" onclick="switchBalcaoTab('produtos')" style="flex: 1; padding: 8px; border: none; border-radius: 6px; background: transparent; color: #718096; cursor: pointer; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s;"><i class="fa fa-search"></i> PRODUTOS E BEBIDAS</button>
                      </div>

                      <!-- ABA: PRODUTOS NORMAIS -->
                      <div id="balcaoSecaoProdutos" style="display: none; background: #f1f5f9; border: 1px solid #cbd5e0; border-radius: 8px; padding: 12px; margin-bottom: 15px; color: #2d3748;">
                          <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                              <div style="flex: 2;">
                                  <input type="text" id="balcaoPesquisaProd" placeholder="Pesquisar refrigerante, pizza pronta..." class="balcao-input" style="margin-top:0;" oninput="filtrarProdutosBalcao(this.value)">
                              </div>
                              <div style="flex: 0.8;">
                                  <input type="number" id="balcaoQtdProd" min="1" value="1" class="balcao-input" style="margin-top:0;">
                              </div>
                              <button type="button" onclick="adicionarItemBalcao()" style="background: #ff4b2b; color: white; border: none; padding: 0 15px; border-radius: 8px; font-weight: bold; cursor: pointer;">
                                  <i class="fa fa-plus"></i> ADD
                              </button>
                          </div>
                          <select id="balcaoSelectProd" class="balcao-input" style="margin-top:0;">
                              <!-- Preenchido por JS -->
                          </select>
                      </div>

                      <!-- ABA: MONTAR PIZZA -->
                      <div id="balcaoSecaoMontar" style="background: #f1f5f9; border: 1px solid #cbd5e0; border-radius: 8px; padding: 12px; margin-bottom: 15px; color: #2d3748;">
                          <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                              <div style="flex: 1;">
                                  <label style="font-size: 0.75rem; font-weight: 600; opacity: 0.7; text-transform: uppercase;">Tamanho</label>
                                  <select id="montarTamanho" class="balcao-input" style="margin-top: 4px;" onchange="ajustarMontarSabores(this.value)">
                                      <option value="Grande" data-price="59.90" data-max="4" selected>Grande (8 fatias - R$ 59,90)</option>
                                      <option value="Broto" data-price="39.90" data-max="1">Broto (4 fatias - R$ 39,90)</option>
                                      <option value="Media" data-price="49.90" data-max="3">Media (6 fatias - R$ 49,90)</option>
                                      <option value="Gigante" data-price="74.90" data-max="4">Gigante (12 fatias - R$ 74,90)</option>
                                  </select>
                              </div>
                              <div style="flex: 1;">
                                  <label style="font-size: 0.75rem; font-weight: 600; opacity: 0.7; text-transform: uppercase;">Borda Recheada</label>
                                  <select id="montarBorda" class="balcao-input" style="margin-top: 4px;">
                                      <option value="Sem Borda" data-price="0.00" selected>Sem Borda (Gratis)</option>
                                      <option value="Catupiry" data-price="8.90">Catupiry (+ R$ 8,90)</option>
                                      <option value="Cheddar" data-price="9.90">Cheddar (+ R$ 9,90)</option>
                                      <option value="Chocolate" data-price="11.90">Chocolate (+ R$ 11,90)</option>
                                  </select>
                              </div>
                          </div>

                          <div class="balcao-sabor-picker">
                              <div class="balcao-sabor-picker-head">
                                  <span>Sabores da pizza</span>
                                  <small id="montarLimiteSabores">0/4 selecionados</small>
                              </div>
                              <div class="balcao-sabor-add">
                                  <input type="text" id="montarBuscaSabor" placeholder="Digite para filtrar sabor..." class="balcao-input" oninput="filtrarSaboresMontar(this.value)">
                                  <select id="montarSelectSabor" class="balcao-input"></select>
                                  <button type="button" onclick="adicionarSaborMontar()">
                                      <i class="fa fa-plus"></i> OK
                                  </button>
                              </div>
                              <div id="containerSaboresMontar" class="balcao-sabores-selecionados">
                                  <!-- Sabores escolhidos por JS -->
                              </div>
                          </div>

                          <div style="display: flex; gap: 10px; align-items: flex-end; border-top: 1px dashed #cbd5e0; padding-top: 10px;">
                              <div style="flex: 0.4;">
                                  <label style="font-size: 0.75rem; font-weight: 600; opacity: 0.7; text-transform: uppercase;">Qtd</label>
                                  <input type="number" id="montarQtd" min="1" value="1" class="balcao-input" style="margin-top: 4px;">
                              </div>
                              <button type="button" onclick="adicionarPizzaMontadaBalcao()" style="flex: 1.6; background: #ff4b2b; color: white; border: none; padding: 11px 0; border-radius: 8px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                  <i class="fa fa-cutlery"></i> Adicionar Pizza ao Carrinho
                              </button>
                          </div>
                      </div>

                      <!-- Carrinho -->
                      <div style="flex: 1; max-height: 180px; overflow-y: auto; border: 1px solid #cbd5e0; border-radius: 8px; background: #f1f5f9; margin-bottom: 15px;">
                          <table class="fechamento-table" style="font-size: 0.85rem;">
                              <thead>
                                  <tr>
                                      <th>Item</th>
                                      <th style="width: 50px; text-align: center;">Qtd</th>
                                      <th style="width: 90px; text-align: right;">Preco</th>
                                      <th style="width: 90px; text-align: right;">Subtotal</th>
                                      <th style="width: 40px; text-align: center;">Acao</th>
                                  </tr>
                              </thead>
                              <tbody id="balcaoCarrinhoRows">
                                  <!-- Inserido por JS -->
                              </tbody>
                          </table>
                      </div>

                      <!-- Totais -->
                      <div style="border-top: 1px solid #edf2f7; padding-top: 12px; display: flex; flex-direction: column; gap: 6px;">
                          <div style="display: flex; justify-content: space-between; font-size: 0.9rem; opacity: 0.8;">
                              <span>Subtotal Itens:</span>
                              <span id="balcaoTotalItens">R$ 0,00</span>
                          </div>
                          <div id="balcaoLinhaTaxa" style="display: none; justify-content: space-between; font-size: 0.9rem; opacity: 0.8;">
                              <span>Taxa de Entrega:</span>
                              <span id="balcaoTotalTaxa">R$ 0,00</span>
                          </div>
                          <div style="display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: 800; border-top: 1px dashed #cbd5e0; padding-top: 8px;">
                              <span>Total do Pedido:</span>
                              <span style="color: #ff4b2b;" id="balcaoTotalGeral">R$ 0,00</span>
                          </div>
                      </div>
                  </div>
              </div>

              <!-- Rodape -->
              <div class="fechamento-footer" style="margin-top: 20px;">
                  <span style="font-size:0.8rem; opacity:0.6; max-width: 50%;">
                      *O pedido entrara automaticamente como "EM PREPARACAO" no Kanban.
                  </span>
                  <div style="display:flex; gap:10px;">
                      <button type="button" class="btn-fechamento-fechar" id="balcaoBtnFechar">Voltar / Cancelar</button>
                      <button type="submit" class="btn-fechamento-fechar" style="background: #ff4b2b; color: white; border: none; box-shadow: 0 2px 4px rgba(255,75,43,0.15);">
                          <i class="fa fa-check"></i> Registrar Venda Manual
                      </button>
                  </div>
              </div>
          </form>
      </div>
  </section>

  <!-- ESTILOS DO BALCAO -->
  <style>
    .balcao-workspace {
        width: 100%;
        max-width: 100%;
        min-height: calc(100vh - 62px);
        padding: 14px !important;
        background:
            radial-gradient(circle at top right, rgba(255, 75, 43, 0.08), transparent 32%),
            #f6f8fb;
        animation: balcaoFadeIn 0.18s ease-out;
    }
    @keyframes balcaoFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .balcao-workspace .fechamento-content {
        max-width: none !important;
        width: 100%;
        margin: 0 !important;
        height: calc(100vh - 90px);
        min-height: 620px;
        padding: 14px !important;
        border-radius: 16px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        border: 1px solid #e6edf5;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: rgba(255,255,255,0.96);
    }
    .balcao-workspace .fechamento-close { display: none; }
    .balcao-workspace .fechamento-header {
        padding-bottom: 10px;
        margin-bottom: 12px;
        border-bottom: 1px solid #eef2f7;
    }
    .balcao-workspace .fechamento-header span { margin-right: 0 !important; }
    .balcao-workspace .fechamento-title {
        font-size: 1.04rem;
        letter-spacing: -0.01em;
        color: #0f172a;
    }
    .balcao-workspace #formPedidoBalcao {
        display: flex;
        flex: 1;
        min-height: 0;
        flex-direction: column;
    }
    .balcao-workspace .fechamento-body-grid {
        grid-template-columns: minmax(250px, 0.78fr) minmax(520px, 1.42fr) !important;
        gap: 12px !important;
        flex: 1;
        min-height: 0;
        margin-bottom: 0 !important;
    }
    .balcao-workspace .fechamento-section {
        padding: 12px !important;
        background: #ffffff !important;
        border: 1px solid #e6edf5 !important;
        border-radius: 14px !important;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.045);
        min-height: 0;
        overflow: hidden;
    }
    .balcao-workspace .fechamento-section h3 {
        font-size: 0.82rem;
        margin-bottom: 10px;
        padding-bottom: 7px;
        border-bottom: 1px solid #eef2f7;
        color: #0f172a;
        letter-spacing: 0.01em;
    }
    .balcao-workspace .fechamento-footer {
        margin-top: 10px !important;
        padding-top: 10px !important;
        border-top: 1px solid #eef2f7;
        flex: 0 0 auto;
    }
    .balcao-form-group { margin-bottom: 8px; display: flex; flex-direction: column; gap: 3px; }
    .balcao-form-group label { font-size: 0.66rem; font-weight: 700; opacity: 0.68; text-transform: uppercase; letter-spacing: 0.55px; }
    .balcao-input {
        background: #fbfdff !important; border: 1px solid #d8e1ec !important; color: #1f2937 !important;
        padding: 7px 10px !important; border-radius: 9px !important; outline: none !important;
        font-size: 0.8rem !important; transition: border-color 0.2s, box-shadow 0.2s, background 0.2s !important;
        width: 100% !important; box-sizing: border-box !important;
    }
    .balcao-input:focus { border-color: #ff4b2b !important; background: #ffffff !important; box-shadow: 0 0 0 3px rgba(255,75,43,0.1) !important; }
    textarea.balcao-input { font-family: inherit; }
    .balcao-input option { background-color: #ffffff !important; color: #2d3748 !important; }
    .balcao-workspace .balcao-tabs {
        margin-bottom: 8px !important;
        background: #f1f5f9 !important;
        border: 1px solid #e6edf5;
        border-radius: 12px !important;
        padding: 4px !important;
    }
    .balcao-workspace .balcao-tab-btn {
        padding: 7px !important;
        font-size: 0.72rem !important;
        border-radius: 9px !important;
    }
    .balcao-workspace #balcaoSecaoProdutos,
    .balcao-workspace #balcaoSecaoMontar {
        padding: 10px !important;
        margin-bottom: 9px !important;
        background: #fbfdff !important;
        border: 1px solid #e6edf5 !important;
        border-radius: 13px !important;
        color: #1f2937 !important;
    }
    .balcao-workspace #balcaoSecaoMontar label, .balcao-workspace #balcaoSecaoMontar span { font-size: 0.68rem !important; }
    .balcao-workspace #containerSaboresMontar {
        max-height: min(16vh, 118px);
        overflow-y: auto;
    }
    .balcao-workspace #balcaoSecaoMontar > div {
        margin-bottom: 8px !important;
    }
    .balcao-sabor-picker {
        background: #ffffff;
        border: 1px solid #e6edf5;
        border-radius: 13px;
        padding: 9px;
        margin-bottom: 10px !important;
    }
    .balcao-sabor-picker-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 7px;
    }
    .balcao-sabor-picker-head span {
        font-size: 0.74rem !important;
        font-weight: 800;
        color: #1f2937;
        text-transform: uppercase;
        letter-spacing: 0.45px;
    }
    .balcao-sabor-picker-head small {
        color: #f1592a;
        font-size: 0.7rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .balcao-sabor-add {
        display: grid;
        grid-template-columns: minmax(120px, 0.95fr) minmax(180px, 1.35fr) auto;
        gap: 7px;
        align-items: center;
        margin-bottom: 8px;
    }
    .balcao-sabor-add .balcao-input {
        margin: 0 !important;
    }
    .balcao-sabor-add button {
        border: none;
        background: #f1592a;
        color: #fff;
        border-radius: 9px;
        padding: 8px 12px;
        font-size: 0.76rem;
        font-weight: 800;
        cursor: pointer;
        min-width: 62px;
    }
    .balcao-sabores-selecionados {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        min-height: 34px;
        align-content: flex-start;
    }
    .balcao-sabor-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        max-width: 100%;
        background: #fff7ed;
        border: 1px solid rgba(241, 89, 42, 0.22);
        color: #9a3412;
        border-radius: 999px;
        padding: 5px 8px 5px 10px;
        font-size: 0.74rem;
        font-weight: 700;
        line-height: 1.15;
    }
    .balcao-sabor-chip button {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: none;
        background: rgba(154, 52, 18, 0.12);
        color: #9a3412;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        cursor: pointer;
        font-size: 0.8rem;
        line-height: 1;
    }
    .balcao-sabor-empty {
        color: #94a3b8;
        font-size: 0.76rem;
        padding: 7px 0;
    }
    .balcao-workspace .fechamento-table { font-size: 0.74rem !important; }
    .balcao-workspace .fechamento-table th, .balcao-workspace .fechamento-table td { padding: 6px 7px !important; }
    .balcao-workspace .fechamento-section[style*="flex-direction: column"] > div[style*="max-height: 180px"] {
        max-height: min(19vh, 150px) !important;
        background: #ffffff !important;
        border-color: #e6edf5 !important;
        border-radius: 13px !important;
    }
    .balcao-workspace .fechamento-section[style*="flex-direction: column"] > div[style*="border-top"] {
        background: #f8fafc;
        border: 1px solid #e6edf5 !important;
        border-radius: 13px;
        padding: 12px 14px 14px !important;
        margin-bottom: 10px;
    }
    .balcao-workspace #balcaoTotalGeral {
        color: #f1592a !important;
        line-height: 1.1;
        display: inline-block;
        padding-bottom: 2px;
    }
    .balcao-workspace #balcaoTotalGeral,
    .balcao-workspace #balcaoTotalGeral + * {
        overflow: visible;
    }
    .balcao-workspace .fechamento-section[style*="flex-direction: column"] > div[style*="border-top"] > div:last-child {
        align-items: center;
        font-size: 1.12rem !important;
        line-height: 1.15;
        padding-top: 10px !important;
        margin-top: 2px;
    }
    .balcao-workspace .btn-fechamento-fechar,
    .balcao-workspace button {
        white-space: normal;
    }
    .balcao-workspace button[type="submit"] {
        background: linear-gradient(135deg, #f1592a, #ff6a3d) !important;
        border: none !important;
        border-radius: 10px !important;
        box-shadow: 0 8px 18px rgba(241, 89, 42, 0.22) !important;
    }
    .balcao-workspace #balcaoBtnFechar {
        border-radius: 10px !important;
        background: #f8fafc;
        border-color: #d8e1ec;
    }
    @media (max-width: 1180px) {
        .balcao-workspace .fechamento-body-grid { grid-template-columns: 1fr !important; }
    }
    @media (max-width: 768px) {
        .balcao-workspace .fechamento-content { padding: 12px !important; }
        .balcao-workspace .fechamento-header { align-items: flex-start; gap: 8px; }
        .balcao-workspace .fechamento-title { font-size: 1rem; line-height: 1.25; }
        .balcao-workspace .fechamento-section { padding: 12px; }
        .balcao-workspace .fechamento-footer {
            align-items: stretch;
            flex-direction: column;
            gap: 10px;
        }
        .balcao-workspace .fechamento-footer > span { max-width: none !important; }
        .balcao-workspace .fechamento-footer > div { width: 100%; flex-direction: column; }
        .balcao-workspace .fechamento-footer button { width: 100%; }
    }
  </style>

  <!-- LOGICA JAVASCRIPT DO BALCAO -->
  <script>
    const catalogoProdutos = <?php echo json_encode($produtosCatalogo, JSON_UNESCAPED_UNICODE); ?>;
    let carrinhoBalcao = [];
    let saboresMontarSelecionados = [];

    // Referencias das telas (workspace inline, nao modal flutuante)
    const balcaoWorkspace  = document.getElementById('balcaoWorkspace');
    const dashboardView    = document.getElementById('pdvDashboardView');
    const mainContentArea  = document.querySelector('.main-content');
    const closeBalcaoX     = document.getElementById('balcaoClose');
    const closeBalcaoBtn   = document.getElementById('balcaoBtnFechar');

    function limparVendaBalcao() {
        carrinhoBalcao = [];
        document.getElementById('formPedidoBalcao').reset();
        saboresMontarSelecionados = [];
        toggleBalcaoEndereco('retirada');
        switchBalcaoTab('montar');
        ajustarMontarSabores(document.getElementById('montarTamanho').value);
        renderizarCatalogoBalcao();
        renderizarCarrinhoBalcao();
    }

    function mostrarTelaBalcao() {
        limparVendaBalcao();
        dashboardView.style.display = 'none';
        mainContentArea.style.display = 'none';
        balcaoWorkspace.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.getElementById('btnNovoPedidoBalcao').addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        try {
            mostrarTelaBalcao();
        } catch(err) {
            console.error(err);
            alert('Nao foi possivel abrir a venda de balcao. Atualize a pagina e tente novamente.');
        }
    });

    function voltarDashboardBalcao() {
        balcaoWorkspace.style.display = 'none';
        mainContentArea.style.display = 'block';
        dashboardView.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function fecharBalcaoModal() {
        voltarDashboardBalcao();
    }

    function confirmarFecharBalcao() {
        if (carrinhoBalcao.length > 0 && !confirm('Voce possui itens no carrinho. Deseja realmente sair e cancelar esta venda?')) {
            return;
        }

        limparVendaBalcao();
        voltarDashboardBalcao();
    }

    closeBalcaoX.addEventListener('click', confirmarFecharBalcao);
    closeBalcaoBtn.addEventListener('click', confirmarFecharBalcao);

    // Alternancia de abas (Montar Pizza vs Produtos e Bebidas)
    function switchBalcaoTab(tab) {
        const btnProds  = document.getElementById('btnTabProdutos');
        const btnMontar = document.getElementById('btnTabMontar');
        const divProds  = document.getElementById('balcaoSecaoProdutos');
        const divMontar = document.getElementById('balcaoSecaoMontar');

        if (tab === 'produtos') {
            btnProds.classList.add('active');
            btnProds.style.background = '#ffffff'; btnProds.style.color = '#2d3748'; btnProds.style.boxShadow = '0 2px 4px rgba(0,0,0,0.04)';
            btnMontar.classList.remove('active');
            btnMontar.style.background = 'transparent'; btnMontar.style.color = '#718096'; btnMontar.style.boxShadow = 'none';
            divProds.style.display = 'block';
            divMontar.style.display = 'none';
        } else {
            btnMontar.classList.add('active');
            btnMontar.style.background = '#ffffff'; btnMontar.style.color = '#2d3748'; btnMontar.style.boxShadow = '0 2px 4px rgba(0,0,0,0.04)';
            btnProds.classList.remove('active');
            btnProds.style.background = 'transparent'; btnProds.style.color = '#718096'; btnProds.style.boxShadow = 'none';
            divProds.style.display = 'none';
            divMontar.style.display = 'block';
            ajustarMontarSabores(document.getElementById('montarTamanho').value);
        }
    }

    // Catalogo de produtos normais (Bebidas, etc.)
    function renderizarCatalogoBalcao(filtro = '') {
        const select = document.getElementById('balcaoSelectProd');
        select.innerHTML = '';
        const filtroNorm = filtro.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g,"").replace(/-/g," ");
        let count = 0;
        catalogoProdutos.forEach(p => {
            if (p.categoria === 'pizza' || p.categoria === 'borda') return;
            const nomeNorm = p.nome.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g,"").replace(/-/g," ");
            if (nomeNorm.includes(filtroNorm)) {
                const precoFmt = p.preco.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                select.innerHTML += `<option value="${p.id}">${p.nome} - (${precoFmt})</option>`;
                count++;
            }
        });
        if (count === 0) select.innerHTML = `<option value="" disabled>Nenhum produto encontrado...</option>`;
    }

    function filtrarProdutosBalcao(valor) { renderizarCatalogoBalcao(valor); }

    function normalizarBuscaBalcao(valor) {
        return (valor || '').toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g,"").replace(/-/g," ");
    }

    function getMaxSaboresMontar() {
        const selectTamanho = document.getElementById('montarTamanho');
        const opt = selectTamanho.options[selectTamanho.selectedIndex];
        return parseInt(opt.getAttribute('data-max')) || 1;
    }

    // Ajusta o seletor dinamico de sabores conforme o tamanho
    function ajustarMontarSabores(tamanho) {
        const maxSabores = getMaxSaboresMontar();
        if (saboresMontarSelecionados.length > maxSabores) {
            saboresMontarSelecionados = saboresMontarSelecionados.slice(0, maxSabores);
        }
        filtrarSaboresMontar(document.getElementById('montarBuscaSabor')?.value || '');
        renderizarSaboresMontarSelecionados();
    }

    function filtrarSaboresMontar(termo = '') {
        const select = document.getElementById('montarSelectSabor');
        if (!select) return;
        const filtroNorm = normalizarBuscaBalcao(termo);
        const pizzas = catalogoProdutos.filter(p => p.categoria === 'pizza');
        select.innerHTML = '<option value="">(Selecione um sabor...)</option>';
        let count = 0;
        pizzas.forEach(p => {
            const nomeNorm = normalizarBuscaBalcao(p.nome);
            if (nomeNorm.includes(filtroNorm)) {
                select.innerHTML += `<option value="${p.id}" data-price="${p.preco}" data-nome="${p.nome}">${p.nome}</option>`;
                count++;
            }
        });
        if (count === 0) select.innerHTML = `<option value="" disabled>Nenhum sabor encontrado...</option>`;
    }

    function renderizarSaboresMontarSelecionados() {
        const container = document.getElementById('containerSaboresMontar');
        const limite = document.getElementById('montarLimiteSabores');
        const maxSabores = getMaxSaboresMontar();
        if (limite) limite.innerText = `${saboresMontarSelecionados.length}/${maxSabores} selecionados`;

        if (!container) return;
        if (saboresMontarSelecionados.length === 0) {
            container.innerHTML = '<div class="balcao-sabor-empty">Nenhum sabor adicionado ainda.</div>';
            return;
        }
        container.innerHTML = saboresMontarSelecionados.map((sabor, index) => `
            <span class="balcao-sabor-chip">
                ${index + 1}. ${sabor.nome}
                <button type="button" onclick="removerSaborMontar(${index})" title="Remover sabor">&times;</button>
            </span>
        `).join('');
    }

    function adicionarSaborMontar() {
        const maxSabores = getMaxSaboresMontar();
        if (saboresMontarSelecionados.length >= maxSabores) {
            alert(`Este tamanho permite no maximo ${maxSabores} sabor(es).`);
            return;
        }

        const select = document.getElementById('montarSelectSabor');
        if (!select || !select.value) {
            alert('Selecione um sabor para adicionar.');
            return;
        }

        const opt = select.options[select.selectedIndex];
        saboresMontarSelecionados.push({
            id: parseInt(select.value),
            nome: opt.getAttribute('data-nome') || opt.text
        });

        document.getElementById('montarBuscaSabor').value = '';
        filtrarSaboresMontar('');
        renderizarSaboresMontarSelecionados();
    }

    function removerSaborMontar(index) {
        saboresMontarSelecionados.splice(index, 1);
        renderizarSaboresMontarSelecionados();
    }

    // Adicionar produto normal ao carrinho
    function adicionarItemBalcao() {
        const select = document.getElementById('balcaoSelectProd');
        const qtdInput = document.getElementById('balcaoQtdProd');
        const prodId = parseInt(select.value);
        const qtd = parseInt(qtdInput.value);
        if (!prodId || qtd <= 0) { alert('Selecione um produto valido e defina a quantidade.'); return; }
        const produto = catalogoProdutos.find(p => p.id === prodId);
        if (!produto) return;
        const itemChave = prodId.toString();
        const itemExistente = carrinhoBalcao.find(item => item.item_chave === itemChave);
        if (itemExistente) {
            itemExistente.quantidade += qtd;
        } else {
            carrinhoBalcao.push({ item_chave: itemChave, produto_id: produto.id, nome: produto.nome, preco: produto.preco, quantidade: qtd, detalhes: 'Venda de Balcao' });
        }
        qtdInput.value = 1;
        document.getElementById('balcaoPesquisaProd').value = '';
        renderizarCatalogoBalcao();
        renderizarCarrinhoBalcao();
    }

    // Adicionar pizza montada ao carrinho
    function adicionarPizzaMontadaBalcao() {
        const selectTamanho = document.getElementById('montarTamanho');
        const optTamanho = selectTamanho.options[selectTamanho.selectedIndex];
        const tamanho = selectTamanho.value;
        const precoBase = parseFloat(optTamanho.getAttribute('data-price'));

        const selectBorda = document.getElementById('montarBorda');
        const borda = selectBorda.value;
        const precoBorda = parseFloat(selectBorda.options[selectBorda.selectedIndex].getAttribute('data-price'));

        const saboresNomes = saboresMontarSelecionados.map(sabor => sabor.nome);

        if (saboresNomes.length === 0) { alert('Selecione ao menos um sabor de pizza.'); return; }

        let saboresTexto = '';
        if (saboresNomes.length === 1) {
            saboresTexto = saboresNomes[0];
        } else {
            const todos = saboresNomes.every(s => s === saboresNomes[0]);
            saboresTexto = todos ? saboresNomes[0] : saboresNomes.map(sabor => `1/${saboresNomes.length} ${sabor}`).join(' + ');
        }

        const qtd = parseInt(document.getElementById('montarQtd').value) || 1;
        const precoTotal = precoBase + precoBorda;
        const detalhes = `Tamanho: ${tamanho} | Sabores: ${saboresTexto} | Borda: ${borda}`;
        const itemChave = "custom_" + detalhes.toLowerCase().replace(/[^a-z0-9]/g, '');

        const itemExistente = carrinhoBalcao.find(item => item.item_chave === itemChave);
        if (itemExistente) {
            itemExistente.quantidade += qtd;
        } else {
            carrinhoBalcao.push({ item_chave: itemChave, produto_id: 0, nome: `Pizza ${tamanho} Personalizada`, preco: precoTotal, quantidade: qtd, detalhes: detalhes });
        }

        document.getElementById('montarQtd').value = 1;
        saboresMontarSelecionados = [];
        filtrarSaboresMontar('');
        renderizarSaboresMontarSelecionados();
        renderizarCarrinhoBalcao();
        switchBalcaoTab('produtos');
    }

    function removerItemBalcao(itemChave) {
        carrinhoBalcao = carrinhoBalcao.filter(item => item.item_chave !== itemChave);
        renderizarCarrinhoBalcao();
    }

    function recalcularTotalBalcao() { renderizarCarrinhoBalcao(); }

    function renderizarCarrinhoBalcao() {
        const tbody = document.getElementById('balcaoCarrinhoRows');
        tbody.innerHTML = '';
        let subtotal = 0;

        if (carrinhoBalcao.length > 0) {
            carrinhoBalcao.forEach(item => {
                const sub = item.preco * item.quantidade;
                subtotal += sub;
                const precoFmt = item.preco.toLocaleString('pt-BR', { style:'currency', currency:'BRL' });
                const subFmt = sub.toLocaleString('pt-BR', { style:'currency', currency:'BRL' });
                const descricao = item.produto_id === 0 ? `<br><small style="color:#718096;font-weight:normal;">${item.detalhes}</small>` : '';
                tbody.innerHTML += `
                    <tr>
                        <td><strong>${item.nome}</strong>${descricao}</td>
                        <td style="text-align:center;">${item.quantidade}</td>
                        <td style="text-align:right;">${precoFmt}</td>
                        <td style="text-align:right;font-weight:600;color:#ff4b2b;">${subFmt}</td>
                        <td style="text-align:center;">
                            <button type="button" onclick="removerItemBalcao('${item.item_chave}')" style="background:none;border:none;color:#ff6b6b;cursor:pointer;font-size:1.1rem;">
                                <i class="fa fa-trash-o"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;opacity:0.7;padding:20px 0;color:#718096;">Nenhum item adicionado ao carrinho.</td></tr>`;
        }

        document.getElementById('balcaoTotalItens').innerText = subtotal.toLocaleString('pt-BR', { style:'currency', currency:'BRL' });

        const modalidade = document.getElementById('balcaoModalidade').value;
        let taxa = 0;
        if (modalidade === 'entrega') {
            taxa = parseFloat(document.getElementById('balcaoTaxaEntrega').value) || 0;
            document.getElementById('balcaoTotalTaxa').innerText = taxa.toLocaleString('pt-BR', { style:'currency', currency:'BRL' });
            document.getElementById('balcaoLinhaTaxa').style.display = 'flex';
        } else {
            document.getElementById('balcaoLinhaTaxa').style.display = 'none';
        }

        document.getElementById('balcaoTotalGeral').innerText = (subtotal + taxa).toLocaleString('pt-BR', { style:'currency', currency:'BRL' });
    }

    function toggleBalcaoEndereco(val) {
        const campos = document.getElementById('balcaoCamposEntrega');
        const inputEnd = document.getElementById('balcaoEndereco');
        if (val === 'entrega') {
            campos.style.display = 'block';
            inputEnd.required = true;
        } else {
            campos.style.display = 'none';
            inputEnd.required = false;
            inputEnd.value = '';
            document.getElementById('balcaoTaxaEntrega').value = '0.00';
        }
        renderizarCarrinhoBalcao();
    }

    // Registrar venda via AJAX
    function registrarVendaBalcao() {
        if (carrinhoBalcao.length === 0) { alert('O carrinho esta vazio. Adicione pelo menos um produto.'); return; }

        const nome = document.getElementById('balcaoClienteNome').value.trim();
        const tel  = document.getElementById('balcaoClienteTel').value.trim();
        const modalidade = document.getElementById('balcaoModalidade').value;
        const endereco   = document.getElementById('balcaoEndereco').value.trim();
        const taxa       = parseFloat(document.getElementById('balcaoTaxaEntrega').value) || 0;
        const formaPgto  = document.getElementById('balcaoFormaPagamento').value;

        const payload = { nome, telefone: tel, tipo_entrega: modalidade, endereco: modalidade === 'entrega' ? endereco : '', taxa_entrega: modalidade === 'entrega' ? taxa : 0, forma_pagamento: formaPgto, itens: carrinhoBalcao };

        const btnSubmit = document.querySelector('#formPedidoBalcao button[type="submit"]');
        const origText = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Registrando...';

        fetch('sheep-filtros/salvar_pedido_balcao_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.sucesso) {
                const bipe = new Audio('assets/audio/sino.wav');
                bipe.play().catch(e => console.log('Audio bloqueado', e));
                window.open(`imprimir.php?id=${data.pedido_id}`, '_blank');

                limparVendaBalcao();
                voltarDashboardBalcao();
                checarNovosPedidos(true);

                // Toast de sucesso
                const toast = document.createElement('div');
                toast.style.cssText = 'position:fixed;bottom:30px;right:30px;background:#2ebd59;color:white;padding:16px 24px;border-radius:10px;font-weight:bold;font-size:1rem;z-index:99999;box-shadow:0 4px 20px rgba(46,189,89,0.3);';
                toast.innerHTML = `<i class="fa fa-check-circle"></i> Pedido #${data.pedido_id} registrado com sucesso!`;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 4000);
            } else {
                alert('Erro ao registrar: ' + data.erro);
            }
        })
        .catch(err => { console.error(err); alert('Erro de comunicação com o servidor.'); })
        .finally(() => { btnSubmit.disabled = false; btnSubmit.innerHTML = origText; });
    }
  </script>

<?php
// Dashboard Modelo Clean
?>
