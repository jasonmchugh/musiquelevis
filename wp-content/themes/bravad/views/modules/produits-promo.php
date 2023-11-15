<h2><?php _e('Produits en promotion', 'bravad'); ?></h2>

<div class="products__slider--promo">
	<div class="products__loading--promo" style="height: 400px;">
		<?php get_template_part( 'views/modules/loading' ); ?>
	</div>

	<div class="products__swipercontainer--promo">
		<div class="swiper-wrapper" style="opacity: 0;">
			<!-- Append posts here with Ajax -->
        </div>

        <!-- Add Arrows -->
	    <div class="products__button---next2"></div>
	    <div class="products__button---prev2"></div>

	</div>
</div>