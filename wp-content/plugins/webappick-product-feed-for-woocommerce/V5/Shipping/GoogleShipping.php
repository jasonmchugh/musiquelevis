<?php

namespace CTXFeed\V5\Shipping;


use CTXFeed\V5\Helper\ProductHelper;
use CTXFeed\V5\Utility\Settings;

class GoogleShipping extends Shipping {

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

		if ( 'xml' === $this->config->get_feed_file_type() ) {
			return $this->get_xml();
		}

		return $this->get_csv( $key );
	}

	private function get_xml() {
		$str = "";

		$shippingAttrs = [
			'location_id',
			'location_group_name',
			'min_handling_time',
			'max_handling_time',
			'min_transit_time',
			'max_transit_time'
		];

		$allow_all_shipping = Settings::get( 'allow_all_shipping' );
		$local_pickup_shipping = Settings::get('only_local_pickup_shipping');
		$country            = $this->config->get_shipping_country();
		$feed_country            = $this->config->get_feed_country();
		$currency           = $this->config->get_feed_currency();

		$methods = $this->shipping;
//		if ( 'no' === $allow_all_shipping) {
//			$methods = array_filter( $this->shipping, static function ( $var ) use ( $country ) {
//				return ( $var['country'] === $country );
//			} );
//		}

		if( is_array( $methods ) || is_object( $methods ) ){
			foreach ( $methods as $key=>$shipping ) {
				if ('local_pickup' == $shipping['method_id'] && $local_pickup_shipping=='yes') {
					unset($methods[$key]);
				}

				if($country!=""){
					if($country=='feed'){
						$allow_all_shipping='no';
					}
					if($country=='all'){
						$allow_all_shipping='yes';
					}
				}

				if ($feed_country !== $shipping['country'] && $allow_all_shipping=='no') {
					unset($methods[$key]);
				}
			}
		}

		$i = 1;
		if(is_array($methods)){
			foreach ( $methods as $shipping ) {
				$str .= ( $i > 1 ) ? "<g:shipping>" . PHP_EOL : PHP_EOL;
				$str .= "<g:country>" . $shipping['country'] . "</g:country>" . PHP_EOL;
				$str .= ( empty( $shipping['state'] ) ) ? "" : "<g:region>" . $shipping['state'] . "</g:region>" . PHP_EOL;
				$str .= ( empty( $shipping['service'] ) ) ? "" : "<g:service>" . $shipping['service'] . "</g:service>" . PHP_EOL;
				$str .= "<g:price>" . $shipping['price'] . " " . $currency . "</g:price>" . PHP_EOL;

				foreach ( $shippingAttrs as $shipping_attr ) {
					$key = array_search( $shipping_attr, $this->config->mattributes, true );
					if ( $key ) {
						$attributeValue = ( $this->config->type[ $key ] === 'pattern' ) ? $this->config->default[ $key ] : $this->config->attributes[ $key ];
						$value          = ProductHelper::getAttributeValueByType( $attributeValue, $this->product, $this->config, $shipping_attr );
						$str            .= "<g:$shipping_attr>$value</g:$shipping_attr>" . PHP_EOL;
					}
				}

				$str .= ( $i !== count( $methods ) ) ? "</g:shipping>" . PHP_EOL : PHP_EOL;
				$i ++;
			}
		}

		return $str;
	}

	private function get_csv( $key ) {
//		return "";
		$allow_all_shipping = Settings::get( 'allow_all_shipping' );
		$local_pickup_shipping = Settings::get('only_local_pickup_shipping');
		$country            = $this->config->get_shipping_country();
		$feed_country            = $this->config->get_feed_country();
		$currency           = $this->config->get_feed_currency();
		$shippingAttrs = [
			'location_id',
			'location_group_name',
			'min_handling_time',
			'max_handling_time',
			'min_transit_time',
			'max_transit_time'
		];

		$methods = $this->shipping;
//		if ( 'no' === $allow_all_shipping ) {
//			$methods = array_filter( $this->shipping, static function ( $var ) use ( $country ) {
//				return ( $var['country'] === $country );
//			} );
//		}

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
		foreach ( $methods as $k=>$shipping ) {
			$shipping = [
				isset( $methods[ $key ]['country'] ) ? $methods[ $key ]['country'] : "",
				isset( $methods[ $key ]['state'] ) ? $methods[ $key ]['state'] : "",
				isset( $methods[ $key ]['service'] ) ? $methods[ $key ]['service'] : "",
				isset( $methods[ $key ]['price'] ) ? $methods[ $key ]['price'] . " " . $currency : "",
	//			$this->get_value( 'location_id' ),
	//			$this->get_value( 'location_group_name' ),
	//			$this->get_value( 'min_handling_time' ),
	//			$this->get_value( 'max_handling_time' ),
	//			$this->get_value( 'min_transit_time' ),
	//			$this->get_value( 'max_transit_time' ),
			];
		}

		return implode( ":", $shipping );
	}

	public function get_value( $shipping_attr ) {
		$mKey = array_search( $shipping_attr, $this->config->mattributes, true );
		if ( $mKey ) {
			$attributeValue = ( $this->config->type[ $mKey ] === 'pattern' ) ? $this->config->default[ $mKey ] : $this->config->attributes[ $mKey ];

			return ProductHelper::getAttributeValueByType( $attributeValue, $this->product, $this->config, $shipping_attr );
		}

		return "";
	}
}
