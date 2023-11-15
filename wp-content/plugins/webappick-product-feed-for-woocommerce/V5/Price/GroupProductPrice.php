<?php
namespace CTXFeed\V5\Price;
use CTXFeed\V5\Utility\Config;
use WC_Product;

class GroupProductPrice implements PriceInterface {

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
	 * Get Grouped Product Price.
	 *
	 * @param $price_type
	 * @param $tax
	 *
	 * @return int|string
	 */
	protected function getGroupProductPrice( $price_type, $tax = false ) {
		$groupProductIds = $this->product->get_children();
		$price           = 0;
		if ( ! empty( $groupProductIds ) ) {
			foreach ( $groupProductIds as $id ) {
				$product = wc_get_product( $id );
				if ( ! is_object( $product ) ) {
					continue; // make sure that the product exists.
				}
				switch ( $price_type ) {
					case 'regular_price':
						$get_price = $this->product->get_regular_price();
						break;
					case 'sale_price':
						$get_price = $this->product->get_sale_price();
						break;
					default:
						$get_price = $this->product->get_price();
						break;
				}
				$get_price = $this->convert_currency( $get_price, $price_type );
				$get_price = $this->add_tax( $get_price, $tax );
				if ( ! empty( $get_price ) ) {
					$price += $get_price;
				}
			}
		}

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
		return $this->getGroupProductPrice( 'regular_price', $tax );
	}

	/**
	 * Get Price.
	 *
	 * @param bool $tax
	 *
	 * @return int|string
	 */
	public function price( $tax = false ) {
		return $this->getGroupProductPrice( 'price', $tax );
	}

	/**
	 * Get Sale Price.
	 *
	 * @param bool $tax
	 *
	 * @return int|string
	 */
	public function sale_price( $tax = false ) {
		return $this->getGroupProductPrice( 'sale_price', $tax );
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
	 * Get Price with Tax.
	 *
	 * @return int
	 */
	public function add_tax( $price, $tax = false ) {
		if ( true === $tax ) {
			return woo_feed_get_price_with_tax( $price, $this->product );
		}

		return $price;
	}
}
