<?php
/**
 * My Account navigation
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/navigation.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_account_navigation' );
?>

<div class="_col _col--xl-12 _col--sm-12">
	<?php $args = array(
	        'delimiter' => ' » ',
		);
		woocommerce_breadcrumb( $args );
	?>
</div>

<nav class="woo__sidebar _col _col--xl-3 _col--sm-12">

	<ul class="woo__sidebar--links">
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>

	<select class="woo__sidebar--links">
		<option value="" selected="selected"><?php _e('Naviguez dans votre compte', 'bravad'); ?></option>
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<option class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>" value="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>">
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>

</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>

<link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">