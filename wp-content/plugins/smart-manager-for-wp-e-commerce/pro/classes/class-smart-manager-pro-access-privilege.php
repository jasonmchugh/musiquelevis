<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Smart_Manager_Pro_Access_Privilege' ) ) {
	class Smart_Manager_Pro_Access_Privilege {

		protected static $_instance = null;
		public static $access_privilege_option_start = 'sm_beta_';
		public static $access_privilege_option_end = '_accessible_dashboards';	

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		function __construct() {
			add_filter( 'sm_active_dashboards', array( $this, 'sm_beta_get_accessible_dashboards' ) );
			add_filter( 'sm_active_taxonomy_dashboards', array( $this, 'sm_beta_get_accessible_dashboards' ) );
			add_action( 'wp_ajax_smart_manager_save_settings', array( $this, 'save_settings' ) );
		}

		public function save_settings() {
			if ( empty( $_POST ) || ( ! wp_verify_nonce( $_POST['smart-manager-security'], 'smart_manager_save_settings' ) ) ) {
			    echo 'Security Check Failed';
			    die();
			}
			global $wpdb;
			$success_msg = _x( 'Settings saved successfully!!!', 'Settings saving success message', 'smart-manager-for-wp-e-commerce' );
			$failed_msg = _x( 'Settings saving Failed!!!', 'Settings saving failed message', 'smart-manager-for-wp-e-commerce' );
			
			$current_user_role = ( is_callable( array( 'Smart_Manager', 'get_current_user_role' ) ) ) ? Smart_Manager::get_current_user_role() : '';
			if( ( ! empty( $current_user_role ) ) && ( 'administrator' === $current_user_role ) ) {
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}options 
	        							WHERE option_name LIKE %s
	        								AND option_name LIKE %s", '%' . $wpdb->esc_like(self::$access_privilege_option_start) . '%', '%' . $wpdb->esc_like(self::$access_privilege_option_end) . '%' ) );
				if( ( ! empty( $_POST['user_role_dashboards'] ) ) ) {
		        	$update_values = array();
		        	foreach( $_POST['user_role_dashboards'] as $user_role => $dashboards ) {
		        		$update_values[] = "( '".self::$access_privilege_option_start."".$user_role."".self::$access_privilege_option_end."', '". implode(",", $dashboards) ."', 'no' )";
		        	}
		        	if( ! empty( $update_values ) ) {
		        		$query = "INSERT INTO {$wpdb->prefix}options ( option_name, option_value, autoload) VALUES ". implode(", ",$update_values) ." ON DUPLICATE KEY UPDATE option_value = VALUES ( option_value )";
		        		$result = $wpdb->query( $query );
		        		$msg = ( ( ( ! empty( $result ) ) && ( ! is_wp_error( $result ) ) ) ) ? $success_msg : $failed_msg;
		        	}
	        	} 		
	        }
			wp_send_json( array( 'msg' => $msg ) );
		}

		public static function get_all_privileges() {
			global $wpdb;

			$user_role_dashboard_privileges = array();

			$results = $wpdb->get_results( $wpdb->prepare( "SELECT LEFT(SUBSTR(option_name, %d), LOCATE(%s, SUBSTR(option_name, %d)) -1) as user_role,
																option_value as dashboards
															FROM {$wpdb->prefix}options 
															WHERE option_name LIKE %s 
																AND option_name LIKE %s", strlen(self::$access_privilege_option_start)+1, self::$access_privilege_option_end, strlen(self::$access_privilege_option_start)+1, '%' . $wpdb->esc_like(self::$access_privilege_option_start) . '%', '%' . $wpdb->esc_like(self::$access_privilege_option_end) . '%' ), 'ARRAY_A' );

			if( !empty( $results ) ) {
				foreach( $results as $result ) {
					$role = ( !empty( $result['user_role'] ) ) ? $result['user_role'] : '';
					$dashboards = ( !empty( $result['dashboards'] ) ) ? explode( ",", $result['dashboards'] ) : '';
					$user_role_dashboard_privileges[ $role ] = $dashboards;
				}
			}

			return json_encode($user_role_dashboard_privileges);
		}

		//Function to render the render the access privilege settings
		public static function render_access_privilege_settings() {

			$user_role_dashboard_privileges = self::get_all_privileges();

			?>

			<style type="text/css">
				table#sm_access_privilege_settings {
				  width: 99%;
				  background-color: #FFFFFF;
				  border-collapse: collapse;
				  border-width: 0px !important;
				  border-color: #3892D3 !important;
				  border-style: solid;
				  color: #656161;
				  border-radius: 0.3em !important;
				}

				table#sm_access_privilege_settings th {
					background-color: #252f3f !important;
					color: #FFFFFF !important;
					/* border-color: #5850ec !important; */
				}

				table#sm_access_privilege_settings td, table#sm_access_privilege_settings th {
				  border-width: 1px;
				  border-color: #3892D3;
				  border-style: solid;
				  padding: 15px;
				}

				table#sm_access_privilege_settings thead {
				  background-color: #3892D3;
				}

				.sm_access_privilege_dashboards_select2 {
					color:#656161;
				}

				table#sm_access_privilege_settings .select2-selection__rendered {
					line-height: 2.3rem !important;
				}


				table#sm_access_privilege_settings .select2-results{
					color: #4b5563 !important;
				}

				table#sm_access_privilege_settings .select2-container .select2-selection--single {
					height: 2.5rem !important;
					/* background-color: #252f3f !important;
					border: 1px solid #8c8f94; */
				}

				table#sm_access_privilege_settings .select2-selection__arrow {
					height: 2.3rem !important;
				}

			</style>

			<script type="text/javascript">
				let smIsJson = function(str) {
					try {
						return (JSON.parse(str) && !!str);
					} catch (e) {
						return false;
					}
				}

				jQuery(document).ready(function() {

					let allUserRoleDashboardPrivileges = '<?php echo $user_role_dashboard_privileges; ?>';

					allUserRoleDashboardPrivileges = ( smIsJson( allUserRoleDashboardPrivileges ) ) ? JSON.parse(allUserRoleDashboardPrivileges) : {};

					jQuery('#toplevel_page_smart-manager').find('.wp-first-item').closest('li').removeClass('current');
					jQuery('#toplevel_page_smart-manager').find('a[href$=sm-settings]').closest('li').addClass('current');

					jQuery(".sm_access_privilege_dashboards").select2({ width: '50%', dropdownCssClass: 'sm_access_privilege_dashboards_select2', placeholder: "Select Dashboards" });

					jQuery('.sm_access_privilege_dashboards').each(function() { //Code for setting the 'name' attribute for each of the select2 box
						let parentID = jQuery(this).parents('tr').attr('id');
						let str = 'user_role_dashboards['+parentID+']'
						jQuery(this).attr('name', str+'[]');

						if( allUserRoleDashboardPrivileges.hasOwnProperty(parentID) ) {
							jQuery(this).val(allUserRoleDashboardPrivileges[parentID]).trigger('change');
						}
						
					});

					jQuery('#smart_manager_settings_form').on('submit', function(e) {
						e.preventDefault();
						let $form = jQuery(this);
						jQuery.post($form.attr('action'), $form.serialize(), function(data) {
							if( typeof data['msg'] != 'undefined' ) {
								alert(data['msg']);
							}
						}, 'json');
					});

				});
			</script>

			<br/>

			<form id="smart_manager_settings_form" action="<?php echo admin_url('admin-ajax.php'); ?>" method="post">

			<?php
				global $current_user;

				wp_nonce_field('smart_manager_save_settings','smart-manager-security');

				$current_user_role = ( is_callable( array( 'Smart_Manager', 'get_current_user_role' ) ) ) ? Smart_Manager::get_current_user_role() : '';
					
				if( ( ! empty( $current_user_role ) && 'administrator' === $current_user_role ) ) {

			?>
					<h3 style="font-size:1rem;color:#656161"><?php _e('Smart Manager Access Privilege Settings', 'smart-manager-for-wp-e-commerce' );?></h3><br/>

					<table id="sm_access_privilege_settings" name="sm_access_privilege_settings" class="form-table">
						<tbody>
							<tr>
								<th><b>Roles</b></th>
								<th><b>Dashboards</b></th>
							</tr>
							
							<?php
								$all_roles = get_editable_roles();

								if ( !empty( $all_roles ) ) {

									if( isset( $all_roles['administrator'] ) ){
							            unset( $all_roles['administrator']);
							        }

							        $dashboard_select_options = '<select class="sm_access_privilege_dashboards" multiple="multiple" style="min-width:130px !important;">';

							        if ( defined( 'SM_BETA_ALL_DASHBOARDS' ) ) {
										$all_dashboards = json_decode(SM_BETA_ALL_DASHBOARDS, true);

										if( !empty( $all_dashboards ) ) {
											$dashboard_select_options .= '<optgroup label="All post types">';
											foreach( $all_dashboards as $dashboard => $title ) {
												$dashboard_select_options .= '<option value="'. $dashboard .'">'. $title .'</option>';
											}
											$dashboard_select_options .= '</optgroup>';
										}
									}

									if ( defined( 'SM_ALL_TAXONOMY_DASHBOARDS' ) ) {
										$all_taxonomy_dashboards = json_decode(SM_ALL_TAXONOMY_DASHBOARDS, true);

										if( !empty( $all_taxonomy_dashboards ) ) {
											$dashboard_select_options .= '<optgroup label="All taxonomies">';
											foreach( $all_taxonomy_dashboards as $dashboard => $title ) {
												$dashboard_select_options .= '<option value="'. $dashboard .'">'. $title .'</option>';
											}
											$dashboard_select_options .= '</optgroup>';
										}
									}

									// Code for fetching all views
									if( class_exists( 'Smart_Manager_Pro_Views' ) ) {
										$view_obj = Smart_Manager_Pro_Views::get_instance();
										if( is_callable( array( $view_obj, 'get' ) ) ){
											$views = $view_obj->get();
											if( ! empty( $views ) ) {
												$dashboard_select_options .= '<optgroup label="All saved views">';
												foreach( $views as $view ) {
													$dashboard_select_options .= '<option value="'. $view['slug'] .'">'. $view['title'] .'</option>';
												}
												$dashboard_select_options .= '</optgroup>';
											}
										}
									}

									$dashboard_select_options .= '</select>';

									foreach( $all_roles as $user_role => $user_role_obj ) {
										echo '<tr id="'. $user_role .'">'.
												'<td>'. ( ( !empty( $user_role_obj['name'] ) ) ? $user_role_obj['name'] : ucwords( $user_role ) ) .'</td>'.
												'<td>'. $dashboard_select_options .'</td>'.
											'</tr>';
									}
								}
							?>					
						</tbody>
					</table>
			<?php
				}
			?>

			<p class="submit">
				<input type="submit" name="smart_manager_save_settings" id="smart_manager_save_settings" class="button-primary" value="Apply" style="padding: 0.5em 2em 0.5em 2em;background-color: rgb(80, 137, 145);border-color: rgb(80, 137, 145);">
				<a style="color: rgb(80, 137, 145);padding-left: 1em;" href="<?php echo admin_url('admin.php?page=smart-manager'); ?>"> <?php _e('Back to Smart Manager', 'smart-manager-for-wp-e-commerce') ?> </a>
			</p>

			<input name="action" value="smart_manager_save_settings" type="hidden">

			</form>
			<?php
		}

		//function to get current user wp_role object
		public static function getRoles( $role ) {
	        global $wp_roles;

	        $current_user_role_obj = array();
	        
	        if (function_exists('wp_roles')) {
	            $roles = wp_roles();
	        } elseif(isset($wp_roles)) {
	            $roles = $wp_roles;
	        } else {
	            $roles = new WP_Roles();
	        }

	        if( !empty( $roles->roles ) ) {
	        	$current_user_role_obj = ( !empty( $roles->roles[$role] ) ) ? $roles->roles[$role] : array();
	        }
	        
	        return $current_user_role_obj;
	    }

	    public static function is_dashboard_valid( $role, $dashboard ) {

	    	$singular_cap = array('edit_', 'read_', 'delete_');
        	$plural_cap = array('edit_','edit_others_','publish_','read_private_','delete_','delete_private_','delete_published_','delete_others_','edit_private_','edit_published_');

        	$current_user_role_obj = self::getRoles( $role );
	        $current_user_role_caps = ( !empty( $current_user_role_obj['capabilities'] ) ) ? $current_user_role_obj['capabilities'] : array();

        	$valid = array( 'custom_cap_isset' => false,
        					'dashboard_valid' => false );

        	if( $dashboard != 'post' && $dashboard != 'page' ) {
        		foreach( $singular_cap as $singular ) {

	        		$cap = $singular.''.$dashboard;

	        		if( isset( $current_user_role_caps[$cap] ) ) {

	        			$valid['custom_cap_isset'] = true;
	        			$valid['dashboard_valid'] = true;

	        			if( empty( $current_user_role_caps[$cap] ) ) {
	        				$valid['dashboard_valid'] = false;
	        				break;
	        			}
	        		}
	        	}
        	}

        	foreach( $plural_cap as $plural ) {

        		$cap = $plural.''.$dashboard.'s';

        		if( isset( $current_user_role_caps[$cap] ) ) {

        			$valid['custom_cap_isset'] = true;
	        		$valid['dashboard_valid'] = true;

        			if( empty( $current_user_role_caps[$cap] ) ) {
        				$valid['dashboard_valid'] = false;
        				break;
        			}
        		}
        	}

        	return $valid;
	    }

		public static function get_current_user_access_privilege_settings(){
			global $current_user;
			$accessible_dashboards = array();

			$current_user_role = ( is_callable( array( 'Smart_Manager', 'get_current_user_role' ) ) ) ? Smart_Manager::get_current_user_role() : '';	
			
			if( ! ( ( ! empty( $current_user_role ) && 'administrator' === $current_user_role ) ) ) {
	        	$accessible_dashboards = explode( ",", get_option( self::$access_privilege_option_start.''.$current_user_role.''.self::$access_privilege_option_end, '' ) );
			}				
			return $accessible_dashboards;
		}

		public function sm_beta_get_accessible_dashboards( $dashboards ) {

			$accessible_dashboards = self::get_current_user_access_privilege_settings();

	        if( ! empty( $accessible_dashboards ) ) {

	        	foreach( $dashboards as $key => $dashboard ) {

	        		if( !in_array( $key, $accessible_dashboards ) ) {
	        			unset( $dashboards[$key] );
	        		}
	        	}
	        }


	        if( empty($dashboards) && !defined('SM_BETA_ACCESS') ){
	        	define('SM_BETA_ACCESS', false);
	        } else if( !empty($dashboards) && !defined('SM_BETA_ACCESS') ){
	        	define('SM_BETA_ACCESS', true);
	        }

			return $dashboards;

		}

	}

}

$GLOBALS['smart_manager_pro_access_privilege'] = Smart_Manager_Pro_Access_Privilege::instance();
