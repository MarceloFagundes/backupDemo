<?php
require_once('header.php');

if (isset($_POST['sendCadastro'])) {
    $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
    unset($dados['sendCadastro']);
    
    // Sanitização de inputs para evitar XSS
    if(isset($dados['nome'])) $dados['nome'] = strip_tags(trim($dados['nome']));
    if(isset($dados['cpf'])) $dados['cpf'] = strip_tags(trim($dados['cpf']));
    if(isset($dados['email'])) $dados['email'] = filter_var(trim($dados['email']), FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        echo '<div class="alert alert-danger" style="margin-top: 10rem; text-align:center; font-size:1.5rem;">E-mail inválido! Por favor, insira um endereço de e-mail correto.</div>';
    } elseif ($dados['senha'] !== $dados['senha2']) {
        echo '<div class="alert alert-danger" style="margin-top: 10rem; text-align:center; font-size:1.5rem;">As senhas não conferem!</div>';
    } else {
        unset($dados['senha2']);
        $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        $dados['nivel'] = 1; // Cliente
        $dados['status'] = 'S';
        
        // NOVO: Adiciona flag de conta não verificada e OTP
        $dados['conta_verificada'] = 0;
        $otp = sprintf("%06d", mt_rand(1, 999999));
        $dados['codigo_otp'] = $otp;

        // Auto-heal das colunas no banco antes de inserir para evitar erros fatais
        try {
            $pdo = new PDO("mysql:host=" . HOST . ";dbname=" . DBSA . ";charset=utf8", USER, PASS);
            $check1 = $pdo->query("SHOW COLUMNS FROM `usuarios` LIKE 'conta_verificada'");
            if($check1->rowCount() == 0) $pdo->exec("ALTER TABLE `usuarios` ADD `conta_verificada` TINYINT(1) DEFAULT 0");
            
            $check2 = $pdo->query("SHOW COLUMNS FROM `usuarios` LIKE 'codigo_otp'");
            if($check2->rowCount() == 0) $pdo->exec("ALTER TABLE `usuarios` ADD `codigo_otp` VARCHAR(10) NULL DEFAULT NULL");
        } catch (Exception $e) {}
        
        $criar = new Criar();
        $criar->Criacao('usuarios', $dados);
        
        if ($criar->getResultado()) {
             // Envia o e-mail de verificação
             $assunto = "Seu código de verificação - Pizzaria Modelo";
             $mensagem = "Olá " . $dados['nome'] . "!\n\nBem-vindo ao Clube Fidelidade.\n\nSeu código de verificação é: " . $otp . "\n\nVolte ao site e digite este código para ativar sua conta.";
             $headers = "From: contato@" . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'pizzariamodelo.com.br');
             @mail($dados['email'], $assunto, $mensagem, $headers);
             
             // Redireciona para a tela de verificação
             header("Location: " . HOME . "/verificar?email=" . urlencode($dados['email']));
             exit;
        } else {
            echo '<div class="alert alert-danger" style="margin-top: 10rem; text-align:center; font-size:1.5rem;">Erro ao realizar cadastro! Este e-mail ou CPF já podem estar em uso.</div>';
        }
    }
}
?>

<section class="cadastro-section">
    <div class="box-container">
        <div class="cadastro-box">
            <h2>Crie sua Conta</h2>
            <p>Cadastre-se para acompanhar seus pedidos e comprar mais rápido.</p>

            <form action="" method="post">
                <div class="input-group">
                    <label for="nome">Nome Completo</label>
                    <input type="text" name="nome" id="nome" placeholder="Seu nome" required>
                </div>

                <div class="input-group">
                    <label for="email">E-mail</label>
                    <input type="email" name="email" id="email" placeholder="seu@email.com" required>
                </div>

                <div class="input-group">
                    <label for="cpf">CPF (Para sua segurança)</label>
                    <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00">
                </div>

                <div class="row-inputs">
                    <div class="input-group">
                        <label for="senha">Senha</label>
                        <input type="password" name="senha" id="senha" placeholder="******" required>
                    </div>
                    <div class="input-group">
                        <label for="senha2">Confirmar Senha</label>
                        <input type="password" name="senha2" id="senha2" placeholder="******" required>
                    </div>
                </div>

                <button type="submit" name="sendCadastro" class="btn">Finalizar Cadastro</button>

                <div class="divider">
                    <span>OU</span>
                </div>

                <a href="#" class="btn-google">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google Logo" width="20">
                    Cadastrar com Google
                </a>
            </form>

            <p class="login-link">Já tem uma conta? <a href="#" id="open-login-modal">Acesse aqui</a></p>
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

.cadastro-box h2 {
    font-size: 3rem;
    color: #130f40;
    margin-bottom: 1rem;
    text-align: center;
}

.cadastro-box p {
    font-size: 1.5rem;
    color: #666;
    margin-bottom: 3rem;
    text-align: center;
}

.cadastro-box .input-group {
    margin-bottom: 2rem;
}

.cadastro-box label {
    display: block;
    font-size: 1.4rem;
    color: #130f40;
    margin-bottom: .8rem;
    font-weight: bold;
}

.cadastro-box input {
    width: 100%;
    padding: 1.2rem 1.5rem;
    font-size: 1.5rem;
    border: 1px solid #ddd;
    border-radius: .8rem;
    background: #fff;
}

.cadastro-box .row-inputs {
    display: flex;
    gap: 2rem;
}

.cadastro-box .btn {
    width: 100%;
    background: red;
    color: #fff;
    border: none;
    padding: 1.5rem;
    font-size: 1.8rem;
    border-radius: .8rem;
    margin-top: 1rem;
}

.cadastro-box .btn:hover {
    background: #130f40;
}

.cadastro-box .divider {
    margin: 2.5rem 0;
    text-align: center;
    position: relative;
}

.cadastro-box .divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    width: 100%;
    height: 1px;
    background: #eee;
    z-index: 1;
}

.cadastro-box .divider span {
    background: #fff;
    padding: 0 1.5rem;
    font-size: 1.2rem;
    color: #999;
    position: relative;
    z-index: 2;
}

.btn-google {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    width: 100%;
    padding: 1.2rem;
    font-size: 1.6rem;
    color: #444;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: .8rem;
    text-decoration: none;
    transition: .3s;
}

.btn-google:hover {
    background: #f5f5f5;
}

.login-link {
    margin-top: 2.5rem;
    font-size: 1.4rem;
    text-align: center;
}

.login-link a {
    color: red;
    font-weight: bold;
}
</style>

<?php
require_once('footer.php');
?>
