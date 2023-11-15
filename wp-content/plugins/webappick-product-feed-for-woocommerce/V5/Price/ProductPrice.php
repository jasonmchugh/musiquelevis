<?php

namespace CTXFeed\V5\Price;
class ProductPrice {
	private $price;
	
	public function __construct( PriceInterface $price ) {
		$this->price = $price;
	}
	
	/**
	 * @param $tax
	 *
	 * @return string
	 */
	public function regular_price( $tax = false ) {
		$regular_price = $this->price->regular_price( $tax );
		if ( $regular_price <= 0 ) {
			return '';
		}
		
		return $regular_price;
	}
	/**
	 * @param $tax
	 *
	 * @return string
	 */
	public function price( $tax = false ) {
		$price = $this->price->price( $tax );
		if ( $price <= 0 ) {
			return '';
		}
		
		return $price;
	}
	
	/**
	 * @param $tax
	 *
	 * @return string
	 */
	public function sale_price( $tax = false ) {
		$sale_price = $this->price->sale_price( $tax );
		if ( $sale_price <= 0 ) {
			return '';
		}
		
		return $sale_price;
	}
}