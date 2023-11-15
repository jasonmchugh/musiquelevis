<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_acf', 10, 0 );
add_action( 'wpmc_scan_post', 'wpmc_scan_html_acf', 10, 2 );
add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_acf', 10, 1 );

function wpmc_scan_once_acf() {
	$wpmc_acf_blocks = array();
	wpmc_scan_postmeta_acf( 'options' );
	wpmc_scan_once_taxonomies_acf();

	// Handle ACF Blocks
	if ( function_exists( 'acf_get_block_types' ) ) {
		$blocks = acf_get_block_types();
		foreach ( $blocks as $block ) {
			array_push( $wpmc_acf_blocks, $block['name'] );
		}
	}

	set_transient( 'wpmc_acf_blocks', $wpmc_acf_blocks, MONTH_IN_SECONDS );
}

// Analyze an ACF Block
function wpmc_scan_acf_block( $blockData, &$images_ids, &$images_urls ) {
	foreach ( $blockData as $field => $value ) {
		if ( !empty( $field ) && $field[0] === '_' ) {
			$fieldObject = get_field_object( $value );
			if ( !empty( $fieldObject ) && $fieldObject['type'] === 'image' ) {
				$realField = ltrim( $field, '_' );
				$realValue = $blockData[$realField];
				if ( !empty( $realValue ) ) {
					array_push( $images_ids, $realValue );
				}
			}
		}
	}
}

function wpmc_scan_html_acf( $html, $id ) {
	global $wpmc;
	$images_ids = array();
	$images_urls = array();

	$post = get_post( $id );
	if ( has_blocks( $post ) ) {
		$blocks = parse_blocks( $post->post_content );
		if ( !empty( $blocks ) ) {
			$wpmc_acf_blocks = get_transient( 'wpmc_acf_blocks' );
			foreach ( $blocks as $block ) {
				if ( in_array( $block['blockName'], $wpmc_acf_blocks ) ) {
					$data = $block['attrs']['data'];
					wpmc_scan_acf_block( $data, $images_ids, $images_urls );
				} 
				else if ( $block['blockName'] === 'core/block' ) {
					$block_content = parse_blocks( get_post( $block['attrs']['ref'] )->post_content );
					if ( in_array( $block_content[0]['blockName'], $wpmc_acf_blocks ) ) {
						$data = $block_content[0]['attrs']['data'];
						wpmc_scan_acf_block( $data, $images_ids, $images_urls );
					}
				}
			}
		}
	}

	$wpmc->add_reference_id( $images_ids, 'ACF BLOCK (ID)', $id );
	$wpmc->add_reference_url( $images_urls, 'ACF BLOCK (URL)', $id );
}

function wpmc_scan_once_taxonomies_acf() {
	global $wpdb;
	$terms = $wpdb->get_results( "SELECT x.term_id, x.taxonomy 
		FROM {$wpdb->term_taxonomy} x, {$wpdb->termmeta} y 
		WHERE x.term_id = y.term_id 
		GROUP BY x.term_id, x.taxonomy"
	);
	foreach ( $terms as $term ) {
		$termStr = $term->taxonomy . '_' . $term->term_id;
		$fields = get_field_objects( $termStr );
		if ( !empty( $fields ) ) {
			//error_log( 'ACF Fields found for Taxonomy+Term ' . $termStr );
			if ( is_array( $fields ) ) {
				foreach ( $fields as $field )
					wpmc_scan_postmeta_acf_field( $field, $termStr, 16 );
			}
		}
	}
}

function wpmc_scan_postmeta_acf( $id ) {
	$fields = get_field_objects( $id );
	if ( is_array( $fields ) ) {
		foreach ( $fields as $field ) {
			wpmc_scan_postmeta_acf_field( $field, $id, 16 );
		}
	}
}

function wpmc_scan_postmeta_acf_field_block( $block, $id, $postmeta_images_acf_ids,
	$postmeta_images_acf_urls, $recursion_limit = -1 ) { 
	if ( $recursion_limit === 0 ) {
		return;
	}
	if ( is_array( $block ) && isset( $block['type'] ) ) {
		wpmc_scan_postmeta_acf_field( $block, $id, $recursion_limit - 1 );
	}
	else if ( is_array( $block ) ) {
		foreach ( $block as $value ) {
			wpmc_scan_postmeta_acf_field_block( $value, $postmeta_images_acf_ids,
					$postmeta_images_acf_urls, $recursion_limit - 1 );
		}
	}
}

/**
 * Scans a single ACF field object.
 * If the specified field is a repeater or a flexible content,
 * scans each subfield recursively.
 *
 * @param array $field
 * An associative array replesenting a single ACF field.
 * The actual array must be structured like this:
 * array (
 *   'name'  => The name of the field
 *   'type'  => The field type i.e. 'text', 'object', 'repeater'
 *   'value' => The value
 *   ...
 * )
 * @param int $id The post ID
 * @param int $recursion_limit The max recursion depth. Negative number means unlimited
 *
 * @since ACF 5.6.10
 */
function wpmc_scan_postmeta_acf_field( $field, $id, $recursion_limit = -1 ) {
	if ( !isset( $field['type'] ) ) return;
	// $field['key'] == field_620085150f4e7
	global $wpmc;

	/** Multiple Fields (Repeater or Flexible Content) **/
	static $recursives = array ( // Possibly Recursive Types
		'repeater',
		'flexible_content',
		'group'
	);

	// $label = $field['label'];
	// if ( $field['label'] === 'Link 1' ) {
	// 	$value = $field['value'];
	// }

	$is_recursive = in_array( $field['type'], $recursives );
	// have_rows (ACF): This function checks to see if a parent field (such as Repeater
	// or Flexible Content) has any rows of data to loop over.
	$is_recursive_with_rows = $is_recursive && have_rows( $field['name'], $id );

	// Recursive through ACF
	if ( $is_recursive_with_rows ) {
		if ( $recursion_limit == 0 ) return; // Too much recursion
		do {
			$row = the_row( true );
			foreach ( $row as $col => $value ) { // Iterate over columns (subfields)
				$subfield = get_sub_field_object( $col, true, true );
				if ( !is_array( $subfield ) ) {
					continue;
				}
				wpmc_scan_postmeta_acf_field( $subfield, $id, $recursion_limit - 1 ); // Recursion
			}
		} while ( have_rows( $field['name'], $id ) );
		return;
	}
	
	// Ignore certain ACF types to improve speed
	if ( in_array( $field['type'], [ 'color_picker' ] ) ) {
		return;
	}

	$postmeta_images_acf_ids = array();
	$postmeta_images_acf_urls = array();

	/** Block Field **/

	// FOR DEBUG
	// if ( $field['key'] == 'field_620085150f4e7' ) {
	// 	$a = "";
	// }

	// Recursive in groups, super annoying, as it's hard to know what's happening
	if ( $is_recursive && is_array( $field['value'] ) ) {
		wpmc_scan_postmeta_acf_field_block( $field['value'], $id, $postmeta_images_acf_ids,
				$postmeta_images_acf_urls, $recursion_limit - 1 );
		return;
	}

	/** Singular Field **/

	$format = "";
	if ( isset( $field['return_format'] ) )
		$format = $field['return_format'];
	else if ( isset( $field['save_format'] ) )
		$format = $field['save_format'];


	// For Dev: This is a debugging conditional helper.
	// if ( in_array( $field['name'], [ 'bild' ] ) ) {
	// 	$value = $field['name'];
	// 	$type = $field['type'];
	// 	$format = $field['format'];
	// }

	// ACF Image ID and URL
	if ( $field['type'] == 'image' && ( $format == 'array' || $format == 'object' ) ) {
		if ( !empty( $field['value']['id'] ) )
			array_push( $postmeta_images_acf_ids, $field['value']['id'] );
		if ( !empty( $field['value']['url'] ) )
			array_push( $postmeta_images_acf_urls, $wpmc->clean_url( $field['value']['url'] ) );
	}
	// ACF Image ID
	else if ( $field['type'] == 'image' && $format == 'id' && !empty( $field['value'] ) ) {
		array_push( $postmeta_images_acf_ids, $field['value'] );
	}
	// ACF Image URL
	else if ( $field['type'] == 'image' && $format == 'url' && !empty( $field['value'] ) ) {
		array_push( $postmeta_images_acf_urls, $wpmc->clean_url( $field['value'] ) );
	}
	// ACF Image Attachment (From Block?)
	else if ( ( $field['type'] == 'image' || $field['type'] == 'video' ) && $field['status'] == 'inherit' ) {
		$id = $field['id'];
		$url = $field['url'];
		array_push( $postmeta_images_acf_ids, $id );
		array_push( $postmeta_images_acf_urls, $wpmc->clean_url( $url ) );
	}
	// ACF Gallery
	else if ( $field['type'] == 'gallery' && !empty( $field['value'] ) ) {
		foreach ( $field['value'] as $media ) {

			// From 6.2.5
			if ( $format === 'url' ) {
				array_push( $postmeta_images_acf_urls, $wpmc->clean_url( $media ) );
			}
			if ( $format === 'id' ) {
				array_push( $postmeta_images_acf_ids, $media );
			}
			if ( $format === 'array' ) {
				if ( isset( $media['id'] ) ) {
					array_push( $postmeta_images_acf_ids, $media['id'] );
				}
			}

			// Before 6.2.5
			// if ( !empty( $media['id'] ) ) {
			// 	array_push( $postmeta_images_acf_ids, $media['id'] );
			// }
		}
	}
	// ACF Photo Gallery
	else if ( $field['type'] == 'photo_gallery' && !empty( $field['value'] ) ) {
		foreach ( $field['value'] as $media ) {
			array_push( $postmeta_images_acf_ids, $media['id'] );
			array_push( $postmeta_images_acf_urls, $wpmc->clean_url( $media['full_image_url'] ) );
			array_push( $postmeta_images_acf_urls, $wpmc->clean_url( $media['thumbnail_image_url'] ) );
		}
	}
	// ACF Wysiwyg
	else if ( $field['type'] == 'wysiwyg' && !empty( $field['value'] ) ) {
		$urls = $wpmc->get_urls_from_html( $field['value']  );
		foreach ( $urls as $url ) {
			array_push( $postmeta_images_acf_urls, $url );
		}
	}
	// ACF File
	else if ( $field['type'] == 'file' && !empty( $field['value'] ) ) {
		$value = $field['value'];
		if ( is_array( $value ) ) {
			$value = $value['url'];
		}
		array_push( $postmeta_images_acf_urls, $wpmc->clean_url( $value ) );
	}
	// ACF Aspect Ratio Crop
	else if ( $field['type'] == 'image_aspect_ratio_crop' && !empty( $field['value'] ) ) {
		$value = $field['value'];
		if ( is_array( $value ) ) {
			$id = $value['id']; // Latest crop
			array_push( $postmeta_images_acf_ids, $id );
			$id = $value['original_image']['id']; // Original image
			array_push( $postmeta_images_acf_ids, $id );
		}
	}
	// ACF Clone 
	else if ( $field['type'] === 'clone' && is_array( $field['value'] ) ) {
		if ( isset( $field['value']['media_type'] ) && $field['value']['media_type'] === 'image' ) {
			array_push( $postmeta_images_acf_ids, $field['value']['media_image']['id'] );
		}
	}
	else if ( $field['type'] === 'repeater' && is_array( $field['value'] ) ) {
		// Handle this case: https://secure.helpscout.net/conversation/1523898475/7197?folderId=1781329
		foreach ( $field['value'] as $value ) {
			if ( isset( $value['image'] ) && is_int( $value['image'] ) ) {
				array_push( $postmeta_images_acf_ids, $value['image'] );
			}
		}
	}
	// Video-URL
	else if ( $field['type'] === 'url' && !empty( $field['value'] ) && !is_array( $field['value'] ) ) {
		$value = $field['value'];
		array_push( $postmeta_images_acf_urls, $wpmc->clean_url( $value ) );
	}
	else {
		// $field_type = $field['type'];
		// $format = $field['format'];
		// $label = $field['label'];
		// $value = $field['value'];
		// $id = $field['id'];
		// if ( $label === 'Videos' ) {
		// 	$id = $field['id'];
		// }

		// if ( $id == 23586 ) {
		// 	$value = $field['value'];
		// }

		// if ( $label === 'Videos' ) {
		// 	$value = $field['value'];
		// }
		// if ( $label === 'Intro' ) {
		// 	$value = $field['value'];
		// }
		// if ( $label === 'Image' ) {
		// 	$value = $field['value'];
		// }
		// if ( $label === 'Link' ) {
		// 	$value = $field['value'];
		// }

		// if ( $field['type'] !== 'text' && $field['type'] !== 'wysiwyg' ) {
		// 	$field['format'] = $field['format'] ?? "N/A";
		// 	error_log( 'ACF field not supported (' . $field['name'] . ') : ' . 
		// 		$field['type'] . ' -> ' . $field['format'] . ' = ' . print_r( $field['value'], 1 ) );
		// }
		return;
	}

	$wpmc->add_reference_id( $postmeta_images_acf_ids, 'ACF (ID)', $id );
	$wpmc->add_reference_url( $postmeta_images_acf_urls, 'ACF (URL)', $id );
}

?>