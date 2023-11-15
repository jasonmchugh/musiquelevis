<?php
/**
 * Related Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/related.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $related_products ) : ?>

	<div class="related _col _col--xl-12 _col--md-12 _col--sm-12">

		<h2><?php esc_html_e( 'Produits similaires', 'bravad' ); ?></h2>

		<?php woocommerce_product_loop_start(); ?>


			<div class="products__slider--promo">

				<div class="products__swipercontainer--promo">
					<div class="swiper-wrapper">

						<?php foreach ( $related_products as $related_product ) : ?>

							<?php
							 	$post_object = get_post( $related_product->get_id() );
								setup_postdata( $GLOBALS['post'] =& $post_object );
								echo '<div class="swiper-slide">';
								get_template_part( 'views/async/product' );
								echo '</div>';
							?>

						<?php endforeach; ?>

			        </div>

			        <!-- Add Arrows -->
				    <div class="products__button---next2"></div>
				    <div class="products__button---prev2"></div>

				</div>
			</div>

		<?php woocommerce_product_loop_end(); ?>

	</div>

<?php endif;

wp_reset_postdata();
