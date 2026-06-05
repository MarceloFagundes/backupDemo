
<?php
require_once('header.php');
?>

<div class="topo-pagina">
	<h1>Nosso Blog</h1>
	<p><a href="<?= HOME ?>/index" title="">Inicio >> </a>Notícias</p>
</div>

<!-- FIM TOPO DA PAGINA -->

<!-- INICIO DO BLOG -->

<section class="blogs">
	<div class="box-container">

		<!-- ITENS DO BLOG -->
		<div class="box">
			<img src="<?= mondiniTemaImagemUrl('blog/blog-1.jpg') ?>" alt="" loading="lazy" decoding="async">
			<div class="icons">
				<a href="" title="">
					<i class="fas fa-calendar"></i> 15 de Maio de 2026
				</a>
				<a href="" title="">Chefe da Casa
				</a>
			</div>
			<h3>Segredos da Massa Italiana Perfeita</h3>
			<p>Descubra como o tempo de fermentação lenta e a farinha correta criam uma borda aerada e super crocante.</p>
			<a href="" title="" class="btn">Saiba Mais</a>			
		</div>	
		<!-- ITENS DO BLOG -->

		<!-- ITENS DO BLOG -->
		<div class="box">
			<img src="<?= mondiniTemaImagemUrl('blog/blog-2.jpg') ?>" alt="" loading="lazy" decoding="async">
			<div class="icons">
				<a href="" title="">
					<i class="fas fa-calendar"></i> 12 de Maio de 2026
				</a>
				<a href="" title="">Nutri da Casa
				</a>
			</div>
			<h3>Ingredientes Frescos e Selecionados</h3>
			<p>Do molho de tomate pelado artesanal ao manjericão fresco, saiba por que a qualidade faz toda a diferença.</p>
			<a href="" title="" class="btn">Saiba Mais</a>			
		</div>	
		<!-- ITENS DO BLOG -->

		<!-- ITENS DO BLOG -->
		<div class="box">
			<img src="<?= mondiniTemaImagemUrl('blog/blog-3.jpg') ?>" alt="" loading="lazy" decoding="async">
			<div class="icons">
				<a href="" title="">
					<i class="fas fa-calendar"></i> 10 de Maio de 2026
				</a>
				<a href="" title="">Sommelier da Casa
				</a>
			</div>
			<h3>Como Harmonizar Vinhos e Pizzas</h3>
			<p>Dicas práticas para escolher o vinho ideal que realça os sabores das pizzas salgadas e doces.</p>
			<a href="" title="" class="btn">Saiba Mais</a>			
		</div>	
		<!-- ITENS DO BLOG -->

	</div>
</section>

<!-- FIM DO BLOG -->

<?php
require_once('footer.php');
?>
