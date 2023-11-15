<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_elementor', 10, 1 );

function wpmc_scan_postmeta_elementor( $id ) {
	global $wpmc;
	$ids = array();
	$urls = array();
  $data = get_post_meta( $id, '_elementor_data' );

	// FOR DEBUGGING
	// if ( $id == "23232" ) {
	// 	error_log( print_r( $data, 1 ) );
	// }
	
	if ( isset( $data[0] ) ) {
		if ( is_array( $data[0] ) ) {
			error_log( "Media Cleaner: Elementor data is an array (not supported yet), Post ID: $id" );
		}
		else {
			$decoded = json_decode( $data[0] );
			$wpmc->get_from_meta( $decoded, array( 'url', 'background_image' ), $ids, $urls );
		}
	}
	$wpmc->add_reference_id( $ids, 'PAGE BUILDER META (ID)', $id );
	$wpmc->add_reference_url( $urls, 'PAGE BUILDER META (URL)', $id );
}

?>