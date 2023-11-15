<?php

use CTXFeed\V5\Common\DisplayNotices;
use CTXFeed\V5\Common\DownloadFiles;
use CTXFeed\V5\Common\ExportFeed;
use CTXFeed\V5\Common\Factory;
use CTXFeed\V5\Common\ImportFeed;
use CTXFeed\V5\Override\OverrideFactory;
use CTXFeed\V5\Utility\Logs;
use CTXFeed\V5\Compatibility\WPMLTranslation;
use CTXFeed\V5\Compatibility\TranslatePress;
use CTXFeed\V5\Compatibility\WCMLCurrency;
use CTXFeed\V5\Compatibility\MultiCurrency;
use CTXFeed\V5\Compatibility\DynamicDiscount;

/**
 * Exclude Feed URL from Caching
 */
OverrideFactory::excludeCache();
/**
 * Override Common Functionality
 */
OverrideFactory::Common();
/**
 * Process Feed Config Import Request
 */
new ImportFeed();
/**
 * Process Export Feed Request
 */
new ExportFeed();

/**
 * Process File Download Request
 */
new DownloadFiles();

/**
 * Process WPML Request
 */
if ( is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) {
	new WPMLTranslation();
}

/**
 * Process TranslatePress Request
 */
if ( is_plugin_active( 'translatepress-multilingual/index.php' ) ) {
	new TranslatePress();
}
/**
 * Process WCML Request
 */
if ( is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) {
	new WCMLCurrency();
}

/**
 * Process MultiCurrency Request
 */
new MultiCurrency();

/**
 * Process Discount Request
 */
new DynamicDiscount();


/**
 * Display Notice
 */
DisplayNotices::init();
/**
 * Product id Query
 *
 * @return void
 */
if ( ! function_exists( 'woo_feed_get_product_information' ) ) {
	function woo_feed_get_product_information() {
		check_ajax_referer( 'wpf_feed_nonce' );

		// Check user permission
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			Logs::write_debug_log( 'User doesnt have enough permission.' );
			wp_send_json_error( esc_html__( 'Unauthorized Action.', 'woo-feed' ) );
			wp_die();
		}


		if ( ! isset( $_REQUEST['feed'] ) ) {
			Logs::write_debug_log( 'Feed name not submitted.' );
			wp_send_json_error( esc_html__( 'Invalid Request.', 'woo-feed' ) );
			wp_die();
		}

		$feed   = sanitize_text_field( wp_unslash( $_REQUEST['feed'] ) );
		$limit  = isset( $_REQUEST['limit'] ) ? absint( $_REQUEST['limit'] ) : 200;
		$config = Factory::get_feed_config( $feed );

		if ( woo_feed_wc_version_check( 3.2 ) ) {

			Logs::delete_log( $config->get_feed_file_name() );
			Logs::write_log( $config->get_feed_file_name(), sprintf( 'Getting Data for %s feed.', $feed ) );
			Logs::write_log( $config->get_feed_file_name(), 'Generating Feed VIA Ajax...' );
			Logs::write_log( $config->get_feed_file_name(), sprintf( 'Current Limit is %d.', $limit ) );
			Logs::write_log( $config->get_feed_file_name(), 'Feed Config::' . PHP_EOL . print_r( $config->get_config(), true ), 'info' );

			try {
				// Hook Before Query Products
				do_action( 'before_woo_feed_get_product_information', $config );

				//Get Product Ids
				$ids = Factory::get_product_ids( $config );

				// Hook After Query Products
				do_action( 'after_woo_feed_get_product_information', $config );

				Logs::write_log( $config->get_feed_file_name(), sprintf( 'Total %d product found', is_array( $ids ) && ! empty( $ids ) ? count( $ids ) : 0 ) );

				if ( is_array( $ids ) && ! empty( $ids ) ) {
					if ( count( $ids ) > $limit ) {
						rsort( $ids ); // sorting ids in descending order
						$batches = array_chunk( $ids, $limit );
					} else {
						$batches = array( $ids );
					}

					Logs::write_log( $config->get_feed_file_name(), sprintf( 'Total %d batches', count( $batches ) ) );

					wp_send_json_success(
						array(
							'product' => $batches,
							'total'   => count( $ids ),
							'success' => true,
						)
					);
				} else {
					wp_send_json_error(
						array(
							'message' => esc_html__( 'No products found. Add product or change feed config before generate the feed.', 'woo-feed' ),
							'success' => false,
						)
					);
				}
				wp_die();

			} catch ( Exception $e ) {

				$message = 'Error getting Product Ids.' . PHP_EOL . 'Caught Exception :: ' . $e->getMessage();
				Logs::write_log( $config->get_feed_file_name(), $message );
				Logs::write_fatal_log( $message, $e );

				wp_send_json_error(
					array(
						'message' => esc_html__( 'Failed to fetch products.', 'woo-feed' ),
						'success' => false,
					)
				);
				wp_die();
			}
		} else { // For Older version of WooCommerce
			do_action( 'before_woo_feed_get_product_information', $config );
			$products = wp_count_posts( 'product' );
			do_action( 'after_woo_feed_get_product_information', $config );
			if ( $products->publish > 0 ) {
				wp_send_json_success(
					array(
						'product' => $products->publish,
						'success' => true,
					)
				);
			} else {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'No products found. Add product or change feed config before generate the feed.', 'woo-feed' ),
						'success' => false,
					)
				);
			}
			wp_die();
		}
	}

	add_action( 'wp_ajax_get_product_information', 'woo_feed_get_product_information' );
}
