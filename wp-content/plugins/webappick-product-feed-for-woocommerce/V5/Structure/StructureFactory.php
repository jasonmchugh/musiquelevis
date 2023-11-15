<?php

namespace CTXFeed\V5\Structure;


/**
 * Class StructureFactory
 *
 * @package    CTXFeed\V5\Structure
 * @subpackage CTXFeed\V5\Structure
 */
class StructureFactory {
	public static function get( $config ) {
		$template = $config->get_feed_template();
		$class    = "\CTXFeed\V5\Structure\\" . ucfirst( $template ) . "Structure";
		if ( class_exists( $class ) ) {
			return new Structure(new $class($config));
		}

		return new Structure(new CustomStructure($config));
	}
}
