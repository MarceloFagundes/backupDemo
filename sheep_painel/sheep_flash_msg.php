 <script>
$(document).ready(function() {
   

<?php 

$shee_uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
if (strrpos($shee_uri,  'sucesso')){ ?>
        
//abre o modal sucesso
$('#sucesso').modal('show');

//FECHA SHEEP o modal em 3 segundos
setTimeout(function() {
      $('#sucesso').modal('hide');
    }, 3000); // 3000 = 3 segundos
    
<?php }; ?> 
    
    
    <?php 



if (strrpos($shee_uri,  'erro')){ ?>	
        
//abre o modal sucesso
$('#erro').modal('show');

//FECHA SHEEP o modal em 3 segundos
setTimeout(function() {
      $('#erro').modal('hide');
    }, 3000); // 3000 = 3 segundos
    
<?php }; ?>
    
  
    
    <?php 

if (strrpos($shee_uri,  'erroTemConteudo')){ ?>	
        
//abre o modal sucesso
$('#erroTemConteudo').modal('show');

//FECHA SHEEP o modal em 3 segundos
setTimeout(function() {
      $('#erroTemConteudo').modal('hide');
    }, 3000); // 3000 = 3 segundos
    
<?php }; ?> 
    
    
    <?php 

if (strrpos($shee_uri,  'delete')){ ?>	
        
//abre o modal sucesso
$('#delete').modal('show');

//FECHA SHEEP o modal em 3 segundos
setTimeout(function() {
      $('#delete').modal('hide');
    }, 3000); // 3000 = 3 segundos
    
<?php }; ?> 
    
    
    
    <?php 


if (strrpos($shee_uri,  'sheep_firewall')){ ?>	
        
//abre o modal sucesso
$('#sheep_firewall').modal('show');

//FECHA SHEEP o modal em 3 segundos
setTimeout(function() {
      $('#webtec-firewall').modal('hide');
    }, 5000); // 3000 = 3 segundos
    
<?php }; ?> 
    
    <?php 


if (strrpos($shee_uri,  'imprimir')){ ?>	
        
//abre o modal sucesso
window.open();

    
<?php }; ?> 
<?php 
if (strpos($shee_uri, 'permissao_negada') !== false){ ?>	
$('#permissao_negada').modal('show');
setTimeout(function() {
      $('#permissao_negada').modal('hide');
    }, 4000); 
<?php }; ?> 
  
});


</script>