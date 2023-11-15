<?php

namespace CTXFeed\V5\Shipping;


/**
 * Class ShippingFactory
 *
 * @package    CTXFeed\V5\Shipping
 * @subpackage CTXFeed\V5\Shipping
 */
class ShippingFactory {
	/**
	 * @param \WC_Product                $product
	 * @param \CTXFeed\V5\Utility\Config $config
	 *
	 * @return \CTXFeed\V5\Shipping\Shipping|void
	 */
	public static function get( $product, $config ) {
		$template = $config->get_feed_template();
		$class    = "\CTXFeed\V5\Shipping\\" . ucfirst( $template ) . "Shipping";
		if ( class_exists( $class ) ) {
			return new $class( $product, $config );
		}
		
		return new CustomShipping( $product, $config );
	}
}