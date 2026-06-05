<?php
$nivel_menu = $_SESSION['sheep_user']['nivel'] ?? 'C';
?>
<aside class="main-sidebar">
  <div class="sidebar-brand">
    <a href="sheep.php">
      <img alt="Pizzaria Modelo" src="<?= SHEEP_LOGO ?>" style="max-width: 150px; border-radius: 8px;" />
    </a>
  </div>
  <ul class="sidebar-menu">
    <li class="menu-header">Gestao da Pizzaria</li>
    <li>
      <a href="sheep.php"><i class="fa fa-desktop"></i><span>Dashboard</span></a>
    </li>
    <li>
      <a href="sheep.php?m=sheep-pedidos/index"><i class="fa fa-history"></i><span>Historico de Pedidos</span></a>
    </li>
    <li>
      <a href="sheep.php?m=sheep-cardapio/index"><i class="fa fa-list"></i><span>Cardapio</span></a>
    </li>
    <?php if ($nivel_menu === 'M'): ?>
    <li>
      <a href="<?= FILTROS ?>sheep-usuarios/index&token="><i class="fa fa-users"></i><span>Equipe / Operadores</span></a>
    </li>
    <?php endif; ?>

    <li class="menu-header">Site & Conteudo (CMS)</li>
    <li>
      <a href="<?= FILTROS ?>sheep-bairros/index"><i class="fa fa-map-marker"></i><span>Bairros e Taxas</span></a>
    </li>
    <li>
      <a href="<?= FILTROS ?>sheep-dados/index"><i class="fa fa-building"></i><span>Dados da Pizzaria</span></a>
    </li>
    <li>
      <a href="<?= FILTROS ?>sheep-dados/aparencia"><i class="fa fa-paint-brush"></i><span>Aparencia & Banners</span></a>
    </li>

    <?php if ($nivel_menu === 'M'): ?>
    <li class="menu-header">Fidelizacao</li>
    <li>
      <a href="<?= FILTROS ?>sheep-dados/cashback"><i class="fa fa-star" style="color: #f59e0b;"></i><span>Cashback</span></a>
    </li>
    <li>
      <a href="sheep.php?m=sheep-usuarios/clientes"><i class="fa fa-address-book" style="color: #27ae60;"></i><span>Base de Clientes</span></a>
    </li>

    <li class="menu-header">Conexoes &amp; Integracoes</li>
    <li>
      <a href="sheep.php?m=ifood_setup"><i class="fa fa-cutlery" style="color: #ea1d2c;"></i><span style="font-weight: 600;">iFood Oficial</span></a>
    </li>
    <li>
      <a href="<?= FILTROS ?>sheep-dados/pagamentos"><i class="fa fa-credit-card" style="color: #009ee3;"></i><span>MercadoPago</span></a>
    </li>
    <li>
      <a href="<?= FILTROS ?>sheep-dados/fiscal"><i class="fa fa-file-text" style="color: #64748b;"></i><span>ERP &amp; Nota Fiscal</span></a>
    </li>
    <?php endif; ?>

    <?php if ($nivel_menu === 'O'): ?>
    <li class="menu-header">Acesso Operacional</li>
    <li style="padding: 8px 16px;">
      <small style="color: #aaa; font-size: 0.78rem;">
        <i class="fa fa-lock"></i> Cashback e integracoes<br>somente para Administradores.
      </small>
    </li>
    <?php endif; ?>
  </ul>
</aside>
