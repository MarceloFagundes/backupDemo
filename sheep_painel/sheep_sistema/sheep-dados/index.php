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
      <li class="breadcrumb-item active" aria-current="page">Dados da Pizzaria</li>
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
              <a class="nav-link active" href="sheep.php?m=sheep-dados/index" style="font-weight: 600; border-radius: 6px;"><i class="fa fa-building mr-2"></i>Dados da Pizzaria</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="sheep.php?m=sheep-dados/aparencia" style="font-weight: 600; border-radius: 6px; color: #666;"><i class="fa fa-paint-brush mr-2"></i>Aparência & Banners</a>
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
           Configurações salvas com sucesso.
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

     <!-- INICIO TOKEN -->
     <?php
     $token_expirou = filter_input(INPUT_GET, 'token_expirou', FILTER_VALIDATE_BOOLEAN);
     if ($token_expirou):
     ?>
       <div class="alert alert-danger alert-has-icon">
         <div class="alert-icon"><i class="fa fa-lightbulb-o"></i></div>
         <div class="alert-body">
           <div class="alert-title">Erro!</div>
           Seu token de sessão expirou!
         </div>
       </div>
     <?php endif; ?>

     <?php
     $token_invalido = filter_input(INPUT_GET, 'token_invalido', FILTER_VALIDATE_BOOLEAN);
     if ($token_invalido):
     ?>
       <div class="alert alert-danger alert-has-icon">
         <div class="alert-icon"><i class="fa fa-lightbulb-o"></i></div>
         <div class="alert-body">
           <div class="alert-title">Erro!</div>
           Seu token de sessão é inválido!
         </div>
       </div>
     <?php endif; ?>

     <?php
     $clique = filter_input(INPUT_GET, 'clique', FILTER_VALIDATE_BOOLEAN);
     if ($clique):
     ?>
       <div class="alert alert-danger alert-has-icon">
         <div class="alert-icon"><i class="fa fa-lightbulb-o"></i></div>
         <div class="alert-body">
           <div class="alert-title">Erro!</div>
           O que está tentando fazer? Dê um clique por vez
         </div>
       </div>
     <?php endif; ?>
     <!-- FIM TOKEN -->

     <?php
     $ler = new Ler();
     $ler->Leitura('configuracoes', "WHERE id = :id", "id=1");
     if ($ler->getResultado()) {
         $dados = $ler->getResultado()[0];
         extract($dados);
     } else {
         $nome = "";
         $descricao = "";
         $cnpj = "";
         $email = "";
         $senha_email = "";
         $fone = "";
         $whatsapp = "";
         $endereco = "";
         $numero = "";
         $cep = "";
     }
     ?>

     <form action="sheep-filtros/atualizar-dados.php" method="post" enctype="multipart/form-data">
       <div class="section-body">
         <div class="row">
           <div class="col-12">
             <div class="card">
               <div class="card-footer text-right">
                 <a href="sheep.php" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Voltar </a>
               </div>

               <div class="card-header">
                 <h4>Dados Cadastrais & Contatos da Pizzaria</h4>
               </div>
               <div class="card-body">

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Nome da Pizzaria</label>
                   <div class="col-md-7">
                     <input type="text" class="form-control" name="nome" placeholder="Digite o nome da pizzaria" value="<?= $nome ?>" required>
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Descrição / Sobre Nós</label>
                   <div class="col-md-7">
                     <textarea class="summernote" name="descricao"><?= $descricao ?></textarea>
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">CNPJ (Opcional)</label>
                   <div class="col-md-7">
                     <input type="text" id="cnpj" class="form-control" name="cnpj" placeholder="CNPJ da empresa" value="<?= $cnpj ?>">
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">E-mail Principal</label>
                   <div class="col-md-7">
                     <input type="email" class="form-control" name="email" placeholder="E-mail para contatos e notificações" value="<?= $email ?>">
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Senha SMTP (Opcional)</label>
                   <div class="col-md-7">
                     <input type="password" class="form-control" name="senha_email" placeholder="Senha SMTP do e-mail" value="<?= $senha_email ?>">
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Telefone Comercial</label>
                   <div class="col-md-7">
                     <input type="text" id="fone" class="form-control" name="fone" placeholder="Telefone fixo" value="<?= $fone ?>">
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">WhatsApp / Celular</label>
                   <div class="col-md-7">
                     <input type="text" id="cel" class="form-control" name="whatsapp" placeholder="WhatsApp para pedidos" value="<?= $whatsapp ?>">
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Endereço</label>
                   <div class="col-md-4">
                     <input type="text" class="form-control" name="endereco" placeholder="Rua / Avenida" value="<?= $endereco ?>">
                   </div>
                   <div class="col-md-3">
                     <input type="number" class="form-control" name="numero" placeholder="Número" value="<?= $numero ?>">
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">CEP</label>
                   <div class="col-md-7">
                     <input type="text" id="cepmj" class="form-control" name="cep" placeholder="CEP" value="<?= $cep ?>">
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Estado</label>
                   <div class="col-sm-12 col-md-7">
                     <select class="form-control select2" name="estado">
                       <option value="">Paraná</option>
                     </select>
                   </div>
                 </div>

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Cidade</label>
                   <div class="col-sm-12 col-md-7">
                     <select class="form-control select2" name="cidade">
                       <option value="">Selecione a cidade</option>
                     </select>
                   </div>
                 </div>

                 <input type="hidden" name="usuario" value="<?= $_SESSION['sheep_user']['id'] ?>">
                 <input type="hidden" name="sheep_firewall" value="<?= date('YmdHis') ?>">
                 <input type="hidden" name="tipo" value="geral">
                 <input type="hidden" name="id" value="1">
                 <input type="hidden" name="redirecionar" value="sheep-dados/index">

                 <div class="form-group row mb-4">
                   <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                   <div class="col-sm-12 col-md-7">
                     <button type="submit" class="btn btn-lg btn-primary" name="sendSheep">Salvar Dados Cadastrais</button>
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
