<?php
	$aboutbg = get_field('image_bg_about', 'options');
	$aboutImgGauche = get_field('image_gauche_about', 'options');
	$aboutImgDroite = get_field('image_droite_about', 'options');
?>
<div class="about _container js-parallax" style="background-image: url(<?php echo $aboutbg['url']; ?>);">
	<div class="about__content _row">
		<div class="_col _col--xl-3 _col--md-3">
			<img src="<?php echo $aboutImgGauche['url']; ?>" alt="<?php echo $aboutImgGauche['alt']; ?>" />
		</div>

		<div class="_col _col--xl-6 _col--md-12">
			<?php if( get_field('titre_about', 'options') ): ?>
				<h2><?php the_field('titre_about', 'options'); ?></h2>
			<?php endif; ?>

			<?php the_field('texte_about', 'options'); ?>

			<?php if( get_field('bouton_texte_about', 'options') ): ?>
				<a class="btn btn--yellow" href="<?php the_field('bouton_lien_about', 'options'); ?>">
					<?php the_field('bouton_texte_about', 'options'); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="_col _col--xl-3 _col--md-3">
			<img src="<?php echo $aboutImgDroite['url']; ?>" alt="<?php echo $aboutImgDroite['alt']; ?>" />
		</div>

	</div>
</div>