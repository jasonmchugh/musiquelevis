<?php
/**
 * Localisation Function
 *
 * @package WPCF
 * @version 0.01
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * PHP to JS
 *
 * @since Bravad 1.0
 */
add_action( 'wp_enqueue_scripts', 'bravad_php_to_js' );

function bravad_php_to_js() {
	$vars = array(
		'site_url'     => home_url(),
		'template_url' => get_template_directory_uri(),
		'site_title'   => get_bloginfo( 'name' ),
		'ajax_url'     => admin_url( 'admin-ajax.php' ),
		'post_id'      => get_the_ID(),
		'you'		   => __( 'You are here', 'bravad' )
	);

	$vars['locate'] = ( isset( $_GET['locate'] ) || isset( $_GET['localisation'] ) ) ? true : false;

	if( isset( $_GET['address'] ) || isset( $_GET['adresse'] ) ) {
		if( isset( $_GET['address'] ) )
			$vars['address'] = $_GET['address'];
		if( isset( $_GET['adresse'] ) )
			$vars['address'] = $_GET['adresse'];

	} else {
		$vars['address'] = false;
	}

	wp_enqueue_script( 'localisationmap','/wp-content/plugins/localisation/includes/template/assets/js/map.min.js', array( 'jquery' ), '1.0.0', true );
	wp_localize_script( 'localisationmap', 'bravad', $vars );
}


/**
 * Map points
 *
 * @since Bravad 1.0
 */
add_action( 'wp_ajax_map_points', 'bravad_ajax_map_points' );
add_action( 'wp_ajax_nopriv_map_points', 'bravad_ajax_map_points' );

function bravad_ajax_map_points() {
	$points = array();

	$site_url = esc_url( home_url( '/' ) );

	$args = array(
		'post_type'      => array( 'retailers' ),
		'status'         => 'publish',
		'order'          => 'ASC',
		'orderby'        => 'title',
		'posts_per_page' => -1
	);

	$loop = new WP_Query( $args );

	if( $loop -> have_posts() ) :
		while( $loop -> have_posts() ) : $loop -> the_post();

			$title = get_the_title();
			$slug = get_the_permalink();
			$coord = get_field( 'adresse' );
			$phone = get_field( 'telephone' );
			$horaire = get_field( 'horaire' );
			$email = get_field( 'courriel' );
			$facebook = get_field( 'facebook' );
			$address = explode( "," , $coord['address']);

			array_push( $points,
			    array(
					'_lat'      => $coord['lat'],
					'_lng'      => $coord['lng'],
					'_title'    => $title,
					'_address'  => $address,
					'_phone'    => $phone,
					'_email'    => $email,
					'_slug'		=> $slug
			    )
			);

		endwhile;
	endif;
	wp_reset_postdata();

	wp_send_json( $points );
}


/**
 * Flushing Rewrite on theme switching
 *
 * @since Bravad 1.0
 */
add_action( 'after_switch_theme', 'bravad_rewrite_flush' );

function bravad_rewrite_flush() {
    flush_rewrite_rules();
}
