<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.multidots.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers
 * @subpackage Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers/includes
 */
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers
 * @subpackage Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers/includes
 * @author     multidots <info@multidots.in>
 */
use  Automattic\WooCommerce\Utilities\OrderUtil ;
class Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected  $loader ;
    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected  $plugin_name ;
    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected  $version ;
    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        $this->plugin_name = 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers';
        $this->version = '1.0.0';
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $prefix = ( is_network_admin() ? 'network_admin_' : '' );
        $plugin_dir_url = ( wbpfoabfc_fs()->is__premium_only() || wbpfoabfc_fs()->can_use_premium_code() ? 'woocommerce-blocker-premium' : 'woocommerce-blocker' );
        $file_path = $plugin_dir_url . '/woocommerce-blocker.php';
        add_filter(
            "{$prefix}plugin_action_links_" . $file_path,
            array( $this, 'plugin_action_links' ),
            10,
            4
        );
    }
    
    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Loader. Orchestrates the hooks of the plugin.
     * - Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_i18n. Defines internationalization functionality.
     * - Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Admin. Defines all hooks for the admin area.
     * - Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-blocker-prevent-fake-orders-and-blacklist-fraud-customers-loader.php';
        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-blocker-prevent-fake-orders-and-blacklist-fraud-customers-i18n.php';
        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-blocker-prevent-fake-orders-and-blacklist-fraud-customers-admin.php';
        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woocommerce-blocker-prevent-fake-orders-and-blacklist-fraud-customers-public.php';
        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/woocommerce-blocker-admin-functions.php';
        $this->loader = new Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Loader();
    }
    
    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_i18n();
        $this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );
    }
    
    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Admin( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_post_submit_form_wcblu', $plugin_admin, 'wcblu_custom_add_update_options' );
        $this->loader->add_action( 'admin_post_nopriv_submit_form_wcblu', $plugin_admin, 'wcblu_custom_add_update_options' );
        $this->loader->add_action( 'wp_ajax_custom_send_user_email', $plugin_admin, 'custom_send_user_email' );
        $this->loader->add_action( 'wp_ajax_nopriv_custom_send_user_email', $plugin_admin, 'custom_send_user_email' );
        $this->loader->add_action( 'wp_ajax_add_plugin_user_wbl', $plugin_admin, 'wp_add_plugin_wbl_pro' );
        $this->loader->add_action( 'wp_ajax_nopriv_add_plugin_user_wbl', $plugin_admin, 'wp_add_plugin_wbl_pro' );
        $this->loader->add_action( 'wp_ajax_wcblu_reset_settings', $plugin_admin, 'wcblu_reset_settings' );
        $this->loader->add_action( 'wp_ajax_export_settings', $plugin_admin, 'wcblu_export_settings' );
        $this->loader->add_action( 'wp_ajax_import_settings', $plugin_admin, 'wcblu_import_settings' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'welcome_screen_pages_blacklist_users' );
        $this->loader->add_action( 'woocommerce_blocker_prevent_fake_orders_and_blacklist_fraud_customers_about', $plugin_admin, 'woocommerce_blocker_prevent_fake_orders_and_blacklist_fraud_customers_about' );
        $this->loader->add_action(
            'init',
            $plugin_admin,
            'register_custom_post_type_banned_user',
            0
        );
        $this->loader->add_action( 'wp_dashboard_setup', $plugin_admin, 'my_custom_dashboard_widgets' );
        $this->loader->add_filter(
            'manage_banned_user_posts_columns',
            $plugin_admin,
            'set_custom_edit_banned_user_columns',
            10,
            1
        );
        $this->loader->add_action( 'bulk_actions-edit-blocked_user', $plugin_admin, 'custom_bulk_edit_action_for_banned_user' );
        $this->loader->add_action( "admin_notices", $plugin_admin, "woocommerce_blocker_pro_plugin_custom_plugin_header" );
        $this->loader->add_action( 'admin_head', $plugin_admin, 'remove_premium_blocker_custom_menu' );
        $custom_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if ( !empty($custom_page) && isset( $custom_page ) && ('woocommerce_blacklist_users' === $custom_page || 'wcblu-import-export-setting' === $custom_page || 'wblp-get-started' === $custom_page || 'wblp-information' === $custom_page || 'wcblu-auto-rules' === $custom_page || 'wcblu-general-settings' === $custom_page) ) {
            $this->loader->add_filter( 'admin_footer_text', $plugin_admin, 'wcblu_admin_footer_review' );
        }
        $this->loader->add_action( 'admin_footer', $plugin_admin, 'wcblu_admin__notify_modal' );
        $this->loader->add_filter(
            'post_row_actions',
            $plugin_admin,
            'wcblu_permanent_delete_action',
            10,
            2
        );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'wcblu_permanent_delete_process' );
        $this->loader->add_action( 'wp_ajax_wcblu_fetch_setting', $plugin_admin, 'wcblu_fetch_setting' );
        $this->loader->add_action( 'admin_head', $plugin_admin, 'mmqw_dot_store_icon_css' );
        $this->loader->add_action( 'wp_ajax_wcblu_plugin_setup_wizard_submit', $plugin_admin, 'wcblu_plugin_setup_wizard_submit' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'wcblu_send_wizard_data_after_plugin_activation' );
    }
    
    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
        $plugin_public = new Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'woocommerce_checkout_process', $plugin_public, 'woo_email_domain_validation' );
        $this->loader->add_filter(
            'woocommerce_process_registration_errors',
            $plugin_public,
            'wooc_validate_extra_register_fields',
            10,
            4
        );
    }
    
    /**
     * Return the plugin action links.  This will only be called if the plugin
     * is active.
     *
     * @param array $actions associative array of action names to anchor tags
     *
     * @return array associative array of plugin action links
     * @since 1.0.0
     */
    public function plugin_action_links( $actions )
    {
        $configure_anchor_tag_start = '<a href="' . esc_url( admin_url( 'admin.php?page=woocommerce_blacklist_users' ) ) . '">';
        $docs_anchor_tag_start = '<a href="' . esc_url( 'www.thedotstore.com/woocommerce-blocker-lite-prevent-fake-orders-blacklist-fraud-customers' ) . '">';
        $support_anchor_tag_start = '<a href="' . esc_url( 'www.thedotstore.com/support' ) . '">';
        $anhor_tag_end = '</a>';
        $custom_actions = array(
            'configure' => sprintf(
            wp_kses_post( '%1$s%2$s%3$s' ),
            $configure_anchor_tag_start,
            esc_html__( 'Settings', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            $anhor_tag_end
        ),
            'docs'      => sprintf(
            wp_kses_post( '%1$s%2$s%3$s' ),
            $docs_anchor_tag_start,
            esc_html__( 'Docs', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            $anhor_tag_end
        ),
            'support'   => sprintf(
            wp_kses_post( '%1$s%2$s%3$s' ),
            $support_anchor_tag_start,
            esc_html__( 'Support', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            $anhor_tag_end
        ),
        );
        // add the links to the front of the actions list
        return array_merge( $custom_actions, $actions );
    }
    
    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }
    
    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }
    
    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Loader    Orchestrates the hooks of the plugin.
     * @since     1.0.0
     */
    public function get_loader()
    {
        return $this->loader;
    }
    
    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */
    public function get_version()
    {
        return $this->version;
    }

}