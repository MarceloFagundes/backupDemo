<div class="row">
  <div class="col-12">
    <div class="card mb-0">
      <div class="card-body">
        <ul class="nav nav-pills" style="margin:5px; float:right;">
          <li class="nav-item">
            <a class="nav-link active" href="sheep.php?m=sheep-usuarios/sheep-criar">Novo Operador</a>
          </li>
        </ul>
        <ul class="nav nav-pills">
          <?php
          $ler = new Ler();
          $ler->Leitura('usuarios');
          $total = $ler->getContaLinhas();
          ?>
          <li class="nav-item">
            <a class="nav-link active" href="sheep.php?m=sheep-usuarios/index">Equipe <span class="badge badge-white"><?= $total ?></span></a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
