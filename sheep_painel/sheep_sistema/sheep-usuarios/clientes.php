<div class="main-content">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="sheep.php">Painel</a></li>
      <li class="breadcrumb-item active">Clientes</li>
    </ol>
  </nav>

  <section class="section">
    <div class="section-body">
      
      <!-- Cabeçalho (Omitimos o topo.php para não ter o botão 'Criar Operador') -->
      <div class="row mb-4">
        <div class="col-12">
          <h2 class="section-title">Base de Clientes</h2>
          <p class="section-lead">Acompanhe os clientes cadastrados e gerencie seus pontos e saldos de cashback.</p>
        </div>
      </div>

      <?php if (filter_input(INPUT_GET, 'sucesso', FILTER_VALIDATE_BOOLEAN)): ?>
        <div class="alert alert-success alert-has-icon">
          <div class="alert-icon"><i class="fa fa-lightbulb-o"></i></div>
          <div class="alert-body"><div class="alert-title">Sucesso!</div>Operação realizada com sucesso!</div>
        </div>
      <?php endif; ?>

      <?php if (filter_input(INPUT_GET, 'erro', FILTER_VALIDATE_BOOLEAN)): ?>
        <div class="alert alert-danger alert-has-icon">
          <div class="alert-icon"><i class="fa fa-lightbulb-o"></i></div>
          <div class="alert-body"><div class="alert-title">Erro!</div>Ocorreu um erro ao processar sua solicitação.</div>
        </div>
      <?php endif; ?>

      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h4>Listagem de Clientes</h4>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped table-hover" id="save-stage" style="width:100%;">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Nome</th>
                      <th>E-mail</th>
                      <th>CPF</th>
                      <th>Cashback</th>
                      <th>Pontos</th>
                      <th>Status</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $ler = new Ler();
                    $ler->Leitura('usuarios', "WHERE nivel = 'C' ORDER BY id DESC");
                    if ($ler->getResultado()):
                      $fidelidade = new FidelidadeService();
                      foreach ($ler->getResultado() as $usuario):
                        extract($usuario);
                        $carteira = $fidelidade->getCarteira($id);
                        $saldo_cashback = $carteira ? (float)$carteira['saldo_cashback'] : 0.00;
                        $pontos = $carteira ? (int)$carteira['pontos_acumulados'] : 0;
                    ?>
                    <tr>
                      <td><?= $id ?></td>
                      <td><?= htmlspecialchars($nome) ?></td>
                      <td><?= htmlspecialchars($email) ?></td>
                      <td><?= ($cpf ? $cpf : '---') ?></td>
                      <td><span style="color: #27ae60; font-weight: bold;">R$ <?= number_format($saldo_cashback, 2, ',', '.') ?></span></td>
                      <td><span class="badge badge-warning"><?= $pontos ?></span></td>
                      <td>
                        <span class="badge badge-<?= ($status == 'S' ? 'success' : 'danger') ?>">
                          <?= ($status == 'S' ? 'Ativo' : 'Inativo') ?>
                        </span>
                      </td>
                      <td>
                        <a href="sheep.php?m=sheep-usuarios/sheep-editar&id=<?= $id ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                        <a href="sheep-filtros/excluir-usuario.php?id=<?= $id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este cliente?')">
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
