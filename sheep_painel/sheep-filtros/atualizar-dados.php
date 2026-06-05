<?php
ob_start();
session_start();
require_once('../../sheep_core/config.php');

$redirect = filter_input(INPUT_POST, 'redirecionar', FILTER_SANITIZE_SPECIAL_CHARS);
if(!$redirect){
    $redirect = "sheep-dados/index";
}

$nivelUsuario = $_SESSION['sheep_user']['nivel'] ?? 'C';
$paginasSomenteAdmin = ['sheep-dados/cashback', 'sheep-dados/pagamentos', 'sheep-dados/fiscal'];

if (
    empty($_SESSION['sheep_user']) ||
    !in_array($nivelUsuario, ['M', 'O'], true) ||
    ($nivelUsuario !== 'M' && in_array($redirect, $paginasSomenteAdmin, true))
) {
    header("Location: " . HOME . "/sheep_painel/sheep.php?acesso_negado=true");
    exit;
}

$sheep_firewall = filter_input(INPUT_POST, 'sheep_firewall', FILTER_SANITIZE_NUMBER_INT);
if(!$sheep_firewall){
    header("Location: " . HOME . "/sheep_painel/sheep.php?m=" . $redirect . "&clique=true");
    exit;
}

$dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
unset($dados['sendSheep'], $dados['sheep_firewall'], $dados['tipo'], $dados['usuario'], $dados['redirecionar']);

if ($nivelUsuario !== 'M') {
    $camposRestritos = [
        'mp_public_key',
        'mp_access_token',
        'paypal_client_id',
        'paypal_client_secret',
        'porcentagem_cashback',
        'pontos_por_real',
        'cashback_status',
        'ifood_client_id',
        'ifood_client_secret',
        'ifood_merchant_id',
        'bling_client_id',
        'bling_client_secret',
        'bling_access_token',
        'bling_refresh_token',
        'certificado_digital',
        'senha_certificado',
        'cnpj_fiscal',
        'serie_nfe',
        'ambiente_nfe'
    ];

    foreach ($camposRestritos as $campoRestrito) {
        unset($dados[$campoRestrito]);
    }
}

// Upload da Logo
if($_FILES['logo']['tmp_name']){
    $upload = new Uploads('../../sheep_painel/assets/img/logo/');
    $upload->Image($_FILES['logo'], 'logo-modelo-' . time(), 480);
    if($upload->getResultado()){
        $dados['logo'] = $upload->getResultado();
    }
}else{
    unset($dados['logo']);
}

// Upload do Ícone
if($_FILES['icone']['tmp_name']){
    $upload = new Uploads('../../sheep_painel/assets/img/logo/');
    $upload->Image($_FILES['icone'], 'favicon-' . time(), 128);
    if($upload->getResultado()){
        $dados['icone'] = $upload->getResultado();
    }
}else{
    unset($dados['icone']);
}

// Upload Banner 1
if($_FILES['banner_1']['tmp_name']){
    $upload = new Uploads('../../sheep_painel/assets/img/banners/');
    $upload->Image($_FILES['banner_1'], 'banner-1-' . time(), 1600);
    if($upload->getResultado()){
        $dados['banner_1'] = $upload->getResultado();
    }
}else{
    unset($dados['banner_1']);
}

// Upload Banner 2
if($_FILES['banner_2']['tmp_name']){
    $upload = new Uploads('../../sheep_painel/assets/img/banners/');
    $upload->Image($_FILES['banner_2'], 'banner-2-' . time(), 1600);
    if($upload->getResultado()){
        $dados['banner_2'] = $upload->getResultado();
    }
}else{
    unset($dados['banner_2']);
}

// Upload Banner 3
if($_FILES['banner_3']['tmp_name']){
    $upload = new Uploads('../../sheep_painel/assets/img/banners/');
    $upload->Image($_FILES['banner_3'], 'banner-3-' . time(), 1600);
    if($upload->getResultado()){
        $dados['banner_3'] = $upload->getResultado();
    }
}else{
    unset($dados['banner_3']);
}

$atualizar = new Atualizar();
$atualizar->Atualizando('configuracoes', $dados, "WHERE id = :id", "id=1");

if($atualizar->getResultado()){
    header("Location: " . HOME . "/sheep_painel/sheep.php?m=" . $redirect . "&sucesso=true");
}else{
    header("Location: " . HOME . "/sheep_painel/sheep.php?m=" . $redirect . "&erro=true");
}
?>
