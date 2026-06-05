<div class="main-content">
  <!-- INICIO NAVEGAÇÃO -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="sheep.php">Painel</a></li>
      <li class="breadcrumb-item active" aria-current="page">Zonas de Entrega (Bairros)</li>
    </ol>
  </nav>
  <!-- FIM NAVEGAÇÃO -->

  <section class="section">
    <div class="section-body">
      
      <!-- INICIO MENSAGENS -->
      <?php
      $sucesso = filter_input(INPUT_GET, 'sucesso', FILTER_VALIDATE_BOOLEAN);
      if ($sucesso):
      ?>
        <div class="alert alert-success alert-has-icon">
          <div class="alert-icon"><i class="fa fa-check-circle"></i></div>
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
          <div class="alert-icon"><i class="fa fa-times-circle"></i></div>
          <div class="alert-body">
            <div class="alert-title">Erro!</div>
            Ocorreu um erro ao processar sua solicitação.
          </div>
        </div>
      <?php endif; ?>
      <!-- FIM MENSAGENS -->

      <div class="row">
        <!-- FORMULÁRIO DE CADASTRO -->
        <div class="col-12 col-md-4">
          <div class="card">
            <div class="card-header">
              <h4>Adicionar Novo Bairro</h4>
            </div>
            <div class="card-body">
              <form action="sheep-filtros/salvar_bairro.php" method="post">
                <div class="form-group">
                  <label>Nome do Bairro</label>
                  <input type="text" name="nome_bairro" class="form-control" placeholder="Ex: Centro" required>
                </div>
                <div class="form-group">
                  <label>Taxa de Entrega (R$)</label>
                  <input type="number" step="0.01" min="0" name="taxa" class="form-control" placeholder="Ex: 5.00" required>
                </div>
                <div class="form-group">
                  <label>Status</label>
                  <select name="status" class="form-control" required>
                    <option value="ativo">Ativo (Fazemos Entrega)</option>
                    <option value="inativo">Inativo (Não Entregamos)</option>
                  </select>
                </div>
                <button type="submit" name="sendBairro" class="btn btn-primary btn-lg btn-block">
                  Salvar Bairro
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- LISTA DE BAIRROS -->
        <div class="col-12 col-md-8">
          <div class="card">
            <div class="card-header">
              <h4>Bairros Cadastrados</h4>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped table-hover" id="save-stage" style="width:100%;">
                  <thead>
                    <tr>
                      <th>Bairro</th>
                      <th>Taxa</th>
                      <th>Status</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $ler = new Ler();
                    $ler->Leitura('bairros_entrega', "ORDER BY nome_bairro ASC");
                    if ($ler->getResultado()):
                      foreach ($ler->getResultado() as $bairro):
                        extract($bairro);
                    ?>
                        <tr>
                          <td><strong><?= htmlspecialchars($nome_bairro) ?></strong></td>
                          <td style="color:#27ae60; font-weight:bold;">R$ <?= number_format($taxa, 2, ',', '.') ?></td>
                          <td>
                            <?php if($status == 'ativo'): ?>
                                <span class="badge badge-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inativo</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <a href="#" class="text-primary" style="font-size: 1.2rem; margin-right: 10px;" title="Editar" data-toggle="modal" data-target="#editModal<?= $id ?>">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a href="sheep-filtros/excluir_bairro.php?id=<?= $id ?>" class="text-danger" style="font-size: 1.2rem;" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este bairro? Clientes não poderão mais selecioná-lo.')">
                                <i class="fa fa-trash"></i>
                            </a>
                          </td>
                        </tr>

                        <!-- Modal Edição -->
                        <div class="modal fade" id="editModal<?= $id ?>" tabindex="-1" role="dialog" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title">Editar Bairro</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <form action="sheep-filtros/salvar_bairro.php" method="post">
                                  <div class="modal-body">
                                    <input type="hidden" name="id" value="<?= $id ?>">
                                    <div class="form-group">
                                      <label>Nome do Bairro</label>
                                      <input type="text" name="nome_bairro" class="form-control" value="<?= htmlspecialchars($nome_bairro) ?>" required>
                                    </div>
                                    <div class="form-group">
                                      <label>Taxa de Entrega (R$)</label>
                                      <input type="number" step="0.01" min="0" name="taxa" class="form-control" value="<?= $taxa ?>" required>
                                    </div>
                                    <div class="form-group">
                                      <label>Status</label>
                                      <select name="status" class="form-control" required>
                                        <option value="ativo" <?= $status == 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                        <option value="inativo" <?= $status == 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                      </select>
                                    </div>
                                  </div>
                                  <div class="modal-footer bg-whitesmoke br">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" name="updateBairro" class="btn btn-primary">Salvar Alterações</button>
                                  </div>
                              </form>
                            </div>
                          </div>
                        </div>

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
