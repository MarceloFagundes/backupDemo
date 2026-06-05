      <footer class="main-footer" style="padding: 16px 24px; text-align: center; color: var(--text-muted); font-size: 0.78rem; border-top: 1px solid var(--border-light); margin-top: auto; background: var(--bg-card);">
        <?= date('d/m/Y') ?> &mdash; <?= SHEEP_RODAPE_PAINEL ?> &mdash; <a href="#" style="color: var(--primary); font-weight: 600;"><?= SHEEP_VERSAO ?></a> &mdash; <a href="#" id="stReplayTour" style="color: var(--primary); font-weight: 600;">Rever tour</a>
      </footer>
    </div> <!-- fecha .main-content-wrapper -->
  </div> <!-- fecha .main-wrapper -->
</div> <!-- fecha #app -->

<?php
  require_once ("sheep_js.php");

  if (strpos($_SERVER['REQUEST_URI'] ?? '', 'permissao_negada') !== false):
?>
<!-- MODAL PERMISSAO NEGADA (MODO DEMONSTRACAO) -->
<div class="modal fade" id="modal_permissao_negada" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document" style="max-width:340px;">
    <div class="modal-content" style="border:none; border-radius:12px; overflow:hidden;">
      <div style="background:#e67e00; padding: 28px 30px 20px; text-align:center; color:#fff;">
        <div style="font-size: 3rem; margin-bottom: 10px;"><i class="fa fa-lock"></i></div>
        <h4 style="margin:0; font-weight:800; color:#fff;">Voce nao tem permissao para fazer isso.</h4>
      </div>
      <div style="padding: 20px 30px 28px; text-align:center; background:#fff;">
        <p style="color:#555; font-size:0.95rem; margin-bottom: 20px;">
          Esta acao esta <strong>desativada no ambiente de demonstracao</strong> para proteger os dados de teste.<br>
          No sistema real do seu negocio, voce tera controle total.
        </p>
        <button type="button" class="btn btn-warning" data-dismiss="modal" style="font-weight:700; padding:10px 28px;">Entendi</button>
      </div>
    </div>
  </div>
</div>
<script>
$(document).ready(function() {
    $('#modal_permissao_negada').modal('show');
});
</script>
<?php endif; ?>

<!-- TOUR GUIADO - PRIMEIRA VISITA -->
<style>
#st-wrap { position:fixed; inset:0; display:none; pointer-events:none; }
#st-bg { position:absolute; inset:0; z-index:99999; background:rgba(15,23,42,0.72); pointer-events:auto; }
#st-box {
  position:fixed; z-index:100003; background:#fff; border-radius:14px;
  padding:24px 26px 20px; width:390px; max-width:calc(100vw - 28px);
  box-shadow:0 18px 50px rgba(0,0,0,0.28);
  font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
  pointer-events:auto;
}
#st-box .st-badge { display:inline-block; background:#e8f6fd; color:#009EDB; font-size:11px; font-weight:800; padding:4px 10px; border-radius:20px; margin-bottom:13px; letter-spacing:.02em; }
#st-box .st-icon { width:38px; height:38px; border-radius:50%; background:#009EDB; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; margin-bottom:13px; }
#st-box h3 { font-size:18px; font-weight:800; color:#111827; margin:0 0 8px; }
#st-box p { font-size:13px; color:#4b5563; line-height:1.6; margin:0 0 18px; }
#st-box .st-steps { display:flex; gap:5px; margin-bottom:18px; }
#st-box .st-step { flex:1; height:4px; border-radius:2px; background:#e5e7eb; transition:background .2s; }
#st-box .st-step.done { background:#009EDB; }
#st-box .st-btns { display:flex; justify-content:space-between; align-items:center; gap:12px; }
#st-box .st-left, #st-box .st-right { display:flex; align-items:center; gap:8px; }
#st-box button { border:0; cursor:pointer; font-size:13px; border-radius:8px; font-weight:700; }
#st-box .st-skip { background:none; color:#9ca3af; text-decoration:underline; padding:0; font-weight:600; }
#st-box .st-prev { background:#eef2f7; color:#4b5563; padding:9px 14px; }
#st-box .st-next { background:#009EDB; color:#fff; padding:9px 18px; }
#st-box .st-next:hover { background:#006A9C; }
.st-highlight { position:relative !important; z-index:100001 !important; border-radius:8px !important; box-shadow:0 0 0 4px rgba(0,158,219,.38), 0 12px 32px rgba(0,0,0,.25) !important; }
.main-sidebar.st-tour-sidebar { z-index:100000 !important; }
.main-sidebar.st-tour-sidebar .sidebar-brand,
.main-sidebar.st-tour-sidebar .sidebar-menu > li { opacity:.34; transition:opacity .18s ease; }
.main-sidebar.st-tour-sidebar .sidebar-menu > li.st-menu-highlight { opacity:1; position:relative; z-index:100001; }
.main-sidebar.st-tour-sidebar .sidebar-menu > li.st-menu-highlight > a.st-highlight {
  background:#fff !important; color:#111827 !important;
  box-shadow:0 0 0 4px rgba(0,158,219,.72), 0 14px 34px rgba(0,0,0,.32) !important;
}
.main-sidebar.st-tour-sidebar .sidebar-menu > li.st-menu-highlight > a.st-highlight i,
.main-sidebar.st-tour-sidebar .sidebar-menu > li.st-menu-highlight > a.st-highlight span { color:#009EDB !important; }
@media(max-width: 768px) {
  #st-box { left:14px !important; right:14px !important; bottom:14px !important; top:auto !important; width:auto; transform:none !important; }
}
</style>

<div id="st-wrap" aria-hidden="true">
  <div id="st-bg"></div>
  <div id="st-box" role="dialog" aria-modal="true" aria-labelledby="stTitle">
    <div class="st-badge" id="stBadge">PASSO 1</div>
    <div class="st-icon" id="stIcon">1</div>
    <h3 id="stTitle"></h3>
    <p id="stDesc"></p>
    <div class="st-steps" id="stSteps"></div>
    <div class="st-btns">
      <div class="st-left">
        <button type="button" class="st-skip" id="stSkip">Pular tour</button>
      </div>
      <div class="st-right">
        <button type="button" class="st-prev" id="stPrev">Voltar</button>
        <button type="button" class="st-next" id="stNext">Proximo</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  var KEY = 'sheep_tour_v3_done';
  var passos = [
    { titulo:'Roteiro do primeiro acesso', desc:'Comece configurando dados, entrega e cardapio. Depois abra a loja e acompanhe os pedidos pelo Dashboard.', target:null },
    { titulo:'Dados da pizzaria', desc:'Confira nome, telefone, endereco e informacoes de contato. Esses dados aparecem para o cliente e evitam pedido com referencia errada.', target:'a[href*="sheep-dados/index"]' },
    { titulo:'Aparencia e banners', desc:'Suba logo, favicon e banners antes de divulgar o link. Isso faz o site parecer seu, nao uma instalacao generica.', target:'a[href*="sheep-dados/aparencia"]' },
    { titulo:'Bairros e taxas', desc:'Cadastre as regioes atendidas, valores de entrega e regras de retirada. Sem isso, o cliente pode travar no fechamento do pedido.', target:'a[href*="sheep-bairros/index"]' },
    { titulo:'Cardapio', desc:'Revise produtos, fotos, precos e categorias. O ideal e testar uma pizza, uma bebida e um combo antes de abrir para pedidos reais.', target:'a[href*="sheep-cardapio/index"]' },
    { titulo:'Abrir ou fechar a loja', desc:'Use este controle no inicio e no fim do expediente. Ao fechar, o site bloqueia novos pedidos sem derrubar o painel.', target:'#btnToggleLoja' },
    { titulo:'Pedidos em tempo real', desc:'Os pedidos aparecem na lista, e os detalhes ficam ao lado. Atualize o status para organizar cozinha, entrega e comunicacao com o cliente.', target:'.pdv-order-list' },
    { titulo:'Pedido de balcao', desc:'Use quando alguem pedir por telefone, WhatsApp ou presencialmente. Assim a venda entra no mesmo fluxo dos pedidos online.', target:'#btnNovoPedidoBalcao' },
    { titulo:'Fechamento de caixa', desc:'No fim do dia, gere o resumo por canal: site, iFood e balcao. Isso ajuda a conferir faturamento sem garimpar pedido por pedido.', target:'#btnFechamentoCaixa' },
    { titulo:'Historico, equipe e integracoes', desc:'Historico mostra filtros e relatorios. Administradores tambem cuidam de operadores, MercadoPago, iFood, cashback e fiscal pelo menu.', target:'a[href*="sheep-pedidos/index"], a[href*="ifood_setup"], a[href*="sheep-dados/pagamentos"]' }
  ];

  var atual = 0;
  var aberto = false;
  var alvoAtivo = null;
  var itemMenuAtivo = null;
  var sidebarAtivo = null;
  var wrap = document.getElementById('st-wrap');
  var box = document.getElementById('st-box');
  var badge = document.getElementById('stBadge');
  var icon = document.getElementById('stIcon');
  var title = document.getElementById('stTitle');
  var desc = document.getElementById('stDesc');
  var steps = document.getElementById('stSteps');
  var btnNext = document.getElementById('stNext');
  var btnPrev = document.getElementById('stPrev');
  var btnSkip = document.getElementById('stSkip');
  var replay = document.getElementById('stReplayTour');

  function storageGet() { try { return localStorage.getItem(KEY); } catch(e) { return null; } }
  function storageSet() { try { localStorage.setItem(KEY, '1'); } catch(e) {} }
  function storageClear() { try { localStorage.removeItem(KEY); } catch(e) {} }
  function selecionarTarget(selector) { try { return selector ? document.querySelector(selector) : null; } catch(e) { return null; } }
  function limparAlvo() {
    if (alvoAtivo) alvoAtivo.classList.remove('st-highlight');
    if (itemMenuAtivo) itemMenuAtivo.classList.remove('st-menu-highlight');
    if (sidebarAtivo) sidebarAtivo.classList.remove('st-tour-sidebar');
    alvoAtivo = null;
    itemMenuAtivo = null;
    sidebarAtivo = null;
  }
  function limitar(valor, min, max) {
    return Math.max(min, Math.min(max, valor));
  }
  function posicionar(target) {
    if (!target || window.innerWidth <= 768) {
      box.style.left = '50%';
      box.style.top = '50%';
      box.style.right = 'auto';
      box.style.bottom = 'auto';
      box.style.transform = 'translate(-50%, -50%)';
      return;
    }

    var gap = 16;
    var rect = target.getBoundingClientRect();
    var largura = box.offsetWidth || 390;
    var altura = box.offsetHeight || 260;
    var x = rect.right + gap;
    if (x + largura > window.innerWidth - gap) x = rect.left - largura - gap;
    if (x < gap) x = limitar(rect.left + (rect.width / 2) - (largura / 2), gap, window.innerWidth - largura - gap);

    var y = limitar(rect.top + (rect.height / 2) - (altura / 2), gap, window.innerHeight - altura - gap);
    box.style.left = x + 'px';
    box.style.top = y + 'px';
    box.style.right = 'auto';
    box.style.bottom = 'auto';
    box.style.transform = 'none';
  }
  function montarBarras() {
    steps.innerHTML = '';
    passos.forEach(function(_, i) {
      var barra = document.createElement('div');
      barra.className = 'st-step';
      if (i <= atual) barra.classList.add('done');
      steps.appendChild(barra);
    });
  }
  function mostrar(i) {
    atual = i;
    limparAlvo();
    var passo = passos[atual];
    badge.textContent = 'PASSO ' + (atual + 1) + ' DE ' + passos.length;
    icon.textContent = String(atual + 1);
    title.textContent = passo.titulo;
    desc.textContent = passo.desc;
    btnPrev.style.visibility = atual === 0 ? 'hidden' : 'visible';
    btnNext.textContent = atual === passos.length - 1 ? 'Concluir' : 'Proximo';
    montarBarras();
    wrap.style.display = 'block';
    wrap.setAttribute('aria-hidden', 'false');
    aberto = true;

    var target = selecionarTarget(passo.target);
    if (target) {
      alvoAtivo = target;
      target.classList.add('st-highlight');
      sidebarAtivo = target.closest ? target.closest('.main-sidebar') : null;
      if (sidebarAtivo) {
        sidebarAtivo.classList.add('st-tour-sidebar');
        itemMenuAtivo = target.closest('li');
        if (itemMenuAtivo) itemMenuAtivo.classList.add('st-menu-highlight');
      }
      target.scrollIntoView({ behavior:'smooth', block:'center', inline:'center' });
      setTimeout(function(){ posicionar(target); }, 260);
    } else {
      posicionar(null);
    }
  }
  function fechar(salvar) {
    limparAlvo();
    wrap.style.display = 'none';
    wrap.setAttribute('aria-hidden', 'true');
    aberto = false;
    if (salvar !== false) storageSet();
  }
  function iniciar(forcar) {
    if (!forcar && storageGet()) return;
    mostrar(0);
  }

  btnNext.addEventListener('click', function(){
    if (atual >= passos.length - 1) { fechar(true); return; }
    mostrar(atual + 1);
  });
  btnPrev.addEventListener('click', function(){
    if (atual > 0) mostrar(atual - 1);
  });
  btnSkip.addEventListener('click', function(){ fechar(true); });
  document.getElementById('st-bg').addEventListener('click', function(){ fechar(true); });
  window.addEventListener('resize', function(){
    if (aberto) mostrar(atual);
  });
  if (replay) {
    replay.addEventListener('click', function(e){
      e.preventDefault();
      storageClear();
      iniciar(true);
    });
  }
  window.addEventListener('load', function(){
    setTimeout(function(){ iniciar(false); }, 900);
  });
})();
</script>
<!-- FIM TOUR GUIADO -->

</body>
</html>
