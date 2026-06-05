<?php
/*************************************************************************************
 * ***********************************************************************************
*SOMOS TODOS(A) OVELHAS DO NOSSO MESSIAS E FILHOS DO ANCIÃO DE DIAS O SENHOR DOS EXÉRCITOS
*QUE O NOSSO BENDITO PAI ABENÇOE SUA VIDA E SEUS PROJETOS
*NUNCA MINTA, SEJA SEMPRE HONESTO E JUSTO, USE ESTE FRAMEWORK PARA O BEM E NUNCA PARA O MAL
*QUE O ETERNO PAI TE DÊ SABEDORIA E MUITAS FELICIDADES:
*QUE A PAZ ESTEJA COM VOCÊ ASS: 
 * 
 
 * QUE A PAZ ESTE COM VOCÊ ASS: 
 * ************************************************************************************
 * *************sheep**TECHNOLOGIES****************************************************
* **************************************************************************************
*** ATUALIZADO UMA VEZ POR ANO *********************************************************
*TODOS OS DIREITOS RESERVADOS E CÓDIGO FONTE RASTREADO COM ARQUIVOS
*TODA SABEDORIA PARA CRIAR ESTES SISTEMAS VEM DO SANTO E ETERNO PAI
*O SANTO SENHOR DEUS DE ABRAÃO, ISAAC E JACÓ E DO MEU ÚNICO SENHOR
*O MESSIAS NOSSO SALVADOR, POIS A GLÓRIA É DO PAI E DO FILHO PARA SEMPRE
**
 * ************************************************************************************
 * Que você seja abençoado em tudo que fizer com este sistema, 
 * contanto que seja justo em todas as suas ações.
* Tudo correrá bem para você, se colocar o Criador dos céus e da terra, 
* e nosso Senhor Jesus, em primeiro lugar em sua vida.
* Muitas são as aflições dos justos, mas o Altíssimo os livra de todas.
 * ************************************************************************************
*/

//PASTA GERAL DE IMAGENS E ARQUIVOS CAMINHO DO PAINEL A MODELOS######################
define('SHEEP_IMG', './sheep-imagens/');
//CAMINHO PASTA IMAGEM PARA TEMAS 
define('SHEEP_IMG_URL', '/sheep_painel/sheep-imagens/');

define('SHEEP_IMG_LOGO', '../../../sheep_temas/sheep-imagens-logo/');

//IMAGENS PARA O LAYUT EXTERNO GERAL DE IMAGENS E ARQUIVOS CAMINHO DO PAINEL A MODELOS######################
define('SHEEP_IMG_PAINEL', './sheep_temas/sheep-imagens/');

//PASTA GERAL DE vídeos CAMINHO DO PAINEL A MODELOS######################
define('SHEEP_AUDIO', '../../../sheep_temas/sheep-midias/');

//AQUI IREI ADICIONAR VERSÃO E MODELO######################
define('SHEEP_VERSAO','Versão: [ 1.0.0 ]');

//AQUI TEXTO DA VERSÃO VERSÃO E MODELO######################
define('sheep','<center><h2>Painel Admin</h2></center><br>'
        . 'Bem-vindo ao sistema de gestão da Pizzaria Modelo.<br> '
        . '<b>Pizzaria Modelo</b><br>'
        . '<p>Controle de vendas e entregas. </p>');

/**********************************************************************
 * ********************************************************************
 * AUTO LOAD DO SITE 
 * 
 * ********************************************************************
 * ********************************************************************
*/
function sheep_classes($sheepClasses) {

    $sheepDiretorio = ['diretor', 'funcionarios',  'gerentes_operacionais', 'gerentes'];
    $sheepFiscaliza = null;

    foreach ($sheepDiretorio as $sheepNomeDiretorio):
        if (!$sheepFiscaliza && file_exists(__DIR__ . '/' ."{$sheepNomeDiretorio}" . '/' ."{$sheepClasses}.php") && !is_dir(__DIR__  . '/' . "{$sheepNomeDiretorio}" . '/' ."{$sheepClasses}.php")):
            include_once (__DIR__  . '/' . "{$sheepNomeDiretorio}" . '/' ."{$sheepClasses}.php");
            $sheepFiscaliza = true;
        endif;
    endforeach;

    if (!$sheepFiscaliza):
        echo "Não foi possível incluir {$sheepClasses}.php";
        exit();
    endif;
}

spl_autoload_register("sheep_classes");




/**********************************************************************
 * ********************************************************************
 * DADOS DO SITE 
 * 
 * ********************************************************************
*/

 

 define('SITENAME', 'Delivery');
 define('SITEDESC', 'SITE ');
 define('FONE', '');
 define('CNPJ', '');
 define('CELULAR', '');
 define('EMAIL', '');
 define('ENDERECO', '');
 define('NUMERO', '');
 define('CEP', '');
 define('CIDADE', '');
 define('ESTADO', '');
 define('CORREIOS_TOKEN', '123');
 
 
 
 
 
/**********************************************************************
 * ********************************************************************
 * PHPMAILER E SEND GRIND 
 * 
 * ********************************************************************
*/



define('EMAIL_PHPMAILER_SECURE', 'tls');
define('EMAIL_PHPMAILER_CHARSET', 'utf-8');
define('EMAIL_PHPMAILER_HOST', '');
define('EMAIL_PHPMAILER_USERNAME', '');
define('EMAIL_PHPMAILER_PASS', '');
define('EMAIL_PHPMAILER_PORT', '');
define('EMAIL_PHPMAILER_QUEM_ENVIA', EMAIL);
define('EMAIL_PHPMAILER_QUEM_ENVIA_NOME', SITENAME);
define('GOOGLE_TITULO', 'titulo do google');
define('GOOGLE_DESC', 'Descrição do google');
define('GOOGLE_TAGS', 'Descrição do google aqui');
define('RODAPE', 'Corporation dsdsd');
define('GOOGLE_VERIFY', 'verificador do google');



if (
    (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)) ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
) {
     $https = 'https://';
}else{
     $https = 'http://';
}
    
    define('HOME', $https. SHEEP_URL); 	
    define('PASTA_DO_PAINEL', '/sheep_painel/'); 	
    define('PASTA_DO_PAINEL_CLIENTE', '/cliente/'); 	
    define('URL_CAMINHO_PAINEL', HOME.PASTA_DO_PAINEL); 	
    define('URL_CAMINHO_PAINEL_CLIENTE', HOME.PASTA_DO_PAINEL_CLIENTE); 	
    define('SHEEP_LAYOUT', 'site');

    //LOGO DO SITE PARA TEMAS 
    define('SITELOGO', HOME . SHEEP_IMG_URL);
    define('FAVICON', HOME . SHEEP_IMG_URL);
    
    
    //INCLUDE_PATCH = CAMINHO_TEMAS;
    //REQUIRE_PATH = SOLICITAR_TEMAS;
    define('CAMINHO_TEMAS', HOME . '/' . 'sheep_temas' . '/' . SHEEP_LAYOUT);
    define('SOLICITAR_TEMAS', 'sheep_temas' . '/' . SHEEP_LAYOUT);
    define('MODELO', 'sheep_temas' . '/' . SHEEP_LAYOUT);
    

define('FILTROS','sheep.php?m=');

define('SHEEP_ICONE', 'assets/img/logo/icone.png');

// LOGO DO PAINEL
define('SHEEP_LOGO', 'assets/img/logo/zoufen-logo.svg');

// TITULO PAINEL
define('SHEEP_TITULO_PAINEL', 'Painel de Controle - Pizzaria Modelo');

// RODAPE TEXTO PAINEL
define('SHEEP_RODAPE_PAINEL', '© ' . date('Y') . ' Pizzaria Modelo - Todos os direitos reservados');



/**
 *  
*/  

?>
