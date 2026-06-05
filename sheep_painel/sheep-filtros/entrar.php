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

require_once('../../sheep_core/config.php');

if (function_exists('mondini_garantir_colunas_usuarios_seguranca')) {
	mondini_garantir_colunas_usuarios_seguranca();
}



$email =filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$senha =filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$csrfToken = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);

if (
	empty($csrfToken) ||
	empty($_SESSION['login_csrf_token']) ||
	!hash_equals($_SESSION['login_csrf_token'], $csrfToken)
) {
	unset($_SESSION['sheep_user']);
	header("Location: " . URL_CAMINHO_PAINEL . "index.php?senha_errada=true");
	exit();
}

$agora = time();
$hostLogin = $_SERVER['HTTP_HOST'] ?? '';
$loginLocal = (
	$hostLogin === 'localhost' ||
	$hostLogin === '127.0.0.1' ||
	strpos($hostLogin, 'localhost:') === 0 ||
	strpos($hostLogin, '127.0.0.1:') === 0
);
if (!$loginLocal && !empty($_SESSION['login_bloqueado_ate']) && $_SESSION['login_bloqueado_ate'] > $agora) {
	header("Location: " . URL_CAMINHO_PAINEL . "index.php?senha_errada=true");
	exit();
}

if($email == null || $senha == null ){
 header("Location: " . URL_CAMINHO_PAINEL ."/index.php?campos_vazios=true");
 return false;
 exit();

}

$verificar = new Entrar();
$verificar->entrar($email, $senha);

if ($verificar->getResultado()) {
	unset($_SESSION['login_tentativas'], $_SESSION['login_bloqueado_ate']);
	header("Location:" .URL_CAMINHO_PAINEL . "sheep.php");

}else{
	if (!$loginLocal) {
		$_SESSION['login_tentativas'] = (int) ($_SESSION['login_tentativas'] ?? 0) + 1;
		if ($_SESSION['login_tentativas'] >= 5) {
			$_SESSION['login_bloqueado_ate'] = $agora + 300;
			$_SESSION['login_tentativas'] = 0;
		}
	} else {
		unset($_SESSION['login_tentativas'], $_SESSION['login_bloqueado_ate']);
	}
	unset($_SESSION['sheep_user']);
	header("Location:" .URL_CAMINHO_PAINEL . "index.php?senha_errada=true");
	exit();
}





?>
