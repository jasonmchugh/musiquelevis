<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.thedotstore.com/
 * @since             1.0.0
 * @package           Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers
 *
 * @wordpress-plugin
 * Plugin Name: Fraud Prevention For Woocommerce
 * Plugin URI:        https://www.thedotstore.com/
 * Description:       Prevent fake orders and Blacklist fraud customers allows your WooCommerce store to refuse orders from specific user, based on blacklist rules.
 * Version:           2.1.6
 * Author:            theDotstore
 * Author URI:        https://www.thedotstore.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers
 * Domain Path:       /languages
 * 
 * WC tested up to:      8.0.1
 * WC requires at least: 3.0
 * Requires PHP:         5.6
 * Requires at least:    5.0
 * 
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}

if ( function_exists( 'wbpfoabfc_fs' ) ) {
    wbpfoabfc_fs()->set_basename( false, __FILE__ );
    return;
}


if ( !function_exists( 'wbpfoabfc_fs' ) ) {
    // Create a helper function for easy SDK access.
    function wbpfoabfc_fs()
    {
        global  $wbpfoabfc_fs ;
        
        if ( !isset( $wbpfoabfc_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $wbpfoabfc_fs = fs_dynamic_init( array(
                'id'               => '3493',
                'slug'             => 'woocommerce-blocker',
                'type'             => 'plugin',
                'public_key'       => 'pk_00eede06ec781d063c23f65779beb',
                'is_premium'       => false,
                'has_addons'       => false,
                'has_paid_plans'   => true,
                'is_org_compliant' => false,
                'trial'            => array(
                'days'               => 14,
                'is_require_payment' => true,
            ),
                'menu'             => array(
                'slug'       => 'woocommerce_blacklist_users',
                'first-path' => 'admin.php?page=woocommerce_blacklist_users&send-wizard-data=true',
                'contact'    => false,
                'support'    => false,
            ),
                'is_live'          => true,
            ) );
        }
        
        return $wbpfoabfc_fs;
    }
    
    // Init Freemius.
    wbpfoabfc_fs();
    // Signal that SDK was initiated.
    do_action( 'wbpfoabfc_fs_loaded' );
    wbpfoabfc_fs()->get_upgrade_url();
}

if ( !defined( 'WB_PLUGIN_URL' ) ) {
    define( 'WB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( !defined( 'WB_PLUGIN_VERSION' ) ) {
    define( 'WB_PLUGIN_VERSION', '2.1.6' );
}
if ( !defined( 'WCBLU_PRO_PLUGIN_URL' ) ) {
    define( 'WCBLU_PRO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( !defined( 'WCBLU_STORE_URL' ) ) {
    define( 'WCBLU_STORE_URL', 'https://www.thedotstore.com' );
}
/** Add the API URL to fetch the promotional banners */
if ( !defined( 'WB_PROMOTIONAL_BANNER_API_URL' ) ) {
    define( 'WB_PROMOTIONAL_BANNER_API_URL', 'https://www.thedotstore.com/' );
}
if ( !defined( 'WB_STORE_URL' ) ) {
    define( 'WB_STORE_URL', 'https://www.thedotstore.com' );
}
/**
 * Start plugin setup wizard before license activation screen
 *
 * @since    3.9.3
 */

if ( !function_exists( 'WCBLU_load_plugin_setup_wizard_connect_before' ) ) {
    function WCBLU_load_plugin_setup_wizard_connect_before()
    {
        require_once plugin_dir_path( __FILE__ ) . 'admin/partials/dots-plugin-setup-wizard.php';
        ?>
        <div class="tab-panel" id="step5">
            <div class="ds-wizard-wrap">
                <div class="ds-wizard-content">
                    <h2 class="cta-title"><?php 
        echo  esc_html__( 'Activate Plugin', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ;
        ?></h2>
                </div>
        <?php 
    }
    
    wbpfoabfc_fs()->add_action( 'connect/before', 'WCBLU_load_plugin_setup_wizard_connect_before' );
}

/**
 * End plugin setup wizard after license activation screen
 *
 * @since    3.9.3
 */

if ( !function_exists( 'WCBLU_load_plugin_setup_wizard_connect_after' ) ) {
    function WCBLU_load_plugin_setup_wizard_connect_after()
    {
        ?>
        </div>
        </div>
        </div>
        </div>
        <?php 
    }
    
    wbpfoabfc_fs()->add_action( 'connect/after', 'WCBLU_load_plugin_setup_wizard_connect_after' );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocommerce-blocker-prevent-fake-orders-and-blacklist-fraud-customers-activator.php
 */
if ( !function_exists( 'activate_woocommerce_blocker_prevent_fake_orders_and_blacklist_fraud_customers' ) ) {
    function activate_woocommerce_blocker_prevent_fake_orders_and_blacklist_fraud_customers()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-blocker-prevent-fake-orders-and-blacklist-fraud-customers-activator.php';
        Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Activator::activate();
    }

}
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocommerce-blocker-prevent-fake-orders-and-blacklist-fraud-customers-deactivator.php
 */
if ( !function_exists( 'deactivate_woocommerce_blocker_prevent_fake_orders_and_blacklist_fraud_customers' ) ) {
    function deactivate_woocommerce_blocker_prevent_fake_orders_and_blacklist_fraud_customers()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-blocker-prevent-fake-orders-and-blacklist-fraud-customers-deactivator.php';
        Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Deactivator::deactivate();
    }

}
register_activation_hook( __FILE__, 'activate_woocommerce_blocker_prevent_fake_orders_and_blacklist_fraud_customers' );
register_deactivation_hook( __FILE__, 'deactivate_woocommerce_blocker_prevent_fake_orders_and_blacklist_fraud_customers' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-blocker-prevent-fake-orders-and-blacklist-fraud-customers.php';
require plugin_dir_path( __FILE__ ) . 'includes/excelwriter.inc.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
if ( !function_exists( 'run_woocommerce_blocker_prevent_fake_orders_and_blacklist_fraud_customers' ) ) {
    function run_woocommerce_blocker_prevent_fake_orders_and_blacklist_fraud_customers()
    {
        $plugin = new Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers();
        $plugin->run();
    }

}
add_action( 'plugins_loaded', 'wcblu_plugin_init' );
if ( !function_exists( 'wcblu_plugin_init' ) ) {
    function wcblu_plugin_init()
    {
        $active_plugins = get_option( 'active_plugins', array() );
        
        if ( is_multisite() ) {
            $network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
            $active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
            $active_plugins = array_unique( $active_plugins );
        }
        
        
        if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', $active_plugins ), true ) ) {
            add_action( 'admin_notices', 'wcblu_plugin_admin_notice' );
            add_action( 'admin_init', 'wcblu_deactivate_plugin' );
            //Deactivate the plugin as it should not be activated.
        } else {
            run_woocommerce_blocker_prevent_fake_orders_and_blacklist_fraud_customers();
        }
    
    }

}
/**
 * Show admin notice in case of WooCommerce plguin is missing
 */
if ( !function_exists( 'wcblu_plugin_admin_notice' ) ) {
    function wcblu_plugin_admin_notice()
    {
        $wcblu_plugin = 'Fraud Prevention For Woocommerce';
        $wc_plugin = 'WooCommerce';
        ?>
        <div class="error">
            <p>
                <?php 
        echo  sprintf( esc_html__( '%1$s requires %2$s to be installed & activated!', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ), '<strong>' . esc_html( $wcblu_plugin ) . '</strong>', '<a href="' . esc_url( 'https://wordpress.org/plugins/woocommerce/' ) . '" target="_blank"><strong>' . esc_html( $wc_plugin ) . '</strong></a>' ) ;
        ?>
            </p>
        </div><?php 
    }

}
/**
 * Deactivate the plugin.
 */
if ( !function_exists( 'wcblu_deactivate_plugin' ) ) {
    function wcblu_deactivate_plugin()
    {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        $activate_plugin_unset = filter_input( INPUT_GET, 'activate', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        unset( $activate_plugin_unset );
    }

}
/**
 * Hide freemius account tab
 *
 * @since    3.9.3
 */

if ( !function_exists( 'wb_hide_account_tab' ) ) {
    function wb_hide_account_tab()
    {
        return true;
    }
    
    wbpfoabfc_fs()->add_filter( 'hide_account_tabs', 'wb_hide_account_tab' );
}

/**
 * Include plugin header on freemius account page
 *
 * @since    1.0.0
 */

if ( !function_exists( 'wb_load_plugin_header_after_account' ) ) {
    function wb_load_plugin_header_after_account()
    {
        require_once plugin_dir_path( __FILE__ ) . 'admin/partials/header/plugin-header.php';
    }
    
    wbpfoabfc_fs()->add_action( 'after_account_details', 'wb_load_plugin_header_after_account' );
}

/**
 * Hide billing and payments details from freemius account page
 *
 * @since    3.9.3
 */

if ( !function_exists( 'wb_hide_billing_and_payments_info' ) ) {
    function wb_hide_billing_and_payments_info()
    {
        return true;
    }
    
    wbpfoabfc_fs()->add_action( 'hide_billing_and_payments_info', 'wb_hide_billing_and_payments_info' );
}

/**
 * Hide powerd by popup from freemius account page
 *
 * @since    3.9.3
 */

if ( !function_exists( 'wb_hide_freemius_powered_by' ) ) {
    function wb_hide_freemius_powered_by()
    {
        return true;
    }
    
    wbpfoabfc_fs()->add_action( 'hide_freemius_powered_by', 'wb_hide_freemius_powered_by' );
}

/**
 * 
 * HPOS Compatiblity 
 * 
 */
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );