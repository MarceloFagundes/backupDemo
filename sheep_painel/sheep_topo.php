<?php
require_once("sheep_checa.php");
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title><?= SHEEP_TITULO_PAINEL ?></title>
  <!-- Sheep CSS -->
  <?php require_once('sheep_css.php') ?>
  <link rel='shortcut icon' type='image/x-icon' href='<?= SHEEP_ICONE ?>' />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

  <div id="app">
    <div class="main-wrapper">
      
      <!--MENU LATERAL-->
      <?php include_once('sheep_menu.php'); ?>
      
      <!-- CONTAINER DO CONTEÚDO PRINCIPAL -->
      <div class="main-content-wrapper">
        
        <header class="main-navbar">
           <div class="nav-left">
              <button class="btn-menu-toggle" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#666;"><i class="fa fa-bars"></i></button>
           </div>
           
           <div class="nav-right" style="display:flex; align-items:center;">
              <div class="user-profile" style="display:flex; align-items:center; gap: 10px;">
                 <img alt="" src="assets/img/sem-imagem.png" style="width:35px; height:35px; border-radius:50%; object-fit:cover;">
                 <span style="font-weight:600; color:#333;"><?= htmlspecialchars($_SESSION['sheep_user']['nome'] ?? 'Administrador') ?></span>
                 <a href="sheep.php?sair=true" style="color:var(--danger); text-decoration:none; margin-left:15px; font-weight:bold;"><i class="fa fa-sign-out"></i> Sair</a>
              </div>
           </div>
        </header>
        
        <script>
          (function () {
            if (window.imprimirPedidoAutomatico) return;

            const PRINT_STORAGE_KEY = 'mondini_auto_printed_orders';
            const PRINT_TTL_MS = 6 * 60 * 60 * 1000;

            function getPrintedMap() {
              try {
                const parsed = JSON.parse(localStorage.getItem(PRINT_STORAGE_KEY) || '{}');
                const now = Date.now();
                Object.keys(parsed).forEach(id => {
                  if (!parsed[id] || now - parsed[id] > PRINT_TTL_MS) {
                    delete parsed[id];
                  }
                });
                localStorage.setItem(PRINT_STORAGE_KEY, JSON.stringify(parsed));
                return parsed;
              } catch (e) {
                return {};
              }
            }

            function markPrinted(id) {
              const printed = getPrintedMap();
              printed[id] = Date.now();
              localStorage.setItem(PRINT_STORAGE_KEY, JSON.stringify(printed));
            }

            window.imprimirPedidoAutomatico = function (id) {
              if (!id) return;

              const printed = getPrintedMap();
              if (printed[id]) return;
              markPrinted(id);

              const frame = document.createElement('iframe');
              frame.setAttribute('aria-hidden', 'true');
              frame.src = 'imprimir.php?id=' + encodeURIComponent(id) + '&auto=1&t=' + Date.now();
              frame.style.position = 'fixed';
              frame.style.right = '0';
              frame.style.bottom = '0';
              frame.style.width = '1px';
              frame.style.height = '1px';
              frame.style.border = '0';
              frame.style.opacity = '0.01';
              frame.style.pointerEvents = 'none';
              frame.style.zIndex = '-1';

              frame.onload = function () {
                setTimeout(function () {
                  try {
                    frame.contentWindow.focus();
                    frame.contentWindow.print();
                  } catch (e) {
                    console.error('Falha ao imprimir pedido #' + id, e);
                  }
                }, 350);
              };

              document.body.appendChild(frame);
              setTimeout(function () {
                if (frame.parentNode) frame.parentNode.removeChild(frame);
              }, 30000);
            };
          })();
        </script>


