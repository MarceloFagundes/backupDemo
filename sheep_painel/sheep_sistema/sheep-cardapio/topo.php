<div class="row">
  <div class="col-12">
    <div class="card mb-0">
      <div class="card-body">
        <ul class="nav nav-pills" style="margin:5px; float:right;">
          <li class="nav-item">
            <a class="nav-link active" href="sheep.php?m=sheep-cardapio/criar"><i class="fa fa-plus"></i> Novo Produto </a>
          </li>
        </ul>
        <ul class="nav nav-pills">
          <?php
          $ler = new Ler();
          $ler->Leitura('produtos');
          $total = $ler->getContaLinhas();
          ?>
          <li class="nav-item">
            <a class="nav-link active" href="sheep.php?m=sheep-cardapio/index">Todos os Produtos <span class="badge badge-white"><?= $total ?></span></a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
