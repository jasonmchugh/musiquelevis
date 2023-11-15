<h2><?php _e('Produits vedettes', 'bravad'); ?></h2>

<div class="products__slider--vedette">
	<div class="products__loading--vedette" style="height: 400px;">
		<?php get_template_part( 'views/modules/loading' ); ?>
	</div>

	<div class="products__swipercontainer--vedette">
		<div class="swiper-wrapper" style="opacity: 0;">
			<!-- Append posts here with Ajax -->
        </div>

        <!-- Add Arrows -->
	    <div class="products__button---next1"></div>
	    <div class="products__button---prev1"></div>

	</div>
</div>