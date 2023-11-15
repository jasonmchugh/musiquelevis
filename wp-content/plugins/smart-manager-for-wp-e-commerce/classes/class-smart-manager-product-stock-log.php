<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Smart_Manager_Product_Stock_Log' ) ) {
	/**
	 * Class that extends Smart_Manager_Task
	 */
	class Smart_Manager_Product_Stock_Log extends Smart_Manager_Task {
		/**
		 * Current dashboard name
		 *
		 * @var string
		 */
		public $dashboard_key = '';
		/**
		 * Array of field names for modifying data model key
		 *
		 * @var array
		 */
		public $key_mod_fields = array();
		/**
		 * Advanced search table types
		 *
		 * @var array
		 */
		public $advanced_search_table_types = array();
		/**
		 * Instance of the class
		 *
		 * @param string $dashboard_key Current dashboard name.
		 * @return object
		 */
		public static function instance( $dashboard_key ) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self( $dashboard_key );
			}
			return self::$_instance;
		}

		/**
		 * Constructor is called when the class is instantiated
		 *
		 * @param string $dashboard_key $dashboard_key Current dashboard name.
		 * @return void
		 */
		function __construct( $dashboard_key ) {
			// should be kept before calling the parent class constructor.
			add_filter(
				'sm_search_table_types',
				function( $advanced_search_table_types = array() ) {
					$advanced_search_table_types['flat'] = array(
						'sm_task_details' => 'task_id',
					);
					return $advanced_search_table_types;
				}
			);
			parent::__construct( $dashboard_key );
			$this->dashboard_key = 'product';
			$this->key_mod_fields = array( 'record_id', 'prev_val', 'updated_val' );
			add_filter( 'sm_default_dashboard_model', array( &$this, 'dashboard_model' ) );
			add_filter( 'sm_data_model', array( &$this, 'data_model' ), 99, 2 );
			add_filter( 'sm_where_tasks_cond', array( &$this, 'where_cond' ) );
			add_filter( 'sm_join_tasks_cond', array( &$this, 'join_cond' ) );
			add_filter( 'sm_select_tasks_query', array( &$this, 'select_query' ) );
			add_filter( 'sm_non_sortable_cols', function( $non_sortable_cols = array() ) {
					$product_cols = array(
						'product_title',
						'product_type',
						'sku',
						'parent_id'
					);
					return ( ! empty( $non_sortable_cols ) && is_array( $non_sortable_cols ) ) ? array_merge( $product_cols, $non_sortable_cols ) : $product_cols;
				} );
		}

		/**
		 * Generate dashboard model for product stock log
		 *
		 * @param array $dashboard_model array contains the dashboard_model data.
		 * @return array $dashboard_model returns dashboard_model data
		 */
		public function dashboard_model( $dashboard_model = array() ) {
			global $wpdb;
			if ( empty( $dashboard_model ) || empty( $dashboard_model['columns'] ) ) {
				return $dashboard_model;
			}
			$task_cols = array(
				'id'   => _x( 'Task ID', 'task id', 'smart-manager-for-wp-e-commerce' ),
				'type' => _x( 'Type', 'task type', 'smart-manager-for-wp-e-commerce' ),
				'status'   => _x( 'Status', 'task status', 'smart-manager-for-wp-e-commerce' ),
				'completed_date' => _x( 'Date', 'task completed date', 'smart-manager-for-wp-e-commerce' )
			);
			$column_model = array();
			$column_model = &$dashboard_model['columns'];
			foreach ( $column_model as $key => &$column ) {
				if ( empty( $column['src'] ) ) continue;
				$src_exploded = explode( "/", $column['src'] );
				if ( empty( $src_exploded ) ) {
					$src = $column['src'];
				}
				$src = $src_exploded[1];
				$col_table = $src_exploded[0];
				if ( sizeof( $src_exploded ) > 2 ) {
					$col_table = $src_exploded[0];
					$cond = explode( "=", $src_exploded[1] );
					if ( 2 === sizeof( $cond ) ) {
						$src = $cond[1];
					}
				}
				if ( empty( $src ) ) {
					continue;
				}
				if ( false === array_key_exists( $src, $task_cols ) ) {
					unset( $column_model[ $key ] );
					continue;
				}
				$column['name'] = $column['key'] = $task_cols[ $src ];
			}
			$product_stock_fields = array( 
				'record_id'   => _x( 'Product ID', 'product id', 'smart-manager-for-wp-e-commerce' ),
				'prev_val'    => _x( 'Old Value', 'old stock value', 'smart-manager-for-wp-e-commerce' ),
				'updated_val' => _x( 'New Value', 'New stock value', 'smart-manager-for-wp-e-commerce' )
			);
			$numeric_cols = array_merge( $product_stock_fields, array( 
				'parent_id' => _x( 'Parent ID', 'parent id', 'smart-manager-for-wp-e-commerce' )
				 ) 
			);
			$product_cols = array( 
				'sku'           => _x( 'SKU', 'product SKU', 'smart-manager-for-wp-e-commerce' ),
				'product_title' => _x( 'Product Title', 'product title', 'smart-manager-for-wp-e-commerce' ),
				'product_type'  => _x( 'Product Type', 'product type', 'smart-manager-for-wp-e-commerce' )
			);
			$cols = array_merge( $numeric_cols, $product_cols );
            foreach ( $cols as $key => $val ) {
				$args = array(
					'table_nm' 	=> 'posts',
					'col'		=> $key,
					'type'		=> ( array_key_exists( $key, $numeric_cols ) ) ? 'numeric' : 'text',
					'editable'	=> false,
					'editor'	=> false,
					'hidden'    => false,
					'name'      => $cols[ $key ]
				);
				if ( array_key_exists( $key, $product_stock_fields ) ) {
					$args['table_nm' ] = 'sm_task_details';
				} elseif ( array_key_exists( $key, array_merge( $product_cols, array( 'parent_id' => _x( 'Parent ID', 'product parent ID', 'smart-manager-for-wp-e-commerce' ) ) ) ) ) {
					$args['sortable']	= false;
					$args['searchable']	= false;
				}
				$column_model[] = $this->get_default_column_model( $args );
            }
            return $dashboard_model;
		}
		
		/**
		 * Generate data model
		 *
		 * @param array $data_model array containing the data model.
		 * @param array $data_col_params array containing column params.
		 * @return array $data_model returns data_model array
		 */
		public function data_model( $data_model = array(), $data_col_params = array() ) {
			if ( empty( $data_model ) || ( ! is_array( $data_model ) ) || empty( $data_model['items'] ) ) {
				return $data_model;
			}
			global $wpdb;
			$index = 0;
			$items = $data_model['items'];
			$product = null;
			foreach ( $items as $value ) {
				if ( ( ( ! empty( $value ) ) && is_array( $value ) ) && in_array( 'sm_task_details_record_id', array_keys( $value ) ) && ( ! empty( $value['sm_task_details_record_id'] ) ) ) {
					$product = function_exists( 'wc_get_product' ) ? wc_get_product( $value['sm_task_details_record_id'] ) : null;
					;
				}
				if ( ! $product instanceof WC_Product ) {
					continue;
				}
				$items[ $index ]['posts_product_title'] = ( is_callable( array( $product, 'get_name' ) ) ) ? $product->get_name() : '';
				$items[ $index ]['posts_product_type'] = ( is_callable( array( $product, 'get_type' ) ) ) ? $product->get_type() : '';
				$items[ $index ]['posts_sku'] = ( is_callable( array( $product, 'get_sku' ) ) ) ? $product->get_sku() : '';
				$items[ $index ]['posts_parent_id'] = ( is_callable( array( $product, 'get_parent_id' ) ) ) ? $product->get_parent_id() : 0;
				$index++;	
			}
			$data_model['items'] = ( ! empty( $items ) ) ? $items : array();
			return $data_model;
		}
		
		/**
		 * Modify where condition for fetching stock fields from task details
		 *
		 * @param string $where where condition of sm_tasks table.
		 * @return string updated where condition
		 */
		public function where_cond( $where = '' ) {
			global $wpdb;
			$where_cond = " AND {$wpdb->prefix}sm_task_details.field = 'postmeta/meta_key=_stock/meta_value=_stock'";
			return ( false === strpos( $where, $where_cond ) ) ? $where . $where_cond : $where;
		}
		
		/**
		 * Modify join condition for fetching stock fields from task details
		 *
		 * @param string $join join condition of sm_tasks table.
		 * @return string updated join condition
		 */
		public function join_cond( $join = '' ) {
			global $wpdb;
			$join_cond = " JOIN {$wpdb->prefix}sm_task_details
								ON ({$wpdb->prefix}sm_task_details.task_id = {$wpdb->prefix}sm_tasks.id)";
			return ( false === strpos( $join, $join_cond ) ) ? $join . $join_cond : $join;
		}
		
		/**
		 * Modify select condition for fetching stock fields from task details
		 *
		 * @param string $select select query of sm_tasks table.
		 * @return string updated select query
		 */
		public function select_query( $select = '' ) {
			global $wpdb;
			return "SELECT {$wpdb->prefix}sm_tasks.*, {$wpdb->prefix}sm_task_details.record_id, {$wpdb->prefix}sm_task_details.prev_val, {$wpdb->prefix}sm_task_details.updated_val";
		}
	}
}
