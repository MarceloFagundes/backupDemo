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

if (function_exists('mondini_garantir_colunas_usuarios_seguranca')) {
    mondini_garantir_colunas_usuarios_seguranca();
}

$see_uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
$ms = filter_input(INPUT_GET, 'm', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$sair = filter_input(INPUT_GET, 'sair', FILTER_VALIDATE_BOOLEAN);

if ($sair) {
    unset($_SESSION['sheep_user']);
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
    header("Location: index.php?sheep_saiu=true");
    exit();
}

// Proteção do Painel: Redireciona para o login se a sessão não estiver ativa
if (empty($_SESSION['sheep_user'])) {
    header("Location: index.php");
    exit();
}

// Controle de Acesso por Nível
// nivel = 'M' (Master/Admin) → acesso total
// nivel = 'O' (Operador)     → acesso operacional: dashboard, pedidos, cardápio, bairros
// nivel = 'C' (Cliente)      → sem acesso ao painel
$nivel_usuario = $_SESSION['sheep_user']['nivel'] ?? 'C';

// Páginas exclusivas de Administrador (nível M)
$paginas_admin = [
    'sheep-usuarios/index',
    'sheep-usuarios/sheep-criar',
    'sheep-usuarios/sheep-editar',
    'sheep-dados/cashback',
    'sheep-dados/pagamentos',
    'sheep-dados/fiscal',
    'ifood_setup',
];

// Clientes (C) não têm acesso ao painel
if ($nivel_usuario === 'C') {
    unset($_SESSION['sheep_user']);
    header("Location: index.php?acesso_negado=true");
    exit();
}

// Operadores (O) não acessam páginas administrativas
if ($nivel_usuario === 'O' && in_array($ms, $paginas_admin)) {
    header("Location: sheep.php?acesso_negado=true");
    exit();
}

// Helper global para verificar nível nas views
function is_admin() {
    return ($_SESSION['sheep_user']['nivel'] ?? 'C') === 'M';
}
function is_operador() {
    return ($_SESSION['sheep_user']['nivel'] ?? 'C') === 'O';
}
?>
