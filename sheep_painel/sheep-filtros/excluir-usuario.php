<?php
ob_start();
session_start();
require_once('../../sheep_core/config.php');

if (empty($_SESSION['sheep_user']) || ($_SESSION['sheep_user']['nivel'] ?? 'C') !== 'M') {
    header("Location: ../sheep.php?acesso_negado=true");
    exit;
}
$idExcluir = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if(isset($_SESSION['sheep_user']['email']) && $_SESSION['sheep_user']['email'] === 'demo@admin.com'){
    header("Location: ../sheep.php?m=sheep-usuarios/index&permissao_negada=true");
    exit;
}

if($idExcluir){
    // Não permitir excluir o próprio usuário logado
    if($idExcluir == $_SESSION['sheep_user']['id']){
        header("Location: ../sheep.php?m=sheep-usuarios/index&erro=true&msg=nao_pode_excluir_si_mesmo");
        exit;
    }

    $excluir = new Excluir();
    $excluir->Remover('usuarios', "WHERE id = :id", "id={$idExcluir}");

    if($excluir->getResultado()){
        header("Location: ../sheep.php?m=sheep-usuarios/index&sucesso=true");
    } else {
        header("Location: ../sheep.php?m=sheep-usuarios/index&erro=true");
    }
} else {
    header("Location: ../sheep.php?m=sheep-usuarios/index&erro=true");
}

ob_end_flush();
?>
