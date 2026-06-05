<div class="main-content">
  <!-- INICIO NAVEGAÇÃO -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="sheep.php">Painel</a></li>
      <li class="breadcrumb-item"><a href="sheep.php?m=sheep-cardapio/index">Cardápio</a></li>
      <li class="breadcrumb-item active" aria-current="page">Novo Produto</li>
    </ol>
  </nav>
  <!-- FIM NAVEGAÇÃO -->

  <section class="section">
    <form action="sheep-filtros/criar-produto.php" method="post" enctype="multipart/form-data">
      <div class="section-body">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-footer text-right">
                <a href="sheep.php?m=sheep-cardapio/index" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Voltar</a>
              </div>

              <div class="card-header">
                <h4>Adicionar Novo Produto</h4>
              </div>
              <div class="card-body">

                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Imagem do Produto (Recomendado 500x500)</label>
                  <div class="col-sm-12 col-md-7">
                    <div id="image-preview" class="image-preview">
                      <label for="image-upload" id="image-label">Selecionar Imagem</label>
                      <input type="file" name="imagem" id="image-upload" required />
                      <img src="<?= HOME ?>/sheep_painel/assets/img/sem-imagem.png" style="width:100%; height:auto;" alt="Preview">
                    </div>
                  </div>
                </div>

                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Nome do Produto</label>
                  <div class="col-md-7">
                    <input type="text" class="form-control" name="nome" placeholder="Ex: Pizza Calabresa" required>
                  </div>
                </div>

                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Categoria</label>
                  <div class="col-sm-12 col-md-7">
                    <select class="form-control select2" name="categoria" required>
                      <option value="pizza">Pizza</option>
                      <option value="bebida">Bebida</option>
                      <option value="borda">Borda</option>
                      <option value="sobremesa">Sobremesa</option>
                      <option value="adicional">Adicional</option>
                    </select>
                  </div>
                </div>

                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Preço Base (R$)</label>
                  <div class="col-md-7">
                    <input type="number" step="0.01" min="0" class="form-control" name="preco" placeholder="Ex: 59.90" required>
                  </div>
                </div>

                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Preço Promocional (R$) <br><small class="text-muted">(Deixe em branco se não houver)</small></label>
                  <div class="col-md-7">
                    <input type="number" step="0.01" min="0" class="form-control" name="preco_promocional" placeholder="Ex: 49.90">
                  </div>
                </div>

                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Descrição Detalhada</label>
                  <div class="col-md-7">
                    <textarea class="summernote" name="descricao"></textarea>
                  </div>
                </div>

                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                  <div class="col-sm-12 col-md-7">
                    <button type="submit" class="btn btn-lg btn-primary" name="sendProduto">Adicionar Produto</button>
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
