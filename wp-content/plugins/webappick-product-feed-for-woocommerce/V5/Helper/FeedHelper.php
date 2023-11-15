<?php

namespace CTXFeed\V5\Helper;

use CTXFeed\V5\Common\Helper;
use CTXFeed\V5\Product\AttributeValueByType;
use CTXFeed\V5\Query\QueryFactory;
use CTXFeed\V5\Template\TemplateFactory;
use CTXFeed\V5\Utility\Config;
use CTXFeed\V5\Utility\FileSystem;
use \WP_Error;

class FeedHelper {


	/** Sanitize Feed Configs
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public static function sanitize_form_fields( $data ) {
		foreach ( $data as $k => $v ) {
			if ( true === apply_filters( 'woo_feed_sanitize_form_fields', true, $k, $v, $data ) ) {
				if ( is_array( $v ) ) {
					$v = self::sanitize_form_fields( $v );
				} else {
					// $v = sanitize_text_field( $v ); #TODO should not trim Prefix and Suffix field
				}
			}
			$data[ $k ] = apply_filters( 'woo_feed_sanitize_form_field', $v, $k );
		}

		return $data;
	}


	/**
	 * Generate Unique file Name.
	 * This will insure unique slug and file name for a single feed.
	 *
	 * @param string $filename
	 * @param string $type
	 * @param string $provider
	 *
	 * @return string|string[]
	 */
	public static function generate_unique_feed_file_name( $filename, $type, $provider ) {

		$feedDir      = Helper::get_file_dir( $provider, $type );
		$raw_filename = sanitize_title( $filename, '', 'save' );
		// check option name uniqueness ...
		$raw_filename = self::unique_feed_slug( $raw_filename, 'wf_feed_' );
		$raw_filename = sanitize_file_name( $raw_filename . '.' . $type );
		$raw_filename = wp_unique_filename( $feedDir, $raw_filename );
		$raw_filename = str_replace( '.' . $type, '', $raw_filename );

		return - 1 !== (int) $raw_filename ? $raw_filename : false;
	}

	/**
	 * Generate Unique slug for feed.
	 * This function only check database for existing feed for generating unique slug.
	 * Use generate_unique_feed_file_name() for complete unique slug name.
	 *
	 * @param string $slug slug for checking uniqueness.
	 * @param string $prefix prefix to check with. Optional.
	 * @param int $option_id option id. Optional option id to exclude specific option.
	 *
	 * @return string
	 * @see wp_unique_post_slug()
	 *
	 */
	public static function unique_feed_slug( $slug, $prefix = '', $option_id = null ) {
		$slug = CommonHelper::unique_option_name( $slug, $prefix, $option_id );

		return $slug;
	}

	/**
	 * Sanitize And Save Feed config data (array) to db (option table)
	 *
	 * @param array $feedrules data to be saved in db
	 * @param null $feed_option_name feed (file) name. optional, if empty or null name will be auto generated
	 * @param bool $configOnly save only wf_config or both wf_config and wf_feed_. default is only wf_config
	 *
	 * @return bool|string          return false if failed to update. return filename if success
	 */
	public static function save_feed_config_data( $feedrules, $feed_option_name = null, $configOnly = true ) {


		$prepared_feed_rules = self::prepare_feed_rules_to_save( $feedrules, $feed_option_name );
		$feed_option_name    = $prepared_feed_rules['feed_option_name'];
		$update              = $prepared_feed_rules['is_update'];
		$old_data            = $prepared_feed_rules['old_data'];

		self::call_action_before_update_feed_config( $update, $feedrules, $feed_option_name );


		$updated = update_option( $feed_option_name, $prepared_feed_rules['feedrules_to_save'], false );


		self::call_action_after_update_feed_config( $update, $feedrules, $feed_option_name );

		// return filename on success or update status
		return $updated ? $feed_option_name : $updated;
	}

	/**
	 * @param $feedrules
	 * @param $feed_option_name
	 *
	 * @return array|false
	 */
	public static function prepare_feed_rules_to_save( $feedrules, $feed_option_name ) {
		if ( ! is_array( $feedrules ) ) {
			return false;
		}


		if ( ! isset( $feedrules['filename'], $feedrules['feedType'], $feedrules['provider'] ) ) {
			return false;
		}
		// Unnecessary form fields to remove
		$removables = array( 'closedpostboxesnonce', '_wpnonce', '_wp_http_referer', 'save_feed_config', 'edit-feed' );
		foreach ( $removables as $removable ) {
			if ( isset( $feedrules[ $removable ] ) ) {
				unset( $feedrules[ $removable ] );
			}
		}

		// Sanitize Fields
		$feedrules = FeedHelper::sanitize_form_fields( $feedrules );
		$update    = false;
		$old_feed  = [];
		// Get new feed file name if new feed.
		if ( empty( $feed_option_name ) ) {
			$feed_option_name = FeedHelper::generate_unique_feed_file_name( $feedrules['filename'], $feedrules['feedType'], $feedrules['provider'] );
			$feed_option_name = AttributeValueByType::FEED_RULES_OPTION_PREFIX . $feed_option_name;
		} else {
			$old_feed = get_option( $feed_option_name, array() );
			$update   = true;
		}


		$feed_URL = FeedHelper::get_file_url( $feed_option_name, $feedrules['provider'], $feedrules['feedType'] );

		/**
		 * Filters feed data just before it is inserted into the database.
		 *
		 * @param array $feedrules An array of sanitized config
		 * @param array $old_feed An array of old feed data
		 * @param string $feed_option_name Option name
		 *
		 * @since 3.3.3
		 *
		 */
		$feedrules = apply_filters( 'woo_feed_insert_feed_data', $feedrules, $old_feed, $feed_option_name );

		// Save Info into database
		$feedrules_to_save['feedrules']    = $feedrules;
		$feedrules_to_save['url']          = $feed_URL;
		$feedrules_to_save['last_updated'] = date( 'Y-m-d H:i:s', strtotime( current_time( 'mysql' ) ) );
		$feedrules_to_save['status']       = isset( $old_feed['status'] ) && 1 === (int) $old_feed['status'] ? 1 : 1;

		return [
			'feedrules_to_save' => $feedrules_to_save,
			'is_update'         => $update,
			'old_data'          => $old_feed,
			'feed_option_name'  => $feed_option_name,
		];
	}

	/**
	 * @param $update
	 * @param $feedrules
	 * @param $feed_option_name
	 *
	 * @return void
	 */
	public static function call_action_before_update_feed_config( $update, $feedrules, $feed_option_name ) {
		if ( $update ) {
			/**
			 * Before Updating Config to db
			 *
			 * @param array $feedrules An array of sanitized config
			 * @param string $feed_option_name Option name
			 */
			do_action( 'woo_feed_before_update_config', $feedrules, $feed_option_name );
		} else {
			/**
			 * Before inserting Config to db
			 *
			 * @param array $feedrules An array of sanitized config
			 * @param string $feed_option_name Option name
			 */
			do_action( 'woo_feed_before_insert_config', $feedrules, $feed_option_name );
		}
	}

	/**
	 * @param $update
	 * @param $feedrules
	 * @param $feed_option_name
	 *
	 * @return void
	 */
	public static function call_action_after_update_feed_config( $update, $feedrules, $feed_option_name ) {
		if ( $update ) {
			/**
			 * After Updating Config to db
			 *
			 * @param array $feedrules An array of sanitized config
			 * @param string $feed_option_name Option name
			 */
			do_action( 'woo_feed_after_update_config', $feedrules, $feed_option_name );
		} else {
			/**
			 * After inserting Config to db
			 *
			 * @param array $feedrules An array of sanitized config
			 * @param string $feed_option_name Option name
			 */
			do_action( 'woo_feed_after_insert_config', $feedrules, $feed_option_name );
		}
	}

	/**
	 * @param $rules
	 * @param $context
	 *
	 * @return mixed|null
	 */
	private static function parse_feed_rules( $rules = array(), $context = 'view' ) {

		if ( empty( $rules ) ) {
			$rules = array();
		}

		if ( Helper::is_pro() ) {
			$defaults = self::pro_default_feed_rules();
		} else {
			$defaults = self::free_default_feed_rules();
		}

		$rules                = wp_parse_args( $rules, $defaults );
		$rules['filter_mode'] = wp_parse_args(
			$rules['filter_mode'],
			array(
				'product_ids' => 'include',
				'categories'  => 'include',
				'post_status' => 'include',
			)
		);

		$rules['campaign_parameters'] = wp_parse_args(
			$rules['campaign_parameters'],
			array(
				'utm_source'   => '',
				'utm_medium'   => '',
				'utm_campaign' => '',
				'utm_term'     => '',
				'utm_content'  => '',
			)
		);

		if ( ! empty( $rules['provider'] ) && is_string( $rules['provider'] ) ) {
			/**
			 * filter parsed rules for provider
			 *
			 * @param array $rules
			 * @param string $context
			 *
			 * @since 3.3.7
			 *
			 */
			$rules = apply_filters( "woo_feed_{$rules['provider']}_parsed_rules", $rules, $context );
		}

		/**
		 * filter parsed rules
		 *
		 * @param array $rules
		 * @param string $context
		 *
		 * @since 3.3.7 $provider parameter removed
		 *
		 */
		return apply_filters( 'woo_feed_parsed_rules', $rules, $context );
	}

	/**
	 * Get pro version feed default rules.
	 *
	 * @param $rules
	 *
	 * @return mixed|null
	 */
	private static function free_default_feed_rules( $rules = [] ) {
		$defaults = array(
			'provider'            => '',
			'filename'            => '',
			'feedType'            => '',
			'feed_country'        => '',
			'ftpenabled'          => 0,
			'ftporsftp'           => 'ftp',
			'ftphost'             => '',
			'ftpport'             => '21',
			'ftpuser'             => '',
			'ftppassword'         => '',
			'ftppath'             => '',
			'ftpmode'             => 'active',
			'is_variations'       => 'y',
			'variable_price'      => 'first',
			'variable_quantity'   => 'first',
			'feedLanguage'        => apply_filters( 'wpml_current_language', null ),
			'feedCurrency'        => get_woocommerce_currency(),
			'itemsWrapper'        => 'products',
			'itemWrapper'         => 'product',
			'delimiter'           => ',',
			'enclosure'           => 'double',
			'extraHeader'         => '',
			'vendors'             => array(),
			// Feed Config
			'mattributes'         => array(), // merchant attributes
			'prefix'              => array(), // prefixes
			'type'                => array(), // value (attribute) types
			'attributes'          => array(), // product attribute mappings
			'default'             => array(), // default values (patterns) if value type set to pattern
			'suffix'              => array(), // suffixes
			'output_type'         => array(), // output type (output filter)
			'limit'               => array(), // limit or command
			// filters tab
			'composite_price'     => '',
			'shipping_country'    => '',
			'tax_country'         => '',
			'product_ids'         => '',
			'categories'          => array(),
			'post_status'         => array( 'publish' ),
			'filter_mode'         => array(),
			'campaign_parameters' => array(),

			'ptitle_show'        => '',
			'decimal_separator'  => wc_get_price_decimal_separator(),
			'thousand_separator' => wc_get_price_thousand_separator(),
			'decimals'           => wc_get_price_decimals(),
		);
		$rules    = wp_parse_args( $rules, $defaults );

		return apply_filters( 'woo_feed_free_default_feed_rules', $rules );
	}

	/**
	 * Get pro version feed default rules.
	 *
	 * @param $rules
	 *
	 * @return mixed|null
	 */
	private static function pro_default_feed_rules( $rules = [] ) {
		$defaults = array(
			'provider'              => '',
			'feed_country'          => '',
			'filename'              => '',
			'feedType'              => '',
			'ftpenabled'            => 0,
			'ftporsftp'             => 'ftp',
			'ftphost'               => '',
			'ftpport'               => '21',
			'ftpuser'               => '',
			'ftppassword'           => '',
			'ftppath'               => '',
			'ftpmode'               => 'active',
			'is_variations'         => 'y', // Only Variations (All Variations)
			'variable_price'        => 'first',
			'variable_quantity'     => 'first',
			'feedLanguage'          => apply_filters( 'wpml_current_language', null ),
			'feedCurrency'          => get_woocommerce_currency(),
			'itemsWrapper'          => 'products',
			'itemWrapper'           => 'product',
			'delimiter'             => ',',
			'enclosure'             => 'double',
			'extraHeader'           => '',
			'vendors'               => array(),
			// Feed Config
			'mattributes'           => array(), // merchant attributes
			'prefix'                => array(), // prefixes
			'type'                  => array(), // value (attribute) types
			'attributes'            => array(), // product attribute mappings
			'default'               => array(), // default values (patterns) if value type set to pattern
			'suffix'                => array(), // suffixes
			'output_type'           => array(), // output type (output filter)
			'limit'                 => array(), // limit or command
			// filters tab
			'composite_price'       => 'all_product_price',
			'product_ids'           => '',
			'categories'            => array(),
			'post_status'           => array( 'publish' ),
			'filter_mode'           => array(),
			'campaign_parameters'   => array(),
			'is_outOfStock'         => 'n',
			'is_backorder'          => 'n',
			'is_emptyDescription'   => 'n',
			'is_emptyImage'         => 'n',
			'is_emptyPrice'         => 'n',
			'product_visibility'    => 0,
			// include hidden ? 1 yes 0 no
			'outofstock_visibility' => 0,
			// override wc global option for out-of-stock product hidden from catalog? 1 yes 0 no
			'ptitle_show'           => '',
			'decimal_separator'     => wc_get_price_decimal_separator(),
			'thousand_separator'    => wc_get_price_thousand_separator(),
			'decimals'              => wc_get_price_decimals(),
		);
		$rules    = wp_parse_args( $rules, $defaults );

		return apply_filters( 'woo_feed_pro_default_feed_rules', $rules );
	}


	/**
	 * @param $item
	 * @param $request
	 *
	 * @return void|\WP_Error|\WP_REST_Response
	 */
	public static function prepare_item_for_response( $item ) {

		if ( isset( $item['option_value'] ) ) {
			$item['option_value'] = maybe_unserialize( maybe_unserialize( $item['option_value'] ) );

			return $item;
		} else {
			$item['option_value'] = maybe_unserialize( get_option( $item['option_name'] ) );
		}

		if ( ! isset( $item['option_value']['url'] ) ) {
			$item['option_value']['url'] = Helper::get_file_url( $item['option_name'], $item['option_value']['feedrules']['provider'], $item['option_value']['feedrules']['feedType'] );
		}

		if ( ! isset( $item['option_value']['status'] ) ) {
			$item['option_value']['status'] = false;
		}

		return $item;
	}

	/**
	 * @param $feed_lists
	 * @param $status
	 *
	 * @return array
	 */
	public static function prepare_all_feeds( $feed_lists, $status ) {
		$lists = [];

		foreach ( $feed_lists as $feed ) {
			$item = self::prepare_item_for_response( $feed );
			if ( $status ) {
				if ( is_object( $item['option_value'] ) ) {
					$lists[] = $item;
					continue;
				}
				if ( 'active' === $status && 1 === $item['option_value']['status'] ) {
					$lists[] = $item;
				}
				if ( 'inactive' === $status && 0 === $item['option_value']['status'] ) {
					$lists[] = $item;
				}
			} else {
				$lists[] = $item;
			}
		}

		return $lists;
	}

	/**
	 * Remove Feed Option Name Prefix and return the slug
	 *
	 * @param string $feed
	 *
	 * @return string
	 */
	public static function get_feed_option_name( $feed ) {
		return str_replace( [ 'wf_feed_', 'wf_config' ], '', $feed );
	}


	/**
	 * Get Schedule Intervals
	 * @return mixed
	 */
	public static function get_schedule_interval_options() {
		if ( Helper::is_pro() ) {
			$interval_options = array(
				WEEK_IN_SECONDS        => esc_html__( '1 Week', 'woo-feed' ),
				DAY_IN_SECONDS         => esc_html__( '24 Hours', 'woo-feed' ),
				12 * HOUR_IN_SECONDS   => esc_html__( '12 Hours', 'woo-feed' ),
				6 * HOUR_IN_SECONDS    => esc_html__( '6 Hours', 'woo-feed' ),
				HOUR_IN_SECONDS        => esc_html__( '1 Hours', 'woo-feed' ),
				30 * MINUTE_IN_SECONDS => esc_html__( '30 Minutes', 'woo-feed' ),
				15 * MINUTE_IN_SECONDS => esc_html__( '15 Minutes', 'woo-feed' ),
				5 * MINUTE_IN_SECONDS  => esc_html__( '5 Minutes', 'woo-feed' )
			);
		} else {
			$interval_options = array(
				WEEK_IN_SECONDS      => esc_html__( '1 Week', 'woo-feed' ),
				DAY_IN_SECONDS       => esc_html__( '24 Hours', 'woo-feed' ),
				12 * HOUR_IN_SECONDS => esc_html__( '12 Hours', 'woo-feed' ),
				6 * HOUR_IN_SECONDS  => esc_html__( '6 Hours', 'woo-feed' ),
				HOUR_IN_SECONDS      => esc_html__( '1 Hours', 'woo-feed' ),
			);
		}

		return apply_filters(
			'woo_feed_schedule_interval_options', $interval_options
		);
	}

	/**
	 * @return false|float|int|string
	 */
	public static function get_minimum_interval_option() {
		$intervals = array_keys( self::get_schedule_interval_options() );
		if ( ! empty( $intervals ) ) {
			return end( $intervals );
		}

		return 15 * MINUTE_IN_SECONDS;
	}

	/**
	 * Get Merchant list that are allowed on Custom2 Template
	 * @return array
	 */
	public static function get_custom2_merchant() {
		return array( 'custom2', 'admarkt', 'yandex_xml', 'glami' );
	}

	/**
	 * Get Feed File URL
	 *
	 * @param string $fileName
	 * @param string $provider
	 * @param string $type
	 *
	 * @return string
	 */
	public static function get_file_url( $fileName, $provider, $type ) {
		$fileName   = Helper::extract_feed_option_name( $fileName );
		$upload_dir = wp_get_upload_dir();

		return esc_url(
			sprintf(
				'%s/woo-feed/%s/%s/%s.%s',
				$upload_dir['baseurl'],
				$provider,
				$type,
				$fileName,
				$type
			)
		);
	}

	/**
	 * Remove temporary feed files
	 *
	 * @param array $config Feed config
	 * @param string $fileName feed file name.
	 *
	 * @return void
	 */
	public static function unlink_tempFiles( $config, $fileName ) {
		$type = $config['feedType'];
		$ext  = $type;
		$path = Helper::get_file_dir( $config['provider'], $type );

		if ( 'csv' === $type || 'tsv' === $type || 'xls' === $type || 'xlsx' === $type ) {
			$ext = 'json';
		}
		$files = array(
			'headerFile' => $path . '/' . 'wf_store_feed_header_info_' . $fileName . '.' . $ext,
			'bodyFile'   => $path . '/' . 'wf_store_feed_body_info_' . $fileName . '.' . $ext,
			'footerFile' => $path . '/' . 'wf_store_feed_footer_info_' . $fileName . '.' . $ext,
		);

		//TODO: Replace this helper function with V5 Utility\Logs functionality.
		woo_feed_log_feed_process( $config['filename'], sprintf( 'Deleting Temporary Files (%s).', implode( ', ', array_values( $files ) ) ) );
		foreach ( $files as $key => $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file ); // phpcs:ignore
			}
		}
	}

	/**
	 * Save Feed Batch Chunk
	 *
	 * @param string $feedService merchant.
	 * @param string $type file type (ext).
	 * @param string|array $string data.
	 * @param string $fileName file name.
	 * @param array $info feed config.
	 *
	 * @return bool
	 */
	public static function save_batch_feed_info( $feedService, $type, $string, $fileName, $info ) {
		$ext = $type;
		if ( 'csv' === $type || 'tsv' === $type || 'xls' === $type || 'xlsx' === $type || 'json' === $type ) {
			$string = wp_json_encode( $string );
			$ext    = 'json';
		}
		// Save File.
		$path   = Helper::get_file_dir( $feedService, $type );
		$file   = $path . '/' . $fileName . '.' . $ext;
		$status = FileSystem::saveFile( $path, $file, $string );
		if ( Helper::is_debugging_enabled() ) {
			if ( $status ) {
				$message = sprintf( 'Batch chunk file (%s) saved.', $fileName );
			} else {
				$message = sprintf( 'Unable to save batch chunk file %s.', $fileName );
			}
			woo_feed_log_feed_process( $info['filename'], $message );
		}

		return $status;
	}

	/**
	 * @param string $feedService
	 * @param string $type
	 * @param string $fileName
	 *
	 * @return bool|array|string
	 */
	public static function get_batch_feed_info( $feedService, $type, $fileName ) {
		$ext = $type;
		if ( 'csv' === $type || 'tsv' === $type || 'xls' === $type || 'xlsx' === $type || 'json' === $type ) {
			$ext = 'json';
		}
		// Save File
		$path = Helper::get_file_dir( $feedService, $type );
		$file = $path . '/' . $fileName . '.' . $ext;
		if ( ! file_exists( $file ) ) {
			return false;
		}
		$data = '';
		$data = file_get_contents( $file ); // phpcs:ignore

		if ( 'csv' === $type || 'tsv' === $type || 'xls' === $type || 'xlsx' === $type || 'json' === $type ) {
			$data = ( $data ) ? json_decode( $data, true ) : false;
		}

		return $data;
	}

	/**
	 * Get file extension type
	 *
	 * @param $type
	 *
	 * @return mixed|string
	 */
	public static function get_file_type( $type ) {
		if ( 'csv' === $type || 'tsv' === $type || 'xls' === $type || 'xlsx' === $type || 'json' === $type ) {
			return 'json';
		}

		return $type;
	}

	/**
	 * Should decode content
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public static function should_json_decode( $type ) {
		if ( 'csv' === $type || 'tsv' === $type || 'xls' === $type || 'xlsx' === $type || 'json' === $type ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $file_ext_type
	 *
	 * @return bool
	 */
	public static function should_create_footer( $file_ext_type ) {
		return 'xml' == $file_ext_type;
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	public static function is_attribute_price_type( $value ) {
		return in_array( $value, [
			'price',
			'current_price',
			'sale_price',
			'price_with_tax',
			'current_price_with_tax',
			'sale_price_with_tax'
		] );
	}

	/**
	 * @return string[]
	 */
	public static function get_special_templates() {
		return array(
			'custom2',
			'admarkt',
			'glami',
			'yandex_xml',
		);
	}

	/**
	 * @param $feed_info
	 *
	 * @return array
	 */
	public static function get_product_ids( $feed_info ) {

		$config = new Config( $feed_info );

		do_action( 'before_woo_feed_get_product_information', $feed_info['option_value']['feedrules'] );

		$ids = QueryFactory::get_ids( $config );

		do_action( 'after_woo_feed_get_product_information', $feed_info['option_value']['feedrules'] );

		return $ids;
	}

	/**
	 * @param $feed_info
	 * @param $product_ids
	 * @param $offset
	 * @param $status
	 *
	 * @return bool|mixed
	 */
	public static function generate_temp_feed_body( $feed_info, $product_ids, $offset, $status = false ) {

		$feedrules = $feed_info['option_value']['feedrules'];
		$feedName  = Helper::extract_feed_option_name( $feed_info['option_name'] );
		do_action( 'before_woo_feed_generate_batch_data', $feedrules );
		if ( ! empty( $feedrules['provider'] ) ) {

			$config        = new Config( $feed_info );
			$provider      = $config->get_feed_template();
			$file_ext_type = $config->get_feed_file_type();
			$feed_template = TemplateFactory::MakeFeed( $product_ids, $config );
			//Generate Header footer
			// TODO: call this function only when offset is 0. But when creating the new feed 0 is calling 2 times. and not generating the header footer.
			self::generate_header_footer( $feed_template, $file_ext_type, $feedName, $feedrules, $provider, $offset );


			$current_feed = $feed_template->get_feed();

			woo_feed_log_feed_process( $feedrules['filename'], sprintf( 'Initializing merchant Class %s for %s', $provider, $provider ) );
			if ( ! empty( $current_feed ) ) {
				// Get previous feed body data from temporary file to concat with current data.
				$temp_feed_body_name = AttributeValueByType::FEED_TEMP_BODY_PREFIX . $feedName;
				$previous_feed       = self::get_batch_feed_info( $provider, $file_ext_type, $temp_feed_body_name );
				// Has previous feed body.
				if ( $previous_feed ) {
					/**
					 * If file extension type is csv, tsv, xls, json, xlsx then
					 * merge previous array with current array
					 *
					 * Else concat previous feed body with current feed body
					 */
					if ( 'csv' === $file_ext_type || 'tsv' === $file_ext_type || 'xls' === $file_ext_type || 'json' === $file_ext_type || 'xlsx' === $file_ext_type ) {
						if ( is_array( $previous_feed ) ) {
							$newFeed = array_merge( $previous_feed, $current_feed );
							self::save_batch_feed_info( $provider, $file_ext_type, $newFeed, $temp_feed_body_name, $feedrules );
						} else {
							$newFeed = $previous_feed . $current_feed;
							self::save_batch_feed_info( $provider, $file_ext_type, $newFeed, $temp_feed_body_name, $feedrules );
						}
					} else {
						$newFeed = $previous_feed . $current_feed;
						self::save_batch_feed_info( $provider, $file_ext_type, $newFeed, $temp_feed_body_name, $feedrules );
					}
				} else {
					self::save_batch_feed_info( $provider, $file_ext_type, $current_feed, $temp_feed_body_name, $feedrules );
				}
				$status = true;
			} else {
				$status = false;
			}
		}
		do_action( 'after_woo_feed_generate_batch_data', $feedrules );

		return $status;
	}

	/**
	 * @param $feed_info
	 * @param $should_update_last_update_time
	 *
	 * @return array
	 */
	public static function save_feed_file( $feed_info, $should_update_last_update_time = false ) {
		$option_name_orginal = $feed_info['option_name'];
		$provider            = $feed_info['option_value']['feedrules']['provider'];
		$feed_type_ext       = $feed_info['option_value']['feedrules']['feedType'];

		$path        = Helper::get_file_dir( $provider, $feed_type_ext );
		$option_name = Helper::extract_feed_option_name( $option_name_orginal );
		$feed_url    = Helper::get_file_url( $option_name, $provider, $feed_type_ext );

		$contents = '';
		$sections = [ 'header', 'body', 'footer' ];
		// Remove the footer if feed type is csv
		if ( ! self::should_create_footer( $feed_type_ext ) ) {
			$sections = array_filter( $sections, function ( $section ) {
				return 'footer' != $section;
			} );
		}

		$temp_file_name = '';
		foreach ( $sections as $section ) {
			$temp_file_ext = self::get_file_type( $feed_type_ext );
//			$temp_file_name = AttributeValueByType::FEED_TEMP_BODY_PREFIX . $option_name . '.' . $temp_file_ext;
			if ( 'header' === $section ) {
				$temp_file_name = AttributeValueByType::FEED_TEMP_HEADER_PREFIX . $option_name . '.' . $temp_file_ext;
			} elseif ( 'footer' === $section ) {
				$temp_file_name = AttributeValueByType::FEED_TEMP_FOOTER_PREFIX . $option_name . '.' . $temp_file_ext;
			} else {
				$temp_file_name = AttributeValueByType::FEED_TEMP_BODY_PREFIX . $option_name . '.' . $temp_file_ext;
			}


			$temp_content = FileSystem::ReadFile( $path, $temp_file_name );


			// If there is a problem regarding file system or other.
			if ( is_wp_error( $temp_content ) ) {
				$status = new WP_Error(
					$temp_content->get_error_code(),
					$temp_content->get_error_message(),
					[ 'status' => 404 ]
				);

				return [
					'status'   => $status,
					'feed_url' => $feed_url,
				];
			}

			if ( self::should_json_decode( $feed_type_ext ) ) {
				$temp_content = json_decode( $temp_content, true );
				if ( is_array( $temp_content ) || 'json' === $feed_type_ext ) { // json, csv fil
					$temp_contents = $contents ? $contents : [];
					$temp_content  = $temp_content ? $temp_content : [];
					$contents      = array_merge( $temp_contents, $temp_content );
				} else {
					$contents .= $temp_content;
				}
			} else {
				$contents .= $temp_content;
			}
		}

		$file_name = $option_name . '.' . $feed_type_ext;


		if ( is_array( $contents ) ) { // file type json, csv
			$contents = wp_json_encode( $contents );
			$status   = FileSystem::WriteFile( $contents, $path, $file_name );
		} else {
			$status = FileSystem::WriteFile( $contents, $path, $file_name );
		}

		// Remove temporary files.
		self::unlink_tempFiles( $feed_info['option_value']['feedrules'], $option_name );


		if ( ! isset( $feed_info['option_value']['url'] ) ) {
			$feed_info['option_value']['url'] = Helper::get_file_url( $feed_info['option_name'], $feed_info['option_value']['feedrules']['provider'], $feed_info['option_value']['feedrules']['feedType'] );
		}

		if ( ! isset( $feed_info['option_value']['status'] ) ) {
			$feed_info['option_value']['status'] = false;
		}

		if ( $should_update_last_update_time ) {
			$feed_info['option_value']['last_updated'] = date( 'Y-m-d H:i:s', strtotime( current_time( 'mysql' ) ) );
			update_option( $option_name_orginal, $feed_info['option_value'] );
		}

		return [
			'status'   => $status,
			'feed_url' => $feed_url,
		];

	}

	/**
	 * @param $feedrules
	 * @param $offset
	 * @param $product_ids
	 *
	 * @return void
	 */
	public static function log_data( $feedrules, $offset, $product_ids ) {
		woo_feed_log_feed_process( $feedrules['filename'], sprintf( 'Processing Loop %d.', ( $offset + 1 ) ) );
		$m = 'Processing Product Following Product (IDs) : ' . PHP_EOL;
		foreach ( array_chunk( $product_ids, 10 ) as $ids ) { // pretty print log [B-)=
			$m .= implode( ', ', $ids ) . PHP_EOL;
		}

		woo_feed_log_feed_process( $feedrules['filename'], $m );
	}


	/**
	 * @param $feed_template
	 * @param $file_ext_type
	 * @param $feedName
	 * @param $offset
	 * @param $feedrules
	 * @param $provider
	 *
	 * @return void
	 */
	public static function generate_header_footer( $feed_template, $file_ext_type, $feedName, $feedrules, $provider, $offset ) {
		$feed_header = $feed_template->get_header();
		$feed_footer = $feed_template->get_footer();

		$temp_feed_header_name = AttributeValueByType::FEED_TEMP_HEADER_PREFIX . $feedName;
		$temp_feed_footer_name = AttributeValueByType::FEED_TEMP_FOOTER_PREFIX . $feedName;
		// TODO: should generate footer when template type is csv ?
		self::save_batch_feed_info( $provider, $file_ext_type, $feed_header, $temp_feed_header_name, $feedrules );
		// create footer for xml file .
		if ( self::should_create_footer( $file_ext_type ) ) {
			self::save_batch_feed_info( $provider, $file_ext_type, $feed_footer, $temp_feed_footer_name, $feedrules );
		}


	}

	/**
	 * This function used during cron job. used in cron-header.php file
	 *
	 * @param $feed_info
	 * @param $offset
	 * @param $should_update_last_update_time
	 *
	 * @return bool
	 */
	public static function generate_feed( $feed_info, $offset = 0, $should_update_last_update_time = true ) {
		$ids    = self::get_product_ids( $feed_info );
		$status = self::generate_temp_feed_body( $feed_info, $ids, $offset ) ? true : false;

		self::save_feed_file( $feed_info, $should_update_last_update_time );

		return $status;
	}

	/**  Separate XML header footer and body from feed content
	 *
	 * @param string $string
	 * @param string $start
	 * @param string $end
	 *
	 * @return string
	 */
	public static function get_string_between( $string, $start, $end ) {
		$string = ' ' . $string;
		$ini    = strpos( $string, $start );
		if ( 0 == $ini ) {
			return '';
		}
		$ini += strlen( $start );
		$len = strpos( $string, $end, $ini ) - $ini;

		return substr( $string, $ini, $len );
	}

	/**
	 * Validate old feeds during cron job.
	 *
	 * @param $feed_info
	 *
	 * @return mixed
	 */
	public static function validate_feed( $feed_info ) {
		$tempMakeFeed = $feed_info;
		// Modify old feed data
		$tempFeedRules      = $tempMakeFeed['option_value']['feedrules'];
		$shouldModifyValue  = [ 'enable', 'disable', 'on', 'off', 'y', 'n', 0, 1, '0', '1' ];
		$shouldModifyFilter = array(
			'is_outOfStock'         => false,
			'is_backorder'          => false,
			'is_emptyDescription'   => false,
			'is_emptyImage'         => false,
			'is_emptyPrice'         => false,
			'product_visibility'    => false,
			'outofstock_visibility' => false,
		);
		$shouldModifyFilter = array_keys( $shouldModifyFilter );
		foreach ( $tempFeedRules as $key => $value ) {
			if ( in_array( $key, $shouldModifyFilter ) ) {
				$tempFeedRules[ $key ] = self::getToggleValue( $tempFeedRules[ $key ] );
			}

			/**
			 * Some previous feed is saving an error as value of product_ids.
			 * this value should be an array, it's default value is an array
			 */
			if ( in_array( $key, [
					'product_ids',
					'post_status',
					'categories'
				] ) && ! is_array( $tempFeedRules[ $key ] ) ) {
				if ( $tempFeedRules[ $key ] ) {
					if ( 'product_ids' === $key ) { // if key is product_ids then remove the extra space from ids.
						$tempData              = explode( ',', $tempFeedRules[ $key ] );
						$tempData              = array_map( 'absint', $tempData );
						$tempFeedRules[ $key ] = $tempData;
					} else {
						$tempFeedRules[ $key ] = explode( ',', $tempFeedRules[ $key ] );
					}
				} else {
					$tempFeedRules[ $key ] = [];
				}
			}
		}

		$tempMakeFeed['option_value']['feedrules'] = $tempFeedRules;

		return $tempMakeFeed;
	}

	/**
	 * @param $val
	 *
	 * @return bool
	 */
	public static function getToggleValue( $val ) {
		if ( $val === 'disable' || $val === 'off' || $val === 'no' || $val === false || $val === "" || $val === 0 || $val === 'n' || $val === '0' ) {
			return false;
		}

		return true;
	}

}
