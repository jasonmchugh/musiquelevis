<?php
/**
 * Bravad functions
 *
 * @package Bravad
 */

if ( ! function_exists( 'bravad_is_wpml_activated' ) ) {
	/**
	 * Query WPML activation
	 */
	function bravad_is_wpml_activated() {
		return class_exists( 'SitePress' ) ? true : false;
	}
}

if ( ! function_exists( 'bravad_is_dev' ) ) {
	/**
	 * Check if dev
	 */
	function bravad_is_dev() {
		return strpos( $_SERVER['HTTP_HOST'], 'bravad-dev.com' ) ? true : false;
	}
}

if ( ! function_exists( 'bravad_language_code' ) ) {
	/**
	 * Get language code
	 */
	function bravad_language_code() {
		if ( bravad_is_wpml_activated() ) {
			$code = ICL_LANGUAGE_CODE;
		} else {
			$code = get_locale();
		}

		$code = substr( $code, 0, 2 );

		return $code;
	}
}

if ( ! function_exists( 'bravad_get_slug' ) ) {
	/**
	 * Get the current post slug
	 */
	function bravad_get_slug( $id ) {
		global $post;

		$post_id   = ( empty( $id ) ) ? $post->ID : $id;
		$post_data = get_post( $post_id, ARRAY_A );
		$slug      = $post_data['post_name'];

		return esc_attr( $slug );
	}
}

if ( ! function_exists( 'bravad_dump' ) ) {
	/**
	 * Custom var dump
	 */
	function bravad_dump( $var ) {
		echo '<pre>';
		var_dump( $var );
		echo '</pre>';
	}
}

if ( ! function_exists( 'bravad_date' ) ) {
	/**
	 * Custom translated date
	 */
	function bravad_date( $date ) {
		$date_arr = explode( '-', $date );
		$y = $date_arr[0];
		$m = $date_arr[1];
		$d = $date_arr[2];

		$m = ( substr( $m, 1 ) ) ? substr( $m, 1 ) : $m;

		$months = array(
			'',
			_x( 'janvier', 'bravad_date()', 'bravad' ),
			_x( 'février', 'bravad_date()','bravad' ),
			_x( 'mars', 'bravad_date()','bravad' ),
			_x( 'avril', 'bravad_date()','bravad' ),
			_x( 'mai', 'bravad_date()','bravad' ),
			_x( 'juin', 'bravad_date()','bravad' ),
			_x( 'juillet', 'bravad_date()','bravad' ),
			_x( 'août', 'bravad_date()','bravad' ),
			_x( 'septembre', 'bravad_date()','bravad' ),
			_x( 'octobre', 'bravad_date()','bravad' ),
			_x( 'novembre', 'bravad_date()','bravad' ),
			_x( 'décembre', 'bravad_date()','bravad' ),
		);

		$month = $months[$m];

		if ( bravad_language_code() == 'en' ) {
			return $month . ' ' . $d . ', ' . $y;
		} else {
			return $d . ' ' . $month . ' ' . $y;
		}
	}
}

// ACF Options pages
if(function_exists('acf_add_options_page')) {
	acf_add_options_page('Options');
}