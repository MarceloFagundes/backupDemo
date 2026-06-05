<div class="main-content">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="sheep.php">Painel</a></li>
      <li class="breadcrumb-item active">Usuários</li>
    </ol>
  </nav>

  <section class="section">
    <div class="section-body">
      <?php include_once 'topo.php'; ?>
      <br>

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

      <?php
      // Helper local de rótulo de nível
      function label_nivel($nivel) {
          switch ($nivel) {
              case 'M': return ['Administrador', 'badge-danger'];
              case 'O': return ['Operador',       'badge-warning'];
              default:  return ['Cliente',         'badge-secondary'];
          }
      }
      ?>

      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h4>Equipe e Operadores</h4>
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
                      <th>Nível</th>
                      <th>Status</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $ler = new Ler();
                    $ler->Leitura('usuarios', "WHERE nivel IN ('M', 'O') ORDER BY id DESC");
                    if ($ler->getResultado()):
                      foreach ($ler->getResultado() as $usuario):
                        extract($usuario);
                        [$label_n, $badge_n] = label_nivel($nivel);
                    ?>
                    <tr>
                      <td><?= $id ?></td>
                      <td><?= htmlspecialchars($nome) ?></td>
                      <td><?= htmlspecialchars($email) ?></td>
                      <td><?= ($cpf ? $cpf : '---') ?></td>
                      <td><span class="badge <?= $badge_n ?>"><?= $label_n ?></span></td>
                      <td>
                        <span class="badge badge-<?= ($status == 'S' ? 'success' : 'danger') ?>">
                          <?= ($status == 'S' ? 'Ativo' : 'Inativo') ?>
                        </span>
                      </td>
                      <td>
                        <a href="sheep.php?m=sheep-usuarios/sheep-editar&id=<?= $id ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                        <a href="sheep-filtros/excluir-usuario.php?id=<?= $id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
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
