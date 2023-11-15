<?php
namespace CTXFeed\V5\Price;
use CTXFeed\V5\Utility\Config;
use WC_Product;
use WC_Product_Variable;
use WC_Product_Variation;
use WC_Product_Bundle;
class WCBundleProductPrice implements PriceInterface {

	private $product;
	private $config;

	/**
	 * @param WC_Product $product
	 * @param Config     $config
	 */
	public function __construct( $product, $config ) {

		$this->product = $product;
		$this->config  = $config;
	}

	/**
	 * Get Bundle Price.
	 * @param $price_type
	 * @param $tax
	 *
	 * @return int|string
	 */
	public function bundle_price( $price_type = 'price', $tax = false ) {
		if ( ! class_exists( 'WC_Product_Bundle' ) ) {
			return $this->product->get_price();
		}

		$bundle = new \WC_Product_Bundle( $this->product );

		if ( $price_type === 'regular_price' ) {
			$price = $bundle->get_bundle_regular_price();
		} else {
			$price = $bundle->get_bundle_price();
		}

		// Get WooCommerce Multi language Price by Currency.
		$price = $this->convert_currency( $price, $price_type );

		// Get Price with tax
		$price = $this->add_tax( $price, $tax );

		return $price > 0 ? $price : '';
	}

	/**
	 * Get Regular Price.
	 *
	 * @param bool $tax
	 *
	 * @return int|string
	 */
	public function regular_price( $tax = false ) {
		return $this->bundle_price( 'regular_price', $tax );
	}

	/**
	 * Get Price.
	 *
	 * @param bool $tax
	 *
	 * @return int|string
	 */
	public
	function price(
		$tax = false
	) {
		return $this->bundle_price( 'price', $tax );
	}

	/**
	 * Get Sale Price.
	 *
	 * @param bool $tax
	 *
	 * @return int|string
	 */
	public
	function sale_price(
		$tax = false
	) {
		return $this->bundle_price( 'sale_price', $tax );
	}

	/**
	 * Convert Currency.
	 *
	 * @param $price
	 * @param string $price_type price type (regular_price|price|sale_price)
	 *
	 * @return mixed|void
	 */
	public
	function convert_currency(
		$price, $price_type
	) {

		return apply_filters( 'woo_feed_wcml_price',
			$price, $this->product->get_id(), $this->config->get_feed_currency(), '_' . $price_type
		);
	}

	/**
	 * Get Price with Tax.
	 *
	 * @return int
	 */
	public
	function add_tax(
		$price, $tax = false
	) {
		if ( true === $tax ) {
			return woo_feed_get_price_with_tax( $price, $this->product );
		}

		return $price;
	}
}
