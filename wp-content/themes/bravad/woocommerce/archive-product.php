<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
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
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header( 'shop' ); ?>

	<?php
		/**
		 * woocommerce_before_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked woocommerce_breadcrumb - 20
		 * @hooked WC_Structured_Data::generate_website_data() - 30
		 */
		//do_action( 'woocommerce_before_main_content' );
	?>

	<div class="page__wrapper">
		<div class="_container">
		    <div class="page__content">

				<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
					<h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
				<?php endif; ?>

				<div class="_row">
					<div class="_col _col--xl-6 _col--sm-12">
						<?php $args = array(
						    'delimiter' => ' » ',
							);
							woocommerce_breadcrumb( $args );
						?>
					</div>
					<div class=" _col _col--xl-6 _col--sm-12">
						<div class="filters">
							<?php /* <span><?php _e('Trier par', 'bravad'); ?></span> */ ?>
							<?php woocommerce_catalog_ordering(); ?>

							<div class="products-per-page">
								<span><?php _e('Montrer', 'bravad'); ?></span>
								<?php
									$current_value = bv_get_products_per_page();
								?>
						        <form action="" method="POST" class="woocommerce-products-per-page">
						            <select name="bv-woocommerce-products-per-page" onchange="this.form.submit()">
						                <option value="15" <?php selected('15', $current_value); ?>>15</option>
						                <option value="30" <?php selected('30', $current_value); ?>>30</option>
						                <option value="45" <?php selected('45', $current_value); ?>>45</option>
						                <option value="60" <?php selected('60', $current_value); ?>>60</option>
									</select>
						        </form>
						    </div>
						</div>
					</div>
				</div>

				<div class="_row">
					<div class="woo__sidebar _col _col--xl-3 _col--sm-12">
						<li id="woocommerce_product_categories" class="widget woocommerce widget_product_categories">
							<h2 class="js-toggle">Catégories</h2>
							<ul class="product-categories">
								<?php
									$curr_id = array();
									$curr_id[] = get_queried_object()->term_id;
									$curr_term = get_term( $curr_id[0], 'product_cat' );

									if ( $curr_term->parent !== 0 ) {
										$curr_id[] = $curr_term->parent;

										$sub_term = get_term( $curr_term->parent, 'product_cat' );

										if ( $sub_term->parent ) {
											$curr_id[] = $sub_term->parent;
										}
									}

									echo '<span style="display: none;">';
									var_dump( $curr_id );
									echo '</span>';
									$terms = get_terms( array(
										'taxonomy'   => 'product_cat',
										'hide_empty' => false,
										'parent'     => 0,
										'exclude'    => '634'
									) );

									foreach ( $terms as $term ) {
										echo sprintf( '<li class="cat-item cat-item-%s cat-parent%s"><a href="%s">%s</a>',
											$term->term_id,
											in_array( $term->term_id, $curr_id ) ? ' current-cat-parent' : '',
											get_term_link( $term->term_id ),
											$term->name
										);

										$subs = get_terms( array(
											'taxonomy'   => 'product_cat',
											'hide_empty' => false,
											'parent'     => $term->term_id,
											'orderby'    => 'name'
										) );

										if ( $subs ) {
											$open = in_array( $term->term_id, $curr_id ) ? ' style="display: block;"' : '';
											echo '<ul class="children"' . $open . '>';

											foreach ( $subs as $sub ) {
												$subsubs = get_terms( array(
													'taxonomy'   => 'product_cat',
													'hide_empty' => false,
													'parent'     => $sub->term_id,
													'orderby'    => 'name'
												) );

												echo sprintf( '<li class="cat-item cat-item-%s%s%s"><a href="%s">%s</a>',
													$sub->term_id,
													get_term_children( $sub->term_id, 'product_cat' ) ? ' cat-parent' : '',
													in_array( $sub->term_id, $curr_id ) ? ' current-cat-parent' : '',
													get_term_link( $sub->term_id ),
													$sub->name
												);

												if ( $subsubs ) {
													echo '<ul class="children">';

													foreach ( $subsubs as $subsub ) {
														echo sprintf( '<li class="cat-item cat-item-%s%s"><a href="%s">%s</a></li>',
															$subsub->term_id,
															in_array( $subsub->term_id, $curr_id ) ? ' current-cat' : '',
															get_term_link( $subsub->term_id ),
															$subsub->name
														);
													}

													echo '</ul>';
												}

												echo '</li>';
											}

											echo '</ul>';
										}

										echo '</li>';
									}
								?>
							</ul>
						</li>
						<?php dynamic_sidebar( 'shoparchive' ); ?>
					</div>
					<div class="woo__content _col _col--xl-9 _col--sm-12">
						<?php if ( have_posts() ) : ?>
							<?php
								/**
								 * Hook: woocommerce_before_shop_loop.
								 *
								 * @hooked wc_print_notices - 10
								 * @hooked woocommerce_result_count - 20
								 * @hooked woocommerce_catalog_ordering - 30
								 */
								do_action( 'woocommerce_before_shop_loop' );
							?>
							<?php woocommerce_product_loop_start(); ?>
								<?php woocommerce_product_subcategories(); ?>

								<?php while ( have_posts() ) : the_post(); ?>
									<?php do_action( 'woocommerce_shop_loop' ); ?>
									<?php //wc_get_template_part( 'content', 'product' ); ?>	

									<div class="_col _col--xl-4 _col--md-6 _col--sm-12">
										<?php get_template_part( 'views/async/product' ); ?>
									</div>
								<?php endwhile; // end of the loop. ?>

							<?php woocommerce_product_loop_end(); ?>
							<?php do_action( 'woocommerce_after_shop_loop' ); ?>
						<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>
							<?php do_action( 'woocommerce_no_products_found' ); ?>
						<?php endif; ?>
					</div>
				</div>

		    </div>

			<?php do_action( 'woocommerce_after_main_content' ); ?>
			<?php do_action( 'woocommerce_sidebar' ); ?>

		</div>
	</div>

<?php get_footer( 'shop' ); ?>
