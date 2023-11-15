<?php
namespace CTXFeed\V5\Price;
interface PriceInterface {
	public function __construct( $product, $config );

	public function regular_price( $tax = false );

	public function price( $tax = false );

	public function sale_price( $tax = false );

	public function convert_currency( $price, $price_type );

	public function add_tax( $price );

}



