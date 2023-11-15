<?php

namespace CTXFeed\V5\Helper;

use WP_Error;
use function cli\err;

class CommonHelper {


	public static function supported_product_types() {
		return array(
			'simple',
			'variable',
			'variation',
			'grouped',
			'external',
			'composite',
			'bundle',
			'bundled',
			'yith_bundle',
			'yith-composite',
			'subscription',
			'variable-subscription',
			'woosb',
		);
	}

	public static function remove_shortcodes( $content ) {
		if ( $content === '' ) {
			return '';
		}

		$content = do_shortcode( $content );

		$content = self::woo_feed_stripInvalidXml( $content );

		// Covers all kinds of shortcodes
		$expression = '/\[\/*[č?a-zA-Z1-90_| -=\'"\{\}]*\/*\]/m';

		$content = preg_replace( $expression, '', $content );

		return strip_shortcodes( $content );
	}

	public static function add_utm_parameter( $utm, $url ) {

		if ( ! empty( $utm['utm_source'] ) && ! empty( $utm['utm_medium'] ) && ! empty( $utm['utm_campaign'] ) ) {

			$utm = array(
				'utm_source'   => str_replace( ' ', '+', $utm['utm_source'] ),
				'utm_medium'   => str_replace( ' ', '+', $utm['utm_medium'] ),
				'utm_campaign' => str_replace( ' ', '+', $utm['utm_campaign'] ),
				'utm_term'     => str_replace( ' ', '+', $utm['utm_term'] ),
				'utm_content'  => str_replace( ' ', '+', $utm['utm_content'] ),
			);

			$url = add_query_arg( array_filter( $utm ), $url );

		}

		return $url;
	}

	/**
	 * Check WooCommerce Version
	 *
	 * @param string $version
	 *
	 * @return bool
	 */
	public static function wc_version_check( $version = '3.0' ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugins = get_plugins();
		if ( array_key_exists( 'woocommerce/woocommerce.php', $plugins ) ) {
			$currentVersion = $plugins['woocommerce/woocommerce.php']['Version'];
			if ( version_compare( $currentVersion, $version, '>=' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $slug
	 * @param $prefix
	 * @param $option_id
	 *
	 * @return mixed|string
	 */
	public static function unique_option_name( $slug, $prefix = '', $option_id = null ) {
		global $wpdb;
		$slug = str_replace( ' ', '_', $slug);
		if( '_' === substr($slug, -1)){
			$slug = substr($slug, 0, -1);
		}
		/** @noinspection SpellCheckingInspection */
		$slug = preg_replace("/[^A-Za-z0-9_]/", '', $slug);
		$disallowed = array( 'siteurl', 'home', 'blogname', 'blogdescription', 'users_can_register', 'admin_email' );
		if ( $option_id && $option_id > 0 ) {
			$checkSql  = "SELECT option_name FROM $wpdb->options WHERE option_name = %s AND option_id != %d LIMIT 1";
			$nameCheck = $wpdb->get_var( $wpdb->prepare( $checkSql, $prefix . $slug, $option_id ) ); // phpcs:ignore
		} else {
			$checkSql  = "SELECT option_name FROM $wpdb->options WHERE option_name = %s LIMIT 1";
			$nameCheck = $wpdb->get_var( $wpdb->prepare( $checkSql, $prefix . $slug ) ); // phpcs:ignore
		}
		// slug found or slug in disallowed list
		if ( $nameCheck || in_array( $slug, $disallowed, true ) ) {
			$suffix = 2;
			do {
				$altName = _truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
				if ( $option_id && $option_id > 0 ) {
					$nameCheck = $wpdb->get_var( $wpdb->prepare( $checkSql, $prefix . $altName, $option_id ) ); // phpcs:ignore
				} else {
					$nameCheck = $wpdb->get_var( $wpdb->prepare( $checkSql, $prefix . $altName ) ); // phpcs:ignore
				}
				$suffix ++;
			} while ( $nameCheck );
			$slug = $altName;
		}

		return $slug;
	}

	public static function get_options( $prefix ) {

		global $wpdb;
		/** @noinspection SpellCheckingInspection */
		$sql = "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s";

		// phpcs:ignore

		return $wpdb->get_results( $wpdb->prepare( $sql, $prefix . '%' ) );
	}

	/**
	 * Extends wp_strip_all_tags to fix WP_Error object passing issue
	 *
	 * @param string | WP_Error $string
	 *
	 * @return string
	 * @since 4.5.10
	 * */
	public static function woo_feed_strip_all_tags( $string ) {

		if ( $string instanceof WP_Error ) {
			return '';
		}

		return wp_strip_all_tags( $string );

	}

	/**
	 * Remove non supported xml character
	 *
	 * @param string $value
	 *
	 * @return string
	 *
	 * @since 4.5.10
	 * @see https://stackoverflow.com/questions/3466035/how-to-skip-invalid-characters-in-xml-file-using-php
	 */
	public static function woo_feed_stripInvalidXml( $value ) {
		$ret = '';
		if ( empty( $value ) ) {
			return $ret;
		}
		if ( is_int( $value ) ) {
			$ret =  $value;
		} else {
			$length = strlen( $value );
			for ( $i = 0; $i < $length; $i ++ ) {
				$current = ord( $value[ $i ] );
				if ( ( 0x9 == $current ) || ( 0xA == $current ) || ( 0xD == $current ) || ( ( $current >= 0x20 ) && ( $current <= 0xD7FF ) ) || ( ( $current >= 0xE000 ) && ( $current <= 0xFFFD ) ) || ( ( $current >= 0x10000 ) && ( $current <= 0x10FFFF ) ) ) {
					$ret .= chr( $current );
				} else {
					$ret .= '';
				}
			}
		}


		return $ret;
	}

}
