<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_cornerstone', 10, 1 );

function wpmc_scan_postmeta_cornerstone( $id ) {
	global $wpmc;
	$postmeta_images_ids = array();
	//$postmeta_images_urls = array();
	$data = get_post_meta( $id, '_cornerstone_data', true );
	if ( !empty( $data ) ) {
		$data = json_decode( $data );
		foreach ( $data as $piece ) {
			$results = [];
			$wpmc->get_from_meta( $piece, array( 'image_src' ), $results, $results, true );
			if ( !empty( $results ) ) {
				foreach ( $results as $result ) {
					$result = substr( $result, 0, strpos( $result, ":" ) );
					if ( is_numeric( $result ) ) {
						array_push( $postmeta_images_ids, (int)$result );
					}
				}
			}
		}
	}
	$wpmc->add_reference_id( $postmeta_images_ids, 'PAGE BUILDER META (ID)', $id );
	//$wpmc->add_reference_url( $postmeta_images_urls, 'PAGE BUILDER META (URL)', $id );
}

?>