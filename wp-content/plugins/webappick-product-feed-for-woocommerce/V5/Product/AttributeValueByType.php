<?php

namespace CTXFeed\V5\Product;

use CTXFeed\V5\Helper\FeedHelper;
use CTXFeed\V5\Helper\ProductHelper;
use CTXFeed\V5\Output\AttributeMapping;
use CTXFeed\V5\Output\CategoryMapping;
use CTXFeed\V5\Output\DynamicAttributes;
use CTXFeed\V5\Output\FormatOutput;
use CTXFeed\V5\Output\OutputCommands;
use CTXFeed\V5\Utility\Config;
use WC_Abstract_Legacy_Product;
use WC_Product;
use WC_Product_External;
use WC_Product_Grouped;
use WC_Product_Variable;
use WC_Product_Variation;

class AttributeValueByType {

	/**
	 * Advance Custom Field (ACF) Prefix
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const PRODUCT_ACF_FIELDS = 'acf_fields_';
	/**
	 * Post meta prefix for dropdown item
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const POST_META_PREFIX = 'wf_cattr_';
	/**
	 * Product Attribute (taxonomy & local) Prefix
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const PRODUCT_ATTRIBUTE_PREFIX = 'wf_attr_';
	/**
	 * Product Taxonomy Prefix
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const PRODUCT_TAXONOMY_PREFIX = 'wf_taxo_';
	/**
	 * Product Category Mapping Prefix
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const PRODUCT_CATEGORY_MAPPING_PREFIX = 'wf_cmapping_';
	/**
	 * Product Dynamic Attribute Prefix
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const PRODUCT_DYNAMIC_ATTRIBUTE_PREFIX = 'wf_dattribute_';
	/**
	 * WordPress Option Prefix
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const WP_OPTION_PREFIX = 'wf_option_';
	/**
	 * Extra Attribute Prefix
	 *
	 * @since 3.2.20
	 */
	const PRODUCT_EXTRA_ATTRIBUTE_PREFIX = 'wf_extra_';
	/**
	 * Product Attribute Mappings Prefix
	 *
	 * @since 3.3.2*
	 */
	const PRODUCT_ATTRIBUTE_MAPPING_PREFIX = 'wp_attr_mapping_';
	/**
	 * Product Custom Field Prefix
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const PRODUCT_CUSTOM_IDENTIFIER = 'woo_feed_';

	/**
	 * Feed rules prefix
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const FEED_RULES_OPTION_PREFIX = 'wf_feed_';

	/**
	 * Feed temporary file body name prefix
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const FEED_TEMP_BODY_PREFIX = 'wf_store_feed_body_info_';

	/**
	 * Feed temporary file header name prefix
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const FEED_TEMP_HEADER_PREFIX = 'wf_store_feed_header_info_';

	/**
	 * Feed temporary file footer name prefix
	 *
	 * @since 3.1.18
	 * @var string
	 */
	const FEED_TEMP_FOOTER_PREFIX = 'wf_store_feed_footer_info_';


	/**
	 * WP Option Name
	 *
	 * @since 6.1.1
	 * @var string
	 */
	const WP_OPTION_NAME = 'wpfp_option';

	private $attribute;
	private $merchant_attribute;
	private $product;
	private $productInfo;
	private $formatOutput;
	private $formatCommand;
	private $config;

	/**
	 * @param        $attribute
	 * @param        $merchant_attribute
	 * @param        $product
	 * @param Config $config
	 */
	public function __construct( $attribute, $product, $config, $merchant_attribute = null ) {
		$this->attribute          = $attribute;
		$this->merchant_attribute = $merchant_attribute;
		$this->product            = $product;
		$this->config             = $config;
		$this->productInfo        = new ProductInfo( $this->product, $this->config );
		$this->formatOutput       = new FormatOutput( $this->product, $this->config, $this->attribute );
		$this->formatCommand      = new OutputCommands( $this->product, $this->config, $this->attribute );



		// Load Merchant Template Override File.
		// OverrideFactory::init( $config );
	}

	/**
	 * Get product attribute value by attribute type.
	 *
	 * @return mixed|void
	 */
	public function get_value( $attr = '' ) {
		if ( ! empty( $attr ) ) {
			$this->attribute = $attr;
		}

		$this->attribute = ($this->attribute === null ? '' : $this->attribute);

		if ( method_exists( $this->productInfo, $this->attribute ) ) {
			$attribute = $this->attribute;
			$output    = $this->productInfo->$attribute();
		} elseif ( false !== strpos( $this->attribute, self::PRODUCT_EXTRA_ATTRIBUTE_PREFIX ) ) {
			$attribute = str_replace( self::PRODUCT_EXTRA_ATTRIBUTE_PREFIX, '', $this->attribute );

			/**
			 * Filter output for extra attribute, which can be added via 3rd party plugins.
			 *
			 * @param string $output the output
			 * @param WC_Product|WC_Product_Variable|WC_Product_Variation|WC_Product_Grouped|WC_Product_External|WC_Product_Composite $product Product Object.
			 *
			 *
			 * @since 3.3.5
			 */
			return apply_filters( "woo_feed_get_extra_{$attribute}_attribute", '', $this->product, $this->config );
		} elseif ( false !== strpos( $this->attribute, 'csv_tax_' ) ) {
			$key    = str_replace( 'csv_tax_', '', $this->attribute );
			$output = $this->productInfo->tax( (string) $key );
		} elseif ( false !== strpos( $this->attribute, 'csv_shipping_' ) ) {
			$key    = str_replace( 'csv_shipping_', '', $this->attribute );
			$output = $this->productInfo->shipping( (string) $key );
		} elseif ( false !== strpos( $this->attribute, self::PRODUCT_ACF_FIELDS ) ) {
			$output = ProductHelper::get_acf_field( $this->product, $this->attribute );
		} elseif ( false !== strpos( $this->attribute, self::PRODUCT_ATTRIBUTE_MAPPING_PREFIX ) ) {
			//$output = ProductHelper::get_attribute_mapping( $this->product, $this->attribute, $this->merchant_attribute, $this->config );
			$output = AttributeMapping::getMappingValue( $this->attribute, $this->merchant_attribute, $this->product, $this->config );
			//die($output);
		} elseif ( false !== strpos( $this->attribute, self::PRODUCT_DYNAMIC_ATTRIBUTE_PREFIX ) ) {
			//$output = ProductHelper::get_dynamic_attribute( $this->product, $this->attribute, $this->merchant_attribute, $this->config );
			$output = DynamicAttributes::getDynamicAttributeValue( $this->attribute, $this->merchant_attribute, $this->product, $this->config );
		} elseif ( false !== strpos( $this->attribute, self::PRODUCT_CUSTOM_IDENTIFIER ) || woo_feed_strpos_array( array(
				'_identifier_gtin',
				'_identifier_ean',
				'_identifier_mpn'
			), $this->attribute ) ) {
			$output = ProductHelper::get_custom_filed( $this->attribute, $this->product, $this->config );
		} elseif ( false !== strpos( $this->attribute, self::PRODUCT_ATTRIBUTE_PREFIX ) ) {
			$this->attribute = str_replace( self::PRODUCT_ATTRIBUTE_PREFIX, '', $this->attribute );
			$output          = ProductHelper::get_product_attribute( $this->attribute, $this->product, $this->config );
		} elseif ( false !== strpos( $this->attribute, self::POST_META_PREFIX ) ) {
			$this->attribute = str_replace( self::POST_META_PREFIX, '', $this->attribute );
			$output          = ProductHelper::get_product_meta( $this->attribute, $this->product, $this->config );
			$this->attribute = self::POST_META_PREFIX.$this->attribute;
		} elseif ( false !== strpos( $this->attribute, self::PRODUCT_TAXONOMY_PREFIX ) ) {
			$this->attribute = str_replace( self::PRODUCT_TAXONOMY_PREFIX, '', $this->attribute );
			$output          = ProductHelper::get_product_taxonomy( $this->attribute, $this->product, $this->config );
			//[For getting exact attribute name need to added "PRODUCT_TAXONOMY_PREFIX" which is removed before cz in ProductHelper check  '$productAttribute !== $str_replace['subject']', 'Jira tkt: CTX-656']
			$this->attribute = self::PRODUCT_TAXONOMY_PREFIX.$this->attribute;
		} elseif ( false !== strpos( $this->attribute, self::PRODUCT_CATEGORY_MAPPING_PREFIX ) ) {
			$id = $this->product->is_type( 'variation' ) ? $this->product->get_parent_id() : $this->product->get_id();
			//$output = ProductHelper::get_category_mapping( $this->attribute, $id );
			$output = CategoryMapping::getCategoryMappingValue( $this->attribute, $id );
		} elseif ( false !== strpos( $this->attribute, self::WP_OPTION_PREFIX ) ) {
			$optionName = str_replace( self::WP_OPTION_PREFIX, '', $this->attribute );
			$output     = get_option( $optionName );
		} elseif ( strpos( $this->attribute, 'image_' ) === 0 ) {
			// For additional image method images() will be used with extra parameter - image number
			$imageKey = explode( '_', $this->attribute );
			if ( empty( $imageKey[1] ) || ! is_numeric( $imageKey[1] ) ) {
				$imageKey[1] = '';
			}
			$output = $this->productInfo->images( $imageKey[1] );
		} elseif ($this->attribute == 'identifier_exists' ) {
			$output          = ProductHelper::overwrite_identifier_exists( $this->attribute, $this->product, $this->config );
		} else {
			// return the attribute so multiple attribute can be used with separator to make custom attribute.
			$output = $this->attribute;
		}

		// Json encode if value is an array
		if ( is_array( $output ) ) {
			$output = wp_json_encode( $output );
		}


		//String Replace
		if ( $this->config->get_string_replace() ) {
			$output = ProductHelper::str_replace( $output, $this->attribute, $this->config );
		}


		// Number Format and Format output according to Output Types config.
		$outputTypes = $this->config->get_attribute_output_types( $this->attribute, $this->merchant_attribute );
		if ( ! empty( $outputTypes ) ) {
			$output = $this->formatOutput->get_output( $output, $outputTypes );
		}


		// Process Commands.
		$outputCommands = $this->config->get_attribute_commands( $this->attribute, $this->merchant_attribute );
		if ( ! empty( $outputCommands ) ) {
			$output = $this->formatCommand->process_command( $output, $outputCommands );
		}

		$output = ProductHelper::add_prefix_suffix( $output, $this->attribute, $this->config, $this->merchant_attribute );

		return $this->apply_filters_to_attribute_value( $output, $this->merchant_attribute );
	}

	/**
	 *  Apply Filter to Attribute value
	 *
	 * @param $output
	 * @param $merchant_attribute
	 *
	 * @return mixed|void
	 */
	protected function apply_filters_to_attribute_value( $output, $merchant_attribute ) {
		$product_attribute = $this->attribute;
		/**
		 * Filter attribute value
		 *
		 * @param string $output the output
		 * @param WC_Abstract_Legacy_Product $product Product Object.
		 * @param object $config feed config/rule
		 *
		 * @since 3.4.3
		 *
		 */
		$output = apply_filters( 'woo_feed_get_attribute', $output, $this->product, $this->config, $product_attribute, $merchant_attribute );

		/**
		 * Filter attribute value before return based on product attribute name
		 *
		 * @param string $output the output
		 * @param WC_Abstract_Legacy_Product $product Product Object.
		 * @param array feed config/rule
		 *
		 * @since 3.3.5
		 *
		 */

		$output = apply_filters( "woo_feed_get_{$product_attribute}_attribute", $output, $this->product, $this->config, $product_attribute, $merchant_attribute );

		/**
		 * Filter attribute value before return based on merchant name
		 *
		 * @param string $output the output
		 * @param WC_Abstract_Legacy_Product $product Product Object.
		 * @param array feed config/rule
		 *
		 * @since 3.3.5
		 *
		 */

		$output = apply_filters( "woo_feed_get_{$this->config->provider}_attribute", $output, $this->product, $this->config, $product_attribute, $merchant_attribute );

		/**
		 * Filter attribute value before return based on merchant and merchant attribute name
		 *
		 * @param string $output the output
		 * @param WC_Abstract_Legacy_Product $product Product Object.
		 * @param array feed config/rule
		 *
		 * @since 3.3.7
		 *
		 */
		$merchant_attribute= ($merchant_attribute === null ? '' : $merchant_attribute);
		$merchant_attribute = str_replace( [ ' ', 'g:' ], '', $merchant_attribute );

		if( isset( $this->config->provider ) && $this->config->provider === 'google' && $merchant_attribute === 'certification'){
			if( $this->config->feedType === 'xml' ){
				$output = array(
					'g:certification_authority'=>'EC',
					'g:certification_name'=>'EPREL',
					'g:certification_code'=>$output,
				);
			}else {
				$output = "EC:EPREL:$output";
			}

		}

		//$output = "woo_feed_get_{$this->config->provider}_{$merchant_attribute}_attribute";
		return apply_filters( "woo_feed_get_{$this->config->provider}_{$merchant_attribute}_attribute", $output, $this->product, $this->config, $product_attribute, $merchant_attribute );


		/**
		 * Filter attribute value for translatePress
		 *
		 * @param string $output the output
		 * @param WC_Abstract_Legacy_Product $product Product Object.
		 * @param object $config feed config/rule
		 *
		 * @since 7.0.0
		 *
		 */

		$output = apply_filters( 'woo_feed_get_translatePress_attribute', $output, $this->product, $this->config, $product_attribute, $merchant_attribute );
	}


}
