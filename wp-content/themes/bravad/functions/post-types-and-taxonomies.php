<?php
/* ====================================================================================================
	Custom post type
==================================================================================================== */

function codex_custom_init() {

	// Infolettre
/*
	register_post_type( 'newsletter',
		array(
			'labels' => array(
				'name' => __( 'Infolettre' ),
				'singular_name' => __( 'Infolettre' )
			),
		'public' => true,
		'menu_icon'=> 'dashicons-forms',
		'has_archive' => true,
		'supports' => array( 'title', 'editor', 'thumbnail' ),
		)
	);
*/

}

add_action( 'init', 'codex_custom_init' );


//===========================================
//	CUSTOM TAXONOMIES
//===========================================
/*
function bravad_custom_taxonomies() {

	// Retailer Provinces
	$labels = array(
		'name'			=> 'Province',
		'singular_name'	=> 'Province',
		'menu_name'		=> 'Province',
	);

	$args = array(
		'hierarchical'	=> true,
		'labels'		=> $labels,
		'show_admin_column' => true
	);

	register_taxonomy('province', array('retailers'), $args);

}
add_action('init', 'bravad_custom_taxonomies', 0);
*/

?>