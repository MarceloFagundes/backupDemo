<?php
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
      <li class="breadcrumb-item active" aria-current="page">Cardápio</li>
    </ol>
  </nav>
  <!-- FIM NAVEGAÇÃO -->

  <section class="section">
    <div class="section-body">
      <!--INICIO LINKS TOPO -->
      <?php include_once 'topo.php'; ?>
      <!--FIM LINKS TOPO -->
      <br>

      <!-- INICIO MENSAGENS -->
      <?php
      $sucesso = filter_input(INPUT_GET, 'sucesso', FILTER_VALIDATE_BOOLEAN);
      if ($sucesso):
      ?>
        <div class="alert alert-success alert-has-icon">
          <div class="alert-icon"><i class="fa fa-lightbulb-o"></i></div>
          <div class="alert-body">
            <div class="alert-title">Sucesso!</div>
            Operação realizada com sucesso!
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
            Ocorreu um erro ao processar sua solicitação.
          </div>
        </div>
      <?php endif; ?>
      <!-- FIM MENSAGENS -->

      <!-- INICIO TABELA -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h4>Produtos Cadastrados</h4>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped table-hover" id="save-stage" style="width:100%;">
                  <thead>
                    <tr>
                      <th style="width: 80px;">Imagem</th>
                      <th>ID</th>
                      <th>Nome</th>
                      <th>Categoria</th>
                      <th>Preço</th>
                      <th>Preço Promo</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>

                    <?php
                    $ler = new Ler();
                    $ler->Leitura('produtos', "ORDER BY id DESC");
                    if ($ler->getResultado()):
                      foreach ($ler->getResultado() as $produto):
                        extract($produto);
                    ?>
                        <tr>
                          <td>
                            <?php if(!empty($imagem)): ?>
                              <img src="<?= mondiniPainelProdutoImagemUrl($imagem) ?>" alt="<?= $nome ?>" style="width:50px; height:50px; object-fit:cover; border-radius:5px;" onerror="this.onerror=null;this.src='<?= HOME ?>/sheep_painel/assets/img/sem-imagem.png';">
                            <?php else: ?>
                              <img src="<?= HOME ?>/sheep_painel/assets/img/sem-imagem.png" alt="Sem Imagem" style="width:50px; height:50px; object-fit:cover; border-radius:5px;">
                            <?php endif; ?>
                          </td>
                          <td><?= $id ?></td>
                          <td><strong><?= $nome ?></strong></td>
                          <td><span class="badge badge-light"><?= ucfirst($categoria) ?></span></td>
                          <td>R$ <?= number_format($preco, 2, ',', '.') ?></td>
                          <td><?= (!empty($preco_promocional) && $preco_promocional > 0 ? 'R$ '.number_format($preco_promocional, 2, ',', '.') : '---') ?></td>
                          <td>
                            <a href="sheep.php?m=sheep-cardapio/editar&id=<?= $id ?>" class="text-primary" style="font-size: 1.2rem; margin-right: 10px;" title="Editar"><i class="fa fa-edit"></i></a>
                            
                            <a href="sheep-filtros/excluir-produto.php?id=<?= $id ?>" class="text-danger" style="font-size: 1.2rem;" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este produto do cardápio?')">
                                <i class="fa fa-trash"></i>
                            </a>
                          </td>
                        </tr>
                    <?php
                      endforeach;
                    endif;
                    ?>

                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>
