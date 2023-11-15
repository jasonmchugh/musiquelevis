<?php

namespace CTXFeed\V5\Override;


use CTXFeed\V5\Compatibility\ExcludeCaching;

/**
 * Class OverrideFactory
 *
 * @package    CTXFeed
 * @subpackage CTXFeed\V5\Override
 */
class OverrideFactory {
	
	public static function TemplateOverride( $config ) {
		$class = "\CTXFeed\V5\Override\\" . ucfirst( $config->provider ) . 'Template';
		if ( class_exists( $class ) ) {
			return new $class();
		}
		
		return false;
	}
	
	public static function excludeCache() {
		return new ExcludeCaching();
	}
	
	public static function Common() {
		return new Common();
	}
	
	public static function Advance() {
		return new Advance();
	}
	
}