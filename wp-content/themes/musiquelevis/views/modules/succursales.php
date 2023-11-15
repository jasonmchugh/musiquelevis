<?php
	$succursalestbg = get_field('image_bg_succursales', 'options');
	$succursalestImgDroite = get_field('image_droite_succursales', 'options');
?>

<div class="succursales _container--full">
	<div class="_row u-flex">
		<div class="_col _col--xl-6 _col--md-12" style="background-image: url(<?php echo $succursalestbg['url']; ?>);">
			<div class="succursales__content">
				<div class="succursales__content--wrap">
					<span class="succursales__title--number"><?php the_field('nombre_de_succursales', 'options'); ?></span>
					<span class="succursales__title"><?php the_field('titre_succursales', 'options'); ?></span>
				</div>
				<?php if( get_field('bouton_texte_succursales', 'options') ): ?>
					<a class="btn btn--yellow" href="<?php the_field('bouton_lien_succursales', 'options'); ?>">
						<?php the_field('bouton_texte_succursales', 'options'); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<div class="_col _col--xl-6 _col--md-12" style="background-image: url(<?php echo $succursalestImgDroite['url']; ?>);">
			<img class="u-visually-hidden" src="<?php echo $succursalestImgDroite['url']; ?>" alt="<?php echo $$succursalestImgDroite['alt']; ?>" />
		</div>
	</div>
</div>