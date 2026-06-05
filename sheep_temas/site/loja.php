<?php
require_once('header.php');
require_once(__DIR__ . '/rating_helper.php');
?>

<!-- INICIO TOPO DA PAGINA -->
<div class="topo-pagina">
	<h1>Nosso Cardápio</h1>
	<p><a href="<?= HOME ?>/index" title="">Inicio >> </a>Cardápio</p>
</div>
<!-- FIM TOPO DA PAGINA -->

<!-- INICIO DA LOJA VIRTUAL -->
<section class="loja">
	
	<!-- ATALHO DESTAQUE PARA MONTAR PIZZA -->
	<div style="width: 100%; text-align: center; margin-bottom: 5rem;">
		<a href="<?= HOME ?>/montar-pizza" class="btn btn-atract" style="background: #27ae60; border-color: #27ae60; color: #fff; display: inline-block; font-size: 1.8rem; padding: 1.5rem 4rem; font-weight: bold; border-radius: 5rem; box-shadow: 0 1rem 2rem rgba(39, 174, 96, 0.2); animation: pulseAnimation 2s infinite; text-transform: uppercase; letter-spacing: 1px; text-decoration: none;">
			🍕 Prefere Montar do seu Jeito? Clique Aqui!
		</a>
	</div>

	<!-- SEÇÃO 1: PIZZAS ESPECIAIS -->
	<h2 style="font-size: 2.8rem; color: #130f40; margin: 4rem 0 2rem 0; width: 100%; text-align: center; font-weight: 700; font-family: 'Outfit', 'Inter', sans-serif;">🍕 Nossas Pizzas Especiais</h2>
	<div class="box-container" style="margin-bottom: 6rem;">
		<?php
		$sheep->Leitura('produtos', "WHERE id NOT BETWEEN 5 AND 12 ORDER BY nome ASC");
		$pizzas = $sheep->getResultado();
		if($pizzas):
			foreach($pizzas as $pizza):
		?>
			<div class="box">
				<img src="<?= mondiniTemaImagemUrl('loja/' . $pizza['imagem']) ?>" alt="<?= htmlspecialchars($pizza['nome'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async">
				<h3><?= htmlspecialchars($pizza['nome'], ENT_QUOTES, 'UTF-8') ?></h3>
				<p><?= htmlspecialchars($pizza['descricao'], ENT_QUOTES, 'UTF-8') ?></p>
				<div class="estrelas">
					<i class="fa fa-star"></i>
					<i class="fa fa-star"></i>
					<i class="fa fa-star"></i>
					<i class="fa fa-star"></i>
					<i class="fa fa-star-half-alt"></i>
				</div>
				<div class="loja_valor">
					R$ <?= number_format($pizza['preco_promocional'] ? $pizza['preco_promocional'] : $pizza['preco'], 2, ',', '.') ?> 
					<?php if($pizza['preco_promocional']): ?>
						<span>R$ <?= number_format($pizza['preco'], 2, ',', '.') ?></span>
					<?php endif; ?>
				</div>
				<?php if($status_loja == 'fechada'): ?>
					<button class="btn" style="background: #95a5a6; cursor: not-allowed; opacity: 0.8;" disabled>Loja Fechada</button>
				<?php else: ?>
					<a href="<?= HOME ?>/montar-pizza?sabor_id=<?= $pizza['id'] ?>" class="btn">Comprar</a>
				<?php endif; ?>
			</div>	
		<?php 
			endforeach;
		endif;
		?>
	</div>


</section>
<!-- FIM DA LOJA VIRTUAL -->

<style>
@keyframes pulseAnimation {
	0% {
		transform: scale(1);
		box-shadow: 0 1rem 2rem rgba(39, 174, 96, 0.2);
	}
	50% {
		transform: scale(1.03);
		box-shadow: 0 1.2rem 2.5rem rgba(39, 174, 96, 0.4);
	}
	100% {
		transform: scale(1);
		box-shadow: 0 1rem 2rem rgba(39, 174, 96, 0.2);
	}
}
.btn-atract:hover {
	background: #2196f3 !important;
	border-color: #2196f3 !important;
	box-shadow: 0 1.2rem 2.5rem rgba(33, 150, 243, 0.3) !important;
}
</style>

<?php
require_once('footer.php');
?>
