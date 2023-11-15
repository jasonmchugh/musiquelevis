<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="_row">
	<h1><?php _e('Tableau de bord', 'bravad'); ?></h1>
</div>

<div class="_row">

	<?php do_action( 'woocommerce_account_navigation' ); ?>

	<div class="woo__content _col _col--xl-9 _col--sm-12">

		<p class="woo__content--greats"><?php
			/* translators: 1: user display name 2: logout url */
			printf(
				__( 'Hello %1$s (not %1$s? <a href="%2$s">Log out</a>)', 'woocommerce' ),
				'<strong>' . esc_html( $current_user->first_name ) . ' ' .  esc_html( $current_user->last_name ) . '</strong>',
				esc_url( wc_logout_url( wc_get_page_permalink( 'myaccount' ) ) )
			);
		?></p>

		<p><?php
/*
			printf(
				__( 'From your account dashboard you can view your <a href="%1$s">recent orders</a>, manage your <a href="%2$s">shipping and billing addresses</a> and <a href="%3$s">edit your password and account details</a>.', 'woocommerce' ),
				esc_url( wc_get_endpoint_url( 'orders' ) ),
				esc_url( wc_get_endpoint_url( 'edit-address' ) ),
				esc_url( wc_get_endpoint_url( 'edit-account' ) )
			);
*/
		?></p>

		<div class="_row">
			<div class="dashboard__button _col _col--xl-3 _col--md-6 _col--sm-12">
				<?php
					printf(
						__( '<a href="%1$s">', 'woocommerce' ),
						esc_url( wc_get_endpoint_url( 'orders' ) ),
						esc_url( wc_get_endpoint_url( 'edit-address' ) ),
						esc_url( wc_get_endpoint_url( 'edit-account' ) )
					);
				?>
					<div class="dashboard__icon--orders"></div>
					<h2><?php _e('Commandes', 'bravad'); ?></h2>
				</a>
			</div>
			<div class="dashboard__button _col _col--xl-3 _col--md-6 _col--sm-12">
				<?php
					printf(
						__( '<a href="%2$s">', 'woocommerce' ),
						esc_url( wc_get_endpoint_url( 'orders' ) ),
						esc_url( wc_get_endpoint_url( 'edit-address' ) ),
						esc_url( wc_get_endpoint_url( 'edit-account' ) )
					);
				?>
					<div class="dashboard__icon--address"></div>
					<h2><?php _e('Adresses', 'bravad'); ?></h2>
				</a>
			</div>
			<div class="dashboard__button _col _col--xl-3 _col--md-6 _col--sm-12">
				<?php
					printf(
						__( '<a href="%3$s">', 'woocommerce' ),
						esc_url( wc_get_endpoint_url( 'orders' ) ),
						esc_url( wc_get_endpoint_url( 'edit-address' ) ),
						esc_url( wc_get_endpoint_url( 'edit-account' ) )
					);
				?>
					<div class="dashboard__icon--account"></div>
					<h2><?php _e('Compte', 'bravad'); ?></h2>
				</a>
			</div>
			<div class="dashboard__button _col _col--xl-3 _col--md-6 _col--sm-12">
				<?php
					printf(
						__( '<a href="%1$s">', 'woocommerce' ),
						esc_url( wc_logout_url( wc_get_page_permalink( 'myaccount' ) ) )
					);
				?>
					<div class="dashboard__icon--logout"></div>
					<h2><?php _e('Déconnexion', 'bravad'); ?></h2>
				</a>
			</div>
		</div>

	</div>
</div>

<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_dashboard' );

	/**
	 * Deprecated woocommerce_before_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_before_my_account' );

	/**
	 * Deprecated woocommerce_after_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
