<?php
ob_start();
session_start();
require_once('../../sheep_core/config.php');

if(isset($_POST['sendProduto'])){
    $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
    unset($dados['sendProduto']);
    
    if(isset($dados['nome'])) $dados['nome'] = strip_tags(trim($dados['nome']));
    if(isset($dados['descricao'])) $dados['descricao'] = strip_tags(trim($dados['descricao']));

    // Handle Image Upload
    $imagemNome = '';
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $imagemNome = 'produto-' . time() . '-' . rand(1000, 9999) . '.' . $ext;
        $destino = '../../sheep_temas/site/assets/img/loja/' . $imagemNome;
        
        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
            $imagemNome = ''; // fallback
        }
    }
    
    $dados['imagem'] = $imagemNome;
    $dados['preco'] = floatval($dados['preco']);
    $dados['preco_promocional'] = !empty($dados['preco_promocional']) ? floatval($dados['preco_promocional']) : null;
    $dados['criado_em'] = date('Y-m-d H:i:s');

    $criar = new Criar();
    $criar->Criacao('produtos', $dados);

    if($criar->getResultado()){
        header("Location: ../sheep.php?m=sheep-cardapio/index&sucesso=true");
    } else {
        header("Location: ../sheep.php?m=sheep-cardapio/index&erro=true");
    }
} else {
    header("Location: ../sheep.php?m=sheep-cardapio/index&erro=true");
}

ob_end_flush();
?>
