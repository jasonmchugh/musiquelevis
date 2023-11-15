<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
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
	exit; // Exit if accessed directly
}

?>

<?php
	/**
	 * woocommerce_before_single_product hook.
	 *
	 * @hooked wc_print_notices - 10
	 */
	 do_action( 'woocommerce_before_single_product' );

	 if ( post_password_required() ) {
	 	echo get_the_password_form();
	 	return;
	 }
?>

<div class="product__content _row">


	<p class="h1 product__title--mobile"><?php the_title(); ?></p>

	<?php
		/**
		 * woocommerce_before_single_product_summary hook.
		 *
		 * @hooked woocommerce_show_product_sale_flash - 10
		 * @hooked woocommerce_show_product_images - 20
		 */
		remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
		do_action( 'woocommerce_before_single_product_summary' );
	?>

	<div class="_col _col--xl-6 _col--md-6 _col--sm-12">

		<?php
			global $product;
			echo '<br />';
			$args = array(
			    'delimiter' => ' » ',
				);
			woocommerce_breadcrumb( $args );

			// Title
			echo '<h1 class="product__title">';
			echo the_title();
			echo '</h1>';

			// SKU
			if ( $product->get_sku() != null ) {
				echo _e('Code de produit : ', 'wpcustom') . "<b>" . strtoupper($product->get_sku()) . "</b>";
			}

			// Succursale
			$productid = $product->get_id();
			function get_shipping_class_name( $productid ) {
			    $classes = get_the_terms( $productid, 'product_shipping_class' );
			    return ( $classes && ! is_wp_error( $classes ) ) ? current( $classes )->name : '';
			}
			echo '<div class="product__succursale">';

			$brand = get_field( 'brand' );
			$model = get_field( 'model' );
			$size = get_field( 'size' );
			$shipping = get_field( 'shipping' );

			if ( $product->is_in_stock() ) {
				echo _e('Disponible à la succursale : ', 'bravad');
			} else {
				echo _e('Était disponible à la succursale : ', 'bravad');
			}
			
			echo get_shipping_class_name( $productid ) . '</div>';

			// Description
			the_content();

			// Product state
			if ( ! empty( $brand ) || ! empty( $model ) || ! empty( $size ) || ! empty( $shipping ) ) {
				echo '<p>';
				echo ! empty( $brand ) ? sprintf( 'Marque : %s<br />', "<b>" . strtoupper($brand ) . "</b>" ) : '';
				echo ! empty( $model ) ? sprintf( 'Modèle : %s<br />', "<b>" . strtoupper($model ) . "</b>" ) : '';
				echo ! empty( $size ) ? sprintf( 'Grandeur : %s', "<b>" . $size . "</b>" ) : '';
				echo ! empty( $shipping ) ? sprintf( 'Grandeur : %s', $shipping ) : '';
				echo '</p>';
			}

			if ( $product->get_attribute('pa_product-state') != null ) {
				echo '<b style="margin-bottom: 20px; display: block;">';
				echo _e('État du produit : ', 'bravad');
				echo $product->get_attribute('pa_product-state');
				echo '</b>';
			}

			

			//Availability
			if ( $product->is_in_stock() ) {

				// Prix
				echo '<div class="product__price">' . $product->get_price_html() . '</div>';
				
				// Qty in stock
			    if( $product->get_stock_quantity() > 1 ) {
				    echo '<p class="product__availability--instock">';
			    	echo _e('En inventaire. ', 'bravad');
			    	echo $product->get_stock_quantity();
					echo _e(' en stock.', 'bravad');
					echo '</p>';
				} else {
					echo '<p class="product__availability--instock">';
			    	echo _e('En inventaire.', 'bravad');
					echo '</p>';
				}

		    } else {
			    echo '<p class="product__availability--sold">';
			    echo _e('Vendu', 'bravad');
			    echo '</p>';
		    }




			// Add to cart
			if ( $product->is_in_stock() ) {
		    	woocommerce_template_loop_add_to_cart();
		    }

		    add_filter( 'ft_value', 'bravad_custom_values', 10, 2 ); 
		     
		    function bravad_custom_values( $value, $id ) { 
		    	global $product;

		    	$succ = array_shift( wc_get_product_terms( $product->id, 'pa_succursale', array( 'fields' => 'slugs' ) ) );
		    	$retailer = get_post_by_slug( $succ );
		    	$post_id = $retailer->ID;

		    	$email = get_field( 'courriel', $post_id );

		        if ( $id == 'ft_5b71d296a1bb2' ) { // Succursale
		            $value = $email; 
		        } 

		       	if ( $id == 'ft_5b71dd46c6b25' ) { // Url
		       	    $value = get_permalink(); 
		       	}

		       	if ( $id == 'ft_5b71dd5cb2e21' ) { // Sku
		       	    $value = $product->get_sku(); 
		       	} 
		      
		        return $value; 
		    } 

		    echo '<br /><p class="js-toggle">Ce produit vous intéresse? Vous avez des questions sur ce produits, <a>contactez-nous</a></p>';
		    echo sprintf( '<div%s>', 
		    	isset( $_GET['formulaire'] ) ? '' : ' style="display: none;"'
			);
		    echo do_shortcode( '[formtastic id="7410"]' );
		    echo '</div>';
		?>



	</div><!-- .summary -->

	<?php
		/**
		 * woocommerce_after_single_product_summary hook.
		 *
		 * @hooked woocommerce_output_product_data_tabs - 10
		 * @hooked woocommerce_upsell_display - 15
		 * @hooked woocommerce_output_related_products - 20
		 */
		remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
		do_action( 'woocommerce_after_single_product_summary' );
	?>

</div><!-- #product-<?php the_ID(); ?> -->

<?php do_action( 'woocommerce_after_single_product' ); ?>
