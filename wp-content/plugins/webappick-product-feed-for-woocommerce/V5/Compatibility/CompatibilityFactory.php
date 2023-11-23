<?php

namespace CTXFeed\V5\Compatibility;

/**
 * Class Compatibility Factory
 *
 * @package    CTXFeed
 * @subpackage CTXFeed\V5\Compatibility
 * @author     Ohidul Islam <wahid0003@gmail.com>
 * @link       https://webappick.com
 * @license    https://opensource.org/licenses/gpl-license.php GNU Public License
 * @category   MyCategory
 */
class CompatibilityFactory {

	/**
	 * Initialize the compatibility classes
	 */
	public static function init() {
		$classes = self::get_classes();

		foreach ( $classes as $class ) {
			$class_name = __NAMESPACE__ . '\\' . $class . 'Compatibility';

			if ( ! class_exists( $class_name ) ) {
				continue;
			}

			new $class_name;
		}
	}

	/**
	 * Get the compatibility class for the current plugin version
	 *
	 * @return array Array of compatibility classes
	 */
	public static function get_classes() {
		// Get the current working directory
		$directory = plugin_dir_path( __FILE__ );

		// Scan the directory for files
		$all_files = scandir( $directory );

		// Filter files to get only those ending with 'Compatibility.php'
		$filtered_files = array_filter(
			$all_files,
			static function ( $file ) {
				return strpos( $file, 'Compatibility.php' ) && substr( $file, -strlen( 'Compatibility.php' ) ) === 'Compatibility.php';
			}
		);

		// Extract the part of the filename before 'Compatibility'
		return array_map(
			static function ( $file ) {
				return str_replace( 'Compatibility.php', '', $file );
			},
			$filtered_files
		);
	}

}
