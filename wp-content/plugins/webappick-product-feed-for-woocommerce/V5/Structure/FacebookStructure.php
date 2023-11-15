<?php
namespace CTXFeed\V5\Structure;
use CTXFeed\V5\Merchant\MerchantAttributeReplaceFactory;

class FacebookStructure implements StructureInterface {
	private $config;

	public function __construct( $config ) {
		$this->config = $config;
		$this->config->itemWrapper  = 'item';
		$this->config->itemsWrapper = 'items';
	}


	public function get_grouped_attributes() {
		$group['additional_variant']       = [
			'additional_variant_label',
			'additional_variant_value'
		];

		$group['tax']               = [
			'tax_country',
			'tax_region',
			'tax_rate',
			'tax_ship'
		];
		$group['shipping']          = [
//            'country','region','service','price','postal_code',
			'location_id',
			'location_group_name',
			'min_handling_time',
			'max_handling_time',
			'min_transit_time',
			'max_transit_time'
		];

		return $group;
	}

	public function getXMLStructure() {
		$additional_variant    = [];
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
			$attributeValue   = ( $type[ $key ] === 'pattern' ) ? $static[ $key ] : $attributes[ $key ];
			$additional_variant_sub  = str_replace( "additional_variant_", "", $attribute );
			$replacedAttribute = MerchantAttributeReplaceFactory::replace_attribute( $attribute, $this->config );
			// Installment Attribute
			if ( in_array( $attribute, $group['additional_variant'], true ) && count( $additional_variant ) < 1 ) {
				$additional_variant[ $additional_variant_sub ] = $attributeValue;
			}elseif ( in_array( $attribute, $group['additional_variant'], true ) ) {
				$additional_variant[ $additional_variant_sub ] = $attributeValue;
				$data[ $wrapper ][]['additional_variant_attribute'] = $additional_variant;
				$additional_variant                     = [];
			}elseif ( strpos( $attribute, 'images_' ) !== false ) {
				$data[ $wrapper ][][ $replacedAttribute ] = $attributeValue;
			}else {
				$data[ $wrapper ][ $replacedAttribute ] = $attributeValue;
			}
		}

		return $data;
	}

	public function getCSVStructure() {
		$group          = $this->get_grouped_attributes();
		$attributes     = $this->config->attributes;
		$mattributes    = $this->config->mattributes;
		$static         = $this->config->default;
		$type           = $this->config->type;
		$data           = [];

		if (!in_array("identifier_exists", $attributes)){
			array_push($attributes,'identifier_exists');
			array_push($mattributes,'identifier_exists');
			array_push($type,'attribute');
		}

		foreach ( $mattributes as $key => $attribute ) {
			$additional_variant_sub  = str_replace( "additional_variant_", "", $attribute );
			$attributeValue   = ( $type[ $key ] === 'pattern' ) ? $static[ $key ] : $attributes[ $key ];

			if ( in_array( $attribute, $group['additional_variant'], true ) && count( $additional_variant ) < 1 ) {
				$additional_variant[ $additional_variant_sub ] = $attributeValue;
			}elseif ( in_array( $attribute, $group['additional_variant'], true ) ) {
				$additional_variant[ $additional_variant_sub ] = $attributeValue;
				$data[ 'additional_variant_attribute' ][] = $additional_variant;
				$additional_variant                     = [];
			} elseif ( strpos( $attribute, 'images_' ) !== false ) {
				$replacedAttribute = MerchantAttributeReplaceFactory::replace_attribute( 'additional_image_link', $this->config );
				$data[][$replacedAttribute] = $attributeValue;
			}  else {
				$replacedAttribute = MerchantAttributeReplaceFactory::replace_attribute( $attribute, $this->config );
				$data[][ $replacedAttribute ] = $attributeValue;
			}
		}

		if ( array_key_exists( 'shipping', $data ) && ! empty( $data['shipping'] ) ) {
			$attr            = 'shipping(' . implode( ':', array_keys( $data['shipping'] ) ) . ')';
			$data[][ $attr ] = implode( ':', array_values( $data['shipping'] ) );
			unset( $data['shipping'] );
		}

		if ( array_key_exists( 'additional_variant_attribute', $data ) && ! empty( $data['additional_variant_attribute'] ) ) {
			foreach ( $data['additional_variant_attribute'] as $detail ) {
				$additional_variant[] = implode( ':', array_values( $detail ) );
			}
			$data[]['additional_variant_attribute'] = implode( ',', array_values( $additional_variant ) );
			unset( $data['additional_variant_attribute'] );
		}


		return $data;
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

