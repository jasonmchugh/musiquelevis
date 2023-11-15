<?php

namespace CTXFeed\V5\Override;


/**
 * Class PinterestTemplate
 *
 * @package    CTXFeed
 * @subpackage CTXFeed\V5\Override
 */
class PinterestTemplate {
	public function __construct() {
		add_filter( 'woo_feed_get_pinterest_color_attribute', [
			$this,
			'woo_feed_get_pinterest_color_size_attribute_callback'
		] );
		add_filter( 'woo_feed_get_pinterest_size_attribute', [
			$this,
			'woo_feed_get_pinterest_color_size_attribute_callback'
		] );
	}
	
	public function woo_feed_get_pinterest_color_size_attribute_callback( $output ) {
		return str_replace( [ ' ' ], [ '' ], $output );
	}
}

