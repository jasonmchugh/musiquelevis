<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.multidots.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers
 * @subpackage Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers/admin
 */
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers
 * @subpackage Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers/admin
 * @author     multidots <info@multidots.in>
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
use  Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController ;
use  Automattic\WooCommerce\Utilities\OrderUtil ;
class Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Admin
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private  $plugin_name ;
    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private  $version ;
    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version     The version of this plugin.
     *
     * @since    1.0.0
     *
     */
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }
    
    /**
     * @param $string
     *
     * @return bool
     * funtion to return string is in json format or not
     */
    public static function wcblu_isdata_Json( $string )
    {
        json_decode( $string );
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts( $hook )
    {
        global  $typenow ;
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'css/woocommerce-blocker-prevent-fake-orders-and-blacklist-fraud-customers-admin.css',
            array(),
            $this->version,
            'all'
        );
        wp_enqueue_style(
            $this->plugin_name . 'plugin-setup-wizard',
            plugin_dir_url( __FILE__ ) . 'css/plugin-setup-wizard.css',
            array(),
            'all'
        );
        
        if ( 'admin_page_wblp-get-started' === $hook || 'admin_page_woocommerce_blacklist_users' === $hook || 'dotstore-plugins_page_woocommerce_blacklist_users' === $hook || 'blocked_user' === $typenow || 'dotstore-plugins_page_wcblu-import-export-setting' === $hook || 'dotstore-plugins_page_wblp-get-started' === $hook || 'dotstore-plugins_page_wblp-information' === $hook || 'dotstore-plugins_page_wcblu-general-settings' === $hook || 'dotstore-plugins_page_wcblu-auto-rules' === $hook || 'admin_page_woocommerce_blacklist_users-account' === $hook || 'dotstore-plugins_page_wcblu-upgrade-dashboard' === $hook || 'toplevel_page_woocommerce_blacklist_users' === $hook ) {
            wp_enqueue_style( 'main-style', plugin_dir_url( __FILE__ ) . 'css/style.css' );
            wp_enqueue_style( 'plugin-new-style', plugin_dir_url( __FILE__ ) . 'css/plugin-new-style.css' );
            wp_enqueue_style( 'wp-jquery-ui-dialog' );
            wp_enqueue_script(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'js/woocommerce-blocker-prevent-fake-orders-and-blacklist-fraud-customers-admin.js',
                array( 'jquery' ),
                $this->version,
                false
            );
            wp_enqueue_style(
                $this->plugin_name . 'select2-style',
                plugin_dir_url( __FILE__ ) . 'css/select2.min.css',
                array(),
                'all'
            );
            wp_enqueue_script(
                $this->plugin_name . 'select2-js',
                plugin_dir_url( __FILE__ ) . 'js/select2.full.min.js',
                array( 'jquery' ),
                $this->version,
                true
            );
            
            if ( !(wbpfoabfc_fs()->is__premium_only() && wbpfoabfc_fs()->can_use_premium_code()) ) {
                wp_enqueue_style(
                    $this->plugin_name . 'upgrade-dashboard-style',
                    plugin_dir_url( __FILE__ ) . 'css/upgrade-dashboard.css',
                    array(),
                    'all'
                );
                wp_enqueue_style(
                    $this->plugin_name . 'font-awesome',
                    plugin_dir_url( __FILE__ ) . 'css/font-awesome.min.css',
                    array(),
                    $this->version,
                    'all'
                );
            }
        
        }
        
        //wp_enqueue_script( 'chosen-proto', plugin_dir_url( __FILE__ ) . 'js/chosen.proto.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script(
            'jquery-validate',
            plugin_dir_url( __FILE__ ) . 'js/jquery.validate.min.js',
            array(),
            false
        );
        wp_enqueue_script(
            'jquery-knob-min-js',
            plugin_dir_url( __FILE__ ) . 'js/jquery.knob.min.js',
            array(),
            false
        );
        wp_localize_script( $this->plugin_name, 'adminajax', array(
            'ajaxurl'     => admin_url( 'admin-ajax.php' ),
            'ajax_icon'   => plugin_dir_url( __FILE__ ) . '/images/ajax-loader.gif',
            'nonce'       => wp_create_nonce( 'wcblu-ajax-nonce' ),
            'dpb_api_url' => WB_PROMOTIONAL_BANNER_API_URL,
            'importError' => array(
            'invalidFile' => esc_html__( 'Please add JSON file', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            'exmptyFile'  => esc_html__( 'Please choose JSON file', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
        ),
        ) );
        wp_enqueue_script( 'jquery-ui-dialog' );
        $getpluginoption = get_option( 'wcblu_option' );
        $getpluginoptionarray = json_decode( $getpluginoption, true );
        $fetchSelectedIpAddress = ( !empty($getpluginoptionarray['wcblu_block_ip']) ? $getpluginoptionarray['wcblu_block_ip'] : '' );
        $optionsIpAddress = ( !empty($fetchSelectedIpAddress) ? wp_json_encode( $fetchSelectedIpAddress ) : wp_json_encode( array() ) );
        $fetchSelecetedState = ( !empty($getpluginoptionarray['wcblu_block_state']) ? $getpluginoptionarray['wcblu_block_state'] : '' );
        $optionsState = ( !empty($fetchSelecetedState) ? wp_json_encode( $fetchSelecetedState ) : wp_json_encode( array() ) );
        $fetchSelecetedCountry = ( !empty($getpluginoptionarray['wcblu_block_country']) ? $getpluginoptionarray['wcblu_block_country'] : '' );
        $optionsCountry = ( !empty($fetchSelecetedCountry) ? wp_json_encode( $fetchSelecetedCountry ) : wp_json_encode( array() ) );
        $fetchSelecetedZip = ( !empty($getpluginoptionarray['wcblu_block_zip']) ? $getpluginoptionarray['wcblu_block_zip'] : '' );
        $optionsZip = ( !empty($fetchSelecetedZip) ? wp_json_encode( $fetchSelecetedZip ) : wp_json_encode( array() ) );
        $fetchSelecetedZone = ( !empty($getpluginoptionarray['wcblu_block_zone']) ? $getpluginoptionarray['wcblu_block_zone'] : '' );
        $optionsZone = ( !empty($fetchSelecetedZone) ? wp_json_encode( $fetchSelecetedZone ) : wp_json_encode( array() ) );
        $fetchSelecetedUserRole = ( !empty($getpluginoptionarray['wcblu_block_userrole']) ? $getpluginoptionarray['wcblu_block_userrole'] : '' );
        $optionsUserRole = ( !empty($fetchSelecetedUserRole) ? wp_json_encode( $fetchSelecetedUserRole ) : wp_json_encode( array() ) );
        $fetchSelecetedDomain = ( !empty($getpluginoptionarray['wcblu_block_domain']) ? $getpluginoptionarray['wcblu_block_domain'] : '' );
        $optionsDomain = ( !empty($fetchSelecetedDomain) ? wp_json_encode( $fetchSelecetedDomain ) : wp_json_encode( array() ) );
        $fetchWildcardDomainExt = ( !empty($getpluginoptionarray['wcblu_block_domain_ext']) ? $getpluginoptionarray['wcblu_block_domain_ext'] : '' );
        $optionsDomainExt = ( !empty($fetchWildcardDomainExt) ? wp_json_encode( $fetchWildcardDomainExt ) : wp_json_encode( array() ) );
        $fetchSelectedPhone = ( !empty($getpluginoptionarray['wcblu_block_phone']) ? $getpluginoptionarray['wcblu_block_phone'] : '' );
        $optionsPhone = ( !empty($fetchSelectedPhone) ? wp_json_encode( $fetchSelectedPhone ) : wp_json_encode( array() ) );
        $fetchSelecetedFirstName = ( !empty($getpluginoptionarray['wcblu_block_first_name']) ? $getpluginoptionarray['wcblu_block_first_name'] : '' );
        $optionsFirstName = ( !empty($fetchSelecetedFirstName) ? wp_json_encode( $fetchSelecetedFirstName ) : wp_json_encode( array() ) );
        $fetchSelecetedLastName = ( !empty($getpluginoptionarray['wcblu_block_last_name']) ? $getpluginoptionarray['wcblu_block_last_name'] : '' );
        $optionsLastName = ( !empty($fetchSelecetedLastName) ? wp_json_encode( $fetchSelecetedLastName ) : wp_json_encode( array() ) );
        $fetchCurrentBrowser = ( !empty($getpluginoptionarray['wcblu_block_user_agent']) ? $getpluginoptionarray['wcblu_block_user_agent'] : '' );
        $optionsUserAgent = ( !empty($fetchCurrentBrowser) ? wp_json_encode( $fetchCurrentBrowser ) : wp_json_encode( array() ) );
        $fetchSelectedEmail = ( !empty($getpluginoptionarray['wcblu_block_email']) ? $getpluginoptionarray['wcblu_block_email'] : '' );
        $optionsEmail = ( !empty($fetchSelectedEmail) ? wp_json_encode( $fetchSelectedEmail ) : wp_json_encode( array() ) );
        $localize_php_variable_array = array(
            'getEmailOption'     => array(
            'emailarray' => $optionsEmail,
        ),
            'getUserAgentOption' => array(
            'useragentarray' => $optionsUserAgent,
        ),
            'getLastNameOption'  => array(
            'lastnamearray' => $optionsLastName,
        ),
            'getFirstNameOption' => array(
            'firstnamearray' => $optionsFirstName,
        ),
            'getPhoneOption'     => array(
            'phonearray' => $optionsPhone,
        ),
            'getDomainextOption' => array(
            'domainextarray' => $optionsDomainExt,
        ),
            'getDomainOption'    => array(
            'domainarray' => $optionsDomain,
        ),
            'getUserroleOption'  => array(
            'userrolearray' => $optionsUserRole,
        ),
            'getZoneOption'      => array(
            'zonearray' => $optionsZone,
        ),
            'getZipOption'       => array(
            'ziparray' => $optionsZip,
        ),
            'getStateOption'     => array(
            'statearray' => $optionsState,
        ),
            'getCountryOption'   => array(
            'countryarray' => $optionsCountry,
        ),
            'getIpOption'        => array(
            'ipaddressarray' => $optionsIpAddress,
        ),
        );
        ?>

		<input type="hidden" value='<?php 
        echo  esc_attr( wp_json_encode( $localize_php_variable_array ) ) ;
        ?>' name="localize_json_output">
	
	<?php 
    }
    
    /**
     * function to return custom menu for dot store.
     */
    public function remove_premium_blocker_custom_menu()
    {
        remove_submenu_page( 'dots_store', 'wblp-information' );
        remove_submenu_page( 'dots_store', 'wblp-get-started' );
        remove_submenu_page( 'dots_store', 'blocked_user' );
        remove_submenu_page( 'dots_store', 'wcblu-import-export-setting' );
        remove_submenu_page( 'dots_store', 'wcblu-general-settings' );
        remove_submenu_page( 'dots_store', 'wcblu-auto-rules' );
        remove_submenu_page( 'dots_store', 'wcblu-upgrade-dashboard' );
    }
    
    /**
     * function to return information page of plugins
     */
    public function wblp_information_page()
    {
        // Exit if accessed directly
        if ( !defined( 'ABSPATH' ) ) {
            exit;
        }
        $file_dir = '/partials/wblp-information-page.php';
        if ( file_exists( dirname( __FILE__ ) . $file_dir ) ) {
            require_once dirname( __FILE__ ) . $file_dir;
        }
    }
    
    /**
     * function to return started page of plugin
     */
    public function wblp_get_started_page()
    {
        // Exit if accessed directly
        if ( !defined( 'ABSPATH' ) ) {
            exit;
        }
        $file_dir = '/partials/wblp-get-started-page.php';
        if ( file_exists( dirname( __FILE__ ) . $file_dir ) ) {
            include_once dirname( __FILE__ ) . $file_dir;
        }
    }
    
    /**
     * Function to return inclued import export setting page.
     */
    public function wbclu_import_export_page()
    {
        if ( !defined( 'ABSPATH' ) ) {
            exit;
        }
        $file_dir = '/partials/wcblu-import-export-setting.php';
        if ( file_exists( dirname( __FILE__ ) . $file_dir ) ) {
            include_once dirname( __FILE__ ) . $file_dir;
        }
    }
    
    /**
     * Premium version info page
     *
     */
    public function wbclu_free_user_upgrade_page()
    {
        require_once plugin_dir_path( __FILE__ ) . '/partials/dots-upgrade-dashboard.php';
    }
    
    /**
     * Function to return inclued general settings setting page.
     */
    public function wbclu_general_settings_page()
    {
        if ( !defined( 'ABSPATH' ) ) {
            exit;
        }
        $file_dir = '/partials/wcblu-general-settings.php';
        if ( file_exists( dirname( __FILE__ ) . $file_dir ) ) {
            include_once dirname( __FILE__ ) . $file_dir;
        }
    }
    
    /**
     * Function to return inclued rules setting page.
     */
    public function wbclu_rules_page()
    {
        if ( !defined( 'ABSPATH' ) ) {
            exit;
        }
        $file_dir = '/partials/wcblu-rule-settings.php';
        if ( file_exists( dirname( __FILE__ ) . $file_dir ) ) {
            include_once dirname( __FILE__ ) . $file_dir;
        }
    }
    
    /**
     * function to returnplugin header page of plugin
     */
    public function woocommerce_blocker_pro_plugin_custom_plugin_header()
    {
        $post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        
        if ( isset( $post_type ) && 'blocked_user' === $post_type ) {
            $file_dir = '/partials/header/plugin-header.php';
            if ( file_exists( dirname( __FILE__ ) . $file_dir ) ) {
                include_once dirname( __FILE__ ) . $file_dir;
            }
        }
    
    }
    
    /**
     * @param $columns
     *
     * @return array
     * function to return add column in blocker wp list
     */
    function set_custom_edit_banned_user_columns( $columns )
    {
        $columns = array(
            'cb'      => '<input type="checkbox" />',
            'title'   => __( 'Email', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            'attempt' => __( 'Attempt', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
        );
        return $columns;
    }
    
    /**
     * @param $actions
     *
     * @return mixed
     * function to return edit column
     */
    function custom_bulk_edit_action_for_banned_user( $actions )
    {
        unset( $actions['edit'] );
        return $actions;
    }
    
    /**
     * function return to add content in footer
     */
    function custom_banned_user_admin_footer()
    {
        global  $post_type ;
        
        if ( 'blocked_user' === $post_type ) {
            ?>
			<script type="text/javascript">
							jQuery(document).ready(function () {
								jQuery('<option>').val('delete_permanently').text('<?php 
            esc_attr_e( 'Delete Permanently', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
            ?>').appendTo('select[name=\'action\']');
							});
			</script>
			<?php 
        }
    
    }
    
    /**
     * function to return update option in plugin
     */
    public function wcblu_custom_add_update_options()
    {
        /**
         * get form action
         */
        $getformsumbitaction = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $getformsumbitaction = ( !empty($getformsumbitaction) ? $getformsumbitaction : '' );
        $getformactiontype = filter_input( INPUT_POST, 'action-which', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $getformactiontype = ( !empty($getformactiontype) ? $getformactiontype : '' );
        $wcpoa_blklist_nonce = filter_input( INPUT_POST, 'wcblu_blacklist_settings_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        /**
         * check form action
         *
         */
        
        if ( !empty($getformsumbitaction) && 'submit_form_wcblu' === $getformsumbitaction && !empty($getformactiontype) && 'add' === $getformactiontype && wp_verify_nonce( sanitize_text_field( $wcpoa_blklist_nonce ), 'wcblu_blacklist_settings' ) ) {
            /**
             * get plugin option settings
             */
            //get user block track type
            $wc_user_register_type = filter_input( INPUT_POST, 'wc_user_register_type', FILTER_SANITIZE_NUMBER_INT );
            $wc_user_place_order_type = filter_input( INPUT_POST, 'wc_user_place_order_type', FILTER_SANITIZE_NUMBER_INT );
            $wc_user_address_type = filter_input( INPUT_POST, 'wc_user_address_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wc_user_register_type = ( empty($wc_user_register_type) ? '0' : $wc_user_register_type );
            $wc_user_place_order_type = ( empty($wc_user_place_order_type) ? '0' : $wc_user_place_order_type );
            $wc_user_address_type = ( empty($wc_user_address_type) ? '' : $wc_user_address_type );
            $wc_register_type = $wc_user_register_type;
            $wc_place_order_type = $wc_user_place_order_type;
            $wc_address_type = $wc_user_address_type;
            //get plugin block option
            $post_array_sanitise_ = filter_input_array( INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $getemail = ( !empty($post_array_sanitise_['email']) ? array_filter( $post_array_sanitise_['email'] ) : '' );
            $wc_all_chk_selection = ( !empty($post_array_sanitise_['all_chk_selection']) ? array_filter( $post_array_sanitise_['all_chk_selection'] ) : '' );
            $getipbasic = ( !empty($post_array_sanitise_['ip-basic']) ? array_filter( $post_array_sanitise_['ip-basic'] ) : '' );
            $getstate = ( !empty($post_array_sanitise_['state']) ? array_filter( $post_array_sanitise_['state'] ) : '' );
            $getcountry = ( !empty($post_array_sanitise_['country']) ? array_filter( $post_array_sanitise_['country'] ) : '' );
            $getzip = ( !empty($post_array_sanitise_['zip']) ? array_filter( $post_array_sanitise_['zip'] ) : '' );
            $getdomain = ( !empty($post_array_sanitise_['domain']) ? array_filter( $post_array_sanitise_['domain'] ) : '' );
            $getdomainext = ( !empty($post_array_sanitise_['domain_ext']) ? array_filter( $post_array_sanitise_['domain_ext'] ) : '' );
            $getphone = ( !empty($post_array_sanitise_['phone']) ? array_filter( $post_array_sanitise_['phone'] ) : '' );
            $getfirstname = ( !empty($post_array_sanitise_['first_name']) ? array_filter( $post_array_sanitise_['first_name'] ) : '' );
            $getlastname = ( !empty($post_array_sanitise_['last_name']) ? array_filter( $post_array_sanitise_['last_name'] ) : '' );
            $getuseragent = ( !empty($post_array_sanitise_['user_agent']) ? array_filter( $post_array_sanitise_['user_agent'] ) : '' );
            $getzone = ( !empty($post_array_sanitise_['zone']) ? array_filter( $post_array_sanitise_['zone'] ) : '' );
            $getuserrole = ( !empty($post_array_sanitise_['userrole']) ? array_filter( $post_array_sanitise_['userrole'] ) : '' );
            //get plugin error messages
            $wc_email_msg_sett = filter_input( INPUT_POST, 'wc_email_msg_sett', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wc_ip_msg_sett = filter_input( INPUT_POST, 'wc_ip_msg_sett', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wc_state_msg_sett = filter_input( INPUT_POST, 'wc_state_msg_sett', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wc_country_msg_sett = filter_input( INPUT_POST, 'wc_country_msg_sett', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wc_zpcode_msg_sett = filter_input( INPUT_POST, 'wc_zpcode_msg_sett', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wc_domain_msg_sett = filter_input( INPUT_POST, 'wc_domain_msg_sett', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wc_domain_ext_msg_sett = filter_input( INPUT_POST, 'wc_domain_ext_msg_sett', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wc_phone_msg_sett = filter_input( INPUT_POST, 'wc_phone_msg_sett', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wc_first_name_msg_sett = filter_input( INPUT_POST, 'wc_first_name_msg_sett', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wc_first_name_relation = filter_input( INPUT_POST, 'wc_first_name_relation', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wc_user_agent_msg_sett = filter_input( INPUT_POST, 'wc_user_agent_msg_sett', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wc_last_name_msg_sett = filter_input( INPUT_POST, 'wc_last_name_msg_sett', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wc_last_name_relation = filter_input( INPUT_POST, 'wc_last_name_relation', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wc_zone_msg_sett = filter_input( INPUT_POST, 'wc_zone_msg_sett', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wc_userrole_msg_sett = filter_input( INPUT_POST, 'wc_userrole_msg_sett', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $automatic_blacklist = filter_input( INPUT_POST, 'wcblu_automatic_blacklist', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wc_email_msg_sett = ( !empty($wc_email_msg_sett) ? $wc_email_msg_sett : esc_html__( 'This email address has been blocked, please try other email address or Kindly contact admin.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) );
            $wc_ip_msg_sett = ( !empty($wc_ip_msg_sett) ? $wc_ip_msg_sett : esc_html__( 'This IP address has been blocked due to some reason, Kindly contact admin.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) );
            $wc_state_msg_sett = ( !empty($wc_state_msg_sett) ? $wc_state_msg_sett : esc_html__( 'Sorry :( We are not shipping this products in this state.  Kindly contact admin. ', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) );
            $wc_country_msg_sett = ( !empty($wc_country_msg_sett) ? $wc_country_msg_sett : esc_html__( 'Sorry :( We are not shipping this products in this country.  Kindly contact admin. ', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) );
            $wc_zpcode_msg_sett = ( !empty($wc_zpcode_msg_sett) ? $wc_zpcode_msg_sett : esc_html__( 'Sorry :( We are not shipping this products in this location. Kindly contact admin.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) );
            $wc_domain_msg_sett = ( !empty($wc_domain_msg_sett) ? $wc_domain_msg_sett : esc_html__( 'Sorry :( This domain has been blocked due to some reason. Kindly contact admin.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) );
            $wc_domain_ext_msg_sett = ( !empty($wc_domain_ext_msg_sett) ? $wc_domain_ext_msg_sett : esc_html__( 'Sorry :( This domain extension has been blocked, Kindly contact admin.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) );
            $wc_phone_msg_sett = ( !empty($wc_phone_msg_sett) ? $wc_phone_msg_sett : esc_html__( 'Sorry :( This phone number has been blocked due to some reason. Kindly contact admin.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) );
            $wc_first_name_msg_sett = ( !empty($wc_first_name_msg_sett) ? $wc_first_name_msg_sett : esc_html__( 'Sorry :( This first name has been blocked due to some reason. Kindly contact admin.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) );
            $wc_user_agent_msg_sett = ( !empty($wc_user_agent_msg_sett) ? $wc_user_agent_msg_sett : esc_html__( 'This browser has been blocked due to some reason, Kindly contact admin.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) );
            $wc_last_name_msg_sett = ( !empty($wc_last_name_msg_sett) ? $wc_last_name_msg_sett : esc_html__( 'Sorry :( This last name has been blocked due to some reason. Kindly contact admin.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) );
            $wc_zone_msg_sett = ( !empty($wc_zone_msg_sett) ? $wc_zone_msg_sett : esc_html__( 'Sorry :( This zone has been blocked due to some reason. Kindly contact admin.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) );
            $wc_userrole_msg_sett = ( !empty($wc_userrole_msg_sett) ? $wc_userrole_msg_sett : esc_html__( 'Sorry :( This user role has been blocked due to some reason. Kindly contact admin.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) );
            $automatic_blacklist = ( !empty($automatic_blacklist) ? $automatic_blacklist : '0' );
            $wc_first_name_relation = ( !empty($wc_first_name_relation) ? $wc_first_name_relation : 'or' );
            $wc_last_name_relation = ( !empty($wc_last_name_relation) ? $wc_last_name_relation : 'or' );
            //get plugin external user block list
            $wc_enb_ext_bl = filter_input( INPUT_POST, 'wc_enb_ext_bl', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wc_enb_ext_bl = ( !empty($wc_enb_ext_bl) ? $wc_enb_ext_bl : '' );
            $wcbluoption_array = array();
            $wcbluoption_array['wcblu_register_type'] = $wc_register_type;
            $wcbluoption_array['wcblu_place_order_type'] = $wc_place_order_type;
            $wcbluoption_array['wcblu_address_type'] = $wc_address_type;
            $wcbluoption_array['wcblu_block_ip'] = $getipbasic;
            $wcbluoption_array['wcblu_block_state'] = $getstate;
            $wcbluoption_array['wcblu_block_country'] = $getcountry;
            $wcbluoption_array['wcblu_block_zip'] = $getzip;
            $wcbluoption_array['wcblu_block_domain'] = $getdomain;
            $wcbluoption_array['wcblu_block_domain_ext'] = $getdomainext;
            $wcbluoption_array['wcblu_block_phone'] = $getphone;
            $wcbluoption_array['wcblu_block_first_name'] = $getfirstname;
            $wcbluoption_array['wcblu_block_last_name'] = $getlastname;
            $wcbluoption_array['wcblu_block_user_agent'] = $getuseragent;
            $wcbluoption_array['wcblu_block_zone'] = $getzone;
            $wcbluoption_array['wcblu_block_userrole'] = $getuserrole;
            $wcbluoption_array['wcblu_email_msg'] = $wc_email_msg_sett;
            $wcbluoption_array['wcblu_ip_msg'] = $wc_ip_msg_sett;
            $wcbluoption_array['wcblu_state_msg'] = $wc_state_msg_sett;
            $wcbluoption_array['wcblu_country_msg'] = $wc_country_msg_sett;
            $wcbluoption_array['wcblu_zpcode_msg'] = $wc_zpcode_msg_sett;
            $wcbluoption_array['wcblu_domain_msg'] = $wc_domain_msg_sett;
            $wcbluoption_array['wcblu_domain_ext_msg'] = $wc_domain_ext_msg_sett;
            $wcbluoption_array['wcblu_phone_msg'] = $wc_phone_msg_sett;
            $wcbluoption_array['wcblu_first_name_msg'] = $wc_first_name_msg_sett;
            $wcbluoption_array['wcblu_last_name_msg'] = $wc_last_name_msg_sett;
            $wcbluoption_array['wcblu_user_agent_msg'] = $wc_user_agent_msg_sett;
            $wcbluoption_array['wcblu_zone_msg'] = $wc_zone_msg_sett;
            $wcbluoption_array['wcblu_userrole_msg'] = $wc_userrole_msg_sett;
            $wcbluoption_array['wcblu_enable_ext_bl'] = $wc_enb_ext_bl;
            $wcbluoption_array['wcblu_block_email'] = $getemail;
            $wcbluoption_array['wcblu_automatic_blacklist'] = $automatic_blacklist;
            $wcbluoption_array['wcblu_all_chk_selection'] = $wc_all_chk_selection;
            $wcbluoption_array['wcblu_first_name_relation'] = $wc_first_name_relation;
            $wcbluoption_array['wcblu_last_name_relation'] = $wc_last_name_relation;
            $wcbluoption_array = wp_json_encode( $wcbluoption_array );
            update_option( 'wcblu_option', $wcbluoption_array );
        }
        
        wp_safe_redirect( add_query_arg( array(
            'page'    => 'woocommerce_blacklist_users',
            'success' => 'true',
        ), admin_url( 'admin.php' ) ) );
        exit;
    }
    
    /**
     * function for reset plugins all settings.
     *
     */
    public function wcblu_reset_settings()
    {
        check_ajax_referer( 'wcblu-ajax-nonce', 'nonce' );
        update_option( 'wcblu_option', '' );
        die;
    }
    
    /**
     * Remove email address from blacklist if whiltelisted.
     */
    public function wcblu_check_blacklist_whitelist()
    {
        check_ajax_referer( 'wcblu-ajax-nonce', 'nonce' );
        $wcblu_option = ( !empty(get_option( 'wcblu_option' )) ? get_option( 'wcblu_option' ) : '' );
        $wcblu_option = json_decode( $wcblu_option, true );
        $wcblu_block_email = ( isset( $wcblu_option['wcblu_block_email'] ) ? $wcblu_option['wcblu_block_email'] : '' );
        
        if ( !empty($wcblu_block_email) ) {
            $whitelistarray = filter_input( INPUT_POST, 'whitelist', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $expwhitearray = explode( "\n", $whitelistarray );
            $result = array_diff( $wcblu_block_email, $expwhitearray );
            $wcblu_option['wcblu_block_email'] = $result;
            $wcblu_option_array = wp_json_encode( $wcblu_option );
            update_option( 'wcblu_option', $wcblu_option_array );
        }
        
        wp_die();
    }
    
    /**
     * function for update general settings.
     */
    function wcblu_update_general_settings()
    {
        $getformsumbitaction = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $wcblu_save_settings_nonce = filter_input( INPUT_POST, 'wcblu_plugin_general_settings_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        /**
         * check form action
         *
         */
        
        if ( !empty($getformsumbitaction) && 'submit_general_setting_form_wcblu' === $getformsumbitaction && wp_verify_nonce( sanitize_text_field( $wcblu_save_settings_nonce ), 'wcblu_plugin_general_settings' ) ) {
            $wcbfc_fraud_check_status = filter_input( INPUT_POST, 'wcbfc_fraud_check_status', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_fraud_check_before_pay = filter_input( INPUT_POST, 'wcbfc_fraud_check_before_pay', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcblu_pre_payment_message = filter_input( INPUT_POST, 'wcblu_pre_payment_message', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_update_order_status_on_score = filter_input( INPUT_POST, 'wcbfc_update_order_status_on_score', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_settings_low_risk_threshold = filter_input( INPUT_POST, 'wcbfc_settings_low_risk_threshold', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_settings_high_risk_threshold = filter_input( INPUT_POST, 'wcbfc_settings_high_risk_threshold', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_email_notification = filter_input( INPUT_POST, 'wcbfc_email_notification', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_settings_cancel_score = filter_input( INPUT_POST, 'wcbfc_settings_cancel_score', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_settings_email_score = filter_input( INPUT_POST, 'wcbfc_settings_email_score', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_settings_hold_score = filter_input( INPUT_POST, 'wcbfc_settings_hold_score', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcblu_settings_custom_email = filter_input( INPUT_POST, 'wcblu_settings_custom_email', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcblu_settings_whitelist = filter_input( INPUT_POST, 'wcblu_settings_whitelist', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcblu_whitelist_payment_method = filter_input(
                INPUT_POST,
                'wcblu_whitelist_payment_method',
                FILTER_DEFAULT,
                FILTER_REQUIRE_ARRAY
            );
            $wcbfc_enable_whitelist_payment_method = filter_input( INPUT_POST, 'wcbfc_enable_whitelist_payment_method', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_enable_whitelist_user_roles = filter_input( INPUT_POST, 'wcbfc_enable_whitelist_user_roles', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcblu_whitelist_user_roles = filter_input(
                INPUT_POST,
                'wcblu_whitelist_user_roles',
                FILTER_DEFAULT,
                FILTER_REQUIRE_ARRAY
            );
            $wcbfc_fraud_check_status = ( empty($wcbfc_fraud_check_status) ? 'off' : $wcbfc_fraud_check_status );
            $wcbfc_fraud_check_before_pay = ( empty($wcbfc_fraud_check_before_pay) ? '0' : $wcbfc_fraud_check_before_pay );
            $wcblu_pre_payment_message = ( empty($wcblu_pre_payment_message) ? '' : $wcblu_pre_payment_message );
            $wcbfc_update_order_status_on_score = ( empty($wcbfc_update_order_status_on_score) ? '0' : $wcbfc_update_order_status_on_score );
            $wcbfc_settings_low_risk_threshold = ( empty($wcbfc_settings_low_risk_threshold) ? '0' : $wcbfc_settings_low_risk_threshold );
            $wcbfc_settings_high_risk_threshold = ( empty($wcbfc_settings_high_risk_threshold) ? '0' : $wcbfc_settings_high_risk_threshold );
            $wcbfc_email_notification = ( empty($wcbfc_email_notification) ? '0' : $wcbfc_email_notification );
            $wcbfc_settings_cancel_score = ( empty($wcbfc_settings_cancel_score) ? '0' : $wcbfc_settings_cancel_score );
            $wcbfc_settings_hold_score = ( empty($wcbfc_settings_hold_score) ? '0' : $wcbfc_settings_hold_score );
            $wcbfc_settings_email_score = ( empty($wcbfc_settings_email_score) ? '0' : $wcbfc_settings_email_score );
            $wcblu_settings_custom_email = ( empty($wcblu_settings_custom_email) ? '' : $wcblu_settings_custom_email );
            $wcblu_settings_whitelist = ( empty($wcblu_settings_whitelist) ? '' : $wcblu_settings_whitelist );
            $wcblu_whitelist_payment_method = ( empty($wcblu_whitelist_payment_method) ? array() : $wcblu_whitelist_payment_method );
            $wcbfc_enable_whitelist_payment_method = ( empty($wcbfc_enable_whitelist_payment_method) ? '' : $wcbfc_enable_whitelist_payment_method );
            $wcbfc_enable_whitelist_user_roles = ( empty($wcbfc_enable_whitelist_user_roles) ? '0' : $wcbfc_enable_whitelist_user_roles );
            $wcblu_whitelist_user_roles = ( empty($wcblu_whitelist_user_roles) ? array() : $wcblu_whitelist_user_roles );
            /**
             * Whitelist the user roles
             */
            
            if ( '1' === $wcbfc_enable_whitelist_user_roles && !empty($wcblu_whitelist_user_roles) ) {
                $wcblu_option = ( !empty(get_option( 'wcblu_option' )) ? get_option( 'wcblu_option' ) : '' );
                $wcblu_option = json_decode( $wcblu_option, true );
                $wcblu_block_userrole = ( isset( $wcblu_option['wcblu_block_userrole'] ) ? $wcblu_option['wcblu_block_userrole'] : '' );
                
                if ( !empty($wcblu_block_userrole) ) {
                    $expwhitearray = $wcblu_whitelist_user_roles;
                    $result = array_diff( $wcblu_block_userrole, $expwhitearray );
                    $wcblu_option['wcblu_block_userrole'] = $result;
                    $wcblu_option_array = wp_json_encode( $wcblu_option );
                    update_option( 'wcblu_option', $wcblu_option_array );
                }
            
            }
            
            $wcblugeneraloption_array['wcbfc_fraud_check_before_pay'] = $wcbfc_fraud_check_before_pay;
            $wcblugeneraloption_array['wcblu_pre_payment_message'] = $wcblu_pre_payment_message;
            $wcblugeneraloption_array['wcbfc_update_order_status_on_score'] = $wcbfc_update_order_status_on_score;
            $wcblugeneraloption_array['wcbfc_settings_low_risk_threshold'] = $wcbfc_settings_low_risk_threshold;
            $wcblugeneraloption_array['wcbfc_settings_high_risk_threshold'] = $wcbfc_settings_high_risk_threshold;
            $wcblugeneraloption_array['wcbfc_email_notification'] = $wcbfc_email_notification;
            $wcblugeneraloption_array['wcbfc_settings_cancel_score'] = $wcbfc_settings_cancel_score;
            $wcblugeneraloption_array['wcbfc_settings_hold_score'] = $wcbfc_settings_hold_score;
            $wcblugeneraloption_array['wcbfc_settings_email_score'] = $wcbfc_settings_email_score;
            $wcblugeneraloption_array['wcblu_settings_custom_email'] = $wcblu_settings_custom_email;
            $wcblugeneraloption_array['wcblu_settings_whitelist'] = $wcblu_settings_whitelist;
            $wcblugeneraloption_array['wcbfc_fraud_check_status'] = $wcbfc_fraud_check_status;
            $wcblugeneraloption_array['wcblu_whitelist_payment_method'] = $wcblu_whitelist_payment_method;
            $wcblugeneraloption_array['wcbfc_enable_whitelist_payment_method'] = $wcbfc_enable_whitelist_payment_method;
            $wcblugeneraloption_array['wcbfc_enable_whitelist_user_roles'] = $wcbfc_enable_whitelist_user_roles;
            $wcblugeneraloption_array['wcblu_whitelist_user_roles'] = $wcblu_whitelist_user_roles;
            $wcblugeneralopt_array = wp_json_encode( $wcblugeneraloption_array );
            update_option( 'wcblu_general_option', $wcblugeneralopt_array );
        }
        
        wp_safe_redirect( add_query_arg( array(
            'page'    => 'wcblu-general-settings',
            'success' => 'true',
        ), admin_url( 'admin.php' ) ) );
        exit;
    }
    
    /**
     * function for update rules settings.
     */
    public function wcblu_update_rules_settings()
    {
        $getformsumbitaction = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $wcblu_save_rule_settings = filter_input( INPUT_POST, 'wcblu_save_rule_settings_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        /**
         * check form action
         *
         */
        
        if ( !empty($getformsumbitaction) && 'submit_general_rules_form_wcblu' === $getformsumbitaction && wp_verify_nonce( sanitize_text_field( $wcblu_save_rule_settings ), 'wcblu_save_rule_settings' ) ) {
            $wcbfc_first_order_status = filter_input( INPUT_POST, 'wcbfc_first_order_status', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_first_order_weight = filter_input( INPUT_POST, 'wcbfc_first_order_weight', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_first_order_custom = filter_input( INPUT_POST, 'wcbfc_first_order_custom', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_first_order_custom_weight = filter_input( INPUT_POST, 'wcbfc_first_order_custom_weight', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_bca_order = filter_input( INPUT_POST, 'wcbfc_bca_order', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_bca_order_weight = filter_input( INPUT_POST, 'wcbfc_bca_order_weight', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_proxy_order = filter_input( INPUT_POST, 'wcbfc_proxy_order', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_proxy_order_weight = filter_input( INPUT_POST, 'wcbfc_proxy_order_weight', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_international_order = filter_input( INPUT_POST, 'wcbfc_international_order', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_international_order_weight = filter_input( INPUT_POST, 'wcbfc_international_order_weight', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_suspecius_email = filter_input( INPUT_POST, 'wcbfc_suspecius_email', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_suspecius_email_list = filter_input(
                INPUT_POST,
                'wcbfc_suspecius_email_list',
                FILTER_DEFAULT,
                FILTER_REQUIRE_ARRAY
            );
            $wcbfc_suspecious_email_weight = filter_input( INPUT_POST, 'wcbfc_suspecious_email_weight', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_unsafe_countries = filter_input( INPUT_POST, 'wcbfc_unsafe_countries', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcblu_define_unsafe_countries_list = filter_input(
                INPUT_POST,
                'wcblu_define_unsafe_countries_list',
                FILTER_DEFAULT,
                FILTER_REQUIRE_ARRAY
            );
            $wcbfc_unsafe_countries_ip = filter_input( INPUT_POST, 'wcbfc_unsafe_countries_ip', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_unsafe_countries_weight = filter_input( INPUT_POST, 'wcbfc_unsafe_countries_weight', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_billing_phone_number_order = filter_input( INPUT_POST, 'wcbfc_billing_phone_number_order', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_phone_number_order_weight = filter_input( INPUT_POST, 'wcbfc_billing_phone_number_order_weight', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_ip_multiple_check = filter_input( INPUT_POST, 'wcbfc_ip_multiple_check', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_ip_multiple_time_span = filter_input( INPUT_POST, 'wcbfc_ip_multiple_time_span', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_ip_multiple_weight = filter_input( INPUT_POST, 'wcbfc_ip_multiple_weight', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_order_avg_amount_check = filter_input( INPUT_POST, 'wcbfc_order_avg_amount_check', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_order_avg_amount_time_span = filter_input( INPUT_POST, 'wcbfc_order_avg_amount_time_span', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_order_avg_amount_weight = filter_input( INPUT_POST, 'wcbfc_order_avg_amount_weight', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_order_amount_check = filter_input( INPUT_POST, 'wcbfc_order_amount_check', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_max_order_attempt_span = filter_input( INPUT_POST, 'wcbfc_max_order_attempt_span', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_max_order_attempt_weight = filter_input( INPUT_POST, 'wcbfc_max_order_attempt_weight', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_too_many_oa_check = filter_input( INPUT_POST, 'wcbfc_too_many_oa_check', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_too_many_oats_attempt_span = filter_input( INPUT_POST, 'wcbfc_too_many_oats_attempt_span', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_too_many_oatos_attempt_span = filter_input( INPUT_POST, 'wcbfc_too_many_oatos_attempt_span', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_too_many_oa_attempt_weight = filter_input( INPUT_POST, 'wcbfc_too_many_oa_attempt_weight', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcbfc_first_order_status = ( empty($wcbfc_first_order_status) ? '0' : $wcbfc_first_order_status );
            $wcbfc_first_order_weight = ( empty($wcbfc_first_order_weight) ? '5' : $wcbfc_first_order_weight );
            $wcbfc_first_order_custom = ( empty($wcbfc_first_order_custom) ? '0' : $wcbfc_first_order_custom );
            $wcbfc_first_order_custom_weight = ( empty($wcbfc_first_order_custom_weight) ? '20' : $wcbfc_first_order_custom_weight );
            $wcbfc_bca_order = ( empty($wcbfc_bca_order) ? '0' : $wcbfc_bca_order );
            $wcbfc_bca_order_weight = ( empty($wcbfc_bca_order_weight) ? '20' : $wcbfc_bca_order_weight );
            $wcbfc_proxy_order = ( empty($wcbfc_proxy_order) ? '0' : $wcbfc_proxy_order );
            $wcbfc_proxy_order_weight = ( empty($wcbfc_proxy_order_weight) ? '50' : $wcbfc_proxy_order_weight );
            $wcbfc_international_order = ( empty($wcbfc_international_order) ? '0' : $wcbfc_international_order );
            $wcbfc_international_order_weight = ( empty($wcbfc_international_order_weight) ? '50' : $wcbfc_international_order_weight );
            $wcbfc_suspecius_email = ( empty($wcbfc_suspecius_email) ? '0' : $wcbfc_suspecius_email );
            $wcbfc_suspecius_email_list = ( empty($wcbfc_suspecius_email_list) ? array() : $wcbfc_suspecius_email_list );
            $wcbfc_suspecious_email_weight = ( empty($wcbfc_suspecious_email_weight) ? '10' : $wcbfc_suspecious_email_weight );
            $wcbfc_unsafe_countries = ( empty($wcbfc_unsafe_countries) ? '0' : $wcbfc_unsafe_countries );
            $wcbfc_unsafe_countries_ip = ( empty($wcbfc_unsafe_countries_ip) ? '0' : $wcbfc_unsafe_countries_ip );
            $wcblu_define_unsafe_countries_list = ( empty($wcblu_define_unsafe_countries_list) ? array() : $wcblu_define_unsafe_countries_list );
            $wcbfc_unsafe_countries_weight = ( empty($wcbfc_unsafe_countries_weight) ? '10' : $wcbfc_unsafe_countries_weight );
            $wcbfc_billing_phone_number_order = ( empty($wcbfc_billing_phone_number_order) ? '0' : $wcbfc_billing_phone_number_order );
            $wcbfc_phone_number_order_weight = ( empty($wcbfc_phone_number_order_weight) ? '15' : $wcbfc_phone_number_order_weight );
            $wcbfc_ip_multiple_check = ( empty($wcbfc_ip_multiple_check) ? '0' : $wcbfc_ip_multiple_check );
            $wcbfc_ip_multiple_time_span = ( empty($wcbfc_ip_multiple_time_span) ? '30' : $wcbfc_ip_multiple_time_span );
            $wcbfc_ip_multiple_weight = ( empty($wcbfc_ip_multiple_weight) ? '25' : $wcbfc_ip_multiple_weight );
            $wcbfc_order_avg_amount_check = ( empty($wcbfc_order_avg_amount_check) ? '0' : $wcbfc_order_avg_amount_check );
            $wcbfc_order_avg_amount_time_span = ( empty($wcbfc_order_avg_amount_time_span) ? '2' : $wcbfc_order_avg_amount_time_span );
            $wcbfc_order_avg_amount_weight = ( empty($wcbfc_order_avg_amount_weight) ? '15' : $wcbfc_order_avg_amount_weight );
            $wcbfc_order_amount_check = ( empty($wcbfc_order_amount_check) ? '0' : $wcbfc_order_amount_check );
            $wcbfc_max_order_attempt_span = ( empty($wcbfc_max_order_attempt_span) ? '0' : $wcbfc_max_order_attempt_span );
            $wcbfc_max_order_attempt_weight = ( empty($wcbfc_max_order_attempt_weight) ? '5' : $wcbfc_max_order_attempt_weight );
            $wcbfc_too_many_oa_check = ( empty($wcbfc_too_many_oa_check) ? '0' : $wcbfc_too_many_oa_check );
            $wcbfc_too_many_oats_attempt_span = ( empty($wcbfc_too_many_oats_attempt_span) ? '24' : $wcbfc_too_many_oats_attempt_span );
            $wcbfc_too_many_oatos_attempt_span = ( empty($wcbfc_too_many_oatos_attempt_span) ? '5' : $wcbfc_too_many_oatos_attempt_span );
            $wcbfc_too_many_oa_attempt_weight = ( empty($wcbfc_too_many_oa_attempt_weight) ? '25' : $wcbfc_too_many_oa_attempt_weight );
            $wcbluruleoption_array['wcbfc_first_order_status'] = $wcbfc_first_order_status;
            $wcbluruleoption_array['wcbfc_first_order_weight'] = $wcbfc_first_order_weight;
            $wcbluruleoption_array['wcbfc_first_order_custom'] = $wcbfc_first_order_custom;
            $wcbluruleoption_array['wcbfc_first_order_custom_weight'] = $wcbfc_first_order_custom_weight;
            $wcbluruleoption_array['wcbfc_bca_order'] = $wcbfc_bca_order;
            $wcbluruleoption_array['wcbfc_bca_order_weight'] = $wcbfc_bca_order_weight;
            $wcbluruleoption_array['wcbfc_proxy_order'] = $wcbfc_proxy_order;
            $wcbluruleoption_array['wcbfc_proxy_order_weight'] = $wcbfc_proxy_order_weight;
            $wcbluruleoption_array['wcbfc_international_order'] = $wcbfc_international_order;
            $wcbluruleoption_array['wcbfc_international_order_weight'] = $wcbfc_international_order_weight;
            $wcbluruleoption_array['wcbfc_suspecius_email'] = $wcbfc_suspecius_email;
            $wcbluruleoption_array['wcbfc_suspecius_email_list'] = $wcbfc_suspecius_email_list;
            $wcbluruleoption_array['wcbfc_suspecious_email_weight'] = $wcbfc_suspecious_email_weight;
            $wcbluruleoption_array['wcbfc_unsafe_countries'] = $wcbfc_unsafe_countries;
            $wcbluruleoption_array['wcbfc_unsafe_countries_ip'] = $wcbfc_unsafe_countries_ip;
            $wcbluruleoption_array['wcblu_define_unsafe_countries_list'] = $wcblu_define_unsafe_countries_list;
            $wcbluruleoption_array['wcbfc_unsafe_countries_weight'] = $wcbfc_unsafe_countries_weight;
            $wcbluruleoption_array['wcbfc_billing_phone_number_order'] = $wcbfc_billing_phone_number_order;
            $wcbluruleoption_array['wcbfc_billing_phone_number_order_weight'] = $wcbfc_phone_number_order_weight;
            $wcbluruleoption_array['wcbfc_ip_multiple_check'] = $wcbfc_ip_multiple_check;
            $wcbluruleoption_array['wcbfc_ip_multiple_time_span'] = $wcbfc_ip_multiple_time_span;
            $wcbluruleoption_array['wcbfc_ip_multiple_weight'] = $wcbfc_ip_multiple_weight;
            $wcbluruleoption_array['wcbfc_order_avg_amount_check'] = $wcbfc_order_avg_amount_check;
            $wcbluruleoption_array['wcbfc_order_avg_amount_time_span'] = $wcbfc_order_avg_amount_time_span;
            $wcbluruleoption_array['wcbfc_order_avg_amount_weight'] = $wcbfc_order_avg_amount_weight;
            $wcbluruleoption_array['wcbfc_order_amount_check'] = $wcbfc_order_amount_check;
            $wcbluruleoption_array['wcbfc_max_order_attempt_span'] = $wcbfc_max_order_attempt_span;
            $wcbluruleoption_array['wcbfc_max_order_attempt_weight'] = $wcbfc_max_order_attempt_weight;
            $wcbluruleoption_array['wcbfc_too_many_oa_check'] = $wcbfc_too_many_oa_check;
            $wcbluruleoption_array['wcbfc_too_many_oats_attempt_span'] = $wcbfc_too_many_oats_attempt_span;
            $wcbluruleoption_array['wcbfc_too_many_oatos_attempt_span'] = $wcbfc_too_many_oatos_attempt_span;
            $wcbluruleoption_array['wcbfc_too_many_oa_attempt_weight'] = $wcbfc_too_many_oa_attempt_weight;
            $wcbluruleopt_array = wp_json_encode( $wcbluruleoption_array );
            update_option( 'wcblu_rules_option', $wcbluruleopt_array );
        }
        
        wp_safe_redirect( add_query_arg( array(
            'page'    => 'wcblu-auto-rules',
            'success' => 'true',
        ), admin_url( 'admin.php' ) ) );
        exit;
    }
    
    /**
     * Add risk column.
     */
    public function wcblu_add_column( $columns )
    {
        $getGeneralSettings = get_option( 'wcblu_general_option' );
        $getGeneralSettings = ( empty($getGeneralSettings) ? '' : $getGeneralSettings );
        $getGeneralSettingsArray = json_decode( $getGeneralSettings, true );
        $wcbfc_fraud_check_status = ( empty($getGeneralSettingsArray['wcbfc_fraud_check_status']) ? 'off' : $getGeneralSettingsArray['wcbfc_fraud_check_status'] );
        
        if ( isset( $wcbfc_fraud_check_status ) && 'on' === $wcbfc_fraud_check_status ) {
            foreach ( $columns as $key => $column ) {
                $reordered_columns[$key] = $column;
                if ( 'order_status' === $key ) {
                    // Inserting after "Status" column.
                    $reordered_columns['wcblu_anti_fraud'] = 'Risk Status';
                }
            }
            $columns = $reordered_columns;
        }
        
        return $columns;
    }
    
    /**
     * Render column.
     */
    public function wcblu_render_column( $column, $order )
    {
        global  $post ;
        
        if ( 'wcblu_anti_fraud' == $column ) {
            $order_id = $order->get_id();
            $fscore = get_post_meta( $order_id, 'wcbfc_order_score', true );
            $fscore = ( empty($fscore) ? '' : $fscore );
            $getGeneralSettings = get_option( 'wcblu_general_option' );
            $getGeneralSettings = ( empty($getGeneralSettings) ? '' : $getGeneralSettings );
            $getGeneralSettingsArray = json_decode( $getGeneralSettings, true );
            $wcbfc_whitelisted_email = get_post_meta( $order_id, 'wcbfc_whitelisted_email', true );
            $mediumRisk = ( empty($getGeneralSettingsArray['wcbfc_settings_low_risk_threshold']) ? '' : $getGeneralSettingsArray['wcbfc_settings_low_risk_threshold'] );
            $heighRisk = ( empty($getGeneralSettingsArray['wcbfc_settings_high_risk_threshold']) ? '' : $getGeneralSettingsArray['wcbfc_settings_high_risk_threshold'] );
            $allow_html_args = array(
                'span' => array(
                'class' => array(),
            ),
            );
            
            if ( $fscore > 0 && $fscore <= $mediumRisk ) {
                $label = '<span class="dashicons dashicons-shield-alt low"></span>';
            } else {
                
                if ( $fscore >= $mediumRisk && $fscore <= $heighRisk ) {
                    $label = '<span class="dashicons dashicons-shield-alt medium"></span>';
                } else {
                    
                    if ( $fscore >= $heighRisk ) {
                        $label = '<span class="dashicons dashicons-shield-alt high"></span>';
                    } else {
                        $label = '<span class="dashicons dashicons-shield-alt empty"></span>';
                    }
                
                }
            
            }
            
            if ( isset( $wcbfc_whitelisted_email ) && !empty($wcbfc_whitelisted_email) ) {
                $label = '<span class="dashicons dashicons-shield-alt wh-empty"></span>';
            }
            echo  wp_kses( $label, $allow_html_args ) ;
        }
    
    }
    
    /**
     * Render column.
     */
    public function wcblu_render_column_post_table( $column )
    {
        global  $post ;
        
        if ( 'wcblu_anti_fraud' == $column ) {
            $fscore = get_post_meta( $post->ID, 'wcbfc_order_score', true );
            $fscore = ( empty($fscore) ? '' : $fscore );
            $getGeneralSettings = get_option( 'wcblu_general_option' );
            $getGeneralSettings = ( empty($getGeneralSettings) ? '' : $getGeneralSettings );
            $getGeneralSettingsArray = json_decode( $getGeneralSettings, true );
            $wcbfc_whitelisted_email = get_post_meta( $post->ID, 'wcbfc_whitelisted_email', true );
            $mediumRisk = ( empty($getGeneralSettingsArray['wcbfc_settings_low_risk_threshold']) ? '' : $getGeneralSettingsArray['wcbfc_settings_low_risk_threshold'] );
            $heighRisk = ( empty($getGeneralSettingsArray['wcbfc_settings_high_risk_threshold']) ? '' : $getGeneralSettingsArray['wcbfc_settings_high_risk_threshold'] );
            $allow_html_args = array(
                'span' => array(
                'class' => array(),
            ),
            );
            
            if ( $fscore > 0 && $fscore <= $mediumRisk ) {
                $label = '<span class="dashicons dashicons-shield-alt low"></span>';
            } else {
                
                if ( $fscore >= $mediumRisk && $fscore <= $heighRisk ) {
                    $label = '<span class="dashicons dashicons-shield-alt medium"></span>';
                } else {
                    
                    if ( $fscore >= $heighRisk ) {
                        $label = '<span class="dashicons dashicons-shield-alt high"></span>';
                    } else {
                        $label = '<span class="dashicons dashicons-shield-alt empty"></span>';
                    }
                
                }
            
            }
            
            if ( isset( $wcbfc_whitelisted_email ) && !empty($wcbfc_whitelisted_email) ) {
                $label = '<span class="dashicons dashicons-shield-alt wh-empty"></span>';
            }
            echo  wp_kses( $label, $allow_html_args ) ;
        }
    
    }
    
    /**
     * Score widget in order 
     */
    public function wcblu_add_meta_boxes( $post_type, $post )
    {
        $order = ( $post instanceof WP_Post ? wc_get_order( $post->ID ) : $post );
        
        if ( 'shop_order' === $post_type || 'woocommerce_page_wc-orders' === $post_type ) {
            $getGeneralSettings = get_option( 'wcblu_general_option' );
            $getGeneralSettings = ( empty($getGeneralSettings) ? '' : $getGeneralSettings );
            $getGeneralSettingsArray = json_decode( $getGeneralSettings, true );
            $wcbfc_fraud_check_status = ( empty($getGeneralSettingsArray['wcbfc_fraud_check_status']) ? 'off' : $getGeneralSettingsArray['wcbfc_fraud_check_status'] );
            $order_score = get_post_meta( $order->get_id(), 'wcbfc_order_score', true );
            $order_score = ( empty($order_score) ? '0' : $order_score );
            
            if ( isset( $wcbfc_fraud_check_status ) && 'on' === $wcbfc_fraud_check_status ) {
                
                if ( class_exists( "Automattic\\WooCommerce\\Internal\\DataStores\\Orders\\CustomOrdersTableController" ) ) {
                    $screen = ( wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order' );
                } else {
                    $screen = 'shop_order';
                }
                
                add_meta_box(
                    'wcblu-meta-box-id',
                    esc_html__( 'Fraud Risk', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
                    array( $this, 'wcblu_fc_meta_box_callback' ),
                    $screen,
                    'side',
                    'high'
                );
            }
        
        }
    
    }
    
    function wcblu_fc_meta_box_callback( $order )
    {
        
        if ( class_exists( 'Automattic\\WooCommerce\\Utilities\\OrderUtil' ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
            // HPOS usage is enabled.
            $order_id = $order->get_id();
        } else {
            // Traditional CPT-based orders are in use.
            $order_id = $order->ID;
        }
        
        $getGeneralSettings = get_option( 'wcblu_general_option' );
        $getGeneralSettings = ( empty($getGeneralSettings) ? '' : $getGeneralSettings );
        $getGeneralSettingsArray = json_decode( $getGeneralSettings, true );
        $mediumRisk = ( empty($getGeneralSettingsArray['wcbfc_settings_low_risk_threshold']) ? '' : $getGeneralSettingsArray['wcbfc_settings_low_risk_threshold'] );
        $heighRisk = ( empty($getGeneralSettingsArray['wcbfc_settings_high_risk_threshold']) ? '' : $getGeneralSettingsArray['wcbfc_settings_high_risk_threshold'] );
        $wcbfc_score_arry = get_post_meta( $order_id, 'wcbfc_score', true );
        $wcbfc_score_msg_arry = get_post_meta( $order_id, 'wcbfc_score_msg', true );
        $wcbfc_whitelisted_email = get_post_meta( $order_id, 'wcbfc_whitelisted_email', true );
        $order_score = get_post_meta( $order_id, 'wcbfc_order_score', true );
        $wcbfc_score_arry = ( empty($wcbfc_score_arry) ? array() : $wcbfc_score_arry );
        $wcbfc_score_msg_arry = ( empty($wcbfc_score_msg_arry) ? array() : $wcbfc_score_msg_arry );
        $fscore = 0;
        $score_lables = '';
        $color = '#ccc';
        $label = 'risk';
        $risk_label_color = '';
        if ( isset( $wcbfc_whitelisted_email ) && !empty($wcbfc_whitelisted_email) ) {
            $score_lables .= '<li>' . $wcbfc_whitelisted_email . '</li>';
        }
        
        if ( '0' === $order_score ) {
            $fscore = '0';
        } else {
            foreach ( $wcbfc_score_arry as $key => $val ) {
                $fscore = $fscore + (int) $val;
                $score_lables .= '<li>' . $wcbfc_score_msg_arry[$key] . '</li>';
            }
        }
        
        $fscore = ( $fscore >= 100 ? 100 : $fscore );
        
        if ( $fscore > 0 && $fscore <= $mediumRisk ) {
            $color = '#7AD03A';
            $label = esc_html__( 'Low Risk', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        } else {
            
            if ( $fscore >= $mediumRisk && $fscore <= $heighRisk ) {
                $color = '#FFAE00';
                $label = esc_html__( 'Medium Risk', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
            } else {
                
                if ( $fscore >= $heighRisk ) {
                    $color = '#D54E21';
                    $label = esc_html__( 'High Risk', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
                }
            
            }
        
        }
        
        $allow_html_args = array(
            'li' => array(),
        );
        
        if ( isset( $wcbfc_whitelisted_email ) && !empty($wcbfc_whitelisted_email) ) {
            $color = '#4f575d';
            $risk_label_color = '#4f575d';
        } else {
            $risk_label_color = '#D54E21';
        }
        
        ?>
		<div class="wcbfc_position">
			<input class="wcblu_chart" data-width="50%" data-fgColor="<?php 
        echo  esc_attr( $color ) ;
        ?>" data-thickness=".4" data-readOnly=true value="0" rel="<?php 
        echo  esc_attr( $fscore ) ;
        ?>">
		</div>
		<span class="mb-score-label" style="color:<?php 
        echo  esc_attr( $color ) ;
        ?>;"><?php 
        echo  esc_html( $fscore . ' % ' ) ;
        echo  esc_html( $label ) ;
        ?></span>
		<div class="mb-score-label-list">
			<ul style="color:<?php 
        echo  esc_attr( $risk_label_color ) ;
        ?>">
				<?php 
        echo  wp_kses( $score_lables, $allow_html_args ) ;
        ?>
			</ul>
		</div>
	
		<script type="text/javascript">
			jQuery('.wcblu_chart').knob();
			var data = jQuery('.wcblu_chart').attr('rel');
			jQuery({value: 0}).animate({value: data}, {
				duration: 3000,
				easing  : 'swing',
				step    : function () {
					jQuery('.wcblu_chart').val(Math.ceil(this.value)).trigger('change');
				}
			});
			jQuery('.mb-score-label-list').click( function(){
				jQuery(this).find('ul').fadeToggle();
			});
			jQuery(".mb-score-label-list ul").click(function(e) {
				e.stopPropagation();
			});
		</script>
		<?php 
    }
    
    /**
     * function for export settings in JSON file
     */
    public function wcblu_export_settings()
    {
        check_ajax_referer( 'wcblu-ajax-nonce', 'nonce' );
        $main_arr = array();
        $current_setting = ( get_option( 'wcblu_option' ) ? get_option( 'wcblu_option' ) : '' );
        $general_setting = ( get_option( 'wcblu_general_option' ) ? get_option( 'wcblu_general_option' ) : '' );
        $rules_setting = ( get_option( 'wcblu_rules_option' ) ? get_option( 'wcblu_rules_option' ) : '' );
        $main_arr['wcblu_option'] = json_decode( $current_setting );
        $main_arr['wcblu_general_option'] = json_decode( $general_setting );
        $main_arr['wcblu_rules_option'] = json_decode( $rules_setting );
        $merge_all_arr = json_encode( $main_arr );
        
        if ( empty($merge_all_arr) ) {
            $return = array(
                'message' => esc_html__( 'No data to export! please setup setting then export.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            );
        } else {
            $filename = 'export_settings_' . time() . '.json';
            $file_path = wp_upload_dir()['basedir'] . '/wcblu-export/';
            if ( !file_exists( $file_path ) ) {
                wp_mkdir_p( $file_path );
            }
            $file_path = wp_upload_dir()['basedir'] . '/wcblu-export/' . $filename;
            $download_path = wp_upload_dir()['baseurl'] . '/wcblu-export/' . $filename;
            $fp = fopen( $file_path, 'w' );
            fwrite( $fp, $merge_all_arr );
            //phpcs:ignore
            fclose( $fp );
            $return = array(
                'message'  => esc_html__( 'Export Done!', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
                'file'     => $download_path,
                'filename' => $filename,
            );
        }
        
        wp_send_json( $return );
    }
    
    /**
     * function for import setting from JSON file
     */
    public function wcblu_import_settings()
    {
        check_ajax_referer( 'wcblu-ajax-nonce', 'nonce' );
        // Allow certain file formats
        $allowTypes = array( 'json' );
        $import_file_args = array(
            'import_file' => array(
            'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'flags'  => FILTER_FORCE_ARRAY,
        ),
        );
        $import_file_arr = filter_var_array( $_FILES, $import_file_args );
        $filename = ( isset( $import_file_arr['import_file']['name'] ) && !empty($import_file_arr['import_file']['name']) ? $import_file_arr['import_file']['name'] : '' );
        $fileType = pathinfo( $filename, PATHINFO_EXTENSION );
        
        if ( !in_array( $fileType, $allowTypes, true ) ) {
            $return = array(
                'success' => false,
                'message' => esc_html__( 'Please add JSON file!', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            );
        } else {
            $filetempname = ( isset( $import_file_arr['import_file']['tmp_name'] ) && !empty($import_file_arr['import_file']['tmp_name']) ? $import_file_arr['import_file']['tmp_name'] : '' );
            // Read JSON file
            $json = file_get_contents( $filetempname );
            $decode_json = json_decode( $json, true );
            $import_flag = false;
            
            if ( isset( $decode_json['wcblu_option'] ) && !empty($decode_json['wcblu_option']) && null !== $decode_json['wcblu_option'] ) {
                $wcblu_option = json_encode( $decode_json['wcblu_option'] );
                update_option( 'wcblu_option', $wcblu_option );
                $import_flag = true;
            }
            
            
            if ( isset( $decode_json['wcblu_general_option'] ) && !empty($decode_json['wcblu_general_option']) && null !== $decode_json['wcblu_general_option'] ) {
                $wcblu_general_option = json_encode( $decode_json['wcblu_general_option'] );
                update_option( 'wcblu_general_option', $wcblu_general_option );
                $import_flag = true;
            }
            
            
            if ( isset( $decode_json['wcblu_rules_option'] ) && !empty($decode_json['wcblu_rules_option']) && null !== $decode_json['wcblu_rules_option'] ) {
                $wcblu_rules_option = json_encode( $decode_json['wcblu_rules_option'] );
                update_option( 'wcblu_rules_option', $wcblu_rules_option );
                $import_flag = true;
            }
            
            if ( true === $import_flag ) {
                $return = array(
                    'success' => true,
                    'message' => esc_html__( 'Data has been successfully imported!', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
                );
            }
        }
        
        wp_send_json( $return );
    }
    
    /**
     * Publick Function for create custom post type
     *
     */
    public function register_custom_post_type_banned_user()
    {
        register_post_type( 'blocked_user', array(
            'labels'            => array(
            'name'               => __( 'Blocked Users', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            'singular_name'      => __( 'Blocked Users', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            'menu_name'          => __( 'Blocked Users', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            'add_new'            => __( 'Add New', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            'add_new_item'       => __( 'Add New Block User', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            'edit'               => __( 'Edit', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            'edit_item'          => __( 'Edit Block User', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            'new_item'           => __( 'New Block User', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            'view'               => __( 'View', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            'view_item'          => __( 'View Block User', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            'search_items'       => __( 'Search Block Users', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            'not_found'          => __( 'No Block User found', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            'not_found_in_trash' => __( 'No Block User found in Trash', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            'parent'             => __( 'Parent Block User Listing', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
        ),
            'public'            => false,
            'show_in_nav_menus' => false,
            'show_in_menu'      => false,
            'show_ui'           => true,
            'supports'          => array( 'title', 'custom-fields' ),
            'taxonomies'        => array( '' ),
            'has_archive'       => false,
            'capabilities'      => array(
            'create_posts' => 'do_not_allow',
        ),
            'map_meta_cap'      => true,
        ) );
    }
    
    /**
     * Ajax call for subscription
     */
    public function wp_add_plugin_wbl_pro()
    {
        $email_id = filter_input( INPUT_POST, 'email_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $email_id = ( isset( $email_id ) && !empty($email_id) ? $email_id : '' );
        $log_url = filter_input( INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $cur_date = gmdate( 'Y-m-d' );
        $request_url = 'http://www.multidots.com/store/wp-content/themes/business-hub-child/API/wp-add-plugin-users.php';
        
        if ( !empty($email_id) ) {
            wp_remote_post( $request_url, array(
                'method'      => 'POST',
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking'    => true,
                'headers'     => array(),
                'body'        => array(
                'user' => array(
                'plugin_id'       => '35',
                'user_email'      => $email_id,
                'plugin_site'     => $log_url,
                'status'          => 1,
                'activation_date' => $cur_date,
            ),
            ),
                'cookies'     => array(),
            ) );
            update_option( 'wbl_plugin_notice_shown', 'true' );
        }
    
    }
    
    /**
     * function for wooCommerce blacklist users create welcom screen page.
     */
    public function welcome_screen_pages_blacklist_users()
    {
        add_dashboard_page(
            'Welcome To Fraud Prevention for WC',
            'Welcome To Fraud Prevention for WC',
            'read',
            'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers',
            array( $this, 'welcome_screen_content_blacklist_users' )
        );
        remove_submenu_page( 'index.php', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        global  $GLOBALS ;
        if ( empty($GLOBALS['admin_page_hooks']['dots_store']) ) {
            add_menu_page(
                'DotStore Plugins',
                __( 'DotStore Plugins', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
                'manage_option',
                'dots_store',
                array( $this, '' ),
                'dashicons-marker',
                25
            );
        }
        
        if ( wbpfoabfc_fs()->is_plan( 'free', true ) ) {
            add_submenu_page(
                'dots_store',
                'woocommerce-blacklist-users',
                'Fraud Prevention',
                'manage_options',
                'woocommerce_blacklist_users',
                'wcblu_custom_admin_setting_options'
            );
        } else {
            add_submenu_page(
                'dots_store',
                'woocommerce-blacklist-users',
                'Fraud Prevention',
                'manage_options',
                'woocommerce_blacklist_users',
                'wcblu_custom_admin_setting_options'
            );
        }
        
        add_submenu_page(
            'dots_store',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'wcblu-upgrade-dashboard',
            array( $this, 'wbclu_free_user_upgrade_page' )
        );
        add_submenu_page(
            'dots_store',
            'banned-user',
            __( 'Block Users Lite', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ),
            'manage_options',
            'blocked_user',
            'custom_banned_user_listing'
        );
        add_submenu_page(
            'dots_store',
            'Get Started',
            'Get Started',
            'manage_options',
            'wblp-get-started',
            array( $this, 'wblp_get_started_page' )
        );
        add_submenu_page(
            'dots_store',
            'Import Export Data',
            'Import Export Data',
            'manage_options',
            'wcblu-import-export-setting',
            array( $this, 'wbclu_import_export_page' )
        );
        add_submenu_page(
            'dots_store',
            'Introduction',
            'Introduction',
            'manage_options',
            'wblp-information',
            array( $this, 'wblp_information_page' )
        );
        /**
         * function for admin side settings option
         */
        function wcblu_custom_admin_setting_options()
        {
            $file_dir = '/partials/header/plugin-header.php';
            if ( file_exists( dirname( __FILE__ ) . $file_dir ) ) {
                include dirname( __FILE__ ) . $file_dir;
            }
            $success_note = filter_input( INPUT_GET, 'success', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            ?>

			<div class="wcblu-col-container wcblu-main-table">
				<?php 
            
            if ( !empty($success_note) ) {
                ?>
					<div id="message" class="updated notice is-dismissible"><p><?php 
                esc_html_e( 'Data has been updated.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
                ?></p></div>
				<?php 
            }
            
            ?>
				<form id="wcblu_plugin_form_id" method="post"
				      action="<?php 
            esc_url( get_admin_url() );
            ?>admin-post.php"
				      enctype="multipart/form-data" novalidate="novalidate">
					<input type='hidden' name='action' value='submit_form_wcblu'/>
					<input type='hidden' name='action-which' value='add'/>
					<?php 
            wp_nonce_field( 'wcblu_blacklist_settings', 'wcblu_blacklist_settings_nonce' );
            ?>
					<?php 
            $getpluginoption = get_option( 'wcblu_option' );
            $getpluginoptionarray = json_decode( $getpluginoption, true );
            $allow_html_args = array(
                'input'      => array(
                'type'     => array(
                'checkbox' => true,
                'text'     => true,
                'submit'   => true,
                'button'   => true,
                'file'     => true,
            ),
                'class'    => true,
                'name'     => true,
                'value'    => true,
                'id'       => true,
                'style'    => true,
                'selected' => true,
                'checked'  => true,
                'disabled' => array(),
            ),
                'select'     => array(
                'id'               => true,
                'data-placeholder' => true,
                'name'             => true,
                'multiple'         => true,
                'class'            => true,
                'style'            => true,
                'selected'         => array(),
                'disabled'         => true,
            ),
                'a'          => array(
                'href'   => array(),
                'title'  => array(),
                'target' => array(),
            ),
                'b'          => array(
                'class' => true,
            ),
                'i'          => array(
                'class' => true,
            ),
                'p'          => array(
                'class' => true,
            ),
                'blockquote' => array(
                'class' => true,
            ),
                'h2'         => array(
                'class' => true,
            ),
                'h3'         => array(
                'class' => true,
            ),
                'ul'         => array(
                'class' => true,
            ),
                'ol'         => array(
                'class' => true,
            ),
                'li'         => array(
                'class' => true,
            ),
                'option'     => array(
                'value'    => true,
                'selected' => true,
            ),
                'table'      => array(
                'class' => true,
            ),
                'td'         => array(
                'class' => true,
            ),
                'th'         => array(
                'class' => true,
                'scope' => true,
            ),
                'tr'         => array(
                'class' => true,
            ),
                'tbody'      => array(
                'class' => true,
            ),
                'label'      => array(
                'for' => true,
            ),
                'div'        => array(
                'id'    => true,
                'class' => true,
                'title' => true,
                'style' => true,
            ),
                'textarea'   => array(
                'id'    => true,
                'class' => true,
                'name'  => true,
                'style' => true,
            ),
                'button'     => array(
                'type'  => true,
                'id'    => true,
                'class' => true,
                'name'  => true,
                'value' => true,
            ),
            );
            echo  wp_kses( wcblu_get_setting_html_for_free_user( $getpluginoptionarray ), $allow_html_args ) ;
            ?>
				</form>

			</div>
			
			</div>
			</div>
			<?php 
        }
        
        /**
         * function to return link for menu
         */
        function custom_banned_user_listing()
        {
            $url = admin_url() . 'edit.php?post_type=blocked_user';
            ?>
			<script>location.href = '<?php 
            esc_attr_e( esc_url( $url ), 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
            ?>';</script>
			<?php 
        }
    
    }
    
    /**
     * function return to welcom screen of plugin
     */
    public function welcome_screen_content_blacklist_users()
    {
        ?>
		<div class="wrap about-wrap">
			<h1 class="welcome_message_plugin"><?php 
        printf( esc_html__( 'Welcome To Woocommerce Blocker', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) );
        ?></h1>

			<div class="about-text woocommerce-about-text">
				<?php 
        $message = '';
        printf( esc_html__( '%s Prevent fake orders and Blacklist fraud customers plugin allows your WooCommerce store to refuse orders from specific user, based on blacklist rules.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ), esc_html( $message ), esc_html( $this->version ) );
        ?>
				<img class="version_logo_img"
				     src="<?php 
        esc_url( plugin_dir_url( __FILE__ ) . 'images/black_list.png' );
        ?>">
			</div>
			
			<?php 
        $setting_tabs_wc = apply_filters( 'woocommerce_black_list_user_tab', array(
            "about"         => "Overview",
            "other_plugins" => "Checkout our other plugins",
        ) );
        $current_tab_wc = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $current_tab_wc = ( isset( $current_tab_wc ) ? $current_tab_wc : 'general' );
        ?>
			<h2 id="woo-black-list-tab-wrapper" class="nav-tab-wrapper">
				<?php 
        if ( is_array( $setting_tabs_wc ) ) {
            foreach ( $setting_tabs_wc as $name => $label ) {
                echo  '<a  href="' . esc_url( home_url( 'wp-admin/index.php?page=woocommerce-blocker-prevent-fake-orders-and-blacklist-fraud-customers&tab=' . $name ) ) . '" class="nav-tab ' . (( $name === $current_tab_wc ? 'nav-tab-active' : '' )) . '">' . wp_kses_post( $label ) . '</a>' ;
            }
        }
        ?>
			</h2>
			<?php 
        if ( is_array( $setting_tabs_wc ) ) {
            foreach ( $setting_tabs_wc as $setting_tabkey_wc ) {
                switch ( $setting_tabkey_wc ) {
                    case $current_tab_wc:
                        do_action( 'woocommerce_blocker_prevent_fake_orders_and_blacklist_fraud_customers_' . $current_tab_wc );
                        break;
                }
            }
        }
        ?>
			<hr/>
			<div class="return-to-dashboard">
				<a href="<?php 
        echo  esc_url( home_url( '/wp-admin/admin.php?page=woocommerce_blacklist_users' ) ) ;
        ?>"><?php 
        esc_html_e( 'Go to Woocommerce Blocker - Prevent fake orders and Blacklist fraud customers Settings', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?></a>
			</div>
		</div>
		
		<?php 
    }
    
    /**
     * function return to messages of blocker selection
     */
    public function woocommerce_blocker_prevent_fake_orders_and_blacklist_fraud_customers_about()
    {
        ?>
		<div class="changelog">
			<div class="changelog about-integrations">
				<div class="wc-feature feature-section col three-col">
					<div>
						<div class="woocommerce_black_list_content">
							<h3><?php 
        esc_html_e( 'Plugin Overview', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?></h3>
							<p><?php 
        esc_html_e( 'This plugin can be used to refuse orders from specific users, based on customize blacklisting criteria.
You could specify a list of Email Addresses, IP address, State, Country, Zip-code and Domain names,  that will be blacklisted.
When a user will try to place an order or register using one of the blacklisted email, domain name, IP ect.. the checkout or account will be interrupted and the user will be notified of the reason why the operation was blocked.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?></p>
							<p><?php 
        esc_html_e( 'Additionally, an admin can get the detail report of genuine block user "who try to buy products more than one time" and contact them to resolve issue.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?></p>
							<p>
								<span><?php 
        esc_html_e( 'Key Features:', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?></span>
							</p>
							<ul class="bulets">
								<li><?php 
        esc_html_e( 'Block user based on Email, IP, State, Country, Zipcode, and Domain.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?></li>
								<li><?php 
        esc_html_e( 'Block user from web centralize data using API.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?></li>
								<li><?php 
        esc_html_e( 'Ability to review fraudulent checkouts OR registration attempts based on rule.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?></li>
								<li><?php 
        esc_html_e( 'Works in both checkout and registration pages.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?></li>
								<li><?php 
        esc_html_e( 'Display standard WooCommerce notices, error messages can be customized via back-end settings.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?></li>
								<li><?php 
        esc_html_e( 'Display blocked users report and admin can check how many time they tried to website.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?></li>
								<li><?php 
        esc_html_e( 'Work with WooCommerce 2.0+ and Word Press 3.9+', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?></li>
								<li><?php 
        esc_html_e( 'Easy to use interface to create and review blacklist rules.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?></li>
								<li><?php 
        esc_html_e( 'Localization compatible.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?></li>
							</ul>
						</div>

					</div>
				</div>
			</div>
		</div>
		
		<?php 
    }
    
    /**
     * function create dashboad widget.
     * view   in dashboard.
     *
     */
    function my_custom_dashboard_widgets()
    {
        wp_add_dashboard_widget( 'custom_help_widget', __( 'Blacklist Users Report', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ), 'custom_dashboard_help' );
        function custom_dashboard_help()
        {
            $attempt_value = 3;
            $html = '';
            $argsUserData = array(
                'post_type'      => 'blocked_user',
                'posts_per_page' => 5,
                'post_status'    => 'publish',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC',
                'post_parent'    => 0,
                'meta_query'     => array( array(
                'key'     => 'Attempt',
                'value'   => (int) $attempt_value,
                'type'    => 'numeric',
                'compare' => '>=',
            ) ),
            );
            $UserData = get_posts( $argsUserData );
            $html .= '<div class="main_custom_dashboard_visit_page blk_dashboard">';
            $html .= '<table border="0" cellpadding="5" cellspacing="10">';
            $html .= '<tr>';
            $html .= '<th class="email_1">' . __( 'Email id', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) . '</th>';
            $html .= '<th class="attempts_2">' . __( 'Attempts', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) . '</th>';
            $html .= '<th class="review_3">' . __( 'Review details', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) . '</th>';
            $html .= '</tr>';
            
            if ( '' !== $UserData && !empty($UserData) ) {
                if ( is_array( $UserData ) ) {
                    foreach ( $UserData as $values ) {
                        $attempt = get_post_meta( $values->ID, 'Attempt', true );
                        
                        if ( $attempt >= 3 ) {
                            $html .= '<tr>';
                            $html .= '<td class="email_1">' . $values->post_title . '</td>';
                            $html .= '<td class="attempts_2">' . $attempt . '</td>';
                            $html .= '<td class="review_3"><a href="' . get_edit_post_link( $values->ID ) . '" target="_blank">View details</a></td>';
                            $html .= '</tr>';
                        }
                    
                    }
                }
                $bloked_user_list = site_url( 'wp-admin/edit.php?post_type=blocked_user' );
                $html .= '<tr>';
                $html .= '<td><a href="' . $bloked_user_list . '" target="_blank">View all records</a></td>';
                $html .= '<tr>';
            } else {
                $html .= '<tr>';
                $html .= '<td>' . __( 'No Record Found', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) . '</td>';
                $html .= '</tr>';
            }
            
            $html .= '</table>';
            $html .= '</div>';
            echo  wp_kses_post( $html ) ;
        }
    
    }
    
    public function wcblu_admin_footer_review()
    {
        $url = '';
        $url = esc_url( 'https://wordpress.org/plugins/woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers/#reviews' );
        echo  sprintf( wp_kses( __( 'If you like <strong>Blocker – Prevent Fake Orders And Blacklist Fraud Customers for WooCommerce</strong> plugin, please leave us &#9733;&#9733;&#9733;&#9733;&#9733; ratings on <a href="%1$s" target="_blank">DotStore</a>.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ), array(
            'strong' => array(),
            'a'      => array(
            'href' => array(),
        ),
        ) ), esc_url( $url ) ) ;
    }
    
    /**
     * Migrate Data
     */
    public function wcblu_fetch_setting()
    {
        check_ajax_referer( 'wcblu-ajax-nonce', 'nonce' );
        $migrate_wcbluoption_array = array();
        global  $wpdb ;
        $get_option_wcblu_option = get_option( 'wcblu_option' );
        
        if ( is_serialized( $get_option_wcblu_option ) ) {
            $us_get_option_wcblu_option = maybe_unserialize( $get_option_wcblu_option );
        } else {
            $us_get_option_wcblu_option = $get_option_wcblu_option;
        }
        
        
        if ( !empty($us_get_option_wcblu_option) ) {
            /*Fetch email*/
            $final_email_merged_array = array();
            $email_qry_result = $wpdb->get_results( $wpdb->prepare( "SELECT email FROM {$wpdb->prefix}import_excel_tbl" ) );
            if ( !empty($email_qry_result) ) {
                foreach ( $email_qry_result as $key => $values ) {
                    $final_email_merged_array[] = $values->email;
                }
            }
            update_option( 'wcblu_block_email_tbl', $final_email_merged_array );
            /*Fetch state*/
            $final_state_merged_array = array();
            $state_qry_result = $wpdb->get_results( $wpdb->prepare( "SELECT state FROM {$wpdb->prefix}import_excel_state_tbl" ) );
            if ( !empty($state_qry_result) ) {
                foreach ( $state_qry_result as $key => $values ) {
                    $final_state_merged_array[] = $values->state;
                }
            }
            update_option( 'wcblu_block_state_tbl', $final_state_merged_array );
            /*Fetch zip*/
            $final_zipcode_merged_array = array();
            $zipcode_qry_result = $wpdb->get_results( $wpdb->prepare( "SELECT zipcode FROM {$wpdb->prefix}import_excel_zipcode_tbl" ) );
            if ( !empty($zipcode_qry_result) ) {
                foreach ( $zipcode_qry_result as $key => $values ) {
                    $final_zipcode_merged_array[] = $values->zipcode;
                }
            }
            update_option( 'wcblu_block_zip_tbl', $final_zipcode_merged_array );
            if ( array_key_exists( 'wcblu_register_type', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_register_type'] = $us_get_option_wcblu_option['wcblu_register_type'];
            }
            if ( array_key_exists( 'wcblu_place_order_type', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_place_order_type'] = $us_get_option_wcblu_option['wcblu_place_order_type'];
            }
            if ( array_key_exists( 'wcblu_address_type', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_address_type'] = $us_get_option_wcblu_option['wcblu_address_type'];
            }
            if ( array_key_exists( 'wcblu_block_ip', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_ip'] = $us_get_option_wcblu_option['wcblu_block_ip'];
            }
            if ( array_key_exists( 'wcblu_block_state', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_state'] = $us_get_option_wcblu_option['wcblu_block_state'];
            }
            if ( array_key_exists( 'wcblu_block_country', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_country'] = $us_get_option_wcblu_option['wcblu_block_country'];
            }
            if ( array_key_exists( 'wcblu_block_zip', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_zip'] = $us_get_option_wcblu_option['wcblu_block_zip'];
            }
            if ( array_key_exists( 'wcblu_block_email', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_email'] = $us_get_option_wcblu_option['wcblu_block_email'];
            }
            if ( array_key_exists( 'all_email_chk', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['all_email_chk'] = $us_get_option_wcblu_option['all_email_chk'];
            }
            if ( array_key_exists( 'wcblu_email_msg', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_email_msg'] = $us_get_option_wcblu_option['wcblu_email_msg'];
            }
            if ( array_key_exists( 'wcblu_ip_msg', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_ip_msg'] = $us_get_option_wcblu_option['wcblu_ip_msg'];
            }
            if ( array_key_exists( 'wcblu_state_msg', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_state_msg'] = $us_get_option_wcblu_option['wcblu_state_msg'];
            }
            if ( array_key_exists( 'wcblu_country_msg', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_country_msg'] = $us_get_option_wcblu_option['wcblu_country_msg'];
            }
            if ( array_key_exists( 'wcblu_zpcode_msg', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_zpcode_msg'] = $us_get_option_wcblu_option['wcblu_zpcode_msg'];
            }
            if ( array_key_exists( 'wcblu_block_domain', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_domain'] = $us_get_option_wcblu_option['wcblu_block_domain'];
            }
            if ( array_key_exists( 'wcblu_block_domain_ext', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_domain_ext'] = '';
            }
            if ( array_key_exists( 'wcblu_block_phone', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_phone'] = $us_get_option_wcblu_option['wcblu_block_phone'];
            }
            if ( array_key_exists( 'wcblu_block_first_name', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_first_name'] = $us_get_option_wcblu_option['wcblu_block_first_name'];
            }
            if ( array_key_exists( 'wcblu_block_last_name', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_last_name'] = $us_get_option_wcblu_option['wcblu_block_last_name'];
            }
            if ( array_key_exists( 'wcblu_block_user_agent', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_user_agent'] = $us_get_option_wcblu_option['wcblu_block_user_agent'];
            }
            if ( array_key_exists( 'wcblu_block_zone', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_zone'] = $us_get_option_wcblu_option['wcblu_block_zone'];
            }
            if ( array_key_exists( 'wcblu_block_userrole', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_userrole'] = $us_get_option_wcblu_option['wcblu_block_userrole'];
            }
            if ( array_key_exists( 'wcblu_domain_msg', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_domain_msg'] = $us_get_option_wcblu_option['wcblu_domain_msg'];
            }
            if ( array_key_exists( 'wcblu_domain_ext_msg', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_domain_ext_msg'] = $us_get_option_wcblu_option['wcblu_domain_ext_msg'];
            }
            if ( array_key_exists( 'wcblu_phone_msg', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_phone_msg'] = $us_get_option_wcblu_option['wcblu_phone_msg'];
            }
            if ( array_key_exists( 'wcblu_first_name_msg', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_first_name_msg'] = $us_get_option_wcblu_option['wcblu_first_name_msg'];
            }
            if ( array_key_exists( 'wcblu_last_name_msg', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_last_name_msg'] = $us_get_option_wcblu_option['wcblu_last_name_msg'];
            }
            if ( array_key_exists( 'wcblu_user_agent_msg', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_user_agent_msg'] = $us_get_option_wcblu_option['wcblu_user_agent_msg'];
            }
            if ( array_key_exists( 'wcblu_zone_msg', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_zone_msg'] = $us_get_option_wcblu_option['wcblu_zone_msg'];
            }
            if ( array_key_exists( 'wcblu_userrole_msg', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_userrole_msg'] = $us_get_option_wcblu_option['wcblu_userrole_msg'];
            }
            if ( array_key_exists( 'wcblu_enable_ext_bl', $us_get_option_wcblu_option ) ) {
                $migrate_wcbluoption_array['wcblu_enable_ext_bl'] = $us_get_option_wcblu_option['wcblu_enable_ext_bl'];
            }
        }
        
        $get_option_wcblu_lite_option = get_option( 'wcblu_lite_option' );
        
        if ( !empty($get_option_wcblu_lite_option) ) {
            
            if ( is_serialized( $get_option_wcblu_lite_option ) ) {
                $us_get_option_wcblu_lite_option = maybe_unserialize( $get_option_wcblu_lite_option );
            } else {
                $us_get_option_wcblu_lite_option = $get_option_wcblu_lite_option;
            }
            
            if ( array_key_exists( 'wcblu_lite_register_type', $us_get_option_wcblu_lite_option ) ) {
                $migrate_wcbluoption_array['wcblu_register_type'] = $us_get_option_wcblu_lite_option['wcblu_lite_register_type'];
            }
            if ( array_key_exists( 'wcblu_lite_place_order_type', $us_get_option_wcblu_lite_option ) ) {
                $migrate_wcbluoption_array['wcblu_place_order_type'] = $us_get_option_wcblu_lite_option['wcblu_lite_place_order_type'];
            }
            if ( array_key_exists( 'wcblu_lite_block_ip', $us_get_option_wcblu_lite_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_ip'] = $us_get_option_wcblu_lite_option['wcblu_lite_block_ip'];
            }
            if ( array_key_exists( 'wcblu_lite_block_state', $us_get_option_wcblu_lite_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_state'] = $us_get_option_wcblu_lite_option['wcblu_lite_block_state'];
            }
            if ( array_key_exists( 'wcblu_lite_block_country', $us_get_option_wcblu_lite_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_country'] = $us_get_option_wcblu_lite_option['wcblu_lite_block_country'];
            }
            if ( array_key_exists( 'wcblu_lite_block_zip', $us_get_option_wcblu_lite_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_zip'] = $us_get_option_wcblu_lite_option['wcblu_lite_block_zip'];
            }
            if ( array_key_exists( 'wcblu_lite_block_email', $us_get_option_wcblu_lite_option ) ) {
                $migrate_wcbluoption_array['wcblu_block_email'] = $us_get_option_wcblu_lite_option['wcblu_lite_block_email'];
            }
            if ( array_key_exists( 'wcblu_lite_email_msg', $us_get_option_wcblu_lite_option ) ) {
                $migrate_wcbluoption_array['wcblu_email_msg'] = $us_get_option_wcblu_lite_option['wcblu_lite_email_msg'];
            }
            if ( array_key_exists( 'wcblu_lite_ip_msg', $us_get_option_wcblu_lite_option ) ) {
                $migrate_wcbluoption_array['wcblu_ip_msg'] = $us_get_option_wcblu_lite_option['wcblu_lite_ip_msg'];
            }
            if ( array_key_exists( 'wcblu_lite_state_msg', $us_get_option_wcblu_lite_option ) ) {
                $migrate_wcbluoption_array['wcblu_state_msg'] = $us_get_option_wcblu_lite_option['wcblu_lite_state_msg'];
            }
            if ( array_key_exists( 'wcblu_lite_country_msg', $us_get_option_wcblu_lite_option ) ) {
                $migrate_wcbluoption_array['wcblu_country_msg'] = $us_get_option_wcblu_lite_option['wcblu_lite_country_msg'];
            }
            if ( array_key_exists( 'wcblu_lite_zpcode_msg', $us_get_option_wcblu_lite_option ) ) {
                $migrate_wcbluoption_array['wcblu_zpcode_msg'] = $us_get_option_wcblu_lite_option['wcblu_lite_zpcode_msg'];
            }
            $get_all_old_args = array(
                'post_type'      => 'blocked_user_lite',
                'order'          => 'DESC',
                'posts_per_page' => -1,
                'orderby'        => 'ID',
            );
            $get_all_old_query = new WP_Query( $get_all_old_args );
            $get_all_old = $get_all_old_query->get_posts();
            $get_all_old_count = $get_all_old_query->found_posts;
            if ( $get_all_old_count > 0 ) {
                foreach ( $get_all_old as $get_all_old_val ) {
                    $get_post_id = $get_all_old_val->ID;
                    $post_title = $get_all_old_val->post_title;
                    $post_author = $get_all_old_val->post_author;
                    $post_date = $get_all_old_val->post_date;
                    $post_date_gmt = $get_all_old_val->post_date_gmt;
                    $post_status = $get_all_old_val->post_status;
                    $post_name = $get_all_old_val->post_name;
                    $post_modified = $get_all_old_val->post_modified;
                    $post_modified_gmt = $get_all_old_val->post_modified_gmt;
                    $first_name = get_post_meta( $get_post_id, 'First Name', true );
                    $last_name = get_post_meta( $get_post_id, 'Last Name', true );
                    $city = get_post_meta( $get_post_id, 'City', true );
                    $country = get_post_meta( $get_post_id, 'Country', true );
                    $phone = get_post_meta( $get_post_id, 'Phone', true );
                    $company = get_post_meta( $get_post_id, 'Company', true );
                    $postcode = get_post_meta( $get_post_id, 'Postcode', true );
                    $address_1 = get_post_meta( $get_post_id, 'Address 1', true );
                    $address_2 = get_post_meta( $get_post_id, 'Address 2', true );
                    $state = get_post_meta( $get_post_id, 'State', true );
                    $country = get_post_meta( $get_post_id, 'Country', true );
                    $whereuserbanned = get_post_meta( $get_post_id, 'WhereUserBanned', true );
                    $post_data = array(
                        'post_title'        => $post_title,
                        'post_status'       => $post_status,
                        'post_author'       => $post_author,
                        'post_date'         => $post_date,
                        'post_date_gmt'     => $post_date_gmt,
                        'post_name'         => $post_name,
                        'post_modified'     => $post_modified,
                        'post_modified_gmt' => $post_modified_gmt,
                        'post_type'         => 'blocked_user',
                    );
                    $new_post_id = wp_insert_post( $post_data );
                    update_post_meta( $new_post_id, 'First Name', $first_name );
                    update_post_meta( $new_post_id, 'Last Name', $last_name );
                    update_post_meta( $new_post_id, 'City', $city );
                    update_post_meta( $new_post_id, 'Country', $country );
                    update_post_meta( $new_post_id, 'Phone', $phone );
                    update_post_meta( $new_post_id, 'Company', $company );
                    update_post_meta( $new_post_id, 'Postcode', $postcode );
                    update_post_meta( $new_post_id, 'Address 1', $address_1 );
                    update_post_meta( $new_post_id, 'Address 2', $address_2 );
                    update_post_meta( $new_post_id, 'State', $state );
                    update_post_meta( $new_post_id, 'Country', $country );
                    update_post_meta( $new_post_id, 'WhereUserBanned', $whereuserbanned );
                }
            }
        }
        
        $wje_migrate_wcbluoption_array = wp_json_encode( $migrate_wcbluoption_array );
        update_option( 'wcblu_option', $wje_migrate_wcbluoption_array );
        update_option( 'wcblu_migrate_option_data', 'true' );
        wp_die();
    }
    
    /**
     * Modal for contacting admin when products get out of stock.
     */
    function wcblu_admin__notify_modal()
    {
        ob_start();
        ?>
		<div class="notification_popup">
			<span class="notification_close"></span>
			<div class="notification_icon"><i class="fa fa-shield" aria-hidden="true"></i></div>
			<div class="notification_message">
				<h3 class="title"></h3>
				<p class="message"></p>
			</div>
		</div>
		<?php 
        echo  wp_kses_post( ob_get_clean() ) ;
    }
    
    /**
     * @param $actions
     * @param $post
     *
     * @return mixed
     * Function to return delete action of blocker plugin.
     */
    function wcblu_permanent_delete_action( $actions, $post )
    {
        if ( 'blocked_user' === $post->post_type ) {
            $actions['was-delete-permanent'] = '<a href="?post_type=blocked_user&was_permanent_delete=' . $post->ID . '" class="was-permanent-user">' . esc_html__( 'Delete Permanently', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) . '</a>';
        }
        return $actions;
    }
    
    /**
     * function to return process of delete blocked user
     */
    function wcblu_permanent_delete_process()
    {
        $unblock_user_id = filter_input( INPUT_GET, 'was_permanent_delete', FILTER_SANITIZE_NUMBER_INT );
        if ( !empty($unblock_user_id) ) {
            wcblu_permanent_delete_data( $unblock_user_id );
        }
    }
    
    /**
     * Add custom css for dotstore icon in admin area
     *
     * @since  1.0.0
     *
     */
    public function mmqw_dot_store_icon_css()
    {
        echo  '<style>
	    .toplevel_page_dots_store .dashicons-marker::after{content:"";border:3px solid;position:absolute;top:14px;left:15px;border-radius:50%;opacity: 0.6;}
	    li.toplevel_page_dots_store:hover .dashicons-marker::after,li.toplevel_page_dots_store.current .dashicons-marker::after{opacity: 1;}
	  	</style>' ;
    }
    
    /**
     * Get dynamic promotional bar of plugin
     *
     * @param   String  $plugin_slug  slug of the plugin added in the site option
     * @since    3.9.3
     * 
     * @return  null
     */
    public function wb_get_promotional_bar( $plugin_slug = '' )
    {
        $promotional_bar_upi_url = WB_PROMOTIONAL_BANNER_API_URL . 'wp-json/dpb-promotional-banner/v2/dpb-promotional-banner?' . wp_rand();
        $promotional_banner_request = wp_remote_get( $promotional_bar_upi_url );
        //phpcs:ignore
        
        if ( empty($promotional_banner_request->errors) ) {
            $promotional_banner_request_body = $promotional_banner_request['body'];
            $promotional_banner_request_body = json_decode( $promotional_banner_request_body, true );
            echo  '<div class="dynamicbar_wrapper">' ;
            if ( !empty($promotional_banner_request_body) && is_array( $promotional_banner_request_body ) ) {
                foreach ( $promotional_banner_request_body as $promotional_banner_request_body_data ) {
                    $promotional_banner_id = $promotional_banner_request_body_data['promotional_banner_id'];
                    $promotional_banner_cookie = $promotional_banner_request_body_data['promotional_banner_cookie'];
                    $promotional_banner_image = $promotional_banner_request_body_data['promotional_banner_image'];
                    $promotional_banner_description = $promotional_banner_request_body_data['promotional_banner_description'];
                    $promotional_banner_button_group = $promotional_banner_request_body_data['promotional_banner_button_group'];
                    $dpb_schedule_campaign_type = $promotional_banner_request_body_data['dpb_schedule_campaign_type'];
                    $promotional_banner_target_audience = $promotional_banner_request_body_data['promotional_banner_target_audience'];
                    
                    if ( !empty($promotional_banner_target_audience) ) {
                        $plugin_keys = array();
                        
                        if ( is_array( $promotional_banner_target_audience ) ) {
                            foreach ( $promotional_banner_target_audience as $list ) {
                                $plugin_keys[] = $list['value'];
                            }
                        } else {
                            $plugin_keys[] = $promotional_banner_target_audience['value'];
                        }
                        
                        $display_banner_flag = false;
                        if ( in_array( 'all_customers', $plugin_keys, true ) || in_array( $plugin_slug, $plugin_keys, true ) ) {
                            $display_banner_flag = true;
                        }
                    }
                    
                    if ( true === $display_banner_flag ) {
                        
                        if ( 'default' === $dpb_schedule_campaign_type ) {
                            $banner_cookie_show = filter_input( INPUT_COOKIE, 'banner_show_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            $banner_cookie_visible_once = filter_input( INPUT_COOKIE, 'banner_show_once_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            $flag = false;
                            
                            if ( empty($banner_cookie_show) && empty($banner_cookie_visible_once) ) {
                                setcookie( 'banner_show_' . $promotional_banner_cookie, 'yes', time() + 86400 * 7 );
                                //phpcs:ignore
                                setcookie( 'banner_show_once_' . $promotional_banner_cookie, 'yes' );
                                //phpcs:ignore
                                $flag = true;
                            }
                            
                            $banner_cookie_show = filter_input( INPUT_COOKIE, 'banner_show_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            
                            if ( !empty($banner_cookie_show) || true === $flag ) {
                                $banner_cookie = filter_input( INPUT_COOKIE, 'banner_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                                $banner_cookie = ( isset( $banner_cookie ) ? $banner_cookie : '' );
                                
                                if ( empty($banner_cookie) && 'yes' !== $banner_cookie ) {
                                    ?>
                            	<div class="dpb-popup <?php 
                                    echo  ( isset( $promotional_banner_cookie ) ? esc_html( $promotional_banner_cookie ) : 'default-banner' ) ;
                                    ?>">
                                    <?php 
                                    
                                    if ( !empty($promotional_banner_image) ) {
                                        ?>
                                        <img src="<?php 
                                        echo  esc_url( $promotional_banner_image ) ;
                                        ?>"/>
                                        <?php 
                                    }
                                    
                                    ?>
                                    <div class="dpb-popup-meta">
                                        <p>
                                            <?php 
                                    echo  wp_kses_post( str_replace( array( '<p>', '</p>' ), '', $promotional_banner_description ) ) ;
                                    if ( !empty($promotional_banner_button_group) ) {
                                        foreach ( $promotional_banner_button_group as $promotional_banner_button_group_data ) {
                                            ?>
                                                    <a href="<?php 
                                            echo  esc_url( $promotional_banner_button_group_data['promotional_banner_button_link'] ) ;
                                            ?>" target="_blank"><?php 
                                            echo  esc_html( $promotional_banner_button_group_data['promotional_banner_button_text'] ) ;
                                            ?></a>
                                                    <?php 
                                        }
                                    }
                                    ?>
                                    	</p>
                                    </div>
                                    <a href="javascript:void(0);" data-bar-id="<?php 
                                    echo  esc_attr( $promotional_banner_id ) ;
                                    ?>" data-popup-name="<?php 
                                    echo  ( isset( $promotional_banner_cookie ) ? esc_attr( $promotional_banner_cookie ) : 'default-banner' ) ;
                                    ?>" class="dpbpop-close"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10"><path id="Icon_material-close" data-name="Icon material-close" d="M17.5,8.507,16.493,7.5,12.5,11.493,8.507,7.5,7.5,8.507,11.493,12.5,7.5,16.493,8.507,17.5,12.5,13.507,16.493,17.5,17.5,16.493,13.507,12.5Z" transform="translate(-7.5 -7.5)" fill="#acacac"/></svg></a>
                                </div>
                                <?php 
                                }
                            
                            }
                        
                        } else {
                            $banner_cookie_show = filter_input( INPUT_COOKIE, 'banner_show_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            $banner_cookie_visible_once = filter_input( INPUT_COOKIE, 'banner_show_once_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            $flag = false;
                            
                            if ( empty($banner_cookie_show) && empty($banner_cookie_visible_once) ) {
                                setcookie( 'banner_show_' . $promotional_banner_cookie, 'yes' );
                                //phpcs:ignore
                                setcookie( 'banner_show_once_' . $promotional_banner_cookie, 'yes' );
                                //phpcs:ignore
                                $flag = true;
                            }
                            
                            $banner_cookie_show = filter_input( INPUT_COOKIE, 'banner_show_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            
                            if ( !empty($banner_cookie_show) || true === $flag ) {
                                $banner_cookie = filter_input( INPUT_COOKIE, 'banner_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                                $banner_cookie = ( isset( $banner_cookie ) ? $banner_cookie : '' );
                                
                                if ( empty($banner_cookie) && 'yes' !== $banner_cookie ) {
                                    ?>
                    			<div class="dpb-popup <?php 
                                    echo  ( isset( $promotional_banner_cookie ) ? esc_html( $promotional_banner_cookie ) : 'default-banner' ) ;
                                    ?>">
                                    <?php 
                                    
                                    if ( !empty($promotional_banner_image) ) {
                                        ?>
                                            <img src="<?php 
                                        echo  esc_url( $promotional_banner_image ) ;
                                        ?>"/>
                                        <?php 
                                    }
                                    
                                    ?>
                                    <div class="dpb-popup-meta">
                                        <p>
                                            <?php 
                                    echo  wp_kses_post( str_replace( array( '<p>', '</p>' ), '', $promotional_banner_description ) ) ;
                                    if ( !empty($promotional_banner_button_group) ) {
                                        foreach ( $promotional_banner_button_group as $promotional_banner_button_group_data ) {
                                            ?>
                                                    <a href="<?php 
                                            echo  esc_url( $promotional_banner_button_group_data['promotional_banner_button_link'] ) ;
                                            ?>" target="_blank"><?php 
                                            echo  esc_html( $promotional_banner_button_group_data['promotional_banner_button_text'] ) ;
                                            ?></a>
                                                    <?php 
                                        }
                                    }
                                    ?>
                                        </p>
                                    </div>
                                    <a href="javascript:void(0);" data-bar-id="<?php 
                                    echo  esc_attr( $promotional_banner_id ) ;
                                    ?>" data-popup-name="<?php 
                                    echo  ( isset( $promotional_banner_cookie ) ? esc_html( $promotional_banner_cookie ) : 'default-banner' ) ;
                                    ?>" class="dpbpop-close"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10"><path id="Icon_material-close" data-name="Icon material-close" d="M17.5,8.507,16.493,7.5,12.5,11.493,8.507,7.5,7.5,8.507,11.493,12.5,7.5,16.493,8.507,17.5,12.5,13.507,16.493,17.5,17.5,16.493,13.507,12.5Z" transform="translate(-7.5 -7.5)" fill="#acacac"/></svg></a>
                                </div>
                                <?php 
                                }
                            
                            }
                        
                        }
                    
                    }
                }
            }
            echo  '</div>' ;
        }
    
    }
    
    /**
     * Get and save plugin setup wizard data
     * 
     * @since    3.9.3
     * 
     */
    public function wcblu_plugin_setup_wizard_submit()
    {
        check_ajax_referer( 'wcblu-ajax-nonce', 'nonce' );
        $survey_list = filter_input( INPUT_GET, 'survey_list', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if ( !empty($survey_list) && 'Select One' !== $survey_list ) {
            update_option( 'wcblu_where_hear_about_us', $survey_list );
        }
        wp_die();
    }
    
    /**
     * Send setup wizard data to sendinblue
     * 
     * @since    3.9.3
     * 
     */
    public function wcblu_send_wizard_data_after_plugin_activation()
    {
        $send_wizard_data = filter_input( INPUT_GET, 'send-wizard-data', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if ( isset( $send_wizard_data ) && !empty($send_wizard_data) ) {
            
            if ( !get_option( 'wcblu_data_submited_in_sendiblue' ) ) {
                $wcblu_where_hear = get_option( 'wcblu_where_hear_about_us' );
                $get_user = wbpfoabfc_fs()->get_user();
                $data_insert_array = array();
                if ( isset( $get_user ) && !empty($get_user) ) {
                    $data_insert_array = array(
                        'user_email'              => $get_user->email,
                        'ACQUISITION_SURVEY_LIST' => $wcblu_where_hear,
                    );
                }
                $feedback_api_url = WCBLU_STORE_URL . '/wp-json/dotstore-sendinblue-data/v2/dotstore-sendinblue-data?' . wp_rand();
                $query_url = $feedback_api_url . '&' . http_build_query( $data_insert_array );
                
                if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
                    $response = vip_safe_wp_remote_get(
                        $query_url,
                        3,
                        1,
                        20
                    );
                } else {
                    $response = wp_remote_get( $query_url );
                }
                
                
                if ( !is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
                    update_option( 'wcblu_data_submited_in_sendiblue', '1' );
                    delete_option( 'wcblu_where_hear_about_us' );
                }
            
            }
        
        }
    }

}