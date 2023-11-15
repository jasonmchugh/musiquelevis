<?php
/**
 * Shipping Methods Display
 *
 * In 2.1 we show methods per package. This allows for multiple methods per order if so desired.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-shipping.php.
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
 * @version     3.2.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<tr class="shipping">
	<th>
		<?php _e('Provenant de la succursale :', 'bravad'); ?><br>
		<?php echo wp_kses_post( $package_name ); ?>
	</th>
	<td data-title="<?php echo esc_attr( $package_name ); ?>">
		<?php if ( 1 < count( $available_methods ) ) : ?>
			<ul id="shipping_method">
				<?php foreach ( $available_methods as $method ) : ?>
					<li>
						<?php
							printf( '<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s />
								<label for="shipping_method_%1$d_%2$s">%5$s</label>',
								$index, sanitize_title( $method->id ), esc_attr( $method->id ), checked( $method->id, $chosen_method, false ), wc_cart_totals_shipping_method_label( $method ) );

							do_action( 'woocommerce_after_shipping_rate', $method, $index );
						?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php elseif ( 1 === count( $available_methods ) ) :  ?>
			<?php _e('L’item ne peut être envoyé. Veuillez contacter la succursale après l’achat pour planifier la livraison ou le ramassage en magasin', 'bravad'); ?><br>
		<?php endif; ?>

		<?php if ( $show_package_details ) : ?>
			<?php _e('Cette commande contient :', 'bravad'); ?>
			<?php echo '<p class="woocommerce-shipping-contents"><small>' . esc_html( preg_replace('/<p\b[^>]*>(.*?)<\/p>/i', '', $package_details ) ) . '</small></p>'; ?>
		<?php endif; ?>
	</td>
</tr>
