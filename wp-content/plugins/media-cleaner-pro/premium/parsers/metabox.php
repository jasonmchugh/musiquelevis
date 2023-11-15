<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_download_monitor', 10, 2 );

function wpmc_scan_postmeta_download_monitor( $id ) {
  global $wpmc;
  $fields = rwmb_get_object_fields( $id );
  if ( !empty( $fields ) ) {
    foreach ( $fields as $field ) {
      $value = rwmb_get_value( $field['id'], null, (int)$id );
      if ( $field['type'] === 'image_upload' && !empty( $value ) ) {
        foreach ( $value as $id => $image ) {
          $url = $wpmc->clean_url( $image['url'] );
          $full_url = $wpmc->clean_url( $image['full_url'] );
          $wpmc->add_reference_url( $url, 'METABOX (URL)', $id );
          $wpmc->add_reference_url( $full_url, 'METABOX (URL)', $id );
          $wpmc->add_reference_id( $id, 'METABOX (ID)', $id );
        }
      }
    }
  }
}

?>