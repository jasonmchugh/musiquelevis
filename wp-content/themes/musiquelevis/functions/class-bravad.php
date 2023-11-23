<?php
/**
 * Bravad Class
 *
 * @package  Bravad
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Bravad' ) ) :

	/**
	 * The main bravad class
	 */
	class Bravad {

		/**
		 * Setup class
		 */
		public function __construct() {
			add_action( 'after_setup_theme', array( $this, 'setup' ) );
			add_action( 'admin_head', array( $this, 'bravad_admin_favicon' ) );
			add_action( 'after_switch_theme', array( $this, 'bravad_flush_rewrite' ) );

			if ( ! is_admin() ) add_action( 'init', array( $this, 'bravad_disable_emojis' ) );

			add_filter( 'body_class', array( $this, 'body_classes' ) );
			add_filter( 'wp_default_scripts', array( $this, 'bravad_remove_jquery_migrate' ) );
			add_filter( 'login_headerurl', array( $this, 'bravad_login_url' ) );
			add_filter( 'login_headertitle', array( $this, 'bravad_login_title' ) );
			add_filter( 'admin_footer_text', array( $this, 'bravad_admin_footer_text' ) );
			add_filter( 'login_enqueue_scripts', array( $this, 'bravad_custom_login' ) );
		}

		/**
		 * Sets up theme defaults and registers support for various WordPress features
		 */
		public function setup() {
			/*
			 * Load theme text
			 */
			load_theme_textdomain( 'bravad', esc_url( get_template_directory() . '/languages' ) );

			/**
			 * Add default posts and comments RSS feed links to head
			 */
			add_theme_support( 'automatic-feed-links' );

			/*
			 * Enable support for Post Thumbnails on posts and pages
			 */
			add_theme_support( 'post-thumbnails' );

			/*
			 * This theme uses wp_nav_menu() in two locations
			 */
			register_nav_menus( array(
				'primary'   => __( 'Menu principal', 'bravad' ),
				'secondary'   => __( 'Menu secondaire', 'bravad' ),
				'footer'   => __( 'Menu Footer', 'bravad' ),
				'footer2'   => __( 'Menu Footer 2', 'bravad' ),
				'footer3'   => __( 'Menu Footer 3', 'bravad' ),
				'footer4'   => __( 'Menu Footer 4', 'bravad' ),
			) );

			/*
			 * Switch default core markup for search form, comment form, comments, galleries, captions and widgets
			 * to output valid HTML5
			 */
			add_theme_support( 'html5', array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'widgets',
			) );

			/* 
			 * Image size
			 */
			// update_option( 'thumbnail_size_w', 0 );
			// update_option( 'thumbnail_size_h', 0 );
			// update_option( 'medium_size_w', 0 );
			// update_option( 'medium_size_h', 0 );
			update_option( 'medium_large_size_w', 0 );
			update_option( 'medium_large_size_h', 0 );
			update_option( 'large_size_w', 0 );
			update_option( 'large_size_h', 0 );

			/* 
			 * Declare WooCommerce support
			 */
			add_theme_support( 'woocommerce' );
			
			/*
			 * Declare support for title theme feature
			 */
			add_theme_support( 'title-tag' );

			/*
			 * Declare support for selective refreshing of widgets
			 */
			add_theme_support( 'customize-selective-refresh-widgets' );

			/*
			 * This theme uses its own gallery styles
			 */
			add_filter( 'use_default_gallery_style', '__return_false' );

			/*
			 * Remove admin bar
			 */
			add_filter( 'show_admin_bar', '__return_false' );

			/*
			 * Remove crap from head
			 */
			remove_action( 'wp_head', 'rsd_link' );
			remove_action( 'wp_head', 'wp_generator' );
			remove_action( 'wp_head', 'feed_links', 2 );
			remove_action( 'wp_head', 'index_rel_link' );
			remove_action( 'wp_head', 'wlwmanifest_link' );
			remove_action( 'wp_head', 'feed_links_extra', 3 );
			remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
			remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
			remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 );
			remove_action( 'wp_head', 'rel_canonical' );

			/*
			 * WPML
			 */
			if ( bravad_is_wpml_activated() ) {
				define( 'ICL_DONT_LOAD_NAVIGATION_CSS', true );
				define( 'ICL_DONT_LOAD_LANGUAGE_SELECTOR_CSS', true );
				define( 'ICL_DONT_LOAD_LANGUAGES_JS', true );
			}
		}

		/**
		 * Adds custom classes to the array of body classes
		 */
		public function body_classes( $classes ) {
			if ( is_multi_author() ) {
				$classes[] = 'group-blog';
			}

			if ( bravad_is_dev() ) {
				$classes[] = 'is-dev';
			}

			return $classes;
		}

		/**
		 * Adds favicon to admin
		 */
		public function bravad_admin_favicon() {
			echo sprintf( '<link rel="shortcut icon" href="%s" />',
				esc_url( get_template_directory_uri() . '/assets/img/bravad.ico' )
			);
		}

		/**
		 * Disable emojis
		 */
		public function bravad_disable_emojis() {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );

			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

			add_filter( 'tiny_mce_plugins', 'bravad_disable_emojis_tinymce' );
		}

		/**
		 * Don't load jQuery migrate on frontend
		 */
		public function bravad_remove_jquery_migrate( &$scripts ) {
			if ( ! is_admin() ) {
				$scripts->remove( 'jquery');
				$scripts->add( 'jquery', false, array( 'jquery-core' ), '1.10.2' );
			}
		}

		/**
		 * Change WP link on the admin
		 */
		public function bravad_login_url() {
			return esc_url( home_url( '/' ) );
		}

		/**
		 * Change WP title on the admin
		 */
		public function bravad_login_title() {
			return esc_html( get_bloginfo( 'name' ) );
		}

		/**
		 * Flush rewrite rules
		 */
		public function bravad_flush_rewrite() {
			flush_rewrite_rules();
		}

	}
endif;

return new Bravad();
