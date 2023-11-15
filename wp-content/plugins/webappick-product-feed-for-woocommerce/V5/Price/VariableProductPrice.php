<?php
namespace CTXFeed\V5\Price;
use CTXFeed\V5\Utility\Config;
use WC_Product;
use WC_Product_Variable;
use WC_Product_Variation;

class VariableProductPrice implements PriceInterface {
	private $product;
	private $config;
	private $min_max_first;

	/**
	 * @param WC_Product|WC_Product_Variable|WC_Product_Variation $product
	 * @param Config                                              $config
	 */
	public function __construct( $product, $config ) {

		$this->product       = $product;
		$this->config        = $config;
		$this->min_max_first = $this->config->get_variable_config();

	}

	/**
	 * Get First Variation Price.
	 *
	 * @param bool $tax
	 *
	 * @return int
	 */
	private function first_variation( $price_type = 'price', $tax = false ) {
		$children = $this->product->get_visible_children();
		$price    = $this->product->get_variation_price();
		if ( isset( $children[0] ) && ! empty( $children[0] ) ) {
			$variation = wc_get_product( $children[0] );

			switch ( $price_type ) {
				case 'regular_price':
					$price = $variation->get_regular_price();
					break;
				case 'sale_price':
					$price = $variation->get_sale_price();
					break;
				default:
					$price = $variation->get_price();
					break;
			}
		}

		$price = $this->convert_currency( $price, $price_type );

		return $this->add_tax( $price, $tax );
	}

	/**
	 * Get Regular Price.
	 *
	 * @param bool $tax
	 *
	 * @return float|int
	 */
	public function regular_price( $tax = false ) {
		$min_max_first = ( $this->min_max_first['variable_price'] ) ?: "min";
		if ( 'first' === $min_max_first ) {
			$regular_price = $this->first_variation( 'regular_price', $tax );
		} else {
			$regular_price = $this->product->get_variation_regular_price( $min_max_first );
			$regular_price = $this->convert_currency( $regular_price, 'regular_price' );
			$regular_price = $this->add_tax( $regular_price, $tax );
		}

		return $regular_price;
	}

	/**
	 * Get Price.
	 *
	 * @param bool $tax
	 *
	 * @return float|int
	 */
	public function price( $tax = false ) {
		$min_max_first = ( $this->min_max_first['variable_price'] ) ?: "min";
		if ( 'first' === $min_max_first ) {
			$price = $this->first_variation( 'price', $tax );
		} else {
			$price = $this->product->get_variation_price( $min_max_first );
			$price = $this->convert_currency( $price, 'price' );
			$price = $this->add_tax( $price, $tax );
		}

		return $price;
	}

	/**
	 * Get Sale Price.
	 *
	 * @param bool $tax
	 *
	 * @return float|int
	 */
	public function sale_price( $tax = false ) {
		$min_max_first = ( $this->min_max_first['variable_price'] ) ?: "min";
		if ( 'first' === $min_max_first ) {
			$sale_price = $this->first_variation( 'sale_price', $tax );
		} else {
			$sale_price = $this->product->get_variation_sale_price( $min_max_first );
			$sale_price = $this->convert_currency( $sale_price, 'sale_price' );
			$sale_price = $this->add_tax( $sale_price, $tax );
		}

		return $sale_price;
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
	 * @return int|float
	 */
	public function add_tax( $price, $tax = false ) {
		if ( true === $tax ) {
			return woo_feed_get_price_with_tax( $price, $this->product );
		}

		return $price;
	}
}
