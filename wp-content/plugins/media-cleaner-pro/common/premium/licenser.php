<?php

//phpcs:disable

if ( !class_exists( 'MeowCommonPro_Licenser' ) ) {

	class MeowCommonPro_Licenser {
		public $license = null;
		public $prefix; 		// prefix used for actions, filters (mfrh)
		public $mainfile; 	// plugin main file (media-file-renamer.php)
		public $domain; 		// domain used for translation (media-file-renamer)
		public $item; 	    // name of the Pro plugin (Media File Renamer Pro)
		public $version; 	  // version of the plugin (Media File Renamer Pro)

		public function __construct( $prefix, $mainfile, $domain, $item, $version ) {
			$this->prefix = $prefix;
			$this->mainfile = $mainfile;
			$this->domain = $domain;
			$this->item = $item;
			$this->version = $version;

			if ( $this->is_registered() ) {
				add_filter( $this->prefix . '_meowapps_is_registered', array( $this, 'is_registered' ), 10 );
			}
			
			if ( MeowCommon_Helpers::is_rest() ) {
				new MeowCommonPro_Rest_License( $this );
			}
			else if ( is_admin() ) {
				$license_key = $this->license && isset( $this->license['key'] ) ? $this->license['key'] : "";
				new MeowCommonPro_Updater(
					( get_option( 'force_sslverify', false ) ? 'https' : 'http' ) . '://meowapps.com', $this->mainfile,
					array(
						'version' => $this->version,
						'license' => $license_key,
						'item_name' => $this->item,
						'wp_override' => true,
						'author' => 'Jordy Meow',
						'url' => strtolower( home_url() ),
						'beta' => false
					)
				);
			}
		}

		function retry_validation() {
			if ( isset( $_POST[$this->prefix . '_pro_serial'] ) ) {
				$serial = sanitize_text_field( $_POST[$this->prefix . '_pro_serial'] );
				$this->validate_pro( $serial );
			}
		}

		function is_registered( $force = false ) {
			if ( !$force && !empty( $this->license ) ) {
				$has_no_issues = empty( $this->license['issue'] );
				return $has_no_issues;
			}
			$this->license = get_option( $this->prefix . '_license', "" );
			if ( empty( $this->license ) || !empty( $this->license['issue'] ) ) {
				return false;
			}
			if ( $this->license['expires'] == "lifetime" ) {
				return true;
			}
			$datediff = strtotime( $this->license['expires'] ) - time();
			$days = floor( $datediff / ( 60 * 60 * 24 * 7 * 3 ) );
			if ( $days < 0 ) {
				$this->validate_pro( $this->license['key'] );
			}
			return true;
		}

		function validate_pro( $subscr_id, $override = false ) {
			$prefix = $this->prefix;
			delete_option( $prefix . '_license', "" );
			if ( empty( $subscr_id ) ) {
				return false;
			}

			if ( $override ) {
				// This doesn't work with updates.
				$current_user = wp_get_current_user();
				delete_option( '_site_transient_update_plugins' );
				$url = 'https://meowapps.com/?edd_action=activate_license' .
					'&item_name=' . urlencode( $this->item ) .
					'&license=' . $subscr_id . '&url=' . strtolower( home_url() );
				update_option( $prefix . '_license',  array( 'key' => $subscr_id, 'issue' => null,
					'logs' => sprintf( "Forced by %s on %s.", $current_user->user_email, date( "Y/m/d" ) ),
					'expires' => 'lifetime', 'license' => null, 'check_url' => $url ) );
				//return $this->is_registered( true );
			}
			else {
				// Only https is accepted by Meow Apps
				$url = ( get_option( 'force_sslverify', false ) ? 'https' : 'https' ) .
					'://meowapps.com/?edd_action=activate_license' .
					'&item_name=' . urlencode( $this->item ) .
					'&license=' . $subscr_id . '&url=' . strtolower( home_url() ) . '&cache=' . bin2hex( openssl_random_pseudo_bytes( 4 ) );
				$response = wp_remote_get( $url, array(
						'user-agent' => "MeowApps",
						'sslverify' => get_option( 'force_sslverify', false ),
						'timeout' => 45,
						'method' => 'GET'
					)
				);
				$body = is_array( $response ) ? $response['body'] : null;
				$post = @json_decode( $body );
				$status = null;
				$license = null;
				$expires = null;
				$logs = null;
				if ( !$post || ( property_exists( $post, 'code' ) ) ) {
					$status = 'error';
					// $status = __( "There was an error while validating the serial.<br />Please contact <a target='_blank' href='https://meowapps.com/contact/'>Meow Apps</a> and mention the following log: <br /><ul>", $this->domain );
					$logs = "<li>Server IP: <b>" . gethostbyname( $_SERVER['SERVER_NAME'] ) . "</b></li>";
					$logs .= "<li>Google Get: ";
					$r = wp_remote_get( 'http://google.com' );
					$logs .= is_wp_error( $r ) ? print_r( $r, true ) : 'OK';
					$logs .= "</li><li>MeowApps Get: ";
					$r = wp_remote_get( 'http://meowapps.com' );
					$logs .= is_wp_error( $r ) ? print_r( $r, true ) : 'OK';
					$logs .= "</li><li>MeowApps License:<br /><br />";
					$logs .= "REQUEST: $url<br /><br />";
					$logs .= "RESPONSE: ";
					$logs .= print_r( $response, true );
					$logs .= "</li></ul>";
					error_log( print_r( $response, true ) );
				}
				else if ( $post->license !== "valid" ) {
					$status = $post->error ;
				}
				else {
					$license = $post->license;
					$expires = $post->expires;
					delete_option( '_site_transient_update_plugins' );
				}
				update_option( $prefix . '_license', array( 'key' => $subscr_id, 'issue' => $status,
					'logs' => $logs, 'expires' => $expires, 'license' => $license ) );
			}
			return $this->is_registered( true );
		}
	}
}

?>
