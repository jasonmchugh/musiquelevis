<?php
/**
 * Handles free plugin user dashboard
 * 
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 * @since   3.9.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once( plugin_dir_path( __FILE__ ) . 'header/plugin-header.php' );
global $wbpfoabfc_fs;
?>
	<div class="wcblu-section-left">
		<div class="dotstore-upgrade-dashboard">
			<div class="premium-benefits-section">
				<h2><?php esc_html_e( 'Go Premium to Increase Profitability', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></h2>
				<p><?php esc_html_e( 'Three Benefits for Upgrading to Premium', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></p>
				<div class="premium-features-boxes">
					<div class="feature-box">
						<span><?php esc_html_e('01', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></span>
						<h3><?php esc_html_e('Prevent fake orders', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></h3>
						<p><?php esc_html_e('Utilize effective measures to prevent fake orders and safeguard against fraudulent transaction attempts for enhanced security.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
					</div>
					<div class="feature-box">
						<span><?php esc_html_e('02', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></span>
						<h3><?php esc_html_e('Block fraud users', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></h3>
						<p><?php esc_html_e('You can block fraud order users and restrict them from placing orders against threats or malicious activities.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
					</div>
					<div class="feature-box">
						<span><?php esc_html_e('03', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></span>
						<h3><?php esc_html_e('Fraud automation', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></h3>
						<p><?php esc_html_e('Deploy automated systems for fraud order prevention against unauthorized transactions based on weight rules.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
					</div>
				</div>
			</div>
			<div class="premium-benefits-section unlock-premium-features">
				<p><span><?php esc_html_e( 'Unlock Premium Features', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></span></p>
				<div class="premium-features-boxes">
					<div class="feature-box">
						<h3><?php esc_html_e('Block suspicious users with multiple activities.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></h3>
						<span><i class="fa fa-list-alt"></i></span>
						<p><?php esc_html_e('Enables blocking of suspicious users during registration and checkout, with customizable criteria including domains, names, email, IP addresses, etc.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
						<div class="feature-explanation-popup-main">
							<div class="feature-explanation-popup-outer">
								<div class="feature-explanation-popup-inner">
									<div class="feature-explanation-popup">
										<span class="dashicons dashicons-no-alt popup-close-btn" title="<?php esc_attr_e('Close', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>"></span>
										<div class="popup-body-content">
											<div class="feature-image">
												<img src="<?php echo esc_url(WB_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-one-img.png'); ?>" alt="<?php echo esc_attr('WooCommerce Dynamic Extra Fees', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>">
											</div>
											<div class="feature-content">
												<p><?php esc_html_e('The feature helps build customer trust and confidence in the platform\'s security and reliability by effectively preventing fake orders.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
												<ul>
													<li><?php esc_html_e('It will show the risk icon on the order listing page.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></li>
													<li><?php esc_html_e('As per the screenshot, the blocked user will get a blocked message and fraud score with an applied rule, as shown in the widget.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></li>
												</ul>
											</div>
										</div>
									</div>		
								</div>
							</div>
						</div>
					</div>
					<div class="feature-box">
						<h3><?php esc_html_e('Blacklist Bulk Fraud User Email', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></h3>
						<span><i class="fa fa-envelope-o"></i></span>
						<p><?php esc_html_e('Upload a list of blacklisted email addresses in bulk, helping you proactively block and prevent access for users associated with those emails.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
						<div class="feature-explanation-popup-main">
							<div class="feature-explanation-popup-outer">
								<div class="feature-explanation-popup-inner">
									<div class="feature-explanation-popup">
										<span class="dashicons dashicons-no-alt popup-close-btn" title="<?php esc_attr_e('Close', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>"></span>
										<div class="popup-body-content">
											<div class="feature-image">
												<img src="<?php echo esc_url(WB_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-two-img.png'); ?>" alt="<?php echo esc_attr('Location-Based Conditional Fees', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>">
											</div>
											<div class="feature-content">
												<p><?php esc_html_e('Upload Bulk email address in blacklisted email Field using the upload blacklist email feature.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
												<ul>
													<li><?php esc_html_e('Use an xlsx format file to add multiple email addresses.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></li>
													<li><?php esc_html_e('A demo file is also available for reference.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></li>
												</ul>
											</div>
										</div>
									</div>		
								</div>
							</div>
						</div>
					</div>
					<div class="feature-box">
						<h3><?php esc_html_e('Automated Fraud Blacklisting', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></h3>
						<span><i class="fa fa-magic"></i></span>
						<p><?php esc_html_e('Blocks suspicious users from accessing your store based on their assigned fraud scores, providing an effective way to prevent fraudulent activities.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
						<div class="feature-explanation-popup-main">
							<div class="feature-explanation-popup-outer">
								<div class="feature-explanation-popup-inner">
									<div class="feature-explanation-popup">
										<span class="dashicons dashicons-no-alt popup-close-btn" title="<?php esc_attr_e('Close', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>"></span>
										<div class="popup-body-content">
											<div class="feature-image">
												<img src="<?php echo esc_url(WB_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-three-img.png'); ?>" alt="<?php echo esc_attr('User Role-Based Checkout Fees', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>">
											</div>
											<div class="feature-content">
												<p><?php esc_html_e('Blocks users based on their fraud scores, preventing potentially suspicious or malicious individuals from accessing the platform.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
												<ul>
													<li><?php esc_html_e('As shown screenshot, enable auto fraud, set rules and fraud weight, and enable automatic blacklisting.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></li>
													<li><?php esc_html_e('It will automatically block users based on fraud score.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></li>
												</ul>
											</div>
										</div>
									</div>		
								</div>
							</div>
						</div>
					</div>
					<div class="feature-box">
						<h3><?php esc_html_e('Configurable Risk Threshold Levels', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></h3>
						<span><i class="fa fa-cogs"></i></span>
						<p><?php esc_html_e('Customize and set risk threshold levels, allowing them to adjust the sensitivity of fraud detection according to their specific needs and preferences.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
						<div class="feature-explanation-popup-main">
							<div class="feature-explanation-popup-outer">
								<div class="feature-explanation-popup-inner">
									<div class="feature-explanation-popup">
										<span class="dashicons dashicons-no-alt popup-close-btn" title="<?php esc_attr_e('Close', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>"></span>
										<div class="popup-body-content">
											<div class="feature-image">
												<img src="<?php echo esc_url(WB_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-four-img.png'); ?>" alt="<?php echo esc_attr('Percentage Fees Based On Product Quantity', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>">
											</div>
											<div class="feature-content">
												<p><?php echo sprintf( esc_html__('Allows users to adjust and fine-tune the sensitivity of fraud detection.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'), 2 ); ?></p>
												<ul>
													<li><?php esc_html_e('The gray icon is whitelisted orders, the yellow icons are the medium risks, and the red icon is the high-risk detections. ', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></li>
												</ul>
											</div>
										</div>
									</div>		
								</div>
							</div>
						</div>
					</div>
					<div class="feature-box">
						<h3><?php esc_html_e('Customizable Fraud Rules & Scoring', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></h3>
						<span><i class="fa fa-tasks"></i></span>
						<p><?php esc_html_e('Own fraud prevention rules and scoring criteria, enabling a personalized approach to detecting and blocking suspicious activities in the store. ', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
						<div class="feature-explanation-popup-main">
							<div class="feature-explanation-popup-outer">
								<div class="feature-explanation-popup-inner">
									<div class="feature-explanation-popup">
										<span class="dashicons dashicons-no-alt popup-close-btn" title="<?php esc_attr_e('Close', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>"></span>
										<div class="popup-body-content">
											<div class="feature-image">
												<img src="<?php echo esc_url(WB_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-eight-img.png'); ?>" alt="<?php echo esc_attr('Tiered Extra Fees', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>">
											</div>
											<div class="feature-content">
												<p><?php esc_html_e('Modify fraud prevention rules and scoring criteria, tailoring the system to match specific security needs.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
												<ul>
													<li><?php esc_html_e('Set your own rule weight, as shown in the screenshot. ', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></li>
													<li><?php esc_html_e('So if the first three rule applies, add a 10 + 20 + 20 = 50 fraud score.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></li>
												</ul>
											</div>
										</div>
									</div>		
								</div>
							</div>
						</div>
					</div>
					<div class="feature-box">
						<h3><?php esc_html_e('Easy to Whitelist Exclusive Emails', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></h3>
						<span><i class="fa fa-shield"></i></span>
						<p><?php esc_html_e('Allows users to create a select list of approved email addresses, ensuring that only whitelisted users can place orders on the store.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
						<div class="feature-explanation-popup-main">
							<div class="feature-explanation-popup-outer">
								<div class="feature-explanation-popup-inner">
									<div class="feature-explanation-popup">
										<span class="dashicons dashicons-no-alt popup-close-btn" title="<?php esc_attr_e('Close', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>"></span>
										<div class="popup-body-content">
											<div class="feature-image">
												<img src="<?php echo esc_url(WB_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-nine-img.png'); ?>" alt="<?php echo esc_attr('Revenue Dashboard', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>">
											</div>
											<div class="feature-content">
												<p><?php esc_html_e('Enable trusted users to place orders without unnecessary restrictions or additional verifications. ', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
												<ul>
													<li><?php esc_html_e('Add multiple email addresses like the ones below:', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></li>
													<li><?php esc_html_e('abc@gmail.com.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></li>
													<li><?php esc_html_e('def@gmail.com', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></li>
													<li><?php esc_html_e('Add one email address per line.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></li>
												</ul>
											</div>
										</div>
									</div>		
								</div>
							</div>
						</div>
					</div>
					<div class="feature-box">
						<h3><?php esc_html_e('External Temporary Email Blacklist', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></h3>
						<span><i class="fa fa-external-link"></i></span>
						<p><?php esc_html_e('Incorporate an external list of temporary email domains, preventing users with such email addresses from engaging with the store and reducing the risk of potential fraud or abuse.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
						<div class="feature-explanation-popup-main">
							<div class="feature-explanation-popup-outer">
								<div class="feature-explanation-popup-inner">
									<div class="feature-explanation-popup">
										<span class="dashicons dashicons-no-alt popup-close-btn" title="<?php esc_attr_e('Close', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>"></span>
										<div class="popup-body-content">
											<div class="feature-image">
												<img src="<?php echo esc_url(WB_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-seven-img.png'); ?>" alt="<?php echo esc_attr('Advanced Extra Fees Rules', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>">
											</div>
											<div class="feature-content">
												<p><?php esc_html_e('The feature strengthens platform security by automatically blocking access from predefined temporary email providers.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
												<ul>
													<li><?php esc_html_e('Enable the external blocking as shown in the screenshot, and all emails mentioned in the git repo will be blocked.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></li>
												</ul>
											</div>
										</div>
									</div>		
								</div>
							</div>
						</div>
					</div>
					<div class="feature-box">
						<h3><?php esc_html_e('Personalize Your Block Messages', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></h3>
						<span><i class="fa fa-comment"></i></span>
						<p><?php esc_html_e('Users can customize the messages displayed to blocked individuals, providing clear explanations for their restricted access and creating a more personalized user experience. ', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
						<div class="feature-explanation-popup-main">
							<div class="feature-explanation-popup-outer">
								<div class="feature-explanation-popup-inner">
									<div class="feature-explanation-popup">
										<span class="dashicons dashicons-no-alt popup-close-btn" title="<?php esc_attr_e('Close', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>"></span>
										<div class="popup-body-content">
											<div class="feature-image">
												<img src="<?php echo esc_url(WB_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-five-img.png'); ?>" alt="<?php echo esc_attr('Free Shipping Based Check-Out Fees', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>">
											</div>
											<div class="feature-content">
												<p><?php esc_html_e('Customize the messages displayed to blocked individuals, providing clear explanations for their restricted access and creating a more personalized user experience.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
												<ul>
													<li><?php esc_html_e('So any block for an email address will get the message “this email address has been blocked” message.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></li>
												</ul>
											</div>
										</div>
									</div>		
								</div>
							</div>
						</div>
					</div>
					<div class="feature-box">
						<h3><?php esc_html_e('Browse Blacklisted User Details', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></h3>
						<span><i class="fa fa-info"></i></span>
						<p><?php esc_html_e('Allows users to view comprehensive information about users who have been blacklisted, offering insights into the number of attempts.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
						<div class="feature-explanation-popup-main">
							<div class="feature-explanation-popup-outer">
								<div class="feature-explanation-popup-inner">
									<div class="feature-explanation-popup">
										<span class="dashicons dashicons-no-alt popup-close-btn" title="<?php esc_attr_e('Close', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>"></span>
										<div class="popup-body-content">
											<div class="feature-image">
												<img src="<?php echo esc_url(WB_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-six-img.png'); ?>" alt="<?php echo esc_attr('Payment Gateway-Based Extra Fees', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?>">
											</div>
											<div class="feature-content">
												<p><?php esc_html_e('Check all blacklisted users in the Blocked User List tab. All the blocked users will list with the title of their email.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p>
												<ul>
													<li><?php echo sprintf( esc_html__('Check more details like displayed screenshots such as an address, number of attempts, Where the User was Banned, etc.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'), 2 ); ?></li>
												</ul>
											</div>
										</div>
									</div>		
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="upgrade-to-premium-btn">
				<a href="<?php echo esc_url('https://bit.ly/3E6rTQF') ?>" target="_blank" class="button button-primary"><?php esc_html_e('Upgrade to Premium', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?><svg id="Group_52548" data-name="Group 52548" xmlns="http://www.w3.org/2000/svg" width="22" height="20" viewBox="0 0 27.263 24.368"><path id="Path_199491" data-name="Path 199491" d="M333.833,428.628a1.091,1.091,0,0,1-1.092,1.092H316.758a1.092,1.092,0,1,1,0-2.183h15.984a1.091,1.091,0,0,1,1.091,1.092Z" transform="translate(-311.117 -405.352)" fill="#fff"></path><path id="Path_199492" data-name="Path 199492" d="M312.276,284.423h0a1.089,1.089,0,0,0-1.213-.056l-6.684,4.047-4.341-7.668a1.093,1.093,0,0,0-1.9,0l-4.341,7.668-6.684-4.047a1.091,1.091,0,0,0-1.623,1.2l3.366,13.365a1.091,1.091,0,0,0,1.058.825h18.349a1.09,1.09,0,0,0,1.058-.825l3.365-13.365A1.088,1.088,0,0,0,312.276,284.423Zm-4.864,13.151H290.764l-2.509-9.964,5.373,3.253a1.092,1.092,0,0,0,1.515-.4l3.944-6.969,3.945,6.968a1.092,1.092,0,0,0,1.515.4l5.373-3.253Z" transform="translate(-285.455 -280.192)" fill="#fff"></path></svg></a>
			</div>
		</div>
	</div>
	</div>
</div>
</div>
</div>
<?php 
