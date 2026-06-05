
<?php
require_once('header.php');
require_once(__DIR__ . '/rating_helper.php');
?>

<!--INICIO DO SLIDE DO SITE -->

<?php
// Define URLs dos banners: usa o enviado pelo admin, ou o padrão estático como fallback
$slide_1_url = !empty($banner_1) ? HOME . '/sheep_painel/assets/img/banners/' . $banner_1 : mondiniTemaImagemUrl('slides/slider-1.png');
$slide_2_url = !empty($banner_2) ? HOME . '/sheep_painel/assets/img/banners/' . $banner_2 : mondiniTemaImagemUrl('slides/slider-2.png');
$slide_3_url = !empty($banner_3) ? HOME . '/sheep_painel/assets/img/banners/' . $banner_3 : mondiniTemaImagemUrl('slides/slider-3.png');
?>

<!-- PRELOAD DO PRIMEIRO SLIDE PARA CARREGAMENTO MAIS RÁPIDO -->
<link rel="preload" as="image" href="<?= $slide_1_url ?>" fetchpriority="high">

<section class="destaques">

	<div class="swiper home-slider">

		<div class="swiper-wrapper">

    	<!-- Slides 1-->
		    <div class="swiper-slide slide" style="background-color: #130f40; background-image: url('<?= $slide_1_url ?>');">
		    	<div class="content">
		    		<h3>A <span>melhor</span> pizza<br> do Brasil</h3>
		    		<a href="<?= HOME ?>/montar-pizza" class="btn">Monte sua Pizza</a>
		    	</div>
		    </div>
    	<!-- Fim Slides 1 -->


    	<!-- Slides 2 -->
		    <div class="swiper-slide slide" style="background-color: #130f40; background-image: url('<?= $slide_2_url ?>');">
		    	<div class="content">
		    		<h3>A <span>melhor</span> pizza<br> do Brasil</h3>
		    		<a href="<?= HOME ?>/montar-pizza" class="btn">Monte sua Pizza</a>
		    	</div>
		    </div>
    	<!-- Fim Slides 2 -->


    	<!-- Slides 3 -->
		    <div class="swiper-slide slide" style="background-color: #130f40; background-image: url('<?= $slide_3_url ?>');">
		    	<div class="content">
		    		<h3>A <span>melhor</span> pizza<br> do Brasil</h3>
		    		<a href="<?= HOME ?>/montar-pizza" class="btn">Monte sua Pizza</a>
		    	</div>
		    </div>
    	<!-- Fim Slides 3 -->

 	</div>
 		<div class="swiper-button-prev"></div>
  		<div class="swiper-button-next"></div>
   </div> 

</section>
<!--FIM DO SLIDE DO SITE -->






<!-- INICIO CATEGORIAS MODERNAS (ICONES) -->

<section class="destaques-site" style="padding: 4rem 9%;">
	<div class="categorias-container">
		
		<!-- Categoria 1 -->
		<a href="<?= HOME ?>/loja" class="box-categoria">
			<div class="icone-categoria">
				<i class="fas fa-pizza-slice"></i>
			</div>
			<h3>Tradicionais</h3>
		</a>

		<!-- Categoria 2 -->
		<a href="<?= HOME ?>/loja" class="box-categoria">
			<div class="icone-categoria">
				<i class="fas fa-ice-cream"></i>
			</div>
			<h3>Sobremesas</h3>
		</a>

		<!-- Categoria 3 -->
		<a href="<?= HOME ?>/loja" class="box-categoria">
			<div class="icone-categoria">
				<i class="fas fa-cookie-bite"></i>
			</div>
			<h3>Doces</h3>
		</a>

		<!-- Categoria 4 -->
		<a href="<?= HOME ?>/loja" class="box-categoria">
			<div class="icone-categoria">
				<i class="fas fa-crown"></i>
			</div>
			<h3>Gourmet</h3>
		</a>

	</div>
</section>

<style>
.categorias-container {
	display: flex;
	flex-wrap: wrap;
	justify-content: center;
	gap: 3rem;
}

.box-categoria {
	display: flex;
	flex-direction: column;
	align-items: center;
	text-decoration: none;
	width: 12rem;
	transition: transform 0.3s ease;
}

.box-categoria:hover {
	transform: translateY(-5px);
}

.icone-categoria {
	width: 8rem;
	height: 8rem;
	border-radius: 50%;
	background: #fff;
	display: flex;
	justify-content: center;
	align-items: center;
	box-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, 0.08);
	border: 2px solid transparent;
	transition: all 0.3s ease;
	margin-bottom: 1rem;
}

.icone-categoria i {
	font-size: 3.5rem;
	color: #130f40;
	transition: all 0.3s ease;
}

.box-categoria:hover .icone-categoria {
	border-color: var(--red);
	box-shadow: 0 .8rem 2rem rgba(255, 0, 0, 0.15);
}

.box-categoria:hover .icone-categoria i {
	color: var(--red);
}

.box-categoria h3 {
	font-size: 1.6rem;
	color: #333;
	font-weight: 600;
	text-align: center;
}

@media(max-width: 768px) {
	.categorias-container {
		gap: 1.5rem;
	}
	.box-categoria {
		width: 9rem;
	}
	.icone-categoria {
		width: 6.5rem;
		height: 6.5rem;
	}
	.icone-categoria i {
		font-size: 2.8rem;
	}
	.box-categoria h3 {
		font-size: 1.4rem;
	}
}
</style>

<!-- FIM CATEGORIAS MODERNAS -->

<!-- NOVO BANNER PREMIUM: MONTE SUA PIZZA AGORA -->
<section class="banner-customizador" style="padding: 4rem 9%; background: #f8f9fa;">
	<div style="background: linear-gradient(135deg, #130f40 0%, #0c082b 100%); border-radius: 1.5rem; padding: 4rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 3rem; box-shadow: 0 1rem 3rem rgba(0,0,0,0.15); border: 1px solid rgba(255,255,255,0.05); position: relative; overflow: hidden;">
		<!-- Brilho radial no fundo -->
		<div style="position: absolute; top: -50%; left: -50%; width: 100%; height: 100%; background: radial-gradient(circle, rgba(231, 76, 60, 0.15) 0%, transparent 60%); pointer-events: none;"></div>
		
		<div class="texto-banner" style="flex: 1 1 50rem; z-index: 2;">
			<span style="color: #f1c40f; font-size: 1.6rem; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; display: block; margin-bottom: 1rem;">🔥 Combinação Perfeita</span>
			<h2 style="color: #fff; font-size: 3.5rem; line-height: 1.2; margin-bottom: 1.5rem;">Crie sua pizza de forma simples e do seu jeito!</h2>
			<p style="color: #a4b0be; font-size: 1.5rem; line-height: 1.6; margin-bottom: 2rem;">Escolha o tamanho ideal para sua fome, divida em até 3 sabores deliciosos, recheie a borda com Cheddar ou Catupiry original e adicione refrigerante trincando de gelado!</p>
			<a href="<?= HOME ?>/montar-pizza" class="btn" style="background: var(--red); border-color: var(--red); color: #fff; font-size: 1.6rem; padding: 1.2rem 3rem; font-weight: bold; border-radius: 5rem;">🍕 Começar a Personalizar</a>
		</div>
		<div class="imagem-banner" style="flex: 1 1 30rem; display: flex; justify-content: center; align-items: center; z-index: 2;">
			<img src="<?= mondiniTemaImagemUrl('loja/pizza-1.png') ?>" alt="Monte sua pizza" loading="lazy" decoding="async" style="width: 28rem; filter: drop-shadow(0 1.5rem 2.5rem rgba(0,0,0,0.3)); animation: floatAnimation 6s ease-in-out infinite;">
		</div>
	</div>
</section>

<style>
@keyframes floatAnimation {
	0% { transform: translateY(0) rotate(0deg); }
	50% { transform: translateY(-15px) rotate(5deg); }
	100% { transform: translateY(0) rotate(0deg); }
}
.card-montar-pizza:hover {
	transform: translateY(-5px);
	border-color: #27ae60 !important;
	box-shadow: 0 1rem 2rem rgba(0,0,0,0.08);
}
</style>


<!-- INICIO COMO FUNCIONA O SITE -->

<div  class="como-funciona">
	<h1 class="titulo">Como <span>Funciona</span></h1>
	<div class="box-container">

		<!-- PASSO 1: ESCOLHER OU MONTAR -->
		<div class="box">
			<img src="<?= mondiniTemaImagemUrl('como-funciona/msflix-1.gif') ?>" alt="Escolha ou monte sua pizza" loading="lazy" decoding="async">
			<h3>Escolha ou Monte sua Pizza</h3>
		</div>

		<!-- PASSO 2: PAGAMENTO FÁCIL -->
		<div class="box">
			<img src="<?= mondiniTemaImagemUrl('como-funciona/msflix-2.gif') ?>" alt="Métodos de pagamento fáceis" loading="lazy" decoding="async">
			<h3>Métodos de Pagamentos Fáceis</h3>
		</div>

		<!-- PASSO 3: ENTREGA RÁPIDA -->
		<div class="box">
			<img src="<?= mondiniTemaImagemUrl('como-funciona/msflix-3.gif') ?>" alt="Entrega rápida" loading="lazy" decoding="async">
			<h3>Entrega Rápida</h3>
		</div>

		<!-- PASSO 4: APROVEITAR -->
		<div class="box">
			<img src="<?= mondiniTemaImagemUrl('como-funciona/msflix-4.gif') ?>" alt="Aproveite sua pizza" loading="lazy" decoding="async">
			<h3>Aproveite sua Pizza!</h3>
		</div>
		
	</div>
</div>

<!-- FIM COMO FUNCIONA O SITE -->



<!-- INICIO DA LOJA VIRTUAL -->

<section class="	loja">
	<h1 class="titulo">As pizzas mais<span> compradas</span></h1>
	<div class="box-container">

		<!-- CARD ESPECIAL: MONTE SEU COMBO -->
		<div class="box card-montar-pizza" style="border: 2px dashed var(--red); background: rgba(255, 0, 0, 0.02); transition: all 0.3s ease;">
			<img src="<?= mondiniTemaImagemUrl('loja/pizza-1.png') ?>" alt="Monte sua Pizza" loading="lazy" decoding="async" style="transform: rotate(15deg); filter: drop-shadow(0 10px 15px rgba(211, 84, 0, 0.3));">
			<h3 style="color: var(--red);">🍕 Monte seu Combo!</h3>
			<p>Escolha o tamanho, recheie a borda (Cheddar/Catupiry), adicione bebidas e escolha até 3 sabores do seu jeito!</p>
			<div class="estrelas">
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i>
			</div>
			<div class="loja_valor" style="color: #27ae60; font-weight: bold;">
				A partir de R$ 39,90
			</div>
			<a href="<?= HOME ?>/montar-pizza" class="btn" style="background: #27ae60; border-color: #27ae60;">Montar Pizza</a>
		</div>

	<?php
	$sheep->Leitura('produtos', "WHERE id NOT BETWEEN 5 AND 12 ORDER BY id DESC LIMIT 3");
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




<!-- INICIO EMPRESA DA PAGINA PRINCIPAL -->

<section class="empresa" id="empresa">
	<div class="row">
		
		<div class="empresa-img">
			<img src="<?= mondiniTemaImagemUrl('empresa.jpg') ?>" alt="" loading="lazy" decoding="async">
		</div>

		<div class="content">
			<h3>A Pizzaria Modelo: <span> O sabor irresistível </span> feito para você</h3>

			<p>
				Se você é um amante da verdadeira pizza artesanal, a Pizzaria Modelo é o lugar perfeito para satisfazer seus desejos gastronômicos. Localizada no coração da cidade, a Modelo se destaca pela tradição e pela paixão de servir momentos inesquecíveis em fatias crocantes e saborosas.
			</p>
			<p>
				Nosso cardápio oferece uma ampla variedade de opções, desde as tradicionais até criações gourmet exclusivas. E o melhor de tudo: agora você também pode montar a sua pizza do seu jeito, escolhendo tamanhos, recheios e sabores especiais para tornar cada pedido único!
			</p>

			<a href="<?= HOME ?>/contato" class="btn">Fale Conosco</a>
		</div>

	</div>
</section>

<!-- FIM EMPRESA DA PAGINA PRINCIPAL -->




<!-- INICIO DO BLOG -->

<section class="blogs">
	<h1 class="titulo">Nosso <span>Blog</span></h1>
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
