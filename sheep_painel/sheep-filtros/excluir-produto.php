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
    header("Location: ../sheep.php?m=sheep-cardapio/index&permissao_negada=true");
    exit;
}

if($id){
    // Try to delete image first
    $ler = new Ler();
    $ler->Leitura('produtos', "WHERE id = :id", "id={$id}");
    if($ler->getResultado() && !empty($ler->getResultado()[0]['imagem'])) {
        $oldImg = '../../sheep_temas/assets/img/loja/' . $ler->getResultado()[0]['imagem'];
        if(file_exists($oldImg)){
            unlink($oldImg);
        }
    }

    $excluir = new Excluir();
    $excluir->Remover('produtos', "WHERE id = :id", "id={$id}");

    if($excluir->getResultado()){
        header("Location: ../sheep.php?m=sheep-cardapio/index&sucesso=true");
    } else {
        header("Location: ../sheep.php?m=sheep-cardapio/index&erro=true");
    }
} else {
    header("Location: ../sheep.php?m=sheep-cardapio/index&erro=true");
}

ob_end_flush();
?>
