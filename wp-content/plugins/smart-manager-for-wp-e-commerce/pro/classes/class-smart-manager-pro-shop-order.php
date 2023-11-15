<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Smart_Manager_Pro_Shop_Order' ) ) {
	class Smart_Manager_Pro_Shop_Order extends Smart_Manager_Pro_Base {
		public $dashboard_key = '',
				$req_params = array(),
				$plugin_path = '',
				$custom_product_search_key_prefix = 'sm_custom_product_',
				$advanced_search_option_name = 'sa_sm_search_order_product_ids',
				$custom_product_search_cols = array(),
				$custom_search_cols = array();

		public $shop_order = '';

		protected static $_instance = null;

		public static function instance($dashboard_key) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self($dashboard_key);
			}
			return self::$_instance;
		}

		function __construct($dashboard_key) {

			add_filter( 'sm_search_table_types', array( &$this, 'orders_search_table_types' ), 12, 1 ); // should be kept before calling the parent class constructor

			parent::__construct($dashboard_key);
			self::actions();

			$this->plugin_path  = untrailingslashit( plugin_dir_path( __FILE__ ) );
			$this->custom_product_search_cols = array( 
														'sku'	=> 'postmeta/_sku',
														'title'	=> 'posts/post_title'
													);

			$this->custom_search_cols = array( 
												'coupons_used'		=> 'woocommerce_order_items/coupon',
												'shipping_method'	=> 'woocommerce_order_items/shipping'
											);

			if ( file_exists(SM_PLUGIN_DIR_PATH . '/classes/class-smart-manager-shop-order.php') ) {
				include_once SM_PLUGIN_DIR_PATH . '/classes/class-smart-manager-shop-order.php';
				$this->shop_order = new Smart_Manager_Shop_Order( $dashboard_key );
			}
			
			add_filter( 'sm_dashboard_model', array( &$this, 'orders_dashboard_model' ), 12, 2 );
			add_filter( 'sm_search_query_formatted', array( &$this, 'order_itemmeta_search_query' ), 12, 2 );

			// Filters for modifying advanced search query clauses
			add_filter( 'sm_search_query_woocommerce_order_itemmeta_select', array( &$this, 'orders_advanced_search_select' ), 12, 2 );
			add_filter( 'sm_search_query_woocommerce_order_itemmeta_from', array( &$this, 'orders_advanced_search_from' ), 12, 2 );
			add_filter( 'sm_search_query_woocommerce_order_itemmeta_join', array( &$this, 'orders_advanced_search_join' ), 12, 2 );
			add_filter( 'sm_search_woocommerce_order_items_cond', array( &$this, 'orders_advanced_search_flat_table_cond' ), 12, 2 );

			add_action( 'sm_advanced_search_processing_complete', array( &$this, 'orders_advanced_search_post_processing' ), 12, 1 );

			if ( ! empty( Smart_Manager::$sm_is_woo79 ) ) {
				add_filter( 'sm_beta_background_entire_store_ids_query', array( $this,'get_entire_store_ids_query' ), 12, 1 );
			}
		}

		public static function actions() {
			add_filter( 'sm_beta_batch_update_prev_value',  'Smart_Manager_Shop_Order::get_previous_value', 10, 2 );

			add_filter( 'sm_default_batch_update_db_updates',  __CLASS__. '::default_batch_update_db_updates', 10, 2 );
			add_filter( 'sm_post_batch_update_db_updates', __CLASS__. '::post_batch_update_db_updates', 10, 2 );
			add_filter( 'sm_pro_default_process_delete_records', function() { return false; } );
			add_filter( 'sm_pro_default_process_delete_records_result', 'Smart_Manager_Shop_Order::process_delete', 12, 3 );
		}

		public function __call( $function_name, $arguments = array() ) {

			if( empty( $this->shop_order ) ) {
				return;
			}

			if ( ! is_callable( array( $this->shop_order, $function_name ) ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( array( $this->shop_order, $function_name ), $arguments );
			} else {
				return call_user_func( array( $this->shop_order, $function_name ) );
			}
		}

		public static function default_batch_update_db_updates( $flag = false, $args = array() ) {
			return ( 'posts' === $args['table_nm'] && 'post_status' === $args['col_nm'] ) ? false : $flag;
		}

		public static function post_batch_update_db_updates( $update_flag = false, $args = array() ) {
			if ( empty( $args ) || empty( $args['id'] ) ) return $update_flag;
			$args['order_obj'] = wc_get_order( $args['id'] );
			if ( ! empty( Smart_Manager::$sm_is_woo79 ) ) {
				if( ! empty( $args['curr_obj_getter_func'] ) && ! empty( $args['curr_obj_class_nm'] ) && function_exists( $args['curr_obj_getter_func'] ) && class_exists( $args['curr_obj_class_nm'] ) ) {
					$args['order_obj'] = call_user_func( $args['curr_obj_getter_func'], $args['id'] );
				}

				//Code for handling 'copy_from' and 'copy_from_field' action
				if( ! empty( $args ) && ! empty( $args['operator'] ) && $args['copy_from_operators'] && in_array( $args['operator'], $args['copy_from_operators'] ) && ! empty( $args['selected_table_name'] ) && ! empty( $args['selected_column_name'] ) && ! empty( $args['selected_value'] ) && is_callable( array( 'Smart_Manager_Shop_Order', 'get_previous_value') ) ) {
					$args['value'] = Smart_Manager_Shop_Order::get_previous_value(
							( ! empty( $args['prev_val'] ) ) ? $args['prev_val'] : '',
							array(
								'col_nm' => $args['selected_column_name'],
								'table_nm' => $args['selected_table_name'],
								'id' => intval( $args['selected_value'] ),
								'order_obj' => wc_get_order( intval( $args['selected_value'] ) )
							)
						);
				}
				return ( is_callable( array( 'Smart_Manager_Shop_Order', 'update_order_data') ) ) ? Smart_Manager_Shop_Order::update_order_data( $args ) : $update_flag; 
			}
			if( 'posts' === $args['table_nm'] && 'post_status' === $args['col_nm'] && ! empty( $args['value'] ) && class_exists( 'WC_Order' ) ){
				$order = new WC_Order( $args['id'] );
				return $order->update_status( $args['value'], '', true );
			}
			return $update_flag;
		}

		/**
		 * Function for adding custom columns for Orders dashboard
		 *
		 * @param array $dashboard_model array of dashboard model.
		 * @param array $dashboard_model_saved The saved dashboard model.
		 * @return array $dashboard_model updated dashboard model.
		 */
		public function orders_dashboard_model( $dashboard_model = array(), $dashboard_model_saved = array() ){
			if( empty( $this->custom_product_search_cols ) ){
				return $dashboard_model;
			}

			global $wpdb;

			if( empty( $dashboard_model['columns'] ) ){
				$dashboard_model['columns'] = array();
			}
			$column_model = &$dashboard_model['columns'];

			foreach( array_keys( $this->custom_product_search_cols ) as $col ){
				$col_name = $this->custom_product_search_key_prefix. '' .$col;
				$col_index = sm_multidimesional_array_search ( 'woocommerce_order_itemmeta/meta_key='. $col_name .'/meta_value='. $col_name, 'src', $column_model );

				if( ! empty( $col_index ) ) {
					continue;
				}
				$index = sizeof( $column_model );

				$column_model [$index] = array();

				$column_model [$index]['src'] = 'woocommerce_order_itemmeta/meta_key='. $col_name .'/meta_value='. $col_name;
				$column_model [$index]['data'] = sanitize_title( str_replace( '/', '_', $column_model [$index]['src'] ) ); // generate slug using the wordpress function if not given 
				$column_model [$index]['name'] = __( 'Product '. ( ( 'sku' === $col ) ? 'SKU' : ucwords( str_replace('_', ' ', $col) ) ), 'smart-manager-for-wp-e-commerce' );
				$column_model [$index]['key'] = $column_model[$index]['name'];
				$column_model [$index]['type'] = 'text';
				$column_model [$index]['hidden']	= true;
				$column_model [$index]['editable']	= false;
				$column_model [$index]['batch_editable']	= false;
				$column_model [$index]['sortable']	= false;
				$column_model [$index]['resizable']	= false;
				$column_model [$index]['allow_showhide'] = false;
				$column_model [$index]['exportable']	= false;
				$column_model [$index]['searchable']	= true;
				$column_model [$index]['wordWrap'] = false; //For disabling word-wrap
				$column_model [$index]['table_name'] = $wpdb->prefix.'woocommerce_order_itemmeta';
				$column_model [$index]['col_name'] = $col_name;
				$column_model [$index]['width'] = 0;
				$column_model [$index]['save_state'] = false;
				//Code for assigning values
				$column_model [$index]['values'] = array();
				$column_model [$index]['search_values'] = array();
			}

			if( !empty( $dashboard_model_saved ) ) {
				$col_model_diff = sm_array_recursive_diff( $dashboard_model_saved,$dashboard_model );	
			}

			//clearing the transients before return
			if ( !empty( $col_model_diff ) ) {
				delete_transient( 'sa_sm_'.$this->dashboard_key );	
			}
			return $dashboard_model;
		}

		/**
		 * Function for modifying search query for meta tables for advanced search.
		 *
		 * @param array $query The search query array.
		 * @param array $params The search condition params.
		 * @return array $query updated search query array.
		 */
		public function order_itemmeta_search_query( $query = array(), $params = array() ){
			$search_string = ( ! empty( $params['search_string'] ) ) ? $params['search_string'] : array();

			if( empty( $search_string ) || ( ! empty( $search_string ) && empty( $search_string['table_name'] ) ) ){
				return $query;
			}

			$col = ( ! empty( $params['search_col'] ) ) ? $params['search_col'] : '';
			$searched_col_table_nm = $search_string['table_name'];

			if( empty( $col ) ||  strlen( $col ) < strlen( $this->custom_product_search_key_prefix ) || ( ! empty( $col ) &&  strlen( $col ) > strlen( $this->custom_product_search_key_prefix ) && ! in_array( substr( $col, strlen( $this->custom_product_search_key_prefix ) ), array_keys( $this->custom_product_search_cols ) ) ) ){
				return $query;
			}

			global $wpdb;

			$search_meta = explode( "/", $this->custom_product_search_cols[substr( $col, strlen( $this->custom_product_search_key_prefix ) )] );
			$search_table = ( ! empty( $search_meta[0] ) ) ? $search_meta[0] : '';
			$search_col = ( ! empty( $search_meta[1] ) ) ? $search_meta[1] : '';

			if( empty( $search_table ) || empty( $search_col ) ){
				return $query;
			}

			$search_val = ( ! empty( $params['search_value'] ) ) ? $params['search_value'] : '';
			$search_op = ( ! empty( $params['search_operator'] ) ) ? $params['search_operator'] : '';
			$searched_col_op = ( strpos( $search_op, ' not' ) || strpos( $search_op, 'not ' ) ) ? 'not' : '';

			$p_ids = array();

			$rule = array(
				'type' => $wpdb->prefix. '' .$search_table. '.' .$search_col,
				'operator' => $search_op,
				'value' => $search_val,
				'table_name' => $wpdb->prefix. '' .$search_table,
				'col_name' => $search_col
			);

			$params = array(
				'table_nm'	=> $search_table,
				'search_query' => array(
					'cond_'.$search_table => '',
					'cond_'.$search_table.'_col_name' => '',
					'cond_'.$search_table.'_col_value' => '',
					'cond_'.$search_table.'_operator' => ''
				),
				'search_params' => array(
					'search_string' => $rule,
					'search_col' => $search_col,
					'search_operator' => $search_op,
					'search_data_type' => 'text',
					'search_value' => $search_val,
					'selected_search_operator' => ( ! empty( $params['selected_search_operator'] ) ) ? $params['selected_search_operator'] : '',
					'SM_IS_WOO30' => ( !empty( $params['SM_IS_WOO30'] ) ) ? $params['SM_IS_WOO30'] : '',
					'post_type' => array( 'product', 'product_variation')
				),
				'rule'			=> $rule
			);

			// code for postmeta cols
			if( 'postmeta' === $search_table ){
				$meta_query = $this->create_meta_table_search_query( $params );
				if( empty( $meta_query ) || ( ! empty( $meta_query ) && empty( $meta_query['cond_postmeta'] ) ) ){
					return $query;
				}
				$cond = ( ! empty( $meta_query['cond_postmeta'] ) ) ? substr( $meta_query['cond_postmeta'], 0, -4 ) : '';
				if( empty( $cond ) ){
					return $query;
				}
				//Query to get the post_id of the products whose meta value matches with the one type in the search text box of the Orders Module
				$p_ids  = $wpdb->get_col( "SELECT DISTINCT(post_id) FROM {$wpdb->prefix}postmeta
														WHERE 1=1 AND ". $cond ); //not using wpdb->prepare as its failing if the `cond` is having `%s`
			} else if( 'posts' === $search_table ){
				$meta_query = $this->create_flat_table_search_query( $params );
				if( empty( $meta_query ) || ( ! empty( $meta_query ) && empty( $meta_query['cond_posts'] ) ) ){
					return $query;
				}
				$cond = ( ! empty( $meta_query['cond_posts'] ) ) ? substr( $meta_query['cond_posts'], 0, -4 ) : '';
				if( empty( $cond ) ){
					return $query;
				}
				//Query to get the post_id of the products whose meta value matches with the one type in the search text box of the Orders Module
				$p_ids  = $wpdb->get_col( "SELECT DISTINCT(ID) FROM {$wpdb->prefix}posts
														WHERE 1=1 AND ". $cond ); //not using wpdb->prepare as its failing if the `cond` is having `%s`
			}


			if( is_wp_error( $p_ids ) || empty( $p_ids ) ) {
				return $query;
			}
			$ometa_cond = $searched_col_table_nm .".meta_value ". $searched_col_op ." in (". implode( ",", $p_ids ) .")";
			if( count( $p_ids ) > 100 && !empty( $this->advanced_search_option_name ) ){
				update_option( $this->advanced_search_option_name, implode( ",", $p_ids ), 'no' );
				$ometa_cond = $searched_col_op ." FIND_IN_SET( ". $searched_col_table_nm .".meta_value, (SELECT option_value FROM ". $wpdb->prefix ."options WHERE option_name = '". $this->advanced_search_option_name ."') )";
			}

			$query['cond_woocommerce_order_itemmeta'] = "( ( ". $searched_col_table_nm .".meta_key = '_product_id' AND ". $ometa_cond ." )
															OR ( ". $searched_col_table_nm .".meta_key = '_variation_id' AND ". $ometa_cond ." ) )";
			return $query;
		}

		/**
		 * Function for modifying table types for advanced search.
		 *
		 * @param array $table_types array of table types.
		 * @return array $table_types updated array of table types.
		 */
		public function orders_search_table_types( $table_types = array() ){
			$table_types['flat']['woocommerce_order_items'] =  'order_id';
			$table_types['meta']['woocommerce_order_itemmeta'] =  'order_id';
			return $table_types;
		}
		
		/**
		 * Function for modifying select clause for meta tables for advanced search.
		 *
		 * @param string $select The search query select clause.
		 * @param array $params The search condition params.
		 * @return string updated search query select clause.
		 */
		public function orders_advanced_search_select( $select = '', $params = array() ){
			return str_replace( 'woocommerce_order_itemmeta.order_id', 'woocommerce_order_items.order_id', $select );
		}

		/**
		 * Function for modifying from clause for meta tables for advanced search.
		 *
		 * @param string $from The search query from clause.
		 * @param array $params The search condition params.
		 * @return string updated search query from clause.
		 */
		public function orders_advanced_search_from( $from = '', $params = array() ){
			global $wpdb;
			return $from. '' .( ( false === strpos( $from, 'woocommerce_order_items' ) ) ? " JOIN {$wpdb->prefix}woocommerce_order_items
																					ON ({$wpdb->prefix}woocommerce_order_itemmeta.order_item_id = {$wpdb->prefix}woocommerce_order_items.order_item_id)" : '' );
		}

		/**
		 * Function for modifying join clause for meta tables for advanced search.
		 *
		 * @param string $join The search query join clause.
		 * @param array $params The search condition params.
		 * @return string updated search query join clause.
		 */
		public function orders_advanced_search_join( $join = '', $params = array() ){
			return str_replace( 'woocommerce_order_itemmeta.order_id', 'woocommerce_order_items.order_id', $join );
		}
		
		/**
		 * Function for modifying condition for flat tables for advanced search.
		 *
		 * @param string $cond The search condition string.
		 * @param array $params The search condition params.
		 * @return string updated search query condition.
		 */
		public function orders_advanced_search_flat_table_cond( $cond = '', $params = array() ){
			$col = ( ! empty( $params['search_col'] ) ) ? $params['search_col'] : '';
			if( empty( $col ) || ( ! empty( $col ) && !in_array( $col, array_keys( $this->custom_search_cols ) ) ) ){
				return $cond;
			}
			$search_meta = explode( "/", $this->custom_search_cols[$col] );
			$search_table = ( ! empty( $search_meta[0] ) ) ? $search_meta[0] : '';
			$search_col = ( ! empty( $search_meta[1] ) ) ? $search_meta[1] : '';

			if( empty( $search_table ) || empty( $search_col ) || 'woocommerce_order_items' !== $search_table ){
				return $cond;
			}

			global $wpdb;

			// Handling for negation search conditions
			$updated_cond = "(". $wpdb->prefix ."woocommerce_order_items.order_item_type = '". $search_col ."' AND ". str_replace( $col, 'order_item_name', $cond ) ." )";
			if( ! empty( $updated_cond ) && !empty( $params['search_operator'] ) && 'not like' === $params['search_operator'] ){
				$o_ids = $wpdb->get_col( "SELECT DISTINCT(order_id) FROM {$wpdb->prefix}woocommerce_order_items WHERE ". str_replace( 'not like', 'like', $updated_cond ) );
				
				if( is_wp_error( $o_ids ) || empty( $o_ids ) ) {
					return $updated_cond;
				}

				if( count( $o_ids ) > 100 && !empty( $this->advanced_search_option_name ) ){
					update_option( $this->advanced_search_option_name, implode( ",", $o_ids ), 'no' );
					return "( NOT FIND_IN_SET( ". $wpdb->prefix ."woocommerce_order_items.order_id, (SELECT option_value FROM ". $wpdb->prefix ."options WHERE option_name = '". $this->advanced_search_option_name ."') ) )";
				}

				return "(". $wpdb->prefix ."woocommerce_order_items.order_id NOT IN (". implode( ",", $o_ids ) .") )";
			}

			return $updated_cond;
		}

		/**
		 * Function for things to be done post processing of advanced search.
		 *
		 * @return void
		 */
		public function orders_advanced_search_post_processing(){
			if( !empty( $this->advanced_search_option_name ) && !empty( get_option( $this->advanced_search_option_name ) ) ){
				delete_option( $this->advanced_search_option_name );
			}
		}

		/**
		 * AJAX handler function for copy from operator for bulk edit.
		 *
		 * @param array $args bulk edit params.
		 * @return string|array json encoded string or array of values.
		 */
		public function get_batch_update_copy_from_record_ids( $args = array() ) {
			return ( is_callable( array( 'Smart_Manager_Pro_Shop_Order', 'get_copy_from_record_ids' ) ) ) ? Smart_Manager_Pro_Shop_Order::get_copy_from_record_ids( array_merge( array( 'curr_obj' => $this, 'type' => 'shop_order', 'field_title_prefix' => 'Order' ), $args ) ) : '';
		}

		/**
		 * Function to get values for copy from operator for bulk edit.
		 *
		 * @param array $args function arguments.
		 * @return string|array json encoded string or array of values.
		 */
		public static function get_copy_from_record_ids( $args = array() ) {

			if ( empty( Smart_Manager::$sm_is_woo79 ) ) {
				parent::get_batch_update_copy_from_record_ids( $args );
				return;
			}

			$curr_obj = ( ! empty( $args ) && ! empty( $args['curr_obj'] ) ) ? $args['curr_obj'] : null;
			if( empty( $curr_obj ) || empty( $args['type'] ) || empty( $args['field_title_prefix'] ) ){
				return;
			}
			
			global $wpdb;
			$data = array();

			$is_ajax = ( isset( $args['is_ajax'] )  ) ? $args['is_ajax'] : true;
			$search_term = ( ! empty( $curr_obj->req_params['search_term'] ) ) ? $curr_obj->req_params['search_term'] : ( ( ! empty( $args['search_term'] ) ) ? $args['search_term'] : '' );
			$select = apply_filters( 'sm_batch_update_copy_from_ids_select', "SELECT id AS id, CONCAT('". $args['field_title_prefix'] ." #', id) AS title", $args );
			$search_cond = ( ! empty( $search_term ) ) ? " AND ( id LIKE '%".$search_term."%' OR status LIKE '%".$search_term."%' OR billing_email LIKE '%".$search_term."%' ) " : '';
			$search_cond_ids = ( !empty( $args['search_ids'] ) ) ? " AND id IN ( ". implode(",", $args['search_ids']) ." ) " : '';

			$query = $select . " FROM {$wpdb->prefix}wc_orders WHERE status != 'trash' ". $search_cond ." ". $search_cond_ids ." AND type = '" . $args['type'] . "'";
			$results = $wpdb->get_results( $query, 'ARRAY_A' );

			if( count( $results ) > 0 ) {
				foreach( $results as $result ) {
					$data[ $result['id'] ] = trim($result['title']);
				}
			}

			$data = apply_filters( 'sm_batch_update_copy_from_ids', $data );
			if( $is_ajax ){
				wp_send_json( $data );
			} else {
				return $data;
			}
		}

		/**
		 * Function for modifying query for getting ids in case of 'entire store' option.
		 *
		 * @param string $query query for fetching the ids when entire store option is selected.
		 * @return string updated query for fetching the ids when entire store option is selected.
		 */
		public function get_entire_store_ids_query( $query = '' ) {
			global $wpdb;
			return $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}wc_orders WHERE status != 'trash' AND type = %s", 'shop_order' );
		}
	}
}
