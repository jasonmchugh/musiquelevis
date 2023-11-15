<?php
namespace CTXFeed\V5\Price;
use CTXFeed\V5\Utility\Config;
use WC_Product;

class SimpleProductPrice implements PriceInterface {

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
	 * Get Regular Price.
	 *
	 * @param bool $tax
	 *
	 * @return float|int
	 */
	public function regular_price( $tax = false ) {
		$regular_price = $this->product->get_regular_price();
		$regular_price = $this->convert_currency( $regular_price, 'regular_price' );
		return $this->add_tax( $regular_price, $tax );
	}

	/**
	 * Get Price.
	 *
	 * @param bool $tax
	 *
	 * @return int|float
	 */
	public function price( $tax = false ) {
		$price = $this->product->get_price();
		$price = $this->convert_currency( $price, 'price' );
		return $this->add_tax( $price, $tax );
	}

	/**
	 * Get Sale Price.
	 *
	 * @param bool $tax
	 *
	 * @return int|float
	 */
	public function sale_price( $tax = false ) {
		$sale_price = $this->product->get_sale_price();
		$sale_price = $this->convert_currency( $sale_price, 'sale_price' );
		return $this->add_tax( $sale_price, $tax );
	}

	/**
	 * Convert Currency.
	 *
	 * @param $price
	 * @param string $price_type price type (regular_price|price|sale_price)
	 *
	 * @return mixed|void
	 */
	public function convert_currency( $price, $price_type ) {

		return apply_filters( 'woo_feed_wcml_price',
			$price, $this->product->get_id(), $this->config->get_feed_currency(), '_' . $price_type
		);
	}

	/**
	 * @param $price
	 * @param $tax
	 *
	 * @return float|mixed|string|null
	 */
	public function add_tax( $price, $tax = false ) {
		if ( true === $tax ) {
			return woo_feed_get_price_with_tax( $price, $this->product );
		}

		return $price;
	}
}
