<?php get_header(); ?>

<div class="content">
	<?php get_template_part( 'views/modules/slider' ); ?>
</div>

<div class="home__produits test">

	<?php get_template_part( 'views/modules/produits-vedette' ); ?>
	<?php get_template_part( 'views/modules/produits-promo' ); ?>

</div>

<?php get_footer(); ?>