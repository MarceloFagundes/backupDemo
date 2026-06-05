<?php
require_once('header.php');
$pedidoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

$token = isset($_GET['token']) ? $_GET['token'] : '';

// Limpar o carrinho agora que o pedido foi gerado com sucesso
unset($_SESSION['carrinho']);
unset($_SESSION['custom_pizzas']);
unset($_SESSION['produtos_normais']);

// Capturar pagamento do PayPal via REST API se tiver token
if($pedidoId > 0 && !empty($token)){
    // 1. Obter Token de Acesso do PayPal
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api-m.sandbox.paypal.com/v1/oauth2/token'); // Mudar para api-m.paypal.com em prod
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $headers = array();
    $headers[] = 'Accept: application/json';
    $headers[] = 'Accept-Language: en_US';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    curl_close($ch);
    
    $json = json_decode($result);
    $access_token = $json->access_token ?? '';

    if($access_token){
        // 2. Capturar a Ordem
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, 'https://api-m.sandbox.paypal.com/v2/checkout/orders/' . $token . '/capture');
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch2, CURLOPT_POST, 1);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, false);
        $headers2 = array();
        $headers2[] = 'Content-Type: application/json';
        $headers2[] = 'Authorization: Bearer ' . $access_token;
        curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers2);
        $result2 = curl_exec($ch2);
        $http_code = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);

        if ($http_code == 201 || $http_code == 200) {
            $status = 'pago'; // Força o status para pago se capturou com sucesso
        } else {
            $_GET['erro'] = 'paypal_capture';
        }
    }
}

// Atualiza o banco se vier do PayPal (pago)
if($pedidoId > 0 && $status == 'pago'){
    $dadosUpdate = ['status' => 'pago'];
    $atualizar = new Atualizar();
    $atualizar->Atualizando('pedidos', $dadosUpdate, "WHERE id = :id", "id={$pedidoId}");
}

// Buscar dados iniciais do pedido para renderizar
$pedidoValido = false;
$pedidoDados = null;
$itensPedido = [];

if ($pedidoId > 0) {
    $lerPed = new Ler();
    $lerPed->Leitura('pedidos', "WHERE id = :id", "id={$pedidoId}");
    if ($lerPed->getResultado()) {
        $pedidoDados = $lerPed->getResultado()[0];
        $pedidoValido = true;
        
        // Buscar itens do pedido
        $lerItens = new Ler();
        $lerItens->Leitura('itens_pedido', "WHERE pedido_id = :pid", "pid={$pedidoId}");
        if ($lerItens->getResultado()) {
            $itensPedido = $lerItens->getResultado();
        }
    }
}
?>

<!-- GOOGLE FONTS & FONTAWESOME -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
:root {
    --primary-red: #ea1d2c;
    --dark-blue: #130f40;
    --emerald: #27ae60;
    --amber: #f39c12;
    --gray-bg: #f8f9fa;
    --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    --border-radius: 1.5rem;
    --text-muted: #777;
    --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.sucesso-section {
    background: var(--gray-bg);
    padding: 5rem 9% 8rem 9%;
    font-family: 'Outfit', sans-serif;
    min-height: 80vh;
}

.sucesso-container {
    max-width: 1100px;
    margin: 0 auto;
}

/* Header de Sucesso */
.sucesso-header {
    text-align: center;
    margin-bottom: 4rem;
}

.sucesso-header .icon-success-box {
    width: 9rem;
    height: 9rem;
    background: #e8f8f0;
    color: var(--emerald);
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 4.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 20px rgba(39, 174, 96, 0.1);
    animation: scaleIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.sucesso-header h1 {
    font-size: 3.6rem;
    color: var(--dark-blue);
    font-weight: 700;
    margin-bottom: 1rem;
}

.sucesso-header p {
    font-size: 1.8rem;
    color: var(--text-muted);
}

/* CARD DO RASTREADOR (TRACKER) */
.tracker-card {
    background: #fff;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    padding: 4rem;
    margin-bottom: 4rem;
    border: 1px solid rgba(0, 0, 0, 0.03);
    position: relative;
    overflow: hidden;
}

.tracker-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, var(--primary-red), var(--amber));
}

.tracker-title-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px dashed #eee;
    padding-bottom: 2rem;
    margin-bottom: 3.5rem;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.tracker-id-badge {
    background: var(--dark-blue);
    color: #fff;
    font-weight: 700;
    padding: 0.6rem 1.6rem;
    border-radius: 5rem;
    font-size: 1.5rem;
}

.tracker-time-eta {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 1.6rem;
    color: var(--text-muted);
}

.tracker-time-eta strong {
    color: var(--dark-blue);
    font-size: 1.8rem;
}

/* STEPPER DESIGN */
.stepper-wrapper {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-bottom: 4rem;
    padding: 0 2rem;
}

.stepper-line-bg {
    position: absolute;
    top: 2.5rem;
    left: 4rem;
    right: 4rem;
    height: 6px;
    background: #e0e0e0;
    z-index: 1;
    border-radius: 10px;
}

.stepper-line-progress {
    position: absolute;
    top: 2.5rem;
    left: 4rem;
    height: 6px;
    background: linear-gradient(90deg, var(--primary-red), var(--emerald));
    z-index: 2;
    border-radius: 10px;
    width: 0%;
    transition: width 1s ease-in-out;
}

.step-node {
    position: relative;
    z-index: 3;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    width: 12rem;
}

.step-icon-outer {
    width: 5.6rem;
    height: 5.6rem;
    border-radius: 50%;
    background: #fff;
    border: 3px solid #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
    color: #a0a0a0;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
    transition: var(--transition);
}

.step-node.completed .step-icon-outer {
    border-color: var(--emerald);
    background: var(--emerald);
    color: #fff;
    box-shadow: 0 8px 15px rgba(39, 174, 96, 0.2);
}

.step-node.active .step-icon-outer {
    border-color: var(--primary-red);
    background: #fff;
    color: var(--primary-red);
    box-shadow: 0 8px 20px rgba(234, 29, 44, 0.25);
    transform: scale(1.15);
    animation: pulseActive 2s infinite;
}

.step-label {
    font-size: 1.4rem;
    font-weight: 600;
    color: #888;
    transition: var(--transition);
}

.step-node.active .step-label {
    color: var(--primary-red);
    font-weight: 700;
}

.step-node.completed .step-label {
    color: var(--dark-blue);
}

/* DESCRIÇÃO DE STATUS DINÂMICO */
.status-desc-box {
    background: #fafafa;
    border-radius: 1.2rem;
    padding: 2.2rem 3rem;
    display: flex;
    align-items: center;
    gap: 2rem;
    border-left: 5px solid var(--primary-red);
}

.status-desc-box .status-pulse-dot {
    width: 1.5rem;
    height: 1.5rem;
    background: var(--primary-red);
    border-radius: 50%;
    position: relative;
}

.status-desc-box .status-pulse-dot::after {
    content: '';
    position: absolute;
    top: -0.6rem;
    left: -0.6rem;
    right: -0.6rem;
    bottom: -0.6rem;
    border: 3px solid rgba(234, 29, 44, 0.4);
    border-radius: 50%;
    animation: pulseDot 1.5s infinite;
}

.status-desc-box.completed-status {
    border-left-color: var(--emerald);
}

.status-desc-box.completed-status .status-pulse-dot {
    background: var(--emerald);
}

.status-desc-box.completed-status .status-pulse-dot::after {
    border-color: rgba(39, 174, 96, 0.4);
}

.status-desc-text {
    font-size: 1.6rem;
    font-weight: 500;
    color: var(--dark-blue);
    line-height: 1.5;
}

/* DETALHES DO PEDIDO DOIS BLOCOS */
.details-row {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 3rem;
}

.details-card {
    background: #fff;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    padding: 3.5rem;
    border: 1px solid rgba(0, 0, 0, 0.02);
}

.details-card h2 {
    font-size: 2.2rem;
    color: var(--dark-blue);
    margin-bottom: 2.5rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 1.2rem;
}

.details-card h2 i {
    color: var(--primary-red);
}

/* Blocos de Informação do Cliente */
.info-block {
    margin-bottom: 2.2rem;
}

.info-block label {
    font-size: 1.2rem;
    color: var(--text-muted);
    text-transform: uppercase;
    font-weight: 700;
    display: block;
    margin-bottom: 0.5rem;
    letter-spacing: 0.5px;
}

.info-block p {
    font-size: 1.6rem;
    color: var(--dark-blue);
    font-weight: 500;
    margin: 0;
}

/* ITENS DO PEDIDO */
.items-list {
    display: flex;
    flex-direction: column;
    gap: 1.8rem;
}

.item-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid #f3f3f3;
    padding-bottom: 1.5rem;
}

.item-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.item-info {
    flex: 1;
}

.item-info h4 {
    font-size: 1.6rem;
    color: var(--dark-blue);
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.item-info p {
    font-size: 1.2rem;
    color: var(--text-muted);
    line-height: 1.4;
    margin: 0;
}

.item-qty-price {
    text-align: right;
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--dark-blue);
}

.item-qty-price .qty {
    color: var(--primary-red);
    font-size: 1.3rem;
    background: #fdf1f2;
    padding: 0.2rem 0.8rem;
    border-radius: 5rem;
    margin-right: 0.8rem;
}

/* TOTAIS DO CARD */
.total-divider {
    border-top: 2px dashed #eee;
    margin: 2.5rem 0;
}

.total-row {
    display: flex;
    justify-content: space-between;
    font-size: 1.5rem;
    margin-bottom: 1.2rem;
    color: var(--text-muted);
}

.total-row.grand-total {
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--dark-blue);
    margin-top: 1.5rem;
}

.total-row.grand-total .val {
    color: var(--emerald);
}

/* BOTÕES DE SUPORTE */
.actions-footer {
    display: flex;
    gap: 2rem;
    margin-top: 4rem;
    justify-content: center;
}

.btn-suporte-whats {
    display: inline-flex;
    align-items: center;
    gap: 1rem;
    background: #25d366;
    color: #fff;
    padding: 1.5rem 3.5rem;
    border-radius: 5rem;
    font-size: 1.6rem;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 8px 20px rgba(37, 211, 102, 0.2);
    transition: var(--transition);
}

.btn-suporte-whats:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(37, 211, 102, 0.3);
    filter: brightness(1.05);
}

.btn-voltar-home {
    display: inline-flex;
    align-items: center;
    gap: 1rem;
    background: var(--dark-blue);
    color: #fff;
    padding: 1.5rem 3.5rem;
    border-radius: 5rem;
    font-size: 1.6rem;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 8px 20px rgba(19, 15, 64, 0.15);
    transition: var(--transition);
}

.btn-voltar-home:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(19, 15, 64, 0.25);
    filter: brightness(1.15);
}

/* ERRO STYLES */
.sucesso-error {
    text-align: center;
    background: #fff;
    padding: 5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    max-width: 600px;
    margin: 0 auto;
}

.sucesso-error i {
    font-size: 8rem;
    color: #e74c3c;
    margin-bottom: 2rem;
}

.sucesso-error h1 {
    font-size: 3rem;
    color: var(--dark-blue);
    margin-bottom: 1.5rem;
}

.sucesso-error p {
    font-size: 1.6rem;
    color: var(--text-muted);
    margin-bottom: 2.5rem;
}

/* ANIMATIONS */
@keyframes scaleIn {
    0% { transform: scale(0); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

@keyframes pulseActive {
    0% { box-shadow: 0 0 0 0 rgba(234, 29, 44, 0.5); }
    70% { box-shadow: 0 0 0 15px rgba(234, 29, 44, 0); }
    100% { box-shadow: 0 0 0 0 rgba(234, 29, 44, 0); }
}

@keyframes pulseDot {
    0% { transform: scale(0.9); opacity: 0.9; }
    50% { transform: scale(1.4); opacity: 0.3; }
    100% { transform: scale(1.8); opacity: 0; }
}

/* CONFETTI CANVAS */
#confetti-canvas {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 9999;
    pointer-events: none;
}

/* RESPONSIVE DESIGN */
@media (max-width: 991px) {
    .details-row {
        grid-template-columns: 1fr;
    }
    .stepper-wrapper {
        flex-wrap: wrap;
        gap: 3rem;
        justify-content: center;
    }
    .stepper-line-bg, .stepper-line-progress {
        display: none;
    }
    .step-node {
        width: 45%;
    }
}

@media (max-width: 576px) {
    .step-node {
        width: 100%;
    }
    .actions-footer {
        flex-direction: column;
        align-items: stretch;
    }
    .btn-suporte-whats, .btn-voltar-home {
        justify-content: center;
    }
}
</style>

<canvas id="confetti-canvas"></canvas>

<section class="sucesso-section">
    <div class="sucesso-container">

        <?php if(!$pedidoValido): ?>
            <!-- Erro de Pedido Não Encontrado -->
            <div class="sucesso-error">
                <i class="fas fa-exclamation-triangle"></i>
                <h1>Pedido não encontrado!</h1>
                <p>O identificador do pedido é inválido ou não foi localizado em nossa base de dados.</p>
                <a href="<?= HOME ?>/index" class="btn-voltar-home">Voltar para o Início</a>
            </div>
        <?php elseif(isset($_GET['erro']) && $_GET['erro'] == 'mp'): ?>
            <!-- Erro de pagamento do Mercado Pago -->
            <div class="sucesso-error">
                <i class="fas fa-circle-exclamation" style="color:var(--amber);"></i>
                <h1>Aguardando Pagamento!</h1>
                <p>Seu pedido #<?= $pedidoId ?> foi gerado, mas ocorreu um problema ao redirecionar para a plataforma de pagamento.</p>
                <p style="margin-bottom: 2rem;">Por favor, finalize ou valide via WhatsApp com nossa equipe técnica.</p>
                
                <div class="actions-footer" style="flex-direction: row;">
                    <a href="https://api.whatsapp.com/send?phone=5547999999999&text=Ol%C3%A1%2C%20estou%20com%20problema%20no%20pagamento%20do%20pedido%20%23<?= $pedidoId ?>" target="_blank" class="btn-suporte-whats">
                        <i class="fab fa-whatsapp"></i> Falar com Suporte
                    </a>
                    <a href="<?= HOME ?>/index" class="btn-voltar-home">Voltar para a Página Inicial</a>
                </div>
            </div>
        <?php else: ?>

            <!-- HEADER DE SUCESSO -->
            <div class="sucesso-header">
                <div class="icon-success-box">
                    <i class="fas fa-check"></i>
                </div>
                <h1>Pedido Confirmado!</h1>
                <p>Agora você pode acompanhar cada passo da sua pizza em tempo real abaixo.</p>
            </div>

            <!-- PIZZA TRACKER CARD -->
            <div class="tracker-card">
                <div class="tracker-title-row">
                    <div>
                        <span class="tracker-id-badge">Pedido #<?= $pedidoId ?></span>
                    </div>
                    <div class="tracker-time-eta">
                        <i class="far fa-clock"></i>
                        <span>Tempo estimado de entrega: <strong>35 - 50 min</strong></span>
                    </div>
                </div>

                <!-- STEPPER -->
                <div class="stepper-wrapper">
                    <div class="stepper-line-bg"></div>
                    <div class="stepper-line-progress" id="tracker-progress"></div>

                    <!-- STEP 1: RECEBIDO -->
                    <div class="step-node" id="step-recebido">
                        <div class="step-icon-outer">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <span class="step-label">Confirmado</span>
                    </div>

                    <!-- STEP 2: PREPARANDO -->
                    <div class="step-node" id="step-preparando">
                        <div class="step-icon-outer">
                            <i class="fas fa-kitchen-set"></i>
                        </div>
                        <span class="step-label">Na Cozinha</span>
                    </div>

                    <!-- STEP 3: ASSANDO -->
                    <div class="step-node" id="step-assando">
                        <div class="step-icon-outer">
                            <i class="fas fa-fire"></i>
                        </div>
                        <span class="step-label">No Forno</span>
                    </div>

                    <!-- STEP 4: EM ROTA -->
                    <div class="step-node" id="step-rota">
                        <div class="step-icon-outer">
                            <i class="fas fa-motorcycle"></i>
                        </div>
                        <span class="step-label">Saiu para Entrega</span>
                    </div>

                    <!-- STEP 5: ENTREGUE -->
                    <div class="step-node" id="step-entregue">
                        <div class="step-icon-outer">
                            <i class="fas fa-house-chimney-user"></i>
                        </div>
                        <span class="step-label">Entregue</span>
                    </div>
                </div>

                <!-- DESCRIÇÃO DINÂMICA -->
                <div class="status-desc-box" id="tracker-desc-box">
                    <div class="status-pulse-dot"></div>
                    <div class="status-desc-text" id="tracker-desc-text">
                        Carregando informações do pedido...
                    </div>
                </div>
            </div>

            <!-- DETALHES DE RESUMO (DUAS COLUNAS) -->
            <div class="details-row">
                
                <!-- COLUNA 1: DADOS DE ENTREGA -->
                <div class="details-card">
                    <h2><i class="fas fa-location-dot"></i> Informações de Entrega</h2>
                    
                    <div class="info-block">
                        <label>Cliente</label>
                        <p><?= htmlspecialchars($pedidoDados['cliente_nome']) ?></p>
                    </div>

                    <div class="info-block">
                        <label>Endereço de Entrega</label>
                        <p><?= htmlspecialchars($pedidoDados['cliente_endereco']) ?></p>
                    </div>

                    <div class="info-block">
                        <label>Forma de Pagamento</label>
                        <p><span class="badge badge-pill" style="background:#e0e0e0; color:var(--dark-blue); font-size:1.3rem; padding:0.5rem 1.2rem; font-weight:600; text-transform:uppercase;"><?= htmlspecialchars($pedidoDados['forma_pagamento']) ?></span></p>
                    </div>
                    
                    <div class="info-block" style="margin-bottom:0; margin-top:3rem;">
                        <span style="font-size: 1.4rem; color: var(--text-muted); display: block; line-height: 1.5;">
                            Precisa alterar algo ou adicionar alguma instrução ao motoboy? Nos mande uma mensagem!
                        </span>
                    </div>
                </div>

                <!-- COLUNA 2: ITENS DA PIZZA -->
                <div class="details-card">
                    <h2><i class="fas fa-pizza-slice"></i> Resumo da Compra</h2>
                    
                    <div class="items-list">
                        <?php if(!empty($itensPedido)): foreach($itensPedido as $item): 
                            if ($item['produto_id'] == 0) {
                                $nomeProduto = "Pizza Personalizada";
                                $detalhesItem = $item['detalhes'];
                            } else {
                                $lerP = new Ler();
                                $lerP->Leitura('produtos', "WHERE id = :id", "id={$item['produto_id']}");
                                $nomeProduto = $lerP->getResultado() ? $lerP->getResultado()[0]['nome'] : 'Produto';
                                $detalhesItem = "";
                            }
                        ?>
                            <div class="item-row">
                                <div class="item-info">
                                    <h4><?= htmlspecialchars($nomeProduto) ?></h4>
                                    <?php if(!empty($detalhesItem)): ?>
                                        <p><?= htmlspecialchars($detalhesItem) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="item-qty-price">
                                    <span class="qty">x<?= $item['quantidade'] ?></span>
                                    <span>R$ <?= number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.') ?></span>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>

                    <div class="total-divider"></div>

                    <div class="total-row">
                        <span>Subtotal</span>
                        <span>R$ <?= number_format($pedidoDados['valor_total'], 2, ',', '.') ?></span>
                    </div>
                    <div class="total-row">
                        <span>Taxa de Entrega</span>
                        <span style="color:var(--emerald); font-weight:600;">Grátis</span>
                    </div>
                    
                    <div class="total-divider"></div>
                    
                    <div class="total-row grand-total" style="margin-bottom: 0.5rem;">
                        <span>Total Pago</span>
                        <span class="val">R$ <?= number_format($pedidoDados['valor_total'], 2, ',', '.') ?></span>
                    </div>
                    <?php
                    // Calcular ganhos de cashback e pontos dinamicamente
                    $lerConfig = new Ler();
                    $lerConfig->Leitura('configuracoes', "WHERE id = :id", "id=1");
                    $taxaCashback = 0.05;
                    if ($lerConfig->getResultado()) {
                        $configData = $lerConfig->getResultado()[0];
                        $taxaCashback = isset($configData['porcentagem_cashback']) ? (float)$configData['porcentagem_cashback'] / 100 : 0.05;
                    }
                    $cashbackGanho = round($pedidoDados['valor_total'] * $taxaCashback, 2);
                    $pontosGanhos = (int) floor($pedidoDados['valor_total']);
                    ?>
                    <div class="cashback-summary" style="margin-top: 1rem; font-size: 1.3rem; background: #f4fbf7; border: 1px solid #e1f5eb; border-radius: 0.8rem; padding: 1rem 1.5rem; width: 100%; box-sizing: border-box;">
                        <p style="margin: 0; display: flex; align-items: center; gap: 0.8rem; color: #27ae60; font-weight: 500; line-height: 1.4;">
                            <i class="fas fa-gift" style="font-size: 1.5rem; color: #2ecc71;"></i>
                            <span>Parabéns! Você recebeu <strong style="font-weight: 700; color: #219653;">R$ <?= number_format($cashbackGanho, 2, ',', '.') ?></strong> de cashback e <strong style="font-weight: 700; color: #219653;"><?= $pontosGanhos ?> pontos</strong> no Clube Fidelidade.</span>
                        </p>
                    </div>
                </div>

            </div>

            <!-- ACTIONS FOOTER -->
            <div class="actions-footer">
                <a href="https://api.whatsapp.com/send?phone=5547999999999&text=Ol%C3%A1%2C%20gostaria%20de%20saber%20do%20meu%20pedido%20%23<?= $pedidoId ?>%20da%20Modelo" target="_blank" class="btn-suporte-whats">
                    <i class="fab fa-whatsapp"></i> Conversar no WhatsApp
                </a>
                <a href="<?= HOME ?>/index" class="btn-voltar-home">Ir para o Cardápio</a>
            </div>

        <?php endif; ?>

    </div>
</section>


<!-- SCRIPTS E INTEGRAÇÃO EM TEMPO REAL -->
<script>
var lastStatus = '';
var checkCount = 0;

// Função para sintetizar um tom musical agradável nativo (Chime/Ding) via Web Audio API
function playStatusChime() {
    try {
        var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        
        // Tom 1
        var osc1 = audioCtx.createOscillator();
        var gain1 = audioCtx.createGain();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(523.25, audioCtx.currentTime); // Nota Dó (C5)
        gain1.gain.setValueAtTime(0.08, audioCtx.currentTime);
        gain1.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.4);
        osc1.connect(gain1);
        gain1.connect(audioCtx.destination);
        osc1.start();
        osc1.stop(audioCtx.currentTime + 0.4);
        
        // Tom 2 (ligeiramente atrasado para harmonia)
        setTimeout(function() {
            var osc2 = audioCtx.createOscillator();
            var gain2 = audioCtx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(659.25, audioCtx.currentTime); // Nota Mi (E5)
            gain2.gain.setValueAtTime(0.08, audioCtx.currentTime);
            gain2.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.5);
            osc2.connect(gain2);
            gain2.connect(audioCtx.destination);
            osc2.start();
            osc2.stop(audioCtx.currentTime + 0.5);
        }, 120);
    } catch (e) {
        console.log("AudioContext bloqueado pelo navegador até interação inicial:", e);
    }
}

// Efeito de Confete festivo em Vanilla JS
function startConfetti() {
    const canvas = document.getElementById('confetti-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    
    const colors = ['#ea1d2c', '#27ae60', '#3498db', '#f1c40f', '#9b59b6'];
    const confettiCount = 150;
    const particles = [];
    
    for (let i = 0; i < confettiCount; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height - canvas.height,
            r: Math.random() * 6 + 4,
            d: Math.random() * canvas.height,
            color: colors[Math.floor(Math.random() * colors.length)],
            tilt: Math.random() * 10 - 5,
            tiltAngleIncremental: Math.random() * 0.07 + 0.02,
            tiltAngle: 0
        });
    }
    
    let animationFrame;
    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        let remaining = false;
        particles.forEach((p, idx) => {
            p.tiltAngle += p.tiltAngleIncremental;
            p.y += (Math.cos(p.d) + 3 + p.r / 2) / 2;
            p.x += Math.sin(p.tiltAngle);
            p.tilt = Math.sin(p.tiltAngle - idx / 3) * 15;
            
            if (p.y < canvas.height) remaining = true;
            
            ctx.beginPath();
            ctx.lineWidth = p.r;
            ctx.strokeStyle = p.color;
            ctx.moveTo(p.x + p.tilt + p.r / 2, p.y);
            ctx.lineTo(p.x + p.tilt, p.y + p.tilt + p.r / 2);
            ctx.stroke();
        });
        
        if (remaining) {
            animationFrame = requestAnimationFrame(draw);
        } else {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }
    }
    
    draw();
    setTimeout(() => cancelAnimationFrame(animationFrame), 8000);
}

// Atualiza o visual do Stepper baseado no status
function updateTrackerUI(status) {
    const nodes = {
        recebido: document.getElementById('step-recebido'),
        preparando: document.getElementById('step-preparando'),
        assando: document.getElementById('step-assando'),
        rota: document.getElementById('step-rota'),
        entregue: document.getElementById('step-entregue')
    };

    const progressLine = document.getElementById('tracker-progress');
    const descText = document.getElementById('tracker-desc-text');
    const descBox = document.getElementById('tracker-desc-box');

    // Reset geral das classes
    Object.values(nodes).forEach(node => {
        if(node) {
            node.classList.remove('active', 'completed');
        }
    });
    if (descBox) descBox.classList.remove('completed-status');

    let percent = 0;
    let description = "Seu pedido está sendo analisado pela nossa equipe.";

    switch(status) {
        case 'pendente':
        case 'aguardando_pagamento':
            if (nodes.recebido) nodes.recebido.classList.add('active');
            percent = 0;
            description = "🍔 **Pedido recebido!** Estamos aguardando a confirmação do seu pedido ou a aprovação do balcão antes de enviá‑lo para a cozinha.";
            break;
            
        case 'pago':
            if (nodes.recebido) nodes.recebido.classList.add('completed');
            if (nodes.preparando) nodes.preparando.classList.add('active');
            percent = 25;
            description = "✅ **Pagamento Aprovado!** Sua pizza já entrou na nossa fila de produção e será montada em instantes.";
            break;
            
        case 'em_producao':
            if (nodes.recebido) nodes.recebido.classList.add('completed');
            if (nodes.preparando) nodes.preparando.classList.add('completed');
            
            // Simular uma etapa intermediária de "Forno" para tornar a experiência espetacular
            // Após 3 minutos na tela em produção, nós iluminamos o "No Forno" de forma divertida!
            checkCount++;
            if (checkCount >= 10) { // Aproximadamente 1 minuto+ de tela aberta
                if (nodes.assando) nodes.assando.classList.add('active');
                percent = 55;
                description = "🔥 **Quentinho!** Sua pizza personalizada já está no nosso forno à lenha dourando o queijo mozzarella!";
            } else {
                if (nodes.assando) nodes.assando.classList.add('active'); // Mantém o forno como o passo atual a ser atingido
                if (nodes.preparando) {
                    nodes.preparando.classList.remove('completed');
                    nodes.preparando.classList.add('active');
                }
                percent = 25;
                description = "👨‍🍳 **Na Cozinha!** Nosso pizzaiolo está abrindo a massa fresca artesanal e espalhando os recheios selecionados.";
            }
            break;
            
        case 'saiu_para_entrega':
            if (nodes.recebido) nodes.recebido.classList.add('completed');
            if (nodes.preparando) nodes.preparando.classList.add('completed');
            if (nodes.assando) nodes.assando.classList.add('completed');
            if (nodes.rota) nodes.rota.classList.add('active');
            percent = 75;
            description = "🏍️ **A caminho!** O motoboy acabou de retirar sua pizza quentinha em nossa bolsa térmica. Fique atento à sua campainha!";
            break;
            
        case 'entregue':
            if (nodes.recebido) nodes.recebido.classList.add('completed');
            if (nodes.preparando) nodes.preparando.classList.add('completed');
            if (nodes.assando) nodes.assando.classList.add('completed');
            if (nodes.rota) nodes.rota.classList.add('completed');
            if (nodes.entregue) nodes.entregue.classList.add('completed');
            if (descBox) descBox.classList.add('completed-status');
            percent = 100;
            description = "🏁 **Entregue com Sucesso!** Bom apetite! Esperamos que ame cada fatia. Avalie-nos depois pelo WhatsApp!";
            
            // Disparar confetes caso tenha mudado para entregue neste instante
            if(lastStatus !== 'entregue' && lastStatus !== '') {
                startConfetti();
            }
            break;
            
        case 'cancelado':
            if (nodes.recebido) nodes.recebido.classList.add('active');
            percent = 0;
            description = "❌ **Pedido Cancelado.** Infelizmente, seu pedido foi cancelado. Se tiver dúvidas, fale com nossa equipe via WhatsApp.";
            break;
            
        default:
            if (nodes.recebido) nodes.recebido.classList.add('active');
            percent = 0;
            description = "Seu pedido foi registrado em nossa fila geral de atendimento.";
    }

    if (progressLine) progressLine.style.width = percent + "%";
    if (descText) descText.innerHTML = description;
}

// Polling AJAX a cada 7 segundos para buscar status em tempo real do banco de dados
function pollPedidoStatus() {
    var pedidoId = <?= $pedidoId ?>;
    if(pedidoId <= 0) return;
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '<?= HOME ?>/get_pedido_status.php?id=' + pedidoId + '&_=' + new Date().getTime(), true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                if(response.sucesso) {
                    var currentStatus = response.pedido.status;
                    
                    // Se o status mudou, emite alerta sonoro
                    if (lastStatus !== '' && lastStatus !== currentStatus) {
                        playStatusChime();
                    }
                    
                    lastStatus = currentStatus;
                    updateTrackerUI(currentStatus);
                }
            } catch(e) {
                console.log("Erro ao processar JSON de status:", e);
            }
        }
    };
    xhr.send();
}

// Inicia no carregamento da tela
window.addEventListener('DOMContentLoaded', (event) => {
    // Primeira chamada instantânea
    pollPedidoStatus();
    
    // Configura o intervalo de polling
    setInterval(pollPedidoStatus, 7000);
});
</script>

<?php
require_once('footer.php');
?>
