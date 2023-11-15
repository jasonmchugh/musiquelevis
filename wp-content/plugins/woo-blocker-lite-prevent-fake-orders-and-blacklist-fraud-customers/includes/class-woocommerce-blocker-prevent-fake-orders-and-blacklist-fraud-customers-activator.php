<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Fired during plugin activation
 *
 * @link       http://www.multidots.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers
 * @subpackage Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers
 * @subpackage Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers/includes
 * @author     multidots <info@multidots.in>
 */
class Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        set_transient('_woo_blacklist_users_welcome_screen', true, 30);

        if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')), true) && !is_plugin_active_for_network('woocommerce/woocommerce.php')) {
            wp_die("<strong>WooCommerce Blacklist users</strong> Plugin requires <strong>WooCommerce</strong> <a href='" . esc_url(get_admin_url(null, 'plugins.php')) . "'>Plugins page</a>.");
        }
    }
    /**
     * save_default_settings
     */
    public static function save_default_settings() {

        $default_sett_opt = get_option( 'wcblu_general_option', true );
        $default_sett_rul = get_option( 'wcblu_rules_option', true );
        
        if( !isset( $default_sett_rul ) && empty( $default_sett_rul ) ) {
            $wcbluruleoption_array['wcbfc_first_order_status']           = '1';
            $wcbluruleoption_array['wcbfc_first_order_weight']           = '5';
            $wcbluruleoption_array['wcbfc_first_order_custom']           = '1';
            $wcbluruleoption_array['wcbfc_first_order_custom_weight']    = '20';
            $wcbluruleoption_array['wcbfc_bca_order']                    = '1';
            $wcbluruleoption_array['wcbfc_bca_order_weight']             = '20';
            $wcbluruleoption_array['wcbfc_proxy_order']                  = '1';
            $wcbluruleoption_array['wcbfc_proxy_order_weight']           = '50';
            $wcbluruleoption_array['wcbfc_international_order']          = '1';
            $wcbluruleoption_array['wcbfc_international_order_weight']   = '10';
            $wcbluruleoption_array['wcbfc_suspecius_email']              = '1';
            $wcbluruleoption_array['wcbfc_suspecius_email_list']         = '';
            $wcbluruleoption_array['wcbfc_suspecious_email_weight']      = '5';
            $wcbluruleoption_array['wcbfc_unsafe_countries']             = '1';
            $wcbluruleoption_array['wcblu_define_unsafe_countries_list'] = '';
            $wcbluruleoption_array['wcbfc_unsafe_countries_weight']      = '25';
    
            $wcbluruleopt_array = wp_json_encode( $wcbluruleoption_array );
            update_option( 'wcblu_rules_option', $wcbluruleopt_array );
        }

        if( !isset( $default_sett_opt ) && empty( $default_sett_opt ) ) {
            $wcblugeneraloption_array['wcbfc_fraud_check_before_pay']       = '0';
            $wcblugeneraloption_array['wcblu_pre_payment_message']          = '';
            $wcblugeneraloption_array['wcbfc_update_order_status_on_score'] = '0';
            $wcblugeneraloption_array['wcbfc_settings_low_risk_threshold']  = '25';
            $wcblugeneraloption_array['wcbfc_settings_high_risk_threshold'] = '75';
            $wcblugeneraloption_array['wcbfc_email_notification']           = '0';
            $wcblugeneraloption_array['wcbfc_settings_cancel_score']        = '90';
            $wcblugeneraloption_array['wcbfc_settings_hold_score']          = '70';
            $wcblugeneraloption_array['wcbfc_settings_email_score']         = '50';
            $wcblugeneraloption_array['wcblu_settings_custom_email']        = '';
            $wcblugeneraloption_array['wcblu_settings_whitelist']           = '';
            
            $wcblugeneralopt_array = wp_json_encode( $wcblugeneraloption_array );
            update_option( 'wcblu_general_option', $wcblugeneralopt_array );
        }
    }
}
