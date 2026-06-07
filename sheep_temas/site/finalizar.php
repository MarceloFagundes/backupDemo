<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$checkoutClienteLogado = (
    !empty($_SESSION['sheep_user']) &&
    is_array($_SESSION['sheep_user']) &&
    in_array((string)($_SESSION['sheep_user']['nivel'] ?? ''), ['C', '1'], true)
);
$checkoutCliente = $checkoutClienteLogado ? $_SESSION['sheep_user'] : null;

if (empty($_SESSION['checkout_csrf_token'])) {
    $_SESSION['checkout_csrf_token'] = bin2hex(random_bytes(32));
}

if(empty($_SESSION['carrinho']) && !isset($_GET['pagar'])){
    header("Location: " . HOME . "/loja");
    exit;
}

// Carrega configurações dinâmicas
if (!isset($sheepConfig)) $sheepConfig = new Ler();
$sheepConfig->Leitura('configuracoes', "WHERE id = '1'");
$configs = $sheepConfig->getResultado() ? $sheepConfig->getResultado()[0] : null;
$mp_access_token_dyn = (!empty($configs['mp_access_token'])) ? $configs['mp_access_token'] : (defined('MP_ACCESS_TOKEN') ? MP_ACCESS_TOKEN : '');
$mp_public_key_dyn = (!empty($configs['mp_public_key'])) ? $configs['mp_public_key'] : (defined('MP_PUBLIC_KEY') ? MP_PUBLIC_KEY : '');
$taxa_cashback_dyn = (!empty($configs['porcentagem_cashback'])) ? (float)$configs['porcentagem_cashback'] : 5.00;

// Carrega Bairros
$lerBairros = new Ler();
$lerBairros->Leitura('bairros_entrega', "WHERE status = 'ativo' ORDER BY nome_bairro ASC");
$bairrosDb = $lerBairros->getResultado();
if(!$bairrosDb) $bairrosDb = [];

$checkoutErros = [];

function checkout_limpar_texto($valor, $max = 120) {
    $valor = trim((string)$valor);
    $valor = strip_tags($valor);
    $valor = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $valor);
    $valor = preg_replace('/\s+/u', ' ', $valor);
    return function_exists('mb_substr') ? mb_substr($valor, 0, $max, 'UTF-8') : substr($valor, 0, $max);
}

function checkout_somente_digitos($valor) {
    return preg_replace('/\D+/', '', (string)$valor);
}

function checkout_numero_decimal($valor) {
    $valor = str_replace(['R$', ' ', '.'], '', (string)$valor);
    $valor = str_replace(',', '.', $valor);
    return is_numeric($valor) ? (float)$valor : 0.00;
}

// Processar o pedido
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pagamento'])){
    $agora = time();
    if (!empty($_SESSION['checkout_bloqueado_ate']) && $_SESSION['checkout_bloqueado_ate'] > $agora) {
        $checkoutErros[] = 'Muitas tentativas em pouco tempo. Aguarde alguns minutos e tente novamente.';
    }

    $csrfToken = $_POST['csrf_token'] ?? '';
    if (empty($csrfToken) || empty($_SESSION['checkout_csrf_token']) || !hash_equals($_SESSION['checkout_csrf_token'], $csrfToken)) {
        $checkoutErros[] = 'Sessao expirada. Recarregue a pagina e tente novamente.';
    }

    $cliente_nome = checkout_limpar_texto($_POST['nome'] ?? '', 90);
    $email = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
    $telefone = checkout_somente_digitos($_POST['telefone'] ?? '');
    $bairro = checkout_limpar_texto($_POST['bairro'] ?? '', 150);
    $endereco = checkout_limpar_texto($_POST['endereco'] ?? '', 180);
    $numero = checkout_limpar_texto($_POST['numero'] ?? '', 20);
    $endereco_completo = $endereco . ", Numero: " . $numero . " - Bairro: " . $bairro;
    $pagamento = checkout_limpar_texto($_POST['pagamento'] ?? '', 40);
    $pagamentosPermitidos = ['Pagamento Online', 'Dinheiro', 'Cartao Entrega'];

    $bairrosPermitidos = [];
    foreach ($bairrosDb as $bairroDb) {
        $bairrosPermitidos[$bairroDb['nome_bairro']] = true;
    }

    if (!preg_match('/^[\p{L}\s\'-]{3,90}$/u', $cliente_nome)) {
        $checkoutErros[] = 'Informe um nome valido, sem numeros ou simbolos.';
    }
    if (!$email) {
        $checkoutErros[] = 'Informe um e-mail valido.';
    }
    if (!preg_match('/^\d{10,11}$/', $telefone)) {
        $checkoutErros[] = 'Informe um telefone valido com DDD, usando apenas numeros.';
    }
    if (strlen($endereco) < 5 || strlen($endereco) > 180) {
        $checkoutErros[] = 'Informe um endereco valido.';
    }
    if (!preg_match('/^(\d{1,6}|s\/?n)$/i', $numero)) {
        $checkoutErros[] = 'Informe um numero valido. Use apenas numeros ou S/N.';
    }
    if (empty($bairro) || empty($bairrosPermitidos[$bairro])) {
        $checkoutErros[] = 'Selecione um bairro valido para entrega.';
    }
    if (!in_array($pagamento, $pagamentosPermitidos, true)) {
        $checkoutErros[] = 'Selecione uma forma de pagamento valida.';
    }
    if (!$checkoutClienteLogado && isset($_POST['criar_conta']) && $_POST['criar_conta'] == '1' && strlen((string)($_POST['senha_nova'] ?? '')) < 8) {
        $checkoutErros[] = 'A senha da conta precisa ter no minimo 8 caracteres.';
    }
    
    // Detalhes extras de pagamento na entrega
    if($pagamento == 'Dinheiro' && !empty($_POST['troco'])){
        $troco = checkout_numero_decimal($_POST['troco']);
        if ($troco > 0 && $troco <= 10000) {
            $pagamento .= " (Troco para: R$ " . number_format($troco, 2, ',', '.') . ")";
        }
    }
    if($pagamento == 'Cartao Entrega' && !empty($_POST['bandeira'])){
        $bandeira = checkout_limpar_texto($_POST['bandeira'], 40);
        if (preg_match('/^[\p{L}\p{N}\s-]{2,40}$/u', $bandeira)) {
            $pagamento .= " (" . $bandeira . ")";
        }
    }

    if(empty($checkoutErros)){
        unset($_SESSION['checkout_tentativas'], $_SESSION['checkout_bloqueado_ate']);
        
        // === CRIAR CONTA OPCIONAL NO CHECKOUT ===
        if(!$checkoutClienteLogado && isset($_POST['criar_conta']) && $_POST['criar_conta'] == '1' && !empty($_POST['senha_nova'])){
            $email_checkout = $email;
            $senha_nova = $_POST['senha_nova'];
            if (strlen($senha_nova) < 8) {
                $senha_nova = '';
            }
            
            // Verifica se e-mail já existe
            $verificar = new Ler();
            $verificar->Leitura('usuarios', "WHERE email = :email", "email={$email_checkout}");
            
            if(!$verificar->getResultado()){
                // E-mail livre, cria a conta!
                $otp = sprintf("%06d", mt_rand(1, 999999));
                $dadosConta = [
                    'nome'   => $cliente_nome,
                    'email'  => $email_checkout,
                    'senha'  => password_hash($senha_nova, PASSWORD_DEFAULT),
                    'nivel'  => 'C',
                    'status' => 'S',
                    'conta_verificada' => 0,
                    'codigo_otp' => $otp
                ];
                
                // Auto-heal
                try {
                    $pdo = new PDO("mysql:host=" . SHEEP_HOST . ";dbname=" . SHEEP_BD . ";charset=utf8mb4", SHEEP_USER, SHEEP_SENHA);
                    $check1 = $pdo->query("SHOW COLUMNS FROM `usuarios` LIKE 'conta_verificada'");
                    if($check1->rowCount() == 0) $pdo->exec("ALTER TABLE `usuarios` ADD `conta_verificada` TINYINT(1) DEFAULT 0");
                    $check2 = $pdo->query("SHOW COLUMNS FROM `usuarios` LIKE 'codigo_otp'");
                    if($check2->rowCount() == 0) $pdo->exec("ALTER TABLE `usuarios` ADD `codigo_otp` VARCHAR(10) NULL DEFAULT NULL");
                } catch (Exception $e) {}

                $criarUser = new Criar();
                $criarUser->Criacao('usuarios', $dadosConta);
                $novoUserId = $criarUser->getResultado();
                
                if($novoUserId){
                     // Envia o e-mail de verificação
                     $assunto = "Seu código de verificação - Pizzaria Modelo";
                     $mensagem = "Olá " . $cliente_nome . "!\n\nSeu pedido foi recebido e sua conta no Clube Fidelidade foi pré-criada.\n\nSeu código de verificação é: " . $otp . "\n\nVocê precisa verificar seu e-mail para utilizar seus benefícios no futuro.";
                     $headers = "From: contato@" . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'pizzariamodelo.com.br');
                     @mail($email_checkout, $assunto, $mensagem, $headers);
                    // Loga o usuário automaticamente na sessão
                    $lerNovoUser = new Ler();
                    $lerNovoUser->Leitura('usuarios', "WHERE id = :id", "id={$novoUserId}");
                    if($lerNovoUser->getResultado()){
                        $_SESSION['sheep_user'] = $lerNovoUser->getResultado()[0];
                        $checkoutClienteLogado = true;
                        $checkoutCliente = $_SESSION['sheep_user'];
                    }
                }
            }
        }
        // === FIM CRIAR CONTA ===
        // Calcular total
        $total = 0;
        $itemsMP = array();
        foreach($_SESSION['carrinho'] as $id => $qtd){
            if (strpos((string)$id, 'custom_') === 0) {
                // É uma pizza customizada
                $customPizza = isset($_SESSION['custom_pizzas'][$id]) ? $_SESSION['custom_pizzas'][$id] : null;
                $preco = $customPizza ? $customPizza['preco'] : 59.90;
                $nome = $customPizza ? $customPizza['nome'] : "Pizza Personalizada";
                $detalhes = $customPizza ? $customPizza['detalhes'] : "Montada no site";
                $total += $preco * $qtd;

                $itemsMP[] = array(
                    "title" => $nome . " (" . $detalhes . ")",
                    "quantity" => (int)$qtd,
                    "unit_price" => (float)$preco,
                    "currency_id" => "BRL"
                );
            } else {
                // Produto normal
                $produtoNormal = isset($_SESSION['produtos_normais'][$id]) ? $_SESSION['produtos_normais'][$id] : null;
                $preco = 49.90;
                $nome = "Pizza Tradicional";
                if($produtoNormal){
                    $preco = $produtoNormal['preco'];
                    $nome = $produtoNormal['nome'];
                } else {
                    $sheep->Leitura('produtos', "WHERE id = :id", "id={$id}");
                    $res = $sheep->getResultado();
                    if($res){
                        $p = $res[0];
                        $preco = ($p['preco_promocional'] ? $p['preco_promocional'] : $p['preco']);
                        $nome = $p['nome'];
                    }
                }
                
                $total += $preco * $qtd;

                $itemsMP[] = array(
                    "title" => $nome,
                    "quantity" => (int)$qtd,
                    "unit_price" => (float)$preco,
                    "currency_id" => "BRL"
                );
            }
        }

        // Buscar a taxa do bairro no banco para segurança
        $taxa_bairro = 0.00;
        $lerTaxa = new Ler();
        $lerTaxa->Leitura('bairros_entrega', "WHERE nome_bairro = :bairro AND status = 'ativo'", "bairro={$bairro}");
        if($lerTaxa->getResultado()){
            $taxa_bairro = (float)$lerTaxa->getResultado()[0]['taxa'];
        }

        // Adiciona taxa ao total
        $total += $taxa_bairro;

        // Salvar pedido no banco
        $usuario_id = $checkoutCliente ? $checkoutCliente['id'] : null;
        $desconto_cashback = 0.00;

        if ($usuario_id && isset($_POST['usar_cashback']) && $_POST['usar_cashback'] == '1') {
            $fidelidade = new FidelidadeService();
            $carteira = $fidelidade->getCarteira($usuario_id);
            if ($carteira) {
                $saldo_atual = $carteira['saldo_cashback'];
                $desconto_cashback = min($total, $saldo_atual);
            }
        }

        $total_final = $total - $desconto_cashback;

        $status_inicial = 'pendente';
        if ($total_final <= 0) {
            $status_inicial = 'pago'; // Se zerar com cashback, já está pago
            $pagamento = 'Saldo de Cashback (100% Desconto)';
        } elseif ($pagamento == 'Pagamento Online') {
            $status_inicial = 'aguardando_pagamento';
        }

        $dadosPedido = [
            'cliente_nome' => $cliente_nome,
            'cliente_telefone' => $telefone,
            'cliente_endereco' => $endereco_completo,
            'forma_pagamento' => $pagamento,
            'valor_total' => $total_final,
            'taxa_entrega' => $taxa_bairro,
            'bairro' => $bairro,
            'desconto_cashback' => $desconto_cashback,
            'status' => $status_inicial 
        ];

        if ($usuario_id) {
            $dadosPedido['usuario_id'] = $usuario_id;
        }

        $criar = new Criar();
        $criar->Criacao('pedidos', $dadosPedido);
        $pedidoId = $criar->getResultado();

        if (!$pedidoId) {
            echo '<div style="position:fixed; top:0; left:0; width:100%; height:100%; background:red; color:white; z-index:999999; padding:50px; font-size:24px;">';
            echo '<h1>ERRO CRÍTICO NO BANCO DE DADOS</h1>';
            echo '<p>O pedido não pôde ser salvo na tabela "pedidos".</p>';
            echo '<h3>Dados tentados:</h3><pre style="background:#000; color:#0f0; padding:20px;">';
            print_r($dadosPedido);
            echo '</pre>';
            echo '<p>Por favor, tire um print desta tela e mande para o desenvolvedor.</p>';
            echo '</div>';
            exit;
        }

        if ($pedidoId) {
            // Deduzir o cashback se aplicável
            if ($desconto_cashback > 0) {
                $fidelidade = new FidelidadeService();
                $fidelidade->registrarDeducao($usuario_id, $pedidoId, $desconto_cashback);
            }

            // Salvar itens do pedido
            foreach($_SESSION['carrinho'] as $id => $qtd){
                if (strpos((string)$id, 'custom_') === 0) {
                    // É uma pizza customizada
                    $customPizza = isset($_SESSION['custom_pizzas'][$id]) ? $_SESSION['custom_pizzas'][$id] : null;
                    $dadosItem = [
                        'pedido_id' => $pedidoId,
                        'produto_id' => 0, // 0 indica pizza customizada
                        'quantidade' => $qtd,
                        'preco_unitario' => $customPizza ? $customPizza['preco'] : 59.90,
                        'detalhes' => $customPizza ? $customPizza['detalhes'] : "Pizza Personalizada"
                    ];
                    $criar->Criacao('itens_pedido', $dadosItem);
                } else {
                    // Produto normal
                    $produtoNormal = isset($_SESSION['produtos_normais'][$id]) ? $_SESSION['produtos_normais'][$id] : null;
                    $preco_unitario = 49.90;
                    if($produtoNormal){
                        $preco_unitario = $produtoNormal['preco'];
                    } else {
                        $sheep->Leitura('produtos', "WHERE id = :id", "id={$id}");
                        $res = $sheep->getResultado();
                        if($res){
                            $p = $res[0];
                            $preco_unitario = ($p['preco_promocional'] ? $p['preco_promocional'] : $p['preco']);
                        }
                    }
                    
                    $dadosItem = [
                        'pedido_id' => $pedidoId,
                        'produto_id' => $id,
                        'quantidade' => $qtd,
                        'preco_unitario' => $preco_unitario,
                        'detalhes' => null
                    ];
                    $criar->Criacao('itens_pedido', $dadosItem);
                }
            }

            // Lógica especial para Cashback Total (R$ 0,00) - pula pagamentos e vai para o sucesso
            if ($total_final <= 0) {
                unset($_SESSION['carrinho']);
                session_write_close();
                header("Location: " . HOME . "/sucesso?id=" . $pedidoId);
                exit;
            }

            // LOGICA PAGAMENTO ONLINE - Usa Mercado Pago Transparente (Bricks)
            if($pagamento == 'Pagamento Online'){
                $mp_token = $_POST['mp_token'] ?? '';
                $payment_method_id = $_POST['mp_payment_method_id'] ?? '';
                $issuer_id = $_POST['mp_issuer_id'] ?? '';
                $installments = $_POST['mp_installments'] ?? 1;
                $payer_email = $_POST['mp_payer_email'] ?? $email;
                $doc_type = $_POST['mp_payer_id_type'] ?? '';
                $doc_number = $_POST['mp_payer_id_number'] ?? '';

                $paymentData = [
                    "transaction_amount" => (float)$total_final,
                    "token" => $mp_token,
                    "description" => "Pedido #{$pedidoId} - Pizzaria Modelo",
                    "installments" => (int)$installments,
                    "payment_method_id" => $payment_method_id,
                    "issuer_id" => $issuer_id,
                    "payer" => [
                        "email" => $payer_email,
                        "identification" => [
                            "type" => $doc_type,
                            "number" => $doc_number
                        ]
                    ],
                    "external_reference" => (string)$pedidoId
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/v1/payments');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
                $headers = array();
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Authorization: Bearer ' . $mp_access_token_dyn;
                $headers[] = 'X-Idempotency-Key: ' . uniqid('', true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $result = curl_exec($ch);
                curl_close($ch);

                $paymentJson = json_decode($result);
                
                if(isset($paymentJson->status) && ($paymentJson->status == 'approved' || $paymentJson->status == 'in_process' || $paymentJson->status == 'pending')){
                    session_write_close();
                    header("Location: " . HOME . "/sucesso?id=" . $pedidoId);
                    exit;
                }
                
                // Se falhar a API, vai para o sucesso mas fica aguardando
                session_write_close();
                header("Location: " . HOME . "/sucesso?id=" . $pedidoId . "&erro=mercadopago");
                exit;
            }

            // Se for outra forma ou falhar, vai direto para sucesso
            unset($_SESSION['carrinho']);
            session_write_close();
            header("Location: " . HOME . "/sucesso?id=" . $pedidoId);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($checkoutErros)) {
    $_SESSION['checkout_tentativas'] = (int)($_SESSION['checkout_tentativas'] ?? 0) + 1;
    if ($_SESSION['checkout_tentativas'] >= 5) {
        $_SESSION['checkout_bloqueado_ate'] = time() + 300;
        $_SESSION['checkout_tentativas'] = 0;
    }
}

require_once('header.php');
?>

<style>
.finalizar-pagina {
    padding: 2rem 9%;
}

.checkout-form {
    background: #fff;
    padding: 2rem;
    border-radius: .5rem;
    box-shadow: var(--box-shadow);
}

.checkout-alert {
    background: #fff3f3;
    border: 1px solid #ffc9c9;
    color: #9f1d1d;
    border-radius: .6rem;
    padding: 1.2rem 1.5rem;
    margin-bottom: 1.5rem;
    font-size: 1.4rem;
}

.checkout-alert strong {
    display: block;
    margin-bottom: .6rem;
}

.checkout-alert p {
    margin: .2rem 0;
    color: inherit;
}

.checkout-form .flex {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.checkout-form .flex .inputBox {
    flex: 1 1 45rem;
}

.checkout-form .flex .inputBox span {
    font-size: 1.7rem;
    color: var(--light-color);
}

.checkout-form .flex .inputBox input,
.checkout-form .flex .inputBox select,
.checkout-form .flex .inputBox textarea {
    width: 100%;
    background: #eee;
    border-radius: .5rem;
    padding: 1.2rem 1.4rem;
    font-size: 1.6rem;
    color: var(--black);
    text-transform: none;
    margin-top: .5rem;
    border: none;
}

.resumo-pedido {
    margin-top: 2rem;
    border-top: .1rem solid rgba(0,0,0,.1);
    padding-top: 2rem;
}

.resumo-pedido h3 {
    font-size: 2.2rem;
    color: var(--black);
    margin-bottom: 1.5rem;
}

.resumo-pedido p {
    font-size: 1.6rem;
    color: var(--light-color);
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.total-final {
    font-size: 2.2rem;
    color: var(--red);
    text-align: right;
    margin-top: 1rem;
}

.checkout-form .btn {
    width: 100%;
    margin-top: 2rem;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.criar-conta-checkout {
    background: linear-gradient(135deg, #0f3460, #16213e);
    border-radius: 1.2rem;
    padding: 2rem 2.5rem;
    margin: 2rem 0;
    border: 1px solid rgba(255,255,255,0.08);
    box-shadow: 0 8px 25px rgba(15, 52, 96, 0.25);
    animation: fadeIn 0.4s ease;
}

.toggle-criar-conta {
    display: flex;
    align-items: center;
    gap: 1.4rem;
    cursor: pointer;
    padding: 0.5rem 0;
}

.toggle-criar-conta input[type="checkbox"] {
    width: 2.2rem;
    height: 2.2rem;
    cursor: pointer;
    accent-color: #ea1d2c;
    flex-shrink: 0;
    margin: 0;
}

.toggle-icon {
    font-size: 2.2rem;
    line-height: 1;
}

.toggle-texto {
    font-size: 1.7rem;
    font-weight: 700;
    color: #fff;
    line-height: 1.3;
}

.toggle-criar-conta.ativa .toggle-texto {
    color: #f1c40f;
}

.criar-conta-desc {
    font-size: 1.4rem;
    color: #a0b8d8;
    line-height: 1.6;
    margin: 0 0 0.5rem 0;
    display: block !important;
}

.criar-conta-desc strong {
    color: #f1c40f;
}

#campo-senha-nova .inputBox span {
    color: #c8d8ea;
    font-size: 1.5rem;
}

#campo-senha-nova .inputBox input {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    border-radius: .8rem;
    padding: 1.2rem 1.5rem;
    font-size: 1.5rem;
    margin-top: 0.6rem;
    width: 100%;
    transition: border-color 0.3s;
}

#campo-senha-nova .inputBox input::placeholder {
    color: rgba(255,255,255,0.4);
}

#campo-senha-nova .inputBox input:focus {
    outline: none;
    border-color: #ea1d2c;
    background: rgba(255,255,255,0.15);
}
</style>

<div class="topo-pagina">
	<h1>Finalizar Pedido</h1>
	<p><a href="<?= HOME ?>" title="">Inicio >> </a>Finalizar</p>
</div>

<section class="finalizar-pagina">
    <form action="" method="post" class="checkout-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['checkout_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        <?php if (!empty($checkoutErros)): ?>
            <div class="checkout-alert">
                <strong>Revise os dados do pedido:</strong>
                <?php foreach ($checkoutErros as $erro): ?>
                    <p><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="flex">
            <div class="inputBox">
                <span>Seu Nome</span>
                <input type="text" name="nome" placeholder="Digite seu nome completo" minlength="3" maxlength="90" autocomplete="name" pattern="[A-Za-zÀ-ÿ\s'-]{3,90}" required>
            </div>
            <div class="inputBox">
                <span>Seu E-mail</span>
                <input type="email" name="email" placeholder="seu@email.com" maxlength="120" autocomplete="email" required>
            </div>
            <div class="inputBox">
                <span>Seu Telefone</span>
                <input type="tel" name="telefone" id="checkout_telefone" placeholder="(00) 00000-0000" inputmode="numeric" autocomplete="tel" minlength="14" maxlength="15" pattern="\(\d{2}\)\s\d{4,5}-\d{4}" required>
            </div>
            <div class="inputBox">
                <span>Seu Endereço</span>
                <textarea name="endereco" placeholder="Nome da rua e cidade" minlength="5" maxlength="180" autocomplete="street-address" required></textarea>
            </div>
            <div class="inputBox">
                <span>Seu Bairro</span>
                <select name="bairro" id="select_bairro" required onchange="atualizarTotalComTaxa()">
                    <option value="" selected disabled>Selecione seu bairro...</option>
                    <?php foreach($bairrosDb as $b): ?>
                        <option value="<?= htmlspecialchars($b['nome_bairro']) ?>" data-taxa="<?= $b['taxa'] ?>">
                            <?= htmlspecialchars($b['nome_bairro']) ?> (Taxa: R$ <?= number_format($b['taxa'], 2, ',', '.') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="inputBox">
                <span>Número da Residência</span>
                <input type="text" name="numero" id="checkout_numero" placeholder="Ex: 123 ou S/N" maxlength="6" pattern="(\d{1,6}|[sS]\/?[nN])" required>
            </div>
            <div class="inputBox">
                <select name="pagamento" id="metodo_pagamento" onchange="verificarEntrega(this.value)" required>
                    <option value="" selected disabled>Selecione a forma de pagamento...</option>
                    <option value="Pagamento Online">Pagamento Online (Cartão de Crédito / Débito)</option>
                    <option value="Dinheiro">Pagar na Entrega: Dinheiro / Pix</option>
                    <option value="Cartao Entrega">Pagar na Entrega: Cartão (Maquininha)</option>
                </select>
            </div>
            <div class="inputBox" id="campo_troco" style="display:none;">
                <span>Precisa de troco para quanto?</span>
                <input type="text" name="troco" id="checkout_troco" placeholder="Ex: R$ 50,00" inputmode="decimal" maxlength="12">
            </div>
            <div class="inputBox" id="campo_maquininha" style="display:none;">
                <span>Qual bandeira/tipo de cartão?</span>
                <input type="text" name="bandeira" placeholder="Ex: Visa Débito, Master Crédito...">
            </div>
        </div>

        <script>
            function verificarEntrega(valor) {
                document.getElementById('campo_troco').style.display = (valor === 'Dinheiro') ? 'block' : 'none';
                document.getElementById('campo_maquininha').style.display = (valor === 'Cartao Entrega') ? 'block' : 'none';
            }

            document.addEventListener('input', function(e) {
                if (e.target && e.target.id === 'checkout_telefone') {
                    let digits = e.target.value.replace(/\D/g, '').slice(0, 11);
                    if (digits.length > 10) {
                        e.target.value = digits.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3');
                    } else if (digits.length > 6) {
                        e.target.value = digits.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
                    } else if (digits.length > 2) {
                        e.target.value = digits.replace(/^(\d{2})(\d{0,5}).*/, '($1) $2');
                    } else {
                        e.target.value = digits;
                    }
                }

                if (e.target && e.target.id === 'checkout_numero') {
                    const raw = e.target.value.toUpperCase();
                    if (raw === 'S' || raw === 'SN' || raw === 'S/N') {
                        e.target.value = raw === 'SN' ? 'S/N' : raw;
                    } else {
                        e.target.value = raw.replace(/\D/g, '').slice(0, 6);
                    }
                }

                if (e.target && e.target.id === 'checkout_troco') {
                    e.target.value = e.target.value.replace(/[^\d,\.]/g, '').slice(0, 12);
                }
            });
        </script>

        <?php
        $usuario_id = $checkoutCliente ? $checkoutCliente['id'] : null;
        $saldo_cashback = 0.00;
        $pontos_acumulados = 0;
        if ($usuario_id) {
            $fidelidade = new FidelidadeService();
            $carteira = $fidelidade->getCarteira($usuario_id);
            if ($carteira) {
                $saldo_cashback = (float)$carteira['saldo_cashback'];
                $pontos_acumulados = (int)$carteira['pontos_acumulados'];
            }
        }
        
        if ($usuario_id && $saldo_cashback > 0):
        ?>
        <div class="cashback-container" style="background: linear-gradient(135deg, #130f40, #1a1a2e); color: #fff; padding: 2rem; border-radius: 1rem; margin-top: 2rem; margin-bottom: 2.5rem; box-shadow: 0 8px 20px rgba(19, 15, 64, 0.15); font-family: 'Outfit', sans-serif;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin: 0; font-size: 2rem; color: #f39c12; font-weight: 700; display: flex; align-items: center; gap: 1rem;">
                    <i class="fas fa-crown"></i> Clube Fidelidade
                </h3>
                <span style="font-size: 1.2rem; background: rgba(255,255,255,0.15); padding: 0.4rem 1rem; border-radius: 5rem; font-weight: 600;">
                    <i class="fas fa-star" style="color:#f1c40f;"></i> <?= $pontos_acumulados ?> Pontos
                </span>
            </div>
            <p style="font-size: 1.5rem; margin: 0 0 1.5rem 0; line-height: 1.4; color: #eee;">
                Olá, <strong><?= htmlspecialchars($checkoutCliente['nome'] ?? 'Cliente') ?></strong>! Você tem <strong>R$ <?= number_format($saldo_cashback, 2, ',', '.') ?></strong> de saldo de cashback acumulado para utilizar como desconto.
            </p>
            <label class="cashback-toggle-label" style="display: flex; align-items: center; gap: 1.2rem; background: rgba(255,255,255,0.08); padding: 1.2rem 1.5rem; border-radius: .8rem; cursor: pointer; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.1); width: 100%;">
                <input type="checkbox" id="usar_cashback_chk" name="usar_cashback" value="1" onchange="toggleCashback(this.checked)" style="width: 2rem; height: 2rem; cursor: pointer; accent-color: #ea1d2c;">
                <span style="font-size: 1.5rem; font-weight: 600; color: #fff;">Usar meu cashback como desconto nesta compra!</span>
            </label>
        </div>
        <?php endif; ?>

        <!-- BLOCO: CRIAR CONTA OPCIONAL -->
        <?php if(!$checkoutClienteLogado): ?>
        <div class="criar-conta-checkout" id="bloco-criar-conta">
            <label class="toggle-criar-conta" id="label-toggle-conta">
                <input type="checkbox" id="chk_criar_conta" name="criar_conta" value="1" onchange="toggleCriarConta(this.checked)">
                <span class="toggle-icon">🎉</span>
                <span class="toggle-texto">Criar conta e ganhar Cashback nesta compra!</span>
            </label>
            
            <div id="campo-senha-nova" style="display:none; margin-top: 1.5rem;">
                <p class="criar-conta-desc">Ao criar sua conta, você acumula <strong><?= rtrim(rtrim(number_format($taxa_cashback_dyn, 2, ',', '.'), '0'), ',') ?>% de cashback</strong> deste pedido para usar na próxima compra no <strong>Clube Fidelidade</strong>!</p>
                <div class="inputBox" style="margin-top:1rem;">
                    <span>Crie uma senha para sua conta</span>
                    <input type="password" name="senha_nova" id="senha_nova" placeholder="Mínimo 6 caracteres" minlength="6" autocomplete="new-password" value="">
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="resumo-pedido">
            <h3>Resumo do Pedido</h3>
            <div class="checkout-itens-lista" style="display: flex; flex-direction: column; gap: 1.5rem; margin-bottom: 2rem;">
            <?php
            $total = 0;
            foreach($_SESSION['carrinho'] as $id => $qtd):
                if (strpos((string)$id, 'custom_') === 0) {
                    // É uma pizza customizada
                    $customPizza = isset($_SESSION['custom_pizzas'][$id]) ? $_SESSION['custom_pizzas'][$id] : null;
                    $nome = $customPizza ? $customPizza['nome'] : "Pizza Personalizada";
                    $detalhes = $customPizza ? $customPizza['detalhes'] : "Montada no site";
                    $preco = $customPizza ? $customPizza['preco'] : 59.90;
                    $imagem = $customPizza ? $customPizza['imagem'] : "pizza-1.png";
                    
                    $subtotal = $preco * $qtd;
                    $total += $subtotal;
            ?>
                <div class="checkout-item" style="display: flex; gap: 1.5rem; align-items: center; background: #fafafa; padding: 1rem; border-radius: .5rem; border: 1px solid #eee;">
                    <img src="<?= mondiniTemaImagemUrl('loja/' . $imagem) ?>" alt="<?= $nome ?>" loading="lazy" decoding="async" style="width: 6rem; height: 6rem; object-fit: cover; border-radius: 50%; border: 2px solid #ddd;">
                    <div style="flex: 1;">
                        <h4 style="font-size: 1.6rem; color: var(--black); margin: 0;"><?= $nome ?> x <?= $qtd ?></h4>
                        <p style="font-size: 1.2rem; color: #666; font-style: italic; margin: .3rem 0 0 0; line-height: 1.4; display: block;"><?= $detalhes ?></p>
                    </div>
                    <span style="font-size: 1.6rem; font-weight: bold; color: #27ae60;">R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
                </div>
            <?php
                } else {
                    // Produto normal
                    $produtoNormal = isset($_SESSION['produtos_normais'][$id]) ? $_SESSION['produtos_normais'][$id] : null;
                    $preco = 49.90;
                    $nome = "Pizza Tradicional";
                    $imagem = "pizza-1.png";
                    
                    if($produtoNormal){
                        $nome = $produtoNormal['nome'];
                        $preco = $produtoNormal['preco'];
                        $imagem = $produtoNormal['imagem'];
                    } else {
                        $sheep->Leitura('produtos', "WHERE id = :id", "id={$id}");
                        $produto = $sheep->getResultado();
                        if($produto){
                            $produto = $produto[0];
                            $nome = $produto['nome'];
                            $preco = $produto['preco_promocional'] ? $produto['preco_promocional'] : $produto['preco'];
                            $imagem = $produto['imagem'];
                        }
                    }
                    $subtotal = $preco * $qtd;
                    $total += $subtotal;
            ?>
                <div class="checkout-item" style="display: flex; gap: 1.5rem; align-items: center; background: #fafafa; padding: 1rem; border-radius: .5rem; border: 1px solid #eee;">
                    <img src="<?= mondiniTemaImagemUrl('loja/' . $imagem) ?>" alt="<?= $nome ?>" loading="lazy" decoding="async" style="width: 6rem; height: 6rem; object-fit: cover; border-radius: 50%; border: 2px solid #ddd;">
                    <div style="flex: 1;">
                        <h4 style="font-size: 1.6rem; color: var(--black); margin: 0;"><?= $nome ?> x <?= $qtd ?></h4>
                        <p style="font-size: 1.2rem; color: #666; margin: .3rem 0 0 0; display: block;">Preço unitário: R$ <?= number_format($preco, 2, ',', '.') ?></p>
                    </div>
                    <span style="font-size: 1.6rem; font-weight: bold; color: #27ae60;">R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
                </div>
            <?php 
                }
            endforeach; 
            ?>
            </div>

            <!-- Linha da Taxa de Entrega -->
            <div id="row-taxa-entrega" style="display: flex; justify-content: space-between; font-size: 1.6rem; margin-bottom: 1.5rem; color: #7f8c8d; font-weight: 500; padding: 0 1rem;">
                <span>Taxa de Entrega (<span id="nome-bairro-resumo">Nenhum</span>)</span>
                <span id="valor-taxa-entrega">+ R$ 0,00</span>
            </div>

            <!-- Linha de Desconto de Cashback (Oculta por padrão) -->
            <div id="row-desconto-cashback" style="display: none; justify-content: space-between; font-size: 1.6rem; margin-bottom: 1.5rem; color: #e74c3c; font-weight: 600; padding: 0 1rem;">
                <span>Desconto Clube Fidelidade (Cashback)</span>
                <span id="valor-desconto-cashback">- R$ 0,00</span>
            </div>

            <h4 class="total-final" id="total-final-exibir">Total a Pagar: R$ <?= number_format($total, 2, ',', '.') ?></h4>
        </div>

        <input type="hidden" name="mp_token" id="mp_token">
        <input type="hidden" name="mp_payment_method_id" id="mp_payment_method_id">
        <input type="hidden" name="mp_issuer_id" id="mp_issuer_id">
        <input type="hidden" name="mp_installments" id="mp_installments">
        <input type="hidden" name="mp_payer_email" id="mp_payer_email">
        <input type="hidden" name="mp_payer_id_type" id="mp_payer_id_type">
        <input type="hidden" name="mp_payer_id_number" id="mp_payer_id_number">

        <input type="submit" id="btn_submit_normal" name="finalizar" value="Confirmar Pedido e Pagar" class="btn">
    </form>
</section>

<!-- TELA NOVA DO MERCADO PAGO (Estilo AJAX) -->
<div id="mp-fullscreen" style="display:none; max-width: 600px; margin: 0 auto; background: #fff; padding: 3rem; border-radius: 1rem; box-shadow: var(--box-shadow); animation: fadeIn 0.5s ease;">
    <h3 style="font-size:2.4rem; margin-bottom:2rem; text-align:center; color:var(--black);">Digite os dados do Cartão</h3>
    <div id="paymentBrick_container"></div>
    <div style="text-align:center; margin-top: 2rem;">
        <a href="javascript:voltarCheckout();" style="font-size: 1.6rem; color: #666; text-decoration: underline;">&laquo; Voltar para revisar os dados</a>
    </div>
</div>

<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
    const totalOriginalProdutos = <?= (float)$total ?>;
    const saldoCashback = <?= (float)$saldo_cashback ?>;
    let taxaEntregaAtual = 0.00;

    function obterTotalAPagar() {
        let base = totalOriginalProdutos + taxaEntregaAtual;
        const checkbox = document.getElementById('usar_cashback_chk');
        if (checkbox && checkbox.checked) {
            return Math.max(0, base - saldoCashback);
        }
        return base;
    }

    function atualizarTotalComTaxa() {
        const selectBairro = document.getElementById('select_bairro');
        if(selectBairro.selectedIndex > 0) {
            const opt = selectBairro.options[selectBairro.selectedIndex];
            taxaEntregaAtual = parseFloat(opt.getAttribute('data-taxa')) || 0;
            document.getElementById('nome-bairro-resumo').innerText = opt.value;
            document.getElementById('valor-taxa-entrega').innerText = '+ R$ ' + taxaEntregaAtual.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        } else {
            taxaEntregaAtual = 0;
            document.getElementById('nome-bairro-resumo').innerText = 'Nenhum';
            document.getElementById('valor-taxa-entrega').innerText = '+ R$ 0,00';
        }
        
        // Dispara o recálculo do cashback e atualiza a interface
        const isCashbackChecked = document.getElementById('usar_cashback_chk') ? document.getElementById('usar_cashback_chk').checked : false;
        toggleCashback(isCashbackChecked);
    }

    function toggleCashback(checked) {
        const rowDesconto = document.getElementById('row-desconto-cashback');
        const valorDesconto = document.getElementById('valor-desconto-cashback');
        const totalExibir = document.getElementById('total-final-exibir');
        
        let subtotalComTaxa = totalOriginalProdutos + taxaEntregaAtual;
        let totalAPagar = subtotalComTaxa;
        
        if (checked) {
            let desconto = Math.min(subtotalComTaxa, saldoCashback);
            totalAPagar = Math.max(0, subtotalComTaxa - desconto);
            
            rowDesconto.style.display = 'flex';
            valorDesconto.innerText = '- R$ ' + desconto.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        } else {
            rowDesconto.style.display = 'none';
        }
        
        totalExibir.innerText = 'Total a Pagar: R$ ' + totalAPagar.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        
        // Se zerar o total, ativa lógica especial de Cashback Total
        if (totalAPagar <= 0) {
            document.getElementById('metodo_pagamento').value = 'Pagamento Online';
            document.getElementById('metodo_pagamento').setAttribute('disabled', 'true');
            verificarEntrega('Pagamento Online');
            
            // Cria input hidden se não existir
            if (!document.getElementById('hidden_pagamento_zero')) {
                let hiddenPay = document.createElement('input');
                hiddenPay.type = 'hidden';
                hiddenPay.name = 'pagamento';
                hiddenPay.value = 'Pagamento Online';
                hiddenPay.id = 'hidden_pagamento_zero';
                document.querySelector('.checkout-form').appendChild(hiddenPay);
            }
            document.getElementById('btn_submit_normal').value = "Finalizar Pedido Totalmente Grátis!";
        } else {
            document.getElementById('metodo_pagamento').removeAttribute('disabled');
            let hiddenPay = document.getElementById('hidden_pagamento_zero');
            if (hiddenPay) hiddenPay.remove();
            document.getElementById('btn_submit_normal').value = "Confirmar Pedido e Pagar";
        }
    }

    const mp = new MercadoPago('<?= $mp_public_key_dyn ?>', {
        locale: 'pt-BR'
    });
    const bricksBuilder = mp.bricks();

    const renderCardPaymentBrick = async (bricksBuilder) => {
        let amountToCharge = obterTotalAPagar();
        const settings = {
            initialization: {
                amount: parseFloat(amountToCharge.toFixed(2)), // valor total
            },
            customization: {
                visual: {
                    style: {
                        theme: 'default',
                    }
                },
                paymentMethods: {
                    maxInstallments: 1
                },
            },
            callbacks: {
                onReady: () => {
                },
                onSubmit: ({ selectedPaymentMethod, formData }) => {
                    return new Promise((resolve, reject) => {
                        document.getElementById('mp_token').value = formData.token || "";
                        document.getElementById('mp_payment_method_id').value = formData.payment_method_id || "";
                        document.getElementById('mp_issuer_id').value = formData.issuer_id || "";
                        document.getElementById('mp_installments').value = formData.installments || 1;
                        if(formData.payer){
                            document.getElementById('mp_payer_email').value = formData.payer.email || "";
                            if(formData.payer.identification){
                                document.getElementById('mp_payer_id_type').value = formData.payer.identification.type || "";
                                document.getElementById('mp_payer_id_number').value = formData.payer.identification.number || "";
                            }
                        }
                        
                        // Enviamos o formulário principal usando o HTMLFormElement original
                        HTMLFormElement.prototype.submit.call(document.querySelector('.checkout-form'));
                        resolve();
                    });
                },
                onError: (error) => {
                    console.error(error);
                }
            },
        };
        window.cardPaymentBrickController = await bricksBuilder.create('cardPayment', 'paymentBrick_container', settings);
    };
    
    // Interceptar o submit do formulário
    document.querySelector('.checkout-form').addEventListener('submit', function(e) {
        const metodo = document.getElementById('metodo_pagamento').value;
        const mpToken = document.getElementById('mp_token').value;
        
        // Se o total a pagar for zero, deixa enviar direto!
        if (obterTotalAPagar() <= 0) {
            return;
        }
        
        if (metodo === 'Pagamento Online' && mpToken === '') {
            e.preventDefault(); // Impede o envio
            
            // Esconde o formulário atual e mostra a tela do cartão com transição suave
            document.querySelector('.checkout-form').style.display = 'none';
            document.querySelector('.topo-pagina h1').innerText = 'Pagamento Seguro';
            document.getElementById('mp-fullscreen').style.display = 'block';
            window.scrollTo(0, 0);
 
            // Renderiza o brick se ainda não foi renderizado
            if(!window.cardPaymentBrickController && typeof bricksBuilder !== 'undefined'){
                renderCardPaymentBrick(bricksBuilder);
            }
        }
    });

    function voltarCheckout() {
        if (window.cardPaymentBrickController) {
            window.cardPaymentBrickController.unmount();
            window.cardPaymentBrickController = null;
        }
        document.getElementById('mp-fullscreen').style.display = 'none';
        document.querySelector('.checkout-form').style.display = 'block';
        document.querySelector('.topo-pagina h1').innerText = 'Finalizar Pedido';
    }

    window.onload = function() {
        verificarEntrega(document.getElementById('metodo_pagamento').value);
    }

    function toggleCriarConta(checked) {
        const campo = document.getElementById('campo-senha-nova');
        const label = document.getElementById('label-toggle-conta');
        const senhaInput = document.getElementById('senha_nova');
        if (checked) {
            campo.style.display = 'block';
            label.classList.add('ativa');
            senhaInput.setAttribute('required', 'required');
        } else {
            campo.style.display = 'none';
            label.classList.remove('ativa');
            senhaInput.removeAttribute('required');
        }
    }
</script>

<?php
require_once('footer.php');
?>
