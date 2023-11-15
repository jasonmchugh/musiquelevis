<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once plugin_dir_path( __FILE__ ) . 'header/plugin-header.php';
?>

<?php 
switch ( $current_tab ) {
    case 'wblp-get-started':
        ?>
                    <div class="wcblu-main-table res-cl">
                        <div class="heading_div">
                            <h2><?php 
        esc_html_e( 'Thanks For Installing Fraud Prevention For Woocommerce - Prevent fake orders and Blacklist fraud customers', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?></h2>
                        </div>
                        <table class="table-outer">
                            <tbody>
                                <tr>
                                    <td class="fr-2">
                                        <?php 
        ?>
                                        <p class="block gettingstarted"><strong><?php 
        esc_html_e( 'Getting Started', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?> </strong></p>
                                        <p class="block textgetting">
                                            <?php 
        esc_html_e( 'Prevent fake orders and Blacklist fraud customers allows your WooCommerce store to refuse orders from specific user, based on blacklist rules ', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?>
                                        </p>
                                        <p class="block textgetting">
                                            <?php 
        esc_html_e( 'This plugin can be used to refuse orders from specific users, based on customize blacklisting criteria. ', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?>
                                        </p>

                                        <p class="block textgetting">
                                            <?php 
        esc_html_e( 'You can specify that user should be blocked on registration or placing the order.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?>
                                        </p>
                                        <span class="gettingstarted">
                                            <img src="<?php 
        echo  esc_url( WB_PLUGIN_URL . 'admin/images/Getting_Started_01.PNG' ) ;
        ?>">
                                        </span>
                                        <p class="block textgetting">
                                            <?php 
        esc_html_e( 'You can specify a list of Email Addresses, IP address, State, Shipping zone or Zip-code, that will be blacklisted. When a user will try to place an order or register using one of the blacklisted email, IP, state, zip or his shipping location falls under the blocked shipping zone - the checkout or account will be interrupted and the user will be notified of the reason why the operation was blocked.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?>
                                        </p>
                                        <span class="gettingstarted">
                                            <img src="<?php 
        echo  esc_url( WB_PLUGIN_URL . 'admin/images/Getting_Started_02.PNG' ) ;
        ?>">
                                        </span>
                                        <p class="block textgetting">
                                            <?php 
        esc_html_e( 'You can customize different messages which will be displayed to blocker user.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?>
                                        </p>
                                        <span class="gettingstarted">
                                            <img src="<?php 
        echo  esc_url( WB_PLUGIN_URL . 'admin/images/Getting_Started_03.PNG' ) ;
        ?>">
                                        </span>
                                        <p class="block textgetting">
                                            <?php 
        esc_html_e( 'Whenever a user is blocked on website, it will be added to banned user list. You can view Blocked user list as well.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
        ?>
                                        </p>
                                        <span class="gettingstarted">
                                            <img src="<?php 
        echo  esc_url( WB_PLUGIN_URL . 'admin/images/Getting_Started_04.PNG' ) ;
        ?>">
                                        </span>

                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                <?php 
        break;
    case 'wcblu-import-export-setting':
        if ( wbpfoabfc_fs()->is__premium_only() && wbpfoabfc_fs()->can_use_premium_code() ) {
            require_once dirname( __FILE__ ) . '/wcblu-import-export-setting.php';
        }
        break;
    case 'wblp-information':
        require_once dirname( __FILE__ ) . '/wblp-information-page.php';
        break;
}