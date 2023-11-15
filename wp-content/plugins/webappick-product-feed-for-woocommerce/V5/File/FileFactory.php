<?php
namespace CTXFeed\V5\File;
use CTXFeed\V5\Utility\Config;
use CTXFeed\V5\File\CSV;
use CTXFeed\V5\File\TXT;
use CTXFeed\V5\File\XLS;
use CTXFeed\V5\File\XML;
use CTXFeed\V5\File\JSON;

/**
 *
 */
class FileFactory {
	/**
	 * Get Feed file data by file type.
	 *
	 * @param array  $data   Contain Products array.
	 * @param Config $config Contain Feed Configurations.
	 *
	 * @return FileInfo
	 */
	public static function GetData( $data, $config ) {
		$type  = $config->feedType;
		$class = "\CTXFeed\V5\File\\".strtoupper( $type );

		if ( class_exists( $class ) ) {
			return new FileInfo( new $class( $data, $config ) );
		}

		return new FileInfo( new CSV( $data, $config ));
	}
}
