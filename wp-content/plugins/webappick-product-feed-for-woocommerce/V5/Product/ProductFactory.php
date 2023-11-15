<?php

namespace CTXFeed\V5\Product;

use CTXFeed\V5\File\FileFactory;
use CTXFeed\V5\File\FileInfo;
use CTXFeed\V5\Filter\ValidateProduct;
use CTXFeed\V5\Helper\ProductHelper;
use CTXFeed\V5\Structure\GooglereviewStructure;
use CTXFeed\V5\Utility\Logs;
use Exception;
use WC_Product;

/**
 *
 */
class ProductFactory {

	/**
	 * @param $ids
	 * @param $config
	 * @param $structure
	 *
	 * @return FileInfo
	 * @throws Exception
	 */
	public static function get_content( $ids, $config, $structure ) {

		$info = [];
		Logs::write_log( $config->filename, 'Getting Products Information.' );
		Logs::write_log( $config->filename, 'Validating Product' );

		if( $config->get_feed_template()!=='googlereview' ) {
			foreach ($ids as $id) {
				$product = ProductHelper::get_product_object($id, $config);
				// Validate Product and add for feed.
				if (ValidateProduct::is_valid($product, $config, $id)) {
					$info1 = [];

					$info [] = self::get_product_info($product, $structure, $config, $info1);
				}
			}
		}else{
			$info [] = $structure;
		}

		return FileFactory::GetData( $info, $config );
	}

	/**
	 * @param $product
	 * @param $structure
	 * @param $config
	 * @param $info
	 *
	 * @return array|mixed
	 */
	public static function get_product_info( $product, $structure, $config, $info ) {
		$value = [];
		if ( is_array( $structure ) ) {
			foreach ( $structure as $key => $attribute ) {
				if ( is_array( $attribute ) ) {
					$value[ $key ] = self::get_product_info( $product, $attribute, $config, $info );
				} elseif ( $config->feedType === 'xml' ) {
					$value[ $key ] = ProductHelper::getAttributeValueByType( $attribute, $product, $config, $key );
				} else {
					$value[ $key ] = self::get_csv_attribute_value( $attribute, $product, $config, $key );
				}
			}
		} else {
			return $info;
		}

		return $value;
	}

	/**
	 * @param                            $attribute
	 * @param                            $product
	 * @param \CTXFeed\V5\Utility\Config $config
	 * @param                            $merchant_attribute
	 *
	 * @return mixed|void
	 */
	public static function get_csv_attribute_value( $attribute, $product, $config, $merchant_attribute ) {

		$attribute= ($attribute === null ? '' : $attribute);
		$value = [];
		if ( strpos( $attribute, ',' ) ) {
			$separator = ',';
			$data3     = explode( ',', $attribute );
			foreach ( $data3 as $data2 ) {
				if ( strpos( $attribute, ':' ) ) {
					$value[] = self::get_csv_attribute_value( $data2, $product, $config, $merchant_attribute );
				} else {
					$value[] = ProductHelper::getAttributeValueByType( $data2, $product, $config, $merchant_attribute );
				}
			}

			return implode( $separator, array_filter( $value ) );
		}

		if ( strpos( $attribute, ':' ) ) {
			$separator = ':';
			$attribute = explode( ':', $attribute );
			foreach ( $attribute as $data ) {
				$value [] = ProductHelper::getAttributeValueByType( $data, $product, $config, $merchant_attribute );
			}

			return implode( $separator, array_filter( $value ) );
		}

		return ProductHelper::getAttributeValueByType( $attribute, $product, $config, $merchant_attribute );
	}

}
