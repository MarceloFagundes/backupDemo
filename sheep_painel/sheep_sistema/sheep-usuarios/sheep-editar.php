<div class="main-content">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="sheep.php">Painel</a></li>
      <li class="breadcrumb-item"><a href="sheep.php?m=sheep-usuarios/index">Usuários</a></li>
      <li class="breadcrumb-item active">Editar</li>
    </ol>
  </nav>

  <section class="section">
    <?php
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        echo '<div class="alert alert-danger">ID inválido.</div>';
        return;
    }

    // Buscar usuário
    $ler = new Ler();
    $ler->Leitura('usuarios', "WHERE id = :id", "id={$id}");
    $usuario = $ler->getResultado() ? $ler->getResultado()[0] : null;

    if (!$usuario) {
        echo '<div class="alert alert-danger">Usuário não encontrado.</div>';
        return;
    }

    // Processar formulário
    if (($_SESSION['sheep_user']['nivel'] ?? 'C') === 'O' && ($usuario['nivel'] ?? 'C') === 'M') {
        echo '<div class="alert alert-warning">Operador nao pode editar usuario Administrador.</div>';
        return;
    }

    if (isset($_POST['sendSheep'])) {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        unset($dados['sendSheep']);

        // Somente Admin pode promover a Admin
        if ($dados['nivel'] === 'M' && ($_SESSION['sheep_user']['nivel'] ?? '') !== 'M') {
            $dados['nivel'] = 'O';
        }
        if (!in_array($dados['nivel'], ['M', 'O', 'C'])) {
            $dados['nivel'] = 'C';
        }

        // Senha: só atualiza se preenchida
        if (($_SESSION['sheep_user']['nivel'] ?? 'C') === 'O') {
            $dados['nivel'] = $usuario['nivel'] ?? 'C';
            $dados['status'] = $usuario['status'] ?? 'S';
        }

        if (empty($dados['senha'])) {
            unset($dados['senha']);
        } else {
            $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        }

        $dados = mondini_filtrar_dados_por_colunas_tabela('usuarios', $dados);

        $atualizar = new Atualizar();
        $atualizar->Atualizando('usuarios', $dados, "WHERE id = :id", "id={$id}");

        if ($atualizar->getResultado()) {
            header("Location: sheep.php?m=sheep-usuarios/index&sucesso=true");
            exit;
        } else {
            echo '<div class="alert alert-danger">Erro ao salvar alterações.</div>';
        }
    }
    ?>

    <form action="" method="post" enctype="multipart/form-data">
      <div class="section-body">
        <div class="row">
          <div class="col-12">
            <div class="card">

              <div class="card-footer text-right">
                <a href="sheep.php?m=sheep-usuarios/index" class="btn btn-primary"><i class="fa fa-list"></i> Listar Usuários</a>
              </div>

              <div class="card-header"><h4>Editar Usuário: <strong><?= htmlspecialchars($usuario['nome']) ?></strong></h4></div>
              <div class="card-body">

                <!-- Nome -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Nome (Obrigatório)</label>
                  <div class="col-md-7">
                    <input type="text" class="form-control" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                  </div>
                </div>

                <!-- CPF -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">CPF</label>
                  <div class="col-md-7">
                    <input type="text" class="form-control" name="cpf" value="<?= htmlspecialchars($usuario['cpf'] ?? '') ?>">
                  </div>
                </div>

                <!-- E-mail -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">E-mail (Obrigatório)</label>
                  <div class="col-md-7">
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                  </div>
                </div>

                <!-- Telefone -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Telefone</label>
                  <div class="col-md-7">
                    <input type="text" id="fone" class="form-control" name="fone" value="<?= htmlspecialchars($usuario['fone'] ?? '') ?>">
                  </div>
                </div>

                <!-- WhatsApp -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">WhatsApp</label>
                  <div class="col-md-7">
                    <input type="text" id="cel" class="form-control" name="whatsapp" value="<?= htmlspecialchars($usuario['whatsapp'] ?? '') ?>">
                  </div>
                </div>

                <!-- Nível de Acesso -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Nível de Acesso</label>
                  <div class="col-sm-12 col-md-7">
                    <select class="form-control select2" name="nivel" id="select-nivel">
                      <?php if (($_SESSION['sheep_user']['nivel'] ?? '') === 'M'): ?>
                      <option value="M" <?= ($usuario['nivel'] === 'M' ? 'selected' : '') ?>>Administrador — acesso total</option>
                      <?php endif; ?>
                      <option value="O" <?= ($usuario['nivel'] === 'O' ? 'selected' : '') ?>>Operador — dashboard, pedidos, cardápio e bairros</option>
                      <option value="C" <?= ($usuario['nivel'] === 'C' ? 'selected' : '') ?>>Cliente — sem acesso ao painel</option>
                    </select>
                  </div>
                </div>
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                  <div class="col-sm-12 col-md-7">
                    <div id="nivel-desc" class="alert alert-info" style="margin:0; font-size: 0.875rem;"></div>
                  </div>
                </div>

                <!-- Senha -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Nova Senha <small class="text-muted">(deixe em branco para manter)</small></label>
                  <div class="col-md-7">
                    <input type="password" class="form-control" name="senha" placeholder="Nova senha (opcional)">
                  </div>
                </div>

                <!-- Status -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Status</label>
                  <div class="col-sm-12 col-md-7">
                    <select class="form-control selectric" name="status">
                      <option value="S" <?= ($usuario['status'] === 'S' ? 'selected' : '') ?>>Ativo</option>
                      <option value="R" <?= ($usuario['status'] === 'R' ? 'selected' : '') ?>>Rascunho</option>
                      <option value="P" <?= ($usuario['status'] === 'P' ? 'selected' : '') ?>>Pendente</option>
                      <option value="C" <?= ($usuario['status'] === 'C' ? 'selected' : '') ?>>Inativo</option>
                    </select>
                  </div>
                </div>

                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                  <div class="col-sm-12 col-md-7">
                    <button type="submit" class="btn btn-lg btn-primary" name="sendSheep">Salvar Alterações</button>
                  </div>
                </div>

              </div><!-- card-body -->
            </div><!-- card -->
          </div>
        </div>
      </div>
    </form>
  </section>
</div>

<script>
(function(){
  var descs = {
    'M': '🔴 <strong>Administrador:</strong> Acesso total — configurações, integrações, usuários, CMS, cashback e financeiro.',
    'O': '🟡 <strong>Operador:</strong> Acesso operacional — dashboard, pedidos, cardápio e bairros. Não vê configurações nem integrações.',
    'C': '⚪ <strong>Cliente:</strong> Sem acesso ao painel administrativo. Só acessa o site de pedidos.'
  };
  var sel  = document.getElementById('select-nivel');
  var desc = document.getElementById('nivel-desc');
  function update() { desc.innerHTML = descs[sel.value] || ''; }
  sel.addEventListener('change', update);
  update();
})();
</script>
