<?php
// Protection to ensure file runs within admin context
if (!class_exists('Ler')) {
    exit('Acesso direto negado.');
}
?>

<!-- Main Content -->
<div class="main-content">

  <!-- INICIO NAVEGAÇÃO -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="sheep.php">Inicio</a></li>
      <li class="breadcrumb-item active" aria-current="page">MercadoPago</li>
    </ol>
  </nav>
  <!-- FIM NAVEGAÇÃO -->

  <section class="section">

     <!-- INICIO MENSAGENS DE SUCESSO E ERRO -->
     <?php
     $sucesso = filter_input(INPUT_GET, 'sucesso', FILTER_VALIDATE_BOOLEAN);
     if ($sucesso):
     ?>
       <div class="alert alert-success alert-has-icon">
         <div class="alert-icon"><i class="fa fa-lightbulb-o"></i></div>
         <div class="alert-body">
           <div class="alert-title">Sucesso!</div>
           Configurações do MercadoPago salvas com sucesso.
         </div>
       </div>
     <?php endif; ?>

     <?php
     $erro = filter_input(INPUT_GET, 'erro', FILTER_VALIDATE_BOOLEAN);
     if ($erro):
     ?>
       <div class="alert alert-danger alert-has-icon">
         <div class="alert-icon"><i class="fa fa-lightbulb-o"></i></div>
         <div class="alert-body">
           <div class="alert-title">Erro!</div>
           Ocorreu um erro ao salvar as configurações.
         </div>
       </div>
     <?php endif; ?>
     <!-- FIM MENSAGENS DE SUCESSO E ERRO -->

     <?php
      $ler = new Ler();
      $ler->Leitura('configuracoes', "WHERE id = :id", "id=1");
      if ($ler->getResultado()) {
          $dados = $ler->getResultado()[0];
          extract($dados);
      } else {
          $mp_public_key = "";
          $mp_access_token = "";
      }
      ?>

      <form action="sheep-filtros/atualizar-dados.php" method="post">
        <div class="section-body">
          <div class="row">
            <div class="col-12">
              <div class="card" style="border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.03);">
                <div class="card-footer text-right">
                  <a href="sheep.php" class="btn btn-primary" style="border-radius: 6px;"><i class="fa fa-arrow-left"></i> Voltar </a>
                </div>

                <div class="card-header" style="border-bottom: 1px solid #f9f9f9; padding: 20px 25px;">
                  <h4 style="font-weight: 700; color: #130f40;"><i class="fa fa-credit-card mr-2" style="font-size: 1.5rem; color: #009ee3;"></i> Mercado Pago</h4>
                </div>
                <div class="card-body" style="padding: 25px;">

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3" style="font-weight: 600; color: #444;">Public Key</label>
                   <div class="col-md-7">
                     <input type="text" class="form-control" name="mp_public_key" placeholder="TEST-..." value="<?= $mp_public_key ?? '' ?>" style="border-radius: 6px; border: 1px solid #ddd;">
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3" style="font-weight: 600; color: #444;">Access Token</label>
                   <div class="col-md-7">
                     <input type="text" class="form-control" name="mp_access_token" placeholder="TEST-..." value="<?= $mp_access_token ?? '' ?>" style="border-radius: 6px; border: 1px solid #ddd;">
                   </div>
                 </div>

                 <input type="hidden" name="usuario" value="<?= $_SESSION['sheep_user']['id'] ?>">
                 <input type="hidden" name="sheep_firewall" value="<?= date('YmdHis') ?>">
                 <input type="hidden" name="tipo" value="geral">
                 <input type="hidden" name="id" value="1">
                 <input type="hidden" name="redirecionar" value="sheep-dados/pagamentos">

                  <div class="form-group row mb-4">
                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                    <div class="col-sm-12 col-md-7">
                      <button type="submit" class="btn btn-lg btn-primary" name="sendSheep" style="border-radius: 6px; font-weight: 600;">Salvar</button>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>

        </div>
      </form>
   </section>
 </div>
