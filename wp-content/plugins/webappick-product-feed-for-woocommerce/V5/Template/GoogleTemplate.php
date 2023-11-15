<?php

namespace CTXFeed\V5\Template;

use CTXFeed\V5\File\FileFactory;
use CTXFeed\V5\Filter\ValidateProduct;
use CTXFeed\V5\Helper\ProductHelper;
use CTXFeed\V5\Product\ProductFactory;
use CTXFeed\V5\Utility\Config;
use CTXFeed\V5\Utility\Logs;

class GoogleTemplate implements TemplateInterface {
	/**
	 * @var Config $config Contain Feed Config.
	 */
	private $config;
	/**
	 * @var array $ids Contain Product Ids.
	 */
	private $ids;
	/**
	 * @var array $structure Contain Feed Structure.
	 */
	private $structure;

	public function __construct( $ids, $config ) {
		$this->ids       = $ids;
		$this->config    = $config;
		$this->structure = TemplateFactory::get_structure( $config );
	}

	/**
	 * Get Feed Body.
	 *
	 * @return false|string
	 */
	public function get_feed() {
		$feed = ProductFactory::get_content( $this->ids, $this->config, $this->structure );

		return $feed->make_body();
	}

	/**
	 * Get Feed Header.
	 *
	 * @return mixed
	 */
	public function get_header() {
		$feed = FileFactory::GetData( $this->structure, $this->config );
		$feed = $feed->make_header_footer();

		return $feed['header'];
	}

	/**
	 * Get Feed Footer.
	 *
	 * @return mixed
	 */
	public function get_footer() {
		$feed = FileFactory::GetData( $this->ids, $this->config );
		$feed = $feed->make_header_footer();

		return $feed['footer'];
	}

	/**
	 * @param $ids
	 * @param $config
	 * @param $structure
	 *
	 * @return \CTXFeed\V5\File\FileInfo
	 */
//	private function get_content( $ids, $config, $structure ) {
//		$info = [];
//		Logs::write_log( $config->filename, 'Getting Products Information.' );
//		Logs::write_log( $config->filename, 'Validating Product' );
//
//		foreach ( $ids as $id ) {
//			$product = wc_get_product( $id );
//
//			// Validate Product and add for feed.
//			if ( ValidateProduct::is_valid( $product, $config, $id ) ) {
//				$info1   = [];
//				$info [] = $this->get_product_info( $product, $structure, $config, $info1 );
//			}
//		}
//
//		return FileFactory::GetData( $info, $config );
//	}

	/**
	 * @param $product
	 * @param $structure
	 * @param $config
	 * @param $info
	 *
	 * @return array
	 */
//	private function get_product_info( $product, $structure, $config, $info ) {
//		if ( is_array( $structure ) ) {
//			foreach ( $structure as $key => $attribute ) {
//				if ( is_array( $attribute ) ) {
//					$value[ $key ] = $this->get_product_info( $product, $attribute, $config, $info );
//				} else {
//					$getValue ="";
////					if(strpos($attribute,':')){
////
////					}else{
////
////					}
//					$value[ $key ] = ProductHelper::getAttributeValueByType( $attribute, $product, $config );
//				}
//			}
//		} else {
//			return $info;
//		}
//
//		return $value;
//	}
}
