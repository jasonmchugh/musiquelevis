<?php
/**
 * MEDIA LIBRARY
 * -------------
 *
 * Add "alt" column to media library
 * Add "url" column to media library
 */
if ( ! function_exists( 'bravad_media_extra_column' ) && ! function_exists( 'bravad_media_extra_column_value' ) ) {
	function bravad_media_extra_column( $cols ) {
		// Add column
		$cols["alt"] = "ALT";
		$cols["media_url"] = "URL";
		return $cols;
	}
	function bravad_media_extra_column_value($column_name, $id) {
		// Add value
		if($column_name == 'alt') {
			$alt = get_post_meta($id, '_wp_attachment_image_alt', true);
			$alt = $alt != '' ? $alt : '<em style="opacity:0.5;">(none)</em>';
			echo $alt;
		}

		if ($column_name == "media_url") {

			$subject = wp_get_attachment_url($id);
			$grab = array("http://example.com", "http://another.example.com");
			$replaceWith = '';
			$path = str_replace($grab, $replaceWith, $subject);

			echo '<input type="text" width="100%" onclick="jQuery(this).select();" value="'.$path.'" />';
		}
	}
	add_filter('manage_media_columns', 'bravad_media_extra_column');
	add_action('manage_media_custom_column', 'bravad_media_extra_column_value', 10, 2);
}


/**
 * Clean up the unnecessary Screen Options
 */
function bravad_manage_media_columns( $columns) {
	// Remove Comments and Author Columns
	unset( $columns['comments'], $columns['author'] );
	return $columns;
}
add_action( 'manage_media_columns', 'bravad_manage_media_columns', 10, 2 );


/**
 * Media Categories
 */

	/** Custom update_count_callback */
	function bravad_media_category_update_count_callback( $terms = array(), $taxonomy = 'category' ) {
		global $wpdb;

		// default taxonomy
		$taxonomy = 'category';
		// add filter to change the default taxonomy
		$taxonomy = apply_filters( 'bravad_media_category_taxonomy', $taxonomy );

		// select id & count from taxonomy
		$query = "SELECT term_taxonomy_id, MAX(total) AS total FROM ((
		SELECT tt.term_taxonomy_id, COUNT(*) AS total FROM $wpdb->term_relationships tr, $wpdb->term_taxonomy tt WHERE tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = %s GROUP BY tt.term_taxonomy_id
		) UNION ALL (
		SELECT term_taxonomy_id, 0 AS total FROM $wpdb->term_taxonomy WHERE taxonomy = %s
		)) AS unioncount GROUP BY term_taxonomy_id";
		$rsCount = $wpdb->get_results( $wpdb->prepare( $query, $taxonomy, $taxonomy ) );
		// update all count values from taxonomy
		foreach ( $rsCount as $rowCount ) {
			$wpdb->update( $wpdb->term_taxonomy, array( 'count' => $rowCount->total ), array( 'term_taxonomy_id' => $rowCount->term_taxonomy_id ) );
		}
	}


	/** register taxonomy for attachments */
	function bravad_media_category_init() {
		// Default taxonomy
		$taxonomy = 'category';
		// Add filter to change the default taxonomy
		$taxonomy = apply_filters( 'bravad_media_category_taxonomy', $taxonomy );

		if ( taxonomy_exists( $taxonomy ) ) {
			register_taxonomy_for_object_type( $taxonomy, 'attachment' );
		} else {
			$args = array(
				'hierarchical'          => true,  // hierarchical: true = display as categories, false = display as tags
				'show_admin_column'     => true,
				'update_count_callback' => 'bravad_media_category_update_count_callback',
				'public'				=> false
			);
			register_taxonomy( $taxonomy, array( 'attachment' ), $args );
		}
	}
	add_action( 'init', 'bravad_media_category_init' );


	/** change default update_count_callback for category taxonomy */
	function bravad_media_category_change_category_update_count_callback() {
		global $wp_taxonomies;

		// Default taxonomy
		$taxonomy = 'category';
		// Add filter to change the default taxonomy
		$taxonomy = apply_filters( 'bravad_media_category_taxonomy', $taxonomy );

		if ( $taxonomy == 'category' ) {
			if ( ! taxonomy_exists( 'category' ) )
				return false;

			$new_arg = &$wp_taxonomies['category']->update_count_callback;
			$new_arg = 'bravad_media_category_update_count_callback';
		}
	}
	add_action( 'init', 'bravad_media_category_change_category_update_count_callback', 100 );


	// load code that is only needed in the admin section
	if ( is_admin() ) {

		/** Handle default category of attachments without category */
		function bravad_media_category_set_attachment_category( $post_ID ) {

			// default taxonomy
			$taxonomy = 'category';
			// add filter to change the default taxonomy
			$taxonomy = apply_filters( 'bravad_media_category_taxonomy', $taxonomy );

			// if attachment already have categories, stop here
			if ( wp_get_object_terms( $post_ID, $taxonomy ) )
				return;

			// no, then get the default one
			$post_category = array( get_option('default_category') );

			// then set category if default category is set on writting page
			if ( $post_category )
				wp_set_post_categories( $post_ID, $post_category );
		}
		add_action( 'add_attachment', 'bravad_media_category_set_attachment_category' );
		add_action( 'edit_attachment', 'bravad_media_category_set_attachment_category' );


		/** Custom walker for wp_dropdown_categories, based on https://gist.github.com/stephenh1988/2902509 */
		class bravad_media_category_walker_category_filter extends Walker_CategoryDropdown{

			function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
				$pad = str_repeat( '&nbsp;', $depth * 3 );
				$cat_name = apply_filters( 'list_cats', $category->name, $category );

				if( ! isset( $args['value'] ) ) {
					$args['value'] = ( $category->taxonomy != 'category' ? 'slug' : 'id' );
				}

				$value = ( $args['value']=='slug' ? $category->slug : $category->term_id );
				if ( 0 == $args['selected'] && isset( $_GET['category_media'] ) && '' != $_GET['category_media'] ) {  // custom taxonomy
					$args['selected'] = $_GET['category_media'];
				}

				$output .= '<option class="level-' . $depth . '" value="' . $value . '"';
				if ( (string) $value === (string) $args['selected'] ) {
					$output .= ' selected="selected"';
				}
				$output .= '>';
				$output .= $pad . $cat_name;
				if ( $args['show_count'] )
					$output .= '&nbsp;&nbsp;(' . $category->count . ')';

				$output .= "</option>\n";
			}

		}


		/** Add a category filter */
		function bravad_media_category_add_category_filter() {
			global $pagenow;
			if ( 'upload.php' == $pagenow ) {
				// Default taxonomy
				$taxonomy = 'category';
				// Add filter to change the default taxonomy
				$taxonomy = apply_filters( 'bravad_media_category_taxonomy', $taxonomy );
				if ( $taxonomy != 'category' ) {
					$dropdown_options = array(
						'taxonomy'        => $taxonomy,
						'name'            => $taxonomy,
						'show_option_all' => __( 'View all categories', 'wp-media-library-categories' ),
						'hide_empty'      => false,
						'hierarchical'    => true,
						'orderby'         => 'name',
						'show_count'      => true,
						'walker'          => new bravad_media_category_walker_category_filter(),
						'value'           => 'slug'
					);
				} else {
					$dropdown_options = array(
						'taxonomy'        => $taxonomy,
						'show_option_all' => __( 'View all categories', 'wp-media-library-categories' ),
						'hide_empty'      => false,
						'hierarchical'    => true,
						'orderby'         => 'name',
						'show_count'      => false,
						'walker'          => new bravad_media_category_walker_category_filter(),
						'value'           => 'id'
					);
				}
				wp_dropdown_categories( $dropdown_options );
			}
		}
		add_action( 'restrict_manage_posts', 'bravad_media_category_add_category_filter' );


		/** Add custom Bulk Action to the select menus */
		function bravad_media_category_custom_bulk_admin_footer() {
			// default taxonomy
			$taxonomy = 'category';
			// add filter to change the default taxonomy
			$taxonomy = apply_filters( 'bravad_media_category_taxonomy', $taxonomy );
			$terms = get_terms( $taxonomy, 'hide_empty=0' );
			if ( $terms && ! is_wp_error( $terms ) ) :

				echo '<script type="text/javascript">';
				echo 'jQuery(window).load(function() {';
				echo 'jQuery(\'<optgroup id="bravad_media_category_optgroup1" label="' .  html_entity_decode( __( 'Categories', 'wp-media-library-categories' ), ENT_QUOTES, 'UTF-8' ) . '">\').appendTo("select[name=\'action\']");';
				echo 'jQuery(\'<optgroup id="bravad_media_category_optgroup2" label="' .  html_entity_decode( __( 'Categories', 'wp-media-library-categories' ), ENT_QUOTES, 'UTF-8' ) . '">\').appendTo("select[name=\'action2\']");';

				// add categories
				foreach ( $terms as $term ) {
					$sTxtAdd = esc_js( __( 'Add', 'wp-media-library-categories' ) . ': ' . $term->name );
					echo "jQuery('<option>').val('bravad_media_category_add_" . $term->term_taxonomy_id . "').text('" . $sTxtAdd . "').appendTo('#bravad_media_category_optgroup1');";
					echo "jQuery('<option>').val('bravad_media_category_add_" . $term->term_taxonomy_id . "').text('" . $sTxtAdd . "').appendTo('#bravad_media_category_optgroup2');";
				}
				// remove categories
				foreach ( $terms as $term ) {
					$sTxtRemove = esc_js( __( 'Remove', 'wp-media-library-categories' ) . ': ' . $term->name );
					echo "jQuery('<option>').val('bravad_media_category_remove_" . $term->term_taxonomy_id . "').text('" . $sTxtRemove . "').appendTo('#bravad_media_category_optgroup1');";
					echo "jQuery('<option>').val('bravad_media_category_remove_" . $term->term_taxonomy_id . "').text('" . $sTxtRemove . "').appendTo('#bravad_media_category_optgroup2');";
				}
				// remove all categories
				echo "jQuery('<option>').val('bravad_media_category_remove_0').text('" . esc_js(  __( 'Remove all categories', 'wp-media-library-categories' ) ) . "').appendTo('#bravad_media_category_optgroup1');";
				echo "jQuery('<option>').val('bravad_media_category_remove_0').text('" . esc_js(  __( 'Remove all categories', 'wp-media-library-categories' ) ) . "').appendTo('#bravad_media_category_optgroup2');";
				echo "});";
				echo "</script>";

			endif;
		}
		add_action( 'admin_footer-upload.php', 'bravad_media_category_custom_bulk_admin_footer' );


		/** Handle the custom Bulk Action */
		function bravad_media_category_custom_bulk_action() {
			global $wpdb;

			if ( ! isset( $_REQUEST['action'] ) ) {
				return;
			}

			// is it a category?
			$sAction = ( $_REQUEST['action'] != -1 ) ? $_REQUEST['action'] : $_REQUEST['action2'];
			if ( substr( $sAction, 0, 16 ) != 'bravad_media_category_' ) {
				return;
			}

			// security check
			check_admin_referer( 'bulk-media' );

			// make sure ids are submitted.  depending on the resource type, this may be 'media' or 'post'
			if( isset( $_REQUEST['media'] ) ) {
				$post_ids = array_map( 'intval', $_REQUEST['media'] );
			}

			if( empty( $post_ids ) ) {
				return;
			}

			$sendback = admin_url( "upload.php?editCategory=1" );

			// remember pagenumber
			$pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
			$sendback = esc_url( add_query_arg( 'paged', $pagenum, $sendback ) );

			// remember orderby
			if ( isset( $_REQUEST['orderby'] ) ) {
				$sOrderby = $_REQUEST['orderby'];
				$sendback = esc_url( add_query_arg( 'orderby', $sOrderby, $sendback ) );
			}
			// remember order
			if ( isset( $_REQUEST['order'] ) ) {
				$sOrder = $_REQUEST['order'];
				$sendback = esc_url( add_query_arg( 'order', $sOrder, $sendback ) );
			}
			// remember author
			if ( isset( $_REQUEST['author'] ) ) {
				$sOrderby = $_REQUEST['author'];
				$sendback = esc_url( add_query_arg( 'author', $sOrderby, $sendback ) );
			}

			foreach( $post_ids as $post_id ) {

				if ( is_numeric( str_replace( 'bravad_media_category_add_', '', $sAction ) ) ) {
					$nCategory = str_replace( 'bravad_media_category_add_', '', $sAction );

					// update or insert category
					$wpdb->replace( $wpdb->term_relationships,
						array(
							'object_id'        => $post_id,
							'term_taxonomy_id' => $nCategory
						),
						array(
							'%d',
							'%d'
						)
					);

				} else if ( is_numeric( str_replace( 'bravad_media_category_remove_', '', $sAction ) ) ) {
					$nCategory = str_replace( 'bravad_media_category_remove_', '', $sAction );

					// remove all categories
					if ( $nCategory == 0 ) {
						$wpdb->delete( $wpdb->term_relationships,
							array(
								'object_id' => $post_id
							),
							array(
								'%d'
							)
						);
					// remove category
					} else {
						$wpdb->delete( $wpdb->term_relationships,
							array(
								'object_id'        => $post_id,
								'term_taxonomy_id' => $nCategory
							),
							array(
								'%d',
								'%d'
							)
						);
					}

				}
			}

			bravad_media_category_update_count_callback();

			wp_redirect( $sendback );
			exit();
		}
		add_action( 'load-upload.php', 'bravad_media_category_custom_bulk_action' );


		/** Display an admin notice on the page after changing category */
		function bravad_media_category_custom_bulk_admin_notices() {
			global $post_type, $pagenow;

			if ( $pagenow == 'upload.php' && $post_type == 'attachment' && isset( $_GET['editCategory'] ) ) {
				echo '<div class="updated"><p>' . __( 'Category changes are saved.', 'wp-media-library-categories' ) . '</p></div>';
			}
		}
		add_action( 'admin_notices', 'bravad_media_category_custom_bulk_admin_notices' );


		/** Changing categories in the 'grid view' */
		function bravad_media_category_ajax_query_attachments_args( $query = array() ) {
			// grab original query, the given query has already been filtered by WordPress
			$taxquery = isset( $_REQUEST['query'] ) ? (array) $_REQUEST['query'] : array();

			$taxonomies = get_object_taxonomies( 'attachment', 'names' );
			$taxquery = array_intersect_key( $taxquery, array_flip( $taxonomies ) );

			// merge our query into the WordPress query
			$query = array_merge( $query, $taxquery );

			$query['tax_query'] = array( 'relation' => 'AND' );

			foreach ( $taxonomies as $taxonomy ) {
				if ( isset( $query[$taxonomy] ) && is_numeric( $query[$taxonomy] ) ) {
					array_push( $query['tax_query'], array(
						'taxonomy' => $taxonomy,
						'field'    => 'id',
						'terms'    => $query[$taxonomy]
					) );
				}
				unset( $query[$taxonomy] );
			}

			return $query;
		}
		add_filter( 'ajax_query_attachments_args', 'bravad_media_category_ajax_query_attachments_args' );


		/** Custom walker for wp_dropdown_categories for media grid view filter */
		class bravad_media_category_walker_category_mediagridfilter extends Walker_CategoryDropdown {

			function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
				$pad = str_repeat( '&nbsp;', $depth * 3 );

				$cat_name = apply_filters( 'list_cats', $category->name, $category );

				// {"term_id":"1","term_name":"no category"}
				$output .= ',{"term_id":"' . $category->term_id . '",';

				$output .= '"term_name":"' . $pad . esc_attr( $cat_name );
				if ( $args['show_count'] ) {
					$output .= '&nbsp;&nbsp;('. $category->count .')';
				}
				$output .= '"}';
			}

		}


		/** Save categories from attachment details on insert media popup */
		function bravad_media_category_save_attachment_compat() {

			if ( ! isset( $_REQUEST['id'] ) ) {
				wp_send_json_error();
			}

			if ( ! $id = absint( $_REQUEST['id'] ) ) {
				wp_send_json_error();
			}

			if ( empty( $_REQUEST['attachments'] ) || empty( $_REQUEST['attachments'][ $id ] ) ) {
				wp_send_json_error();
			}
			$attachment_data = $_REQUEST['attachments'][ $id ];

			check_ajax_referer( 'update-post_' . $id, 'nonce' );

			if ( ! current_user_can( 'edit_post', $id ) ) {
				wp_send_json_error();
			}

			$post = get_post( $id, ARRAY_A );

			if ( 'attachment' != $post['post_type'] ) {
				wp_send_json_error();
			}

			/** This filter is documented in wp-admin/includes/media.php */
			$post = apply_filters( 'attachment_fields_to_save', $post, $attachment_data );

			if ( isset( $post['errors'] ) ) {
				$errors = $post['errors']; // @todo return me and display me!
				unset( $post['errors'] );
			}

			wp_update_post( $post );

			foreach ( get_attachment_taxonomies( $post ) as $taxonomy ) {
				if ( isset( $attachment_data[ $taxonomy ] ) ) {
					wp_set_object_terms( $id, array_map( 'trim', preg_split( '/,+/', $attachment_data[ $taxonomy ] ) ), $taxonomy, false );
				} else if ( isset($_REQUEST['tax_input']) && isset( $_REQUEST['tax_input'][ $taxonomy ] ) ) {
					wp_set_object_terms( $id, $_REQUEST['tax_input'][ $taxonomy ], $taxonomy, false );
				} else {
					wp_set_object_terms( $id, '', $taxonomy, false );
				}
			}

			if ( ! $attachment = wp_prepare_attachment_for_js( $id ) ) {
				wp_send_json_error();
			}

			wp_send_json_success( $attachment );
		}
		add_action( 'wp_ajax_save-attachment-compat', 'bravad_media_category_save_attachment_compat', 0 );


		/** Add category checkboxes to attachment details on insert media popup */
		function bravad_media_category_attachment_fields_to_edit( $form_fields, $post ) {

			foreach ( get_attachment_taxonomies( $post->ID ) as $taxonomy ) {
				$terms = get_object_term_cache( $post->ID, $taxonomy );

				$t = (array)get_taxonomy( $taxonomy );
				if ( ! $t['public'] || ! $t['show_ui'] ) {
					continue;
				}
				if ( empty($t['label']) ) {
					$t['label'] = $taxonomy;
				}
				if ( empty($t['args']) ) {
					$t['args'] = array();
				}

				if ( false === $terms ) {
					$terms = wp_get_object_terms($post->ID, $taxonomy, $t['args']);
				}

				$values = array();

				foreach ( $terms as $term ) {
					$values[] = $term->slug;
				}

				$t['value'] = join(', ', $values);
				$t['show_in_edit'] = false;

				if ( $t['hierarchical'] ) {
					ob_start();

						wp_terms_checklist( $post->ID, array( 'taxonomy' => $taxonomy, 'checked_ontop' => false, 'walker' => new bravad_media_category_walker_media_taxonomy_checklist() ) );

						if ( ob_get_contents() != false ) {
							$html = '<ul class="term-list">' . ob_get_contents() . '</ul>';
						} else {
							$html = '<ul class="term-list"><li>No ' . $t['label'] . '</li></ul>';
						}

					ob_end_clean();

					$t['input'] = 'html';
					$t['html'] = $html;
				}

				$form_fields[$taxonomy] = $t;
			}

			return $form_fields;
		}
		add_filter( 'attachment_fields_to_edit', 'bravad_media_category_attachment_fields_to_edit', 10, 2 );


		/** Custom walker for wp_dropdown_categories for media grid view filter */
		class bravad_media_category_walker_media_taxonomy_checklist extends Walker {

			var $tree_type = 'category';
			var $db_fields = array(
				'parent' => 'parent',
				'id'     => 'term_id'
			);

			function start_lvl( &$output, $depth = 0, $args = array() ) {
				$indent = str_repeat( "\t", $depth );
				$output .= "$indent<ul class='children'>\n";
			}

			function end_lvl( &$output, $depth = 0, $args = array() ) {
				$indent = str_repeat("\t", $depth);
				$output .= "$indent</ul>\n";
			}

			function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
				extract( $args );

				// Default taxonomy
				$taxonomy = 'category';
				// Add filter to change the default taxonomy
				$taxonomy = apply_filters( 'bravad_media_category_taxonomy', $taxonomy );

				$name = 'tax_input[' . $taxonomy . ']';

				$class = in_array( $category->term_id, $popular_cats ) ? ' class="popular-category"' : '';
				$output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->slug . '" type="checkbox" name="' . $name . '[' . $category->slug . ']" id="in-' . $taxonomy . '-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters( 'the_category', $category->name ) ) . '</label>';
			}

			function end_el( &$output, $category, $depth = 0, $args = array() ) {
				$output .= "</li>\n";
			}
		}

	}

	/** Allow SVG upload from backend */
	function cc_mime_types($mimes) {
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	}
	add_filter('upload_mimes', 'cc_mime_types');


?>