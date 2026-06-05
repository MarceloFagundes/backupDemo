<?php
ob_start();
session_start();
require_once('../../sheep_core/config.php');

if(isset($_POST['sendProduto'])){
    $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
    unset($dados['sendProduto']);
    
    if(isset($dados['nome'])) $dados['nome'] = strip_tags(trim($dados['nome']));
    if(isset($dados['descricao'])) $dados['descricao'] = strip_tags(trim($dados['descricao']));
    
    $id = $dados['id'];
    unset($dados['id']);

    // Handle Image Upload (Optional on Edit)
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $imagemNome = 'produto-' . time() . '-' . rand(1000, 9999) . '.' . $ext;
        $destino = '../../sheep_temas/site/assets/img/loja/' . $imagemNome;
        
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
            $dados['imagem'] = $imagemNome;
            
            // Try to delete old image
            $ler = new Ler();
            $ler->Leitura('produtos', "WHERE id = :id", "id={$id}");
            if($ler->getResultado() && !empty($ler->getResultado()[0]['imagem'])) {
                $oldImg = '../../sheep_temas/site/assets/img/loja/' . $ler->getResultado()[0]['imagem'];
                if(file_exists($oldImg)){
                    unlink($oldImg);
                }
            }
        }
    }
    
    $dados['preco'] = floatval($dados['preco']);
    $dados['preco_promocional'] = !empty($dados['preco_promocional']) ? floatval($dados['preco_promocional']) : null;

    $atualizar = new Atualizar();
    $atualizar->Atualizando('produtos', $dados, "WHERE id = :id", "id={$id}");

    if($atualizar->getResultado()){
        header("Location: ../sheep.php?m=sheep-cardapio/index&sucesso=true");
    } else {
        header("Location: ../sheep.php?m=sheep-cardapio/index&erro=true");
    }
} else {
    header("Location: ../sheep.php?m=sheep-cardapio/index&erro=true");
}

ob_end_flush();
?>
