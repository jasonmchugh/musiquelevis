<?php

namespace CTXFeed\V5\Structure;

use CTXFeed\V5\Merchant\MerchantAttributeReplaceFactory;

class BingStructure implements StructureInterface {

	private $config;

	public function __construct( $config ) {
		$this->config = $config;
	}

	public function getXMLStructure() {
		return $this->getCSVStructure();
	}

	public function getCSVStructure() {
		$attributes  = $this->config->attributes;
		$mattributes = $this->config->mattributes;
		$static      = $this->config->default;
		$type        = $this->config->type;
		$data        = [];

		if (!in_array("identifier_exists", $attributes)){
			array_push($attributes,'identifier_exists');
			array_push($mattributes,'identifier_exists');
			array_push($type,'attribute');
		}

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
	function getJSONStructure() {
		return $this->getCSVStructure();
	}
}
