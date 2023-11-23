<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Smart_Manager_Pro_Base' ) ) {
	class Smart_Manager_Pro_Base extends Smart_Manager_Base {

		public $dashboard_key = '';

		protected static $sm_beta_background_updater;
		protected static $sm_beta_background_updater_action;

		function __construct($dashboard_key) {
			$this->dashboard_key = $dashboard_key;
			parent::__construct($dashboard_key);
			self::$sm_beta_background_updater = Smart_Manager_Pro_Background_Updater::instance();
			$this->advance_search_operators = array_merge( $this->advance_search_operators, array( 
				'startsWith' => 'like',
				'endsWith' => 'like',
				'anyOf' => 'like',
				'notStartsWith' => 'not like',
				'notEndsWith' => 'not like',
				'notAnyOf' => 'not like'
			 ) );

			add_filter( 'sm_dashboard_model', array( &$this, 'pro_dashboard_model' ), 11, 2 );
			add_filter( 'sm_data_model', array( &$this, 'pro_data_model' ), 11, 2);
			add_filter( 'sm_inline_update_pre', array( &$this, 'pro_inline_update_pre' ), 11, 1);
			add_filter( 'sm_default_dashboard_model_postmeta_cols', array( &$this, 'pro_custom_postmeta_cols' ), 11, 1 );

			// Code for handling of `starts with/ends with` advanced search operators
			$advanced_search_filter_tables = array( 'posts', 'postmeta', 'terms' );
			switch(  $this->advanced_search_table_types ) {
				case ( ! empty( $this->advanced_search_table_types['flat'] ) && ! empty( $this->advanced_search_table_types['meta'] ) ):
					$advanced_search_filter_tables = array_merge( array_merge( array_keys( $this->advanced_search_table_types['flat'] ), array_keys( $this->advanced_search_table_types['meta'] ) ), array( 'terms' ) );
					break;
				case ( ! empty( $this->advanced_search_table_types['flat'] ) && empty( $this->advanced_search_table_types['meta'] ) ):
					$advanced_search_filter_tables = array_merge( array_keys( $this->advanced_search_table_types['flat'] ), array( 'terms' ) );
					break;
				case ( empty( $this->advanced_search_table_types['flat'] ) && ! empty( $this->advanced_search_table_types['meta'] ) ):
					$advanced_search_filter_tables = array_merge( array_keys( $this->advanced_search_table_types['meta'] ), array( 'terms' ) );
					break;
			}
			if( ! empty( $advanced_search_filter_tables ) && is_array( $advanced_search_filter_tables ) ){
				foreach( $advanced_search_filter_tables as $table ){
					add_filter( 'sm_search_format_query_' . $table . '_col_value', array( &$this, 'format_search_value' ), 11, 2 );
					add_filter( 'sm_search_'. $table .'_cond', array( &$this, 'modify_search_cond' ), 11, 2 );
				}
			}
			add_filter(
				'sm_get_process_names_for_adding_tasks',
				function( $process_name = '' ) {
					if( empty( $process_name ) ) {
						return;
					}
					return array(
						'Bulk Edit',
					);
				}
			);
			if ( 'yes' === Smart_Manager_Settings::get( 'delete_media_when_permanently_deleting_post_type_records' ) ) {
				add_action( 'before_delete_post', array( &$this, 'delete_attached_media' ), 11, 2 );
			}
		}

		public function get_yoast_meta_robots_values() {
			return array( '-'            => __( 'Site-wide default', 'smart-manager-for-wp-e-commerce' ),
						'none'         => __( 'None', 'smart-manager-for-wp-e-commerce' ),
						'noimageindex' => __( 'No Image Index', 'smart-manager-for-wp-e-commerce' ),
						'noarchive'    => __( 'No Archive', 'smart-manager-for-wp-e-commerce' ),
						'nosnippet'    => __( 'No Snippet', 'smart-manager-for-wp-e-commerce' ) );
		}

		public function get_rankmath_robots_values() {
			return array( 'index'      => __( 'Index', 'smart-manager-for-wp-e-commerce' ),
						'noindex'      => __( 'No Index', 'smart-manager-for-wp-e-commerce' ),
						'nofollow'     => __( 'No Follow', 'smart-manager-for-wp-e-commerce' ),
						'noarchive'    => __( 'No Archive', 'smart-manager-for-wp-e-commerce' ),
						'noimageindex' => __( 'No Image Index', 'smart-manager-for-wp-e-commerce' ),
						'nosnippet'    => __( 'No Snippet', 'smart-manager-for-wp-e-commerce' ) );
		}

		public function get_rankmath_seo_score_class( $score ) {
			if ( $score > 80 ) {
				return 'great';
			}

			if ( $score > 51 && $score < 81 ) {
				return 'good';
			}

			return 'bad';
		}

		//Filter to add custom columns
		public function pro_custom_postmeta_cols( $postmeta_cols ) {

			$yoast_pm_cols = $rank_math_pm_cols = array();

			$active_plugins = (array) get_option( 'active_plugins', array() );

			if ( is_multisite() ) {
				$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
			}

			if ( ( in_array( 'wordpress-seo/wp-seo.php', $active_plugins, true ) || array_key_exists( 'wordpress-seo/wp-seo.php', $active_plugins ) ) ) {
				$yoast_pm_cols = array('_yoast_wpseo_metakeywords','_yoast_wpseo_title','_yoast_wpseo_metadesc','_yoast_wpseo_meta-robots-noindex','_yoast_wpseo_primary_product_cat','_yoast_wpseo_focuskw_text_input','_yoast_wpseo_linkdex','_yoast_wpseo_focuskw','_yoast_wpseo_redirect','_yoast_wpseo_primary_category','_yoast_wpseo_content_score','_yoast_wpseo_meta-robots-nofollow','_yoast_wpseo_primary_kbe_taxonomy','_yoast_wpseo_opengraph-title','_yoast_wpseo_opengraph-description','_yoast_wpseo_primary_wpm-testimonial-category','_yoast_wpseo_twitter-title','_yoast_wpseo_twitter-description', '_yoast_wpseo_opengraph-image', '_yoast_wpseo_opengraph-image-id', '_yoast_wpseo_twitter-image', '_yoast_wpseo_twitter-image-id', '_yoast_wpseo_focuskeywords');
			}

			if( !empty( $yoast_pm_cols ) ) {
				foreach( $yoast_pm_cols as $meta_key ) {
					if( !isset( $postmeta_cols[ $meta_key ] ) ) {
						$postmeta_cols[ $meta_key ] = array( 'meta_key' => $meta_key, 'meta_value' => '' );
					}
				}	
			}

			if ( ( in_array( 'seo-by-rank-math/rank-math.php', $active_plugins, true ) || array_key_exists( 'seo-by-rank-math/rank-math.php', $active_plugins ) ) ) {
				$rank_math_pm_cols = array('rank_math_title','rank_math_description','rank_math_focus_keyword','rank_math_canonical_url','rank_math_facebook_title','rank_math_facebook_description','rank_math_twitter_title','rank_math_twitter_description','rank_math_breadcrumb_title', 'rank_math_robots', 'rank_math_seo_score', 'rank_math_facebook_image', 'rank_math_twitter_image_id', 'rank_math_twitter_image', 'rank_math_twitter_image_id', 'rank_math_primary_product_cat');
			}

			if( !empty( $rank_math_pm_cols ) ) {
				foreach( $rank_math_pm_cols as $meta_key ) {
					if( !isset( $postmeta_cols[ $meta_key ] ) ) {
						$postmeta_cols[ $meta_key ] = array( 'meta_key' => $meta_key, 'meta_value' => '' );
					}
				}	
			}

			return $postmeta_cols;
		}

		//Function to handle custom fields common in more than 1 post type
		public function pro_dashboard_model( $dashboard_model, $dashboard_model_saved ) {

			$colum_name_titles = array( 	'_yoast_wpseo_title' => __( 'Yoast SEO Title', 'smart-manager-for-wp-e-commerce' ), 
					 						'_yoast_wpseo_metadesc' => __( 'Yoast Meta Description', 'smart-manager-for-wp-e-commerce' ), 
					 						'_yoast_wpseo_metakeywords' => __( 'Yoast Meta Keywords', 'smart-manager-for-wp-e-commerce' ), 
					 						'_yoast_wpseo_focuskw' => __( 'Yoast Focus Keyphrase', 'smart-manager-for-wp-e-commerce' ), 
			 						);

			$html_columns = array( '_yoast_wpseo_content_score' => __( 'Yoast Readability Score', 'smart-manager-for-wp-e-commerce' ),
									'_yoast_wpseo_linkdex' => __( 'Yoast SEO Score', 'smart-manager-for-wp-e-commerce' ),
									'rank_math_seo_score' => __( 'Rank Math SEO Score', 'smart-manager-for-wp-e-commerce' ) );

			$product_cat_index = sm_multidimesional_array_search('terms_product_cat', 'data', $dashboard_model['columns']);

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

				switch( $col_nm ) {
					case '_yoast_wpseo_meta-robots-noindex':
						$column['key'] = $column['name'] = sprintf( 
							/* translators: %1$s: dashboard title */
							__( 'Allow search engines to show this %1$s in search results?', 'smart-manager-for-wp-e-commerce' ), rtrim( $this->dashboard_title, 's' ) );
						$yoast_noindex = array( '0' => __( 'Default', 'smart-manager-for-wp-e-commerce'),
														'2' => __( 'Yes', 'smart-manager-for-wp-e-commerce' ),
														'1' => __( 'No', 'smart-manager-for-wp-e-commerce' ) );

						$column = $this->generate_dropdown_col_model( $column, $yoast_noindex );
						break;

					case '_yoast_wpseo_meta-robots-nofollow':
						$column['key'] = $column['name'] = sprintf(
							/* translators: %1$s: dashboard title */
							__( 'Should search engines follow links on this %1$s?', 'smart-manager-for-wp-e-commerce' ), rtrim( $this->dashboard_title, 's' ) );
						$yoast_nofollow = array('0' => __( 'Yes', 'smart-manager-for-wp-e-commerce' ),
												'1' => __( 'No', 'smart-manager-for-wp-e-commerce' ) );

						$column = $this->generate_dropdown_col_model( $column, $yoast_nofollow );
						break;
					case '_yoast_wpseo_meta-robots-adv':
						$column['key'] = $column['name'] = __( 'Meta robots advanced', 'smart-manager-for-wp-e-commerce' );
						$values = $this->get_yoast_meta_robots_values();
						$column = $this->generate_multilist_col_model( $column, $values );
						break;
					case 'rank_math_robots':
						$column['key'] = $column['name'] = __( 'Robots Meta', 'smart-manager-for-wp-e-commerce' );
						$values = $this->get_rankmath_robots_values();
						$column = $this->generate_multilist_col_model( $column, $values );
						break;
					case ($col_nm == '_yoast_wpseo_primary_product_cat' || $col_nm == 'rank_math_primary_product_cat'):

						$product_cat_values = array();

						$taxonomy_terms = get_terms('product_cat', array('hide_empty'=> 0,'orderby'=> 'id'));
						

						if( !empty( $taxonomy_terms ) ) {
							foreach ($taxonomy_terms as $term_obj) {
								$product_cat_values[$term_obj->term_id] = array();
								$product_cat_values[$term_obj->term_id]['term'] = $term_obj->name;
								$product_cat_values[$term_obj->term_id]['parent'] = $term_obj->parent;
							}
						}

						$values = $parent_cat_term_ids = array();
						foreach( $product_cat_values as $term_id => $obj ) {

							$values[ $term_id ] = $obj['term'];

							if( !empty( $obj['parent'] ) ) {
								$values[ $term_id ] = ( ! empty( $product_cat_values[ $obj['parent'] ] ) ) ? $product_cat_values[ $obj['parent'] ]['term']. ' > ' .$values[ $term_id ] : $values[ $term_id ];
								if( in_array( $obj['parent'], $parent_cat_term_ids ) === false ) {
									$parent_cat_term_ids[] = $obj['parent'];
								}
							}
						}

						//Code for unsetting the parent category ids
						if( !empty( $parent_cat_term_ids ) ) {
							foreach( $parent_cat_term_ids as $parent_id ) {
								if( isset( $values[ $parent_id ] ) ) {
									unset( $values[ $parent_id ] );
								}
							}
						}

						$column = $this->generate_dropdown_col_model( $column, $values );
						break;
					case ( !empty( $colum_name_titles[ $col_nm ] ) ):
						$column['key'] = $column['name'] = $colum_name_titles[ $col_nm ];
						break;
					case ( !empty( $html_columns[ $col_nm ] ) ):
						$column['key'] = $column['name'] = $html_columns[ $col_nm ];
						$column['type'] = 'text';
						$column['renderer']= 'html';
						$column['frozen'] = false;
						$column['sortable'] = false;
						$column['exportable'] = true;
						$column['searchable'] = false;
						$column['editable'] = false;
						$column['editor'] = false;
						$column['batch_editable'] = false;
						$column['hidden'] = true;
						$column['allow_showhide'] = true;
						$column['width'] = 200;
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

		public function pro_data_model ($data_model, $data_col_params) {

			if( !class_exists('WPSEO_Rank') && file_exists( WP_PLUGIN_DIR. '/wordpress-seo/inc/class-wpseo-rank.php' ) ) {
				include_once WP_PLUGIN_DIR. '/wordpress-seo/inc/class-wpseo-rank.php';
			}

			if( empty( $data_model['items'] ) ) {
				return $data_model;
			}

			foreach ($data_model['items'] as $key => $data) {
				if (empty($data['posts_id'])) continue;

				//Code for handling data for Yoast Readability Score
				if( !empty( $data['postmeta_meta_key__yoast_wpseo_content_score_meta_value__yoast_wpseo_content_score'] ) && is_callable( array( 'WPSEO_Rank', 'from_numeric_score' ) ) ) {

					$rank  = WPSEO_Rank::from_numeric_score( (int)$data['postmeta_meta_key__yoast_wpseo_content_score_meta_value__yoast_wpseo_content_score'] );
					$title = $rank->get_label();
					$data_model['items'][$key]['postmeta_meta_key__yoast_wpseo_content_score_meta_value__yoast_wpseo_content_score'] = '<div aria-hidden="true" title="' . esc_attr( $title ) . '" class="wpseo-score-icon ' . esc_attr( $rank->get_css_class() ) . '"></div><span class="screen-reader-text wpseo-score-text">' . $title . '</span>';
				}

				//Code for handling data for Yoast SEO Score
				if( !empty( $data['postmeta_meta_key__yoast_wpseo_linkdex_meta_value__yoast_wpseo_linkdex'] ) && is_callable( array( 'WPSEO_Rank', 'from_numeric_score' ) ) ) {

					$rank  = WPSEO_Rank::from_numeric_score( (int)$data['postmeta_meta_key__yoast_wpseo_linkdex_meta_value__yoast_wpseo_linkdex'] );
					$title = $rank->get_label();
					$data_model['items'][$key]['postmeta_meta_key__yoast_wpseo_linkdex_meta_value__yoast_wpseo_linkdex'] = '<div aria-hidden="true" title="' . esc_attr( $title ) . '" class="wpseo-score-icon ' . esc_attr( $rank->get_css_class() ) . '"></div><span class="screen-reader-text wpseo-score-text">' . $title . '</span>';
				}

				//Code for handling Yoast Meta Robots
				if( isset( $data['postmeta_meta_key__yoast_wpseo_meta-robots-adv_meta_value__yoast_wpseo_meta-robots-adv'] ) ) {
					$actual_values = $this->get_yoast_meta_robots_values();
					if( !empty( $data['postmeta_meta_key__yoast_wpseo_meta-robots-adv_meta_value__yoast_wpseo_meta-robots-adv'] ) ) {

						$current_values = explode( ',', $data['postmeta_meta_key__yoast_wpseo_meta-robots-adv_meta_value__yoast_wpseo_meta-robots-adv'] );

						$formatted_value = array();

						foreach( $current_values as $value ) {

							if( !empty( $actual_values[ $value ] ) ) {
								$formatted_value[] = $actual_values[ $value ];
							}
						}

						$data_model['items'][$key]['postmeta_meta_key__yoast_wpseo_meta-robots-adv_meta_value__yoast_wpseo_meta-robots-adv'] = implode(', <br>', $formatted_value);
					} else {
						$data_model['items'][$key]['postmeta_meta_key__yoast_wpseo_meta-robots-adv_meta_value__yoast_wpseo_meta-robots-adv'] = $actual_values['-'];
					}	
				}

				//Code for handling Yoast Meta Robots
				if( isset( $data['postmeta_meta_key_rank_math_robots_meta_value_rank_math_robots'] ) ) {
					$actual_values = $this->get_rankmath_robots_values();
					if( !empty( $data['postmeta_meta_key_rank_math_robots_meta_value_rank_math_robots'] ) ) {

						$current_values = maybe_unserialize( $data['postmeta_meta_key_rank_math_robots_meta_value_rank_math_robots'] );

						$formatted_value = array();

						foreach( $current_values as $value ) {

							if( !empty( $actual_values[ $value ] ) ) {
								$formatted_value[] = $actual_values[ $value ];
							}
						}

						$data_model['items'][$key]['postmeta_meta_key_rank_math_robots_meta_value_rank_math_robots'] = implode(', <br>', $formatted_value);
					} else {
						$data_model['items'][$key]['postmeta_meta_key_rank_math_robots_meta_value_rank_math_robots'] = $actual_values['index'];
					}
				}

				//Code for handling data for Rank Math SEO Score
				if( isset( $data['postmeta_meta_key_rank_math_seo_score_meta_value_rank_math_seo_score'] ) ) {

					$score = ( !empty( $data['postmeta_meta_key_rank_math_seo_score_meta_value_rank_math_seo_score'] ) ) ? $data['postmeta_meta_key_rank_math_seo_score_meta_value_rank_math_seo_score'] : 0;
					$class     = $this->get_rankmath_seo_score_class( $score );
					$score = $score . ' / 100';

					$data_model['items'][$key]['postmeta_meta_key_rank_math_seo_score_meta_value_rank_math_seo_score'] = '<span class="rank-math-seo-score '.$class.'">
						<strong>'.$score.'</strong></span>';
				}
			}

			return $data_model;
		}

		public function pro_inline_update_pre( $edited_data ) {
			if (empty($edited_data)) return $edited_data;

			foreach ($edited_data as $id => $edited_row) {

				if( empty( $id ) ) {
					continue;
				}

				//Code for handling Yoast SEO meta robots editing
				if( !empty( $edited_row['postmeta/meta_key=_yoast_wpseo_meta-robots-adv/meta_value=_yoast_wpseo_meta-robots-adv'] ) ) {
					$actual_values = $this->get_yoast_meta_robots_values();
					$current_values = explode( ', <br>', $edited_row['postmeta/meta_key=_yoast_wpseo_meta-robots-adv/meta_value=_yoast_wpseo_meta-robots-adv'] );

					$formatted_value = array();

					foreach( $current_values as $value ) {

						$key = array_search( $value, $actual_values );

						if( $key !== false ) {
							$formatted_value[] = $key;
						}
					}

					$edited_data[$id]['postmeta/meta_key=_yoast_wpseo_meta-robots-adv/meta_value=_yoast_wpseo_meta-robots-adv'] = implode(',', $formatted_value);
				}

				// Code for handling Rank Math robots editing
				if( !empty( $edited_row['postmeta/meta_key=rank_math_robots/meta_value=rank_math_robots'] ) ) {
					$actual_values = $this->get_yoast_meta_robots_values();
					$current_values = explode( ', <br>', $edited_row['postmeta/meta_key=rank_math_robots/meta_value=rank_math_robots'] );
					$formatted_value = array();

					foreach( $current_values as $value ) {

						$key = array_search( $value, $actual_values );

						if( $key !== false ) {
							$formatted_value[] = $key;
						}
					}

					$edited_data[$id]['postmeta/meta_key=rank_math_robots/meta_value=rank_math_robots'] = $formatted_value;
				}

			}

			return $edited_data;
		}

		public function generate_multilist_col_model( $colObj, $values = array() ) {
			
			$colObj ['values'] = array();

			foreach( $values as $key => $value ) {
				$colObj ['values'][$key] = array( 'term' => $value, 'parent' => 0 );
			}

			//code for handling values for advanced search
			$colObj['search_values'] = array();
			foreach( $values as $key => $value ) {
				$colObj['search_values'][] = array( 'key' => $key, 'value' => $value );
			}

			$colObj ['type'] = $colObj ['editor'] = 'sm.multilist';
			$colObj ['strict'] 			= true;
			$colObj ['allowInvalid'] 	= false;
			$colObj ['editable']		= false;

			return $colObj;
		}

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

		public function get_entire_store_ids() {

			global $wpdb;

			$selected_ids = array();

			if( !empty( $this->req_params['filteredResults'] ) ) {
				$post_ids = get_transient('sa_sm_search_post_ids');
				$selected_ids = ( !empty( $post_ids ) ) ? explode( ",", $post_ids ) : array();
			} else {

				$post_type = (!empty($this->req_params['table_model']['posts']['where'])) ? $this->req_params['table_model']['posts']['where'] : array('post_type' => $this->dashboard_key);

				if( !empty( $this->req_params['table_model']['posts']['where']['post_type'] ) ) {
            		$post_type = ( is_array( $this->req_params['table_model']['posts']['where']['post_type'] ) ) ? $this->req_params['table_model']['posts']['where']['post_type'] : array( $this->req_params['table_model']['posts']['where']['post_type'] );
            	}

				$from = " FROM {$wpdb->prefix}posts ";
				$where = " WHERE post_type IN ('". implode( "','", $post_type ) ."') ";

				$update_trash_records = apply_filters( 'sm_update_trash_records', ( 'yes' === get_option( 'sm_update_trash_records', 'no' ) ) );
				if( empty( $update_trash_records ) && ( is_callable( array( $this, 'is_show_trash_records' ) ) && empty( $this->is_show_trash_records() ) ) ){
					$where .= " AND post_status != 'trash'";
				}
				
				$from	= apply_filters('sm_beta_background_entire_store_ids_from', $from, $this->req_params);
				$where	= apply_filters('sm_beta_background_entire_store_ids_where', $where, $this->req_params);
				
				$query = apply_filters( 'sm_beta_background_entire_store_ids_query', $wpdb->prepare( "SELECT ID ". $from ." ". $where ." AND 1=%d", 1 ) );
				$selected_ids = $wpdb->get_col( $query );
			}

			return $selected_ids;
		}

		//function to handle batch update request
		public function batch_update() {
			global $wpdb, $current_user;
			$col_data_type = self::get_column_data_type( $this->dashboard_key ); // For fetching column data type		
			$batch_update_actions = (!empty($this->req_params['batch_update_actions'])) ? json_decode(stripslashes($this->req_params['batch_update_actions']), true) : array();
			$dashboard_key = $this->dashboard_key; //fix for PHP 5.3 or earlier	
			$batch_update_actions = array_map( function( $batch_update_action ) use ( $dashboard_key, $col_data_type ) {
				$batch_update_action['dashboard_key'] = $dashboard_key;
				$batch_update_action['date_type'] = ( ! empty( $col_data_type[$batch_update_action['type']] ) ) ? $col_data_type[$batch_update_action['type']] : 'text';
				//data type for handling copy_from_field operator
				if ( 'copy_from_field' === $batch_update_action['operator'] ) { 
					$batch_update_action['copy_field_data_type'] = ( ! empty( $col_data_type[$batch_update_action['value']] ) ) ? $col_data_type[$batch_update_action['value']] : 'text';
				}
				return $batch_update_action;
			}, $batch_update_actions);
			$this->send_to_background_process( array( 'process_name' => 'Bulk Edit', 
														'callback' => array( 'class_path' => $this->req_params['class_path'], 
																			'func' => array( $this->req_params['class_nm'], 'process_batch_update' ) ),
														'actions' => $batch_update_actions ) );
		}

		//function to handle batch update request
		public function send_to_background_process( $params = array() ) {
			$selected_ids = ( ! empty( $this->req_params['selected_ids'] ) ) ? json_decode( stripslashes( $this->req_params['selected_ids'] ), true ) : array();
			$entire_store = false;

			if ( ( false === $this->entire_task ) && ( ! empty( $this->req_params['storewide_option'] ) ) && ( 'entire_store' === $this->req_params['storewide_option'] ) && ( ! empty( $this->req_params['active_module'] ) ) ) { //code for fetching all the ids in case of any background process
				$selected_ids = $this->get_entire_store_ids();
				$entire_store = true;
			}
			$identifier = '';	
			$process_name = apply_filters( 'sm_get_process_names_for_adding_tasks', $params['process_name'] );
			if ( ! empty( $process_name ) && ( is_array( $process_name ) ) && ( in_array( $params['process_name'], $process_name, true ) ) ) {
				$task_id = 0;
				if ( is_callable( array( 'Smart_Manager_Task', 'task_update' ) ) && ( ! empty( $this->req_params['title'] ) ) && ( ! empty( $this->dashboard_key ) ) && ( ! empty( $params['actions'] ) ) && ( ! empty( $selected_ids ) && is_array( $selected_ids ) ) ) {
					$task_id = Smart_Manager_Task::task_update(
						array(
							'title' => $this->req_params['title'],
							    'created_date' => date('Y-m-d H:i:s'),
							    'completed_date' => '0000-00-00 00:00:00',
							    'post_type' => $this->dashboard_key,
							    'type' => 'bulk_edit',
							    'status' => 'in-progress',
							    'actions' => $params['actions'],
							    'record_count' => count( $selected_ids ),
							) 
					);
				}
				$params['actions'] = array_map( function( $params_action ) use( $task_id ) {
					$params_action['task_id'] = $task_id;	
					return $params_action;
				}, $params['actions'] );
			}
			if ( is_callable( array( 'Smart_Manager_Pro_Background_Updater', 'get_identifier' ) ) ) {
				$identifier = Smart_Manager_Pro_Background_Updater::get_identifier();
			}
			if ( !empty( $identifier ) && ! empty( $selected_ids ) ) {
				$default_params = array( 'process_name' => 'Bulk edit / Batch update', 
										'callback' => array( 'class_path' => $this->req_params['class_path'], 
															'func' => array( $this->req_params['class_nm'], 'process_batch_update' ) ),
										'id_count' => count( $selected_ids ),
										'active_dashboard' => $this->dashboard_title,
										'backgroundProcessRunningMessage' => $this->req_params['backgroundProcessRunningMessage'],
										'entire_store' => $entire_store, 
										'dashboard_key' => $this->dashboard_key,
										'SM_IS_WOO30' => $this->req_params['SM_IS_WOO30'] );
				$params = ( !empty( $params ) ) ? array_merge( $default_params, $params ) : $default_params;
				update_option( $identifier.'_params', $params, 'no' );
				update_option( $identifier.'_ids', $selected_ids, 'no' );
				update_option( $identifier.'_initial_process', 1, 'no' );

				//Calling the initiate_batch_process function to initiaite the batch process
				if ( is_callable( array( self::$sm_beta_background_updater, 'initiate_batch_process' ) ) ) {
					self::$sm_beta_background_updater->initiate_batch_process();
				}
			}
		}

		//function to process batch update conditions
		public static function process_batch_update( $args= array() ) {
			do_action('sm_beta_pre_process_batch');
			// code for processing logic for batch update
			if( empty($args['type']) || empty($args['operator']) || empty($args['id']) || empty( $args['date_type'] ) ) {
				return false;
			}
			$type_exploded = explode("/",$args['type']);

			if( empty( $type_exploded ) ) {
				return false;
			}

			if ( sizeof($type_exploded) > 2 ) {
				$args['table_nm'] = $type_exploded[0];
				$cond = explode("=",$type_exploded[1]);

				if (sizeof($cond) == 2) {
					$args['col_nm'] = $cond[1];
				}
			} else {
				$args['col_nm'] = $type_exploded[1];
				$args['table_nm'] = $type_exploded[0];
			}

			$prev_val = $new_val = '';
			$prev_val = apply_filters( 'sm_beta_batch_update_prev_value', $prev_val, $args );
			if( empty( $prev_val ) ) {
				if( is_callable( array( 'Smart_Manager_Task', 'get_previous_data' ) ) ) {
					$prev_val = Smart_Manager_Task::get_previous_data( $args['id'], $args['table_nm'], $args['col_nm'] );
				}	
			}
			if( $args['date_type'] == 'numeric' ) {
				$prev_val = ( ! empty( $prev_val ) ) ? floatval( $prev_val ) : 0;
			}

			$args['prev_val'] = $prev_val;

			$value1 = $args['value'];
			$args_meta = ( ! empty( $args['meta'] ) ) ? $args['meta'] : array();
			// $value1 = ( ( is_array( $args['value'] ) && isset( $args['value'][0]) ) ? $args['value'][0] : $args['value'] );
			// $value2 = ( ( is_array( $args['value'] ) && isset( $args['value'][1]) ) ? $args['value'][1] : '' );

			if( $args['date_type'] == 'numeric' ) {
				$value1 = ( ! empty( $value1 ) ) ? floatval( $value1 ) : 0;
			}

			//Code for handling different conditions for updating datetime fields
			if( $args['date_type'] == 'sm.datetime' && ( $args['operator'] == 'set_date_to' || $args['operator'] == 'set_time_to' ) ) {
				//if prev_val is null
				if( empty($prev_val) ) {
					$date = ( $args['operator'] == 'set_date_to' ) ? $value1 : current_time( 'Y-m-d' );
					$time = ( $args['operator'] == 'set_time_to' ) ? $value1 : current_time( 'H:i:s' );
				} else {
					$date = ( $args['operator'] == 'set_date_to' ) ? $value1 : date('Y-m-d', strtotime($prev_val));
					$time = ( $args['operator'] == 'set_time_to' ) ? $value1 : date('H:i:s', strtotime($prev_val));
				}

				$value1 = $date.' '.$time;
			}

			if( ( $args['date_type'] == 'sm.datetime' || $args['date_type'] == 'sm.date' || $args['date_type'] == 'sm.time' ) && !empty( $args['date_type'] ) && $args['date_type'] == 'timestamp' ) { //code for handling timestamp values

				if( $args['date_type'] == 'sm.time' ) {
					$value1 = '1970-01-01 '.$value1;
				}

				$value1 = strtotime( $value1 );
			}


			// Code for handling increase/decrease date by operator

			$date_type_fields = array( 'sm.date', 'sm.datetime', 'sm.time', 'timestamp' );
			$additional_date_operators = array( 'increase_date_by', 'decrease_date_by' );

			if( in_array( $args['date_type'], $date_type_fields ) && in_array( $args['operator'], $additional_date_operators ) )
			{
				$args['meta']['dateDuration'] = !empty ( $args['meta']['dateDuration'] ) ? $args['meta']['dateDuration'] : ( ( 'sm.time' === $args['date_type'] ) ? 'hours' : 'days' );
				$args['value'] = !empty ( $args['value'] ) ? $args['value'] : 0;

				switch ( $args['date_type'] ) {
					case 'timestamp': 
					case 'sm.date':
						$date_format = 'Y-m-d';												
						break;
					case 'sm.datetime':
						$date_format = 'Y-m-d H:i:s';							
						break;
					case 'sm.time':
						$date_format = 'h:i';
						break;
				}
				$prev_val = ( ! empty( $prev_val ) ) ?  $prev_val : current_time( $date_format );
				$value1  =  date( $date_format, strtotime( $prev_val. ( ( 'increase_date_by' === $args['operator'] ) ? '+' : '-' ) .$args['value']. $args['meta']['dateDuration'] ) );
			}


			if( $args['date_type'] == 'dropdown' || $args['date_type'] == 'multilist' ) {
				if( $args['operator'] == 'add_to' || $args['operator'] == 'remove_from' ) {

					if( $args['table_nm'] == 'terms' ) {
						$prev_val = wp_get_object_terms( $args['id'], $args['col_nm'], 'orderby=none&fields=ids' );
					} else {
						if( !empty( $args['multiSelectSeparator'] ) && !empty( $prev_val ) ) {
							$prev_val = explode( $args['multiSelectSeparator'], $prev_val );
						} else {
							$prev_val = ( !empty( $prev_val ) ) ? $prev_val : array();	
						}
					}

					$value1 = ( !is_array( $value1 ) ) ? array( $value1 ) : $value1;

					if( !empty( $prev_val ) ) {
						$value1 = ( $args['operator'] == 'add_to' ) ? array_merge($prev_val, $value1) : array_diff($prev_val, $value1);
					}

					$value1 = array_unique( $value1 );
				} 
				
				$separator = ( !empty( $args['multiSelectSeparator'] ) ) ? $args['multiSelectSeparator'] : ",";
				$value1 = ( !empty( $separator ) && is_array( $value1 ) ) ? implode( $separator, $value1 ) : $value1;
			}

			if( $args['date_type'] == 'sm.multilist' && $args['operator'] != 'set_to' && $args['table_nm'] == 'postmeta' ) { //code for handling multilist values
				
			}

			// Code for handling serialized data updates
			if( $args['date_type'] == 'sm.serialized' ) {
				$value1 = maybe_unserialize( $value1 );
			}

			// default value for prev_val
			$numeric_operators = array( 'increase_by_per', 'decrease_by_per', 'increase_by_num', 'decrease_by_num' );
			if ( in_array( $args['operator'], $numeric_operators ) && empty( $prev_val ) ) {
				$prev_val = 0;
			}

			//cases to update the value based on the batch update actions
			switch( $args['operator'] ) {
				case 'set_to':
					$new_val = $value1;
					break;
				case 'prepend':
					$new_val = $value1.''.$prev_val;
					break;
				case 'append':
					$new_val = $prev_val.''.$value1;
					break;
				case 'search_and_replace':
					if( isset( $args_meta['replace_value'] ) ){
						$replace_val = ( ! empty( $args_meta['replace_value'] ) ) ? $args_meta['replace_value'] : '';
						$new_val = str_replace( $value1, $replace_val, $prev_val );
					} else {
						$new_val = $prev_val;
					}
					break;
				case 'increase_by_per':
					$new_val = round( ($prev_val + ($prev_val * ($value1 / 100))), apply_filters('sm_beta_pro_num_decimals',get_option( 'woocommerce_price_num_decimals' )) );
					break;
				case 'decrease_by_per':
					$new_val = round( ($prev_val - ($prev_val * ($value1 / 100))), apply_filters('sm_beta_pro_num_decimals',get_option( 'woocommerce_price_num_decimals' )) );
					break;
				case 'increase_by_num':
					$new_val = round( ($prev_val + $value1), apply_filters('sm_beta_pro_num_decimals',get_option( 'woocommerce_price_num_decimals' )) );
					break;
				case 'decrease_by_num':
					$new_val = round( ($prev_val - $value1), apply_filters('sm_beta_pro_num_decimals',get_option( 'woocommerce_price_num_decimals' )) );
					break;
				default:
					$new_val = $value1;
					break;
			}


			//Code for handling 'copy_from' and 'copy_from_field' action
			$args['copy_from_operators'] = array('copy_from', 'copy_from_field');
			$value1 = ( 'copy_from_field' === $args['operator'] && empty( $value1 ) ) ? $args['type'] : $value1;

			if( in_array( $args['operator'], $args['copy_from_operators'] ) && ( ! empty( $value1 ) ) ) {

				$args['selected_table_name'] = $args['table_nm'];
				$args['selected_column_name'] = $args['col_nm'];
				$args['selected_value'] = $value1;

				if( 'copy_from_field' === $args['operator'] ) {

					$explode_selected_value = ( false !== strpos( $args['selected_value'], '/' ) ) ? explode('/', $args['selected_value']) : $args['selected_value'];

					if ( is_array( $explode_selected_value ) && sizeof( $explode_selected_value ) >= 2 ) {
						$args['selected_table_name'] = $explode_selected_value[0];
						$args['selected_column_name'] = $explode_selected_value[1];
						$cond = ( false !== strpos( $args['selected_column_name'], '=' ) ) ? explode( "=", $args['selected_column_name'] ) : $args['selected_column_name'];
						$args['selected_column_name'] = ( ( is_array( $cond ) ) && ( 2 === sizeof( $cond ) ) ) ? $cond[1] : $cond;				
					}  

					$args['selected_value'] = $args['id'];	
				}

				switch ( $args['selected_table_name'] ) {
					case 'posts':
						$new_val = get_post_field( $args['selected_column_name'], $args['selected_value'] );
						break;
					case 'postmeta':
						$new_val = get_post_meta( $args['selected_value'], $args['selected_column_name'], true );
						break;
					case 'terms':
						$term_ids = wp_get_object_terms( $args['selected_value'], $args['selected_column_name'], array( 'orderby' => 'term_id', 'order' => 'ASC', 'fields' => 'ids' ) );
						$new_val = ( ! is_wp_error( $term_ids ) && ! empty( $term_ids ) ) ? $term_ids : array();
						break;
					case 'custom':
						$new_val = apply_filters( 'sm_get_value_for_copy_from_operator', $new_val, $args );
						break;
					default:
						$new_val = $value1;
						break;
				}

				$new_val = ( 'numeric' === $args['date_type'] && empty( $new_val ) ) ? 0 : $new_val;

				$args['new_value'] = $new_val;
				$new_val = ( ( 'copy_from_field' === $args['operator'] && ( ! empty ( $args['copy_field_data_type'] ) ) ) && is_callable( array( 'Smart_Manager_Pro_Base', 'handle_serialized_data' ) ) ) ? self::handle_serialized_data( $args ) : $new_val;	

			}
			$args['value'] = $new_val;

			$args = apply_filters( 'sm_beta_post_batch_process_args', $args );
			self::process_batch_update_db_updates( $args );
		}

		//function to handle serialized values for copy from field operator
		public static function handle_serialized_data( $args = array() ) {

			if( empty( $args['date_type'] ) || empty( $args['new_value'] ) ) {
				return '';
			}

			switch( true ) {
				case( 'sm.serialized' === $args['date_type'] ):
					return maybe_unserialize( $args['new_value'] );
				case( 'sm.serialized' !== $args['date_type'] && 'sm.serialized' === $args['copy_field_data_type'] ):
					return maybe_serialize( $args['new_value'] );
				default:
					return $args['new_value'];
			}
		}

		//function to handle the batch update db updates
		public static function process_batch_update_db_updates( $args = array() ) {
			do_action( 'sm_pre_batch_update_db_updates',$args );

			set_transient( 'sm_beta_skip_delete_dashboard_transients', 1, DAY_IN_SECONDS ); // for preventing delete dashboard transients
			Smart_Manager_Base::$update_task_details_params = array();
			$update = false;
			$default_batch_update = true;

			$default_batch_update = apply_filters( 'sm_default_batch_update_db_updates', $default_batch_update, $args );

			if( $default_batch_update ) {			
				switch ( $args['table_nm'] ) {
					case 'posts':
						$update = wp_update_post( array( 'ID' => $args['id'], $args['col_nm'] => $args['value'] ) );
						break;
					case 'postmeta':
						$update = update_post_meta( $args['id'], $args['col_nm'], $args['value'] );
						break;
					case 'terms':
						self::batch_update_terms_table_data( $args );
						break;
					case ( 'custom' === $args['table_nm'] && 'copy_from' === $args['operator'] ):
						$update = apply_filters( 'sm_update_value_for_copy_from_operator', $args );
						break;
					default:
						$update = wp_update_post( array( 'ID' => $args['id'], $args['col_nm'] => $args['value'] ) );
						$value = $args['value'];
						break;
				}
			}
			$update = apply_filters( 'sm_post_batch_update_db_updates',$update ,$args );
			if ( is_wp_error( $update ) ) {
				return false;
			} elseif ( ! empty( $args['task_id'] ) && ( ! empty( property_exists( 'Smart_Manager_Base', 'update_task_details_params' ) ) ) ) {
				$action = 'set_to';
				if ( in_array( $args['operator'], array( 'add_to', 'remove_from' ) ) ) {
					$action = apply_filters( 'sm_task_update_action', $args['operator'], $args );
				}
				Smart_Manager_Base::$update_task_details_params[] = array(
					'task_id' => $args['task_id'],
						    'action' => $action,
						    'status' => 'completed',
						    'record_id' => $args['id'],
						    'field' => $args['type'],  
						    'prev_val' => ( ( ! empty( $args['col_nm'] ) ) && ( ! empty( $args['date_type'] ) ) ) ? sa_sm_format_prev_val( array(
											'prev_val' => $args['prev_val'],
											'update_column' => $args['col_nm'],
											'col_data_type' => $args['date_type'],
											'updated_val' => $args['value']
											)
										) : $args['prev_val'],
						    'updated_val' => $args['value'],
						    'operator' => $args['operator'],
				);
			    apply_filters( 'sm_task_details_update_by_prev_val', Smart_Manager_Base::$update_task_details_params );
			    // For updating task details table
				if ( ( ! empty( Smart_Manager_Base::$update_task_details_params ) ) && is_callable( array( 'Smart_Manager_Task', 'task_details_update' ) ) ) {
					return Smart_Manager_Task::task_details_update();
				}
			} else {
				return true;
			}
		}

		//function to handle batch process complete
		public static function batch_process_complete() {
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

			delete_option( $identifier.'_params' );

			// Preparing email content
			$email = get_option('admin_email');
			$site_title = get_option( 'blogname' );

			$email_heading_color = get_option('woocommerce_email_base_color');
			$email_heading_color = (empty($email_heading_color)) ? '#96588a' : $email_heading_color; 
			$email_text_color = get_option('woocommerce_email_text_color');
			$email_text_color = (empty($email_text_color)) ? '#3c3c3c' : $email_text_color; 

			$actions = ( !empty($background_process_params['actions']) ) ? $background_process_params['actions'] : array();

			$records_str = $background_process_params['id_count'] .' '. (( $background_process_params['id_count'] > 1 ) ? __( 'records', SM_TEXT_DOMAIN ) : __( 'record', SM_TEXT_DOMAIN ));
			$records_str .= ( $background_process_params['entire_store'] ) ? ' ('. __( 'entire store', SM_TEXT_DOMAIN ) .')' : '';

			$background_process_param_name = $background_process_params['process_name'];

			$title = sprintf( __( '[%1s] %2s process completed!', SM_TEXT_DOMAIN ), $site_title, $background_process_param_name );

			ob_start();

			include( apply_filters( 'sm_beta_pro_batch_email_template', SM_PRO_URL.'templates/email.php' ) );

			$message = ob_get_clean();

			$subject = $title;

			if( function_exists( 'wc_mail' ) ) {
				wc_mail( $email, $subject, $message );
			} else {
				wp_mail( $email, $subject, $message );
			}

		}

		//Function to generate the data for print_invoice
		public function get_print_invoice() {

			global $smart_manager_beta;

			ini_set('memory_limit','512M');
			set_time_limit(0);

			$purchase_id_arr = ( ! empty( $this->req_params['selected_ids'] ) ) ? json_decode( stripslashes( $this->req_params['selected_ids'] ), true ) : array();
			if ( ( ! empty( $this->req_params['storewide_option'] ) ) && ( 'entire_store' === $this->req_params['storewide_option'] ) && ( ! empty( $this->req_params['active_module'] ) ) ) { //code for fetching all the ids
				$purchase_id_arr = $this->get_entire_store_ids();
			}

			$sm_text_domain = 'smart-manager-for-wp-e-commerce';
			$sm_is_woo30 = ( ! empty( Smart_Manager::$sm_is_woo30 ) && 'true' === Smart_Manager::$sm_is_woo30 ) ? true : false;
			$sm_is_woo44 = ( ! empty( Smart_Manager::$sm_is_woo44 ) && 'true' === Smart_Manager::$sm_is_woo44 ) ? true : false;

			ob_start();
			if ( function_exists( 'wc_get_template' ) ) {
				$template = 'order-invoice.php';
				wc_get_template(
					$template,
					array( 'purchase_id_arr' => $purchase_id_arr,
							'sm_text_domain' => $sm_text_domain,
							'sm_is_woo30' => $sm_is_woo30,
							'sm_is_woo44' => $sm_is_woo44,
							'smart_manager_beta' => $smart_manager_beta
						),
					$this->get_template_base_dir( $template ),
					SM_PLUGIN_DIR_PATH .'/pro/templates/'
				);
			} else {
				include( apply_filters( 'sm_beta_pro_batch_order_invoice_template', SM_PRO_URL.'templates/order-invoice.php' ) );
			}
			echo ob_get_clean();
			exit;
		}

		//function to handle duplicate records functionality
		public function duplicate_records() {
			$this->send_to_background_process( array( 'process_name' => 'Duplicate Records', 
														'callback' => array( 'class_path' => $this->req_params['class_path'], 
																			'func' => array( $this->req_params['class_nm'], 'process_duplicate_record' ) ) 
													)
											);
		}

		public static function get_duplicate_record_settings() {
	
			$defaults = array(
				'status' => 'same',
				'type' => 'same',
				'timestamp' => 'current',
				'title' => '('.__('Copy', SM_TEXT_DOMAIN).')',
				'slug' => 'copy',
				'time_offset' => false,
				'time_offset_days' => 0,
				'time_offset_hours' => 0,
				'time_offset_minutes' => 0,
				'time_offset_seconds' => 0,
				'time_offset_direction' => 'newer'
			);
			
			$settings = apply_filters( 'sm_beta_duplicate_records_settings', $defaults );
			
			return $settings;
		}


		//function to process duplicate records logic
		public static function process_duplicate_record( $params ) {

			$original_id = ( !empty( $params['id'] ) ) ? $params['id'] : '';

			do_action('sm_beta_pre_process_duplicate_records', $original_id );

			//code for processing logic for duplicate records
			if( empty( $original_id ) ) {
				return false;
			}

			global $wpdb;

			// Get the post as an array
			$duplicate = get_post( $original_id, 'ARRAY_A' );
				
			$settings = self::get_duplicate_record_settings();
			
			// Modify title
			$appended = ( $settings['title'] != '' ) ? ' '.$settings['title'] : '';
			$duplicate['post_title'] = $duplicate['post_title'].' '.$appended;
			$duplicate['post_name'] = sanitize_title($duplicate['post_name'].'-'.$settings['slug']);
			
			// Set the post status
			if( $settings['status'] != 'same' ) {
				$duplicate['post_status'] = $settings['status'];
			}
			
			// Set the post type
			if( $settings['type'] != 'same' ) {
				$duplicate['post_type'] = $settings['type'];
			}
			
			// Set the post date
			$timestamp = ( $settings['timestamp'] == 'duplicate' ) ? strtotime($duplicate['post_date']) : current_time('timestamp',0);
			$timestamp_gmt = ( $settings['timestamp'] == 'duplicate' ) ? strtotime($duplicate['post_date_gmt']) : current_time('timestamp',1);
			
			if( $settings['time_offset'] ) {
				$offset = intval($settings['time_offset_seconds']+$settings['time_offset_minutes']*60+$settings['time_offset_hours']*3600+$settings['time_offset_days']*86400);
				if( $settings['time_offset_direction'] == 'newer' ) {
					$timestamp = intval($timestamp+$offset);
					$timestamp_gmt = intval($timestamp_gmt+$offset);
				} else {
					$timestamp = intval($timestamp-$offset);
					$timestamp_gmt = intval($timestamp_gmt-$offset);
				}
			}
			$duplicate['post_date'] = date('Y-m-d H:i:s', $timestamp);
			$duplicate['post_date_gmt'] = date('Y-m-d H:i:s', $timestamp_gmt);
			$duplicate['post_modified'] = date('Y-m-d H:i:s', current_time('timestamp',0));
			$duplicate['post_modified_gmt'] = date('Y-m-d H:i:s', current_time('timestamp',1));

			// Remove some of the keys
			unset( $duplicate['ID'] );
			unset( $duplicate['guid'] );
			unset( $duplicate['comment_count'] );

			// Insert the post into the database
			$duplicate_id = wp_insert_post( $duplicate );
			
			// Duplicate all the taxonomies/terms
			$taxonomies = get_object_taxonomies( $duplicate['post_type'] );
			foreach( $taxonomies as $taxonomy ) {
				$terms = wp_get_post_terms( $original_id, $taxonomy, array('fields' => 'names') );
				wp_set_object_terms( $duplicate_id, $terms, $taxonomy );
			}
		  
			// Duplicate all the custom fields
			$custom_fields = get_post_custom( $original_id );

			$postmeta_data = array();

			foreach ( $custom_fields as $key => $value ) {
			  if( is_array($value) && count($value) > 0 ) { //TODO: optimize
					foreach( $value as $i=>$v ) {
						$postmeta_data[] = '('.$duplicate_id.',\''.$key.'\',\''.$v.'\')'; 
					}
				}
			}

			if( !empty($postmeta_data) ) {

				$q = "INSERT INTO {$wpdb->prefix}postmeta(post_id, meta_key, meta_value) VALUES ". implode(",", $postmeta_data);
				$query = $wpdb->query("INSERT INTO {$wpdb->prefix}postmeta(post_id, meta_key, meta_value) VALUES ". implode(",", $postmeta_data));
			}

			do_action( 'sm_beta_post_process_duplicate_records', array( 'original_id' => $original_id, 'duplicate_id' => $duplicate_id, 'settings' => $settings, 'duplicate' => $duplicate ) );
			
			if( is_wp_error($duplicate_id) ) {
				return false;
			} else {
				return true;
			}

		}

		/**
		 * Function to handle deletion via background process
		 */
		public function delete_all() {
			$this->send_to_background_process( array( 'process_name' => 'Delete All Records', 
														'callback' => array( 'class_path' => $this->req_params['class_path'], 
																			'func' => array( $this->req_params['class_nm'], 'process_delete_record' ) ),
														'callback_params' => array ( 'delete_permanently' => $this->req_params['deletePermanently'] )
													) 
											);
		}

		/**
		 * Function to handle delete of a single record
		 *
		 * @param  integer $deleting_id The ID of the record to be deleted.
		 * @return boolean
		 */
		public static function process_delete_record( $params ) {

			$deleting_id = ( !empty( $params['id'] ) ) ? $params['id'] : '';
			do_action('sm_pro_pre_process_delete_records', array( 'deleting_id' => $deleting_id, 'source' => __CLASS__ ) );

			//code for processing logic for duplicate records
			if( empty( $deleting_id ) ) {
				return false;
			}

			$force_delete = ( !empty($params['delete_permanently']) ) ? true : false;
			
			$default_process = apply_filters( 'sm_pro_default_process_delete_records', true );
			$result = false;
			if( ! empty( $default_process ) ){
				$result = ( $force_delete ) ? wp_delete_post( $deleting_id, $force_delete ) : wp_trash_post( $deleting_id );
			}
			$params[$force_delete] = $force_delete;
			$result = apply_filters( 'sm_pro_default_process_delete_records_result', $result, $deleting_id, $params );

			do_action( 'sm_pro_post_process_delete_records', array( 'deleting_id' => $deleting_id, 'source' => __CLASS__ ) );
			
			if( empty( $result ) ) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Function to get template base directory for Smart Manager templates
		 *
		 * @param  string $template_name Template name.
		 * @return string $template_base_dir Base directory for Smart Manager templates.
		 */
		public function get_template_base_dir( $template_name = '' ) {

			$template_base_dir = '';
			$sm_dir_name = SM_PLUGIN_DIR . '/';
			$sm_base_dir    = 'woocommerce/' . $sm_dir_name;

			// First locate the template in woocommerce/smart-manager-for-wp-e-commerce folder of active theme.
			$template = locate_template(
				array(
					$sm_base_dir . $template_name,
				)
			);

			if ( ! empty( $template ) ) {
				$template_base_dir = $sm_base_dir;
			} else {
				// If not found then locate the template in smart-manager-for-wp-e-commerce folder of active theme.
				$template = locate_template(
					array(
						$sm_dir_name . $template_name,
					)
				);
				if ( ! empty( $template ) ) {
					$template_base_dir = $sm_dir_name;
				}
			}

			$template_base_dir = apply_filters( 'sm_template_base_dir', $template_base_dir, $template_name );

			return $template_base_dir;
		}

		/**
		 * Function to get modify the search cond for `any of/not any of` search operators
		 *
		 * @param  string $cond Search condition.
		 * @param  array $search_params Advanced search params.
		 * 
		 * @return string $cond Updated search condition.
		 */
		public function modify_search_cond( $cond = '', $search_params = array() ) {
		
			$operator = ( ! empty( $search_params['selected_search_operator'] ) ) ? $search_params['selected_search_operator'] : '';
			
			if( empty( $operator ) ){
				return $cond;
			}

			$val = ( ! empty( $search_params['search_value'] ) ) ? $search_params['search_value'] : '';
			$col = ( ! empty( $search_params['search_col'] ) ) ? $search_params['search_col'] : '';

			if( ! in_array( $operator, array( 'anyOf', 'notAnyOf' ) ) || empty( $val ) || empty( $col ) ){
				return $cond;
			}

			$val = explode( "|", $val );

			if( ! is_array( $val ) ){
				return $cond;
			}

			$addln_cond = '';
			if( ! empty( $search_params['is_meta_table'] ) ){
				$addln_cond = $search_params['table_nm'] . ".meta_key LIKE '%". trim( $col ) . "%' AND ";
				$col = 'meta_value';
			}

			$col = $search_params['table_nm'] . "." . $col;

			$cond = array_reduce( $val, function( $carry, $item ) use( $col, $operator, $addln_cond ){
				return $carry . ( " ( ". $addln_cond . " " . $col . " ". ( ( 'notAnyOf' === $operator ) ? 'NOT ' : '' ) . "LIKE '%". trim( $item ) . "%'" . " ) ".( ( 'notAnyOf' === $operator ) ? 'AND' : 'OR' ) );
			} );

			return ( 'notAnyOf' === $operator ) ? ( ( " AND" === substr( $cond, -4 ) ) ? "( " . substr( $cond, 0, -4 ) . " )" : $cond ) : ( ( " OR" === substr( $cond, -3 ) ) ? "( " . substr( $cond, 0, -3 ) . " )" : $cond );
		}

		/**
		 * Function to get format the search value for `starts with/ends with` search operators
		 *
		 * @param  string $search_value Searched value.
		 * @param  array $search_params Advanced search params.
		 * 
		 * @return string $search_value Formatted searched value.
		 */
		public function format_search_value( $search_value = '', $search_params = array() ) {

			$operator = ( ! empty( $search_params['selected_search_operator'] ) ) ? $search_params['selected_search_operator'] : '';

			if( empty( $operator ) ){
				return $search_value;
			}

			switch( true ) {
				case( in_array( $operator, array( 'startsWith', 'notStartsWith' ) ) ):
					return $search_value. '%';
				case( in_array( $operator, array( 'endsWith', 'notEndsWith' ) ) ):
					return '%'. $search_value;
				default:
					return $search_value;
			}
		}

		/**
		 * Function to fetch column data type
		 *
		 * @param  string $dashboard_key current dashboard name.
		 * @return string $col_data_type column data type
		 */
		public static function get_column_data_type( $dashboard_key = '' ) {
			if ( empty( $dashboard_key ) ) {
				return;
			}
			$current_store_model = get_transient( 'sa_sm_' . $dashboard_key );
			if ( empty( $current_store_model ) && is_array( $current_store_model ) ) {
				return;
			}
			$current_store_model = json_decode( $current_store_model, true );
			$col_model = ( ! empty( $current_store_model['columns'] ) ) ? $current_store_model['columns'] : array();
			if ( empty( $col_model ) ) {
				return;
			}
			$col_data_type = array();
			$date_type_cols = array( 'sm.date', 'sm.datetime', 'sm.time', 'timestamp' );
			//Code for storing the timestamp cols
			foreach ( $col_model as $col ) {
				if ( empty( $col['type'] ) ) {
					continue;
				}
				$col_data_type[ $col['src'] ] = ( ( in_array( $col['type'], $date_type_cols, true ) ) && ( ! empty( $col['date_type'] ) && ( 'timestamp' === $col['date_type'] ) ) ) ? 'timestamp' : $col['type'];
			} 
			return $col_data_type;	
		}

		/**
		 * Function update the edited column titles for the specific dashboard
		 *
		 * @param  array $args request params array.
		 * @return void
		 */
		public static function update_column_titles( $args = array() ){
			( ! empty( $args['edited_column_titles'] ) && ! empty( $args['state_option_name'] ) ) ? update_option( $args['state_option_name'] .'_columns', array_merge( get_option( $args['state_option_name'] .'_columns', array() ), $args['edited_column_titles'] ), 'no' ) : '';
		}

		/**
		 * Function to batch update terms table related data
		 *
		 * @param  array $args request params array.
		 * @return boolean $update result of the function call.
		 */
		public static function batch_update_terms_table_data( $args = array() ) {
			if ( empty( $args ) || ( ! is_array( $args ) ) || empty( $args['operator'] ) || empty( $args['id'] ) || empty( $args['col_nm'] ) ) {
				return false;
			}
			$update = false;
			$value = ( is_array( $args['value'] ) && ! empty( $args['value'][0] ) ) ? intval( $args['value'][0] ) : intval( $args['value'] );
			if ( ( ! empty( $args['copy_from_operators'] )  && is_array( $args['copy_from_operators'] ) ) && in_array( $args['operator'], $args['copy_from_operators'] ) ) {
				$value = $args['value'];
			}
			if ( 'remove_from' === $args['operator'] ) {
				return wp_remove_object_terms( $args['id'], $value, $args['col_nm'] );
			} else {
				$append = ( 'add_to' === $args['operator'] ) ? true : false;
				return wp_set_object_terms( $args['id'], $value, $args['col_nm'], $append );
			}
		}

		/**
		 * Before deleting a post, do some cleanup like removing attached media.
		 *
		 * @param int $order_id Order ID.
		 * @param WP_Post $post Post data.
		 */
		public function delete_attached_media( $post_id = 0, $post = null ) {
			if ( empty( intval( $post_id ) ) ) {
				return;
			}
			global $wpdb;
			$attachments = get_children( array(
				'post_parent' => $post_id,
				'post_type'   => 'attachment', 
				'numberposts' => -1,
				'post_status' => 'any' 
		  	) );
			if ( empty( $attachments ) || ! is_array( $attachments ) ) {
				return;
			}
			$attached_media_post_ids = array();
			$post_ids = array();
			foreach ( $attachments as $attachment ) {
				$attachment_id = $attachment->ID;
				if ( empty( intval( $attachment_id ) ) ) {
					continue;
				}
				$attached_media_post_ids = $wpdb->get_col(
											$wpdb->prepare( "SELECT DISTINCT post_id 
											FROM {$wpdb->prefix}postmeta WHERE post_id <> %d AND meta_key = %s AND meta_value = %s", $post_id, '_thumbnail_id', $attachment_id )
										); 
				$attached_media_post_ids = apply_filters( 'sm_delete_attachment_get_matching_gallery_images_post_ids', $attached_media_post_ids, array(
					'post_id' => $post_id,
					'attachment_id' => $attachment_id
				) );
				$post_ids = $wpdb->get_col(
									$wpdb->prepare( "SELECT DISTINCT ID 
									FROM {$wpdb->prefix}posts WHERE ID <> %d AND post_content LIKE '%wp-image-" . $attachment_id . "%' OR post_excerpt LIKE '%wp-image-" . $attachment_id . "%' OR post_content LIKE '%wp:image {\"id\":$attachment_id%' OR post_excerpt LIKE '%wp:image {\"id\":$attachment_id%'", $post_id )
									);
			}
			if ( empty( ( is_array( $attached_media_post_ids ) && is_array( $post_ids ) ) && array_merge( $attached_media_post_ids, $post_ids ) ) ) {
				wp_delete_attachment( $attachment_id, true );
				wp_delete_post( $attachment_id, true );
			}
		}
	}
}
