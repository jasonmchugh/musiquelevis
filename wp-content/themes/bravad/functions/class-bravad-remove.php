<?php
/**
 * Bravad Remove Class
 *
 * @package  Bravad
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Bravad_Remove class
 */
class Bravad_Remove {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu_items' ) );
		add_action( 'wp_before_admin_bar_render', array( __CLASS__, 'admin_bar_items' ) );
// 		add_action( 'wp_dashboard_setup', array( __CLASS__, 'dashboard_widgets' ) );
		add_action( 'admin_init', array( __CLASS__, 'remove_metaboxes' ) );
		add_action( 'admin_menu', array( __CLASS__, 'footer_version' ) );

		// add_filter( 'manage_posts_columns', array( __CLASS__, 'post_columns' ) );
		// add_filter( 'manage_pages_columns', array( __CLASS__, 'page_columns' ) );

		/**
		 * WPML
		 */
		// add_action( 'admin_head', array( __CLASS__, 'wpml_metaboxes' ), 99 );

		/**
		 * ACF
		 */
// 		add_filter( 'acf/settings/show_admin', '__return_false' );
	}

	/**
	 * Remove menu items
	 */
	public static function menu_items() {
		global $current_user;
		global $menu;
		global $submenu;

		// Display “Appearance > Menu” in the top level menu
		remove_submenu_page( 'themes.php', 'nav-menus.php' );
		$menu[] = array( __( 'Menus', 'theme-slug' ), 'edit_theme_options', 'nav-menus.php', __( 'Menus', 'theme-slug' ), 'menu-top menu-nav', 'menu-nav', 'dashicons-menu');

		// Remove items (non-admins)
//			remove_menu_page( 'index.php' ); 				// Dashboard
			remove_menu_page( 'edit.php' ); 				// Posts
//			remove_menu_page( 'upload.php' ); 				// Media
//			remove_menu_page( 'edit.php?post_type=page' );	// Pages
			remove_menu_page( 'edit-comments.php' ); 		// Comments
//			remove_menu_page( 'themes.php' ); 				// Appearance
//			remove_menu_page( 'plugins.php' ); 				// Plugins
//			remove_menu_page( 'users.php' ); 				// Users
//			remove_menu_page( 'tools.php' ); 				// Tools
//			remove_menu_page( 'options-general.php' ); 		// Settings
// 			remove_menu_page( 'sitepress-multilingual-cms/menu/languages.php' ); // WPML
// 			remove_menu_page( 'wpseo_dashboard' ); 			// SEO Yoast
// 			remove_menu_page( 'Wordfence' );				// Wordfence


			unset( $submenu['index.php'][10] ); 		  	// Update
			unset( $submenu['themes.php'][5] ); 		  	// Themes
			//unset( $submenu['themes.php'][6] ); 		  	// Customize
			// unset( $submenu['themes.php'][7] ); 		  	// Widgets
			unset( $submenu['themes.php'][10] ); 		  	// Menu
			unset( $submenu['options-general.php'][15] ); 	// Writing
			unset( $submenu['options-general.php'][25] ); 	// Discussion
			unset( $submenu['edit.php'][16] ); 			  	// Tags

			remove_action( 'admin_menu', '_add_themes_utility_last', 101 ); // Editor

		// Allow editors to edit menus
		if (in_array('editor', $current_user->roles)) {
			$role_object = get_role('editor');
			$role_object->add_cap('edit_theme_options');
		}
	}

	/**
	 * Remove admin bar items
	 */
	public static function admin_bar_items() {
	    global $wp_admin_bar;

//	    $wp_admin_bar->remove_menu('wp-logo'); 		  // WordPress logo
	    $wp_admin_bar->remove_menu('about'); 		  // About WordPress
	    $wp_admin_bar->remove_menu('wporg'); 		  // WordPress.org
	    $wp_admin_bar->remove_menu('documentation');  // WordPress documentation
	    $wp_admin_bar->remove_menu('support-forums'); // Support forums
	    $wp_admin_bar->remove_menu('feedback'); 	  // Feedback
// 	    $wp_admin_bar->remove_menu('site-name'); 	  // Site name menu
//	    $wp_admin_bar->remove_menu('view-site'); 	  // View site
//	    $wp_admin_bar->remove_menu('updates'); 	  	  // Updates
	    $wp_admin_bar->remove_menu('comments'); 	  // Comments
	    $wp_admin_bar->remove_menu('new-content'); 	  // Content
// 	    $wp_admin_bar->remove_menu('wpseo-menu'); 	  // SEO Yoast
//	    $wp_admin_bar->remove_menu('my-account'); 	  // User details tab
//	    $wp_admin_bar->remove_node('user-info');	  // User info
//	    $wp_admin_bar->remove_node('edit-profile');   // User profile
	}

	/**
	 * Dashboard widgets
	 */
	public static function dashboard_widgets() {
		global $wp_meta_boxes;

		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity'] ); 		 // Activity
		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins'] ); 		 // Plugins
		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments'] ); // Recent comments
		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links'] );  // Incoming links
		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now'] ); 		 // Today
		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_primary'] ); 			 // Wordpress blog
		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary'] ); 		 // Wordpress news
		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press'] ); 		 // Quick press
		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts'] ); 	 // Recent drafts

		remove_meta_box( 'icl_dashboard_widget', 'dashboard', 'normal' ); // WPML

// 		remove_meta_box( 'wpseo-dashboard-overview', 'dashboard', 'normal' ); // SEO Yoast
	}

	/**
	 * Metaboxes
	 */
	public static function remove_metaboxes() {
		remove_meta_box( 'authordiv', 		 'post', 'normal' ); // Author
// 		remove_meta_box( 'categorydiv', 	 'post', 'normal' ); // Categories
		remove_meta_box( 'commentsdiv', 	 'post', 'normal' ); // Comments
		remove_meta_box( 'commentstatusdiv', 'post', 'normal' ); // Comment status
		remove_meta_box( 'formatdiv', 		 'post', 'normal' ); // Format
		remove_meta_box( 'postcustom', 		 'post', 'normal' ); // Custom
		remove_meta_box( 'postexcerpt', 	 'post', 'normal' ); // Excerpt
// 		remove_meta_box( 'revisionsdiv', 	 'post', 'normal' ); // Revision
		remove_meta_box( 'slugdiv', 		 'post', 'normal' ); // Slug
		remove_meta_box( 'tagsdiv-post_tag', 'post', 'normal' ); // Tags
		remove_meta_box( 'trackbacksdiv', 	 'post', 'normal' ); // Trackback

		remove_meta_box( 'authordiv', 		 'page', 'normal' ); // Author
		remove_meta_box( 'commentsdiv', 	 'page', 'normal' ); // Comments
		remove_meta_box( 'commentstatusdiv', 'page', 'normal' ); // Comment status
// 		remove_meta_box( 'pageparentdiv', 	 'page', 'normal' ); // Parent
		remove_meta_box( 'postcustom', 		 'page', 'normal' ); // Custom
// 		remove_meta_box( 'revisionsdiv', 	 'page', 'normal' ); // Revision
		remove_meta_box( 'slugdiv', 		 'page', 'normal' ); // Slug
		remove_meta_box( 'trackbacksdiv', 	 'page', 'normal' ); // Trackback
	}

	/**
	 * WPML Metaboxes
	 */
	public static function wpml_metaboxes() {
		remove_meta_box( 'icl_div_config', 'post', 'normal' );
		remove_meta_box( 'icl_div', 	   'post', 'side' );

		remove_meta_box( 'icl_div_config', 'page', 'normal' );
	}

	/**
	 * Post columns
	 */
	public static function post_columns( $columns ) {
		unset( $columns['author'] ); 	 // Author
		unset( $columns['categories'] ); // Categories
		unset( $columns['comments'] );   // Comments
		unset( $columns['date'] ); 	  	 // Date
		unset( $columns['tags'] ); 	  	 // Tags

		return $columns;
	}

	/**
	 * Page columns
	 */
	public static function page_columns( $columns ) {
		unset( $columns['comments'] ); // Comments
		unset( $columns['author'] );   // Author
		unset( $columns['date'] );	   // Date

		return $columns;
	}

	/**
	 * Footer version
	 */
	public static function footer_version() {
		if ( ! current_user_can( 'manage_options' ) ) {
			remove_filter( 'update_footer', 'core_update_footer' );
		}
	}

}

Bravad_Remove::init();
