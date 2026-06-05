<?php
// Carrega configurações da loja se ainda não foram carregadas
if (!isset($config_loja)) {
    if (!isset($sheep)) $sheep = new Ler();
    $sheep->Leitura('configuracoes', "WHERE id = '1'");
    $config_loja = $sheep->getResultado() ? $sheep->getResultado()[0] : [];
}

$nome_loja      = $config_loja['nome']           ?? 'Pizzaria Modelo';
$link_instagram = $config_loja['link_instagram'] ?? '';
$link_facebook  = $config_loja['link_facebook']  ?? '';
$fone_loja      = $config_loja['fone']           ?? '';
$whatsapp_loja  = $config_loja['whatsapp']       ?? '';
$email_loja     = $config_loja['email']          ?? '';
$endereco_loja  = $config_loja['endereco']       ?? '';
$numero_loja    = $config_loja['numero']         ?? '';
$cidade_loja    = $config_loja['cidade']         ?? '';
$estado_loja    = $config_loja['estado']         ?? '';
?>
<!-- INICIO DO RODAPÉ -->

		<section class="rodape">
			<div class="box-container">


				<!-- ITEM DO RODAPÉ -->

				<div class="box">
					<h3><?= htmlspecialchars($nome_loja) ?></h3>
					<p>Siga-nos nas redes sociais</p>
					<div class="rede-sociais">
						<?php if($link_facebook): ?>
						<a href="<?= htmlspecialchars($link_facebook) ?>" target="_blank" rel="noopener" class="fab fa-facebook-f"></a>
						<?php else: ?>
						<a href="#" class="fab fa-facebook-f"></a>
						<?php endif; ?>

						<?php if($link_instagram): ?>
						<a href="<?= htmlspecialchars($link_instagram) ?>" target="_blank" rel="noopener" class="fab fa-instagram"></a>
						<?php else: ?>
						<a href="#" class="fab fa-instagram"></a>
						<?php endif; ?>

						<a href="#" class="fab fa-youtube"></a>
					</div>
				</div>

				<!-- FIM DO ITEM DO RODAPÉ -->


				<!-- ITEM DO RODAPÉ -->

				<div class="box">
					<h3>Faça seu Pedido</h3>
					<?php if($fone_loja): ?><p><?= htmlspecialchars($fone_loja) ?></p><?php endif; ?>
					<?php if($whatsapp_loja): ?><p><?= htmlspecialchars($whatsapp_loja) ?></p><?php endif; ?>
					<?php if($email_loja): ?>
					<a href="mailto:<?= htmlspecialchars($email_loja) ?>" title="" class="link"><?= htmlspecialchars($email_loja) ?></a>
					<?php endif; ?>
				</div>

				<!-- FIM DO ITEM DO RODAPÉ -->

				<!-- ITEM DO RODAPÉ -->

				<div class="box">
					<h3>Localização</h3>
					<p>
						<?php if($endereco_loja): ?>
							<?= htmlspecialchars($endereco_loja) ?><?= $numero_loja ? ', nº '.$numero_loja : '' ?><br>
						<?php endif; ?>
						<?php if($cidade_loja || $estado_loja): ?>
							<?= htmlspecialchars($cidade_loja) ?><?= $estado_loja ? ' - '.$estado_loja : '' ?><br>
						<?php endif; ?>
					</p>
				</div>

				<!-- FIM DO ITEM DO RODAPÉ -->

			</div>

				<div class="direitos">
					<?= htmlspecialchars($nome_loja) ?> | Todos os Direitos Reservados!
				</div>

			
		</section>
		<!-- FIM DO RODAPÉ -->

