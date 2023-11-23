<div class="swiper-slide product">
	<?php $img = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'large' ); ?>
	<div class="product__thumb" style="background-image: url(<?php echo str_replace("kijiji.","",$img[0]); ?>)">
		<a href="<?php the_permalink(); ?>">
			<div class="product__thumb--overlay" style="opacity: 0;">
				<img src="<?php echo get_template_directory_uri(); ?>/assets/img/plus.svg">
			</div>
		</a>
	</div>
	<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

	<div class="price">
		<?php
			global $product;
			if( $product->is_in_stock() ) {
				echo $product->get_price_html();
			}
		?>
	</div>

	<?php the_excerpt(); ?>

	<?php if ( $product->is_in_stock() ) : ?>
		<form class="cart" method="post" enctype="multipart/form-data">
			<?php
				if ($product->get_stock_quantity() > 1) {
					woocommerce_quantity_input();
				}
			?>
			<?php echo sprintf( '<button type="submit" data-product_id="%s" data-product_sku="%s" data-quantity="1" class="%s button product_type_simple">%s</button>', esc_attr( $product->get_id() ), esc_attr( $product->get_sku() ), esc_attr( 'button' ), esc_html( __('Ajouter') ) ); ?>
			<input type="hidden" name="add-to-cart" value="<?php echo $product->get_id() ?>">
		</form>
	<?php else : ?>
		<p class="in-cart"><?php  _e('Vendu', 'bravad'); ?></p>
	<?php endif; ?>


</div>