<div class="main-content">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="sheep.php">Painel</a></li>
      <li class="breadcrumb-item"><a href="sheep.php?m=sheep-usuarios/index">Usuários</a></li>
      <li class="breadcrumb-item active">Criar</li>
    </ol>
  </nav>

  <section class="section">
    <?php
    if (isset($_POST['sendSheep'])) {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        unset($dados['sendSheep']);

        $dados['nome'] = $dados['nome'] . ' ' . $dados['sobrenome'];
        unset($dados['sobrenome']);

        // Garante que somente Administradores possam criar outro Administrador
        if ($dados['nivel'] === 'M' && ($_SESSION['sheep_user']['nivel'] ?? '') !== 'M') {
            $dados['nivel'] = 'O';
        }
        // Impede criar nível inválido
        if (!in_array($dados['nivel'], ['M', 'O', 'C'])) {
            $dados['nivel'] = 'O';
        }

        $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        $dados = mondini_filtrar_dados_por_colunas_tabela('usuarios', $dados);

        $criar = new Criar();
        $criar->Criacao('usuarios', $dados);

        if ($criar->getResultado()) {
            header("Location: sheep.php?m=sheep-usuarios/index&sucesso=true");
            exit;
        } else {
            echo '<div class="alert alert-danger">Erro ao cadastrar usuário!</div>';
        }
    }
    ?>

    <form action="" method="post" enctype="multipart/form-data">
      <div class="section-body">
        <div class="row">
          <div class="col-12">
            <div class="card">

              <div class="card-footer text-right">
                <a href="sheep.php?m=sheep-usuarios/index" class="btn btn-primary"><i class="fa fa-list"></i> Listar Equipe</a>
              </div>

              <div class="card-header"><h4>Criar Operador</h4></div>
              <div class="card-body">
                <div class="alert alert-info">
                  Cadastre aqui os funcionarios que vao acessar o painel como operador. O operador pode trabalhar com pedidos, cardapio e bairros, mas nao gerencia administradores nem configuracoes sensiveis.
                </div>

                <!-- Foto -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Foto</label>
                  <div class="col-sm-12 col-md-7">
                    <div id="image-preview" class="image-preview">
                      <label for="image-upload" id="image-label">Buscar Imagem</label>
                      <input type="file" name="foto" id="image-upload" />
                    </div>
                  </div>
                </div>

                <!-- Nome -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Nome (Obrigatório)</label>
                  <div class="col-md-3">
                    <input type="text" class="form-control" name="nome" placeholder="Primeiro nome" required>
                  </div>
                  <div class="col-md-4">
                    <input type="text" class="form-control" name="sobrenome" placeholder="Sobrenome" required>
                  </div>
                </div>

                <!-- CPF -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">CPF (Obrigatório)</label>
                  <div class="col-md-7">
                    <input type="text" id="cpfmj" class="form-control" name="cpf" placeholder="CPF">
                  </div>
                </div>

                <!-- E-mail -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">E-mail (Obrigatório)</label>
                  <div class="col-md-7">
                    <input type="email" class="form-control" name="email" placeholder="E-mail" required>
                  </div>
                </div>

                <!-- Telefone -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Telefone (Opcional)</label>
                  <div class="col-md-7">
                    <input type="text" id="fone" class="form-control" name="fone" placeholder="Telefone">
                  </div>
                </div>

                <!-- WhatsApp -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">WhatsApp (Opcional)</label>
                  <div class="col-md-7">
                    <input type="text" id="cel" class="form-control" name="whatsapp" placeholder="WhatsApp">
                  </div>
                </div>

                <!-- Nível de Acesso -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Nível de Acesso (Obrigatório)</label>
                  <div class="col-sm-12 col-md-7">
                    <select class="form-control select2" name="nivel" id="select-nivel">
                      <?php if (($_SESSION['sheep_user']['nivel'] ?? '') === 'M'): ?>
                      <option value="M">Administrador — acesso total ao painel</option>
                      <?php endif; ?>
                      <option value="O" selected>Operador — dashboard, pedidos, cardápio e bairros</option>
                      <option value="C">Cliente — sem acesso ao painel</option>
                    </select>
                  </div>
                </div>
                <!-- Descrição dinâmica do nível -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                  <div class="col-sm-12 col-md-7">
                    <div id="nivel-desc" class="alert alert-info" style="margin:0; font-size: 0.875rem;"></div>
                  </div>
                </div>

                <!-- Senha -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Senha (Obrigatório)</label>
                  <div class="col-md-7">
                    <input type="password" class="form-control" name="senha" placeholder="Senha" required>
                  </div>
                </div>

                <!-- Status -->
                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Status</label>
                  <div class="col-sm-12 col-md-7">
                    <select class="form-control selectric" name="status">
                      <option value="S">Ativo</option>
                      <option value="R">Rascunho</option>
                      <option value="P">Pendente</option>
                      <option value="C">Inativo</option>
                    </select>
                  </div>
                </div>

                <input type="hidden" name="usuario" value="<?= $_SESSION['sheep_user']['id'] ?>">
                <input type="hidden" name="sheep_firewall" value="<?= date('YmdHis') ?>">
                <input type="hidden" name="tipo" value="usuarios">

                <div class="form-group row mb-4">
                  <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                  <div class="col-sm-12 col-md-7">
                    <button type="submit" class="btn btn-lg btn-primary" name="sendSheep">Criar Operador</button>
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
