<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$plugin_name = 'Fraud Prevention';
$plugin_version = WB_PLUGIN_VERSION;
global  $wbpfoabfc_fs ;
$get_account_url_free_premium = ( wbpfoabfc_fs()->is__premium_only() && wbpfoabfc_fs()->can_use_premium_code() ? $wbpfoabfc_fs->get_account_url() : $wbpfoabfc_fs->get_upgrade_url() );
$plugin_slug = '';
$version_label = 'Free';
$plugin_slug = 'basic_woo_fraud';
$wb_admin_object = new Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Admin( '', '' );
?>

<div id="dotsstoremain">
	<div class="all-pad dots-settings-inner-main">
    <?php 
$wb_admin_object->wb_get_promotional_bar( $plugin_slug );
?>
		<header class="dots-header">
			
		<div class="dots-plugin-details">
                <div class="dots-header-left">
                    <div class="dots-logo-main">
                        <img src="<?php 
echo  esc_url( WB_PLUGIN_URL . 'admin/images/thedotstore-images/WooCommerce-Blocker-Prevent-Fake-Orders.png' ) ;
?>">
                    </div>
                    <div class="plugin-name">
                        <div class="title"><?php 
esc_html_e( $plugin_name, 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></div>
                    </div>
                    <span class="version-label">
                        <span><?php 
esc_html_e( $version_label, 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></span>
                    </span>
                    <span class="version-number"><?php 
echo  esc_html( $plugin_version ) ;
?></span>
                </div>
                <div class="dots-header-right">
                        <div class="button-dots">
                            <a target="_blank" href="<?php 
echo  esc_url( 'http://www.thedotstore.com/support/' ) ;
?>">
                                <?php 
esc_html_e( 'Support', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>
                            </a>
                        </div>

                        <div class="button-dots">
                            <a target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/feature-requests/' ) ;
?>">
                                <?php 
esc_html_e( 'Suggest', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>
                            </a>
                        </div>

                        <div class="button-dots <?php 
echo  ( wbpfoabfc_fs()->is__premium_only() && wbpfoabfc_fs()->can_use_premium_code() ? '' : 'last-link-button' ) ;
?>">
                            <a target="_blank" href="<?php 
echo  esc_url( 'https://docs.thedotstore.com/category/149-premium-plugin-settings' ) ;
?>">
                                <?php 
echo  esc_html_e( 'Help', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ;
?>
                            </a>
                        </div>

                        <?php 
?>
                            <div class="button-dots">
                                <a target="_blank" href="<?php 
echo  esc_url( $get_account_url_free_premium ) ;
?>"  class="dots-upgrade-btn">
                                    <?php 
esc_html_e( 'Upgrade', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>
                                </a>
                            </div>
                        <?php 
?>
                </div>
            </div>
			<?php 
global  $pagenow ;
$bloker_data_list = '';
$fee_getting_started = '';
$wcblu_post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$current_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$wcblu_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
if ( isset( $wcblu_post_type ) && 'blocked_user' === $wcblu_post_type && $pagenow === 'edit.php' || $pagenow === 'post.php' || $pagenow === 'post-new.php' ) {
    $bloker_data_list = "active";
}
$fee_list = ( isset( $wcblu_page ) && 'woocommerce_blacklist_users' === $wcblu_page ? 'active' : '' );
$gs_list = ( isset( $wcblu_page ) && 'wcblu-general-settings' === $wcblu_page ? 'active' : '' );
$rules = ( isset( $wcblu_page ) && 'wcblu-auto-rules' === $wcblu_page ? 'active' : '' );
if ( !empty($wcblu_page) && 'wblp-get-started' === $wcblu_page ) {
    $fee_getting_started = 'active';
}
$wcblu_import_export_setting = ( isset( $current_tab ) && 'wcblu-import-export-setting' === $current_tab ? 'active' : '' );
$wcblu_information = ( isset( $current_tab ) && 'wblp-information' === $current_tab ? 'active' : '' );
$wcblu_about = ( isset( $current_tab ) && 'wblp-get-started' === $current_tab ? 'active' : '' );
$wcblu_settings_menu = ( isset( $wcblu_page ) && ('wcblu-import-export-setting' === $wcblu_page || 'wblp-get-started' === $wcblu_page || 'wblp-information' === $wcblu_page || !(wbpfoabfc_fs()->is__premium_only() && wbpfoabfc_fs()->can_use_premium_code()) && 'woocommerce_blacklist_users-account' === $wcblu_page) ? 'active' : '' );
$wcblu_free_dashboard = ( isset( $wcblu_page ) && 'wcblu-upgrade-dashboard' === $wcblu_page ? 'active' : '' );
$wcblu_display_submenu = ( !empty($wcblu_settings_menu) && 'active' === $wcblu_settings_menu ? 'display:inline-block' : 'display:none' );
$wcblu_account_page = ( isset( $wcblu_page ) && 'woocommerce_blacklist_users-account' === $wcblu_page ? 'active' : '' );
?>
			<div class="dots-menu-main">
				<nav>
					<ul>
                        <?php 
?>
                                <li>
                                    <a class="dotstore_plugin <?php 
echo  esc_attr( $wcblu_free_dashboard ) ;
?>" href="<?php 
echo  esc_url( add_query_arg( array(
    'page' => 'wcblu-upgrade-dashboard',
), admin_url( 'admin.php' ) ) ) ;
?>"><?php 
esc_html_e( 'Dashboard', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></a>
                                </li>
                            <?php 
?>
                        <li>
							<a class="dotstore_plugin <?php 
esc_attr_e( $fee_list, 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>" href="<?php 
echo  esc_url( home_url( '/wp-admin/admin.php?page=woocommerce_blacklist_users' ) ) ;
?>"><?php 
esc_html_e( 'Blacklist Settings', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></a>
						</li>
						<li>
							<a class="dotstore_plugin <?php 
esc_attr_e( $bloker_data_list, 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>" href="<?php 
echo  esc_url( site_url( 'wp-admin/edit.php?post_type=blocked_user' ) ) ;
?>"><?php 
esc_html_e( 'Blocked User List', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></a>
						</li>
                        
						
                        <?php 
$wcblu_settings_page_url = '';
$wcblu_settings_page_url = add_query_arg( array(
    'page' => 'wblp-get-started&tab=wblp-get-started',
), admin_url( 'admin.php' ) );
?>
						<li>
                            <a class="dotstore_plugin <?php 
echo  esc_attr( $wcblu_settings_menu ) ;
?>" href="<?php 
echo  esc_url( $wcblu_settings_page_url ) ;
?>"><?php 
esc_html_e( 'Settings', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></a>
                        </li>
                        <?php 

if ( wbpfoabfc_fs()->is__premium_only() && wbpfoabfc_fs()->can_use_premium_code() ) {
    ?>
                            <li>
                                <a class="dotstore_plugin <?php 
    echo  esc_attr( $wcblu_account_page ) ;
    ?>" href="<?php 
    echo  esc_url( wbpfoabfc_fs()->get_account_url() ) ;
    ?>"><?php 
    esc_html_e( 'License', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
    ?></a>
                            </li>
                            <?php 
}

?>
					</ul>
				</nav>
			</div>
		</header>
        <div class="dots-settings-inner-main">
            <div class="dots-settings-left-side">
                <div class="dotstore-submenu-items" style="<?php 
echo  esc_attr( $wcblu_display_submenu ) ;
?>">
                    <ul>
                    <?php 
?>
                        <li><a class="<?php 
echo  esc_attr( $wcblu_about ) ;
?>" href="<?php 
echo  esc_url( add_query_arg( array(
    'page' => 'wblp-get-started&tab=wblp-get-started',
), admin_url( 'admin.php' ) ) ) ;
?>"><?php 
esc_html_e( 'About', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></a></li>
                        <li><a class="<?php 
echo  esc_attr( $wcblu_information ) ;
?>" href="<?php 
echo  esc_url( add_query_arg( array(
    'page' => 'wblp-get-started&tab=wblp-information',
), admin_url( 'admin.php' ) ) ) ;
?>"><?php 
esc_html_e( 'Quick info', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></a></li>
                        
                        <?php 

if ( !(wbpfoabfc_fs()->is__premium_only() && wbpfoabfc_fs()->can_use_premium_code()) ) {
    ?>
                            <li>
                                <a class="<?php 
    echo  esc_attr( $wcblu_account_page ) ;
    ?>" href="<?php 
    echo  esc_url( wbpfoabfc_fs()->get_account_url() ) ;
    ?>"><?php 
    esc_html_e( 'Account', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
    ?></a>
                            </li>
                            <?php 
}

?>
                        
                        <li><a href="<?php 
echo  esc_url( 'https://www.thedotstore.com/plugins/' ) ;
?>" target="_blank"><?php 
esc_html_e( 'Shop Plugins', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></a></li>
                    </ul>
                </div>
