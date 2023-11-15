<?php
namespace CTXFeed\V5\Price;
use CTXFeed\V5\Utility\Config;
use WC_Product;

class PriceFactory {
    /**
     * @param WC_Product $product
     * @param Config $config
     * @return ProductPrice
     */
	public static function get( $product, $config ) {
		if ( $product->is_type( 'variable' ) ) {
			/**
			 * Variable Product does not have its price. So its depends on variations.
			 */
			$class = new ProductPrice( new VariableProductPrice( $product, $config ) );
		} elseif ( $product->is_type( 'grouped' ) ) {
			/**
			 * Grouped Product does not have its price. So its depends on a group of simple Products.
			 */
			$class = new ProductPrice( new GroupProductPrice( $product, $config ) );
		} /**
		 * Plugin Name: WooCommerce Product Bundles.
		 * @link https://woocommerce.com/products/product-bundles
		 */
		elseif ( class_exists( 'WC_Product_Bundle' ) && $product->is_type( 'bundle' ) ) {
			$class = new ProductPrice( new WCBundleProductPrice( $product, $config ) );
		} /**
		 * Plugin Name: WooCommerce Product Bundles.
		 * @link https://iconicwp.com/products/woocommerce-bundled-products/
		 */
		elseif ( class_exists( 'WC_Product_Bundled' ) && $product->is_type( 'bundled' ) ) {
			$class = new ProductPrice( new IconicBundleProductPrice( $product, $config ) );
		} /**
		 * Plugin Name:
		 * @link
		 */
		elseif ( class_exists( 'WC_Product_Composite' ) && $product->is_type( 'composite' ) ) {
			$class = new ProductPrice( new WCCompositeProductPrice( $product, $config ) );
		} /**
		 * Plugin Name: WooCommerce Composite Products.
		 * @link https://wordpress.org/plugins/wpc-composite-products/
		 */
		elseif ( class_exists( 'WPCleverWooco' ) && $product->is_type( 'composite' ) ) {
			$class = new ProductPrice( new WPCCompositeProductPrice( $product, $config ) );
		} /**
		 * Plugin Name: WooCommerce Composite Products.
		 * @link https://woocommerce.com/products/composite-products/
		 */
		elseif ( class_exists( 'WC_Composite_Products' ) && $product->is_type( 'composite' ) ) {
			$class = new ProductPrice( new WCCompositeProductPrice( $product, $config ) );
		} else {
			/**
			 * Simple Product, External Product, Product Variation, YITH Composite etc.
			 * Note*: YITH does not auto select components. So no need to calculate component price.
			 */
			$class = new ProductPrice( new SimpleProductPrice( $product, $config ) );
		}

		return $class;
	}
}
