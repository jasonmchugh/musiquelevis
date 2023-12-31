<?php

defined( 'ABSPATH' ) || exit;

class Smart_Manager_Install {

	/**
	 * DB updates and callbacks that need to be run per version.
	 *
	 * @var array
	 */
	private static $db_updates = array(
		'5.0.0' => array(
			'create_table_for_custom_views',
			'create_dummy_views',
			'update_500_model_transients'
		),
		'5.0.1' => array(
			'update_500_model_transients'
		),
		'5.16.0' => array(
			'update_516_alter_table'
		),
		'8.0.0' => array(
			'create_tables_for_tasks'
		),
		'8.9.0' => array(
			'update_890_port_settings'
		),
		'8.18.0' => array(
			'update_8180_create_tasks_tables_for_lite_version'
		)
	);

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		if( !defined('DOING_AJAX') || true !== DOING_AJAX ){
			add_action( 'init', array( __CLASS__, 'maybe_update_db_version' ) );
		}
	}

    /**
	 * Install SM.
	 */
	public static function install() {
        if ( ! is_blog_installed() ) {
			return;
		}

		// Check if we are not already running this routine.
		if ( 'yes' === get_transient( 'sa_sm_installing' ) ) {
			return;
        }
        
        // If we made it till here nothing is running yet, lets set the transient now.
        set_transient( 'sa_sm_installing', 'yes', MINUTE_IN_SECONDS * 10 );
		self::create_tables();
		self::maybe_update_db_version();
		delete_transient( 'sa_sm_installing' );

		// Redirect to welcome screen
		if ( ! is_network_admin() && ! isset( $_GET['activate-multi'] ) ) {
			set_transient( '_sm_activation_redirect', 1, 30 );
		}
	}

	/**
	 * Actions on deactivate plugin.
	 */
	public static function deactivate() {
		global $wpdb;

		$table_name = $wpdb->prefix.'sm_advanced_search_temp';
		if ( $table_name === $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) ) {
			$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
		}
		
		$wpdb->query( "DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE '_transient_sa_sm_%' OR option_name LIKE '_transient_timeout_sa_sm_%'"); //for deleting post type transients
	}

	/**
	 * Get list of DB update callbacks.
	 *
	 * @return array
	 */
	public static function get_db_update_callbacks() {
		return self::$db_updates;
	}
	
	/**
	 * Is a DB update needed?
	 *
	 * @return boolean
	 */
	public static function needs_db_update() {
		$current_db_version = get_option( 'sa_sm_db_version', null );
		$updates            = self::get_db_update_callbacks();
		$update_versions    = array_keys( $updates );
		usort( $update_versions, 'version_compare' );
		return ( ( ! is_null( $current_db_version ) && version_compare( $current_db_version, end( $update_versions ), '<' ) ) || is_null( $current_db_version ) );
	}

	/**
	 * See if we need to show or run database updates during install.
	 *
	 */
	static function maybe_update_db_version() {
		if ( self::needs_db_update() ) {
			self::update();
		}
	}

	/**
	 * Update DB version to current.
	 *
	 * @param string|null $version New Smart Manager DB version or null.
	 */
	public static function update_db_version( $version = null ) {
		if( ! empty( $version ) ) {
			update_option( 'sa_sm_db_version', $version, 'no' );
		}
	}

	/**
	 * Process all DB updates.
	 */
	private static function update() {

		// Check if we are not already running this routine.
		if ( 'yes' === get_transient( 'sa_sm_updating' ) ) {
			return;
		}
		
		// If we made it till here nothing is running yet, lets set the transient now.
        set_transient( 'sa_sm_updating', 'yes', MINUTE_IN_SECONDS * 10 );

		$current_db_version = get_option( 'sa_sm_db_version' );
		
		foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					if ( is_callable( array( __CLASS__, $update_callback ) ) ) {
						call_user_func( array( __CLASS__, $update_callback ) );
					}
				}
				self::update_db_version($version);
			}
		}

		delete_transient( 'sa_sm_updating' );

	}

	public static function update_500_model_transients(){
		global $wpdb;

		$wpdb->query(
					$wpdb->prepare(
							"UPDATE {$wpdb->prefix}options
							SET option_name = REPLACE(option_name, %s, %s)
							WHERE option_name LIKE %s",	
							'_transient_sm_beta_',
							'_transient_sa_sm_',
							$wpdb->esc_like('_transient_sm_beta_') . '%'
						)
					);

		$wpdb->query(
					$wpdb->prepare(
							"UPDATE {$wpdb->prefix}options
							SET option_name = REPLACE(option_name, %s, %s)
							WHERE option_name LIKE %s",	
							'_transient_timeout_sm_beta_',
							'_transient_timeout_sa_sm_',
							$wpdb->esc_like('_transient_timeout_sm_beta_') . '%'
						)
					);
	}
	
	public static function create_dummy_views() {
		global $wpdb;

		if( !( defined('SMPRO') && true === SMPRO ) ) {
			return;
		}

		$email = get_option( 'admin_email' );
		if ( ! empty( $email ) ) {
			$user = get_user_by( 'email', $email );
			if ( ! empty( $user ) ) {
				$userId = $user->ID;

				$data = array(
							array(
								'title'		=> 'Product Stock',
								'slug'		=> 'product-stock',
								'params'	=> '{"columns":{"posts_id":{"width":100,"position":1},"postmeta_meta_key__thumbnail_id_meta_value__thumbnail_id":{"width":211.787109375,"position":2},"posts_post_title":{"width":200,"position":3},"postmeta_meta_key__sku_meta_value__sku":{"width":100,"position":4},"postmeta_meta_key__manage_stock_meta_value__manage_stock":{"width":191.77734375,"position":5},"postmeta_meta_key__stock_status_meta_value__stock_status":{"width":168.4130859375,"position":6},"postmeta_meta_key__stock_meta_value__stock":{"width":100,"position":7},"postmeta_meta_key__backorders_meta_value__backorders":{"width":151.728515625,"position":8}},"sort_params":{"orderby":"ID","order":"DESC","default":true},"treegrid":"true","search_params":{"isAdvanceSearch":"false","params":""}}'
							),
							array(
								'title'		=> 'Product Price List',
								'slug'		=> 'product-price-list',
								'params'	=> '{"columns":{"posts_id":{"width":100,"position":1},"postmeta_meta_key__thumbnail_id_meta_value__thumbnail_id":{"width":211.787109375,"position":2},"posts_post_title":{"width":200,"position":3},"postmeta_meta_key__sku_meta_value__sku":{"width":100,"position":4},"postmeta_meta_key__regular_price_meta_value__regular_price":{"width":181.7431640625,"position":5},"postmeta_meta_key__sale_price_meta_value__sale_price":{"width":136.728515625,"position":6},"postmeta_meta_key__sale_price_dates_from_meta_value__sale_price_dates_from":{"width":250,"position":7},"postmeta_meta_key__sale_price_dates_to_meta_value__sale_price_dates_to":{"width":250,"position":8}},"sort_params":{"orderby":"ID","order":"DESC","default":true},"treegrid":"true","search_params":{"isAdvanceSearch":"false","params":""}}'
							)
				);

				foreach( $data as $view ){
					$wpdb->query(
							$wpdb->prepare(
											"INSERT INTO {$wpdb->prefix}sm_views(author, title, slug, params, is_public, post_type, created_date, modified_date)
											VALUES(%d, %s, %s, %s, %d, %s, %d, %d)",
											$userId,
											$view['title'],
											$view['slug'],
											$view['params'],
											1,
											'product',
											time(),
											time()
							)
					);
				}
			}
		}
	}

	public static function create_table_for_custom_views() {
		global $wpdb;

		if( !( defined('SMPRO') && true === SMPRO ) ) {
			return array();
		}

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$tables = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sm_views` (
					`id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
					`author` bigint UNSIGNED NOT NULL,
					`title` varchar(255) NOT NULL,
					`slug` varchar(255) NOT NULL,
					`params` longtext NOT NULL,
					`is_public` bit(1) NOT NULL DEFAULT b'0',
					`post_type` varchar(20) NOT NULL,
					`created_date` int UNSIGNED NOT NULL,
					`modified_date` int UNSIGNED NOT NULL,
					PRIMARY KEY (`id`)
					) $collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		return dbDelta( $tables );
	}

	public static function create_tables(){
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$tables = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sm_advanced_search_temp` (
						`product_id` bigint(20) unsigned NOT NULL UNIQUE default '0',
						`flag` bigint(20) unsigned NOT NULL default '0',
						`cat_flag` bigint(20) unsigned NOT NULL default '0',
						PRIMARY KEY (`product_id`)
						) $collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $tables );

		// Added code in v5.5.0 as in some cases the sm_views table was not created
		if ( is_callable( array( __CLASS__, 'create_table_for_custom_views' ) ) ) {
			call_user_func( array( __CLASS__, 'create_table_for_custom_views' ) );
		}
		if ( is_callable( array( __CLASS__, 'create_tables_for_tasks' ) ) ) {
			call_user_func( array( __CLASS__, 'create_tables_for_tasks' ) );
		}
	}

	public static function update_516_alter_table(){
		global $wpdb;
		
		$table_name = $wpdb->prefix.'sm_advanced_search_temp';
		if( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) ) {
			if ( ! $wpdb->get_var( "SHOW KEYS FROM $table_name WHERE Key_name = 'PRIMARY' AND Column_name = 'product_id'" ) ) {
				$wpdb->query( "ALTER TABLE $table_name ADD PRIMARY KEY(`product_id`), ADD UNIQUE KEY(`product_id`)" );
			}
		}
	}

	public static function create_tables_for_tasks() {
		global $wpdb;
		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$task_table = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sm_tasks` (
								`id` bigint NOT NULL AUTO_INCREMENT COMMENT 'id of the task',
								`title` text NOT NULL COMMENT 'field title',
								`date` datetime NOT NULL COMMENT 'task creation date',
								`completed_date` datetime NULL COMMENT 'task completion date',
								`post_type` text NOT NULL COMMENT 'field post type',
								`author` int NOT NULL DEFAULT '0' COMMENT 'id of the user who created the task',
								`type` enum('inline','bulk_edit') NOT NULL COMMENT 'edit functionality type',
								`status` enum('in-progress','completed','scheduled') NOT NULL COMMENT 'field updated status',
								`actions` longtext NOT NULL COMMENT 'serialized string of all actions executed in this task',
								`record_count` bigint NOT NULL COMMENT 'count of records updated in this task',
								PRIMARY KEY (`id`)
							) $collate;";
		dbDelta( $task_table );					

		$task_details_table = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sm_task_details` (
								`id` bigint NOT NULL AUTO_INCREMENT COMMENT 'task detail id',
								`task_id` bigint NOT NULL COMMENT 'Foreign key (id) from sm_tasks table',
								`record_id` bigint NOT NULL COMMENT 'id of the record being updated',
								`status` enum('in-progress','completed','scheduled') NOT NULL COMMENT 'field updated status',
								`field` text NOT NULL COMMENT 'name of the field/column being updated',
								`action` varchar(255) NOT NULL COMMENT 'action executed on the field',
								`prev_val` longtext NOT NULL COMMENT 'field value before updates',
								`updated_val` longtext NOT NULL COMMENT 'field value after updates',
								PRIMARY KEY (`id`)
							) $collate;";

		dbDelta( $task_details_table );
	}

	// Function to port settings
	public static function update_890_port_settings(){
		if ( ! class_exists( 'Smart_Manager_Settings' ) ) {
			return;
		}
		if( empty( Smart_Manager_Settings::$db_option_key ) ){
			return;
		}

		$settings = array(
			'general' => array(
				'toggle' => array(
					'wp_force_collapse_admin_menu'                  => get_option( 'sm_wp_force_collapse_admin_menu', 'yes' ),
					'use_number_field_for_numeric_cols'             => get_option( 'sm_use_number_field_for_numeric_cols', 'yes' ),
					'view_trash_records'                            => get_option( 'sm_view_trash_records', 'no' ),
					'show_manage_with_smart_manager_button'         => get_option( 'sm_show_manage_with_sm_button', 'yes' ),
					'show_smart_manager_menu_in_admin_bar'          => get_option( 'sm_show_smart_manager_menu_in_admin_bar', 'yes' )
				),
				'numeric' => array(
					'per_page_record_limit' => get_option( '_sm_beta_set_record_limit', 50 )
				),
				'text'  => array(
					'grid_row_height' => get_option( 'sm_grid_row_height', '50px' )
				)
			)
		);
		
		if( defined('SMPRO') && true === SMPRO ) {
			$settings['general']['toggle']['show_tasks_title_modal'] = get_option( 'sm_show_tasks_title_modal', 'yes' );
			
			$attachment_url = get_option( 'smart_manager_company_logo', '' );
			$attachment_id = ( ! empty( $attachment_url ) ) ? attachment_url_to_postid( $attachment_url ) : 0;
			$settings['general']['image'] = array( 'company_logo_for_print_invoice' => $attachment_id );
		}

		update_option( Smart_Manager_Settings::$db_option_key, $settings, 'no' );
	}

	public static function update_8180_create_tasks_tables_for_lite_version() {
		self::create_tables_for_tasks();
	}
}

Smart_Manager_Install::init();
