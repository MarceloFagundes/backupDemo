<?php

/**********************************************************************
 * ********************************************************************
 * 
 * ********************************************************************
 * *************sheep**TECHNOLOGIES***********************************
 * ********************************************************************
 * TODOS OS DIREITOS RESERVADOS E CÓDIGO FONTE RASTREADO COM ARQUIVOS 
 * TODA SABEDORIA PARA CRIAR ESTES SISTEMAS VEM DO SANTO E ETERNOR PAI
 * O SANTO SENHOR DEUS DE ABRAÃO, ISSAC E JACÓ E DO MEU ÚNICO SENHOR 
 * O MESSIAS NOSSO SALVADOR, POIS A GLROIA É DO PAI E DO FILHO PARA SEMPRE
 * ********************************************************************
 * ********************************************************************
 */
session_start();
ob_start();

require('./sheep_core/config.php');

$sheep = new Ler();
$Link = new Link;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <?php $Link->getTags(); ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@1,600&display=swap">
    <link rel="stylesheet" type="text/css" href="<?= CAMINHO_TEMAS?>/assets/css/style.css?v=1.2">
    <link rel="shortcut icon" href="<?= CAMINHO_TEMAS?>/assets/img/msFavicon.png" type="image/x-icon">
</head>
<body>
    
   


<?php

if(!require_once($Link->getPatch())):
   echo 'Erro ao incluir arquivo de navegação!';
endif;
?>

    
 <!-- Plugins JS File -->
   


<!--INICIO JS DO SLIDE DO SITE -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

<!--INICIO JS DO SITE -->
    <script src="<?= CAMINHO_TEMAS?>/assets/js/ms.js"></script>
</body>
</html>
<?php
ob_end_flush();
?>
