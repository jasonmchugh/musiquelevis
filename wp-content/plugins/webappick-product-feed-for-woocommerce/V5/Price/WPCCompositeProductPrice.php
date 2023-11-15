<?php
namespace CTXFeed\V5\Price;
use CTXFeed\V5\Utility\Config;
use WC_Product;
use WC_Product_Variable;
use WC_Product_Variation;
class WPCCompositeProductPrice implements PriceInterface {

	private $product;
	private $config;

	/**
	 * @param WC_Product|WC_Product_Composite $product
	 * @param Config                          $config
	 */
	public function __construct( $product, $config ) {

		$this->product = $product;
		$this->config  = $config;
	}

	public function composite_price( $price_type='price', $tax = false ) {

		$price = 0;
		// Parent Component Price
		$base_price = $this->product->$price_type();

		if (isset($this->config['composite_price']) && 'all_product_price' === $this->config['composite_price']) {
			$components_price = 0;
			$components = $this->product->get_components();
			if (!empty($components) && is_array($components)) {
				foreach ($components as $component) {
					$products = explode(',', $component['products']);
					foreach ($products as $product_id) {
						$default_product = wc_get_product($product_id);
						if (is_object($default_product) && $default_product->is_in_stock()) {
							$quantity = (isset($component['qty']) && $component['qty'] > 0) ? $component['qty'] : 1;
							if ( 'products' === $component['type'] && empty($component['price'])) {
								$components_price += $this->get_price_by_price_type($default_product, 'price');
								$components_price *= $quantity;
							} elseif ( 'products' === $component['type'] && !empty($component['price'])) {
								$clever = new WPCleverWooco();
								$old_price = $this->get_price_by_price_type($default_product, 'price');
								$new_price = $component['price'];
								$components_price += $clever::wooco_new_price($old_price, $new_price);
								$components_price *= $quantity;
							}
							break; // Get first in stock product from component options.
						}
					}
				}

				// Apply discount to components price.
				$discount = $this->product->get_discount();
				if ($discount > 0) {
					$components_price -= (($discount / 100) * $components_price);
				}
			}

			if ('exclude' === $this->product->get_pricing()) {
				$price = $components_price;
			} elseif ('include' === $this->product->get_pricing()) {
				$price = $components_price + $base_price;
			} elseif ('only' === $this->product->get_pricing()) {
				$price = $base_price;
			}
		} else {
			$price = $base_price;
		}

		return $price > 0 ? $price : '';

	}

	public function get_price_by_price_type( $product, $price_type='price') {
		switch ( $price_type ) {
			case 'regular_price':
				$price = $product->get_regular_price();
				break;
			case 'sale_price':
				$price = $product->get_sale_price();
				break;
			default:
				$price = $product->get_price();
				break;
		}

		return $price;
	}

	/**
	 * Get Regular Price.
	 *
	 * @param bool $tax
	 *
	 * @return mixed|void
	 */
	public function regular_price( $tax = false ) {
		$regular_price = $this->product->get_regular_price();
		$regular_price = $this->convert_currency( $regular_price, 'regular_price' );
		$regular_price = $this->add_tax( $regular_price, $tax );

		return apply_filters( 'woo_feed_filter_product_regular_price', $regular_price, $this->product, $this->config );
	}

	/**
	 * Get Price.
	 *
	 * @param bool $tax
	 *
	 * @return mixed|void
	 */
	public function price( $tax = false ) {
		$price = $this->product->get_price();
		$price = $this->convert_currency( $price, 'price' );
		$price = $this->add_tax( $price, $tax );

		return apply_filters( 'woo_feed_filter_product_price', $price, $this->product, $this->config );
	}

	/**
	 * Get Sale Price.
	 *
	 * @param bool $tax
	 *
	 * @return mixed|void
	 */
	public function sale_price( $tax = false ) {
		$sale_price = $this->product->get_sale_price();
		$sale_price = $this->convert_currency( $sale_price, 'sale_price' );
		$sale_price = $this->add_tax( $sale_price, $tax );

		return apply_filters( 'woo_feed_filter_product_sale_price', $sale_price, $this->product, $this->config );
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
