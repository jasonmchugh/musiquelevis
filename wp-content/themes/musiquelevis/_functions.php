<?php

/*  Admin Login    */
function my_custom_login() {
echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo('stylesheet_directory') . '/login/custom-login.css" />';
}
add_action('login_head', 'my_custom_login');
add_filter( 'login_headerurl', 'custom_loginlogo_url' );
function custom_loginlogo_url($url) {
    return 'https://agenceoasis.com';
}

// AUTHORIZE SVG UPLOAD IN ADMIN
function cc_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');

// ----------------------------------------------------------- //

// ADD CSS SHEETS AND JS SCRIPTS TO HEAD
function addThemeStyles() {
    wp_enqueue_style ('fancyboxStyle', get_template_directory_uri().'/assets/css/jquery.fancybox.min.css');
    wp_enqueue_style ('css', get_template_directory_uri().'/assets/css/app.css', array(), filemtime(get_template_directory() . '/assets/css/app.css'), false);
    wp_register_script('slick', get_template_directory_uri() . '/assets/plugins/slick-carousel/slick.min.js', array('jquery'), '1.8.0');
    wp_enqueue_script('slick');
    wp_register_script('smooth-scroll', get_template_directory_uri() . '/assets/plugins/smooth-scroll/smooth-scroll.polyfills.min.js', array('jquery'), '1.0.0');
    wp_enqueue_script('smooth-scroll');
    wp_register_script('gsap', get_template_directory_uri() . '/assets/plugins/gsap/gsap.min.js', array('jquery'), '3.11.5');
    wp_enqueue_script('gsap');
    wp_register_script('locomotive', get_template_directory_uri() . '/assets/plugins/locomotive/locomotive-scroll.min.js', array('jquery'), '4.1.3');
    wp_enqueue_script('locomotive');
    wp_register_script('fancybox', get_stylesheet_directory_uri() . '/assets/plugins/fancybox/jquery.fancybox.min.js', array('jquery'), '3.5.2');
    wp_register_script('mixitup', get_stylesheet_directory_uri() . '/assets/plugins/mixitup/mixitup.min.js', array('jquery'), '3.5.2');
    wp_register_script('mixitup-multifilter', get_stylesheet_directory_uri() . '/assets/plugins/mixitup/mixitup-multifilter.min.js', array('jquery'), '3.5.2');
    wp_enqueue_script('fancybox');
    wp_enqueue_script('mixitup');
    wp_enqueue_script('mixitup-multifilter');
    wp_register_script('cursor', get_template_directory_uri() . '/assets/js/footer/cursor.js', array('jquery'), '1.0',true);
    wp_enqueue_script('cursor');
    wp_enqueue_script( 'js', get_template_directory_uri().'/assets/js/app.min.js', array(), filemtime(get_template_directory() . '/assets/js/app.min.js'), false );
}
add_action( 'wp_enqueue_scripts', 'addThemeStyles' );

function main_nav()
{    
    wp_nav_menu(
    array(
        'theme_location'  => 'menu_principal',
        'menu'            => 'Menu principal',
        'container'       => '',
        'items_wrap'      => '<ul class="header_main-menu_nav">%3$s</ul>',
        )
    );
}
function secondary_nav()
{    
    wp_nav_menu(
    array(
        'theme_location'  => 'menu_secondaire',
        'menu'            => 'Menu secondaire',
        'container'       => '',
        'items_wrap'      => '<ul class="navMenuSec">%3$s</ul>',
        )
    );
}
function mobile_nav()
{    
    wp_nav_menu(
    array(
        'theme_location'  => 'menu_mobile',
        'menu'            => 'Menu mobile',
        'container'       => '',
        'items_wrap'      => '<ul class="header_main-menu_mobile">%3$s</ul>',
        )
    );
}
function langue_nav()
{    
    wp_nav_menu(
    array(
        'theme_location'  => 'menu_langue',
        'menu'            => 'Menu langue',
        'container'       => '',
        'items_wrap'      => '<ul class="header_main-menu_mobile_langue">%3$s</ul>',
        )
    );
}
//ADD MENUS TO THE CODE
function register_my_menu() {
  register_nav_menu('menu_principal',__( 'menu_principal' ));
  register_nav_menu('menu_secondaire',__( 'menu_secondaire' ));
  register_nav_menu('menu_mobile',__( 'menu_mobile' ));
  register_nav_menu('menu_langue',__( 'menu_langue' ));
}
add_action( 'init', 'register_my_menu' );

//Add photo size when import
if (function_exists('add_theme_support'))
{
    // Add Menu Support
    add_theme_support('menus');

    //add_image_size('imageBlog', 1000, 615, true); // Image Preview blogue
    add_image_size('imageHeader', 1920, 1080, true); // Image Slider
    add_image_size('medBlogue', 666, 1000, true); // Image Preview blogue
    add_image_size('thumbProject', 1250, 1250, true); // Image Preview blogue

}

/**
 * Remove recent comments style
 */
function remove_recent_comments_style() {
    global $wp_widget_factory;
    remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
}
add_action('widgets_init', 'remove_recent_comments_style');

/*
  * Remove comments from Site
 */
// Removes from admin menu
add_action( 'admin_menu', 'my_remove_admin_menus' );
function my_remove_admin_menus() {
    remove_menu_page( 'edit-comments.php' );
    remove_menu_page( 'link-manager.php' ); 
}
// Removes from post and pages
add_action('init', 'remove_comment_support', 100);

function remove_comment_support() {
    remove_post_type_support( 'post', 'comments' );
    remove_post_type_support( 'page', 'comments' );
}
// Add Featured image in post
add_theme_support('post-thumbnails', array(
'post',
'page',
'projets',
'clients',
));
// Removes from admin bar
function mytheme_admin_bar_render() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
}
add_action( 'wp_before_admin_bar_render', 'mytheme_admin_bar_render' );

if( function_exists('acf_add_options_page') ) {
    acf_add_options_page(array(
        'page_title'    => 'Configuration Options général',
        'menu_title'    => 'Options général',
        'menu_slug'     => 'theme-general-settings',
        'capability'    => 'edit_posts',
        'redirect'      => false
    ));
    
    acf_add_options_sub_page(array(
        'page_title'    => 'Options - bas de page',
        'menu_title'    => 'Infos bas de page',
        'parent_slug'   => 'theme-general-settings',
    ));
}

// If Dynamic Sidebar Exists
if (function_exists('register_sidebar'))
{
    // Define Sidebar Widget Area 1
    register_sidebar(array(
        'name' => __('Blog sidebar', 'oasis'),
        'description' => __('Widget area blog', 'oasis'),
        'id' => 'widget-blog',
        'before_widget' => '<div id="%1$s" class="%2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>'
    ));
}

/**
 * Permanent changes
 * Remove all junk from <head>
 */
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
remove_action('wp_head', 'rel_canonical');


add_filter( 'show_admin_bar', '__return_false' );

function clients()
{
    register_post_type('clients', // Register Custom Post Type
        array(
        'labels' => array(
            'name' => __('Clients', 'oasis'), // Rename these to suit
            'singular_name' => __('Client', 'oasis'),
            'add_new' => __('Ajouter un client' , 'oasis'),
            'add_new_item' => __('Ajouter un nouveau client', 'oasis'),
            'edit' => __('Changer', 'oasis'),
            'edit_item' => __('Modifier le client', 'oasis'),
            'new_item' => __('Nouveau client', 'oasis'),
            'view' => __('Voir', 'oasis'),
            'view_item' => __('Voir le client', 'oasis'),
            'search_items' => __('Rechercher un client', 'oasis'),
            'not_found' => __('Aucun client', 'oasis'),
            'not_found_in_trash' => __('Aucun client trouvé dans la corbeille', 'oasis')
        ),
        'public' => true,
        'hierarchical' => false,
        'has_archive' => false,
        'menu_icon' => "dashicons-businessperson",
        //'rewrite' => array( 'slug' => 'templates' ),
        'rewrite' => array('slug' => 'clients', 'with_front' => false),
        'menu_position' => 21,
        //'taxonomies' => array('post_tag','category'),
        'taxonomies' => array('clients-category'),
        'supports' => array(
            'title',
            'editor',
            'thumbnail'
        ), // Go to Dashboard Custom HTML5 Blank post for supports
        'can_export' => true, // Allows export in Tools > Export
    ));
    global $translation_name;
    register_taxonomy( 'clients-category', array ( "clients" ), array (
        'public'            => true,
        'hierarchical'      => true,
        'publicly_queryable' => true,
        'labels' => array(
            'name'               => __( 'Catégorie clients', $translation_name ),
            'singular_name'      => __( 'Catégorie clients', $translation_name ),
            'add_new'            => __( 'Nouvelle', $translation_name ),
            'add_new_item'       => __( 'Nouvelle catégorie clients', $translation_name ),
            'edit_item'          => __( 'Changer catégorie clients', $translation_name ),
            'new_item_name'      => __( 'Ajouter une catégorie clients', $translation_name ),
            'all_items'          => __( 'Toutes les catégories clients', $translation_name ),
            'view_item'          => __( 'Voir la catégorie clients', $translation_name ),
            'search_items'       => __( 'Chercher une catégorie clients', $translation_name ),
            'not_found'          => __( 'Catégorie de clients introuvable', $translation_name ),
            'not_found_in_trash' => __( 'Aucune catégorie clients dans la poubelle', $translation_name ),
            'parent_item_colon'  => '',
            'menu_name'          => __( 'Catégorie des clients', $translation_name )
        ),
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => false,
    ) );
}
add_action('init', 'clients');

function projets()
{
    register_post_type('projets', // Register Custom Post Type
        array(
        'labels' => array(
            'name' => __('Projets', 'oasis'), // Rename these to suit
            'singular_name' => __('Projet', 'oasis'),
            'add_new' => __('Ajouter un projet' , 'oasis'),
            'add_new_item' => __('Ajouter un nouveau projet', 'oasis'),
            'edit' => __('Changer', 'oasis'),
            'edit_item' => __('Modifier le projet', 'oasis'),
            'new_item' => __('Nouveau projet', 'oasis'),
            'view' => __('Voir', 'oasis'),
            'view_item' => __('Voir le projet', 'oasis'),
            'search_items' => __('Rechercher un projet', 'oasis'),
            'not_found' => __('Aucun projet', 'oasis'),
            'not_found_in_trash' => __('Aucun projet trouvé dans la corbeille', 'oasis')
        ),
        'public' => true,
        'hierarchical' => false,
        'has_archive' => false,
        'menu_icon' => "dashicons-layout",
        //'rewrite' => array( 'slug' => 'projets' ),
        'rewrite' => array('slug' => 'projets', 'with_front' => false),
        'menu_position' => 22,
        //'taxonomies' => array('post_tag','category'),
        'taxonomies' => array('projets-category'),
        'supports' => array(
            'title',
            'editor',
            'thumbnail'
        ), // Go to Dashboard Custom HTML5 Blank post for supports
        'can_export' => true, // Allows export in Tools > Export
    ));
    global $translation_name;
    register_taxonomy( 'projets-category', array ( "projets" ), array (
        'public'            => true,
        'hierarchical'      => true,
        'publicly_queryable' => true,
        'labels' => array(
            'name'               => __( 'Catégorie projets', $translation_name ),
            'singular_name'      => __( 'Catégorie projets', $translation_name ),
            'add_new'            => __( 'Nouvelle', $translation_name ),
            'add_new_item'       => __( 'Nouvelle catégorie projets', $translation_name ),
            'edit_item'          => __( 'Changer catégorie projets', $translation_name ),
            'new_item_name'      => __( 'Ajouter une catégorie projets', $translation_name ),
            'all_items'          => __( 'Toutes les catégories projets', $translation_name ),
            'view_item'          => __( 'Voir la catégorie projets', $translation_name ),
            'search_items'       => __( 'Chercher une catégorie projets', $translation_name ),
            'not_found'          => __( 'Catégorie de projets introuvable', $translation_name ),
            'not_found_in_trash' => __( 'Aucune catégorie projets dans la poubelle', $translation_name ),
            'parent_item_colon'  => '',
            'menu_name'          => __( 'Catégorie des projets', $translation_name )
        ),
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => false,
    ) );
}
add_action('init', 'projets');

function templates()
{
    register_post_type('templates', // Register Custom Post Type
        array(
        'labels' => array(
            'name' => __('Templates', 'oasis'), // Rename these to suit
            'singular_name' => __('Template', 'oasis'),
            'add_new' => __('Ajouter un template' , 'oasis'),
            'add_new_item' => __('Ajouter un nouveau template', 'oasis'),
            'edit' => __('Changer', 'oasis'),
            'edit_item' => __('Modifier le template', 'oasis'),
            'new_item' => __('Nouveau template', 'oasis'),
            'view' => __('Voir', 'oasis'),
            'view_item' => __('Voir le template', 'oasis'),
            'search_items' => __('Rechercher un template', 'oasis'),
            'not_found' => __('Aucun template', 'oasis'),
            'not_found_in_trash' => __('Aucun template trouvé dans la corbeille', 'oasis')
        ),
        'public' => true,
        'hierarchical' => false,
        'has_archive' => false,
        'menu_icon' => "dashicons-layout",
        'rewrite' => array('slug' => 'templates', 'with_front' => false),
        'menu_position' => 23,
        'taxonomies' => array('templates-category'),
        'supports' => array(
            'title',
            'editor',
            'thumbnail'
        ),
        'can_export' => true, // Allows export in Tools > Export
    ));
    global $translation_name;
    register_taxonomy( 'templates-category', array ( "templates" ), array (
        'public'            => true,
        'hierarchical'      => true,
        'publicly_queryable' => true,
        'labels' => array(
            'name'               => __( 'Catégorie templates', $translation_name ),
            'singular_name'      => __( 'Catégorie templates', $translation_name ),
            'add_new'            => __( 'Nouvelle', $translation_name ),
            'add_new_item'       => __( 'Nouvelle catégorie templates', $translation_name ),
            'edit_item'          => __( 'Changer catégorie templates', $translation_name ),
            'new_item_name'      => __( 'Ajouter une catégorie templates', $translation_name ),
            'all_items'          => __( 'Toutes les catégories templates', $translation_name ),
            'view_item'          => __( 'Voir la catégorie templates', $translation_name ),
            'search_items'       => __( 'Chercher une catégorie templates', $translation_name ),
            'not_found'          => __( 'Catégorie de templates introuvable', $translation_name ),
            'not_found_in_trash' => __( 'Aucune catégorietemplates dans la poubelle', $translation_name ),
            'parent_item_colon'  => '',
            'menu_name'          => __( 'Catégorie des templates', $translation_name )
        ),
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => false,
    ) );
}
add_action('init', 'templates');

?>
