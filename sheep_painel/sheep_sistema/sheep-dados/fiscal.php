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
      <li class="breadcrumb-item active" aria-current="page">ERP & Nota Fiscal</li>
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
           Configurações fiscais salvas com sucesso.
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
         $bling_api_key = "";
         $focus_nfe_token = "";
         $fiscal_ambiente = "homologacao";
     }
     ?>

     <form action="sheep-filtros/atualizar-dados.php" method="post">
       <div class="section-body">
         <div class="row">
           <div class="col-12">
             <div class="card">
               <div class="card-footer text-right">
                 <a href="sheep.php" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Voltar </a>
               </div>

               <div class="card-header">
                 <h4><i class="fa fa-archive text-success mr-2"></i> Integração ERP (Bling)</h4>
               </div>
               <div class="card-body">

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">API Key Bling</label>
                   <div class="col-md-7">
                     <input type="text" class="form-control" name="bling_api_key" placeholder="Digite sua API Key do Bling" value="<?= $bling_api_key ?? '' ?>">
                     <small class="form-text text-muted">Utilizado para sincronização de estoque de insumos e gestão integrada de pedidos no ERP Bling.</small>
                   </div>
                 </div>

               </div>
             </div>
           </div>
         </div>

         <div class="row">
           <div class="col-12">
             <div class="card">
               <div class="card-header">
                 <h4><i class="fa fa-file-text-o text-success mr-2"></i> Emissor de Notas Fiscais (Focus NFe)</h4>
               </div>
               <div class="card-body">

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Token Focus NFe</label>
                   <div class="col-md-7">
                     <input type="text" class="form-control" name="focus_nfe_token" placeholder="Token de Integração Focus NFe" value="<?= $focus_nfe_token ?? '' ?>">
                     <small class="form-text text-muted">Para emissão automática e simplificada de NFC-e (Nota Fiscal de Consumidor Eletrônica) aos seus clientes.</small>
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Ambiente Fiscal</label>
                   <div class="col-md-7">
                     <select class="form-control select2" name="fiscal_ambiente">
                       <option value="homologacao" <?= (isset($fiscal_ambiente) && $fiscal_ambiente == 'homologacao') ? 'selected' : '' ?>>Homologação (Ambiente de Testes / Sem Valor Fiscal)</option>
                       <option value="producao" <?= (isset($fiscal_ambiente) && $fiscal_ambiente == 'producao') ? 'selected' : '' ?>>Produção (Ambiente Real / Emitir Notas de Verdade)</option>
                     </select>
                     <small class="form-text text-muted">Atenção: Mantenha em Homologação até que todo o credenciamento na SEFAZ de seu estado esteja concluído.</small>
                   </div>
                 </div>

                 <input type="hidden" name="usuario" value="<?= $_SESSION['sheep_user']['id'] ?>">
                 <input type="hidden" name="sheep_firewall" value="<?= date('YmdHis') ?>">
                 <input type="hidden" name="tipo" value="geral">
                 <input type="hidden" name="id" value="1">
                 <input type="hidden" name="redirecionar" value="sheep-dados/fiscal">

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                   <div class="col-sm-12 col-md-7">
                     <button type="submit" class="btn btn-lg btn-success" name="sendSheep">Salvar Configurações Fiscais</button>
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
