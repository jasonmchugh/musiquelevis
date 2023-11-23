<?php

namespace CTXFeed\V5\Structure;

use CTXFeed\V5\Merchant\MerchantAttributeReplaceFactory;
use CTXFeed\V5\Shipping\ShippingFactory;
use CTXFeed\V5\Utility\Settings;

class BingStructure implements StructureInterface {

	private $config;

	public function __construct( $config ) {
		$this->config = $config;
	}

	public function get_grouped_attributes() {
		$group['tax']               = [
			'tax_country',
			'tax_region',
			'tax_rate',
			'tax_ship'
		];
		$group['shipping']          = [
			'shipping_country',
			'shipping_region',
			'shipping_service',
			'shipping_price',
		];

		return $group;
	}

	public function getXMLStructure() {
		return $this->getCSVStructure();
	}

	public function getCSVStructure() {

		$group          = $this->get_grouped_attributes();
		$attributes  = $this->config->attributes;
		$mattributes = $this->config->mattributes;
		$static      = $this->config->default;
		$type        = $this->config->type;
		$data        = [];

		$shipping       = false;

		if (!in_array("identifier_exists", $attributes)){
			array_push($attributes,'identifier_exists');
			array_push($mattributes,'identifier_exists');
			array_push($type,'attribute');
		}

		foreach ( $mattributes as $key => $attribute ) {
			$attributeValue               = ( $type[ $key ] === 'pattern' ) ? $static[ $key ] : $attributes[ $key ];

			if ( in_array( $attribute, $group['shipping'], true ) ) {
				$shipping = true;
			} elseif ( $attribute === 'shipping' ) {
				$shipping = true;
			}
            else {
	            $replacedAttribute            = MerchantAttributeReplaceFactory::replace_attribute( $attribute, $this->config );
	            $data[][ $replacedAttribute ] = $attributeValue;
            }

		}

		if ( array_key_exists( 'shipping', $data ) && ! empty( $data['shipping'] ) ) {
			$attr            = 'shipping(' . implode( ':', array_keys( $data['shipping'] ) ) . ')';
			$data[][ $attr ] = implode( ':', array_values( $data['shipping'] ) );
			unset( $data['shipping'] );
		}

		if ( $shipping ) {

			$methods = ( ShippingFactory::get( [], $this->config ) )->get_shipping_info();
			$allow_all_shipping = Settings::get( 'allow_all_shipping' );
			$local_pickup_shipping = Settings::get('only_local_pickup_shipping');
			$country            = $this->config->get_shipping_country();
			$feed_country            = $this->config->get_feed_country();
			$currency           = $this->config->get_feed_currency();


			if ( ! empty( $methods ) ) {

				foreach ( $methods as $k=>$shipping ) {
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
						unset($methods[$k]);
					}
				}

				$iMax = count( $methods );
				$group['shipping'] = array( "country", "region", "service", "price");
				for ( $i = 0; $i < $iMax; $i ++ ) {
					@$data[][ 'shipping(' . implode( ':', @$group['shipping'] ) . ')' ] .= "csv_shipping_" . $i ;
				}
			}

		}

		return $data;
	}


	public
	function getTSVStructure() {
		return $this->getCSVStructure();
	}

	public
	function getTXTStructure() {
		return $this->getCSVStructure();
	}

	public
	function getXLSStructure() {
		return $this->getCSVStructure();
	}

	public
	function getJSONStructure() {
		return $this->getCSVStructure();
	}
}
