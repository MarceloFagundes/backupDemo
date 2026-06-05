<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Tratar ação de logout
if (isset($_GET['sair'])) {
    unset($_SESSION['sheep_user']);
    header("Location: " . HOME . "/index");
    exit;
}

$contaClienteLogado = (
    !empty($_SESSION['sheep_user']) &&
    is_array($_SESSION['sheep_user']) &&
    in_array((string)($_SESSION['sheep_user']['nivel'] ?? ''), ['C', '1'], true)
);

if (!$contaClienteLogado) {
    header("Location: " . HOME . "/index");
    exit;
}

$usuario = $_SESSION['sheep_user'];
$usuario_id = $usuario['id'];

// Buscar Carteira de Cashback
$fidelidade = new FidelidadeService();
$carteira = $fidelidade->getCarteira($usuario_id);
$saldo_cashback = $carteira ? $carteira['saldo_cashback'] : 0.00;
$pontos = $carteira ? $carteira['pontos_acumulados'] : 0;

require_once('header.php');
?>

<div class="topo-pagina">
	<h1>Minha Conta</h1>
	<p><a href="<?= HOME ?>" title="">Inicio >> </a>Painel do Cliente</p>
</div>

<section class="minha-conta-pagina" style="padding: 2rem 9%;">
    <div class="painel-cliente-container" style="display: flex; gap: 3rem; flex-wrap: wrap;">
        
        <!-- Sidebar do Cliente -->
        <div class="sidebar-cliente" style="flex: 1 1 30rem; background: #fff; padding: 2rem; border-radius: .5rem; box-shadow: var(--box-shadow); align-self: flex-start;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="width: 80px; height: 80px; background: #ea1d2c; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; margin: 0 auto 1rem; font-weight: bold;">
                    <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                </div>
                <h3 style="font-size: 2rem; color: var(--black);"><?= htmlspecialchars($usuario['nome']) ?></h3>
                <p style="font-size: 1.4rem; color: #666;"><?= htmlspecialchars($usuario['email']) ?></p>
            </div>
            
            <div class="carteira-box" style="background: linear-gradient(135deg, #130f40, #1a1a2e); color: #fff; padding: 1.5rem; border-radius: .8rem; margin-bottom: 1.5rem; text-align: center;">
                <h4 style="font-size: 1.5rem; color: #f1c40f; margin-bottom: 1rem;"><i class="fas fa-crown"></i> Clube Fidelidade</h4>
                <p style="font-size: 1.3rem; margin-bottom: .5rem;">Saldo de Cashback</p>
                <div style="font-size: 2.5rem; font-weight: bold; margin-bottom: 1rem;">R$ <?= number_format($saldo_cashback, 2, ',', '.') ?></div>
                <p style="font-size: 1.2rem; color: #ccc;">Pontos: <?= $pontos ?></p>
            </div>

            <?php
            $saldosValidos = $fidelidade->getSaldosValidos($usuario_id);
            if (!empty($saldosValidos)):
            ?>
            <div class="expiracoes-box" style="background: #fff; border: 1px solid #eee; padding: 1.5rem; border-radius: .8rem; margin-bottom: 2rem;">
                <h5 style="font-size: 1.4rem; color: var(--black); margin-bottom: 1.2rem; font-weight: 700; border-bottom: 1px dashed #eee; padding-bottom: .6rem; display: flex; align-items: center; gap: 0.8rem;">
                    <i class="far fa-clock text-danger" style="font-size: 1.5rem;"></i>
                    <span>Próximas Expirações</span>
                </h5>
                <ul style="list-style: none; padding: 0; margin: 0; font-size: 1.3rem; display: flex; flex-direction: column; gap: 0.8rem; font-family: 'Outfit', sans-serif;">
                    <?php foreach ($saldosValidos as $saldoVal): 
                        $valCash = (float)$saldoVal['valor_cashback'];
                        $pts = (int)$saldoVal['quantidade_pontos'];
                        $valData = date('d/m/Y', strtotime($saldoVal['data_expiracao']));
                    ?>
                    <li style="display: flex; justify-content: space-between; align-items: center; color: #555; font-weight: 500;">
                        <span>R$ <?= number_format($valCash, 2, ',', '.') ?> <span style="font-size: 1.1rem; color: #888;">(+<?= $pts ?> pts)</span></span>
                        <span style="font-weight: 600; color: #c0392b; background: #fdf2f2; padding: 0.2rem 0.8rem; border-radius: 4px; font-size: 1.1rem;">Vence em <?= $valData ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="menu-lateral">
                <a href="<?= HOME ?>/minha-conta?sair=true" class="btn" style="width: 100%; background: #e74c3c; color: #fff; margin-top: 0;"><i class="fas fa-sign-out-alt"></i> Sair da Conta</a>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="conteudo-cliente" style="flex: 2 1 60rem;">
            
            <h2 style="font-size: 2.5rem; color: var(--black); margin-bottom: 2rem; border-bottom: 2px solid #ea1d2c; padding-bottom: 1rem;">Meus Pedidos</h2>

            <?php
            $lerPedidos = new Ler();
            $lerPedidos->Leitura('pedidos', "WHERE usuario_id = :uid ORDER BY id DESC", "uid={$usuario_id}");
            $pedidos = $lerPedidos->getResultado();

            if ($pedidos) {
                foreach ($pedidos as $pedido) {
                    $status_cores = [
                        'pendente' => ['cor' => '#f39c12', 'texto' => 'Aguardando Aprovação'],
                        'aguardando_pagamento' => ['cor' => '#e67e22', 'texto' => 'Aguardando Pagamento'],
                        'em_preparo' => ['cor' => '#3498db', 'texto' => 'Em Preparo'],
                        'saiu_entrega' => ['cor' => '#9b59b6', 'texto' => 'Saiu para Entrega'],
                        'entregue' => ['cor' => '#2ecc71', 'texto' => 'Entregue'],
                        'cancelado' => ['cor' => '#e74c3c', 'texto' => 'Cancelado']
                    ];
                    
                    $statusInfo = $status_cores[$pedido['status']] ?? ['cor' => '#95a5a6', 'texto' => ucfirst($pedido['status'])];
                    $dataPedido = date('d/m/Y \à\s H:i', strtotime($pedido['criado_em']));
            ?>
            <div class="pedido-card" style="background: #fff; padding: 2rem; border-radius: .5rem; box-shadow: 0 5px 15px rgba(0,0,0,.05); margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h3 style="font-size: 1.8rem; color: var(--black); margin-bottom: .5rem;">Pedido #<?= $pedido['id'] ?></h3>
                    <p style="font-size: 1.3rem; color: #666; margin-bottom: .5rem;"><i class="far fa-calendar-alt"></i> Realizado em <?= $dataPedido ?></p>
                    <p style="font-size: 1.3rem; color: #666; font-weight: bold;">Total: R$ <?= number_format($pedido['valor_total'], 2, ',', '.') ?></p>
                </div>
                <div style="text-align: right;">
                    <span style="display: inline-block; padding: .5rem 1rem; border-radius: 5rem; background: <?= $statusInfo['cor'] ?>22; color: <?= $statusInfo['cor'] ?>; font-size: 1.3rem; font-weight: bold; margin-bottom: 1rem;">
                        <?= $statusInfo['texto'] ?>
                    </span>
                    <br>
                    <a href="<?= HOME ?>/sucesso?id=<?= $pedido['id'] ?>" class="btn" style="padding: .5rem 1.5rem; font-size: 1.2rem; margin-top: 0;">Detalhes</a>
                </div>
            </div>
            <?php
                }
            } else {
            ?>
            <div style="text-align: center; padding: 4rem; background: #fff; border-radius: .5rem; box-shadow: var(--box-shadow);">
                <i class="fas fa-box-open" style="font-size: 5rem; color: #ccc; margin-bottom: 1.5rem;"></i>
                <p style="font-size: 1.6rem; color: #666;">Você ainda não fez nenhum pedido.</p>
                <a href="<?= HOME ?>/loja" class="btn">Fazer meu primeiro pedido</a>
            </div>
            <?php } ?>

        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
