<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Smart_Manager_Pro_Shop_Subscription' ) ) {
	class Smart_Manager_Pro_Shop_Subscription extends Smart_Manager_Pro_Base {
		public $dashboard_key = '',
				$req_params = array(),
				$flat_tables = array( 'wc_orders', 'wc_order_addresses', 'wc_order_operational_data' ),
				$plugin_path = '',
				$status_color_codes = array( 'green' 	=> array( 'wc-active' ),
											'red' 		=> array( 'cancelled' ),
											'orange' 	=> array( 'wc-on-hold', 'wc-pending' ),
											'blue' 		=> array( 'wc-switched', 'wc-pending-cancel' ) );

		protected static $_instance = null;

		public static function instance($dashboard_key) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self($dashboard_key);
			}
			return self::$_instance;
		}

		function __construct($dashboard_key) {

			if ( file_exists(SM_PLUGIN_DIR_PATH . '/classes/class-smart-manager-shop-order.php') ) {
				include_once SM_PLUGIN_DIR_PATH . '/classes/class-smart-manager-shop-order.php';
			}

			if ( file_exists(SM_PLUGIN_DIR_PATH . '/pro/classes/class-smart-manager-pro-shop-order.php') ) {
				include_once SM_PLUGIN_DIR_PATH . '/pro/classes/class-smart-manager-pro-shop-order.php';
			}

			// Hooks for WC v7.9 (HPOS) compat
			if ( ! empty( Smart_Manager::$sm_is_woo79 ) ) {
				add_filter( 'sm_search_table_types', array( 'Smart_Manager_Shop_Order', 'sm_order_search_table_types' ), 12, 1 ); // should be kept before calling the parent class constructor
			}

			parent::__construct($dashboard_key);
			self::actions();

			$this->dashboard_key = $dashboard_key;
			$this->post_type = $dashboard_key;
			$this->req_params  	= ( ! empty( $_REQUEST ) ) ? $_REQUEST : array();

			// Hooks for WC v7.9 (HPOS) compat
			if ( ! empty( Smart_Manager::$sm_is_woo79 ) ) {
				add_filter( 'sm_beta_load_default_store_model', function() { return false; } );
				add_filter( 'sm_default_dashboard_model', array( &$this, 'default_dashboard_model' ), 10, 1 );
				add_filter( 'sm_get_custom_cols', array( 'Smart_Manager_Shop_Order', 'get_address_cols' ), 10, 2 );
				add_filter( 'sm_meta_col_model_args', array( &$this, 'update_meta_col_model' ) );

				add_filter( 'sm_ignored_cols', array( 'Smart_Manager_Shop_Order', 'get_flat_table_ignored_cols' ) );
				add_filter( 'sm_flat_table_col_titles', array( 'Smart_Manager_Shop_Order', 'get_flat_table_col_titles' ) );

				add_filter( 'sm_beta_load_default_data_model', function() { return false; } );

				// Filters for modifying advanced search query clauses
				add_filter( 'woocommerce_orders_table_query_clauses',  array( &$this, 'modify_subscriptions_table_query_clauses' ), 99, 3 );
				add_filter( 'sm_search_query_formatted', array( 'Smart_Manager_Shop_Order', 'sm_order_addresses_search_query_formatted' ), 12, 2 );

				// Filters for 'inline_update' functionality
				add_filter( 'sm_default_inline_update', function() { return false; } );
				add_action( 'sm_inline_update_post', array( &$this, 'subscriptions_inline_update' ), 10, 2 );

				add_filter( 'sm_beta_background_entire_store_ids_query', array( $this,'get_entire_store_ids_query' ), 12, 1 );

			} else {
				add_filter( 'sm_dashboard_model',array( &$this,'subscriptions_dashboard_model' ), 10, 2 );
				add_filter( 'posts_where',array( &$this,'sm_query_sub_where_cond' ), 100, 2 );
				add_filter( 'found_posts',array( 'Smart_Manager_Shop_Order' ,'kpi_data_query'), 100, 2 );
				add_filter( 'sm_batch_update_copy_from_ids_select',array( &$this,'sm_batch_update_copy_from_ids_select' ), 10, 2 );
			}

			add_filter( 'sm_data_model', array( &$this, 'subscriptions_data_model' ), 10, 2 );

			// hooks for delete functionality
			add_filter( 'sm_default_process_delete_records', function() { return false; } );
			add_filter( 'sm_default_process_delete_records_result', array( 'Smart_Manager_Shop_Order', 'order_trash' ), 12, 2 );
		}

		public static function actions() {

			if ( file_exists(SM_PLUGIN_DIR_PATH . '/classes/class-smart-manager-shop-order.php') ) {
				include_once SM_PLUGIN_DIR_PATH . '/classes/class-smart-manager-shop-order.php';
			}

			if ( file_exists(SM_PLUGIN_DIR_PATH . '/pro/classes/class-smart-manager-pro-shop-order.php') ) {
				include_once SM_PLUGIN_DIR_PATH . '/pro/classes/class-smart-manager-pro-shop-order.php';
			}

			add_filter( 'sm_beta_batch_update_prev_value',  __CLASS__ . '::get_previous_value', 10, 2 );
			add_filter( 'sm_default_batch_update_db_updates',  'Smart_Manager_Pro_Shop_Order::default_batch_update_db_updates', 10, 2 );
			add_filter( 'sm_post_batch_update_db_updates', __CLASS__ . '::post_batch_update_db_updates', 10, 2 );
			add_filter( 'sm_pro_default_process_delete_records', function() { return false; } );
			add_filter( 'sm_pro_default_process_delete_records_result', 'Smart_Manager_Shop_Order::process_delete', 12, 3 );
		}

		public function __call( $function_name, $arguments = array() ) {

			if ( ! is_callable( array( 'Smart_Manager_Shop_Order', $function_name ) ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( array( 'Smart_Manager_Shop_Order', $function_name ), $arguments );
			} else {
				return call_user_func( array( 'Smart_Manager_Shop_Order', $function_name ) );
			}
		}

		//Function to generate the col model for dropdown datatype
		public function generate_dropdown_col_model( $colObj, $dropdownValues = array() ) {

			$dropdownKeys = ( !empty( $dropdownValues ) ) ? array_keys( $dropdownValues ) : array();
			$colObj['defaultValue'] = ( !empty( $dropdownKeys[0] ) ) ? $dropdownKeys[0] : '';
			$colObj['save_state'] = true;
			
			$colObj['values'] = $dropdownValues;
			$colObj['selectOptions'] = $dropdownValues; //for inline editing

			$colObj['search_values'] = array();
			foreach( $dropdownValues as $key => $value) {
				$colObj['search_values'][] = array('key' => $key, 'value' => $value);
			}

			$colObj['type'] = 'dropdown';
			$colObj['strict'] = true;
			$colObj['allowInvalid'] = false;
			$colObj['editor'] = 'select';
			$colObj['renderer'] = 'selectValueRenderer';

			return $colObj;
		}

		//Fucntion for overriding the select clause for fetching the ids for batch update 'copy from' functionality
		public function sm_batch_update_copy_from_ids_select( $select, $args ) {
			$select = " SELECT ID AS id, CONCAT('Subscription #', ID) AS title";
			return $select;
		}

		public function subscriptions_dashboard_model ($dashboard_model, $dashboard_model_saved) {
			global $wpdb, $current_user;

			$dashboard_model['tables']['posts']['where']['post_type'] = 'shop_subscription';

			$visible_columns = array('ID', 'post_date', 'post_status', '_billing_email', '_billing_first_name', '_billing_last_name', '_order_total', '_billing_interval', '_billing_period', '_payment_method_title', '_schedule_next_payment', '_schedule_end');

			$numeric_columns = array('_billing_phone', '_cart_discount', '_cart_discount_tax', '_customer_user', '_billing_interval');

			$string_columns = array('_billing_postcode', '_shipping_postcode');

			$post_status_col_index = sm_multidimesional_array_search('posts_post_status', 'data', $dashboard_model['columns']);
			
			if( isset( $dashboard_model['columns'][$post_status_col_index] ) && is_callable( array( 'Smart_Manager_Shop_Order', 'generate_status_col_model' ) ) ) {
				$dashboard_model['columns'][$post_status_col_index] = Smart_Manager_Shop_Order::generate_status_col_model( $dashboard_model['columns'][$post_status_col_index], 
					array( 'curr_obj' => $this, 
							'status_func' => 'wcs_get_subscription_statuses', 
							'default_status' => 'wc-pending', 
							'color_codes' => $this->status_color_codes ) );
			}

			//Code to get the custom column model
			if( is_callable( array( 'Smart_Manager_Shop_Order', 'generate_orders_custom_column_model' ) ) ) {
				$dashboard_model['columns'] = self::generate_orders_custom_column_model( $dashboard_model['columns'] );
			}

			$column_model = &$dashboard_model['columns'];

			//Code for unsetting the position for hidden columns

			foreach( $column_model as &$column ) {
				
				if (empty($column['src'])) continue;

				$src_exploded = explode("/",$column['src']);

				if (empty($src_exploded)) {
					$src = $column['src'];
				}

				if ( sizeof($src_exploded) > 2) {
					$col_table = $src_exploded[0];
					$cond = explode("=",$src_exploded[1]);

					if (sizeof($cond) == 2) {
						$src = $cond[1];
					}
				} else {
					$src = $src_exploded[1];
					$col_table = $src_exploded[0];
				}


				if( empty($dashboard_model_saved) ) {
					if (!empty($column['position'])) {
						unset($column['position']);
					}

					$position = array_search($src, $visible_columns);

					if ($position !== false) {
						$column['position'] = $position + 1;
						$column['hidden'] = false;
					} else {
						$column['hidden'] = true;
					}
				}

				if ($src == 'post_date') {
					$column ['name'] = $column ['key'] = __('Date', 'smart-manager-for-wp-e-commerce');
				} else if ($src == 'post_status') {
					$column ['name'] = $column ['key'] = __('Status', 'smart-manager-for-wp-e-commerce');
				} else if ($src == '_billing_interval') {
					$subscription_period_interval = ( function_exists('wcs_get_subscription_period_interval_strings') ) ? wcs_get_subscription_period_interval_strings() : array();
					$column = $this->generate_dropdown_col_model( $column, $subscription_period_interval );					
				} else if ($src == '_billing_period') {
					$subscription_period = ( function_exists('wcs_get_subscription_period_strings') ) ? wcs_get_subscription_period_strings() : array();
					$column = $this->generate_dropdown_col_model( $column, $subscription_period );				
				} else if( !empty( $numeric_columns ) && in_array( $src, $numeric_columns ) ) {
					$column ['type'] = 'numeric';
					$column ['editor'] = ( '_billing_phone' === $src ) ? 'numeric' : 'customNumericEditor';
				} else if( !empty( $string_columns ) && in_array( $src, $string_columns ) ) {
					$column ['type'] = $column ['editor'] = 'text';
				}
			}

			if (!empty( $dashboard_model_saved )) {
				$col_model_diff = sm_array_recursive_diff($dashboard_model_saved,$dashboard_model);	
			}

			//clearing the transients before return
			if (!empty($col_model_diff)) {
				delete_transient( 'sa_sm_'.$this->dashboard_key );	
			}

			return $dashboard_model;

		}

		//Function to process subscriptions search for custom columns
		public function sm_query_sub_where_cond ($where, $wp_query_obj) {

			if( is_callable( array( 'Smart_Manager_Shop_Order', 'process_custom_search' ) ) ) {
				$where = self::process_custom_search( $where, $this->req_params );
			}

			return $where;
		}

		/**
			* Function for generating default dashboard model.
			* @param  array $dashboard_model default generated dashboard model.
			* @return array Updated default dashboard model
		*/
		public function default_dashboard_model( $dashboard_model = array() ) {
			return ( is_callable( array( 'Smart_Manager_Shop_Order', 'generate_hpos_dashboard_model' ) ) ) ? 
				Smart_Manager_Shop_Order::generate_hpos_dashboard_model( $this, array( 'dashboard_model' => $dashboard_model,
																						'status_col_args' => array(
																							'status_func' => 'wcs_get_subscription_statuses', 
																							'default_status' => 'wc-pending',
																							'color_codes' => $this->status_color_codes
																						),
																						'visible_columns' => array( 
																							'wc_orders_id', 'wc_orders_date_created_gmt', 'wc_orders_status', 'wc_orders_billing_email','wc_order_addresses_billing_first_name', 'wc_order_addresses_billing_last_name', 'wc_orders_total_amount', 'wc_orders_meta_meta_key__billing_interval_meta_value__billing_interval', 'wc_orders_meta_meta_key__billing_period_meta_value__billing_period', 'wc_orders_payment_method_title', 'wc_orders_meta_meta_key__schedule_next_payment_meta_value__schedule_next_payment', 'wc_orders_meta_meta_key__schedule_end_meta_value__schedule_end' )
																					) ) : $dashboard_model;
		}

		/**
		 * Function hooked to filter for modifying data model.
		 *
		 * @param array $data_model default generated data model.
		 * @param array $data_col_params function arguments.
		 * @return array $data_model updated data model.
		 */
		public function subscriptions_data_model( $data_model = array(), $data_col_params = array() ) {
			return ( is_callable( array( 'Smart_Manager_Shop_Order', 'generate_data_model' ) ) ) ? Smart_Manager_Shop_Order::generate_data_model( $data_model, array( 'col_params' => $data_col_params, 'curr_obj' => $this, 'status_func' => 'wcs_get_subscription_statuses', 'curr_obj_getter_func' => 'wcs_get_subscription', 'curr_obj_class_nm' => 'WC_Subscription' ) ) : $data_model;
		}

		/**
		 * Function hooked to filter for modifying query clauses.
		 *
		 * @param array $clauses query clauses.
		 * @param object $args OrdersTableQuery object.
		 * @param array $args Query args.
		 * 
		 * @return array $clauses array of modified query clauses.
		 */
		public function modify_subscriptions_table_query_clauses( $clauses = array(), $query_obj = null, $args = array() ){
			return ( is_callable( array( 'Smart_Manager_Shop_Order', 'modify_table_query_clauses' ) ) ) ? Smart_Manager_Shop_Order::modify_table_query_clauses( $clauses, array( 'curr_obj' => $this, 'query_obj' => $query_obj, 'query_args' => $args ) ) : $clauses;
		}

		/**
		 * Function hooked to filter for processing inline update.
		 *
		 * @param array $edited_data array of edited rows.
		 * @param array $params function arguments.
		 * @return void.
		 */
		public function subscriptions_inline_update( $edited_data = array(), $params = array() ) {
			( is_callable( array( 'Smart_Manager_Shop_Order', 'process_inline_update' ) ) ) ? Smart_Manager_Shop_Order::process_inline_update( $edited_data, array_merge( array( 'curr_obj' => $this ), $params ) ) : '';
		}

		/**
		 * Function for modifying query for getting ids in case of 'entire store' option.
		 *
		 * @param string $query query for fetching the ids when entire store option is selected.
		 * @return string updated query for fetching the ids when entire store option is selected.
		 */
		public function get_entire_store_ids_query( $query = '' ) {
			global $wpdb;
			return $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}wc_orders WHERE status != 'trash' AND type = %s", 'shop_subscription' );
		}

		/**
		 * AJAX handler function for copy from operator for bulk edit.
		 *
		 * @param array $args bulk edit params.
		 * @return string|array json encoded string or array of values.
		 */
		public function get_batch_update_copy_from_record_ids( $args = array() ) {
			return ( is_callable( array( 'Smart_Manager_Pro_Shop_Order', 'get_copy_from_record_ids' ) ) ) ? Smart_Manager_Pro_Shop_Order::get_copy_from_record_ids( array_merge( array( 'curr_obj' => $this, 'type' => 'shop_subscription', 'field_title_prefix' => 'Subscription' ), $args ) ) : '';
		}

		/**
		 * Function to get prev_val
		 *
		 * @param string $prev_val received prev_val.
		 * @param array $args array has id, table name, column name.
		 * @return string $prev_val updated prev_val 
		 */
		public static function get_previous_value( $prev_val = '', $args = array() ) {
			return ( is_callable( array( 'Smart_Manager_Shop_Order', 'get_previous_value' ) ) ) ? Smart_Manager_Shop_Order::get_previous_value( $prev_val, array_merge( $args,  array( 'curr_obj_getter_func' => 'wcs_get_subscription', 'curr_obj_class_nm' => 'WC_Subscription' ) ) ) : $prev_val;
		}
		
		/**
		 * Function hooked to process bulk edits.
		 *
		 * @param boolean $update_flag default update flag.
		 * @param array $args function arguments.
		 * @return boolean $update_flag updated update flag.
		 */
		public static function post_batch_update_db_updates( $update_flag = false, $args = array() ) {
			return ( is_callable( array( 'Smart_Manager_Pro_Shop_Order', 'post_batch_update_db_updates' ) ) ) ? Smart_Manager_Pro_Shop_Order::post_batch_update_db_updates( $update_flag, array_merge( $args,  array( 'curr_obj_getter_func' => 'wcs_get_subscription', 'curr_obj_class_nm' => 'WC_Subscription' ) ) ) : $update_flag;
		}

		/**
			* Modifying args for meta columns.
			* @param  array $args args to get column model.
			* @return array Updated args values for meta columns
		*/
		public function update_meta_col_model( $args = array()) {
			if ( empty( $args['table_nm'] ) || ( empty( $args['col'] ) ) ) {
				return $args;
			}

			switch( $args['col'] ){
				case '_billing_interval':
					$col = ( function_exists('wcs_get_subscription_period_interval_strings') ) ? wcs_get_subscription_period_interval_strings() : array();
					if( empty( $col ) ){
						return $args;
					}
					return array_merge( $args, $this->generate_dropdown_col_model( $args, $col ) );
				case '_billing_period':
					$col = ( function_exists('wcs_get_subscription_period_strings') ) ? wcs_get_subscription_period_strings() : array();
					if( empty( $col ) ){
						return $args;
					}
					return array_merge( $args, $this->generate_dropdown_col_model( $args, $col ) );
			}

			return $args;
		}
	}
}
