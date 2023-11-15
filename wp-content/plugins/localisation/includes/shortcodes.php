<?php
/**
 * Localisation Shortcodes
 *
 * @package WPCF
 * @version 0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Get template
function bravad_get_template( $template_name, $args = array(), $tempate_path = '', $default_path = '' ) {
	if ( is_array( $args ) && isset( $args ) ) :
		extract( $args );
	endif;
	$template_file = bravad_locate_template( $template_name, $tempate_path, $default_path );
	if ( ! file_exists( $template_file ) ) :
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file ), '1.0.0' );
		return;
	endif;
	include $template_file;
}


// Create the Form Shortcode
function localisation_sc_form() {
	return bravad_get_template( 'search-form.php' );
}
add_shortcode( 'localisationform', 'localisation_sc_form' );

// Create the Map Shortcode
function localisation_sc_map() {
	return bravad_get_template( 'archive-retailers.php' );
}
add_shortcode( 'localisationmap', 'localisation_sc_map' );
