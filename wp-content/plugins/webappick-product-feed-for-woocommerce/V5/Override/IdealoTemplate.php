<?php

namespace CTXFeed\V5\Override;


/**
 * Class FacebookTemplate
 *
 * @package    CTXFeed\V5\Override
 * @subpackage CTXFeed\V5\Override
 */
class IdealoTemplate {
	public function __construct() {
		add_filter( 'ctx_feed_number_format', [
			$this,
			'ctx_feed_idealo_number_format'
		] );
	}

	public function ctx_feed_idealo_number_format() {
		return [
			'decimals'           => '2',
			'decimal_separator'  => '.',
			'thousand_separator' => '',
		];
	}
}
