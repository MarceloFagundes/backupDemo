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
      <li class="breadcrumb-item active" aria-current="page">Aparência & Banners</li>
    </ol>
  </nav>
  <!-- FIM NAVEGAÇÃO -->

  <!-- TABS DE NAVEGAÇÃO DAS CONFIGURAÇÕES -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card mb-0" style="border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.03);">
        <div class="card-body p-3">
          <ul class="nav nav-pills" style="gap: 5px;">
            <li class="nav-item">
              <a class="nav-link" href="sheep.php?m=sheep-dados/index" style="font-weight: 600; border-radius: 6px; color: #666;"><i class="fa fa-building mr-2"></i>Dados da Pizzaria</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="sheep.php?m=sheep-dados/aparencia" style="font-weight: 600; border-radius: 6px;"><i class="fa fa-paint-brush mr-2"></i>Aparência & Banners</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

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
           Configurações de aparência salvas com sucesso.
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
         $logo = "";
         $icone = "";
         $cor_primaria = "#ea1d2c";
         $link_instagram = "";
         $link_facebook = "";
         $banner_1 = "";
         $banner_2 = "";
         $banner_3 = "";
     }
     ?>

     <form action="sheep-filtros/atualizar-dados.php" method="post" enctype="multipart/form-data">
       <div class="section-body">
         
         <!-- IDENTIDADE VISUAL DA MARCA -->
         <div class="row">
           <div class="col-12">
             <div class="card">
               <div class="card-footer text-right">
                 <a href="sheep.php" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Voltar </a>
               </div>

               <div class="card-header">
                 <h4><i class="fa fa-magic text-danger mr-2"></i> Identidade Visual & Aparência</h4>
               </div>
               <div class="card-body">

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Logomarca (300x90)</label>
                   <div class="col-sm-12 col-md-7">
                     <div class="image-preview" style="height: auto; min-height: 100px; padding: 15px; background: #fdfdfd; border: 1px dashed #e4e6fc; border-radius: 5px;">
                       <label for="image-upload" class="btn btn-sm btn-primary" style="cursor: pointer; margin-bottom: 10px;">Buscar Nova Logo</label>
                       <input type="file" name="logo" id="image-upload" style="display: none;" onchange="previewImage(this, 'logo-preview')" />
                       <div class="mt-2">
                         <?php if(isset($logo) && $logo): ?>
                           <img id="logo-preview" src="<?= HOME ?>/sheep_painel/assets/img/logo/<?= $logo ?>" style="max-width: 250px; height: auto; border-radius: 8px;">
                         <?php else: ?>
                           <img id="logo-preview" src="assets/img/sem-imagem.png" style="max-width: 200px; height: auto; border-radius: 8px;">
                         <?php endif; ?>
                       </div>
                     </div>
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Favicon (50x50)</label>
                   <div class="col-sm-12 col-md-7">
                     <div class="image-preview" style="height: auto; min-height: 80px; padding: 15px; background: #fdfdfd; border: 1px dashed #e4e6fc; border-radius: 5px;">
                       <label for="image-upload-favicon" class="btn btn-sm btn-primary" style="cursor: pointer; margin-bottom: 10px;">Buscar Favicon</label>
                       <input type="file" name="icone" id="image-upload-favicon" style="display: none;" onchange="previewImage(this, 'favicon-preview')" />
                       <div class="mt-2">
                         <?php if(isset($icone) && $icone): ?>
                           <img id="favicon-preview" src="<?= HOME ?>/sheep_painel/assets/img/logo/<?= $icone ?>" style="width: 50px; height: 50px; border-radius: 4px; object-fit: cover;">
                         <?php else: ?>
                           <img id="favicon-preview" src="assets/img/sem-imagem.png" style="width: 50px; height: 50px; border-radius: 4px;">
                         <?php endif; ?>
                       </div>
                     </div>
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Cor Principal do Site</label>
                   <div class="col-md-7">
                     <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                       <input type="color" id="cor_primaria_input" class="form-control" name="cor_primaria" value="<?= $cor_primaria ?? '#ea1d2c' ?>" style="height: 50px; width: 80px; padding: 4px; cursor: pointer;">
                       <div style="display: flex; flex-direction: column; gap: 5px;">
                         <button type="button" onclick="document.getElementById('cor_primaria_input').value='#ea1d2c';" class="btn btn-sm btn-secondary" style="font-size: 1rem;">
                           <i class="fa fa-undo"></i> Restaurar Cor Padrão (Vermelho)
                         </button>
                         <small class="form-text text-muted">Clique para voltar ao vermelho clássico <strong>#ea1d2c</strong>.</small>
                       </div>
                     </div>
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Link Instagram</label>
                   <div class="col-md-7">
                     <input type="text" class="form-control" name="link_instagram" placeholder="https://instagram.com/suapizzaria" value="<?= $link_instagram ?? '' ?>">
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Link Facebook</label>
                   <div class="col-md-7">
                     <input type="text" class="form-control" name="link_facebook" placeholder="https://facebook.com/suapizzaria" value="<?= $link_facebook ?? '' ?>">
                   </div>
                 </div>

               </div>
             </div>
           </div>
         </div>

         <!-- BANNERS ROTATIVOS DA HOME -->
         <div class="row">
           <div class="col-12">
             <div class="card">
               <div class="card-header">
                 <h4><i class="fa fa-picture-o text-danger mr-2"></i> Banners da Página Inicial (Recomendado: 1920x800)</h4>
               </div>
               <div class="card-body">

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Banner Superior 1</label>
                   <div class="col-sm-12 col-md-7">
                     <input type="file" name="banner_1" class="form-control" onchange="previewImage(this, 'banner1-preview')" />
                     <div class="mt-2">
                       <?php if(isset($banner_1) && $banner_1): ?>
                         <img id="banner1-preview" src="<?= HOME ?>/sheep_painel/assets/img/banners/<?= $banner_1 ?>" style="width:100%; max-height:180px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                       <?php else: ?>
                         <img id="banner1-preview" src="assets/img/sem-imagem.png" style="width:200px; height:auto; border-radius: 8px;">
                       <?php endif; ?>
                     </div>
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Banner Superior 2</label>
                   <div class="col-sm-12 col-md-7">
                     <input type="file" name="banner_2" class="form-control" onchange="previewImage(this, 'banner2-preview')" />
                     <div class="mt-2">
                       <?php if(isset($banner_2) && $banner_2): ?>
                         <img id="banner2-preview" src="<?= HOME ?>/sheep_painel/assets/img/banners/<?= $banner_2 ?>" style="width:100%; max-height:180px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                       <?php else: ?>
                         <img id="banner2-preview" src="assets/img/sem-imagem.png" style="width:200px; height:auto; border-radius: 8px;">
                       <?php endif; ?>
                     </div>
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Banner Superior 3</label>
                   <div class="col-sm-12 col-md-7">
                     <input type="file" name="banner_3" class="form-control" onchange="previewImage(this, 'banner3-preview')" />
                     <div class="mt-2">
                       <?php if(isset($banner_3) && $banner_3): ?>
                         <img id="banner3-preview" src="<?= HOME ?>/sheep_painel/assets/img/banners/<?= $banner_3 ?>" style="width:100%; max-height:180px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                       <?php else: ?>
                         <img id="banner3-preview" src="assets/img/sem-imagem.png" style="width:200px; height:auto; border-radius: 8px;">
                       <?php endif; ?>
                     </div>
                   </div>
                 </div>

                 <input type="hidden" name="usuario" value="<?= $_SESSION['sheep_user']['id'] ?>">
                 <input type="hidden" name="sheep_firewall" value="<?= date('YmdHis') ?>">
                 <input type="hidden" name="tipo" value="geral">
                 <input type="hidden" name="id" value="1">
                 <input type="hidden" name="redirecionar" value="sheep-dados/aparencia">

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                   <div class="col-sm-12 col-md-7">
                     <button type="submit" class="btn btn-lg btn-primary" name="sendSheep">Salvar Aparência e Banners</button>
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

<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById(previewId);
            preview.src = e.target.result;
            preview.style.display = 'block';
            if(previewId.includes('banner')) {
                preview.style.width = '100%';
                preview.style.maxHeight = '180px';
                preview.style.objectFit = 'cover';
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
