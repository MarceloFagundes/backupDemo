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
      <li class="breadcrumb-item active" aria-current="page">Cashback &amp; Fidelidade</li>
    </ol>
  </nav>
  <!-- FIM NAVEGAÇÃO -->

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
           Configurações de cashback salvas com sucesso.
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
          $porcentagem_cashback = "5.00";
          $tipo_validade_cashback = "dias";
          $dias_validade_cashback = 30;
          $data_validade_cashback = "";
      }
      ?>

      <form action="sheep-filtros/atualizar-dados.php" method="post">
        <div class="section-body">
          <div class="row">
            <div class="col-12">
              <div class="card" style="border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.03);">
                <div class="card-footer text-right">
                  <a href="sheep.php" class="btn btn-primary" style="border-radius: 6px;"><i class="fa fa-arrow-left"></i> Voltar </a>
                </div>

                <div class="card-header" style="border-bottom: 1px solid #f9f9f9; padding: 20px 25px;">
                  <h4 style="font-weight: 700; color: #130f40;"><i class="fa fa-money text-primary mr-2" style="font-size: 1.5rem;"></i> Configurações de Cashback &amp; Fidelidade</h4>
                </div>
                <div class="card-body" style="padding: 25px;">

                  <!-- Porcentagem de Cashback -->
                  <div class="form-group row mb-4">
                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3" style="font-weight: 600; color: #444;">Porcentagem de Cashback (%)</label>
                    <div class="col-md-7">
                      <input type="number" step="0.01" class="form-control" name="porcentagem_cashback" placeholder="Ex: 5.00" value="<?= $porcentagem_cashback ?? '5.00' ?>" required style="border-radius: 6px; border: 1px solid #ddd;">
                      <small class="form-text text-muted">Qual a porcentagem do valor do pedido que o cliente receberá de volta em saldo/crédito na carteira digital dele.</small>
                    </div>
                  </div>

                  <!-- Regra de Expiração -->
                  <div class="form-group row mb-4">
                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3" style="font-weight: 600; color: #444;">Regra de Expiração</label>
                    <div class="col-md-7">
                      <select class="form-control" name="tipo_validade_cashback" id="tipo_validade_cashback" onchange="toggleValidadeCampos()" style="border-radius: 6px; border: 1px solid #ddd; height: auto; padding: 10px 15px;">
                        <option value="nunca" <?= (isset($tipo_validade_cashback) && $tipo_validade_cashback == 'nunca') ? 'selected' : '' ?>>Sem Expiração (O cashback nunca expira)</option>
                        <option value="dias" <?= (!isset($tipo_validade_cashback) || $tipo_validade_cashback == 'dias') ? 'selected' : '' ?>>Por Período (Expira X dias após a compra)</option>
                        <option value="data" <?= (isset($tipo_validade_cashback) && $tipo_validade_cashback == 'data') ? 'selected' : '' ?>>Data Limite Fixa (Expira em uma data específica)</option>
                      </select>
                      <small class="form-text text-muted">Defina como e quando o saldo de cashback e pontos do cliente devem expirar para evitar acúmulos infinitos.</small>
                    </div>
                  </div>

                  <!-- Dias de Validade (condicional) -->
                  <div class="form-group row mb-4" id="row_dias_validade" style="display: none;">
                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3" style="font-weight: 600; color: #444;">Dias para Expiração</label>
                    <div class="col-md-7">
                      <input type="number" class="form-control" name="dias_validade_cashback" placeholder="Ex: 30" value="<?= $dias_validade_cashback ?? '30' ?>" style="border-radius: 6px; border: 1px solid #ddd;">
                      <small class="form-text text-muted">Número de dias após o pedido para o cashback e pontos expirarem (Ex: 30, 60 ou 90 dias).</small>
                    </div>
                  </div>

                  <!-- Data de Validade Fixa (condicional) -->
                  <div class="form-group row mb-4" id="row_data_validade" style="display: none;">
                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3" style="font-weight: 600; color: #444;">Data de Expiração Limite</label>
                    <div class="col-md-7">
                      <input type="date" class="form-control" name="data_validade_cashback" value="<?= $data_validade_cashback ?? '' ?>" style="border-radius: 6px; border: 1px solid #ddd;">
                      <small class="form-text text-muted">Data limite específica em que todo o saldo de cashback acumulado expira (Ex: no fim do ano).</small>
                    </div>
                  </div>

                  <input type="hidden" name="usuario" value="<?= $_SESSION['sheep_user']['id'] ?>">
                  <input type="hidden" name="sheep_firewall" value="<?= date('YmdHis') ?>">
                  <input type="hidden" name="tipo" value="geral">
                  <input type="hidden" name="id" value="1">
                  <input type="hidden" name="redirecionar" value="sheep-dados/cashback">

                  <div class="form-group row mb-4">
                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                    <div class="col-sm-12 col-md-7">
                      <button type="submit" class="btn btn-lg btn-primary" name="sendSheep" style="border-radius: 6px; font-weight: 600;">Salvar Configurações de Cashback</button>
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
function toggleValidadeCampos() {
    var tipo = document.getElementById('tipo_validade_cashback').value;
    var rowDias = document.getElementById('row_dias_validade');
    var rowData = document.getElementById('row_data_validade');
    
    if (tipo === 'dias') {
        rowDias.style.display = 'flex';
        rowData.style.display = 'none';
    } else if (tipo === 'data') {
        rowDias.style.display = 'none';
        rowData.style.display = 'flex';
    } else {
        rowDias.style.display = 'none';
        rowData.style.display = 'none';
    }
}

// Executa imediatamente quando a página carrega
document.addEventListener('DOMContentLoaded', function() {
    toggleValidadeCampos();
});
</script>
