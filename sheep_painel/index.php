<?php
ob_start();
$secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
  'lifetime' => 0,
  'path' => '/',
  'secure' => $secureCookie,
  'httponly' => true,
  'samesite' => 'Lax',
]);
session_start();
require('../sheep_core/config.php');
require_once('sheep_top_login.php');

if (empty($_SESSION['login_csrf_token'])) {
  $_SESSION['login_csrf_token'] = bin2hex(random_bytes(32));
}

$camposVazios = filter_input(INPUT_GET, 'campos_vazios', FILTER_VALIDATE_BOOLEAN);
$senhaErrada = filter_input(INPUT_GET, 'senha_errada', FILTER_VALIDATE_BOOLEAN);
$acessoNegado = filter_input(INPUT_GET, 'acesso_negado', FILTER_VALIDATE_BOOLEAN);
$saiu = filter_input(INPUT_GET, 'sheep_saiu', FILTER_VALIDATE_BOOLEAN);
?>

<body class="login-page">
  <style>
    .login-page {
      min-height: 100vh;
      background:
        linear-gradient(135deg, rgba(17, 24, 39, 0.96), rgba(47, 35, 28, 0.94)),
        url('assets/img/fundo-login.jpg');
      background-size: cover;
      background-position: center;
      color: #111827;
    }

    .login-page #app {
      display: block;
      width: 100%;
      min-height: 100vh;
      background: transparent;
    }

    .login-shell {
      min-height: 100vh;
      width: 100%;
      display: grid;
      grid-template-columns: minmax(320px, 0.92fr) minmax(360px, 520px);
      align-items: stretch;
    }

    .login-brand-panel {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 56px;
      color: #ffffff;
      background: rgba(0, 0, 0, 0.16);
    }

    .login-brand-logo {
      width: 92px;
      height: 92px;
      display: grid;
      place-items: center;
      background: #ffffff;
      border-radius: 8px;
      box-shadow: 0 18px 40px rgba(0, 0, 0, 0.24);
      margin-bottom: 28px;
    }

    .login-brand-logo img {
      width: 76px;
      height: 76px;
      object-fit: contain;
    }

    .login-brand-panel h1 {
      max-width: 620px;
      margin: 0;
      font-size: 2.7rem;
      line-height: 1.05;
      font-weight: 800;
      letter-spacing: 0;
      color: #ffffff;
    }

    .login-brand-panel p {
      max-width: 540px;
      margin: 18px 0 0;
      color: rgba(255, 255, 255, 0.78);
      font-size: 1rem;
      line-height: 1.7;
    }

    .login-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 34px;
    }

    .login-meta span {
      padding: 9px 12px;
      border: 1px solid rgba(255, 255, 255, 0.18);
      border-radius: 6px;
      color: rgba(255, 255, 255, 0.78);
      background: rgba(255, 255, 255, 0.06);
      font-size: 0.82rem;
      font-weight: 600;
    }

    .login-form-panel {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px;
      background: #f7f8fa;
    }

    .login-card {
      width: 100%;
      max-width: 440px;
      background: #ffffff;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      box-shadow: 0 24px 70px rgba(17, 24, 39, 0.16);
      padding: 34px;
    }

    .login-card-header {
      margin-bottom: 24px;
    }

    .login-card-header h2 {
      margin: 0;
      color: #111827;
      font-size: 1.55rem;
      font-weight: 800;
      line-height: 1.2;
      letter-spacing: 0;
    }

    .login-card-header p {
      margin: 8px 0 0;
      color: #6b7280;
      font-size: 0.92rem;
      line-height: 1.55;
    }

    .login-alert {
      display: flex;
      gap: 10px;
      padding: 12px 14px;
      border-radius: 8px;
      margin-bottom: 14px;
      font-size: 0.86rem;
      line-height: 1.45;
      border: 1px solid transparent;
    }

    .login-alert strong {
      display: block;
      margin-bottom: 2px;
      font-weight: 800;
    }

    .login-alert.warning {
      background: #fff7ed;
      border-color: #fed7aa;
      color: #9a3412;
    }

    .login-alert.danger {
      background: #fef2f2;
      border-color: #fecaca;
      color: #991b1b;
    }

    .login-alert.success {
      background: #ecfdf5;
      border-color: #a7f3d0;
      color: #065f46;
    }

    .login-form-group {
      margin-bottom: 16px;
    }

    .login-form-group label {
      display: block;
      color: #374151;
      font-size: 0.84rem;
      font-weight: 700;
      margin-bottom: 7px;
    }

    .login-input-wrap {
      position: relative;
    }

    .login-input-wrap i {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
      font-size: 0.95rem;
    }

    .login-card .form-control {
      width: 100%;
      height: 48px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      padding: 0 14px 0 42px;
      font-size: 0.95rem;
      color: #111827;
      background: #ffffff;
      transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .login-card .form-control:focus {
      border-color: #f1592a;
      box-shadow: 0 0 0 4px rgba(241, 89, 42, 0.14);
      outline: none;
    }

    .login-submit {
      width: 100%;
      min-height: 50px;
      border: 0;
      border-radius: 8px;
      background: #f1592a;
      color: #ffffff;
      font-size: 0.95rem;
      font-weight: 800;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 14px 28px rgba(241, 89, 42, 0.22);
      transition: transform 0.18s ease, background 0.18s ease;
    }

    .login-submit:hover {
      background: #d44820;
      transform: translateY(-1px);
    }

    .login-footer {
      margin-top: 22px;
      padding-top: 18px;
      border-top: 1px solid #f3f4f6;
      color: #9ca3af;
      text-align: center;
      font-size: 0.78rem;
      line-height: 1.5;
    }

    @media (max-width: 980px) {
      .login-shell {
        grid-template-columns: 1fr;
      }

      .login-brand-panel {
        padding: 30px 24px 22px;
      }

      .login-brand-panel h1 {
        font-size: 2rem;
      }

      .login-brand-panel p {
        font-size: 0.94rem;
      }

      .login-meta {
        margin-top: 22px;
      }

      .login-form-panel {
        align-items: flex-start;
        padding: 22px;
      }
    }

    @media (max-width: 520px) {
      .login-brand-panel {
        padding: 22px 18px 16px;
      }

      .login-brand-logo {
        width: 72px;
        height: 72px;
        margin-bottom: 18px;
      }

      .login-brand-logo img {
        width: 58px;
        height: 58px;
      }

      .login-brand-panel h1 {
        font-size: 1.55rem;
      }

      .login-meta {
        display: none;
      }

      .login-form-panel {
        padding: 16px;
      }

      .login-card {
        padding: 24px 18px;
      }
    }
  </style>

  <div id="app">
    <main class="login-shell">
      <section class="login-brand-panel">
        <div>
          <div class="login-brand-logo">
            <img src="<?= SHEEP_LOGO ?>" alt="<?= SHEEP_TITULO_PAINEL ?>">
          </div>
          <h1>Painel de controle da sua pizzaria.</h1>
          <p>Acompanhe pedidos, cardapio, entregas e operacao em tempo real com acesso seguro para a equipe.</p>
          <div class="login-meta">
            <span><i class="fa fa-shield"></i> Acesso protegido</span>
            <span><i class="fa fa-clock-o"></i> Operacao em tempo real</span>
            <span><i class="fa fa-cutlery"></i> Delivery e balcao</span>
          </div>
        </div>
      </section>

      <section class="login-form-panel">
        <div class="login-card">
          <!-- BANNER DEMO -->
          <div id="demo-banner" style="margin-bottom: 14px; border-radius: 10px; overflow: hidden; cursor: pointer; box-shadow: 0 3px 12px rgba(0,158,219,0.2);" onclick="preencherLogin()">
            <div style="background: #009EDB; padding: 7px 14px; display: flex; align-items: center; gap: 8px;">
              <span style="background: #fff; color: #009EDB; font-size: 9px; font-weight: 800; padding: 1px 7px; border-radius: 20px; letter-spacing: 0.05em; animation: demoBlink 1s infinite;">● DEMO</span>
              <span style="color: #fff; font-size: 12px; font-weight: 700;">Clique aqui para entrar automaticamente</span>
              <i class="fa fa-hand-pointer-o" style="color: #fff; margin-left: auto; font-size: 15px; animation: demoWave 1s infinite;"></i>
            </div>
            <div style="background: #e8f6fd; border: 2px solid #009EDB; border-top: none; padding: 8px 14px; display: flex; gap: 16px; align-items: center;">
              <div>
                <div style="font-size: 9px; color: #006A9C; margin-bottom: 1px;">LOGIN</div>
                <div style="font-size: 11px; font-weight: 700; color: #0D1F35; font-family: monospace;">demo@admin.com</div>
              </div>
              <div style="width: 1px; background: #b3d9ee; height: 24px;"></div>
              <div>
                <div style="font-size: 9px; color: #006A9C; margin-bottom: 1px;">SENHA</div>
                <div style="font-size: 11px; font-weight: 700; color: #0D1F35; font-family: monospace;">12345</div>
              </div>
              <div style="margin-left: auto; background: #009EDB; color: #fff; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                ENTRAR <i class="fa fa-arrow-right"></i>
              </div>
            </div>
          </div>
          <style>
          @keyframes demoBlink { 0%,100%{opacity:1} 50%{opacity:0.3} }
          @keyframes demoWave  { 0%,100%{transform:translateX(0)} 50%{transform:translateX(4px)} }
          </style>
          <!-- FIM BANNER DEMO -->
          <div class="login-card-header">
            <h2>Entrar no painel</h2>
            <p>Use seu e-mail e senha para acessar a administracao da loja.</p>
          </div>

          <?php if ($camposVazios): ?>
            <div class="login-alert warning">
              <i class="fa fa-exclamation-circle"></i>
              <div><strong>Atencao</strong>Preencha todos os campos para continuar.</div>
            </div>
          <?php endif; ?>

          <?php if ($senhaErrada): ?>
            <div class="login-alert danger">
              <i class="fa fa-times-circle"></i>
              <div><strong>Erro de acesso</strong>E-mail ou senha nao encontrados no sistema.</div>
            </div>
          <?php endif; ?>

          <?php if ($acessoNegado): ?>
            <div class="login-alert warning">
              <i class="fa fa-lock"></i>
              <div><strong>Acesso negado</strong>Esta conta nao tem permissao para acessar o painel administrativo.</div>
            </div>
          <?php endif; ?>

          <?php if ($saiu): ?>
            <div class="login-alert success">
              <i class="fa fa-check-circle"></i>
              <div><strong>Sessao encerrada</strong>Voce saiu do painel com seguranca.</div>
            </div>
          <?php endif; ?>

          <form method="post" action="sheep-filtros/entrar.php" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['login_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="login-form-group">
              <label for="email">E-mail</label>
              <div class="login-input-wrap">
                <i class="fa fa-envelope-o"></i>
                <input id="email" type="email" class="form-control" name="email" tabindex="1" placeholder="Digite seu e-mail" required autofocus>
              </div>
            </div>

            <div class="login-form-group">
              <label for="password">Senha</label>
              <div class="login-input-wrap">
                <i class="fa fa-lock"></i>
                <input id="password" type="password" class="form-control" name="senha" placeholder="Digite sua senha" tabindex="2" required>
              </div>
            </div>

            <button type="submit" class="login-submit" tabindex="3">
              <i class="fa fa-sign-in"></i>
              Entrar
            </button>
          </form>

          <div class="login-footer">
            <?= SHEEP_RODAPE_PAINEL ?>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script src="assets/js/app.min.js"></script>
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/jmask.js?v=20260602"></script>
  <script src="assets/js/custom.js?v=20260602"></script>

<script>
function preencherLogin() {
  var em = document.getElementById('email');
  var pw = document.getElementById('password');
  if (em) { em.value = 'demo@admin.com'; em.dispatchEvent(new Event('input')); }
  if (pw) { pw.value = '12345';          pw.dispatchEvent(new Event('input')); }
  var btn = document.querySelector('.login-submit') || document.querySelector('button[type="submit"]');
  if (btn) { setTimeout(function(){ btn.click(); }, 80); }
}
</script>
</body>
</html>
