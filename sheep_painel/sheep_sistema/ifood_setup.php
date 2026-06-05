<?php
/**
 * Desenvolvido sob o padrão visual premium e Sheep PHP Framework.
 */

// Auto-criar a tabela config_ifood se ela não existir (auto-healing)
try {
    $pdo = new PDO("mysql:host=" . SHEEP_HOST . ";dbname=" . SHEEP_BD . ";charset=utf8", SHEEP_USER, SHEEP_SENHA);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE TABLE IF NOT EXISTS config_ifood (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chave VARCHAR(255) NOT NULL UNIQUE,
        valor TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Exception $e) {
    // Silencia ou loga se necessário
}

// Garante que a classe Ler esteja disponível e carrega credenciais existentes
$ler = new Ler();

$ler->Leitura('config_ifood', "WHERE chave = :chave LIMIT 1", "chave=client_id");
$clientIdSalvo = $ler->getResultado() ? trim($ler->getResultado()[0]['valor']) : '';

$ler->Leitura('config_ifood', "WHERE chave = :chave LIMIT 1", "chave=client_secret");
$clientSecretSalvo = $ler->getResultado() ? trim($ler->getResultado()[0]['valor']) : '';

$ler->Leitura('config_ifood', "WHERE chave = :chave LIMIT 1", "chave=merchant_id");
$merchantIdSalvo = $ler->getResultado() ? trim($ler->getResultado()[0]['valor']) : '';

// Define o Webhook dinamicamente
$webhookUrl = HOME . '/polling_ifood.php';

// Verifica se já temos dados preenchidos para mudar o status visual inicial
$estaConfigurado = (!empty($clientIdSalvo) && !empty($clientSecretSalvo) && !empty($merchantIdSalvo));
?>

<!-- Folha de Estilos Exclusiva da Integração iFood -->
<style>
    :root {
        --ifood-red: #ea1d2c;
        --ifood-red-hover: #d21220;
        --ifood-dark: #0f0f0f;
        --ifood-gray: #1e1e1e;
        --ifood-light: #f5f5f5;
        --glass-bg: rgba(255, 255, 255, 0.08);
        --glass-border: rgba(255, 255, 255, 0.1);
        --neon-green: #2ecc71;
    }

    .ifood-container {
        font-family: 'Outfit', sans-serif;
        color: #333;
        margin-top: 20px;
    }

    .ifood-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        border: 1px solid #eee;
        padding: 30px;
        margin-bottom: 30px;
        transition: transform 0.3s;
    }

    .ifood-header-card {
        background: linear-gradient(135deg, var(--ifood-dark) 0%, #252525 100%);
        color: #fff;
        position: relative;
        overflow: hidden;
        border: none;
    }

    .ifood-header-card::before {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(234, 29, 44, 0.15) 0%, transparent 70%);
        right: -100px;
        top: -100px;
        border-radius: 50%;
    }

    .ifood-badge-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .status-active {
        background: rgba(46, 204, 113, 0.15);
        color: var(--neon-green);
        border: 1px solid rgba(46, 204, 113, 0.3);
    }

    .status-inactive {
        background: rgba(234, 29, 44, 0.15);
        color: #ff6b6b;
        border: 1px solid rgba(234, 29, 44, 0.3);
    }

    .pulse-dot {
        width: 10px;
        height: 10px;
        background-color: currentColor;
        border-radius: 50%;
        animation: pulse 1.8s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(46, 204, 113, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
    }

    .step-list {
        position: relative;
        padding-left: 30px;
        border-left: 2px dashed #ddd;
        margin-left: 15px;
        margin-bottom: 0;
    }

    .step-item {
        position: relative;
        margin-bottom: 40px;
    }

    .step-item:last-child {
        margin-bottom: 0;
    }

    .step-number {
        position: absolute;
        left: -46px;
        top: 0;
        background: #eee;
        color: #555;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        border: 3px solid #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: background 0.3s, color 0.3s;
    }

    .step-item.active .step-number {
        background: var(--ifood-red);
        color: #fff;
    }

    .step-title {
        font-size: 1.15rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: var(--ifood-dark);
    }

    .step-desc {
        color: #666;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    .webhook-copy-container {
        display: flex;
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 8px 12px;
        align-items: center;
        justify-content: space-between;
        margin-top: 15px;
        font-family: monospace;
        font-size: 0.9rem;
        color: #333;
        gap: 10px;
        overflow-x: auto;
    }

    .btn-copy {
        background: var(--ifood-dark);
        color: #fff;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .btn-copy:hover {
        background: #333;
        transform: translateY(-1px);
    }

    .ifood-form .form-group {
        margin-bottom: 20px;
    }

    .ifood-form label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }

    .ifood-form input {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #fff;
        color: #333;
        font-family: inherit;
        font-size: 0.95rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        box-sizing: border-box;
    }

    .ifood-form input:focus {
        border-color: var(--ifood-red);
        outline: none;
        box-shadow: 0 0 0 3px rgba(234, 29, 44, 0.15);
    }

    .btn-submit-ifood {
        background: var(--ifood-red);
        color: #fff;
        border: none;
        padding: 16px 24px;
        border-radius: 8px;
        font-weight: bold;
        font-size: 1.05rem;
        cursor: pointer;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 4px 15px rgba(234, 29, 44, 0.2);
        transition: all 0.2s;
    }

    .btn-submit-ifood:hover {
        background: var(--ifood-red-hover);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(234, 29, 44, 0.3);
    }

    .btn-submit-ifood:active {
        transform: translateY(0);
    }

    .ifood-alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        line-height: 1.5;
        font-size: 0.95rem;
    }

    .alert-warning {
        background: #fff9db;
        border: 1px solid #ffe066;
        color: #856404;
    }

    .alert-success {
        background: #ebfbee;
        border: 1px solid #b2f2bb;
        color: #2b8a3e;
    }

    /* Loader estilizado */
    .loader {
        border: 3px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top: 3px solid #fff;
        width: 20px;
        height: 20px;
        animation: spin 1s linear infinite;
        display: none;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<div class="main-content ifood-container">
    <section class="section">
        <div class="section-body">

            <!-- Cabeçalho Principal -->
            <div class="ifood-card ifood-header-card">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; position:relative; z-index:2;">
                    <div>
                        <h1 style="color:var(--ifood-red); font-size: 42px; font-weight:800; margin:0 0 5px 0;">iFood</h1>
                        <h2 style="font-size:1.8rem; font-weight:600; margin:0 0 10px 0;">Assistente de Integração Oficial</h2>
                        <p style="margin:0; opacity:0.8; font-size:1rem; max-width:550px;">
                            Configure a conexão em tempo real da Pizzaria Modelo com o Portal do Parceiro iFood de forma visual, simples e automatizada.
                        </p>
                    </div>
                    <div>
                        <div id="status-badge" class="ifood-badge-status <?= $estaConfigurado ? 'status-active' : 'status-inactive' ?>">
                            <div class="pulse-dot"></div>
                            <span id="status-text"><?= $estaConfigurado ? 'CONECTADO E ATIVO' : 'PENDENTE DE CONFIGURAÇÃO' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid de Duas Colunas -->
            <div style="display:grid; grid-template-columns: 1.2fr 0.8fr; gap:30px; align-items:start; flex-wrap:wrap;" class="ifood-grid">
                
                <!-- Coluna Esquerda: O Passo a Passo (Wizard) -->
                <div class="ifood-card">
                    <h3 style="font-size:1.4rem; font-weight:600; margin-bottom:25px; display:flex; align-items:center; gap:10px;">
                        <i class="fa fa-map-signs" style="color:var(--ifood-red);"></i> Guia Passo a Passo (Pegar Pela Mão)
                    </h3>

                    <div class="step-list">
                        
                        <!-- Passo 1 -->
                        <div class="step-item active">
                            <div class="step-number">1</div>
                            <div class="step-title">Criar Conta no iFood Developer</div>
                            <div class="step-desc">
                                Acesse o site oficial de desenvolvimento do iFood:
                                <a href="https://developer.ifood.com.br/" target="_blank" style="color:var(--ifood-red); font-weight:bold; text-decoration:underline;">
                                    developer.ifood.com.br <i class="fa fa-external-link"></i>
                                </a>.
                                Cadastre sua Pizzaria e crie uma nova **Aplicação (App)** do tipo **Merchant (Distribuidor/Loja)**. 
                                Vincule a sua loja ao seu aplicativo usando o **Merchant ID** nas configurações internas do iFood.
                            </div>
                        </div>

                        <!-- Passo 2 -->
                        <div class="step-item active">
                            <div class="step-number">2</div>
                            <div class="step-title">Configurar o Webhook do iFood</div>
                            <div class="step-desc">
                                Copie o endereço abaixo e cole-o no campo **Webhook URL** dentro do painel de desenvolvedor do iFood. 
                                Esse endereço avisa a Pizzaria Modelo em tempo real sempre que um novo pedido chegar no iFood!
                                
                                <div class="webhook-copy-container">
                                    <span id="webhook-url-text"><?= $webhookUrl ?></span>
                                    <button class="btn-copy" onclick="copyWebhook()">
                                        <i class="fa fa-copy"></i> <span id="copy-btn-text">Copiar</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Passo 3 -->
                        <div class="step-item active">
                            <div class="step-number">3</div>
                            <div class="step-title">Pegar as Chaves Geradas pelo iFood</div>
                            <div class="step-desc">
                                Após cadastrar o seu aplicativo, o iFood gerará as suas chaves exclusivas de autenticação. 
                                Você deve copiar essas 3 chaves geradas e colá-las no formulário ao lado:
                                <ul style="margin-top:10px; padding-left:20px;">
                                    <li><b>Client ID</b>: Identificador do seu aplicativo.</li>
                                    <li><b>Client Secret</b>: Senha de segurança secreta gerada.</li>
                                    <li><b>Merchant ID</b>: Identificador físico da sua loja no iFood.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Passo 4 -->
                        <div class="step-item active">
                            <div class="step-number">4</div>
                            <div class="step-title">Validar e Conectar!</div>
                            <div class="step-desc">
                                Clique no botão **"Testar e Ativar Conexão"** ao lado. O nosso validador inteligente conversará com o iFood na hora, fará a autenticação segura e ativará o painel sincronizado automaticamente!
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Coluna Direita: Formulário de Credenciais -->
                <div class="ifood-card" style="position: sticky; top: 20px;">
                    <h3 style="font-size:1.3rem; font-weight:600; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                        <i class="fa fa-key" style="color:var(--ifood-red);"></i> Credenciais de Conexão
                    </h3>

                    <!-- Alerta explicativo -->
                    <div class="ifood-alert alert-warning" id="ifood-info-alert">
                        <i class="fa fa-info-circle" style="font-size:1.3rem; margin-top:2px;"></i>
                        <div>
                            Insira os dados gerados no portal do iFood abaixo. A autenticação e salvamento são feitos de forma segura e imediata.
                        </div>
                    </div>

                    <!-- Formulário de Envio -->
                    <form class="ifood-form" id="form-ifood-credenciais" onsubmit="validarEConectar(event)">
                        
                        <div class="form-group">
                            <label for="ifood_client_id">Client ID</label>
                            <input type="text" id="ifood_client_id" value="<?= htmlspecialchars($clientIdSalvo) ?>" placeholder="Cole o Client ID gerado pelo iFood" required autocomplete="off">
                        </div>

                        <div class="form-group">
                            <label for="ifood_client_secret">Client Secret</label>
                            <input type="password" id="ifood_client_secret" value="<?= htmlspecialchars($clientSecretSalvo) ?>" placeholder="Cole o Client Secret gerado pelo iFood" required autocomplete="off">
                        </div>

                        <div class="form-group">
                            <label for="ifood_merchant_id">Merchant ID</label>
                            <input type="text" id="ifood_merchant_id" value="<?= htmlspecialchars($merchantIdSalvo) ?>" placeholder="Cole o Merchant ID da sua loja" required autocomplete="off">
                        </div>

                        <button type="submit" class="btn-submit-ifood" id="btn-submit-active">
                            <i class="fa fa-plug"></i> <span id="btn-text">Testar e Ativar Conexão</span>
                            <div class="loader" id="btn-loader"></div>
                        </button>

                    </form>
                </div>

            </div>

        </div>
    </section>
</div>

<!-- Efeitos Sonoros de Feedback -->
<audio id="audio-sucesso" src="assets/audio/sucesso.mp3" preload="auto"></audio>
<audio id="audio-erro" src="assets/audio/erro.mp3" preload="auto"></audio>

<script>
    // Função para Copiar URL do Webhook
    function copyWebhook() {
        const urlText = document.getElementById('webhook-url-text').innerText;
        navigator.clipboard.writeText(urlText).then(() => {
            const btnText = document.getElementById('copy-btn-text');
            btnText.innerText = 'Copiado!';
            setTimeout(() => {
                btnText.innerText = 'Copiar';
            }, 2500);
        }).catch(err => {
            console.error('Falha ao copiar:', err);
        });
    }

    // Função de Validação e Ativação via AJAX
    function validarEConectar(event) {
        event.preventDefault();

        const clientId = document.getElementById('ifood_client_id').value.trim();
        const clientSecret = document.getElementById('ifood_client_secret').value.trim();
        const merchantId = document.getElementById('ifood_merchant_id').value.trim();

        const btn = document.getElementById('btn-submit-active');
        const btnText = document.getElementById('btn-text');
        const loader = document.getElementById('btn-loader');
        const alertBox = document.getElementById('ifood-info-alert');
        const badge = document.getElementById('status-badge');
        const badgeText = document.getElementById('status-text');

        // Sons de feedback
        const audioSucesso = new Audio('https://assets.mixkit.co/active_storage/sfx/2013/2013-84.wav'); // Som limpo de sucesso
        const audioErro = new Audio('https://assets.mixkit.co/active_storage/sfx/2959/2959-84.wav'); // Som limpo de erro

        // Inicia carregamento visual
        btn.disabled = true;
        btnText.innerText = 'Conectando e Validando...';
        loader.style.display = 'block';

        const formData = new FormData();
        formData.append('client_id', clientId);
        formData.append('client_secret', clientSecret);
        formData.append('merchant_id', merchantId);

        fetch('sheep-filtros/test_ifood_connection.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            loader.style.display = 'none';

            if (data.sucesso) {
                // Toca som de sucesso
                audioSucesso.play().catch(e => console.log('Bloqueio de áudio', e));

                // Atualiza botão
                btnText.innerText = 'Integração Ativa!';
                btn.style.background = 'var(--neon-green)';
                btn.style.boxShadow = '0 4px 15px rgba(46, 204, 113, 0.2)';

                // Atualiza Alerta
                alertBox.className = 'ifood-alert alert-success';
                alertBox.innerHTML = `<i class="fa fa-check-circle" style="font-size:1.3rem; margin-top:2px;"></i>
                                      <div>
                                          <strong>Sucesso!</strong> ${data.mensagem}
                                      </div>`;

                // Atualiza Badge do Status
                badge.className = 'ifood-badge-status status-active';
                badgeText.innerText = 'CONECTADO E ATIVO';

                setTimeout(() => {
                    btnText.innerText = 'Testar e Ativar Conexão';
                    btn.style.background = '';
                    btn.style.boxShadow = '';
                }, 4000);

            } else {
                // Toca som de erro
                audioErro.play().catch(e => console.log('Bloqueio de áudio', e));

                // Atualiza botão
                btnText.innerText = 'Falha na Conexão';
                btn.style.background = '#e74c3c';
                btn.style.boxShadow = '0 4px 15px rgba(231, 76, 60, 0.2)';

                // Atualiza Alerta
                alertBox.className = 'ifood-alert alert-warning';
                alertBox.innerHTML = `<i class="fa fa-exclamation-triangle" style="font-size:1.3rem; margin-top:2px;"></i>
                                      <div>
                                          <strong>Erro na Integração:</strong> ${data.erro}
                                      </div>`;

                // Mantém Badge como Inativo
                badge.className = 'ifood-badge-status status-inactive';
                badgeText.innerText = 'PENDENTE DE CONFIGURAÇÃO';

                setTimeout(() => {
                    btnText.innerText = 'Testar e Ativar Conexão';
                    btn.style.background = '';
                    btn.style.boxShadow = '';
                }, 4000);
            }
        })
        .catch(err => {
            btn.disabled = false;
            loader.style.display = 'none';
            btnText.innerText = 'Erro na Requisição';
            
            alertBox.className = 'ifood-alert alert-warning';
            alertBox.innerHTML = `<i class="fa fa-exclamation-triangle" style="font-size:1.3rem; margin-top:2px;"></i>
                                  <div>
                                      <strong>Erro técnico:</strong> Não foi possível contatar o validador local. Verifique se o servidor Apache está ativo.
                                  </div>`;
            console.error(err);
        });
    }
</script>
