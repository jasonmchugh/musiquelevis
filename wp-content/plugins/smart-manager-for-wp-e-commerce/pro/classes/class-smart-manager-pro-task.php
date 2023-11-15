<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Smart_Manager_Pro_Task' ) ) {
	/**
	 * Class that extends Smart_Manager_Pro_Base
	 */
	class Smart_Manager_Pro_Task extends Smart_Manager_Pro_Base {
		/**
		 * Current dashboard name
		 *
		 * @var string
		 */
		public $dashboard_key = '';
		/**
		 * Selected record ids
		 *
		 * @var array
		 */
		public $selected_ids = array();
		/**
		 * Entire task records
		 *
		 * @var boolean
		 */
		public $entire_task = false;
		/**
		 * Singleton class
		 *
		 * @var object
		 */
		protected static $_instance = null;
		/**
		 * Advanced search table types
		 *
		 * @var array
		 */
		public $advanced_search_table_types = array(
			'flat' => array(
				'sm_tasks' => 'id'
			) 
		);
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
			parent::__construct( $dashboard_key );
			self::actions();
			$this->dashboard_key = $dashboard_key;
			global $current_user;
		}
		/**
		 * Add filters for doing actions
		 *
		 * @return void
		 */
		public static function actions() {
			add_filter( 'sm_beta_background_entire_store_ids_from', __CLASS__ . '::undo_all_task_ids_from_clause' );
			add_filter( 'sm_beta_background_entire_store_ids_where', __CLASS__ . '::undo_all_task_ids_where_clause' );
			add_filter( 'sm_post_batch_update_db_updates', __CLASS__ . '::post_undo', 10, 2 );
			add_action( 'sm_background_process_complete', __CLASS__ . '::background_process_complete' );
		}

		/**
		 * Undo changes for task records
		 *
		 * @return void
		 */
		public function undo() {
			$this->get_task_detail_ids( '_undo_task_id' );
			$this->send_to_background_process(
				array(
					'process_name' => 'Undo Tasks',
					'callback'     => array(
						'class_path' => $this->req_params['class_path'],
						'func'       => array(
							$this->req_params['class_nm'],
							'process_undo',
						),
					),
				)
			);
		}

		/**
		 * Processing undo for task record
		 *
		 * @param array $args contains task_details_ids, fetch.
		 * @return void
		 */
		public static function process_undo( $args = array() ) {
			if ( empty( $args )|| empty( $args['id'] ) ) {
				return;
			}
			$col_data_type = parent::get_column_data_type( $args['dashboard_key'] );
			$dashboard_key = $args['dashboard_key'];
			$args = self::get_task_details(
				array(
					'task_details_ids' => ( ! is_array( $args['id'] ) ) ? array( $args['id'] ) : $args['id'],
					'fetch'            => 'all',
				)
			);
			$type = apply_filters( 'sm_custom_field_name', $args['type'] );
			$args['date_type'] = ( ! empty( $col_data_type[ $type ] ) ) ? $col_data_type[ $type ] : 'text';
			$args['dashboard_key'] = $dashboard_key;
			$arg_values = ( ! empty( $args['value'] ) ) ? explode( ',', $args['value'] ) : '';

			if ( 'set_to' === $args['operator'] && 'sm.multilist' === $args['date_type'] && ( ! empty( $arg_values ) && ( count( $arg_values ) > 0 ) ) && ( ! empty( $args['updated_value'] ) ) ) {
				$arg_updated_values = explode( ',', $args['updated_value'] );
				foreach ( $arg_updated_values as $arg_updated_value ) {
					$args['value'] = $arg_updated_value;
					$args['operator'] = 'remove_from';
					if ( ! empty( $args ) ) {
						parent::process_batch_update( $args );
					}
				}
				foreach ( $arg_values as $value ) {
					$args['value'] = $value;
					$args['operator'] = 'add_to';
					if ( ! empty( $args ) ) {
						parent::process_batch_update( $args );
					}
				}
			} elseif ( ! empty( $args ) ) {
				$args = parent::process_batch_update( $args );
			}
		}

		/**
		 *  Function to update the from clause for getting entire task ids from tasks table
		 *
		 * @param string $from from string.
		 * @return string from query
		 */
		public static function undo_all_task_ids_from_clause( $from = '' ) {
			return ( empty( $from ) ) ? $from : str_replace( 'posts', 'sm_tasks', $from );
		}

		/**
		 * Function to update the where clause for getting entire task ids from tasks table
		 *
		 * @param string $where where string.
		 * @return string where query
		 */
		public static function undo_all_task_ids_where_clause( $where = '' ) {
			return ( ! empty( $where ) && ( false === strpos( $where, 'WHERE' ) ) ) ? 'WHERE 1=1 ' : str_replace( "AND post_status != 'trash'", '', $where );
		}

		/**
		 * Get task ids from tasks table based on completed and scheduled date time
		 *
		 * @param string $scheduled_datetime scheduled datetime.
		 * @return array $task_ids task ids array
		 */
		public static function get_task_ids( $scheduled_datetime = '' ) {
			if ( empty( $scheduled_datetime ) ) {
				return;
			}
			global $wpdb;
			$task_ids = $wpdb->get_col(
				"SELECT id
				FROM {$wpdb->prefix}sm_tasks
				WHERE completed_date < '" . $scheduled_datetime . "'"
			);
			return ( ! is_wp_error( $task_ids ) ) ? $task_ids : array();
		}

		/**
		 * Delete tasks
		 *
		 * @return void
		 */
		public function delete() {
			$this->get_task_detail_ids( '_delete_task_id' );
			$this->send_to_background_process(
				array(
					'process_name' => 'Delete Tasks',
					'callback' => array(
						'class_path' => $this->req_params['class_path'],
						'func' => array(
							$this->req_params['class_nm'], 'process_delete'
						),
					),
				)
			);
		}

		/**
		 * Process the deletion of task details record
		 *
		 * @param array $args record id.
		 * @return boolean
		 */
		public static function process_delete( $args = array() ) {
			if ( empty( $args ) && empty( $args['id'] ) ) {
				return false;
			}
			return ( self::delete_task_details( ( ! is_array( $args['id'] ) ? array( $args['id'] ) : $args['id'] ) ) ) ? true : false;
		}

		/**
		 * Delete tasks from tasks table
		 *
		 * @param array $task_ids array of task ids.
		 * @return boolean true if number of rows deleted, or false on error
		 */
		public static function delete_tasks( $task_ids = array() ) {
			if ( empty( $task_ids ) || ( ! is_array( $task_ids ) ) ) {
				return false;
			}
			global $wpdb;
			return ( ! is_wp_error(
				$wpdb->query(
					"DELETE FROM {$wpdb->prefix}sm_tasks
					WHERE id IN (" . implode( ',', $task_ids ) . ')'
				)
			) ) ? true : false;
		}

		/**
		 * Delete task details from task details table
		 *
		 * @param array $task_detail_ids task detail ids.
		 * @return boolean true if number of rows deleted, or false on error
		 */
		public static function delete_task_details( $task_detail_ids = array() ) {
			if ( empty( $task_detail_ids ) && ! is_array( $task_detail_ids ) ) {
				return false;
			}
			global $wpdb;
				return ( ! is_wp_error(
					$wpdb->query(
						"DELETE FROM {$wpdb->prefix}sm_task_details
						WHERE id IN (" . implode( ',', $task_detail_ids ) . ')'
					)
				) ) ? true : false;
		}

		/**
		 * Schedule task deletion after x number of days
		 *
		 * @return void
		 */
		public static function schedule_task_deletion() {
			if ( ! function_exists( 'as_has_scheduled_action' ) ) {
				return;
			}
			$is_scheduled = as_has_scheduled_action( 'sm_schedule_tasks_cleanup' ) ? true : false;
			if ( ! ( ( false === $is_scheduled ) && function_exists( 'as_schedule_single_action' ) ) ) {
				return;
			}
			$task_deletion_days = intval( get_option( 'sa_sm_tasks_cleanup_interval_days' ) );
			if ( empty( $task_deletion_days ) ) {
				$task_deletion_days = intval( apply_filters( 'sa_sm_tasks_cleanup_interval_days', 90 ) );
				if ( empty( $task_deletion_days ) ) {
					return;
				}
				update_option( 'sa_sm_tasks_cleanup_interval_days', $task_deletion_days, 'no' );
			}
			$timestamp = strtotime( date('Y-m-d H:i:s', strtotime( "+".$task_deletion_days." Days" ) ) );
			if ( empty( $timestamp ) ) {
				return;
			}
			as_schedule_single_action( $timestamp, 'sm_schedule_tasks_cleanup' ); 
		}

		/**
		 * Delete task details after changes are undone
		 *
		 * @param boolean $delete_flag flag for delete.
		 * @param array   $params task_details_id.
		 * @return boolean
		 */
		public static function post_undo( $delete_flag = true, $params = array() ) {
			if ( empty( $params['task_details_id'] ) && ( empty( $delete_flag ) ) ) {
				return;
			}
			return ( self::delete_task_details( ( ! is_array( $params['task_details_id'] ) ? array( $params['task_details_id'] ) : $params['task_details_id'] ) ) ) ? true : false;
		}

		/**
		 * Delete tasks from tasks table and delete undo/delete option from options table after completing undo/delete action
		 *
		 * @param string $identifier identifier name - either undo or delete.
		 * @return void
		 */
		public static function background_process_complete( $identifier = '' ) {
			if ( empty( $identifier ) ) {
				return $identifier;
			}
			$failed_task_ids = array();
			$option_nm = self::get_process_option_name( $identifier );
			if ( empty( $option_nm ) ) {
				return;
			}
			$task_ids = get_option( $identifier . $option_nm );
			if ( empty( $task_ids ) ) {
				return;
			}
			$results = self::get_task_details(
				array(
					'task_ids' => $task_ids,
					'fetch'    => 'count',
				)
			);
			if ( ! empty( $results ) ) {
				foreach ( $results as $result ) {
					if ( ! empty( $result['count'] ) ) {
						$failed_task_ids[] = $result['id'];
					}
				}
			}
			$delete_task_ids = ( ! empty( $failed_task_ids ) && is_array( $failed_task_ids ) && is_array( $task_ids ) ) ? array_diff( $task_ids, $failed_task_ids ) : $task_ids;
			if ( empty( $delete_task_ids ) ) {
				return;
			}
			if ( self::delete_tasks( $delete_task_ids ) ) {
				delete_option( $identifier . $option_nm );
			}
		}

		/**
		 * Get task detail ids using selected task ids and store them in options table in case of undo and delete actions
		 *
		 * @param string $option_nm option name - either _undo_task_id or _delete_task_id.
		 * @return array $fetched_task_details_ids ids of task details
		 */
		public function get_task_detail_ids( $option_nm = '' ) {
			if ( empty( $option_nm ) ) {
				return;
			}
			$identifier = '';
			$task_ids = ( ! empty( $this->req_params['selected_ids'] ) ) ? json_decode( stripslashes( $this->req_params['selected_ids'] ), true ) : array();
			if ( ( ! empty( $this->req_params['storewide_option'] ) ) && ( 'entire_store' === $this->req_params['storewide_option'] ) && ( ! empty( $this->req_params['active_module'] ) ) ) { 
				$task_ids = $this->get_entire_store_ids();
				$this->entire_task = true;
			}
			if ( is_callable( array( 'Smart_Manager_Pro_Background_Updater', 'get_identifier' ) ) ) {
				$identifier = Smart_Manager_Pro_Background_Updater::get_identifier();
			}
			if ( ! empty( $identifier ) && ( ! empty( $task_ids ) ) ) {
				update_option( $identifier . $option_nm, $task_ids, 'no' );
			}
			$task_details_ids = self::get_task_details(
				array(
					'task_ids' => $task_ids,
					'fetch' => 'ids',
				)
			);
			$fetched_task_details_ids = array();
			foreach ( $task_details_ids as $task_details_id ) {
				$fetched_task_details_ids[] = $task_details_id['task_details_id'];
			}
			$this->req_params['selected_ids'] = ( ! empty( $fetched_task_details_ids ) && is_array( $fetched_task_details_ids ) ) ? json_encode( $fetched_task_details_ids ) : $this->req_params['selected_ids']; // ids of task details.
		}

		/**
		 * Get process option name from options table incase of undo and delete actions
		 *
		 * @param string $identifier identifier name - either undo or delete.
		 * @return string | boolean process option name if true, else false
		 */
		public static function get_process_option_name( $identifier = '' ) {
			if ( empty( $identifier ) ) {
				return;
			}
			$params = get_option( $identifier . '_params' );
			if ( empty( $params['process_name'] ) ) {
				return;
			}
			$process_names = array( 'Undo Tasks', 'Delete Tasks' );
			return ( in_array( $params['process_name'], $process_names, true ) ) ? ( ( 'Undo Tasks' === $params['process_name'] ) ? '_undo_task_id' : '_delete_task_id' ) : false;
		}
		
		/**
		 * Get task details
		 *
		 * @param array $params task_ids, task_details_ids, fetch.
		 * @return array task details [ids( tasks/task details ), count of id, record_id, field, prev_value, operator]
		 */
		public static function get_task_details( $params = array() ) {
			if ( empty( $params ) ) {
				return;
			}
			global $wpdb;
			$task_ids         = ( ! empty( $params['task_ids'] ) ) ? $params['task_ids'] : array();
			$task_details_ids = ( ! empty( $params['task_details_ids'] ) ) ? $params['task_details_ids'] : array();
			$fetch            = ( ! empty( $params['fetch'] ) ) ? $params['fetch'] : array();
			switch ( $params ) {
				case ( ( ! empty( $task_ids ) ) && ( ! empty( $fetch ) ) && ( 'ids' === $fetch ) ):
					return $wpdb->get_results(
						"SELECT task_id AS task_id, id AS task_details_id
						FROM {$wpdb->prefix}sm_task_details
						WHERE task_id IN (" . implode( ',', $task_ids ) . ')',
						'ARRAY_A'
					);
				case ( ( ! empty( $task_details_ids ) ) && ( ! empty( $fetch ) ) && ( 'all' === $fetch ) ):
					return $wpdb->get_row(
						"SELECT id AS task_details_id, record_id AS id, field AS type, prev_val AS value, action AS operator, updated_val AS updated_value
						FROM {$wpdb->prefix}sm_task_details
						WHERE id IN (" . implode( ',', $task_details_ids ) . ')',
						'ARRAY_A'
					);
				case ( ( ! empty( $task_ids ) ) && ( ! empty( $fetch ) ) && ( 'count' === $fetch ) ):
					return $wpdb->get_results(
						"SELECT task_id AS id, IFNULL( count(id), 0 ) AS count
						FROM {$wpdb->prefix}sm_task_details
						WHERE task_id IN (" . implode( ',', $task_ids ) . ')
						GROUP BY task_id',
						'ARRAY_A'
					);
			}
		}
	}
}
