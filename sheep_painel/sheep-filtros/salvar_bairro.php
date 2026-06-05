<?php
ob_start();
session_start();
require_once('../../sheep_core/config.php');

// Verificação de autenticação: apenas usuários logados (Admin ou Operador)
if (empty($_SESSION['sheep_user']) || !in_array(($_SESSION['sheep_user']['nivel'] ?? 'C'), ['M', 'O'], true)) {
    header('Location: ../sheep.php?acesso_negado=true');
    exit;
}

if(isset($_POST['sendBairro'])){
    $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
    unset($dados['sendBairro']);
    
    $dados['nome_bairro'] = strip_tags(trim($dados['nome_bairro']));
    $dados['taxa'] = floatval($dados['taxa']);
    $dados['status'] = strip_tags(trim($dados['status']));
    $dados['criado_em'] = date('Y-m-d H:i:s');

    $criar = new Criar();
    $criar->Criacao('bairros_entrega', $dados);

    if($criar->getResultado()){
        header("Location: ../sheep.php?m=sheep-bairros/index&sucesso=true");
    } else {
        header("Location: ../sheep.php?m=sheep-bairros/index&erro=true");
    }
} elseif(isset($_POST['updateBairro'])){
    $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
    unset($dados['updateBairro']);
    
    $id = $dados['id'];
    unset($dados['id']);
    
    $dados['nome_bairro'] = strip_tags(trim($dados['nome_bairro']));
    $dados['taxa'] = floatval($dados['taxa']);
    $dados['status'] = strip_tags(trim($dados['status']));

    $atualizar = new Atualizar();
    $atualizar->Atualizando('bairros_entrega', $dados, "WHERE id = :id", "id={$id}");

    if($atualizar->getResultado()){
        header("Location: ../sheep.php?m=sheep-bairros/index&sucesso=true");
    } else {
        header("Location: ../sheep.php?m=sheep-bairros/index&erro=true");
    }
} else {
    header("Location: ../sheep.php?m=sheep-bairros/index");
}

ob_end_flush();
?>
