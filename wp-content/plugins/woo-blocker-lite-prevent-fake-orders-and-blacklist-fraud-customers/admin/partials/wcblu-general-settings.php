<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once( plugin_dir_path( __FILE__ ) . 'header/plugin-header.php' ); 

$getplugingeneralopt      = get_option( 'wcblu_general_option' );

if( isset( $getplugingeneralopt ) && !empty( $getplugingeneralopt ) ){
    $getplugingeneraloptarray              = json_decode( $getplugingeneralopt, true );
    $wcbfc_fraud_check_before_pay          = ! empty( $getplugingeneraloptarray['wcbfc_fraud_check_before_pay'] ) ? $getplugingeneraloptarray['wcbfc_fraud_check_before_pay'] : '0';
    $wcblu_pre_payment_message             = ! empty( $getplugingeneraloptarray['wcblu_pre_payment_message'] ) ? $getplugingeneraloptarray['wcblu_pre_payment_message'] : '';
    $wcbfc_update_order_status_on_score    = ! empty( $getplugingeneraloptarray['wcbfc_update_order_status_on_score'] ) ? $getplugingeneraloptarray['wcbfc_update_order_status_on_score'] : '0';
    $wcbfc_settings_low_risk_threshold     = ! empty( $getplugingeneraloptarray['wcbfc_settings_low_risk_threshold'] ) ? $getplugingeneraloptarray['wcbfc_settings_low_risk_threshold'] : '25';
    $wcbfc_settings_high_risk_threshold    = ! empty( $getplugingeneraloptarray['wcbfc_settings_high_risk_threshold'] ) ? $getplugingeneraloptarray['wcbfc_settings_high_risk_threshold'] : '75';
    $wcbfc_email_notification              = ! empty( $getplugingeneraloptarray['wcbfc_email_notification'] ) ? $getplugingeneraloptarray['wcbfc_email_notification'] : '0';
    $wcbfc_settings_cancel_score           = ! empty( $getplugingeneraloptarray['wcbfc_settings_cancel_score'] ) ? $getplugingeneraloptarray['wcbfc_settings_cancel_score'] : '0';
    $wcbfc_settings_hold_score             = ! empty( $getplugingeneraloptarray['wcbfc_settings_hold_score'] ) ? $getplugingeneraloptarray['wcbfc_settings_hold_score'] : '0';
    $wcbfc_settings_email_score            = ! empty( $getplugingeneraloptarray['wcbfc_settings_email_score'] ) ? $getplugingeneraloptarray['wcbfc_settings_email_score'] : '0';
    $wcblu_settings_custom_email           = ! empty( $getplugingeneraloptarray['wcblu_settings_custom_email'] ) ? $getplugingeneraloptarray['wcblu_settings_custom_email'] : '';
    $wcblu_settings_whitelist              = ! empty( $getplugingeneraloptarray['wcblu_settings_whitelist'] ) ? $getplugingeneraloptarray['wcblu_settings_whitelist'] : '';
    $wcbfc_fraud_check_status              = ! empty( $getplugingeneraloptarray['wcbfc_fraud_check_status'] ) ? $getplugingeneraloptarray['wcbfc_fraud_check_status'] : 'off';
    $wcblu_whitelist_payment_method        = ! empty( $getplugingeneraloptarray['wcblu_whitelist_payment_method'] ) ? $getplugingeneraloptarray['wcblu_whitelist_payment_method'] : array();
    $wcbfc_enable_whitelist_payment_method = ! empty( $getplugingeneraloptarray['wcbfc_enable_whitelist_payment_method'] ) ? $getplugingeneraloptarray['wcbfc_enable_whitelist_payment_method'] : '0';
    $wcblu_whitelist_user_roles            = ! empty( $getplugingeneraloptarray['wcblu_whitelist_user_roles'] ) ? $getplugingeneraloptarray['wcblu_whitelist_user_roles'] : array();
    $wcbfc_enable_whitelist_user_roles     = ! empty( $getplugingeneraloptarray['wcbfc_enable_whitelist_user_roles'] ) ? $getplugingeneraloptarray['wcbfc_enable_whitelist_user_roles'] : array();
}else{
    $wcbfc_fraud_check_before_pay          = '0';
    $wcblu_pre_payment_message             = '';
    $wcbfc_update_order_status_on_score    = '0';
    $wcbfc_settings_low_risk_threshold     = '25';
    $wcbfc_settings_high_risk_threshold    = '75';
    $wcbfc_email_notification              = '0';
    $wcbfc_settings_cancel_score           = '90';
    $wcbfc_settings_hold_score             = '70';
    $wcbfc_settings_email_score            = '50';
    $wcblu_settings_custom_email           = '';
    $wcblu_settings_whitelist              = '';
    $wcbfc_fraud_check_status              = 'off';
    $wcblu_whitelist_payment_method        = array();
    $wcbfc_enable_whitelist_payment_method = '0';
    $wcblu_whitelist_user_roles            = array();
    $wcbfc_enable_whitelist_user_roles     = '0';
}
$success_note   = filter_input( INPUT_GET, 'success', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$wcbfc_status   = '';
?>
<div class="wcblu-main-table res-cl">
    <?php if( !empty($success_note) ){ ?>
        <div id="message" class="updated notice is-dismissible"><p><?php esc_html_e('Data has been updated.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p></div>
    <?php } ?>
    <form id="wcblu_plugin_general_settings" method="post" action="<?php esc_url( get_admin_url() ); ?>admin-post.php" enctype="multipart/form-data">
        <input type='hidden' name='action' value='submit_general_setting_form_wcblu'/>
        <?php wp_nonce_field( 'wcblu_plugin_general_settings', 'wcblu_plugin_general_settings_nonce' ); ?>
        <div class="heading_div">
            <div class="heading_section">
                <h2><?php echo esc_html__( 'General Settings', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></h2>
            </div>
            <button type="submit" name="wcblu_gs_submit" class="button button-primary wcblu_submit" value="Save Changes"><?php echo esc_html__( 'Save Changes', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></button>
        </div>
        <table class="form-table table-outer general-settings striped res-cl">
            <tbody>
                <tr>
                    <th scope="row" class="titledesc">
                        <label><?php echo esc_html__( 'Fraud Score Check', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                        <label class="switch">
                            <input type="checkbox" name="wcbfc_fraud_check_status" value="on" <?php echo ( isset( $wcbfc_fraud_check_status ) && $wcbfc_fraud_check_status === 'off' ) ? '' : 'checked'; ?>>
                            <div class="slider round"></div>
                        </label>
                        <span class="wcbfc-description-tooltip-icon"></span>
                        <p class="wcblu_content_container wcbfc-tooltip-sh"><?php echo esc_html__( 'If this is enabled, the fraud check will automatically check for all rules and general settings, count the score and prevent the fraud orders.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label><?php echo esc_html__( 'Before Payment Checking', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                    <?php
                    if ( empty( $wcbfc_fraud_check_before_pay ) && '' === $wcbfc_fraud_check_before_pay ) {
                        ?>
                        <input checked type="checkbox" id="wcbfc_fraud_check_before_pay"
                            name="wcbfc_fraud_check_before_pay" value="">
                        <?php
                    } else {
                        ?>
                        <input <?php if ( ! empty( $wcbfc_fraud_check_before_pay ) && '1' === $wcbfc_fraud_check_before_pay ) { ?> checked <?php } ?>
                            type="checkbox" id="wcbfc_fraud_check_before_pay"
                            name="wcbfc_fraud_check_before_pay" value="<?php
                        if ( ! empty( $wcbfc_fraud_check_before_pay ) && '1' === $wcbfc_fraud_check_before_pay ) {
                            echo "1";
                            $wcbfc_status = 'show_it';
                        } else {
                            echo "0";
                            $wcbfc_status = 'hide_it';
                        }
                        ?>">
                        <?php 
                    } 
                    ?>
                        <label for="wcbfc_fraud_check_before_pay"><?php echo esc_html__( 'Fraud check before payment', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        <span class="wcbfc-description-tooltip-icon"></span>
                        <p class="wcblu_content_container wcbfc-tooltip-sh"><?php echo esc_html__( 'If this is enabled, this option will prevent the customer to place an order at the checkout page if the fraud score is equal to or higher than a high risk score.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></p>
                
                    </td>
                </tr>
                <tr class="<?php echo esc_attr( $wcbfc_status ); ?>" data-label="<?php echo esc_attr('wcbfc_fraud_check_before_pay'); ?>">
                    <th scope="row" class="titledesc">
                        <label> <?php echo esc_html__( 'Before Payment Checking Message', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                        <?php
                        if( empty( $wcblu_pre_payment_message ) ){
                            $wcblu_pre_payment_message = 'Website Administrator does not allow you to place this order. Kindly contact admin.';
                        }
                        ?>
                        <textarea name="wcblu_pre_payment_message" id="wcblu_pre_payment_message" style="width:100%; height: 100px;" class="" placeholder="" spellcheck="false"><?php echo esc_html__( $wcblu_pre_payment_message, 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></textarea>
                        <span class="wcbfc-description-tooltip-icon"></span>
                        <p class="wcblu_content_container wcbfc-tooltip-sh"><?php echo esc_html__( 'Add a custom message for blocked users on Before Payment Check', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label><?php echo esc_html__( 'Enable Update Order Status', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                        <?php
                        $wcbfc_status = '';
                        if ( empty( $wcbfc_update_order_status_on_score ) && '' === $wcbfc_update_order_status_on_score ) { ?>
                            <input checked type="checkbox" id="wcbfc_update_order_status_on_score"
                                name="wcbfc_update_order_status_on_score" value="">
                            <?php
                        } else { ?>
                            <input <?php if ( ! empty( $wcbfc_update_order_status_on_score ) && '1' === $wcbfc_update_order_status_on_score ) { ?> checked <?php } ?>
                                type="checkbox" id="wcbfc_update_order_status_on_score"
                                name="wcbfc_update_order_status_on_score" value="<?php
                            if ( ! empty( $wcbfc_update_order_status_on_score ) && '1' === $wcbfc_update_order_status_on_score ) {
                                echo "1";
                                $wcbfc_status = 'show_it';
                            } else {
                                echo "0";
                                $wcbfc_status = 'hide_it';
                            } ?>">
                            <?php 
                        } ?>
                        <label for="wcbfc_update_order_status_on_score"><?php echo esc_html__( 'Update order status according the order score.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        <span class="wcbfc-description-tooltip-icon"></span>
                        <p class="wcblu_content_container wcbfc-tooltip-sh"><?php echo esc_html__( 'If this is enabled, the order status will be updated as score value\'s status.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></p>
                    </td>
                </tr>
                <tr class="<?php echo esc_attr( $wcbfc_status ); ?>" data-label="<?php echo esc_attr('wcbfc_update_order_status_on_score'); ?>">
                    <th scope="row" class="titledesc">
                        <label> <?php echo esc_html__( 'Cancel Score', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                        <select name="wcbfc_settings_cancel_score" id="wcbfc_settings_cancel_score" style="width: 5em;" class="">
                            <?php
                                for ( $i = 100; $i > - 1; $i -- ) {
                                    if ( ( $i % 5 ) == 0 ) {
                                        if( $i === (int) $wcbfc_settings_cancel_score ){ ?>
                                            <option selected value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html__( $i, 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html__( $i, 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
                                        <?php
                                        }
                                    }
                                }
                            ?>
                        </select>
                        <span class="wcbfc-description-tooltip-icon"></span>
                        <p class="wcblu_content_container wcbfc-tooltip-sh"><?php echo esc_html__( 'Orders with a score equal to or greater than this number will be automatically cancelled. Select 0 to disable.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></p>
                    </td>
                </tr>
                <tr class="<?php echo esc_attr( $wcbfc_status ); ?>" data-label="<?php echo esc_attr('wcbfc_update_order_status_on_score'); ?>">
                    <th scope="row" class="titledesc">
                        <label> <?php echo esc_html__( 'On-Hold Score', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                        <select name="wcbfc_settings_hold_score" id="wcbfc_settings_hold_score" style="width: 5em;" class="">
                            <?php
                                for ( $i = 100; $i > - 1; $i -- ) {
                                    if ( ( $i % 5 ) == 0 ) {
                                        if( $i === (int) $wcbfc_settings_hold_score ){ ?>
                                            <option selected value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html__( $i, 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html__( $i, 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
                                        <?php
                                        }
                                    }
                                }
                            ?>
                        </select>
                        <span class="wcbfc-description-tooltip-icon"></span>
                        <p class="wcblu_content_container wcbfc-tooltip-sh"><?php echo esc_html__( 'Orders with a score equal to or greater than this number will be automatically set on hold. Select 0 to disable.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label><?php echo esc_html__( 'Set Risk Thresholds', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                        <div class="wcblu_field">
                            <input name="wcbfc_settings_low_risk_threshold" id="wcbfc_settings_low_risk_threshold" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_settings_low_risk_threshold ); ?>" class="" placeholder="" min="0" step="1" max="100">
                            <label for="wcbfc_settings_low_risk_threshold"><?php echo esc_html__( 'Medium Risk threshold', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                            <span class="wcbfc-description-tooltip-icon"></span>
                        </div>
                        <div class="wcblu_field">
                            <input name="wcbfc_settings_high_risk_threshold" id="wcbfc_settings_high_risk_threshold" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_settings_high_risk_threshold ); ?>" class="" placeholder="" min="0" step="1" max="100">
                            <label for="wcbfc_settings_high_risk_threshold"><?php echo esc_html__( 'High Risk threshold', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                        <p class="wcblu_content_container wcbfc-tooltip-sh"><?php echo esc_html__( 'With above setting you may set the risk threadhold ( Low, Medium, High ) between 0-100.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></p>
                    </td> 
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label><?php echo esc_html__( 'Send Admin Email', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                        <?php
                        $wcbfc_status = '';
                        if ( empty( $wcbfc_email_notification ) && '' === $wcbfc_email_notification ) { ?>
                            <input checked type="checkbox" id="wcbfc_email_notification"
                                name="wcbfc_email_notification" value="">
                            <?php
                        } else { ?>
                            <input <?php if ( ! empty( $wcbfc_email_notification ) && '1' === $wcbfc_email_notification ) { ?> checked <?php } ?>
                                type="checkbox" id="wcbfc_email_notification"
                                name="wcbfc_email_notification" value="<?php
                            if ( ! empty( $wcbfc_email_notification ) && '1' === $wcbfc_email_notification ) {
                                echo "1";
                                $wcbfc_status = 'show_it';
                            } else {
                                echo "0";
                                $wcbfc_status = 'hide_it';
                            } ?>">
                            <?php 
                        } ?>
                        <label for="wcbfc_email_notification"><?php echo esc_html__( 'Send a notification mail to the site admin showing fraud score checks.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                    </td>
                </tr>
                <tr class="<?php echo esc_attr( $wcbfc_status ); ?>" data-label="<?php echo esc_attr('wcbfc_email_notification'); ?>">
                    <th scope="row" class="titledesc">
                        <label> <?php echo esc_html__( 'Email Notification Score', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                        <select name="wcbfc_settings_email_score" id="wcbfc_settings_email_score" style="width: 5em;" class="">
                                <?php
                                    for ( $i = 100; $i > - 1; $i -- ) {
                                        if ( ( $i % 5 ) == 0 ) {
                                            if( $i === (int) $wcbfc_settings_email_score ){ ?>
                                                <option selected value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html__( $i, 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
                                            <?php } else { ?>
                                                <option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html__( $i, 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
                                            <?php
                                            }
                                        }
                                    }
                                ?>
                        </select>
                        <span class="wcbfc-description-tooltip-icon"></span>
                        <p class="wcblu_content_container wcbfc-tooltip-sh"><?php echo esc_html__( 'An admin email notification will be sent if an orders scores equal to or greater than this number. Select 0 to disable.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></p>
                    </td>
                </tr>
                <tr class="<?php echo esc_attr( $wcbfc_status ); ?>" data-label="<?php echo esc_attr('wcbfc_email_notification'); ?>">
                    <th scope="row" class="titledesc">
                        <label> <?php echo esc_html__( 'Additional Recipients', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                        <input name="wcblu_settings_custom_email" id="wcblu_settings_custom_email" type="text" style="width: 70%;" value="<?php echo esc_attr( $wcblu_settings_custom_email ); ?>" class="" placeholder="">
                        <span class="wcbfc-description-tooltip-icon"></span>
                        <p class="wcblu_content_container wcbfc-tooltip-sh"><?php echo esc_html__( 'To send email notifications to additional addresses, enter them, separated by commas, above.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label> <?php echo esc_html__( 'Email Whitelist', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                        <textarea name="wcblu_settings_whitelist" id="wcblu_settings_whitelist" style="width:100%; height: 100px;" class="" placeholder=""><?php echo esc_html( $wcblu_settings_whitelist ); ?></textarea>
                        <span class="wcbfc-description-tooltip-icon"></span>
                        <p class="wcblu_content_container wcbfc-tooltip-sh"><?php echo esc_html__( 'Email addresses listed above will not be subject to fraud checks. Enter one email address per line.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label> <?php echo esc_html__( 'Whitelist Payment Method', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                    <?php
                        if ( empty( $wcbfc_enable_whitelist_payment_method ) && '' === $wcbfc_enable_whitelist_payment_method ) { ?>
                            <input checked type="checkbox" id="wcbfc_enable_whitelist_payment_method"
                                name="wcbfc_enable_whitelist_payment_method" value="">
                            <?php
                        } else { ?>
                            <input <?php if ( ! empty( $wcbfc_enable_whitelist_payment_method ) && '1' === $wcbfc_enable_whitelist_payment_method ) { ?> checked <?php } ?>
                                type="checkbox" id="wcbfc_enable_whitelist_payment_method"
                                name="wcbfc_enable_whitelist_payment_method" value="<?php
                            if ( ! empty( $wcbfc_enable_whitelist_payment_method ) && '1' === $wcbfc_enable_whitelist_payment_method ) {
                                echo "1";
                            } else {
                                echo "0";
                            } ?>">
                            <?php 
                        } ?>
                        <label for="wcbfc_enable_whitelist_payment_method"><?php echo esc_html__( 'Enable Payment Method Whitelisting', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        <div class="wcblu_rule_field">
                            <select name="wcblu_whitelist_payment_method[]" id="wcblu_whitelist_payment_method" multiple data-placeholder="Select payment methods">
                            <?php
                                $installed_payment_methods = WC()->payment_gateways->payment_gateways();
                                if( isset( $installed_payment_methods ) && !empty( $installed_payment_methods ) ){
                                    foreach ( $installed_payment_methods as $methods=>$arr ) { ?>
                                        <?php if( in_array( $methods, $wcblu_whitelist_payment_method ) ){ ?>
                                            <option value="<?php echo esc_attr( $methods ) ?>" selected><?php echo esc_html( $arr->method_title ); ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo esc_attr( $methods ) ?>"><?php echo esc_html( $arr->method_title ); ?></option>    
                                        <?php }
                                    }
                                }
                            ?>
                            </select>
                        </div>
                        <p class="wcblu_content_container"><?php echo esc_html__( 'Selected payment methods will not be considered for Cancel Order Score and On-Hold Order Score evaluation for each order.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label> <?php echo esc_html__( 'User Roles Whitelisting', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                    <?php
                        if ( empty( $wcbfc_enable_whitelist_user_roles ) && '' === $wcbfc_enable_whitelist_user_roles ) { ?>
                            <input checked type="checkbox" id="wcbfc_enable_whitelist_user_roles"
                                name="wcbfc_enable_whitelist_user_roles" value="">
                            <?php
                        } else { ?>
                            <input <?php if ( ! empty( $wcbfc_enable_whitelist_user_roles ) && '1' === $wcbfc_enable_whitelist_user_roles ) { ?> checked <?php } ?>
                                type="checkbox" id="wcbfc_enable_whitelist_user_roles"
                                name="wcbfc_enable_whitelist_user_roles" value="<?php
                            if ( ! empty( $wcbfc_enable_whitelist_user_roles ) && '1' === $wcbfc_enable_whitelist_user_roles ) {
                                echo "1";
                            } else {
                                echo "0";
                            } ?>">
                            <?php 
                        } ?>
                        <label for="wcbfc_enable_whitelist_user_roles"><?php echo esc_html__( 'Enable User Roles Whitelisting', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        <div class="wcblu_rule_field">
                            <select name="wcblu_whitelist_user_roles[]" id="wcblu_whitelist_user_roles" multiple data-placeholder="Select user roles">
                            <?php
                                global $wp_roles;
                                $all_roles = $wp_roles->roles;
                                $editable_roles = apply_filters('editable_roles', $all_roles);
                                if( isset( $editable_roles ) && !empty( $editable_roles ) ){
                                    foreach ($editable_roles as $urole => $details) {
                                        $name = translate_user_role($details['name']);
                                        if( in_array( $urole, $wcblu_whitelist_user_roles ) ){ ?>
                                            <option value="<?php echo esc_attr( $urole ) ?>" selected><?php echo esc_html( $name ); ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo esc_attr( $urole ) ?>"><?php echo esc_html( $name ); ?></option>    
                                        <?php }
                                    }
                                }
                            ?>
                            </select>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <p>
            <button type="submit" name="wcblu_gs_submit" class="button button-primary" value="Save Changes"><?php echo esc_html__( 'Save Changes', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></button>
        </p>
    </form>
</div>

</div>
</div>
</div>
</div>