<?php

namespace CTXFeed\V5\Structure;

use CTXFeed\V5\Merchant\MerchantAttributeReplaceFactory;

class CustomStructure implements StructureInterface,StructureXLSXInterface {
	private $config;

	public function __construct( $config ) {
		$this->config = $config;
	}

	public function getXMLStructure() {
		$attributes  = $this->config->attributes;
		$mattributes = $this->config->mattributes;
		$static      = $this->config->default;
		$type        = $this->config->type;
		$wrapper     = str_replace( " ", "_", $this->config->itemWrapper );;
		$data = [];
		foreach ( $mattributes as $key => $attribute ) {

			$attributeValue                           = ( $type[ $key ] === 'pattern' ) ? $static[ $key ] : $attributes[ $key ];
			$replacedAttribute                        = MerchantAttributeReplaceFactory::replace_attribute( $attribute, $this->config );
			$replacedAttribute                        = str_replace( " ", "_", $replacedAttribute );
			$data[ $wrapper ][][ $replacedAttribute ] = $attributeValue;
		}

		return $data;
	}

	public function getCSVStructure() {
		$attributes  = $this->config->attributes;
		$mattributes = $this->config->mattributes;
		$static      = $this->config->default;
		$type        = $this->config->type;
		$data        = [];
		foreach ( $mattributes as $key => $attribute ) {
			$attributeValue               = ( $type[ $key ] === 'pattern' ) ? $static[ $key ] : $attributes[ $key ];
			$replacedAttribute            = MerchantAttributeReplaceFactory::replace_attribute( $attribute, $this->config );
			$data[][ $replacedAttribute ] = $attributeValue;
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
	function getXLSXStructure() {
		return $this->getCSVStructure();
	}

	public
	function getJSONStructure() {
		$attributes  = $this->config->attributes;
		$mattributes = $this->config->mattributes;
		$static      = $this->config->default;
		$type        = $this->config->type;
		$data        = [];
		foreach ( $mattributes as $key => $attribute ) {
			$attributeValue             = ( $type[ $key ] === 'pattern' ) ? $static[ $key ] : $attributes[ $key ];
			$replacedAttribute          = MerchantAttributeReplaceFactory::replace_attribute( $attribute, $this->config );
			$data[ $replacedAttribute ] = $attributeValue;
		}

		return $data;
	}
}
