<?php
/**
 * Locate template.
 *
 * Locate the called template.
 * Search Order:
 * 1. /themes/theme/localisation/$template_name
 * 2. /themes/theme/$template_name
 * 3. /plugins/localisation/template/$template_name.
 *
 * @since 1.0.0
 *
 * @param 	string 	$template_name			Template to load.
 * @param 	string 	$string $template_path	Path to templates.
 * @param 	string	$default_path			Default path to template files.
 * @return 	string 							Path to the template file.
 */
function bravad_locate_template( $template_name, $template_path = '', $default_path = '' ) {

	// Set variable to search in localisation folder of theme.
	if ( ! $template_path ) :
		$template_path = 'localisation/';
	endif;

	// Set default plugin templates path.
	if ( ! $default_path ) :
		$default_path = plugin_dir_path( __FILE__ ) . 'template/'; // Path to the template folder
	endif;

	// Search template file in theme folder.
	$template = locate_template( array(
		$template_path . $template_name,
		$template_name
	) );

	// Get plugins template file.
	if ( ! $template ) :
		$template = $default_path . $template_name;
	endif;

	return apply_filters( 'bravad_locate_template', $template, $template_name, $template_path, $default_path );
}