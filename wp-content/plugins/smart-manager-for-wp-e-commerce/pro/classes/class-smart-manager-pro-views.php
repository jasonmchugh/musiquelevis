<?php
/**
 * Smart Manager custom views
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;  // Exit if accessed directly.
}

/**
 * Class for handling in app offer for StoreApps
 */
class Smart_Manager_Pro_Views {

    /**
     * Variable to hold instance of this class
     *
     * @var $instance
     */
    private static $instance = null;
    public $req_params = array();

	function __construct() {
		$this->req_params = (!empty($_REQUEST)) ? $_REQUEST : array();
		$this->check_if_table_exists();
	}

    /**
	 * Get single instance of this class
	 *
	 * @param array $args Configuration.
	 * @return Singleton object of this class
	 */
	public static function get_instance() {
		// Check if instance is already exists.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
    }

    /**
	 * Function to insert custom views
	 *
	 * @param array $args Configuration.
	 * @return int $inserted_id ID of the inserted view
	 */
	public function is_view_available() {
		global $wpdb;

		$response = array( 'ACK' => 'Failed', 'is_available' => false );
		$name = ( ! empty( $this->req_params['name'] ) ) ? sanitize_title( wp_unslash( $this->req_params['name'] ) ) : '';

		if( empty( $name ) ){
			wp_send_json($response);
		}

		$row_count = $wpdb->get_var(
						$wpdb->prepare(" SELECT COUNT(id) 
										FROM {$wpdb->prefix}sm_views
										WHERE slug = %s",
										$name
										)
					);
					
		$response = array( 'ACK' => 'Success', 'is_available' => ( empty( $row_count ) ) ? true : false );
		wp_send_json($response);
    }

    /**
	 * Function to insert custom views
	 *
	 * @return int $inserted_id ID of the inserted view
	 */
	public function create() {
		global $wpdb;

		$response = array( 'ACK' => 'Failed' );

		$title = ( ! empty( $this->req_params['name'] ) ) ? sanitize_text_field( wp_unslash( $this->req_params['name'] ) ) : '';
		$slug = sanitize_title( $title );
		$view_state = ( ! empty( $this->req_params['currentView'] ) ) ? $this->req_params['currentView'] : '';
		$active_module = ( ! empty( $this->req_params['active_module'] ) ) ? $this->req_params['active_module'] : '';
		$is_view = ( ( ! empty( $this->req_params['is_view'] ) ) ? 1 : 0 );
		if( ! empty( $is_view ) && ! empty( $active_module ) ) {
			$active_module = $this->get_post_type( $active_module );
		}

		if( empty( $title ) || empty( $view_state ) || empty( $active_module ) ) {
			$response[ 'msg' ] = 'Required params missing.';
			wp_send_json( $response );
		}

		// Code to map view state to required structure
		$view_state = json_decode( stripslashes( $view_state ), true );
		$view_json = sa_sm_generate_column_state( $view_state );

		if( ! empty( $view_state['search_params'] ) ) {
			$view_json['search_params'] = $view_state['search_params'];
		}

		$wpdb->query(
				$wpdb->prepare(
								"INSERT INTO {$wpdb->prefix}sm_views(author, title, slug, params, is_public, post_type, created_date, modified_date)
								VALUES(%d, %s, %s, %s, %d, %s, %d, %d)",
								get_current_user_id(),
								$title,
								$slug,
								json_encode( $view_json ),
								( ( ! empty( $this->req_params['isPublic'] ) && 'false' != $this->req_params['isPublic'] ) ? 1 : 0 ),
								$active_module,
								time(),
								time()
				)
		);

		$insert_id = $wpdb->insert_id;

		if( ! is_wp_error( $insert_id ) ) {
			$response['ACK'] = 'Success';
			$response['id'] = $insert_id;
			$response['slug'] = $slug;
		}

		wp_send_json( $response );
    }

	/**
	 * Function to get view post_type based on slug
	 *
	 * @param string $slug view slug name for which the data is to be fetched.
	 * @return array $post_type post_type that the view is linked to.
	 */
	public function get_post_type( $slug = '' ) {
		global $wpdb;
		$post_type = '';

		if( empty( $slug ) ){
			return $post_type;
		}

		$post_type = $wpdb->get_var(
					$wpdb->prepare(
									"SELECT post_type
										FROM {$wpdb->prefix}sm_views
										WHERE slug = %s",
									$slug
					)
				);
		
		return $post_type;
	}

	/**
	 * Function to get view data based on slug or all views if slug is blank
	 *
	 * @param string $slug view slug name for which the data is to be fetched.
	 * @return array $data array containing the view data or list of all views
	 */
	public function get( $slug = '' ) {
		global $wpdb;
		$data = array();

		if( empty( $slug ) ){ // TODO: improve later
			$data = $wpdb->get_results(
				$wpdb->prepare(
								"SELECT title,
										slug
									FROM {$wpdb->prefix}sm_views
									WHERE 1=%d",
								1
				),
				'ARRAY_A'
			);
		} else {
			$data = $wpdb->get_row(
				$wpdb->prepare(
								"SELECT title,
										params,
										post_type
									FROM {$wpdb->prefix}sm_views
									WHERE slug = %s",
								$slug
				),
				'ARRAY_A'
			);
		}

		return $data;
	}

	/**
	 * Function to get eligible user id for editing columns in custom view
	 */
	public function is_view_author() {	
		global $wpdb;
		$slug = ( ! empty( $this->req_params['slug'] ) ) ? sanitize_title( wp_unslash( $this->req_params['slug'] ) ) : '';
		if( empty( $slug ) )
		{
			wp_send_json( false );
		}

		$row_count = $wpdb->get_var(
						$wpdb->prepare("SELECT COUNT(id) 
										FROM {$wpdb->prefix}sm_views
										WHERE author = %d AND slug = %s",
										get_current_user_id(), $slug
										)
					);

		wp_send_json( ( ! empty( $row_count ) ) ? true : false );
	}
    /**
	 * Function to get all accessible views based on current user
	 *
	 * @param array $post_types array containing the valid post_types for the current user.
	 * @return array $accessible_views array containing the accessible views slug & title
	 */
	public function get_all_accessible_views( $post_types = array() ) {
		global $wpdb;

		$response = array( 'accessible_views' => array(), 'owned_views' => array(), 'public_views' => array() );

		$results = $wpdb->get_results(
					$wpdb->prepare("SELECT title,
											slug,
											author,
											is_public,
											post_type
										FROM {$wpdb->prefix}sm_views
										WHERE ( post_type IN ('". implode("','", array_keys( $post_types ) ) ."')
												AND (is_public = 1
													OR (is_public = 0 AND author = %d) ) )
										GROUP BY slug",
										get_current_user_id()
									),
									'ARRAY_A'
		);

		if( is_callable( array( 'Smart_Manager_Pro_Access_Privilege', 'get_current_user_access_privilege_settings' ) ) ) {
			$accessible_dashboards = Smart_Manager_Pro_Access_Privilege::get_current_user_access_privilege_settings();

			if( ! empty( $accessible_dashboards ) ) {

				$view_results = $wpdb->get_results(
									$wpdb->prepare("SELECT title,
															slug,
															author,
															is_public,
															post_type
														FROM {$wpdb->prefix}sm_views
														WHERE ( slug IN ('". implode("','", $accessible_dashboards ) ."')
															AND 1=%d)
														GROUP BY slug",
														1
													),
													'ARRAY_A'
						);
				if( count( $view_results ) > 0 ){
					$results = array_merge( $results, $view_results );
				}
			}
		}

		if( ! empty( $results ) ) {
			foreach( $results as $result ) {
				$response['accessible_views'][ $result['slug'] ] = $result['title'];
				if( ! empty( $result['author'] ) && $result['author'] == get_current_user_id() ) {
					$response['owned_views'][] = $result['slug'];
				}
				if( ! empty( $result['is_public'] ) ){
					$response['public_views'][] = $result['slug'];	
				}
				if( ! empty( $result['post_type'] ) ){
					$response['view_post_types'][ $result['slug'] ] = $result['post_type'];	
				}
			}
		}

		return $response;

    }

    /**
	 * Function to update custom views
	 *
	 * @return int $inserted_id ID of the inserted view
	 */
	public function update() {
		global $wpdb;

		$response = array( 'ACK' => 'Failed' );

		$title = ( ! empty( $this->req_params['name'] ) ) ? sanitize_text_field( wp_unslash( $this->req_params['name'] ) ) : '';
		$slug = sanitize_title( $title );
		$view_state = ( ! empty( $this->req_params['currentView'] ) ) ? $this->req_params['currentView'] : '';
		$active_module = ( ! empty( $this->req_params['active_module'] ) ) ? $this->req_params['active_module'] : '';

		if( empty( $title ) || empty( $view_state ) || empty( $active_module ) ) {
			$response[ 'msg' ] = 'Required params missing.';
			wp_send_json( $response );
		}

		// Code to map view state to required structure
		$view_state = json_decode( stripslashes( $view_state ), true );
		$view_json = sa_sm_generate_column_state( $view_state );

		if( ! empty( $view_state['search_params'] ) ) {
			$view_json['search_params'] = $view_state['search_params'];
		}

		$result = $wpdb->query( // phpcs:ignore
			$wpdb->prepare( // phpcs:ignore
				"UPDATE {$wpdb->prefix}sm_views
									SET title = %s,
										slug = %s,
										params = %s,
										is_public = %d
									WHERE slug = %s",
				$title,
				$slug,
				json_encode( $view_json ),
				( ( ! empty( $this->req_params['isPublic'] ) && 'false' != $this->req_params['isPublic'] ) ? 1 : 0 ),
				$active_module
			)
		);

		if( ! is_wp_error( $result ) ) {
			$response['ACK'] = 'Success';
			$response['slug'] = $slug;
		}

		wp_send_json( $response );
    }
 
	/**
	 * Function to insert custom views
	 *
	 * @return int $inserted_id ID of the inserted view
	 */
	public function delete() {
		global $wpdb;

		$response = array( 'ACK' => 'Failed' );

		$active_module = ( ! empty( $this->req_params['active_module'] ) ) ? $this->req_params['active_module'] : '';

		if( empty( $active_module ) ) {
			$response[ 'msg' ] = 'Required params missing.';
			wp_send_json( $response );
		}

		$result = $wpdb->query( // phpcs:ignore
			$wpdb->prepare( // phpcs:ignore
							"DELETE FROM {$wpdb->prefix}sm_views
							WHERE slug = %s",
				$active_module
			)
		);

		if( ! is_wp_error( $result ) ) {
			$response['ACK'] = 'Success';
		}

		wp_send_json( $response );
	}

	/**
	 * Function to check & create table for custom views if not exists
	 *
	 * @return void.
	 */
	public function check_if_table_exists(){
		global $wpdb;
		$table_nm = $wpdb->prefix. 'sm_views';
		if ( $table_nm === $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_nm ) ) ) {
			return;
		}
		if ( ! is_callable( array( 'Smart_Manager_Install', 'create_table_for_custom_views' ) ) ) {
			return;
		}
		$table_created = Smart_Manager_Install::create_table_for_custom_views();
		if( ! empty( $table_created ) && is_callable( array( 'Smart_Manager_Install', 'create_dummy_views' ) ) ){
			Smart_Manager_Install::create_dummy_views();
		}
	}
}
