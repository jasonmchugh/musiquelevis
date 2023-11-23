<?php

namespace CTXFeed\V5\Shipping;

use CTXFeed\V5\Utility\Settings;

class BingShipping extends Shipping{
	/**
	 * @var \CTXFeed\V5\Utility\Config $config
	 */
	private $config;

	public function __construct( $product, $config ) {
		parent::__construct( $product, $config );
		$this->config = $config;
	}

	/**
	 * @throws \Exception
	 */
	public function get_shipping_info() {
		$this->get_shipping_zones($this->config->get_feed_file_type());

		return $this->shipping;
	}

	/**
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function get_shipping( $key = '' ) {

		$this->get_shipping_zones($this->config->get_feed_file_type());

		return $this->get_csv( $key );
	}

	private function get_csv( $key ) {

		$allow_all_shipping = Settings::get( 'allow_all_shipping' );
		$local_pickup_shipping = Settings::get('only_local_pickup_shipping');
		$country            = $this->config->get_shipping_country();
		$feed_country            = $this->config->get_feed_country();
		$currency           = $this->config->get_feed_currency();


		$methods = $this->shipping;

		foreach ( $methods as $k=>$shipping ) {
			if ( 'local_pickup' == $shipping['method_id'] && $local_pickup_shipping == 'yes' ) {
				unset( $methods[ $key ] );
			}

			if ( $country != "" ) {
				if ( $country == 'feed' ) {
					$allow_all_shipping = 'no';
				}
				if ( $country == 'all' ) {
					$allow_all_shipping = 'yes';
				}
			}

			if ( $feed_country !== $shipping['country'] && $allow_all_shipping == 'no' ) {
				unset( $methods[ $k ] );
			}
		}
		$shipping_info=array();
		foreach ( $methods as $k=>$shipping ) {
			$shipping_info = [
				isset( $methods[ $key ]['country'] ) ? $methods[ $key ]['country'] : "",
				isset( $methods[ $key ]['state'] ) ? $methods[ $key ]['state'] : "",
				isset( $methods[ $key ]['service'] ) ? $methods[ $key ]['service'] : "",
				isset( $methods[ $key ]['price'] ) ? $methods[ $key ]['price'] . " " . $currency : "",

			];
		}
		return implode( ":", $shipping_info );
	}
}
