<?php
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8'); // already present
// Helper to decode JSON-style Unicode escape sequences
function decodeUnicode($str) {
    // Ensure proper JSON string handling
    $decoded = json_decode('"' . $str . '"');
    return $decoded !== null ? $decoded : $str;
}

if (!function_exists('site_usuario_cliente_logado')) {
    function site_usuario_cliente_logado() {
        if (empty($_SESSION['sheep_user']) || !is_array($_SESSION['sheep_user'])) {
            return false;
        }

        $nivel = (string)($_SESSION['sheep_user']['nivel'] ?? '');
        return in_array($nivel, ['C', '1'], true);
    }
}

if (!function_exists('mondiniTemaImagemUrl')) {
    function mondiniTemaImagemUrl($imagem) {
        $imagem = ltrim(str_replace('\\', '/', (string)$imagem), '/');
        if ($imagem === '' || preg_match('/^https?:\/\//i', $imagem)) {
            return $imagem;
        }

        if (strpos($imagem, 'assets/img/') === 0) {
            $imagem = substr($imagem, strlen('assets/img/'));
        }

        $webp = preg_replace('/\.(jpe?g|png|gif)$/i', '.webp', $imagem);
        if ($webp && $webp !== $imagem && is_file(__DIR__ . '/assets/img/' . $webp)) {
            return CAMINHO_TEMAS . '/assets/img/' . $webp;
        }

        return CAMINHO_TEMAS . '/assets/img/' . $imagem;
    }
}

$site_cliente_logado = site_usuario_cliente_logado();
$site_cliente = $site_cliente_logado ? $_SESSION['sheep_user'] : null;

// Lógica de Login do Cliente
if(isset($_POST['sendLogin']) && isset($_POST['email']) && isset($_POST['senha'])){
    $email = strip_tags(trim($_POST['email']));
    $senha = $_POST['senha'];
    
    if(!isset($sheep)) $sheep = new Ler();
    $sheep->Leitura('usuarios', "WHERE email = :email LIMIT 1", "email={$email}");
    
    if($sheep->getResultado()){
        $usuario = $sheep->getResultado()[0];
        if(password_verify($senha, $usuario['senha'])){
            $nivelUsuarioLogin = (string)($usuario['nivel'] ?? '');
            if (!in_array($nivelUsuarioLogin, ['C', '1'], true)) {
                header("Location: " . HOME . "/sheep_painel/index.php");
                exit;
            }

            // ============================================================
            // ⚠️  MODO DEMO - VERIFICAÇÃO DE E-MAIL DESATIVADA
            // ============================================================
            // Em produção, REMOVA este bloco e DESCOMENTE o bloco abaixo
            // para ativar o envio de OTP via PHPMailer (SMTP real).
            // Veja: PRODUCAO_CHECKLIST.md na raiz do projeto.
            // ============================================================
            if (isset($usuario['conta_verificada']) && $usuario['conta_verificada'] == '0') {
                // AUTO-VERIFICA a conta para funcionar no ambiente demo/local
                $up = new Atualizar();
                $up->Atualizando('usuarios', ['conta_verificada' => '1'], "WHERE id = :id", "id={$usuario['id']}");
            }
            // ============================================================
            // 🚀 BLOCO DE PRODUÇÃO (DESCOMENTADO EM DEPLOY)
            // ============================================================
            // if (isset($usuario['conta_verificada']) && $usuario['conta_verificada'] == '0') {
            //     $otp = sprintf("%06d", mt_rand(1, 999999));
            //     $up = new Atualizar();
            //     $up->Atualizando('usuarios', ['codigo_otp' => $otp], "WHERE id = :id", "id={$usuario['id']}");
            //     // TODO: Substituir mail() por PHPMailer com SMTP real
            //     // Veja PRODUCAO_CHECKLIST.md para as instruções completas
            //     @mail($usuario['email'], "Ative sua conta", "Código: " . $otp);
            //     header("Location: " . HOME . "/verificar?email=" . urlencode($usuario['email']));
            //     exit;
            // }
            $_SESSION['sheep_user'] = $usuario;
            if ($usuario['nivel'] === 'M' || $usuario['nivel'] === 'O') {
                header("Location: " . HOME . "/sheep_painel/index.php");
            } else {
                header("Location: " . HOME . "/minha-conta");
            }
            exit;
        } else {
            $erroLogin = "Senha incorreta!";
            echo "<script>document.addEventListener('DOMContentLoaded', function() { document.querySelector('.login-form').classList.toggle('active'); });</script>";
        }
    } else {
        $erroLogin = "E-mail não encontrado!";
        echo "<script>document.addEventListener('DOMContentLoaded', function() { document.querySelector('.login-form').classList.toggle('active'); });</script>";
    }
}

// Busca TODAS as configurações da loja (status, logo, cor, nome, redes sociais)
if(!isset($config_loja)){
    if(!isset($sheep)) $sheep = new Ler();
    $sheep->Leitura('configuracoes', "WHERE id = '1'");
    $config_loja = [];
    if($sheep->getResultado()){
        $config_loja = $sheep->getResultado()[0];
    }
    $status_loja   = $config_loja['status_loja']   ?? 'aberta';
    $nome_loja     = $config_loja['nome']           ?? 'Pizzaria Modelo';
    $logo_loja     = $config_loja['logo']           ?? '';
    $cor_primaria  = $config_loja['cor_primaria']   ?? '#ea1d2c';
    $link_instagram = $config_loja['link_instagram'] ?? '';
    $link_facebook  = $config_loja['link_facebook']  ?? '';
    $banner_1      = $config_loja['banner_1']       ?? '';
    $banner_2      = $config_loja['banner_2']       ?? '';
    $banner_3      = $config_loja['banner_3']       ?? '';
}
?>
<link rel="stylesheet" href="<?= HOME ?>/sheep_temas/site/assets/css/style.css?v=1.2">

<style>
  :root {
    --cor-primaria: <?= htmlspecialchars($cor_primaria) ?>;
  }
  .btn, a.btn { background-color: var(--cor-primaria) !important; border-color: var(--cor-primaria) !important; color: #fff !important; }
  .topo-site { background-color: var(--cor-primaria) !important; border-bottom: 3px solid var(--cor-primaria); }
  .topo-site .logo { background: transparent !important; }

</style>
   <!-- INICIO CABEÇALHO DO SITE -->
	<header class="topo-site">

        <?php if($status_loja == 'fechada'): ?>
        <div style="background: var(--danger, #e74c3c); color: #fff; text-align: center; padding: 10px; font-size: 1.5rem; font-weight: bold; position: absolute; top: 0; left: 0; width: 100%; z-index: 10000;">
            <i class="fa fa-lock"></i> LOJA FECHADA NO MOMENTO - NÃO ESTAMOS RECEBENDO PEDIDOS
        </div>
        <style>
            .topo-site { padding-top: 5rem !important; }
        </style>
        <?php endif; ?>

		<a href="<?= HOME ?>/index" class="logo">
			<?php if($logo_loja): ?>
				<img src="<?= HOME ?>/sheep_painel/assets/img/logo/<?= htmlspecialchars($logo_loja) ?>" alt="<?= htmlspecialchars($nome_loja) ?>" style="max-height: 55px; width: auto;">
			<?php else: ?>
				<i class="fas fa-pizza-slice"></i> <?= htmlspecialchars($nome_loja) ?>
			<?php endif; ?>
		</a>

			<nav class="menu-site">
				<a href="<?= HOME ?>/index">Inicio</a>
				<a href="<?= HOME ?>/index#empresa">Empresa</a>
				<a href="<?= HOME ?>/loja">Cardápio</a>
				<a href="<?= HOME ?>/montar-pizza" style="color: yellow; font-weight: bold;">Montar Pizza</a>
				<a href="<?= HOME ?>/blog">Blog</a>
				<a href="<?= HOME ?>/contato">Fale conosco</a>
			</nav>

			<div class="icons">
				<div id="cart"class="fas fa-shopping-cart"></div>
				<div id="login" class="fas fa-user"></div>
				<div id="menu" class="fas fa-bars"></div>
			</div>

			

			<div class="carrinho">
				<?php
				$totalCarrinho = 0;
				if(!empty($_SESSION['carrinho'])):
					foreach($_SESSION['carrinho'] as $id => $qtd):
						if (strpos((string)$id, 'custom_') === 0) {
							// É uma pizza customizada
							$customPizza = isset($_SESSION['custom_pizzas'][$id]) ? $_SESSION['custom_pizzas'][$id] : null;
							$nome = $customPizza ? htmlspecialchars(stripslashes($customPizza['nome']), ENT_QUOTES, 'UTF-8') : "Pizza Personalizada";
							$preco = $customPizza ? $customPizza['preco'] : 59.90;
							$imagem = $customPizza ? $customPizza['imagem'] : "pizza-1.png";
							
							$subtotal = $preco * $qtd;
							$totalCarrinho += $subtotal;
				?>
				<div class="box">
					<a href="<?= HOME ?>/carrinho?remove=<?= $id ?>"><i class="fa fa-times"></i></a>
					<img src="<?= mondiniTemaImagemUrl('loja/' . $imagem) ?>" alt="Pizza Personalizada" loading="lazy" decoding="async">
					<div class="content">
						<h3><?= $nome ?></h3>
						<span class="quantidade"><?= $qtd ?></span>
						<span class="multiplica">x</span>
						<span class="valor">R$ <?= number_format($preco, 2, ',', '.') ?></span>
					</div>
				</div>
				<?php
						} else {
							// Produto normal
							$produtoNormal = isset($_SESSION['produtos_normais'][$id]) ? $_SESSION['produtos_normais'][$id] : null;
							$preco = 49.90;
							$nome = "Pizza Tradicional";
							$imagem = "pizza-1.png";
							
							if($produtoNormal){
								$nome = htmlspecialchars(stripslashes($produtoNormal['nome']), ENT_QUOTES, 'UTF-8');
								$preco = $produtoNormal['preco'];
								$imagem = $produtoNormal['imagem'];
							} else {
								$sheep->Leitura('produtos', "WHERE id = :id", "id={$id}");
								$produto = $sheep->getResultado();
								if($produto){
									$produto = $produto[0];
									$nome = htmlspecialchars(stripslashes($produto['nome']), ENT_QUOTES, 'UTF-8');
									$preco = $produto['preco_promocional'] ? $produto['preco_promocional'] : $produto['preco']; // keep price
									$imagem = $produto['imagem'];
								}
							}
							
							$subtotal = $preco * $qtd;
							$totalCarrinho += $subtotal;
				?>
				<div class="box">
					<a href="<?= HOME ?>/carrinho?remove=<?= $id ?>"><i class="fa fa-times"></i></a>
					<img src="<?= mondiniTemaImagemUrl('loja/' . $imagem) ?>" alt="<?= $nome ?>" loading="lazy" decoding="async">
					<div class="content">
						<h3><?= $nome ?></h3>
						<span class="quantidade"><?= $qtd ?></span>
						<span class="multiplica">x</span>
						<span class="valor">R$ <?= number_format($preco, 2, ',', '.') ?></span>
					</div>
				</div>
				<?php 
						}
					endforeach;
				?>
				<h3 class="total">Total: R$ <?= number_format($totalCarrinho, 2, ',', '.') ?></h3>
				<?php if($status_loja == 'fechada'): ?>
				    <button class="btn" style="background: #95a5a6; cursor: not-allowed; opacity: 0.8; width: 100%;" disabled>Loja Fechada</button>
				<?php else: ?>
				    <a href="<?= HOME ?>/carrinho" class="btn">Ir para o carrinho</a>
				<?php endif; ?>
				<?php else: ?>
					<p style="text-align: center; font-size: 1.5rem; padding: 2rem;">Seu carrinho está vazio!</p>
					<a href="<?= HOME ?>/loja" class="btn">Ver Pizzas</a>
				<?php endif; ?>
			</div>


			<!-- LOGIN TOPO DO SITE -->
			<div class="login-form">
				<h3>Acesse sua Conta</h3>
				
                <?php if($site_cliente_logado): ?>
                    <p style="font-size: 1.4rem; color: #666; margin-bottom: 1.5rem; text-align: center;">Olá, <strong><?= htmlspecialchars($site_cliente['nome']) ?></strong>!</p>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        
                        <?php if($site_cliente_logado): ?>
                            <!-- Somente clientes veem o painel do cliente -->
                            <a href="<?= HOME ?>/minha-conta" class="btn" style="width: 100%; text-align: center; margin-top: 0;">Painel do Cliente</a>
                            <a href="<?= HOME ?>/minha-conta?sair=true" class="btn" style="width: 100%; background: #95a5a6; color: #fff; text-align: center; margin-top: 0;">Sair da Conta</a>
                        <?php else: ?>
                            <!-- Administradores veem apenas o atalho para o painel administrativo -->
                            <div style="text-align: center; margin: 5px 0; display: flex; align-items: center; justify-content: center; gap: 10px;">
                                <hr style="flex: 1; border: none; border-top: 1px solid #eee;">
                                <span style="font-size: 1.2rem; color: #999; font-weight: bold;">ADMIN</span>
                                <hr style="flex: 1; border: none; border-top: 1px solid #eee;">
                            </div>
                            <a href="<?= HOME ?>/sheep_painel/index.php" class="btn" style="background: #130f40; color: #fff; border-color: #130f40; width: 100%; text-align: center; margin-top: 0;">
                                <i class="fas fa-user-shield"></i> Painel Administrativo
                            </a>
                            <a href="<?= HOME ?>/minha-conta?sair=true" class="btn" style="width: 100%; background: #95a5a6; color: #fff; text-align: center; margin-top: 0;">Sair da Conta</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p style="font-size: 1.4rem; color: #666; margin-bottom: 1.5rem; text-align: center;">Escolha seu tipo de acesso:</p>
                    
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <!-- Botão Admin -->
                        <a href="<?= HOME ?>/sheep_painel/index.php" class="btn" style="background: #130f40; color: #fff; border-color: #130f40; width: 100%; text-align: center; margin-top: 0;">
                            <i class="fas fa-user-shield"></i> Painel Administrativo
                        </a>
                        
                        <div style="text-align: center; margin: 5px 0; display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <hr style="flex: 1; border: none; border-top: 1px solid #eee;">
                            <span style="font-size: 1.2rem; color: #999; font-weight: bold;">OU</span>
                            <hr style="flex: 1; border: none; border-top: 1px solid #eee;">
                        </div>

                        <!-- Formulário Cliente -->
                        <form action="" method="post" style="box-shadow: none; padding: 0; background: transparent; width: 100%; border: none;">
                            <input type="email" name="email" class="box" placeholder="E-mail do Cliente" style="width: 100%; margin-bottom: 1rem;" required>
                            <input type="password" name="senha" class="box" placeholder="Sua Senha" style="width: 100%; margin-bottom: 1rem;" required>
                            <button type="submit" name="sendLogin" class="btn" style="width: 100%;">Entrar na Conta</button>
                        </form>
                    </div>

                    <p style="margin-top: 1.5rem; font-size: 1.3rem; text-align: center;">Ainda não tem conta?<br> <a href="<?= HOME ?>/loja" style="color: red; font-weight: bold;">Crie automaticamente ao finalizar seu primeiro pedido!</a></p>
                <?php endif; ?>
			</div>

	</header>
