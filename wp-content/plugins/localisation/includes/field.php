<?php
/**
 * Localisation Custom Fields
 *
 * @package WPCF
 * @version 0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Load field
 *
 * @since Localisation 0.1
 */
add_filter( 'acf/load_field', 'localisation_load_field' );

function localisation_load_field( $field ) {
	if ( get_post_type() == 'retailers' ) {
		switch ( $field['key'] ) {
			case 'field_5759ab4fbc3a9' : // Shortcode
				$form_id = get_the_ID();

				$field['label'] = null;
			    $field['value'] = '[localisation id="' . $form_id . '" title="' . get_the_title() . '"]';
			    $field['readonly'] = true;
			    break;

			case 'field_576bd88bf00fe' : // ID
				$field['readonly'] = true;
				break;

			case 'field_57d7131d16bda' : // Hide ID
			case 'field_581ddb2eea184' : // Confirmation
				$field['label'] = null;
				break;
		}
	}

	if ( get_post_type() == 'ft_answer' ) {
		switch ( $field['key'] ) {
			case 'field_576745e40ce96' : // Read
				$field['label'] = null;
				break;

			case 'field_57693a2dcb49a' : // Name
			case 'field_57693a4dcb49b' : // Email
			case 'field_57c5d492e32b2' : // Date
				$field['readonly'] = true;
				break;
		}
	}

	switch ( $field['key'] ) {
		case 'field_57dd1f457cf7d' : // Save to wordpress
			$field['label'] = null;
			break;
	}

    return $field;
}

/**
 * Read field
 *
 * @since localisation 1.0.0
 */
add_action( 'admin_head-post.php', 'localisation_edit_response' );

function localisation_edit_response( $post_id ) {
	if ( get_post_type() == 'ft_answer' ) {
		$read = get_field( 'ft_read', $post_id );

		update_field( 'field_576745e40ce96', array( 'read' ), $post_id );
	}
}

/**
 * Update ID
 *
 * @since localisation 1.0.0
 */
add_filter( 'acf/update_value/key=field_576bd88bf00fe', 'localisation_id_update_value', 10, 3 );

function localisation_id_update_value( $value, $post_id, $field  ) {
	$unique_id = uniqid( 'localisation_' );

	if ( empty( $value ) )
		$value = $unique_id;

	return $value;
}

/**
 * Form field
 *
 * @since localisation 1.0.0
 */
add_filter( 'acf/prepare_field/key=field_57692d591e388', 'localisation_form_field' );

function localisation_form_field( $field ) {
	global $post;

	$post_id = $post->ID;

	if ( get_post_type() == 'ft_answer' ) {
		$form_id = get_field( 'ft_form', $post_id );

		$field['readonly'] = true;
		$field['value'] = get_the_title( $form_id );
	}

    return $field;
}

/**
 * Attachment field
 *
 * @since localisation 1.0.0
 */
add_filter( 'acf/prepare_field/key=field_57d3d49789f86', 'localisation_attachment_field' );

function localisation_attachment_field( $field ) {
	global $post;

	$post_id = $post->ID;
	$files = get_field( 'ft_files', $post_id );
	$output = '';

	if ( isset( $files ) && !empty( $files ) ) {
		foreach ( $files as $file ) {
			$output .= '<small>' . $file['field'] . '</small><a href="' . $file['url'] . '" target="_blank">' . $file['filename'] . '</a>';
		}

		$field['label'] = '<span class="dashicons dashicons-paperclip"></span> ' . __( 'Pièce(s) jointe(s)', 'localisation' ) . ' :';
		$field['message'] = $output;

	} else {
		$field['label'] = null;
	}

    return $field;
}
