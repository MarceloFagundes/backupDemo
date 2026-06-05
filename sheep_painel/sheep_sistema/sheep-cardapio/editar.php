<?php
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if(!$id){
    header("Location: sheep.php?m=sheep-cardapio/index&erro=true");
    exit;
}

$ler = new Ler();
$ler->Leitura('produtos', "WHERE id = :id", "id={$id}");
if(!$ler->getResultado()){
    header("Location: sheep.php?m=sheep-cardapio/index&erro=true");
    exit;
}
$produto = $ler->getResultado()[0];
extract($produto);

if (!function_exists('mondiniPainelProdutoImagemUrl')) {
    function mondiniPainelProdutoImagemUrl($imagem)
    {
        $imagem = trim((string)$imagem);
        if ($imagem === '') {
            return HOME . '/sheep_painel/assets/img/sem-imagem.png';
        }

        if (preg_match('/^https?:\/\//i', $imagem)) {
            return $imagem;
        }

        if (strpos($imagem, '/') !== false || strpos($imagem, '\\') !== false) {
            return CAMINHO_TEMAS . '/' . ltrim(str_replace('\\', '/', $imagem), '/');
        }

        return CAMINHO_TEMAS . '/assets/img/loja/' . $imagem;
    }
}
?>
<div class="main-content">
  <!-- INICIO NAVEGAÇÃO -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="sheep.php">Painel</a></li>
      <li class="breadcrumb-item"><a href="sheep.php?m=sheep-cardapio/index">Cardápio</a></li>
      <li class="breadcrumb-item active" aria-current="page">Editar Produto</li>
    </ol>
  </nav>
  <!-- FIM NAVEGAÇÃO -->

  <section class="section">
    <form action="sheep-filtros/atualizar-produto.php" method="post" enctype="multipart/form-data">
      <div class="section-body">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-footer text-right">
                <a href="sheep.php?m=sheep-cardapio/index" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Voltar</a>
              </div>

              <div class="card-header">
                <h4>Editar Produto: <?= $nome ?></h4>
              </div>
              <div class="card-body">

                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Imagem do Produto (Recomendado 500x500)</label>
                  <div class="col-sm-12 col-md-7">
                    <div id="image-preview" class="image-preview">
                      <label for="image-upload" id="image-label">Alterar Imagem</label>
                      <input type="file" name="imagem" id="image-upload" />
                      <?php if(!empty($imagem)): ?>
                        <img src="<?= mondiniPainelProdutoImagemUrl($imagem) ?>" style="width:100%; height:auto;" alt="Preview" onerror="this.onerror=null;this.src='<?= HOME ?>/sheep_painel/assets/img/sem-imagem.png';">
                      <?php else: ?>
                        <img src="<?= HOME ?>/sheep_painel/assets/img/sem-imagem.png" style="width:100%; height:auto;" alt="Preview">
                      <?php endif; ?>
                    </div>
                    <small class="text-muted">Deixe em branco para manter a imagem atual.</small>
                  </div>
                </div>

                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Nome do Produto</label>
                  <div class="col-md-7">
                    <input type="text" class="form-control" name="nome" value="<?= $nome ?>" required>
                  </div>
                </div>

                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Categoria</label>
                  <div class="col-sm-12 col-md-7">
                    <select class="form-control select2" name="categoria" required>
                      <option value="pizza" <?= ($categoria == 'pizza' ? 'selected' : '') ?>>Pizza</option>
                      <option value="bebida" <?= ($categoria == 'bebida' ? 'selected' : '') ?>>Bebida</option>
                      <option value="borda" <?= ($categoria == 'borda' ? 'selected' : '') ?>>Borda</option>
                      <option value="sobremesa" <?= ($categoria == 'sobremesa' ? 'selected' : '') ?>>Sobremesa</option>
                      <option value="adicional" <?= ($categoria == 'adicional' ? 'selected' : '') ?>>Adicional</option>
                    </select>
                  </div>
                </div>

                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Preço Base (R$)</label>
                  <div class="col-md-7">
                    <input type="number" step="0.01" min="0" class="form-control" name="preco" value="<?= $preco ?>" required>
                  </div>
                </div>

                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Preço Promocional (R$) <br><small class="text-muted">(Deixe em branco se não houver)</small></label>
                  <div class="col-md-7">
                    <input type="number" step="0.01" min="0" class="form-control" name="preco_promocional" value="<?= $preco_promocional ?>">
                  </div>
                </div>

                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Descrição Detalhada</label>
                  <div class="col-md-7">
                    <textarea class="summernote" name="descricao"><?= $descricao ?></textarea>
                  </div>
                </div>

                <input type="hidden" name="id" value="<?= $id ?>">

                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                  <div class="col-sm-12 col-md-7">
                    <button type="submit" class="btn btn-lg btn-primary" name="sendProduto">Atualizar Produto</button>
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
