<?php

namespace CTXFeed\V5\Override;


/**
 * Class Tiktok
 *
 * @package    CTXFeed\V5\Override
 * @subpackage CTXFeed\V5\Override
 */
class TiktokTemplate {
	public function __construct() {
		add_filter( 'woo_feed_get_tiktok_color_attribute', [
			$this,
			'woo_feed_get_tiktok_color_size_attribute_callback'
		] );
		add_filter( 'woo_feed_get_tiktok_size_attribute', [
			$this,
			'woo_feed_get_tiktok_color_size_attribute_callback'
		] );
		add_filter( 'woo_feed_get_tiktok_shipping_weight_attribute', [
			$this,
			'woo_feed_get_tiktok_shipping_weight_attribute_callback'
		] );
	}

	public function woo_feed_get_tiktok_color_size_attribute_callback( $output ) {
		return str_replace( [ ' ' ], [ '' ], $output );
	}

	public function woo_feed_get_tiktok_shipping_weight_attribute_callback( $output, $product, $config, $product_attribute ) {

		$wc_unit    = ' ' . get_option( 'woocommerce_weight_unit' );
		$attributes = ( $config->attributes ) ?: false;

		if ( ! $attributes ) {
			return $output;
		}

		$key = array_search( $product_attribute, $attributes );
		if ( isset( $config->suffix ) && ! empty( $key ) && array_key_exists( $key, $config->suffix ) ) {
			$unit = $config->suffix[ $key ];
			if ( ! empty( $unit ) && ! empty( $output ) ) {
				$output .= ' ' . $unit;
			} else if ( ! empty( $wc_unit ) && ! empty( $output ) ) {
				$output .= $wc_unit;
			}
		}

		return $output;
	}
}
