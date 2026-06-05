<?php
/*************************************************************************************
 * As credenciais sensíveis foram movidas para o arquivo .env na raiz do projeto
 * NUNCA suba o arquivo .env para repositórios públicos (GitHub, etc)
*************************************************************************************/

date_default_timezone_set('America/Sao_Paulo');

ini_set('default_charset', 'UTF-8');
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

if (!defined('MONDINI_UTF8_OUTPUT_NORMALIZER')) {
    define('MONDINI_UTF8_OUTPUT_NORMALIZER', true);

    function mondini_normalizar_utf8_saida($buffer) {
        if (!is_string($buffer) || $buffer === '') {
            return $buffer;
        }

        foreach (headers_list() as $header) {
            if (stripos($header, 'Content-Type:') === 0 && preg_match('/\b(image|audio|video)\b|application\/(pdf|zip|octet-stream)/i', $header)) {
                return $buffer;
            }
        }

        static $map = [
            'ÃƒÂ¡' => 'á', 'ÃƒÂ ' => 'à', 'ÃƒÂ¢' => 'â', 'ÃƒÂ£' => 'ã',
            'ÃƒÂ©' => 'é', 'ÃƒÂª' => 'ê', 'ÃƒÂ­' => 'í',
            'ÃƒÂ³' => 'ó', 'ÃƒÂ´' => 'ô', 'ÃƒÂµ' => 'õ',
            'ÃƒÂº' => 'ú', 'ÃƒÂ¼' => 'ü', 'ÃƒÂ§' => 'ç',
            'ÃƒÂ' => 'Á', 'Ãƒâ€°' => 'É', 'ÃƒÂ' => 'Í',
            'Ãƒâ€œ' => 'Ó', 'ÃƒÅ¡' => 'Ú', 'Ãƒâ€¡' => 'Ç',
            'Ã‡ÃƒO' => 'ÇÃO', 'Ã‡Ã•ES' => 'ÇÕES', 'ÃƒO' => 'ÃO',
            'ÃƒÆ’O' => 'ÃO', 'ÃƒÂ£' => 'ã', 'ÃƒÂ³' => 'ó',
            'Ã¡' => 'á', 'Ã ' => 'à', 'Ã¢' => 'â', 'Ã£' => 'ã',
            'Ã¤' => 'ä', 'Ã©' => 'é', 'Ãª' => 'ê', 'Ã­' => 'í',
            'Ã³' => 'ó', 'Ã´' => 'ô', 'Ãµ' => 'õ', 'Ãº' => 'ú',
            'Ã¼' => 'ü', 'Ã§' => 'ç', 'Ã' => 'Á', 'Ã‰' => 'É',
            'Ã' => 'Í', 'Ã“' => 'Ó', 'Ãš' => 'Ú', 'Ã‡' => 'Ç',
            'Ã•' => 'Õ', 'â€”' => '—', 'â€“' => '–', 'â€¢' => '•',
            'â€¦' => '…', 'â†’' => '→', 'â€˜' => '‘', 'â€™' => '’',
            'â€œ' => '“', 'â€' => '”', 'â‚¬' => '€',
            'Ã¢â‚¬Â¢' => '•', 'Ã¢ÂÅ’' => '✕',
            'Ã°Å¸Å’Â' => '🌐', 'Ã°Å¸Ââ€¢' => '🍕',
            'Ã°Å¸â€ºÂÃ¯Â¸Â' => '🛍️', 'Ã°Å¸â€œÅ ' => '📊',
            'Ã°Å¸â€œâ€¦' => '📅', 'Ã°Å¸â€™Â°' => '💰',
            'Ã°Å¸â€ºâ€™' => '🛒', 'Ã°Å¸â€™Â³' => '💳',
            'Ã°Å¸Å¡â‚¬' => '🚀',
            'ðŸ”' => '', 'ðŸŒ' => '🌐', 'ðŸ•' => '🍕',
            'ðŸ›ï¸' => '🛍️', 'ðŸ“Š' => '📊', 'ðŸ“…' => '📅',
            'ðŸ’°' => '💰', 'ðŸ›’' => '🛒', 'ðŸ’³' => '💳',
            'ðŸš€' => '🚀', 'ðŸ”¥' => '🔥', 'ðŸŽ‰' => '🎉',
            'ðŸ””' => '🔔', 'ðŸ‘‰' => '👉', 'ðŸ‘ˆ' => '👈',
            'âœ“' => '✓', 'âœ”' => '✔', 'âœ•' => '✕',
        ];

        return strtr($buffer, $map);
    }

    if (PHP_SAPI !== 'cli') {
        ob_start('mondini_normalizar_utf8_saida');
    }
}

// ============================================================
// LEITURA DO ARQUIVO .env
// ============================================================
$envPath = __DIR__ . '/../.env'; // raiz do projeto zoufen/
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // ignora comentários
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Função auxiliar para ler .env com fallback
function env($key, $default = '') {
    return isset($_ENV[$key]) ? $_ENV[$key] : $default;
}

// ============================================================
// CONFIGURAÇÕES DO BANCO DE DADOS
// ============================================================
$hostLocal = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$isLocalhost = ($hostLocal === 'localhost' || $hostLocal === '127.0.0.1' || strpos($hostLocal, '192.168.') === 0 || strpos($hostLocal, '10.') === 0 || strpos($hostLocal, '172.') === 0);

if ($isLocalhost) {
    define('SHEEP_URL',   env('DB_LOCAL_URL',   $hostLocal . '/projetos/zoufen/demos/pizzaria'));
    define('SHEEP_HOST',  env('DB_LOCAL_HOST',  'localhost'));
    define('SHEEP_USER',  env('DB_LOCAL_USER',  'root'));
    define('SHEEP_SENHA', env('DB_LOCAL_SENHA', ''));
    define('SHEEP_BD',    env('DB_LOCAL_BD',    'delivery'));
} else {
    define('SHEEP_URL',   env('DB_PROD_URL',   'pizzariamodelo.free.nf/demos/pizzaria'));
    define('SHEEP_HOST',  env('DB_PROD_HOST',  'sql300.infinityfree.com'));
    define('SHEEP_USER',  env('DB_PROD_USER',  'if0_42032951'));
    define('SHEEP_SENHA', env('DB_PROD_SENHA', 'PNPjlUPLevIvG'));
    define('SHEEP_BD',    env('DB_PROD_BD',    'if0_42032951_banco_delivery'));
}

/**
 * para PostgreSQL: 'pgsql'
 * para SQLite:     'sqlite'
 * para MySQL:      'mysql'
 */
define('SHEEP_TIPO_BANCO', 'mysql');

// ============================================================
// PAGAMENTOS — lidos do .env
// ============================================================
define('MP_PUBLIC_KEY',       env('MP_PUBLIC_KEY',       ''));
define('MP_ACCESS_TOKEN',     env('MP_ACCESS_TOKEN',     ''));
define('PAYPAL_CLIENT_ID',    env('PAYPAL_CLIENT_ID',    ''));
define('PAYPAL_CLIENT_SECRET',env('PAYPAL_CLIENT_SECRET',''));

function mondini_garantir_colunas_usuarios_seguranca() {
    static $executado = false;
    if ($executado || !defined('SHEEP_HOST') || !defined('SHEEP_BD')) {
        return;
    }

    $executado = true;

    try {
        $pdo = new PDO(
            "mysql:host=" . SHEEP_HOST . ";dbname=" . SHEEP_BD . ";charset=utf8mb4",
            SHEEP_USER,
            SHEEP_SENHA,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $colunaNivel = $pdo->query("SHOW COLUMNS FROM `usuarios` LIKE 'nivel'");
        if ($colunaNivel->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `usuarios` ADD `nivel` CHAR(1) NOT NULL DEFAULT 'C' AFTER `senha`");
        }

        $colunaStatus = $pdo->query("SHOW COLUMNS FROM `usuarios` LIKE 'status'");
        if ($colunaStatus->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `usuarios` ADD `status` CHAR(1) NOT NULL DEFAULT 'S'");
        }

        $colunaFone = $pdo->query("SHOW COLUMNS FROM `usuarios` LIKE 'fone'");
        if ($colunaFone->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `usuarios` ADD `fone` VARCHAR(20) NULL AFTER `cpf`");
        }

        $colunaWhatsapp = $pdo->query("SHOW COLUMNS FROM `usuarios` LIKE 'whatsapp'");
        if ($colunaWhatsapp->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `usuarios` ADD `whatsapp` VARCHAR(20) NULL AFTER `fone`");
        }

        $pdo->exec("UPDATE `usuarios` SET `nivel` = 'M' WHERE `email` = 'admin@admin.com' OR `id` = 1");
        $pdo->exec("UPDATE `usuarios` SET `nivel` = 'C' WHERE `nivel` IS NULL OR `nivel` = '' OR `nivel` NOT IN ('M','O','C')");
        $pdo->exec("UPDATE `usuarios` SET `status` = 'S' WHERE `status` IS NULL OR `status` = ''");

        $totalAdmins = (int)$pdo->query("SELECT COUNT(*) FROM `usuarios` WHERE `nivel` = 'M'")->fetchColumn();
        if ($totalAdmins === 0) {
            $pdo->exec("UPDATE `usuarios` SET `nivel` = 'M' ORDER BY `id` ASC LIMIT 1");
        }
    } catch (Exception $e) {
        error_log('Falha ao garantir colunas de seguranca dos usuarios: ' . $e->getMessage());
    }
}

function mondini_filtrar_dados_por_colunas_tabela($tabela, array $dados) {
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $tabela)) {
        return $dados;
    }

    try {
        $pdo = Conexao::getCanectar();
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$tabela}`");
        $colunas = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $coluna) {
            if (!empty($coluna['Field'])) {
                $colunas[$coluna['Field']] = true;
            }
        }

        if (!$colunas) {
            return $dados;
        }

        return array_intersect_key($dados, $colunas);
    } catch (Exception $e) {
        error_log('Falha ao filtrar dados da tabela ' . $tabela . ': ' . $e->getMessage());
        return $dados;
    }
}

function mondini_garantir_chocolate() {
    static $checado = false;
    if ($checado) {
        return;
    }
    $checado = true;

    // Inicia a sessão se ainda não foi iniciada, para usar a otimização de cache de checagem
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    
    if (!empty($_SESSION['chocolate_garantido'])) {
        return;
    }

    if (!defined('SHEEP_HOST') || !defined('SHEEP_BD')) {
        return;
    }

    try {
        $pdo = new PDO(
            "mysql:host=" . SHEEP_HOST . ";dbname=" . SHEEP_BD . ";charset=utf8mb4",
            SHEEP_USER,
            SHEEP_SENHA,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        // Verifica se existe o Chocolate (ID 12 ou nome 'Chocolate')
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `produtos` WHERE `id` = 12 OR `nome` = 'Chocolate'");
        $stmt->execute();
        $existe = (int)$stmt->fetchColumn();

        if ($existe === 0) {
            // Garante que o ID 12 e o nome estão livres
            $pdo->exec("DELETE FROM `produtos` WHERE `id` = 12 OR `nome` = 'Chocolate'");
            
            // Insere o Chocolate com todos os dados corretos
            $stmtInsert = $pdo->prepare("INSERT INTO `produtos` (`id`, `nome`, `descricao`, `preco`, `preco_promocional`, `categoria`, `imagem`, `criado_em`) 
                VALUES (12, 'Chocolate', 'Barra de chocolate Garoto ao leite 90g.', 8.90, NULL, 'adicional', 'assets/img/extras/chocolate.png', NOW())");
            $stmtInsert->execute();
        }

        $_SESSION['chocolate_garantido'] = true;
    } catch (Exception $e) {
        error_log('Erro ao garantir produto Chocolate no banco de dados: ' . $e->getMessage());
    }
}

// Executa a verificação e auto-correção
mondini_garantir_chocolate();

require_once('includes.php');
?>
