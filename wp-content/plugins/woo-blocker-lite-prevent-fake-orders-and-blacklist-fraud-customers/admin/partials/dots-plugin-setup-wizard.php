<?php
/**
 * Handles plugin setup wizard
 * 
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 * @since   3.9.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wbpfoabfc_fs;
$is_premium = $wbpfoabfc_fs->is_premium();

$require_license = filter_input(INPUT_GET, 'require_license', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$activate_free_plugin = !empty($require_license) && 'false' === $require_license ? 'yes' : 'no';
?>
<div class="ds-plugin-setup-wizard-main">
	<div class="wizard-tab-content">
		<div class="tab-panel" id="step1">
			<div class="ds-wizard-wrap">
				<div class="ds-wizard-content">
					<h3 class="cta-title"><?php echo esc_html__( 'Welcome! 🤗', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></h3>
					<img class="ds-wizard-logo" src="<?php echo esc_url( WCBLU_PRO_PLUGIN_URL . 'admin/images/thedotstore-images/WooCommerce-Blocker-Prevent-Fake-Orders.png' ); ?>"/>
					<p><?php echo esc_html__( 'Prevent fraudulent transactions and fake orders on your online store, save time, and guarantee the identification of genuine orders!', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></p>
				</div>
				<div class="ds-wizard-next-step">
					<button type="button" class="btn btn-primary next-step"><?php echo esc_html__( 'Continue', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?><svg xmlns="http://www.w3.org/2000/svg" width="20" height="11.877" viewBox="0 0 20 11.877"><g id="Group_481" data-name="Group 481" transform="translate(0 -17.112)"><path id="Path_10268" data-name="Path 10268" d="M19.062,230.9H.937a.937.937,0,0,1,0-1.875H19.062a.937.937,0,0,1,0,1.875Z" transform="translate(0 -206.909)" fill="#fff"/><path id="Path_10269" data-name="Path 10269" d="M224.637,155.643a.938.938,0,0,1-.663-1.6l4.337-4.337-4.337-4.337a.938.938,0,0,1,1.326-1.326l5,5a.938.938,0,0,1,0,1.326l-5,5A.93.93,0,0,1,224.637,155.643Z" transform="translate(-210.575 -126.655)" fill="#fff"/></g></svg></button>
				</div>
			</div>
		</div>
		<div class="tab-panel" id="step2">
			<div class="ds-wizard-wrap">
				<div class="ds-wizard-content">
					<?php 
					if ( $is_premium && 'no' === $activate_free_plugin ) {
						?>
						<h2 class="cta-title">❤️ <?php echo esc_html__( 'Help us build a better "Fraud Prevention" Plugin!', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></h2>
						<p><?php echo esc_html__( 'Get improved features and faster fixes by sharing non-sensitive data via usage-tracking. This will help us make the plugin more compatible with your site. No personal data is tracked or stored.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></p>
						<div>
							<label class="ds-wizard-checkbox"><input type="checkbox" name="" value="" class="ds_count_me_in"><?php echo esc_html__( 'Yes, count me in!', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
						</div>
						<?php
					} else {
						?>
						<h2 class="cta-title"><?php echo esc_html__( 'Never miss an important update', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?> 🔔</h2>
						<p><?php echo esc_html__( 'Opt-in to get email notifications for security & feature updates and to share some important WooCommerce updates. This will help us make the plugin more compatible with your version and better at doing what you need it to. No personal data is tracked or stored.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></p>
						<div>
							<label class="ds-wizard-checkbox"><input type="checkbox" name="" value="" class="ds_count_me_in"><?php echo esc_html__( 'Yes, count me in!', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
						</div>
						<?php
					}
					?>
				</div>
				<div class="ds-wizard-next-step">
					<button type="button" class="btn btn-primary next-step"><?php echo esc_html__( 'Continue', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?><svg xmlns="http://www.w3.org/2000/svg" width="20" height="11.877" viewBox="0 0 20 11.877"><g id="Group_481" data-name="Group 481" transform="translate(0 -17.112)"><path id="Path_10268" data-name="Path 10268" d="M19.062,230.9H.937a.937.937,0,0,1,0-1.875H19.062a.937.937,0,0,1,0,1.875Z" transform="translate(0 -206.909)" fill="#fff"/><path id="Path_10269" data-name="Path 10269" d="M224.637,155.643a.938.938,0,0,1-.663-1.6l4.337-4.337-4.337-4.337a.938.938,0,0,1,1.326-1.326l5,5a.938.938,0,0,1,0,1.326l-5,5A.93.93,0,0,1,224.637,155.643Z" transform="translate(-210.575 -126.655)" fill="#fff"/></g></svg></button>
				</div>
			</div>
		</div>
		<div class="tab-panel" id="step3">
			<div class="ds-wizard-wrap">
				<div class="ds-wizard-content">
					<h2 class="cta-title"><?php echo esc_html__( 'Quick Tour', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?> 🗺️</h2>
					<div class="ds-wizard-quick-tour">
						<iframe src="<?php echo esc_url('https://www.youtube.com/embed/WLHMaHAd70c'); ?>" title="<?php esc_attr_e( 'Quick Tour', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div>
				</div>
				<div class="ds-wizard-next-step">
					<button type="button" class="btn btn-primary next-step"><?php echo esc_html__( 'Continue', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?><svg xmlns="http://www.w3.org/2000/svg" width="20" height="11.877" viewBox="0 0 20 11.877"><g id="Group_481" data-name="Group 481" transform="translate(0 -17.112)"><path id="Path_10268" data-name="Path 10268" d="M19.062,230.9H.937a.937.937,0,0,1,0-1.875H19.062a.937.937,0,0,1,0,1.875Z" transform="translate(0 -206.909)" fill="#fff"/><path id="Path_10269" data-name="Path 10269" d="M224.637,155.643a.938.938,0,0,1-.663-1.6l4.337-4.337-4.337-4.337a.938.938,0,0,1,1.326-1.326l5,5a.938.938,0,0,1,0,1.326l-5,5A.93.93,0,0,1,224.637,155.643Z" transform="translate(-210.575 -126.655)" fill="#fff"/></g></svg></button>
				</div>
			</div>
		</div>
		<div class="tab-panel" id="step4">
			<div class="ds-wizard-wrap">
				<div class="ds-wizard-content">
					<h2 class="cta-title"><?php echo esc_html__( 'You are all set, almost!', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?> 😀</h2>
					<?php 
					if ( $is_premium && 'no' === $activate_free_plugin ) {
						?>
						<div class="ds-wizard-where-hear">
							<p><?php esc_html_e('Would you mind telling how did you hear about us?', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers') ?></p>
							<select name="ds-wizard-where-hear-select" class="ds-wizard-where-hear-select">
								<option><?php echo esc_html__( 'Select One', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
								<option><?php echo esc_html__( 'Social Media', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
								<option><?php echo esc_html__( 'Search Engine', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
								<option><?php echo esc_html__( 'LearnWoo', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
								<option><?php echo esc_html__( 'WPLift', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
								<option><?php echo esc_html__( 'WPBeginner', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
								<option><?php echo esc_html__( 'Do the Woo', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
								<option><?php echo esc_html__( 'WP Mayor', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
								<option><?php echo esc_html__( 'Astra', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
								<option><?php echo esc_html__( 'WPExplorer', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
								<option><?php echo esc_html__( 'Medium', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
								<option><?php echo esc_html__( 'Others', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
							</select>
						</div>
						<?php
					} 
					?>
					<div class="ds-wizard-social">
						<p><?php echo esc_html__( 'Be our friend on', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></p>
						<ul class="wizard-social-list">
							<li><a target="_blank" href="<?php echo esc_url( 'https://www.facebook.com/thedotstore16/' ); ?>"><span class="dashicons dashicons-facebook-alt"></span></a></li>
							<li><a target="_blank" href="<?php echo esc_url( 'https://twitter.com/thedotstore' ); ?>"><span class="dashicons dashicons-twitter"></span></a></li>
							<li><a target="_blank" href="<?php echo esc_url( 'https://www.youtube.com/@DotStore16' ); ?>"><span class="dashicons dashicons-youtube"></span></a></li>
							<li><a target="_blank" href="<?php echo esc_url( 'https://www.linkedin.com/company/dotstore16' ); ?>"><span class="dashicons dashicons-linkedin"></span></a></li>
						</ul>
					</div>
				</div>
				<div class="ds-wizard-next-step">
					<?php 
					if ( $is_premium && 'no' === $activate_free_plugin ) {
						?>
						<button type="button" class="btn btn-primary next-step"><?php echo esc_html__( 'Continue', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?><svg xmlns="http://www.w3.org/2000/svg" width="20" height="11.877" viewBox="0 0 20 11.877"><g id="Group_481" data-name="Group 481" transform="translate(0 -17.112)"><path id="Path_10268" data-name="Path 10268" d="M19.062,230.9H.937a.937.937,0,0,1,0-1.875H19.062a.937.937,0,0,1,0,1.875Z" transform="translate(0 -206.909)" fill="#fff"/><path id="Path_10269" data-name="Path 10269" d="M224.637,155.643a.938.938,0,0,1-.663-1.6l4.337-4.337-4.337-4.337a.938.938,0,0,1,1.326-1.326l5,5a.938.938,0,0,1,0,1.326l-5,5A.93.93,0,0,1,224.637,155.643Z" transform="translate(-210.575 -126.655)" fill="#fff"/></g></svg></button>
						<?php
					} else {
						?>
						<button type="button" class="btn btn-primary next-step ds-wizard-complete"><?php echo esc_html__( 'Done', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></button>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	