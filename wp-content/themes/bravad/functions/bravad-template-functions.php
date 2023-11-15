<?php
/**
 * Bravad template functions
 *
 * @package Bravad
 */

if ( ! function_exists( 'bravad_paging_nav' ) ) {
	/**
	 * Display navigation to next/previous set of posts when applicable
	 */
	function bravad_paging_nav() {
		global $wp_query;

		$args = array(
			'type' 	    => 'list',
			'next_text' => _x( 'Suivant', 'Article suivant', 'bravad' ),
			'prev_text' => _x( 'Précédent', 'Article précédent', 'bravad' ),
		);

		the_posts_pagination( $args );
	}
}

if ( ! function_exists( 'bravad_post_nav' ) ) {
	/**
	 * Display navigation to next/previous post when applicable
	 */
	function bravad_post_nav() {
		$args = array(
			'next_text' => '%title',
			'prev_text' => '%title',
		);

		the_post_navigation( $args );
	}
}

function succ_email( $value, $field_id ) {
	if ( $field_id == 'ft_5a3d60e3d4230' ) {
		$post_id = get_the_ID();

		$value = get_field( 'courriel', $post_id );
	}

    return $value;
}

add_filter( 'ft_value', 'succ_email', 10, 2 );

/**
 * Changer Ccs quand le sujet est un vol
 */
if ( isset( $_POST['ft_5b0d5d95d1596'][0] ) ) {
	if ( $_POST['ft_5b0d5d95d1596'][0] == 'Déclaration de vol' ) {
		add_filter( 'ft_ccs', 'bravad_ccs' );
	}
}

function bravad_ccs( $ccs ) {
	$form = get_post_meta( 212, 'formtastic', true );
	$field = json_decode( $form['fields']['ft_5a148867cc7b5'] );

	foreach ( $field as $value ) {
		if ( $value->name == 'values' ) {
			$email = explode( ':', $value->value );
			$values[] = trim( $email[0] );
		}
	}

	$ccs = implode( ', ', $values );

    return $ccs;
}
