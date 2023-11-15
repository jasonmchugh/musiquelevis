<?php
namespace CTXFeed\V5\Structure;

use CTXFeed\V5\Merchant\MerchantAttributeReplaceFactory;
use CTXFeed\V5\Shipping\ShippingFactory;
use CTXFeed\V5\Tax\Tax;
use CTXFeed\V5\Tax\TaxFactory;
use CTXFeed\V5\Utility\Settings;
use WC_Tax;

class PinterestStructure implements StructureInterface {
	private $config;

	public function __construct( $config ) {
		$this->config = $config;
		$this->config->itemWrapper = 'item';
	}


	public function get_grouped_attributes() {
		$group['installment']       = [
			'installment_months',
			'installment_amount'
		];
		$group['subscription_cost'] = [
			'subscription_period',
			'subscription_period_length',
			'subscription_amount'
		];
		$group['product_detail']    = [
			'section_name',
			'attribute_name',
			'attribute_value'
		];
		$group[]                    = [
			'product_highlight_1',
			'product_highlight_2',
			'product_highlight_3',
			'product_highlight_4',
			'product_highlight_5',
			'product_highlight_6',
			'product_highlight_7',
			'product_highlight_8',
			'product_highlight_9',
			'product_highlight_10'
		];
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
//			'location_id',
//			'location_group_name',
//			'min_handling_time',
//			'max_handling_time',
//			'min_transit_time',
//			'max_transit_time'
		];

		return $group;
	}

	public function getXMLStructure() {
		$product_detail = [];
		$subscription   = [];
		$installment    = [];
		$tax            = [];
		$group          = $this->get_grouped_attributes();
		$attributes     = $this->config->attributes;
		$mattributes    = $this->config->mattributes;
		$static         = $this->config->default;
		$type           = $this->config->type;
		$wrapper        = $this->config->itemWrapper;
		$data           = [];

		if (!in_array("identifier_exists", $attributes)){
			array_push($attributes,'identifier_exists');
			array_push($mattributes,'identifier_exists');
			array_push($type,'attribute');
		}

		foreach ( $mattributes as $key => $attribute ) {
			$installment_sub  = str_replace( "installment_", "", $attribute );
			$installment_sub = MerchantAttributeReplaceFactory::replace_attribute( $installment_sub, $this->config );

			$subscription_sub = str_replace( "subscription_", "", $attribute );
			$subscription_sub = MerchantAttributeReplaceFactory::replace_attribute( $subscription_sub, $this->config );

			$tax_attrs = substr_count( implode( '|', $mattributes ), 'tax_' );
			$attributeValue = ( $type[ $key ] === 'pattern' ) ? $static[ $key ] : $attributes[ $key ];
			$replacedAttribute = MerchantAttributeReplaceFactory::replace_attribute( $attribute, $this->config );
			$product_detail_label = MerchantAttributeReplaceFactory::replace_attribute( 'product_detail', $this->config );
			$shipping_label = MerchantAttributeReplaceFactory::replace_attribute( 'shipping', $this->config );
			$tax_label = MerchantAttributeReplaceFactory::replace_attribute( 'tax', $this->config );
			// Installment Attribute
			if ( in_array( $attribute, $group['installment'], true ) && count( $installment ) < 1 ) {
				$installment[ $installment_sub ] = $attributeValue;
			} elseif ( in_array( $attribute, $group['installment'], true ) ) {
				$installment[ $installment_sub ] = $attributeValue;
				$data[ $wrapper ]['installment'] = $installment;
				$installment                     = [];
			} // Subscription Attributes
			elseif ( in_array( $attribute, $group['subscription_cost'], true ) && count( $subscription ) < 2 ) {
				$subscription[ $subscription_sub ] = $attributeValue;
			} elseif ( in_array( $attribute, $group['subscription_cost'], true ) ) {
				$subscription_cost = MerchantAttributeReplaceFactory::replace_attribute( 'subscription_cost', $this->config );
				$subscription[ $subscription_sub ]     = $attributeValue;
				$data[ $wrapper ][$subscription_cost] = $subscription;
				$subscription                          = [];
			} elseif ( strpos( $attribute, 'product_highlight' ) !== false ) {
				$product_highlight = MerchantAttributeReplaceFactory::replace_attribute( 'product_highlight', $this->config );
				$data[ $wrapper ][][$product_highlight] = $attributeValue;
			} elseif ( strpos( $attribute, 'images_' ) !== false ) {
				$replacedAttribute = MerchantAttributeReplaceFactory::replace_attribute( $attribute, $this->config );
				$data[ $wrapper ][][ $replacedAttribute ] = $attributeValue;
			} elseif ( in_array( $attribute, $group['tax'], true ) ) {
				$sub = str_replace( [ 'tax_', 'ship' ], [ '', 'tax_ship' ], $attribute );
				$sub = MerchantAttributeReplaceFactory::replace_attribute( $sub, $this->config );
				if ( count( $tax ) < $tax_attrs - 1 ) {
					$tax[ $sub ] = $attributeValue;

				} else {
					$tax[ $sub ]               = $attributeValue;
					$data[ $wrapper ][][$tax_label] = $tax;
					$tax                       = [];
				}
			} elseif ( in_array( $attribute, $group['product_detail'], true ) ) {
				if ( $attribute === 'section_name' || $attribute === 'attribute_name' ) {
					$product_detail[ $replacedAttribute ] = $attributeValue;
				} elseif ( $attribute === 'attribute_value' ) {
					$product_detail[ $replacedAttribute ]         = $attributeValue;
					$data[ $wrapper ][][$product_detail_label] = $product_detail;
					$product_detail                       = [];
				}
			} elseif ( in_array( $attribute, $group['shipping'], true ) ) {
				$shipping[ $replacedAttribute ]       = $attributeValue;
				$data[ $wrapper ][$shipping_label] = $shipping;
			} else {
				$data[ $wrapper ][ $replacedAttribute ] = $attributeValue;
			}
		}

		return $data;
	}

	public function getCSVStructure() {
		$product_detail = [];
		$tax            = [];
		$group          = $this->get_grouped_attributes();
		$attributes     = $this->config->attributes;
		$mattributes    = $this->config->mattributes;
		$static         = $this->config->default;
		$type           = $this->config->type;
		$data           = [];

		$shipping       = false;
		$tax            = false;

		if (!in_array("identifier_exists", $attributes)){
			array_push($attributes,'identifier_exists');
			array_push($mattributes,'identifier_exists');
			array_push($type,'attribute');
		}

		foreach ( $mattributes as $key => $attribute ) {
			$installment_sub  = str_replace( "installment_", "", $attribute );
			$subscription_sub = str_replace( "subscription_", "", $attribute );
			$tax_attrs        = substr_count( implode( '|', $mattributes ), 'tax_' );
			$attributeValue   = ( $type[ $key ] === 'pattern' ) ? $static[ $key ] : $attributes[ $key ];

			if ( strpos( $attribute, 'images_' ) !== false ) {
//				$data['additional_image_link'][] = $attributeValue;
				$replacedAttribute = MerchantAttributeReplaceFactory::replace_attribute( $attribute, $this->config );
				$data[][$replacedAttribute] = $attributeValue;
			} elseif ( strpos( $attribute, 'installment_' ) !== false ) {
				$data['installment'][ $installment_sub ] = $attributeValue;
			} elseif ( strpos( $attribute, 'subscription_' ) !== false ) {
				$data['subscription_cost'][ $subscription_sub ] = $attributeValue;
			} elseif ( strpos( $attribute, 'product_highlight_' ) !== false ) {
				$data[]['product_highlight'] = $attributeValue;
			} elseif ( in_array( $attribute, $group['product_detail'], true ) ) {
				if ( $attribute === 'section_name' || $attribute === 'attribute_name' ) {
					$product_detail[ $attribute ] = $attributeValue;
				} elseif ( $attribute === 'attribute_value' ) {
					$product_detail[ $attribute ] = $attributeValue;
					$data['product_detail'][]     = $product_detail;
					$product_detail               = [];
				}
			} elseif ( in_array( $attribute, $group['shipping'], true ) ) {
				$shipping = true;
			} elseif ( $attribute === 'shipping' ) {
				$shipping = true;
			} elseif ( $attribute === 'tax' ) {
//				$replacedAttribute = MerchantAttributeReplaceFactory::replace_attribute( $attribute, $this->config );
//				$taxes             = $this->get_taxes();
//				if ( ! empty( $taxes ) ) {
//					$taxes= Tax::get_tax_setting($taxes, $this->config);
//					foreach ( $taxes as $rates ) {
//						if ( $rates > 0 ) {
//							for ( $i = 0; $i < $rates; $i ++ ) {
//								$data[][ $replacedAttribute ] = "csv_tax_" . $i;
//							}
//						}
//					}
//				}
				$tax = true;
			} else {
				$replacedAttribute            = MerchantAttributeReplaceFactory::replace_attribute( $attribute, $this->config );
				$data[][ $replacedAttribute ] = $attributeValue;
			}
		}

		if ( array_key_exists( 'shipping', $data ) && ! empty( $data['shipping'] ) ) {
			$attr            = 'shipping(' . implode( ':', array_keys( $data['shipping'] ) ) . ')';
			$data[][ $attr ] = implode( ':', array_values( $data['shipping'] ) );
			unset( $data['shipping'] );
		}

		if ( array_key_exists( 'subscription_cost', $data ) && ! empty( $data['subscription_cost'] ) ) {
			$data[]['subscription_cost'] = implode( ':', array_values( $data['subscription_cost'] ) );
			unset( $data['subscription_cost'] );
		}

		if ( array_key_exists( 'installment', $data ) && ! empty( $data['installment'] ) ) {
			$data[]['installment'] = implode( ':', array_values( $data['installment'] ) );
		}

		if ( array_key_exists( 'additional_image_link', $data ) && ! empty( $data['additional_image_link'] ) ) {
			$imageLinks = $data['additional_image_link'];
			unset( $data['additional_image_link'] );
			$data[]['additional_image_link'] = implode( ',', array_values( $imageLinks ) );
		}

		if ( array_key_exists( 'product_detail', $data ) && ! empty( $data['product_detail'] ) ) {
			foreach ( $data['product_detail'] as $detail ) {
				$product_detail[] = implode( ':', array_values( $detail ) );
			}
			$data[]['product_detail'] = implode( ',', array_values( $product_detail ) );
			unset( $data['product_detail'] );
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
//					error_log( print_r( $group['shipping'], true ) );
					@$data[][ 'shipping(' . implode( ':', @$group['shipping'] ) . ')' ] .= "csv_shipping_" . $i ;
				}
			}

		}

		if ( $tax ) {

			$taxes = ( TaxFactory::get( [], $this->config ) )->get_taxes();
			if ( ! empty( $taxes ) ) {

				$taxes= Tax::get_tax_setting($taxes,$this->config);

				$iMax = count( $taxes );
				for ( $i = 0; $i < $iMax; $i ++ ) {
					@$data[][ 'tax(' . implode( ':', @$group['tax'] ) . ')' ] .= "csv_tax_" . $i;
				}
			}
		}


		return $data;
	}

	public function get_taxes() {

		$all_tax_rates = [];
		// Retrieve all tax classes.
		$tax_classes = WC_Tax::get_tax_classes();

		// Make sure "Standard rate" (empty class name) is present.
		if ( ! in_array( '', $tax_classes, true ) ) {
			array_unshift( $tax_classes, '' );
		}

		// For each tax class, get all rates.
		if ( ! empty( $tax_classes ) ) {
			foreach ( $tax_classes as $tax_class ) {
				$tax_class_name                    = ( '' === $tax_class ) ? 'standard-rate' : $tax_class;
				$all_tax_rates [ $tax_class_name ] = count( WC_Tax::get_rates_for_tax_class( $tax_class ) );
			}
		}

		return ! empty( $all_tax_rates ) ? $all_tax_rates : false;
	}

	public function getTSVStructure() {
		return $this->getCSVStructure();
	}

	public function getTXTStructure() {
		return $this->getCSVStructure();
	}

	public function getXLSStructure() {
		return $this->getCSVStructure();
	}

	public function getJSONStructure() {
		return $this->getCSVStructure();
	}
}
