<?php
require_once('header.php');

$email = filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL);
$erro = false;
$sucesso = false;

if (!$email) {
    echo "<script>window.location.href='".HOME."/index';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verificar_codigo'])) {
        $codigo_digitado = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_STRING);
        
        $ler = new Ler();
        $ler->Leitura('usuarios', "WHERE email = :email LIMIT 1", "email={$email}");
        if ($ler->getResultado()) {
            $usuario = $ler->getResultado()[0];
            
            if ($usuario['conta_verificada'] == 1) {
                $sucesso = "Sua conta já está verificada!";
            } else if ($usuario['codigo_otp'] === $codigo_digitado) {
                // Atualiza banco
                $up = new Atualizar();
                $up->Atualizando('usuarios', ['conta_verificada' => 1, 'codigo_otp' => null], "WHERE id = :id", "id={$usuario['id']}");
                
                if ($up->getResultado()) {
                    if (!in_array((string)($usuario['nivel'] ?? ''), ['C', '1'], true)) {
                        header("Location: " . HOME . "/index");
                        exit;
                    }

                    // Faz o login automático
                    $_SESSION['sheep_user'] = $usuario;
                    $_SESSION['sheep_user']['conta_verificada'] = 1;
                    
                    header("Location: " . HOME . "/minha-conta?sucesso_verificacao=true");
                    exit;
                } else {
                    $erro = "Erro ao atualizar conta no sistema.";
                }
            } else {
                $erro = "Código inválido. Verifique o e-mail que enviamos.";
            }
        } else {
            $erro = "Usuário não encontrado.";
        }
    }
    
    if (isset($_POST['reenviar_codigo'])) {
        $ler = new Ler();
        $ler->Leitura('usuarios', "WHERE email = :email LIMIT 1", "email={$email}");
        if ($ler->getResultado()) {
            $usuario = $ler->getResultado()[0];
            
            if ($usuario['conta_verificada'] == 1) {
                $sucesso = "Sua conta já está verificada!";
            } else {
                $novo_otp = sprintf("%06d", mt_rand(1, 999999));
                
                $up = new Atualizar();
                $up->Atualizando('usuarios', ['codigo_otp' => $novo_otp], "WHERE id = :id", "id={$usuario['id']}");
                
                $assunto = "Seu novo código de verificação - Pizzaria Modelo";
                $mensagem = "Olá " . $usuario['nome'] . "!\n\nSeu NOVO código de verificação é: " . $novo_otp . "\n\nVolte ao site e digite este código para ativar sua conta.";
                $headers = "From: contato@" . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'pizzariamodelo.com.br');
                @mail($usuario['email'], $assunto, $mensagem, $headers);
                
                $sucesso = "Um novo código foi enviado para seu e-mail.";
            }
        }
    }
}
?>

<section class="cadastro-section" style="padding-top: 15rem;">
    <div class="box-container">
        <div class="cadastro-box" style="text-align: center;">
            <div style="font-size: 5rem; color: #f39c12; margin-bottom: 2rem;">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <h2>Verifique seu E-mail</h2>
            <p style="font-size: 1.5rem; color: #666; margin-bottom: 3rem;">
                Nós enviamos um código de 6 dígitos para o e-mail<br>
                <strong><?= htmlspecialchars($email) ?></strong>
            </p>

            <?php if ($erro): ?>
                <div class="alert alert-danger" style="margin-bottom: 2rem; color: red; font-size: 1.4rem; padding: 1rem; border: 1px solid red; border-radius: .5rem; background: #fff0f0;">
                    <i class="fas fa-exclamation-circle"></i> <?= $erro ?>
                </div>
            <?php endif; ?>
            
            <?php if ($sucesso): ?>
                <div class="alert alert-success" style="margin-bottom: 2rem; color: green; font-size: 1.4rem; padding: 1rem; border: 1px solid green; border-radius: .5rem; background: #f0fff0;">
                    <i class="fas fa-check-circle"></i> <?= $sucesso ?>
                </div>
            <?php endif; ?>

            <form action="" method="post" id="form-otp">
                <div class="input-group" style="margin-bottom: 3rem;">
                    <label for="codigo" style="font-size: 1.6rem; color: #130f40;">Digite o código de 6 dígitos</label>
                    <input type="text" name="codigo" id="codigo" placeholder="000000" maxlength="6" style="font-size: 3rem; text-align: center; letter-spacing: 1rem; font-weight: bold; padding: 2rem; border: 2px solid #ddd; border-radius: 1rem; width: 100%; max-width: 300px; margin: 0 auto; display: block;" required>
                </div>

                <button type="submit" name="verificar_codigo" class="btn" style="width: 100%; background: #27ae60; color: #fff; padding: 1.5rem; font-size: 1.8rem; border-radius: 5rem; font-weight: bold; cursor: pointer; border: none; box-shadow: 0 .5rem 1.5rem rgba(39, 174, 96, 0.3);">Validar Minha Conta</button>
            </form>

            <div style="margin-top: 3rem; font-size: 1.4rem; color: #666; border-top: 1px solid #eee; padding-top: 2rem;">
                <p>Não recebeu o código? Verifique também sua caixa de Spam.</p>
                <form action="" method="post" style="margin-top: 1rem;">
                    <button type="submit" name="reenviar_codigo" style="background: none; border: none; color: #3498db; text-decoration: underline; font-size: 1.4rem; cursor: pointer; font-weight: bold;">
                        <i class="fas fa-sync-alt"></i> Reenviar código
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
.cadastro-section {
    padding: 12rem 9% 5rem 9%;
    background: #f9f9f9;
    min-height: 80vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.cadastro-box {
    background: #fff;
    padding: 4rem;
    border-radius: 1.5rem;
    box-shadow: 0 1rem 3rem rgba(0,0,0,0.05);
    width: 100%;
    max-width: 500px;
}
</style>

<script>
// Permitir apenas números
document.getElementById('codigo').addEventListener('input', function (e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>

<?php
require_once('footer.php');
?>
