<?php
/**
 * Localisation register styles, scripts and Custom post
 *
 * @package WPCF
 * @version 0.01
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Enqueue admin styles and scripts
 *
 * @return void
 */
function wpcf_admin_styles_scripts() {
	wp_enqueue_style( 'wpcf-admin-styles', plugins_url() . '/wpc-functions/assets/css/wpcf-admin.css', array(), '1.0.0' );
// 	wp_enqueue_script( 'wpcf-admin-scripts', plugins_url() . '/wpc-functions/assets/js/wpcf-admin.js', array( 'jquery' ), '1.0.0', true );
}
//add_action( 'admin_enqueue_scripts', 'wpcf_admin_styles_scripts' );


/**
 * Register retailers post type
 *
 */
add_action( 'init', 'bravad_retailers_init' );

function bravad_retailers_init() {
	$labels = array(
		'name'               => __( 'Succursales', 'bravad' ),
		'singular_name'      => __( 'Succursale', 'bravad' ),
		'menu_name'          => __( 'Succursales', 'bravad' ),
		'name_admin_bar'     => __( 'Succursales', 'bravad' ),
		'add_new'            => __( 'Ajouter', 'bravad' ),
		'add_new_item'       => __( 'Ajouter une succursale', 'bravad' ),
		'new_item'           => __( 'Nouvelle succursale', 'bravad' ),
		'edit_item'          => __( 'Modifier', 'bravad' ),
		'view_item'          => __( 'Voir', 'bravad' ),
		'all_items'          => __( 'Toutes les succursales', 'bravad' ),
		'search_items'       => __( 'Chercher une succursale', 'bravad' ),
		'parent_item_colon'  => __( 'Succursale parent', 'bravad' ),
		'not_found'          => __( 'Aucune succursale trouvée', 'bravad' ),
		'not_found_in_trash' => __( 'Aucune succursale trouvée', 'bravad' )
	);

	$args = array(
		'labels'              => $labels,
		'public'              => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'query_var'           => true,
		'rewrite'             => array( 'slug' => _x('succursales', 'URL slug', 'bravad'), 'with_front' => false ),
		'capability_type'     => 'post',
		'has_archive'         => false,
		'hierarchical'        => false,
		'menu_position'       => 10,
		'menu_icon'			  => 'dashicons-store',
		'supports'            => array() // 'trackbacks', 'custom-fields', 'page-attributes', 'post-formats'
	);

	register_post_type( 'retailers', $args );
}