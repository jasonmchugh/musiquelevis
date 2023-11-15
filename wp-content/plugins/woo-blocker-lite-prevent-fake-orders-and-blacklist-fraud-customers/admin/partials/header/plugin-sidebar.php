<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="dotstore_plugin_sidebar">

<?php 
$review_url = '';
$plugin_at = '';
$changelog_url = '';
$review_url = esc_url( 'https://wordpress.org/plugins/woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers/#reviews' );
$plugin_at = 'WP.org';
$changelog_url = esc_url( 'https://wordpress.org/plugins/woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers/#developers' );
?>
    <?php 
?>
            <div class="dotstore-sidebar-section dotstore-upgrade-to-pro">
                <div class="dotstore-important-link-heading">
                    <span class="heading-text"><?php 
esc_html_e( 'Upgrade to Fraud Prevention Pro', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></span>
                </div>
                <div class="dotstore-important-link-content">
                    <ul class="dotstore-pro-list">
                        <li><?php 
esc_html_e( 'Flexibility to set rules to block users that place fraud orders', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></li>
                        <li><?php 
esc_html_e( 'Prevent fake orders to save time identifying genuine orders', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></li>
                        <li><?php 
esc_html_e( 'Block/blacklist users based on State/ZIP code/IP address', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></li>
                        <li><?php 
esc_html_e( 'Reduce losses resulting from unauthorized transactions', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></li>
                        <li><?php 
esc_html_e( 'Build a strong and profitable ecommerce reputation', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></li>
                        <li><?php 
esc_html_e( 'Focus on genuine customers that bring increased revenue', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></li>
                        <li><?php 
esc_html_e( 'Blacklist fraudulent customers to prevent false order placement', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></li>
                    </ul>
                    <div class="dotstore-pro-button">
                        <a class="button" target="_blank" href="<?php 
echo  esc_url( wbpfoabfc_fs()->get_upgrade_url() ) ;
?>"><?php 
esc_html_e( 'Get Premium Now »', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></a>
                    </div>
                </div>
            </div>
            <?php 
?>
    
    <div class="dotstore-sidebar-section">
        <div class="content_box">
            <h3><?php 
esc_html_e( 'Like This Plugin?', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></h3>
            <div class="et-star-rating">
                <input type="radio" id="5-stars" name="rating" value="5">
                <label for="5-stars" class="star"></label>
                <input type="radio" id="4-stars" name="rating" value="4">
                <label for="4-stars" class="star"></label>
                <input type="radio" id="3-stars" name="rating" value="3">
                <label for="3-stars" class="star"></label>
                <input type="radio" id="2-stars" name="rating" value="2">
                <label for="2-stars" class="star"></label>
                <input type="radio" id="1-star" name="rating" value="1">
                <label for="1-star" class="star"></label>
                <input type="hidden" id="wcblu-review-url" value="<?php 
echo  esc_url( $review_url ) ;
?>">
            </div>
            <p><?php 
esc_html_e( 'Your Review is very important to us as it helps us to grow more.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></p>
        </div>
    </div>
    <?php 
?>
    <div class="dotstore-sidebar-section">
        <div class="dotstore-important-link-heading">
            <span class="dashicons dashicons-star-filled"></span>
            <span class="heading-text"><?php 
esc_html_e( 'Suggest A Feature', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></span>
        </div>
        <div class="dotstore-important-link-content">
            <p><?php 
esc_html_e( 'Let us know how we can improve the plugin experience.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></p>
            <p><?php 
esc_html_e( 'Do you have any feedback & feature requests?', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></p>
            <a target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/suggest-a-feature' ) ;
?>"><?php 
esc_html_e( 'Submit Request »', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></a>
        </div>
    </div>

    <div class="dotstore-sidebar-section">
        <div class="dotstore-important-link-heading">
            <span class="dashicons dashicons-editor-kitchensink"></span>
            <span class="heading-text"><?php 
esc_html_e( 'Changelog', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></span>
        </div>
        <div class="dotstore-important-link-content">
            <p><?php 
esc_html_e( 'We improvise our products on a regular basis to deliver the best results to customer satisfaction.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></p>
            <a target="_blank" href="<?php 
echo  esc_url( $changelog_url ) ;
?>"><?php 
esc_html_e( 'Visit Here »', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></a>
        </div>
    </div>
    <!-- html for popular plugin !-->
    <div class="dotstore-important-link dotstore-sidebar-section">
        <div class="dotstore-important-link-heading">
            <span class="dashicons dashicons-plugins-checked"></span>
            <span class="heading-text">Our Popular Plugins</span>
        </div>
        <div class="video-detail important-link">
        <ul>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/Advanced-Flat-Rate-Shipping-Method.png' ) ;
?>" alt="<?php 
esc_attr_e( 'Advanced Flat Rate Shipping Method', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/flat-rate-shipping-plugin-for-woocommerce/" ) ;
?>">
						<?php 
esc_html_e( 'Advanced Flat Rate Shipping Method', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>
                    </a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/Extra-Fees-Plugin-for-WooCommerce.png' ) ;
?>" alt="<?php 
esc_attr_e( 'Extra Fees Plugin for WooCommerce', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/product/woocommerce-extra-fees-plugin/" ) ;
?>">
						<?php 
esc_html_e( 'Extra Fees Plugin for WooCommerce', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>
                    </a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/Advance-Menu-Manager-For-WordPress.png' ) ;
?>" alt="<?php 
esc_attr_e( 'Advance Menu Manager For WordPress', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/advance-menu-manager-wordpress/" ) ;
?>">
						<?php 
esc_html_e( 'Advance Menu Manager For WordPress', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>
                    </a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/Enhanced-Ecommerce-Google-Analytics-For-WooCommerce.png' ) ;
?>" alt="<?php 
esc_attr_e( 'Enhanced Ecommerce Google Analytics for WooCommerce', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/woocommerce-enhanced-ecommerce-analytics-integration-with-conversion-tracking" ) ;
?>">
						<?php 
esc_html_e( 'Enhanced Ecommerce Google Analytics for WooCommerce', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>
                    </a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/WooCommerce-Conditional-Discount-Rules-For-Checkout.png' ) ;
?>" alt="<?php 
esc_attr_e( 'Conditional Discount Rules For WooCommerce Checkout', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/woocommerce-conditional-discount-rules-for-checkout/" ) ;
?>">
                        <?php 
esc_html_e( 'Conditional Discount Rules For WooCommerce Checkout', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>
                    </a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/popular-plugins/wcbm-logo.png' ) ;
?>" alt="<?php 
esc_attr_e( 'WooCommerce Category Banner Management', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( "https://www.thedotstore.com/product/woocommerce-category-banner-management/" ) ;
?>">
                        <?php 
esc_html_e( 'WooCommerce Category Banner Management', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?>
                    </a>
                </li>
                </br>
            </ul>
        </div>
        <div class="view-button">
            <a class="button button-primary button-large" target="_blank"
               href="<?php 
echo  esc_url( 'https://www.thedotstore.com/plugins/' ) ;
?>"><?php 
esc_html_e( 'VIEW ALL', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></a>
        </div>
    </div>
    <!-- html end for popular plugin !-->
    <div class="dotstore-sidebar-section">
        <div class="dotstore-important-link-heading">
            <span class="dashicons dashicons-sos"></span>
            <span class="heading-text"><?php 
esc_html_e( 'Five Star Support', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></span>
        </div>
        <div class="dotstore-important-link-content">
            <p><?php 
esc_html_e( 'Got a question? Get in touch with theDotstore developers. We are happy to help! ', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></p>
            <a target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/support/' ) ;
?>"><?php 
esc_html_e( 'Submit a Ticket »', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
?></a>
        </div>
    </div>
</div>
</div>
</body>
</html>
