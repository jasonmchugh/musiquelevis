<?php

namespace CTXFeed\V5\Override;
use CTXFeed\V5\Utility\Config;
use WC_Product;

class GoogleTemplate
{
	public function __construct()
	{
		add_filter('woo_feed_get_google_color_attribute', [
			$this,
			'woo_feed_get_google_color_size_attribute_callback'
		], 10, 5);

		add_filter('woo_feed_get_google_size_attribute', [
			$this,
			'woo_feed_get_google_color_size_attribute_callback'
		], 10, 5);

		add_filter('woo_feed_get_google_attribute', [
			$this,
			'woo_feed_get_google_attribute_callback'
		], 10, 5);

		add_filter('woo_feed_filter_product_title', [$this, 'woo_feed_filter_product_title_callback'], 10, 3);
	}

	public function woo_feed_get_google_color_size_attribute_callback($output)
	{
		return str_replace([' ', ','], ['', '/'], $output);
	}

	public function woo_feed_get_google_attribute_callback($output, $product, $config, $product_attribute, $merchant_attribute)
	{
		$weightAttributes = ['product_weight', 'shipping_weight'];
		$dimensionAttributes = [
			'product_length',
			'product_width',
			'product_height',
			'shipping_length',
			'shipping_width',
			'shipping_height'
		];


		$wc_unit = '';
		$override = false;
		if (in_array($merchant_attribute, $weightAttributes)) {
			$override = true;
			$wc_unit = ' ' . get_option('woocommerce_weight_unit');
		}

		if (in_array($merchant_attribute, $dimensionAttributes)) {
			$override = true;
			$wc_unit = ' ' . get_option('woocommerce_dimension_unit');
		}

		if (!$override) {
			return $output;
		}

		$attributes = ($config->attributes) ?: false;

		if (!$attributes) {
			return $output;
		}

		$key = array_search($product_attribute, $attributes);
		if (isset($config->suffix) && !empty($key) && array_key_exists($key, $config->suffix)) {
			$unit = $config->suffix[$key];

			if ( ! empty( $unit ) && ! empty( $output ) ) {
				$output .= ' ' . $unit;
			} else if ( ! empty( $wc_unit ) && ! empty( $output ) ) {
				$output .= $wc_unit;
			}
		}


		return $output;
	}

	/**
	 * Replace comma with dash
	 * @param $title
	 * @param WC_Product $product
	 * @param Config $config
	 * @return string
	 */
	public function woo_feed_filter_product_title_callback($title, $product, $config)
	{
		return str_replace(',', '-', $title);
	}
}

