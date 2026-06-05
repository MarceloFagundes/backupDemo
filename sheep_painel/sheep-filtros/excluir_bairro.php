<?php
ob_start();
session_start();
require_once('../../sheep_core/config.php');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
// Verificação de autenticação: apenas Administradores
if (empty($_SESSION['sheep_user']) || ($_SESSION['sheep_user']['nivel'] ?? 'C') !== 'M') {
    header('Location: ../sheep.php?acesso_negado=true');
    exit;
}

if(isset($_SESSION['sheep_user']['email']) && $_SESSION['sheep_user']['email'] === 'demo@admin.com'){
    header("Location: ../sheep.php?m=sheep-bairros/index&permissao_negada=true");
    exit;
}

if($id){
    $excluir = new Excluir();
    $excluir->Remover('bairros_entrega', "WHERE id = :id", "id={$id}");

    if($excluir->getResultado()){
        header("Location: ../sheep.php?m=sheep-bairros/index&sucesso=true");
    } else {
        header("Location: ../sheep.php?m=sheep-bairros/index&erro=true");
    }
} else {
    header("Location: ../sheep.php?m=sheep-bairros/index&erro=true");
}

ob_end_flush();
?>
