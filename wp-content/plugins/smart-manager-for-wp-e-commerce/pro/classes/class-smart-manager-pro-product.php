<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Smart_Manager_Pro_Product' ) ) {
	class Smart_Manager_Pro_Product extends Smart_Manager_Pro_Base {
		public $dashboard_key = '',
				$variation_product_old_title = '',
				$plugin_path = '';


		protected static $_instance = null;

		public $product = '';

		public static function instance($dashboard_key) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self($dashboard_key);
			}
			return self::$_instance;
		}

		function __construct($dashboard_key) {
			parent::__construct($dashboard_key);
			self::actions();

			$this->post_type = array('product', 'product_variation');
			$this->plugin_path  = untrailingslashit( plugin_dir_path( __FILE__ ) );

			if ( file_exists(SM_PLUGIN_DIR_PATH . '/classes/class-smart-manager-product.php') ) {
				include_once SM_PLUGIN_DIR_PATH . '/classes/class-smart-manager-product.php';
				$this->product = new Smart_Manager_Product( $dashboard_key );
			}

			add_filter('sm_dashboard_model',array(&$this,'products_dashboard_model'),12,2);
		}

		public function __call( $function_name, $arguments = array() ) {

			if( empty( $this->product ) ) {
				return;
			}

			if ( ! is_callable( array( $this->product, $function_name ) ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( array( $this->product, $function_name ), $arguments );
			} else {
				return call_user_func( array( $this->product, $function_name ) );
			}
		}

		public static function actions() {
			// add_filter('sm_beta_batch_update_entire_store_ids_query', __CLASS__. '::products_batch_update_entire_store_ids_query', 10, 1);
			add_filter('sm_beta_post_batch_process_args', __CLASS__. '::products_post_batch_process_args', 10, 1);
			add_action('sm_pre_batch_update_db_updates', __CLASS__. '::products_pre_batch_update_db_updates', 10, 2);
			add_filter('sm_post_batch_update_db_updates', __CLASS__. '::products_post_batch_update_db_updates', 10, 2);
			add_filter( 'sm_beta_batch_update_prev_value',__CLASS__. '::products_batch_update_prev_value', 12, 2 );
			add_filter( 'sm_task_details_update_by_prev_val',__CLASS__. '::task_details_update_by_prev_val', 12, 1 );
			add_filter( 'sm_disable_task_details_update',__CLASS__. '::disable_task_details_update', 12, 2 );
			add_filter( 'sm_get_value_for_copy_from_operator',__CLASS__. '::get_value_for_copy_from_operator', 12, 2 );
			add_filter( 'sm_update_value_for_copy_from_operator',__CLASS__. '::update_value_for_copy_from_operator', 12, 1 );
			add_filter( 'sm_process_undo_args_before_update',__CLASS__. '::process_undo_args_before_update', 12, 1 );
			add_filter( 'sm_task_update_action',__CLASS__. '::task_update_action', 12, 2 );
			add_filter( 'sm_delete_attachment_get_matching_gallery_images_post_ids',__CLASS__. '::get_matching_gallery_images_post_ids', 12, 2 );
		}
		
		public static function products_post_batch_process_args( $args ) {

			if( !empty( $args['operator'] ) && ( $args['operator'] == 'set_to_regular_price' || $args['operator'] == 'set_to_sale_price' ) ) {
				$col = ( $args['operator'] == 'set_to_regular_price' ) ? '_regular_price' : '_sale_price';
				$args['value'] = get_post_meta( $args['id'], $col, true );
			}

			return $args;
		}

		public function products_dashboard_model ($dashboard_model, $dashboard_model_saved) {
			global $wpdb;

			$numeric_columns = array(
				'_wc_booking_duration'               => __( 'Booking duration', 'smart-manager-for-wp-e-commerce' ), 
				'_wc_booking_min_duration'           => __( 'Minimum duration', 'smart-manager-for-wp-e-commerce' ), 
				'_wc_booking_max_duration'           => __( 'Maximum duration', 'smart-manager-for-wp-e-commerce' ),
				'_wc_booking_cancel_limit'           => __( 'Booking can be cancelled until', 'smart-manager-for-wp-e-commerce' ),
				'_wc_booking_min_persons_group'      => __( 'Min persons', 'smart-manager-for-wp-e-commerce' ),
				'_wc_booking_max_persons_group'      => __( 'Max persons', 'smart-manager-for-wp-e-commerce' ),
				'_wc_booking_qty'                    => __( 'Max bookings per block', 'smart-manager-for-wp-e-commerce' ),
				'_wc_booking_min_date'               => __( 'Minimum block bookable', 'smart-manager-for-wp-e-commerce' ),
				'_wc_booking_max_date'               => __( 'Maximum block bookable', 'smart-manager-for-wp-e-commerce' ),
				'_wc_booking_buffer_period'          => __( 'Require a buffer period of', 'smart-manager-for-wp-e-commerce' ),
				// https://woocommerce.com/products/minmax-quantities/
				'minimum_allowed_quantity'           => __( 'Minimum quantity', 'smart-manager-for-wp-e-commerce' ),
				'maximum_allowed_quantity'           => __( 'Maximum quantity', 'smart-manager-for-wp-e-commerce' ),
				'group_of_quantity'                  => __( 'Group of...', 'smart-manager-for-wp-e-commerce' ),
				'variation_minimum_allowed_quantity' => __( 'Variation Minimum quantity', 'smart-manager-for-wp-e-commerce' ),
				'variation_maximum_allowed_quantity' => __( 'Variation Maximum quantity', 'smart-manager-for-wp-e-commerce' ),
				'variation_group_of_quantity'        => __( 'Variation Group of...', 'smart-manager-for-wp-e-commerce' ),
				// https://wordpress.org/plugins/minmax-quantity-for-woocommerce/
				'min_quantity'                       => __( 'Minimum Quantity', 'smart-manager-for-wp-e-commerce' ),
				'max_quantity'                       => __( 'Maximum Quantity', 'smart-manager-for-wp-e-commerce' ),
				'min_quantity_var'                   => __( 'Variation Minimum Quantity', 'smart-manager-for-wp-e-commerce' ),
				'max_quantity_var'                   => __( 'Variation Maximum Quantity', 'smart-manager-for-wp-e-commerce' ),
				// https://wordpress.org/plugins/woo-min-max-quantity-limit/
				'_wc_mmax_min'                       => __( 'Min Quantity', 'smart-manager-for-wp-e-commerce' ),
				'_wc_mmax_max'                       => __( 'Max Quantity', 'smart-manager-for-wp-e-commerce' ),
				// [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/)
				'_subscription_price'				 => __( 'Subscription Price', 'smart-manager-for-wp-e-commerce' ),
				'_subscription_sign_up_fee'			 => __( 'Sign-up Fee', 'smart-manager-for-wp-e-commerce' ),
				'_subscription_trial_length'		 => __( 'Free Trial', 'smart-manager-for-wp-e-commerce' ),
				// [WooCommerce Cost Of Goods](https://woocommerce.com/products/woocommerce-cost-of-goods/)
				'_wc_cog_cost'		 				 => __( 'Cost of Good', 'smart-manager-for-wp-e-commerce' ),
				// [Germanized for WooCommerce](https://wordpress.org/plugins/woocommerce-germanized/)
				'_unit_product'						 => __( 'Product Units', 'smart-manager-for-wp-e-commerce' ),
				'_unit_base'						 => __( 'Unit Price Units', 'smart-manager-for-wp-e-commerce' ),
				'_unit_price_regular'				 => __( 'Regular Unit Price', 'smart-manager-for-wp-e-commerce' ),
				'_unit_price_sale'					 => __( 'Sale Unit Price', 'smart-manager-for-wp-e-commerce' ),
			);

			$numeric_text_editor_columns = array( '_wc_booking_duration', '_wc_booking_min_duration', '_wc_booking_max_duration', '_wc_booking_cancel_limit', 
												'_wc_booking_min_date', '_wc_booking_max_date' );

			$checkbox_empty_one_columns = array(
				'_wc_booking_enable_range_picker'    => __( 'Enable Calendar Range Picker?', 'smart-manager-for-wp-e-commerce' ),
				'_wc_booking_requires_confirmation'  => __( 'Requires confirmation?', 'smart-manager-for-wp-e-commerce' ),
				'_wc_booking_user_can_cancel'        => __( 'Can be cancelled?', 'smart-manager-for-wp-e-commerce' ),
				'_wc_booking_has_persons'            => __( 'Has persons', 'smart-manager-for-wp-e-commerce' ),
				'_wc_booking_has_resources'          => __( 'Has resources', 'smart-manager-for-wp-e-commerce' ),
				'_wc_booking_person_cost_multiplier' => __( 'Multiply all costs by person count', 'smart-manager-for-wp-e-commerce' ),
				'_wc_booking_person_qty_multiplier'  => __( 'Count persons as bookings', 'smart-manager-for-wp-e-commerce' ),
				'_wc_booking_has_person_types'       => __( 'Enable person types', 'smart-manager-for-wp-e-commerce' ),
				'_wc_booking_has_restricted_days'    => __( 'Restrict start and end days?', 'smart-manager-for-wp-e-commerce' ),
				'_wc_booking_apply_adjacent_buffer'  => __( 'Adjacent Buffering?', 'smart-manager-for-wp-e-commerce' ),
			);

			$checkbox_zero_one_columns = array(
				// https://wordpress.org/plugins/woo-min-max-quantity-limit/
				'_wc_mmax_prd_opt_enable' => __( 'Enable Min Max Quantity', 'smart-manager-for-wp-e-commerce' ),
			);

			$checkbox_yes_no_columns = array(
				// https://woocommerce.com/products/minmax-quantities/
				'min_max_rules'                              => __( 'Min/Max Rules', 'smart-manager-for-wp-e-commerce' ),
				'allow_combination'                          => __( 'Allow Combination', 'smart-manager-for-wp-e-commerce' ),
				'minmax_do_not_count'                        => __( 'Order rules: Do not count', 'smart-manager-for-wp-e-commerce' ),
				'minmax_cart_exclude'                        => __( 'Order rules: Exclude', 'smart-manager-for-wp-e-commerce' ),
				'minmax_category_group_of_exclude'           => __( 'Category group-of rules: Exclude', 'smart-manager-for-wp-e-commerce' ),
				'variation_minmax_do_not_count'              => __( 'Variation Order rules: Do not count', 'smart-manager-for-wp-e-commerce' ),
				'variation_minmax_cart_exclude'              => __( 'Variation Order rules: Exclude', 'smart-manager-for-wp-e-commerce' ),
				'variation_minmax_category_group_of_exclude' => __( 'Variation Category group-of rules: Exclude', 'smart-manager-for-wp-e-commerce' ),
				// [Germanized for WooCommerce](https://wordpress.org/plugins/woocommerce-germanized/)
				'_unit_price_auto' 							 => __( 'Calculate unit prices automatically', 'smart-manager-for-wp-e-commerce' ),	
			);

			$booking_duration_unit = array(
				'month'  => __( 'Month(s)', 'smart-manager-for-wp-e-commerce'),
				'day'    => __( 'Day(s)', 'smart-manager-for-wp-e-commerce' ),
				'hour'   => __( 'Hour(s)', 'smart-manager-for-wp-e-commerce' ),
				'minute' => __( 'Minutes(s)', 'smart-manager-for-wp-e-commerce' )
			);

			$column_model = &$dashboard_model['columns'];

			foreach( $column_model as $key => &$column ) {
				if ( empty( $column['src'] ) ) continue;

				$src_exploded = explode("/",$column['src']);

				if (empty($src_exploded)) {
					$col_nm = $column['src'];
				}

				if ( sizeof($src_exploded) > 2 ) {
					$col_table = $src_exploded[0];
					$cond = explode("=",$src_exploded[1]);

					if (sizeof($cond) == 2) {
						$col_nm = $cond[1];
					}
				} else {
					$col_nm = $src_exploded[1];
					$col_table = $src_exploded[0];
				}

				if( empty( $col_nm ) ) {
					continue;
				}

				switch( $col_nm ) {
					case '_wc_booking_duration_type':
						$column['key'] = __( 'Booking Duration (Type)', 'smart-manager-for-wp-e-commerce' );
						$column['name'] = $column['key'];
						$booking_duration_type = array( 'fixed' => __( 'Fixed blocks of', 'smart-manager-for-wp-e-commerce'),
														'customer' => __( 'Customer defined blocks of', 'smart-manager-for-wp-e-commerce' ) );

						$column = $this->generate_dropdown_col_model( $column, $booking_duration_type );
						break;
					case '_wc_booking_duration_unit':
						$column['key'] = __( 'Booking Duration (Unit)', 'smart-manager-for-wp-e-commerce' );
						$column['name'] = $column['key']; 
						$column = $this->generate_dropdown_col_model( $column, $booking_duration_unit );
						break;
					case '_wc_booking_cancel_limit_unit':
						$column['key'] = __( 'Booking can be cancelled until (Unit)', 'smart-manager-for-wp-e-commerce' );
						$column['name'] = $column['key'];
						$column = $this->generate_dropdown_col_model( $column, $booking_duration_unit );
						break;
					case '_wc_booking_min_date_unit':
						$column['key'] = __( 'Minimum block bookable (Unit)', 'smart-manager-for-wp-e-commerce' );
						$column['name'] = $column['key'];
						$column = $this->generate_dropdown_col_model( $column, $booking_duration_unit );
						break;
					case '_wc_booking_max_date_unit':
						$column['key'] = __( 'Maximum block bookable (Unit)', 'smart-manager-for-wp-e-commerce' );
						$column['name'] = $column['key'];
						$column = $this->generate_dropdown_col_model( $column, $booking_duration_unit );
						break;
					case '_wc_booking_calendar_display_mode':
						$column['key'] = __( 'Calendar display mode', 'smart-manager-for-wp-e-commerce' );
						$column['name'] = $column['key'];
						$booking_calendar_display_mode = array( ''               => __( 'Display calendar on click', 'smart-manager-for-wp-e-commerce' ),
															'always_visible' => __( 'Calendar always visible', 'smart-manager-for-wp-e-commerce' )
														);
						$column = $this->generate_dropdown_col_model( $column, $booking_calendar_display_mode );
						break;
					case '_wc_booking_resources_assignment':
						$column['key'] = __( 'Resources are...', 'smart-manager-for-wp-e-commerce' );
						$column['name'] = $column['key'];
						$booking_resources_assignment = array( 'customer'  => __( 'Customer selected', 'smart-manager-for-wp-e-commerce' ),
																'automatic' => __( 'Automatically assigned', 'smart-manager-for-wp-e-commerce' )
															);
						$column = $this->generate_dropdown_col_model( $column, $booking_resources_assignment );
						break;
					case '_wc_booking_default_date_availability':
						$column['key'] = __( 'All dates are...', 'smart-manager-for-wp-e-commerce' );
						$column['name'] = $column['key'];
						$booking_resources_date_availability = array( 'available'     => __( 'available by default', 'smart-manager-for-wp-e-commerce' ),
																'non-available' => __( 'not-available by default', 'smart-manager-for-wp-e-commerce' )
															);
						$column = $this->generate_dropdown_col_model( $column, $booking_resources_date_availability );
						break;
					case '_wc_booking_check_availability_against':
						$column['key'] = __( 'Check rules against...', 'smart-manager-for-wp-e-commerce' );
						$column['name'] = $column['key'];
						$booking_resources_date_availability = array(   ''        => __( 'All blocks being booked', 'smart-manager-for-wp-e-commerce' ),
																		'start'   => __( 'The starting block only', 'smart-manager-for-wp-e-commerce' )
																	);
						$column = $this->generate_dropdown_col_model( $column, $booking_resources_date_availability );
						break;
					case '_product_addons':
						$column['editor_schema'] = file_get_contents( SM_PLUGIN_DIR_PATH . '/pro/assets/js/json-schema/product-addons.json' );
						break;
					case '_subscription_period_interval':
						$column['key'] = __( 'Subscription Periods', 'smart-manager-for-wp-e-commerce' );
						$column['name'] = $column['key'];
						$subscription_period_interval = ( function_exists('wcs_get_subscription_period_interval_strings') ) ? wcs_get_subscription_period_interval_strings() : array();
						$column = $this->generate_dropdown_col_model( $column, $subscription_period_interval );
						break;
					case '_subscription_period':
						$column['key'] = $column['name'] = __( 'Billing Period', 'smart-manager-for-wp-e-commerce' );
						$subscription_period = ( function_exists('wcs_get_subscription_period_strings') ) ? wcs_get_subscription_period_strings() : array();
						$column = $this->generate_dropdown_col_model( $column, $subscription_period );
						break;
					case '_subscription_length':
						$column['key'] = __( 'Expire After', 'smart-manager-for-wp-e-commerce' );
						$column['name'] = $column['key'];
						$wcs_subscription_ranges = ( function_exists('wcs_get_subscription_ranges') ) ? wcs_get_subscription_ranges() : array();
						$subscription_ranges = array( __( 'Never expire', 'smart-manager-for-wp-e-commerce' ) );
						if( !empty( $wcs_subscription_ranges['day'] ) ) {
							foreach( $wcs_subscription_ranges['day'] as $key => $values ) {
								if( $key > 0 ) {
									$subscription_ranges[ $key ] = $key .' Renewals';
								}
							}	
						}
						$column = $this->generate_dropdown_col_model( $column, $subscription_ranges );
						break;
					case '_subscription_trial_period':
						$column['key'] = __( 'Subscription Trial Period', 'smart-manager-for-wp-e-commerce' );
						$column['name'] = $column['key'];
						$subscription_time_periods = ( function_exists('wcs_get_available_time_periods') ) ? wcs_get_available_time_periods() : array();
						$column = $this->generate_dropdown_col_model( $column, $subscription_time_periods );
						break;
					case ( !empty( $numeric_columns[ $col_nm ] ) ):
					  		$column['key'] = $numeric_columns[ $col_nm ];
					  		$column['name'] = $column['key'];
					  		$column['type'] = 'numeric';
					  		$column['editor'] = ( in_array( $col_nm, $numeric_text_editor_columns ) ) ? $column['type'] : 'customNumericEditor';
							$column['min'] = 0;
							$column['width'] = 50;
							$column['align'] = 'right';
							break;
					case ( !empty( $checkbox_empty_one_columns[ $col_nm ] ) ):
						$column['key'] = $checkbox_empty_one_columns[ $col_nm ];
						$column['name'] = $column['key'];
						$column['type'] = 'checkbox';
						$column['editor'] = $column['type'];
						$column['checkedTemplate'] = 1;
      					$column['uncheckedTemplate'] = '';
						$column['width'] = 30;
						break;
					case ( !empty( $checkbox_zero_one_columns[ $col_nm ] ) ):
						$column['key'] = $checkbox_zero_one_columns[ $col_nm ];
						$column['name'] = $column['key'];
						$column['type'] = 'checkbox';
						$column['editor'] = $column['type'];
						$column['checkedTemplate'] = 1;
      					$column['uncheckedTemplate'] = 0;
						$column['width'] = 30;
						break;
					case ( !empty( $checkbox_yes_no_columns[ $col_nm ] ) ):
						$column['key'] = $checkbox_yes_no_columns[ $col_nm ];
						$column['name'] = $column['key'];
						$column['type'] = 'checkbox';
						$column['editor'] = $column['type'];
						$column['checkedTemplate'] = 'yes';
      					$column['uncheckedTemplate'] = 'no';
						$column['width'] = 30;
						break;
					// [Germanized for WooCommerce](https://wordpress.org/plugins/woocommerce-germanized/)
					case 'product_unit':
						$column ['type']= 'dropdown';
						$column ['renderer']= 'selectValueRenderer';
						$column ['editable']= false;
						$column ['editor']= 'select';
						$column ['strict'] = true;
						$column ['allowInvalid'] = false;	
						$column ['selectOptions'] = $column['values'];
						break;
					case '_unit':
						$column ['type']= 'dropdown';
						$column ['renderer']= 'selectValueRenderer';
						$column ['editable']= false;
						$column ['editor']= 'select';
						$column ['strict'] = true;
						$column ['allowInvalid'] = false;	
						
						$column ['values'] = $column ['selectOptions'] = array();
						$column ['search_values'] = array();
						if( function_exists( 'WC_Germanized' ) ){
							$wc_germanized = WC_Germanized();
							if( is_callable( array( $wc_germanized, 'plugin_path' ) ) ){
								$column ['values'] = $column ['selectOptions'] = ( file_exists( $wc_germanized->plugin_path() . '/i18n/units.php' ) ) ? include( $wc_germanized->plugin_path() . '/i18n/units.php' ) : array();
								if( ! empty( $column ['values'] ) ){
									foreach( $column ['values'] as $key => $value ) {
										$column['search_values'][] = array( 'key' => $key, 'value' => $value );
									}			
								}
							}
						}
						break;
				}
			}

			if (!empty($dashboard_model_saved)) {
				$col_model_diff = sm_array_recursive_diff($dashboard_model_saved,$dashboard_model);	
			}

			//clearing the transients before return
			if (!empty($col_model_diff)) {
				delete_transient( 'sa_sm_'.$this->dashboard_key );	
			}

			return $dashboard_model;
		}

		public static function products_batch_update_entire_store_ids_query( $query ) {

			global $wpdb;

			$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE 1=%d AND post_type IN ('product', 'product_variation')", 1 );
			return $query;
		}

		public static function products_pre_batch_update_db_updates($args) {

			if( !empty( $args['id'] ) && ( !empty( $args['table_nm'] ) && $args['table_nm'] == 'posts' ) && ( !empty( $args['col_nm'] ) && $args['col_nm'] == 'post_title' ) && !empty( Smart_Manager::$sm_is_woo30 ) ) {
				$results = sm_get_current_variation_title( array($args['id']) );

	            if( count( $results ) > 0 ) {
	                foreach( $results as $result ) {
	                    self::$_instance->variation_product_old_title[ $result['id'] ] = $result['post_title'];
	                }
	            }
			}
		}

		public static function products_post_batch_update_db_updates($update_flag = false, $args = array()) {

			//code for handling updation of price & sales pice in woocommerce
			$price_columns = array( '_regular_price', '_sale_price', '_sale_price_dates_from', '_sale_price_dates_to');
			if ( ! empty( $args['table_nm'] ) && ( 'postmeta' === $args['table_nm'] ) && ( ( ! empty( $args['col_nm'] ) ) && ( true === in_array( $args['col_nm'], $price_columns ) ) ) ) {
				switch ( $args['col_nm'] ) {
					case '_sale_price_dates_from':
						update_post_meta( $args['id'], '_sale_price_dates_from', sa_sm_get_utc_timestamp_from_site_date( $args['value'].' 00:00:00' ) );
						break;
					case '_sale_price_dates_to':
						update_post_meta( $args['id'], '_sale_price_dates_to', sa_sm_get_utc_timestamp_from_site_date( $args['value'].' 23:59:59' ) );
						break;
					// Code to handle setting of 'regular_price' & 'sale_price' in proper way
					case ( empty( $args['operator'] ) || ( ! empty( $args['operator'] ) && ! in_array( $args['operator'], array( 'set_to_regular_price', 'set_to_sale_price', 'copy_from_field' ) ) ) ):
						$regular_price = ( '_regular_price' === $args['col_nm'] ) ? $args['value'] : get_post_meta( $args['id'], '_regular_price', true );
						$sale_price = ( '_sale_price' === $args['col_nm'] ) ? $args['value'] : get_post_meta( $args['id'], '_sale_price', true );	
						if ( $sale_price >= $regular_price ) {
							update_post_meta( $args['id'], '_sale_price', '' );
						}
						break;
				}
				sm_update_price_meta(array($args['id']));
				//Code For updating the parent price of the product
				sm_variable_parent_sync_price(array($args['id']));
				$update_flag = true;
			}

			if ( ! empty( $args['table_nm'] ) && 'postmeta' === $args['table_nm'] && ( ! empty( $args['col_nm'] ) && '_stock' === $args['col_nm'] ) ) { //For handling product inventory updates
                $update_flag = sm_update_stock_status( $args['id'], $args['value'] );
            }

			// Code for 'WooCommerce Product Stock Alert' plugin compat -- triggering `save_post` action
			if ( !empty($args['table_nm']) && $args['table_nm'] == 'postmeta' && (!empty($args['col_nm']) && ( '_stock' === $args['col_nm'] || '_manage_stock' === $args['col_nm'] )) ) {
				sm_update_post( $args['id'] );
            }

			//code to sync the variations title if the variation parent title has been updated
			if( !empty( Smart_Manager::$sm_is_woo30 ) && ( !empty( $args['table_nm'] ) && $args['table_nm'] == 'posts' ) && ( !empty( $args['col_nm'] ) && $args['col_nm'] == 'post_title' ) ) {

				$new_title = ( !empty( $args['value'] ) ) ? $args['value'] : '';

				if( !empty( self::$_instance->variation_product_old_title[ $args['id'] ] ) && self::$_instance->variation_product_old_title[ $args['id'] ] != $new_title ) {
                    $new_title_update_case = 'WHEN post_parent='. $args['id'] .' THEN REPLACE(post_title, \''. self::$_instance->variation_product_old_title[ $args['id'] ] .'\', \''. $new_title .'\')';
                    sm_sync_variation_title( array($new_title_update_case), array($args['id']) );
                }
			}

			if( ( ! empty( $args['table_nm'] ) && 'terms' === $args['table_nm'] ) && ( ! empty( $args['col_nm'] ) && 'product_visibility' === $args['col_nm'] ) ) {
				$val = ( ! empty( $args['value'] ) ) ? $args['value'] : '';
				$update_flag = self::$_instance->set_product_visibility( $args['id'], $val );
			}

			if( ( !empty( $args['table_nm'] ) && $args['table_nm'] == 'terms' ) && ( !empty( $args['col_nm'] ) && $args['col_nm'] == 'product_visibility_featured' ) ) {
				$val = ( !empty( $args['value'] ) ) ? $args['value'] : '';
				$update_flag = ( $val == "Yes" || $val == "yes" ) ? wp_set_object_terms( $args['id'], 'featured', 'product_visibility', true ) : wp_remove_object_terms( $args['id'], 'featured', 'product_visibility' );
			}

			//Code for updating product attributes
			if ( ! empty( $args['table_nm'] ) && 'custom' === $args['table_nm'] && ( !empty( $args['col_nm'] ) && in_array( $args['col_nm'], array( 'product_attributes', 'product_attributes_add', 'product_attributes_remove' ) ) || ( ! empty($args['operator'] ) && in_array( $args['operator'], array( 'add_to', 'remove_from', 'copy_from' ) ) ) ) && ( ! empty( $args['meta']['attributeName'] ) ) ) {
				$action = $args['meta']['attributeName'];
				$current_term_ids = array();

				if( !empty($action) ) {
					delete_transient( 'wc_layered_nav_counts_' . $action );
				}

				$product_attributes = get_post_meta( $args['id'], '_product_attributes', true );

				if( empty( $product_attributes ) || ! is_array( $product_attributes ) ) {
					$product_attributes = array();
				}

				$all_terms_ids = array();

				if( !empty($action) && $action != 'custom' ) {
					$current_term_ids = wp_get_object_terms( $args['id'], $action, 'orderby=none&fields=ids' );

					if( !empty($args['value']) && $args['value'] == 'all' ) { //creating array of all values for the attribute
						$taxonomy_terms = get_terms($action, array('hide_empty'=> 0,'orderby'=> 'id'));

						if (!empty($taxonomy_terms)) {
							foreach ($taxonomy_terms as $term_obj) {
								$all_terms_ids[] = $term_obj->term_id;
							}
						}
					}
				}

				if ( 'add_to' === $args['operator'] ) {

					if( !empty($action) && $action != 'custom' ) {

						if( ( is_array( $current_term_ids ) ) && !in_array($args['value'], $current_term_ids) ) {

							if( $args['value'] != 'all' ) {
								$current_term_ids[] = intval( $args['value'] );
							} else {
								$current_term_ids = $all_terms_ids;
							}

							$update_flag = wp_set_object_terms( $args['id'], $current_term_ids, $action );
						}

						if( empty($product_attributes[$action]) ) {
							$product_attributes[$action] = array( 'name' => $action,
															            'value' => '',
															            'position' => 1,
															            'is_visible' => 1,
															            'is_variation' => 0,
															            'is_taxonomy' => 1 );
						}

					} else if( !empty($action) && $action == 'custom' ) {

						$value = ( (!empty($args['value2'])) ? $args['value2'] : '' );

						if( !empty($product_attributes[$args['value']]) ) {
							$product_attributes[$args['value']]['value'] = $value;
						} else {
							$product_attributes[$args['value']] = array( 'name' => $args['value'],
															            'value' => $value,
															            'position' => 1,
															            'is_visible' => 1,
															            'is_variation' => 0,
															            'is_taxonomy' => 0 );
						}

						$update_flag = true;

					}

				} else if ( 'remove_from' === $args['operator'] ) {
					if( !empty($action) && $action != 'custom' ) {

						$all = ( !empty($args['value']) && $args['value'] == 'all') ? true : false;

						$key = array_search($args['value'], $current_term_ids);

						if( $key !== false ) {
							unset($current_term_ids[$key]);
							$update_flag = wp_set_object_terms( $args['id'], $current_term_ids, $action );
						} else if( $all === true ) {
							$update_flag = wp_set_object_terms( $args['id'], array(), $action );
						}

						if( (count($current_term_ids) == 0 || $all === true) && !empty($product_attributes[$action]) ) {
							unset($product_attributes[$action]);
						}
					}
				} else if ( 'copy_from' === $args['operator'] && ( ! empty( $args['selected_value'] ) ) && ( ! empty( $args['id'] ) ) ) {
					if ( ! empty( $args['value'] ) && is_array( $args['value'] ) ) {
						foreach ( $args['value'] as $action => $value ) {
							wp_set_object_terms( $args['id'], array(), $action );
							$current_term_ids = wp_get_object_terms( $args['selected_value'], $action, 'orderby=none&fields=ids' );
							$update_flag = wp_set_object_terms( $args['id'], $current_term_ids, $action );
							if ( empty( $product_attributes[ $action ] ) ) {
								$product_attributes[ $action ] = array( 'name' => $action,
																       'value' => '',
																       'position' => 1,
																       'is_visible' => 1,
																       'is_variation' => 0,
																        'is_taxonomy' => 1 );
							}

						}
					}

				}

				update_post_meta( $args['id'], '_product_attributes', $product_attributes );
				sm_update_product_attribute_lookup_table( array( $args['id'] ) );
			}

			//Code for updating product categories
			if( !empty($args['table_nm']) && $args['table_nm'] == 'custom' && (!empty($args['col_nm']) && strpos($args['col_nm'], 'product_cat') !== false ) ) {

				$action = ( !empty($args['operator']) ) ? $args['operator'] : '';
				$value = ( !empty($args['value']) ) ? intval( $args['value'] ) : 0;
				$taxonomy_nm = 'product_cat';
				$current_term_ids = array();

				if( !empty($action) && $action != 'set_to' ) {
					$current_term_ids = wp_get_object_terms( $args['id'], $taxonomy_nm, 'orderby=none&fields=ids' );

					if( $action == 'add_to' ) {
						$current_term_ids[] = $value;						
					} else if( $action == 'remove_from' ) {
						$key = array_search($value, $current_term_ids);
						if( $key !== false ) {
							unset($current_term_ids[$key]);
						}
					}
					
				} else if( !empty($action) && $action == 'set_to' ) {
					$current_term_ids = array( $value );
				}

				$update_flag = wp_set_object_terms( $args['id'], $current_term_ids, $taxonomy_nm );

			}


			//product clear_caches
			clean_post_cache( $args['id'] );
			wc_delete_product_transients( $args['id'] );
			if ( class_exists( 'WC_Cache_Helper' ) ) {
				( !empty( Smart_Manager::$sm_is_woo39 ) && is_callable( array('WC_Cache_Helper', 'invalidate_cache_group') ) ) ? WC_Cache_Helper::invalidate_cache_group( 'product_' . $args['id'] ) : WC_Cache_Helper::incr_cache_prefix( 'product_' . $args['id'] );
			}

			do_action( 'woocommerce_update_product', $args['id'], wc_get_product( $args['id'] ) );

			return ( ( ! empty( $update_flag ) ) && ( ! is_wp_error( $update_flag ) ) ) ? true : false;

		}

		//function to process duplicate products logic
		public static function process_duplicate_record( $params ) {

			$original_id = ( !empty( $params['id'] ) ) ? $params['id'] : '';

			//code for processing logic for duplicate products
			if( empty( $original_id ) ) {
				return false;
			}
			
			$identifier = '';

			if ( is_callable( array( 'Smart_Manager_Pro_Background_Updater', 'get_identifier' ) ) ) {
				$identifier = Smart_Manager_Pro_Background_Updater::get_identifier();
			}

			if( empty( $identifier ) ) {
				return;
			}

			$background_process_params = get_option( $identifier.'_params', false );
			
			if( empty( $background_process_params ) ) {
				return;
			}

			do_action('sm_beta_pre_process_duplicate_products', $original_id );

			$product = wc_get_product( $original_id );

            $parent_id = 0;
            $woo_dup_obj = '';
            $dup_prod_id = '';
            
            if( !empty( $background_process_params ) && (!empty( $background_process_params['SM_IS_WOO30'] ) || !empty( $background_process_params['SM_IS_WOO22'] ) || !empty( $background_process_params['SM_IS_WOO21'] ) ) ) {
                $parent_id = wp_get_post_parent_id($original_id);

                $file = WP_PLUGIN_DIR . '/woocommerce/includes/admin/class-wc-admin-duplicate-product.php';
                if( file_exists( $file ) ) {
                	include_once ( $file ); // for handling the duplicate product functionality
                }

                if ( class_exists( 'WC_Admin_Duplicate_Product' ) ) {
                	$woo_dup_obj = new WC_Admin_Duplicate_Product();
                }
                
            } else {

            	$file = WP_PLUGIN_DIR . '/woocommerce/admin/includes/duplicate_product.php';
                if( file_exists( $file ) ) {
                	include_once ( $file ); // for handling the duplicate product functionality
                }

                $post = get_post( $original_id );
                $parent_id = $post->post_parent;    
            }

            if ($parent_id == 0) {
                
                if ($woo_dup_obj instanceof WC_Admin_Duplicate_Product) {
                    if( !empty( $background_process_params ) && !empty( $background_process_params['SM_IS_WOO30'] ) ) {

                        $product = wc_get_product( $original_id );

                        $dup_prod = $woo_dup_obj->product_duplicate( $product );

                        if( !is_wp_error($dup_prod) ) {
                        	$dup_prod_id = $dup_prod->get_id();
                        }
                        

                    } else {
                        $dup_prod_id = $woo_dup_obj -> duplicate_product($post,0,$post->post_status);
                    }
                } else {
                    $dup_prod_id = woocommerce_create_duplicate_from_product($post,0,$post->post_status); //TODO check
                }

                //Code for updating the post name
                if( !empty( $background_process_params ) && empty( $background_process_params['SM_IS_WOO30'] ) ) {
                    $new_slug = sanitize_title( get_the_title($dup_prod_id) );
                    wp_update_post(
                                        array (
                                            'ID'        => $dup_prod_id,
                                            'post_name' => $new_slug
                                        )
                                    );
                }

            }

            if( is_wp_error($dup_prod_id) ) {
				return false;
			} else {
				return true;
			}
		}
		/**
		* Get previous values for the taxonomy
		*
		* @param string $prev_val previous value for current taxonomy 
		* @param array $args args has id, column name and table name 
		* @return result of function call or empty value
		*/ 
		public static function products_batch_update_prev_value( $prev_val = '', $args = array() ) {
			if ( 'custom' === $args['table_nm'] && 'product_attributes' === $args['col_nm'] && !empty( $args['meta']['attributeName'] ) ) {
				$result = wp_get_object_terms( $args['id'], $args['meta']['attributeName'], 'orderby=none&fields=ids' );
				return ( ( ! empty( $result ) ) && ( ! is_wp_error( $result ) ) ) ? $result : $prev_val;
			}
			return $prev_val;
		}


		/**
		* Update update_task_details_params param by using previous value
		*
		* @param array $args args has array of task details update values
		* @return void
		*/ 
		public static function task_details_update_by_prev_val( $args = array() ) {
			if (  empty( $args ) ) {
				return $args;
			}
			$field_name = '';
			foreach ( $args as $arg ) {
				if ( ! empty( $arg['prev_val'] ) && is_array( $arg['prev_val'] ) ) {
					foreach ( $arg['prev_val'] as $key => $prev_val ) {
						switch (true) {
							case empty( $prev_val ):
								$field_name = 'custom/product_attributes_add';
								break;
							case ( ! empty( $prev_val ) && ( empty( in_array( $arg['updated_val'], $arg['prev_val'] ) ) && ( 'all' === $arg['updated_val'] && ( 'add_to' === $arg['operator'] ) ) ) ):
								$field_name = 'custom/product_attributes_remove';
								break;
							case ( ! empty( $prev_val ) && ! empty( in_array( $arg['updated_val'], $arg['prev_val'] ) ) ):
								$field_name = 'custom/product_attributes_add';
								break;
							case ( ! empty( $prev_val ) && ( empty( in_array( $arg['updated_val'], $arg['prev_val'] ) ) || ( 'all' === $arg['updated_val'] && ( 'remove_from' === $arg['operator'] ) ) ) ):
								$field_name = 'custom/product_attributes_add';
								break;
						}
						if ( ( ! empty( $arg['task_id'] ) ) ) {
							Smart_Manager_Base::$update_task_details_params[] = array(
								'task_id' => $arg['task_id'],
								'action' => $arg['action'],
								'status' => $arg['status'],
								'record_id' => $arg['record_id'],
								'field' => $field_name,   
								'prev_val' => $prev_val,
								'updated_val' => $arg['updated_val'],
							); 
						}
					}
				} else if ( empty( $arg['prev_val'] ) && ! is_array( $arg['prev_val'] ) ) {
					$field_name = 'custom/product_attributes_remove';
					if ( ( ! empty( $arg['task_id'] ) ) ) {
						Smart_Manager_Base::$update_task_details_params[] = array(
							'task_id' => $arg['task_id'],
							'action' => $arg['action'],
							'status' => $arg['status'],
							'record_id' => $arg['record_id'],
							'field' => $field_name,   
							'prev_val' => $arg['updated_val'],
							'updated_val' => $arg['updated_val'],
						); 
					}
				}
			}
		}

		/**
		* Disable task details update
		*
		* @param array $prev_val prev_val has previous value
		* @param string $field_nm field name 
		* @return boolean 
		*/ 
		public static function disable_task_details_update( $prev_val = array(), $field_nm = '' ) {
			return ( ( ! empty( $prev_val ) ) && ( 'postmeta/meta_key=_product_attributes/meta_value=_product_attributes' === $field_nm ) ) ? true : false;
		}

		/**
		* Get value for copy from operator
		*
		* @param string $new_val new value
		* @param array $args array of selected field, operator and value
		* @return array value of selected column 
		*/ 
		public static function get_value_for_copy_from_operator( $new_val = '', $args = array() ) {
			if ( empty( $args['selected_column_name'] ) || ( 'product_attributes' !== $args['selected_column_name'] ) || empty( intval( $args['selected_value'] ) ) ) {
				return $new_val;
			}
			return get_post_meta( $args['selected_value'], '_product_attributes', true );
		}
		
		/**
		* Update value for copy from operator
		*
		* @param array $args array of selected field, operator and value
		* @return boolean 
		*/ 
		public static function update_value_for_copy_from_operator( $args = array() ) {
			if ( empty( $args['id'] ) || ( 'product_attributes' !== $args['col_nm'] ) || ( ! isset( $args['value'] ) ) ) {
				return false;
			}
			return update_post_meta( $args['id'], '_product_attributes', $args['value'] );
		}

		/**
		* Update task args before processing undo
		*
		* @param array $args array of selected field, operator and value
		* @return array $args array of updated args 
		*/ 
		public static function process_undo_args_before_update( $args = array() ) {
			if ( empty( $args['type'] ) || empty( $args['operator'] ) || ( ! in_array( $args['type'], array( 'custom/product_attributes_add', 'custom/product_attributes_remove' ) ) ) ) {
				return $args;
			}
			if ( empty( $args['meta']['attributeName'] ) ) {
				$args['meta']['attributeName'] = $args['operator'];
			}
			switch ( $args['type'] ) {
				case 'custom/product_attributes_add':
					$args['operator'] = 'add_to';
					break;
				case 'custom/product_attributes_remove':
					$args['operator'] = 'remove_from';
					break;
			}
			$args['type'] = 'custom/product_attributes';
			return $args;
		}

		/**
		* Update task action
		*
		* @param string $operator operator name
		* @param array $args array of field, operator and value
		* @return string action name 
		*/ 
		public static function task_update_action( $operator = '', $args = array() ) {
			if ( empty( $args['type'] ) || ( 'custom/product_attributes' !== $args['type'] ) || empty( $operator ) || empty( $args['meta']['attributeName'] ) ) {
				return $operator;
			}
			return $args['meta']['attributeName'];
		}

		/**
		* Get gallery images post ids
		*
		* @param array $attached_media_post_ids attached media post ids
		* @param array $args array of post id and attachment id
		* @return array $attached_media_post_ids Updated value of attached media post ids
		*/ 
		public static function get_matching_gallery_images_post_ids( $attached_media_post_ids = array(), $args = array() ) {
			if ( ! is_array( $attached_media_post_ids ) || empty( $args ) || ! is_array( $args ) || empty( $args['post_id'] ) || empty( $args['attachment_id'] ) ) {
				return $attached_media_post_ids;
			}
			global $wpdb;
			$results = $wpdb->get_col(
				$wpdb->prepare( "SELECT DISTINCT post_id 
				FROM {$wpdb->prefix}postmeta WHERE post_id <> %d AND meta_key = %s AND meta_value REGEXP %s", $args['post_id'], '_product_image_gallery', $args['attachment_id'] )
			); // Improve REGEXP.
			if ( empty( $results ) || ! is_array( $results ) ) {
				return $attached_media_post_ids;
			}
			return array_merge( $attached_media_post_ids, $results );
		}

	} //End of Class
}
