<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once plugin_dir_path( __FILE__ ) . 'header/plugin-header.php';
$plugin_name = 'Fraud Prevention Plugin for WooCommerce';
$plugin_version = WB_PLUGIN_VERSION;
$check_plugin_free_premium = ( wbpfoabfc_fs()->is__premium_only() && wbpfoabfc_fs()->can_use_premium_code() ? 'Premium Version' : 'Free Version' );
?>

    <div class="wcblu-main-table res-cl">
        <div class="heading_div">
            <h2><?php 
esc_html_e( 'Quick info', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></h2>
        </div>
        <table class="table-outer">
            <tbody>
            <tr>
                <td class="fr-1"><?php 
esc_html_e( 'Product Type', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></td>
                <td class="fr-2"><?php 
esc_html_e( 'WooCommerce Plugin', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></td>
            </tr>
            <tr>
                <td class="fr-1"><?php 
esc_html_e( 'Product Name', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></td>
                <td class="fr-2"><?php 
esc_html_e( $plugin_name, 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></td>
            </tr>
            <tr>
                <td class="fr-1"><?php 
esc_html_e( 'Installed Version', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></td>
                <td class="fr-2"><?php 
esc_html_e( $check_plugin_free_premium, 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?> <?php 
esc_html_e( $plugin_version, 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></td>
            </tr>
            <tr>
                <td class="fr-1"><?php 
esc_html_e( 'License & Terms of use', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></td>
                <td class="fr-2"><a target="_blank"  href="https://www.thedotstore.com/terms-and-conditions/">
                        <?php 
esc_html_e( 'Click here', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></a>
                    <?php 
esc_html_e( 'to view license and terms of use.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>
                </td>
            </tr>
            <tr>
                <td class="fr-1"><?php 
esc_html_e( 'Help & Support', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></td>
                <td class="fr-2 wb-information">
                    <ul>
                        <li><a target="_blank" href="<?php 
echo  esc_url( site_url( 'wp-admin/admin.php?page=wblp-get-started' ) ) ;
?>"><?php 
esc_html_e( 'Quick Start', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></a></li>
                        <li><a target="_blank" href="<?php 
echo  esc_url( 'https://docs.thedotstore.com/category/149-premium-plugin-settings' ) ;
?>"><?php 
esc_html_e( 'Guide Documentation', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></a></li>
                        <li><a target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/support/' ) ;
?>"><?php 
esc_html_e( 'Support Forum', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></a></li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td class="fr-1"><?php 
esc_html_e( 'Localization', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></td>
                <td class="fr-2"><?php 
esc_html_e( 'English', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>, <?php 
esc_html_e( 'Spanish', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></td>
            </tr>

            </tbody>
        </table>
    </div>

    </div>
    </div>
    </div>
    </div>