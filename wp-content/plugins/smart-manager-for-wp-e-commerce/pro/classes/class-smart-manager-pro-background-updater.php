<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ActionScheduler' ) && file_exists( SM_PLUGIN_DIR_PATH. '/pro/libraries/action-scheduler/action-scheduler.php' ) ) {
	include_once SM_PLUGIN_DIR_PATH. '/pro/libraries/action-scheduler/action-scheduler.php';
}

/**
 * SM_Background_Updater Class.
 */
if ( ! class_exists( 'Smart_Manager_Pro_Background_Updater' ) ) {
	class Smart_Manager_Pro_Background_Updater {

		/**
		 * @var string
		 */
		public static $_prefix = 'wp';

		public static $_action = 'sm_beta_background_update';

		public static $batch_handler_hook = 'storeapps_smart_manager_batch_handler';

		const SM_WP_CRON_SCHEDULE = 'every_5_seconds';

		protected $action = '';

		protected $identifier = '';

		protected static $_instance = null;

		protected $batch_start_time = '';

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public static function get_identifier() {
			return self::$_prefix . '_' . self::$_action;
		}

		/**
		 * Initiate new background process
		 */
		public function __construct() {
			$this->action = self::$_action;
			$this->identifier = self::get_identifier();

			add_action( 'storeapps_smart_manager_batch_handler', array( $this, 'storeapps_smart_manager_batch_handler' ) );
			add_action( 'action_scheduler_failed_action', array( $this, 'restart_failed_action' ) );
			add_action( 'admin_notices', array( $this, 'background_process_notice' ) );
			add_action( 'admin_head', array( $this, 'background_heartbeat' ) );
			add_filter( 'cron_schedules', array( $this, 'cron_schedules' ), 1000 ); // phpcs:ignore 
			add_filter( 'action_scheduler_run_schedule', array( $this, 'modify_action_scheduler_run_schedule' ), 1000 ); // phpcs:ignore 
			add_action( 'wp_ajax_sa_sm_stop_background_process', array( $this, 'stop_background_process' ) );
			add_action( 'sm_schedule_tasks_cleanup', array( &$this, 'schedule_tasks_cleanup_cron' ) ); // For handling deletion of tasks those are more than x number of days.
		}

		/**
		 * Task
		 *
		 * Override this method to perform any actions required on each
		 * queue item. Return the modified item for further processing
		 * in the next pass through. Or, return false to remove the
		 * item from the queue.
		 *
		 * @param array $callback Update callback function
		 * @return mixed
		 */
		protected function task( $params ) {
			if ( !empty($params['callback']) && !empty($params['args']) ) {
				try {
					include_once dirname( __FILE__ ) .'/class-smart-manager-pro-utils.php';
					include_once( SM_PLUGIN_DIR_PATH . '/classes/class-smart-manager-base.php' );
					include_once dirname( __FILE__ ) .'/class-smart-manager-pro-base.php';
					include_once dirname( __FILE__ ) .'/'. $params['callback']['class_path'];

					if( ! class_exists( 'Smart_Manager_Pro_Task' ) && file_exists( dirname( __FILE__ ) .'/class-smart-manager-pro-task.php' ) ){
						include_once dirname( __FILE__ ) .'/class-smart-manager-pro-task.php';
					}

					if( !empty($params['args']) && is_array($params['args']) ) {
						if( !empty($params['args']['dashboard_key']) && file_exists(dirname( __FILE__ ) . '/class-smart-manager-pro-'. str_replace( '_', '-', $params['args']['dashboard_key'] ) .'.php')) {
							include_once dirname( __FILE__ ) . '/class-smart-manager-pro-'. str_replace( '_', '-', $params['args']['dashboard_key'] ) .'.php';
							$class_name = 'Smart_Manager_Pro_'.ucfirst( str_replace( '-', '_', $params['args']['dashboard_key'] ) );
							$obj = $class_name::instance($params['args']['dashboard_key']);
						}
						if( is_callable( array( $params['callback']['func'][0], 'actions' ) ) ) {
							call_user_func(array($params['callback']['func'][0],'actions'));
						}
						call_user_func($params['callback']['func'],$params['args']);
					}	
				} catch ( Exception $e ) {
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						trigger_error( 'Transactional email triggered fatal error for callback ' . $callback['filter'], E_USER_WARNING );
					}
				}
			}
			return false;
		}

		public function background_heartbeat() {

			?>
			<script type="text/javascript">
				var sa_sm_background_process_heartbeat = function(delay = 0, process = '') {
					
					let admin_ajax_url = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
					admin_ajax_url = (admin_ajax_url.indexOf('?') !== -1) ? admin_ajax_url + '&action=sm_beta_include_file' : admin_ajax_url + '?action=sm_beta_include_file';

					// let isBackground = false;

					// if( jQuery('#sa_sm_background_process_progress').length > 0 && jQuery('#sa_sm_background_process_progress').is(":visible") === true ) {
					// 	isBackground = true;
					// }

					var ajaxParams = {
									url: admin_ajax_url,
									method: 'post',
									dataType: 'json',
									data: {
										cmd: 'get_background_progress',
										active_module: 'Background_Updater',
										security: '<?php echo esc_attr( wp_create_nonce( 'smart-manager-security' ) ); ?>',
										pro: true,
									},
									success: function( response ) {
										let isBackground = false;

										if( jQuery('#sa_sm_background_process_progress').length > 0 && jQuery('#sa_sm_background_process_progress').is(":visible") === true ) {
											isBackground = true;
										}

										if( response.ack == 'Success' ) {
											//Code for updating the progressbar

											let per = parseInt(response.per),
												remainingSeconds = response.remaining_seconds;
											if( isBackground ) {
												jQuery('#sa_sm_remaining_time').html(Math.round(parseInt(per)) + '<?php echo esc_html__( '% completed' , 'smart-manager-for-wp-e-commerce' ); ?>');

												let hours = 0,
													minutes = 0,
													seconds = 0;

												hours   = Math.floor(remainingSeconds / 3600);
												remainingSeconds   %= 3600;
												minutes = Math.floor(remainingSeconds / 60);
												seconds = remainingSeconds % 60;

												hours   = hours < 10 ? "0" + hours : hours;
												minutes = minutes < 10 ? "0" + minutes : minutes;
												seconds = seconds < 10 ? "0" + seconds : seconds;

												jQuery('#sa_sm_remaining_time').append(' ['+ hours + ":" + minutes + ":" + seconds + ' left]')

											} else {
												if( jQuery('.sm_beta_background_update_progressbar').html() == 'Initializing...' ) {
													jQuery('.sm_beta_background_update_progressbar').html('');
												}
												jQuery('.sm_beta_background_update_progressbar').progressbar({ value: parseInt(per) }).children('.ui-progressbar-value').css({"background": "#508991", "height":"2.5em", "color":"#FFF"});
												jQuery('.sm_beta_background_update_progressbar_text').html(Math.round(parseInt(per)) + '<?php echo esc_html__( '% Completed' , 'smart-manager-for-wp-e-commerce' ); ?>');
											}


											if( per < 100 ) {
												setTimeout(function(){
													sa_sm_background_process_heartbeat(0, process);
												}, 1000);
											} else {
												if( isBackground ) {
													jQuery('#sa_sm_background_process_progress').fadeOut();
													jQuery('#sa_sm_background_process_complete').fadeIn();
													setTimeout( function() {
														jQuery('#sa_sm_background_process_complete').fadeOut();							            			
													}, 10000);
												} else {
													window.smart_manager.modal = {}
													if(typeof (window.smart_manager.getDefaultRoute) !== "undefined" && typeof (window.smart_manager.getDefaultRoute) === "function"){
														window.smart_manager.showPannelDialog('',window.smart_manager.getDefaultRoute(true))
													}
													
													jQuery('#sa_sm_background_process_complete').fadeIn();
													window.smart_manager.showLoader();
													let processName = process;
													if (processName) {
														processName = _x(processName.replace(/_/g, ' ').replace(/\b\w/g, function(match) {
															return match.toUpperCase();
														}), 'capitalized process name', 'smart-manager-for-wp-e-commerce');
													}
													let noOfRecords = ('undefined' !== typeof( window.smart_manager.selectedRows ) && window.smart_manager.selectedRows && window.smart_manager.selectedRows.length > 0) ? window.smart_manager.selectedRows.length : (window.smart_manager.selectAll ? _x('All', 'all records', 'smart-manager-for-wp-e-commerce') : 0);
													setTimeout( function() {
														jQuery('#sa_sm_background_process_complete').fadeOut();
														window.smart_manager.notification = {status:'success', message: _x(`${processName} ${_x('for', 'success message', 'smart-manager-for-wp-e-commerce')} ${noOfRecords} ${_x(`${noOfRecords == 1 ? 'record' : 'records'}`, 'success notification', 'smart-manager-for-wp-e-commerce')} ${_x(' completed successfully!', 'success message', 'smart-manager-for-wp-e-commerce')}`, 'success notification', 'smart-manager-for-wp-e-commerce')}
														window.smart_manager.showNotification()
														if(process == 'bulk_edit'){ //code to refresh all the pages for BE
															let p = 1;
															while(p <= window.smart_manager.page){
																window.smart_manager.getData({refreshPage: p});
																p++;
															}
															
															if(window.smart_manager.hot){
																if(window.smart_manager.hot.selection){
																	if(window.smart_manager.hot.selection.highlight){
																		if(window.smart_manager.hot.selection.highlight.selectAll){
																			delete window.smart_manager.hot.selection.highlight.selectAll
																		}
																		window.smart_manager.hot.selection.highlight.selectedRows = []
																	}
																}
															}
															window.smart_manager.hot.render();

															window.smart_manager.selectedRows = [];
															window.smart_manager.selectAll = false;
															window.smart_manager.addRecords_count = 0;
															window.smart_manager.dirtyRowColIds = {};
															window.smart_manager.editedData = {};
															window.smart_manager.updatedEditedData = {};
															window.smart_manager.processContent = '';
															window.smart_manager.updatedTitle = '';
															window.smart_manager.modifiedRows = new Array();
															window.smart_manager.isRefreshingLoadedPage = false;
															window.smart_manager.showLoader(false);
														} else{
															window.smart_manager.refresh();
														}
													}, 1000);
												}
											}
										}
									}

								}

					setTimeout(function(){
						jQuery.ajax(ajaxParams);
					}, delay);
				}
			</script>
			<?php
		}

		/**
		 * Check if batch scheduled action is running
		 *
		 * @return boolean
		 */
		public function is_action_scheduled() {
			$is_scheduled = false;
			if( function_exists( 'as_has_scheduled_action' ) ) {
				$is_scheduled = ( as_has_scheduled_action( self::$batch_handler_hook ) ) ? true : false;
			} else if( function_exists( 'as_next_scheduled_action' ) ) {
				$is_scheduled = ( as_next_scheduled_action( self::$batch_handler_hook ) ) ? true : false;
			}
			return $is_scheduled;
		}

		/**
		 * Stop all scheduled actions by this plugin
		 */
		public function stop_scheduled_actions() {
			if ( function_exists( 'as_unschedule_action' ) ) {
				as_unschedule_action( self::$batch_handler_hook );
			}
			$this->clean_scheduled_action_data(true);
		}

		/**
		 * Stop batch background process via AJAX
		 */
		public function stop_background_process() {
			check_ajax_referer( 'smart-manager-security', 'security' );
			$this->stop_scheduled_actions();
			wp_send_json_success();
		}

		/**
		 * Clean scheduled action data
		 * 
		 * @param  boolean $abort flag whether the process has been forcefully stopped or not.
		 */
		public function clean_scheduled_action_data( $abort = false ) {
			delete_option( $this->identifier.'_start_time' );
			delete_option( $this->identifier.'_current_time' );
			delete_option( $this->identifier.'_tot' );
			delete_option( $this->identifier.'_remaining' );
			delete_option( $this->identifier.'_initial_process' );

			if( ! empty( $abort ) ) {
				delete_option( $this->identifier.'_ids' );
				delete_option( $this->identifier.'_current_id_batch' );
				delete_option( $this->identifier.'_params' );
				delete_option( $this->identifier.'_is_background' );
			}
		}

		/**
		 * Function to display admin notice in case of background process
		 *
		 */
		public function background_process_notice() {

			if ( ! is_admin() ) {
				return;
			}

			if( !( !empty( $_GET['page'] ) && 'smart-manager' === $_GET['page'] ) ) {
				return;
			}

			$initial_process = get_option( $this->identifier.'_initial_process', false );

			if( !empty( $initial_process ) ) {
				if( false === get_option( '_sm_update_42191', false ) ) {
					delete_option( $this->identifier.'_initial_process' );
					update_option( '_sm_update_42191', 1, 'no' );
				}

				$progress = $this->calculate_background_process_progress();
				$percent = ( !empty( $progress['percent_completion'] ) ) ? $progress['percent_completion'] : 0;

				if($percent >= 100){
					return;
				}
			}

			if ( ! $this->is_process_running() && empty( $initial_process ) ) {
				return;
			}

			update_option( $this->identifier.'_is_background', 1, 'no' );

			$batch_params = get_option( $this->identifier.'_params', array() );

			$process_name = ( !empty( $batch_params['process_name'] ) ) ? $batch_params['process_name'] : 'Batch';
			$current_dashboard = ( !empty( $batch_params['active_dashboard'] ) ) ? $batch_params['active_dashboard'] : 'Products';
			$no_of_records = ( ( !empty( $batch_params['entire_store'] ) ) ? __( 'All', 'smart-manager-for-wp-e-commerce' ) : $batch_params['id_count'] ) .' '. esc_html( $current_dashboard ); 
			$admin_email = get_option( 'admin_email', false );
			$admin_email = ( empty( $admin_email ) ) ? 'admin email' : $admin_email;

			?>
			<div id="sa_sm_background_process_progress" class="error" style="display: none;">
				<?php
				if ( empty( $this->is_action_scheduled() ) && empty( $initial_process ) ) {
					$this->clean_scheduled_action_data(true);
					?>
						<p>
						<?php
							/* translators: 1. Error title 2. The bulk process */
							echo sprintf( esc_html__( '%1$s: The %2$s process has stopped. Please review the Smart Manager dashboard to check the status.', 'smart-manager-for-wp-e-commerce' ), '<strong>' . esc_html__( 'Error', 'smart-manager-for-wp-e-commerce' ) . '</strong>', '<strong>' . esc_html( strtolower( $process_name ) ) . '</strong>' );
						?>
						</p>
						<?php
				} else {
					?>
						<p>
							<?php
								echo '<strong>' . esc_html__( 'Important', 'smart-manager-for-wp-e-commerce' ) . '</strong>:';
								echo '&nbsp;' . esc_html( $process_name ) . '&nbsp;'. esc_html__( 'request is running', 'smart-manager-for-wp-e-commerce' ) .'&nbsp;';
								echo esc_html__( 'in the background. You will be notified on', 'smart-manager-for-wp-e-commerce' ) .'&nbsp; <code>'. esc_html( $admin_email ) .'</code>&nbsp; '. esc_html__( 'when it is completed.', 'smart-manager-for-wp-e-commerce' ) . '&nbsp;';
							?>
						</p>
						<p>
								<span id="sa_sm_remaining_time_label">
									<?php echo esc_html__( 'Progress', 'smart-manager-for-wp-e-commerce' ); ?>:&nbsp;
										<strong><span id="sa_sm_remaining_time"><?php echo esc_html__( '--:--:--', 'smart-manager-for-wp-e-commerce' ); ?></span></strong>&nbsp;&nbsp;
										<a id="sa-sm-stop-batch-process" href="javascript:void(0);" style="color: #dc3232;"><?php echo esc_html__( 'Stop', 'woocommerce-smart-coupons' ); ?></a>
								</span>
						</p>
						<p>
							<?php
								echo '<strong>' . esc_html__( 'NOTE', 'smart-manager-for-wp-e-commerce' ) . '</strong>:&nbsp'; 
								echo $batch_params['backgroundProcessRunningMessage']; 
							?>
						</p>
					</div>
					<div id="sa_sm_background_process_complete" class="updated" style="display: none;">
						<p>
							<strong><?php echo esc_html( $process_name ); ?></strong>
							<?php echo esc_html__( 'for', 'smart-manager-for-wp-e-commerce' ). ' <strong>' . esc_html( $no_of_records ) . '</strong> ' .  esc_html__( 'completed successfully', 'smart-manager-for-wp-e-commerce' ) ; ?>
						</p>
					</div>
					<script type="text/javascript">
						sa_sm_background_process_heartbeat(0, '<?php echo esc_html( $process_name ); ?>');

						jQuery('body').on('click', '#sa-sm-stop-batch-process', function(e){
							e.preventDefault();
							<?php /* translators: 1. The bulk process */ ?>
							let admin_ajax_url = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
							admin_ajax_url = (admin_ajax_url.indexOf('?') !== -1) ? admin_ajax_url + '&action=sm_beta_include_file' : admin_ajax_url + '?action=sm_beta_include_file';
							let result = window.confirm('<?php echo sprintf(
								/* translators: %s: process name */
								esc_html__( 'Are you sure you want to stop the %s process? Click OK to stop.', 'smart-manager-for-wp-e-commerce' ), esc_html( $process_name ) ); ?>');
							if (result) {
								jQuery.ajax({
									url     : admin_ajax_url,
									method  : 'post',
									dataType: 'json',
									data    : {
										action  : 'sa_sm_stop_background_process',
										security: '<?php echo esc_attr( wp_create_nonce( 'smart-manager-security' ) ); ?>',
										pro		: true 
									},
									success: function( response ) {
										location.reload();
									}
								});
							}
						});

					</script>
					<?php
				}
				?>
				<script type="text/javascript">
					jQuery('#sa_sm_background_process_progress').fadeIn();
				</script>
			</div>
			<?php
		}

		/**
		 * Calculate progress of background process
		 *
		 * @return array $progress
		 */
		public function calculate_background_process_progress() {
			$progress = array( 'percent_completion' => 0, 'remaining_seconds' => 0 );

			$start_time            = get_option( $this->identifier.'_start_time', false );
			$current_time            = get_option( $this->identifier.'_current_time', false );
			$all_tasks_count       = get_option( $this->identifier.'_tot', false );
			$remaining_tasks_count = get_option( $this->identifier.'_remaining', false );

			if( empty( $start_time ) && empty( $current_time ) && empty( $all_tasks_count ) && empty( $remaining_tasks_count ) ) {
				$progress = array( 'percent_completion' => 100, 'remaining_seconds' => 0 );
			} else {
				$percent_completion = floatval( 0 );
				if ( false !== $all_tasks_count && false !== $remaining_tasks_count ) {
					$percent_completion             = ( ( intval( $all_tasks_count ) - intval( $remaining_tasks_count ) ) * 100 ) / intval( $all_tasks_count );
					$progress['percent_completion'] = floatval( $percent_completion );
				}

				if ( $percent_completion > 0 && false !== $start_time && false !== $current_time ) {
					$time_taken_in_seconds         = intval($current_time) - intval($start_time);
					$time_remaining_in_seconds     = ( $time_taken_in_seconds / $percent_completion ) * ( 100 - $percent_completion );
					$progress['remaining_seconds'] = ceil( $time_remaining_in_seconds );

				}

				if( $progress['percent_completion'] >= 100 ) { //on process completion
					$this->clean_scheduled_action_data();
				}
			}
			
			return $progress;
		}

		/**
		 * Get background process progress via ajax
		 */
		public function get_background_progress() {

			$response = array();

			$progress = $this->calculate_background_process_progress();

			$percent = ( !empty( $progress['percent_completion'] ) ) ? $progress['percent_completion'] : 0;
			$remaining_seconds = ( !empty( $progress['remaining_seconds'] ) ) ? $progress['remaining_seconds'] : 0;
			$response = array( 'ack' => 'Success', 'per' => $percent, 'remaining_seconds' => $remaining_seconds );

			wp_send_json( $response );
		}

		/**
		 * Initiate Batch Process
		 *
		 * initiate batch process and pass control to batch_handler function
		 */
		public function initiate_batch_process() {

			$update_ids = get_option( $this->identifier.'_ids', array() );

			if( !empty( $update_ids ) ) {
				update_option( $this->identifier.'_tot', count( $update_ids ), 'no' );
				update_option( $this->identifier.'_remaining', count( $update_ids ), 'no' );
				update_option( $this->identifier.'_start_time', time(), 'no' );
				update_option( $this->identifier.'_current_time', time(), 'no' );
				update_option( $this->identifier.'_initial_process', 1, 'no' );

				as_unschedule_action( self::$batch_handler_hook );

				if( is_callable( array( $this, 'storeapps_smart_manager_batch_handler' ) ) ) {
					$this->storeapps_smart_manager_batch_handler();
				}
			}
		}

		/**
		 * Batch Handler
		 *
		 * Pass each queue item to the task handler, while remaining
		 * within server memory and time limit constraints.
		 */
		public function storeapps_smart_manager_batch_handler() {

			$batch_params = get_option( $this->identifier.'_params', array() );
			$update_ids = get_option( $this->identifier.'_ids', array() );		

			if( empty( $batch_params ) || empty( $update_ids ) ) {
				return;
			}

			$start_time = get_option( $this->identifier.'_start_time', false );
			if( empty( $start_time ) ) {
				update_option( $this->identifier.'_start_time', time(), 'no' );
			}

			$this->batch_start_time = time();
			$batch_complete = false;

			// update_option( $this->identifier.'_batch_start_time', time() );

			$update_remaining_count = get_option( $this->identifier.'_remaining', false );
			$update_tot_count = get_option( $this->identifier.'_tot', false );

			$current_batch_update_id = $current_batch_action_id = '';

			$current_id_batch_action = get_option( $this->identifier.'_current_id_batch', false );
			
			if( !empty( $current_id_batch_action ) ) {
				$current_params = explode( '__', $current_id_batch_action );
				$current_batch_update_id = ( !empty( $current_params[0] ) ) ? $current_params[0] : '';
				$current_batch_action_id = ( !empty( $current_params[1] ) ) ? $current_params[1] : '';
			}

			foreach( $update_ids as $key => $update_id ) {

				$current_batch_action_id = 0;

				if( !empty( $current_batch_update_id ) && $current_batch_update_id == $update_id && !empty( $current_batch_action_id ) ) {
					$start_index = $current_batch_action_id;
				}

				if( !empty( $batch_params['actions'] ) ) { //For Batch Update
					for( $index = $current_batch_action_id; $index < sizeof( $batch_params['actions'] ); $index++ ) {
						update_option( $this->identifier.'_current_id_batch', $update_id.'__'.$index, 'no' );

						$batch_params['actions'][$index]['id'] = $update_id;
						$this->task( array( 'callback' => $batch_params['callback'], 'args' => $batch_params['actions'][$index] ) );

						update_option( $this->identifier.'_current_time', time(), 'no' );

						if( $this->time_exceeded() || $this->memory_exceeded() ) { //Code for continuing the batch
							$initial_process = get_option( $this->identifier.'_initial_process', false );

							if( !empty( $initial_process ) ) {
								delete_option( $this->identifier.'_initial_process' );
							}

							$batch_complete = true;
							break;
						}
					}	
				} else {

					$args = array( 'dashboard_key' => $batch_params['dashboard_key'], 'id' => $update_id );
					$args = ( !empty( $batch_params['callback_params'] ) && is_array( $batch_params['callback_params'] ) ) ? array_merge( $args, $batch_params['callback_params'] ) : $args;

					$this->task( array( 'callback' => $batch_params['callback'], 'args' => $args ) );

					update_option( $this->identifier.'_current_time', time(), 'no' );

					if( $this->time_exceeded() || $this->memory_exceeded() ) { //Code for continuing the batch
						$initial_process = get_option( $this->identifier.'_initial_process', false );

						if( !empty( $initial_process ) ) {
							delete_option( $this->identifier.'_initial_process' );
						}

						$batch_complete = true;
					}
				}

				//Code for post update
				$update_remaining_count = $update_remaining_count - 1;

				update_option( $this->identifier.'_remaining', $update_remaining_count, 'no' );

				if( 0 === $update_remaining_count ) { // Code for handling when the batch has completed.
					do_action( 'sm_background_process_complete', $this->identifier ); // For triggering task deletion after successfully completing undo task/deleting task.
					delete_option( $this->identifier.'_ids' );
					delete_option( $this->identifier.'_current_id_batch' );
					
					$is_background = get_option( $this->identifier.'_is_background', false );

					if( !empty( $is_background ) ) {
						$this->complete();
					} else {
						delete_option( $this->identifier.'_params' );
					}

					delete_option( $this->identifier.'_is_background' );
				} else if( !empty( $batch_complete ) ) { //Code for continuing the batch
					$update_ids = array_slice( $update_ids, $key+1 );

					update_option( $this->identifier.'_remaining', $update_remaining_count, 'no' );
					update_option( $this->identifier.'_ids', $update_ids, 'no' );

					if ( function_exists( 'as_schedule_single_action' ) ) {
						as_schedule_single_action( time(), self::$batch_handler_hook );
					}

					break;
				}
			}
		}

		/**
		 * Memory exceeded
		 *
		 * Ensures the batch process never exceeds 90%
		 * of the maximum WordPress memory.
		 *
		 * @return bool
		 */
		protected function memory_exceeded() {
			$memory_limit   = $this->get_memory_limit() * 0.9; // 90% of max memory
			$current_memory = memory_get_usage( true );

			if ( $current_memory >= $memory_limit ) {
				return true;
			}

			return false;
		}

		/**
		 * Get memory limit.
		 *
		 * @return int
		 */
		protected function get_memory_limit() {
			if ( function_exists( 'ini_get' ) ) {
				$memory_limit = ini_get( 'memory_limit' );
			} else {
				// Sensible default.
				$memory_limit = '128M';
			}

			if ( ! $memory_limit || -1 === intval( $memory_limit ) ) {
				// Unlimited, set to 32GB.
				$memory_limit = '32G';
			}

			return wp_convert_hr_to_bytes( $memory_limit );
		}

		/**
		 * Time exceeded.
		 *
		 * Ensures the batch never exceeds a sensible time limit.
		 * A timeout limit of 30s is common on shared hosting.
		 *
		 * @return bool
		 */
		protected function time_exceeded() {

			if( empty( $this->batch_start_time ) ) {
				$return = false;
			}

			$finish = $this->batch_start_time + apply_filters( $this->identifier . '_batch_default_time_limit', 20 ); // 20 seconds
			$return = false;

			if ( time() >= $finish ) {
				$return = true;
			}

			return apply_filters( $this->identifier . '_batch_time_exceeded', $return );
		}

		public function complete() {
			Smart_Manager_Pro_Base::batch_process_complete();
		}

		/**
		 * Checks if background process is running
		 *
		 * @return bool  $is_process_running
		 */
		public function is_process_running() {
			$batch_params = get_option( $this->identifier.'_params', array() );
			return ( ! empty( $batch_params ) ) ? true : false;
		}

		/**
		 * Restart scheduler after one minute if it fails
		 *
		 * @param  array $action_id id of failed action.
		 */
		public function restart_failed_action( $action_id ) {

			if ( ! class_exists( 'ActionScheduler' ) || ! is_callable( array( 'ActionScheduler', 'store' ) ) || ! function_exists( 'as_schedule_single_action' ) ) {
				return;
			}

			$action      = ActionScheduler::store()->fetch_action( $action_id );
			$action_hook = $action->get_hook();

			if ( self::$batch_handler_hook === $action_hook ) {
				as_schedule_single_action( time() + MINUTE_IN_SECONDS, self::$batch_handler_hook );
			}
		}

		/**
		 * Function to modify the action sceduler run schedule
		 *
		 * @param string $wp_cron_schedule schedule interval key.
		 * @return string $wp_cron_schedule
		 */
		public function modify_action_scheduler_run_schedule( $wp_cron_schedule ) {
			return self::SM_WP_CRON_SCHEDULE;
		}

		/**
		 * Function to add entry to cron_schedules
		 *
		 * @param array $schedules schedules with interval and display.
		 * @return array $schedules
		 */
		public function cron_schedules( $schedules ) {

			$schedules[self::SM_WP_CRON_SCHEDULE] = array(
				'interval' => 5,
				'display'  => __( 'Every 5 Seconds', 'smart-manager-for-wp-e-commerce' ),
			);

			return $schedules;
		}
		/**
		 * Delete tasks from tasks table those are more than x number of days
		 * 
		 * @return void
		 */
		public function schedule_tasks_cleanup_cron() {
			$tasks_cleanup_interval_days = get_option( 'sa_sm_tasks_cleanup_interval_days' );
			if ( empty( $tasks_cleanup_interval_days ) ) {
				return;
			}
			include_once( SM_PLUGIN_DIR_PATH . '/classes/class-smart-manager-base.php' );
			include_once dirname( __FILE__ ) . '/class-smart-manager-pro-base.php';
			include_once dirname( __FILE__ ) . '/class-smart-manager-pro-task.php';
			if ( is_callable( array( 'Smart_Manager_Pro_Task', 'delete_tasks' ) ) && is_callable( array( 'Smart_Manager_Pro_Task', 'get_task_ids' ) ) ) {
				Smart_Manager_Pro_Task::delete_tasks( Smart_Manager_Pro_Task::get_task_ids( date( 'Y-m-d H:i:s', strtotime( "-" . $tasks_cleanup_interval_days . " Days" ) ) ) );	
			}
		    if ( is_callable( array( 'Smart_Manager_Pro_Task', 'schedule_task_deletion' ) ) ) {
				Smart_Manager_Pro_Task::schedule_task_deletion();
			}
		}
	}
}

Smart_Manager_Pro_Background_Updater::instance();
