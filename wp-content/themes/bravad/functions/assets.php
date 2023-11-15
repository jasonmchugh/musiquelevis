<?php
//===========================================
//	POST THUMBNAILS
//===========================================
function bravad_thumbnails() {
	// Post thumbnails
	// add_theme_support('post-thumbnails', array('post'));
	// set_post_thumbnail_size(540, 395, true);

	// Teaser
	// add_image_size('teaser-small', 269, 269, false);	// ex : Home page news / events
	// Inline images
	// add_image_size('inline-img-default', 540, 395, true);
	// add_image_size('inline-img-small', 480, 352, true);
	// Products
	// add_image_size('product-teaser', 320, 185, false);
	// add_image_size('product-full', 892, 602, false);
	// add_image_size('product-slider', 720, 450, false);
	// Responsive
	// add_image_size('max', 2000, 9999, false);
	// add_image_size('tablet', 980, 9999, false);
	// add_image_size('tablet-cropped', 980, 9999, true);
}
// add_action('after_setup_theme', 'bravad_thumbnails');

add_filter( 'wp_prepare_attachment_for_js', function( $response, $attachment, $meta ) {
	if (!empty($response)) {
		$sizes_list = array(
			'shop_catalog',
		);
		while ( ! isset( $response['sizes']['medium'] ) && count( $sizes_list ) > 0 ) {
			$cur_size = array_pop( $sizes_list );
			if ( isset( $response['sizes'][$cur_size] ) ) {
				$response['sizes']['medium'] = $response['sizes'][$cur_size];
			}
		}
	}

	return $response;
}, 999, 3 );

add_filter( 'image_size_names_choose', function( $sizes ) {
	return array_merge( $sizes, array(
		'shop_catalog'   => __('One'),
	) );
}, 10, 1 );


//===========================================
//	EXCERPTS
//===========================================
function custom_excerpt_length( $length ) {
	return 15;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );


function my_acf_google_map_api( $api ){
    $api['key'] = 'AIzaSyDCINVRmiU2AlezhqfymFgNAkd14yXTRkk';
    return $api;
}
add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');


/*// Load custom images sizes in backend
function bravad_custom_sizes( $sizes ) {
	return array_merge( $sizes, array(
		'size-tag' => 'Nom du format',
	) );
}
add_filter( 'image_size_names_choose', 'bravad_custom_sizes' );*/

//===========================================
//	JQUERY MIGRATE
//	Don't load "jQuery migrate" when including
//	jQuery in frontend
//===========================================
function bravad_remove_jquery_migrate(&$scripts) {
	if (!is_admin()) {
		$scripts->remove('jquery');
	}
}
add_filter('wp_default_scripts', 'bravad_remove_jquery_migrate');


add_filter('wpcf7_autop_or_not', '__return_false');

//=============================================
//	Async load
//=============================================
function async_scripts($url) {
    if ( strpos( $url, '#asyncload') === false )
        return $url;
    else if ( is_admin() )
        return str_replace( '#asyncload', '', $url );
    else
	return str_replace( '#asyncload', '', $url )."' async='async";
}
add_filter( 'clean_url', 'async_scripts', 11, 1 );

//===========================================
//	ENQUEUE CSS / JS
//===========================================
function bravad_assets() {
	$version = '1.0.0';

	// WPML unwanted CSS / JS
	wp_deregister_style('language-selector');
	wp_deregister_script('language-selector');

	// Woocommerce related CSS / JS
	wp_deregister_style('jquery-ui');
	wp_deregister_style('wc-moneris');
	wp_deregister_style('berocket_aapf_widget-style');
	wp_deregister_style('berocket_aapf_widget-scroll-style');
	wp_deregister_script('berocket_aapf_widget-scroll-script');

	// CSS
	wp_enqueue_style( 'bravad', get_template_directory_uri() . '/style.css', '', $version );
	wp_enqueue_style( 'bravad-style', get_template_directory_uri() . '/assets/dist/css/style.min.css', '', $version );

	// JS
	wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-latest.min.js', array(), $version, true);
	wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js', array('jquery'), $version, true);
	wp_enqueue_script('bv-vendor', get_template_directory_uri() . '/assets/dist/js/vendor.min.js', array(), $version, true);
	wp_enqueue_script('bv-main', get_template_directory_uri() . '/assets/dist/js/functions.min.js', array('jquery', 'jquery-ui', 'bv-vendor'), $version, true);
	wp_enqueue_script('modernizr', get_template_directory_uri() . '/assets/dist/js/modernizr.min.js#asyncload', array(), $version, true);

}
add_action('wp_enqueue_scripts', 'bravad_assets');

//=============================================
//  ADD CUSTOM CSS TO BACKEND
//=============================================
add_action( 'admin_enqueue_scripts', 'load_admin_style' );
function load_admin_style() {
    wp_register_style( 'admin_css', get_template_directory_uri() . '/assets/dist/css/admin.css', false, '1.0.0' );
	wp_enqueue_style( 'admin_css', get_template_directory_uri() . '/assets/dist/css/admin.css', false, '1.0.0' );

// 	wp_enqueue_script( 'admin_js', get_template_directory_uri() . '/assets/dist/js/admin.js', array( 'jquery' ), '1.0.0', true );
}

//=============================================
// REORDER TABS
//=============================================

function custom_menu_order($menu_ord) {
    if (!$menu_ord) return true;

    return array(
	    //General
        'index.php', // Dashboard

		//Pages
        'edit.php?post_type=page', // Pages
        'edit.php?post_type=retailers', // CPT
        'edit.php?post_type=product', // CPT

        //Contenu
        'separator1', // First separator
        'upload.php', // Media
        'nav-menus.php', // Appearance
//         'wpseo_dashboard', // Yoast
        'acf-options-options', // ACF Options

		// Administration
        'separator2', // Second separator
        'plugins.php', // Plugins
        'themes.php', // Apparence
        'users.php', // Users
        'tools.php', // Tools
        'options-general.php', // Settings
    );

}
add_filter( 'custom_menu_order', '__return_true' );
add_filter('menu_order', 'custom_menu_order');


//=============================================
//  SVG ICONS
//=============================================
function svgicon( $icon, $render = true ) {
	$html  = '<svg class="svg-icon" role="presentation">';
	$html .= '<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="/assets/dist/img/icons.svg#' . $icon . '"></use>';
	$html .= '</svg>';

	if( $render ) {
		echo $html;

	} else {
		return $html;
	}
}


//=============================================
//  CUSTOM WP LOGIN PAGE
//=============================================
function my_login_logo() { ?>
    <style type="text/css">
        .login h1 a {
            background-image: none !important;
            padding-bottom: 10px;
            height: 0;
            width: 0;
            margin: 0;
        }
        .login h1 {
	        background: url(/wp-content/themes/bravad/assets/dist/img/instant-comptant.svg) no-repeat scroll center center / auto 100%;
			width: 100%;
			height: 50px;
        }
        .login form {
	        border-radius: 10px;
	        -webkit-box-shadow: none !important;
	        box-shadow: none !important;
        }
        #wp-submit {
	        background: #2B2F39 !important;
	        border: none;
	        border-radius: 0;
	        text-shadow: none;
	        -webkit-box-shadow: none !important;
	        box-shadow: none !important;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );


//=============================================
//  REMOVE WOOCOMMERCE CSS
//=============================================
// Remove each style one by one
add_filter( 'woocommerce_enqueue_styles', 'jk_dequeue_styles' );
function jk_dequeue_styles( $enqueue_styles ) {
	unset( $enqueue_styles['woocommerce-general'] );	// Remove the gloss
	unset( $enqueue_styles['woocommerce-layout'] );		// Remove the layout
	unset( $enqueue_styles['woocommerce-smallscreen'] );	// Remove the smallscreen optimisation
	return $enqueue_styles;
}

// Or just remove them all in one line
add_filter( 'woocommerce_enqueue_styles', '__return_false' );

?>