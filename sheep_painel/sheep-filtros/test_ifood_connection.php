<?php
/**
 * Teste em tempo real da conexão com o iFood Merchant API.
 */

header('Content-Type: application/json; charset=utf-8');
session_start();
require_once('../../sheep_core/config.php');

if (empty($_SESSION['sheep_user']) || ($_SESSION['sheep_user']['nivel'] ?? 'C') !== 'M') {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Acesso restrito a administradores.'
    ]);
    exit;
}

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

$client_id = filter_input(INPUT_POST, 'client_id', FILTER_DEFAULT);
$client_secret = filter_input(INPUT_POST, 'client_secret', FILTER_DEFAULT);
$merchant_id = filter_input(INPUT_POST, 'merchant_id', FILTER_DEFAULT);

if (empty($client_id) || empty($client_secret) || empty($merchant_id)) {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Preencha todos os campos obrigatórios (Client ID, Client Secret e Merchant ID) para testar.'
    ]);
    exit;
}

try {
    // Instancia o serviço temporariamente com as novas chaves enviadas
    $ifoodTest = new IfoodService($client_id, $client_secret, $merchant_id);
    
    // Tenta obter o Access Token diretamente do iFood
    $token = $ifoodTest->getAccessToken();
    
    if ($token !== null && !empty($token)) {
        
        // As credenciais são válidas! Vamos salvá-las no banco config_ifood.
        $keys = [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'merchant_id' => $merchant_id
        ];
        
        foreach ($keys as $chave => $valor) {
            $checkKey = new Ler();
            $checkKey->Leitura('config_ifood', "WHERE chave = :chave LIMIT 1", "chave={$chave}");
            
            if ($checkKey->getResultado()) {
                // Se a chave já existe, apenas atualiza o valor
                $atualizar = new Atualizar();
                $atualizar->Atualizando('config_ifood', ['valor' => $valor], "WHERE chave = :chave", "chave={$chave}");
            } else {
                // Se não existe, insere um novo registro
                $criar = new Criar();
                $criar->Criacao('config_ifood', [
                    'chave' => $chave,
                    'valor' => $valor
                ]);
            }
        }
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Conexão estabelecida e autenticada com sucesso! As credenciais foram gravadas no banco de dados.'
        ]);
        
    } else {
        echo json_encode([
            'sucesso' => false,
            'erro' => 'O iFood recusou a autenticação. Verifique se o Client ID e Client Secret estão corretos e correspondem ao ambiente de homologação/produção.'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Erro interno ao validar conexão: ' . $e->getMessage()
    ]);
}
exit;
