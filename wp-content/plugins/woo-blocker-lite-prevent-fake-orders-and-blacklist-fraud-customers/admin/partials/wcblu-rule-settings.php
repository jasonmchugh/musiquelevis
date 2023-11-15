<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once( plugin_dir_path( __FILE__ ) . 'header/plugin-header.php' ); 

$getpluginruleopt      = get_option( 'wcblu_rules_option' );

if( isset( $getpluginruleopt ) && !empty( $getpluginruleopt ) ){
    $getpluginruleoptarray = json_decode( $getpluginruleopt, true );

    $wcbfc_first_order_status           = ! empty( $getpluginruleoptarray['wcbfc_first_order_status'] ) ? $getpluginruleoptarray['wcbfc_first_order_status'] : '0';
    $wcbfc_first_order_weight           = ! empty( $getpluginruleoptarray['wcbfc_first_order_weight'] ) ? $getpluginruleoptarray['wcbfc_first_order_weight'] : '5';
    $wcbfc_first_order_custom           = ! empty( $getpluginruleoptarray['wcbfc_first_order_custom'] ) ? $getpluginruleoptarray['wcbfc_first_order_custom'] : '0';
    $wcbfc_first_order_custom_weight    = ! empty( $getpluginruleoptarray['wcbfc_first_order_custom_weight'] ) ? $getpluginruleoptarray['wcbfc_first_order_custom_weight'] : '5';
    $wcbfc_bca_order                    = ! empty( $getpluginruleoptarray['wcbfc_bca_order'] ) ? $getpluginruleoptarray['wcbfc_bca_order'] : '0';
    $wcbfc_bca_order_weight             = ! empty( $getpluginruleoptarray['wcbfc_bca_order_weight'] ) ? $getpluginruleoptarray['wcbfc_bca_order_weight'] : '20';
    $wcbfc_proxy_order                  = ! empty( $getpluginruleoptarray['wcbfc_proxy_order'] ) ? $getpluginruleoptarray['wcbfc_proxy_order'] : '0';
    $wcbfc_proxy_order_weight           = ! empty( $getpluginruleoptarray['wcbfc_proxy_order_weight'] ) ? $getpluginruleoptarray['wcbfc_proxy_order_weight'] : '50';
    $wcbfc_international_order          = ! empty( $getpluginruleoptarray['wcbfc_international_order'] ) ? $getpluginruleoptarray['wcbfc_international_order'] : '0';
    $wcbfc_international_order_weight   = ! empty( $getpluginruleoptarray['wcbfc_international_order_weight'] ) ? $getpluginruleoptarray['wcbfc_international_order_weight'] : '10';
    $wcbfc_suspecius_email              = ! empty( $getpluginruleoptarray['wcbfc_suspecius_email'] ) ? $getpluginruleoptarray['wcbfc_suspecius_email'] : '0';
    $wcbfc_suspecius_email_list         = ! empty( $getpluginruleoptarray['wcbfc_suspecius_email_list'] ) ? $getpluginruleoptarray['wcbfc_suspecius_email_list'] : array();
    $wcbfc_suspecious_email_weight      = ! empty( $getpluginruleoptarray['wcbfc_suspecious_email_weight'] ) ? $getpluginruleoptarray['wcbfc_suspecious_email_weight'] : '';
    $wcbfc_billing_phone_number_order   = ! empty( $getpluginruleoptarray['wcbfc_billing_phone_number_order'] ) ? $getpluginruleoptarray['wcbfc_billing_phone_number_order'] : '0';
    $wcbfc_phone_number_order_weight    = ! empty( $getpluginruleoptarray['wcbfc_billing_phone_number_order_weight'] ) ? $getpluginruleoptarray['wcbfc_billing_phone_number_order_weight'] : '15';
    $wcbfc_unsafe_countries             = ! empty( $getpluginruleoptarray['wcbfc_unsafe_countries'] ) ? $getpluginruleoptarray['wcbfc_unsafe_countries'] : '0';
    $wcbfc_unsafe_countries_ip          = ! empty( $getpluginruleoptarray['wcbfc_unsafe_countries_ip'] ) ? $getpluginruleoptarray['wcbfc_unsafe_countries_ip'] : '0';
    $wcblu_define_unsafe_countries_list = ! empty( $getpluginruleoptarray['wcblu_define_unsafe_countries_list'] ) ? $getpluginruleoptarray['wcblu_define_unsafe_countries_list'] : array();
    $wcbfc_unsafe_countries_weight      = ! empty( $getpluginruleoptarray['wcbfc_unsafe_countries_weight'] ) ? $getpluginruleoptarray['wcbfc_unsafe_countries_weight'] : '25';
    $wcbfc_ip_multiple_check            = ! empty( $getpluginruleoptarray['wcbfc_ip_multiple_check'] ) ? $getpluginruleoptarray['wcbfc_ip_multiple_check'] : '0';
    $wcbfc_ip_multiple_time_span        = ! empty( $getpluginruleoptarray['wcbfc_ip_multiple_time_span'] ) ? $getpluginruleoptarray['wcbfc_ip_multiple_time_span'] : '30';
    $wcbfc_ip_multiple_weight           = ! empty( $getpluginruleoptarray['wcbfc_ip_multiple_weight'] ) ? $getpluginruleoptarray['wcbfc_ip_multiple_weight'] : '25';
    $wcbfc_order_avg_amount_check       = ! empty( $getpluginruleoptarray['wcbfc_order_avg_amount_check'] ) ? $getpluginruleoptarray['wcbfc_order_avg_amount_check'] : '0';
    $wcbfc_order_avg_amount_time_span   = ! empty( $getpluginruleoptarray['wcbfc_order_avg_amount_time_span'] ) ? $getpluginruleoptarray['wcbfc_order_avg_amount_time_span'] : '2';
    $wcbfc_order_avg_amount_weight      = ! empty( $getpluginruleoptarray['wcbfc_order_avg_amount_weight'] ) ? $getpluginruleoptarray['wcbfc_order_avg_amount_weight'] : '15';
    $wcbfc_order_amount_check           = ! empty( $getpluginruleoptarray['wcbfc_order_amount_check'] ) ? $getpluginruleoptarray['wcbfc_order_amount_check'] : '0';
    $wcbfc_max_order_attempt_span       = ! empty( $getpluginruleoptarray['wcbfc_max_order_attempt_span'] ) ? $getpluginruleoptarray['wcbfc_max_order_attempt_span'] : '0';
    $wcbfc_max_order_attempt_weight     = ! empty( $getpluginruleoptarray['wcbfc_max_order_attempt_weight'] ) ? $getpluginruleoptarray['wcbfc_max_order_attempt_weight'] : '5';
    $wcbfc_too_many_oa_check            = ! empty( $getpluginruleoptarray['wcbfc_too_many_oa_check'] ) ? $getpluginruleoptarray['wcbfc_too_many_oa_check'] : '0';
    $wcbfc_too_many_oats_attempt_span   = ! empty( $getpluginruleoptarray['wcbfc_too_many_oats_attempt_span'] ) ? $getpluginruleoptarray['wcbfc_too_many_oats_attempt_span'] : '24';
    $wcbfc_too_many_oatos_attempt_span  = ! empty( $getpluginruleoptarray['wcbfc_too_many_oatos_attempt_span'] ) ? $getpluginruleoptarray['wcbfc_too_many_oatos_attempt_span'] : '5';
    $wcbfc_too_many_oa_attempt_weight   = ! empty( $getpluginruleoptarray['wcbfc_too_many_oa_attempt_weight'] ) ? $getpluginruleoptarray['wcbfc_too_many_oa_attempt_weight'] : '25';
} else {
    $wcbfc_first_order_status = '0';
    $wcbfc_first_order_weight = '5';
    $wcbfc_first_order_custom = '0';
    $wcbfc_first_order_custom_weight = '20';
    $wcbfc_bca_order = '0';
    $wcbfc_bca_order_weight = '20';
    $wcbfc_proxy_order = '0';
    $wcbfc_proxy_order_weight = '50';
    $wcbfc_international_order = '0';
    $wcbfc_international_order_weight = '10';
    $wcbfc_suspecius_email = '0';
    $wcbfc_suspecius_email_list = '';
    $wcbfc_suspecious_email_weight = '5';
    $wcbfc_unsafe_countries = '0';
    $wcbfc_unsafe_countries_ip = '0';
    $wcblu_define_unsafe_countries_list = array();
    $wcbfc_unsafe_countries_weight = '25';
    $wcbfc_billing_phone_number_order = '0';
    $wcbfc_phone_number_order_weight = '15';
    $wcbfc_ip_multiple_check = '0';
    $wcbfc_ip_multiple_time_span = '30';
    $wcbfc_ip_multiple_weight = '25';
    $wcbfc_order_avg_amount_check = '0';
    $wcbfc_order_avg_amount_time_span = '2';
    $wcbfc_order_avg_amount_weight = '15';
    $wcbfc_order_amount_check = '0';         
    $wcbfc_max_order_attempt_span = '0';    
    $wcbfc_max_order_attempt_weight = '5';
    $wcbfc_too_many_oa_check = '0';
    $wcbfc_too_many_oats_attempt_span = '24';  
    $wcbfc_too_many_oatos_attempt_span = '5';
    $wcbfc_too_many_oa_attempt_weight = '25';
}
$success_note   = filter_input( INPUT_GET, 'success', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
global $wpdb;
?>
<div class="wcblu-main-table res-cl">
    <?php if( !empty($success_note) ){ ?>
        <div id="message" class="updated notice is-dismissible"><p><?php esc_html_e('Data has been updated.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers'); ?></p></div>
    <?php } ?>
    <form id="wcblu_plugin_general_rules" method="post" action="<?php esc_url( get_admin_url() ); ?>admin-post.php" enctype="multipart/form-data" novalidate="novalidate">
        <input type='hidden' name='action' value='submit_general_rules_form_wcblu'/>
        <?php wp_nonce_field( 'wcblu_save_rule_settings', 'wcblu_save_rule_settings_nonce' ); ?>
        <div class="heading_div">
            <div class="heading_section">
                <h2><?php echo esc_html__( 'General Rules', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></h2>
            </div>
            <button type="submit" name="wcblu_gr_submit" class="button button-primary wcblu_submit" value="Save Changes"><?php echo esc_html__( 'Save Changes', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></button>
        </div>
        <table class="form-table table-outer general-rules res-cl">
            <tbody>
                <tr>
                    <th scope="row" class="titledesc">
                        <label><?php echo esc_html__( 'Check Customer\'s First Order?', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                    <?php
                        if ( empty( $wcbfc_first_order_status ) && '' === $wcbfc_first_order_status ) { ?>
                            <input checked type="checkbox" id="wcbfc_first_order_status"
                                name="wcbfc_first_order_status" value="">
                            <?php
                        } else { ?>
                            <input <?php if ( ! empty( $wcbfc_first_order_status ) && '1' === $wcbfc_first_order_status ) { ?> checked <?php } ?>
                                type="checkbox" id="wcbfc_first_order_status"
                                name="wcbfc_first_order_status" value="<?php
                            if ( ! empty( $wcbfc_first_order_status ) && '1' === $wcbfc_first_order_status ) {
                                echo "1";
                            } else {
                                echo "0";
                            } ?>">
                            <?php 
                        } ?>
                        <label for="wcbfc_first_order_status"><?php echo esc_html__( 'Check if it is the customer\'s first Order.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                        <div class="wcblu_rule_field">
                            <input name="wcbfc_first_order_weight" id="wcbfc_first_order_weight" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_first_order_weight ); ?>" class="" placeholder="" min="0" step="1" max="100">
                            <label><?php echo esc_html__( 'Rule Weight', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label><?php echo esc_html__( 'Check If First Orders in Processing State?', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                    <?php
                        if ( empty( $wcbfc_first_order_custom ) && '' === $wcbfc_first_order_custom ) { ?>
                            <input checked type="checkbox" id="wcbfc_first_order_custom"
                                name="wcbfc_first_order_custom" value="">
                            <?php
                        } else { ?>
                            <input <?php if ( ! empty( $wcbfc_first_order_custom ) && '1' === $wcbfc_first_order_custom ) { ?> checked <?php } ?>
                                type="checkbox" id="wcbfc_first_order_custom"
                                name="wcbfc_first_order_custom" value="<?php
                            if ( ! empty( $wcbfc_first_order_custom ) && '1' === $wcbfc_first_order_custom ) {
                                echo "1";
                            } else {
                                echo "0";
                            } ?>">
                            <?php 
                        } ?>
                        <label for="wcbfc_first_order_custom"><?php echo esc_html__( 'Perform first order check again once order is in Processing state', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                        <div class="wcblu_rule_field">
                            <input name="wcbfc_first_order_custom_weight" id="wcbfc_first_order_custom_weight" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_first_order_custom_weight ); ?>" class="" placeholder="" min="0" step="1" max="100">
                            <label><?php echo esc_html__( 'Rule Weight', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label><?php echo esc_html__( 'Are Billing and Shipping Addresses Same?', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                    <?php
                        if ( empty( $wcbfc_bca_order ) && '' === $wcbfc_bca_order ) { ?>
                            <input checked type="checkbox" id="wcbfc_bca_order"
                                name="wcbfc_bca_order" value="">
                            <?php
                        } else { ?>
                            <input <?php if ( ! empty( $wcbfc_bca_order ) && '1' === $wcbfc_bca_order ) { ?> checked <?php } ?>
                                type="checkbox" id="wcbfc_bca_order"
                                name="wcbfc_bca_order" value="<?php
                            if ( ! empty( $wcbfc_bca_order ) && '1' === $wcbfc_bca_order ) {
                                echo "1";
                            } else {
                                echo "0";
                            } ?>">
                            <?php 
                        } ?>
                        <label for="wcbfc_bca_order"><?php echo esc_html__( 'Check billing and shipping addresses are not the same.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                        <div class="wcblu_rule_field">
                            <input name="wcbfc_bca_order_weight" id="wcbfc_bca_order_weight" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_bca_order_weight ); ?>" class="" placeholder="" min="0" step="1" max="100">
                            <label><?php echo esc_html__( 'Rule Weight', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                    </td>
                </tr>
                <tr> 
                    <th scope="row" class="titledesc">
                        <label><?php echo esc_html__( 'Enable phone number and billing country check', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                    <?php
                        if ( empty( $wcbfc_billing_phone_number_order ) && '' === $wcbfc_billing_phone_number_order ) { ?>
                            <input checked type="checkbox" id="wcbfc_billing_phone_number_order"
                                name="wcbfc_billing_phone_number_order" value="">
                            <?php
                        } else { ?>
                            <input <?php if ( ! empty( $wcbfc_billing_phone_number_order ) && '1' === $wcbfc_billing_phone_number_order ) { ?> checked <?php } ?>
                                type="checkbox" id="wcbfc_billing_phone_number_order"
                                name="wcbfc_billing_phone_number_order" value="<?php
                            if ( ! empty( $wcbfc_billing_phone_number_order ) && '1' === $wcbfc_billing_phone_number_order ) {
                                echo "1";
                            } else {
                                echo "0";
                            } ?>">
                            <?php 
                        } ?>
                        <label for="wcbfc_billing_phone_number_order"><?php echo esc_html__( ' If you enable this rule, then it is highly recommended that you use a correct international phone number format on the checkout page. Else, this rule will treat an invalid phone number as a risk. For example: (+1) 111 1111', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                        <div class="wcblu_rule_field">
                            <input name="wcbfc_billing_phone_number_order_weight" id="wcbfc_billing_phone_number_order_weight" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_phone_number_order_weight ); ?>" class="" placeholder="" min="0" step="1" max="100">
                            <label><?php echo esc_html__( 'Rule Weight', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label><?php echo esc_html__( 'Check Customer behind Proxy or VPN?', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                    <?php
                        if ( empty( $wcbfc_proxy_order ) && '' === $wcbfc_proxy_order ) { ?>
                            <input checked type="checkbox" id="wcbfc_proxy_order"
                                name="wcbfc_proxy_order" value="">
                            <?php
                        } else { ?>
                            <input <?php if ( ! empty( $wcbfc_proxy_order ) && '1' === $wcbfc_proxy_order ) { ?> checked <?php } ?>
                                type="checkbox" id="wcbfc_proxy_order"
                                name="wcbfc_proxy_order" value="<?php
                            if ( ! empty( $wcbfc_proxy_order ) && '1' === $wcbfc_proxy_order ) {
                                echo "1";
                            } else {
                                echo "0";
                            } ?>">
                            <?php 
                        } ?>
                        <label for="wcbfc_proxy_order"><?php echo esc_html__( 'Check if the customer is behind either a proxy or a VPN', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                        <div class="wcblu_rule_field">
                            <input name="wcbfc_proxy_order_weight" id="wcbfc_proxy_order_weight" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_proxy_order_weight ); ?>" class="" placeholder="" min="0" step="1" max="100">
                            <label><?php echo esc_html__( 'Rule Weight', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label><?php echo esc_html__( 'Same IP but Different Customer Addresses?', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                    <?php
                        if ( empty( $wcbfc_ip_multiple_check ) && '' === $wcbfc_ip_multiple_check ) { ?>
                            <input checked type="checkbox" id="wcbfc_ip_multiple_check"
                                name="wcbfc_ip_multiple_check" value="">
                            <?php
                        } else { ?>
                            <input <?php if ( ! empty( $wcbfc_ip_multiple_check ) && '1' === $wcbfc_ip_multiple_check ) { ?> checked <?php } ?>
                                type="checkbox" id="wcbfc_ip_multiple_check"
                                name="wcbfc_ip_multiple_check" value="<?php
                            if ( ! empty( $wcbfc_ip_multiple_check ) && '1' === $wcbfc_ip_multiple_check ) {
                                echo "1";
                            } else {
                                echo "0";
                            } ?>">
                            <?php 
                        } ?>
                        <label for="wcbfc_ip_multiple_check"><?php echo esc_html__( 'Check if multiple orders with different billing or shipping addresses have originated from the same IP address', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                        <div class="wcblu_rule_field">
                            <input name="wcbfc_ip_multiple_time_span" id="wcbfc_ip_multiple_time_span" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_ip_multiple_time_span ); ?>" class="" placeholder="" min="0" step="1">
                            <label><?php echo esc_html__( 'The number of days in the past to check IP addresses and physical addresses', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                        <div class="wcblu_rule_field">
                            <input name="wcbfc_ip_multiple_weight" id="wcbfc_ip_multiple_weight" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_ip_multiple_weight ); ?>" class="" placeholder="" min="0" step="1" max="100">
                            <label><?php echo esc_html__( 'Rule Weight', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label><?php echo esc_html__( 'Is International Order?', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                    <?php
                        if ( empty( $wcbfc_international_order ) && '' === $wcbfc_international_order ) { ?>
                            <input checked type="checkbox" id="wcbfc_international_order"
                                name="wcbfc_international_order" value="">
                            <?php
                        } else { ?>
                            <input <?php if ( ! empty( $wcbfc_international_order ) && '1' === $wcbfc_international_order ) { ?> checked <?php } ?>
                                type="checkbox" id="wcbfc_international_order"
                                name="wcbfc_international_order" value="<?php
                            if ( ! empty( $wcbfc_international_order ) && '1' === $wcbfc_international_order ) {
                                echo "1";
                            } else {
                                echo "0";
                            } ?>">
                            <?php 
                        } ?>
                        <label for="wcbfc_international_order"><?php echo esc_html__( 'Check if the order originates from outside of your store\'s home country.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                        <div class="wcblu_rule_field">
                            <input name="wcbfc_international_order_weight" id="wcbfc_international_order_weight" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_international_order_weight ); ?>" class="" placeholder="" min="0" step="1" max="100">
                            <label><?php echo esc_html__( 'Rule Weight', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label><?php echo esc_html__( 'Is suspicious email domain?', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                    <?php
                        if ( empty( $wcbfc_suspecius_email ) && '' === $wcbfc_suspecius_email ) { ?>
                            <input checked type="checkbox" id="wcbfc_suspecius_email"
                                name="wcbfc_suspecius_email" value="">
                            <?php
                        } else { ?>
                            <input <?php if ( ! empty( $wcbfc_suspecius_email ) && '1' === $wcbfc_suspecius_email ) { ?> checked <?php } ?>
                                type="checkbox" id="wcbfc_suspecius_email"
                                name="wcbfc_suspecius_email" value="<?php
                            if ( ! empty( $wcbfc_suspecius_email ) && '1' === $wcbfc_suspecius_email ) {
                                echo "1";
                            } else {
                                echo "0";
                            } ?>">
                            <?php 
                        } ?>
                        <label for="wcbfc_suspecius_email"><?php echo esc_html__( 'Check if customer\'s email address originates from any high-risk domain listed below', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                        <div class="wcblu_rule_field">
                            <select id="wcbfc_suspecius_email_list"
                            data-placeholder="<?php esc_attr_e( 'Add emails separated by comma', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?>"
                            name="wcbfc_suspecius_email_list[]" multiple="true"
                            class="chosen-ss-select-email category-select chosen-rtl">
                                <option value=""></option>
                                <?php
                                if ( ! empty( $wcbfc_suspecius_email_list ) ) {
                                    if ( is_array( $wcbfc_suspecius_email_list ) ) {
                                        $final_email_merged_array_result = $wcbfc_suspecius_email_list;
                                    }
                                } else {
                                    $final_email_from_option = array('hotmail',
                                    'live',
                                    'gmail',
                                    'yahoo',
                                    'mail',
                                    '123vn',
                                    'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijk',
                                    'aaemail',
                                    'webmail',
                                    'postmaster',
                                    'personal',
                                    'atgratis',
                                    'aventuremail',
                                    'byke',
                                    'lycos',
                                    'computermail',
                                    'dodgeit',
                                    'thedoghousemail',
                                    'doramail',
                                    'e-mailanywhere',
                                    'yifan',
                                    'earthlink',
                                    'emailaccount',
                                    'zzn',
                                    'everymail',
                                    'excite',
                                    'expatmail',
                                    'fastmail',
                                    'flashmail',
                                    'fuzzmail',
                                    'galacmail',
                                    'godmail',
                                    'gurlmail',
                                    'howlermonkey',
                                    'hushmail',
                                    'icqmail',
                                    'indiatimes',
                                    'juno',
                                    'katchup',
                                    'kukamail',
                                    'mail',
                                    'mail2web',
                                    'mail2world',
                                    'mailandnews',
                                    'mailinator',
                                    'mauimail',
                                    'meowmail',
                                    'merawalaemail',
                                    'muchomail',
                                    'MyPersonalEmail',
                                    'myrealbox',
                                    'nameplanet',
                                    'netaddress',
                                    'nz11',
                                    'orgoo',
                                    'phat',
                                    'probemail',
                                    'prontomail',
                                    'rediff',
                                    'returnreceipt',
                                    'synacor',
                                    'walkerware',
                                    'walla',
                                    'wongfaye',
                                    'xasamail',
                                    'zapak',
                                    'zappo');
                                    $final_email_merged_array_result = $final_email_from_option;
                                }
                                if ( ! empty( $final_email_merged_array_result ) ) {
                                    if ( is_array( $final_email_merged_array_result ) ) {
                                        foreach ( $final_email_merged_array_result as $key => $email ) {
                                            ?>
                                             <option value="<?php echo esc_attr( $email ); ?>" selected><?php esc_html_e( $email, 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></option>
                                             <?php
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="wcblu_rule_field">
                            <input name="wcbfc_suspecious_email_weight" id="wcbfc_suspecious_email_weight" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_suspecious_email_weight ); ?>" class="" placeholder="" min="0" step="1" max="100">
                            <label><?php echo esc_html__( 'Rule Weight', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label><?php echo esc_html__( 'Is order from high-risk country?', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                    <?php
                        if ( empty( $wcbfc_unsafe_countries ) && '' === $wcbfc_unsafe_countries ) { ?>
                            <input checked type="checkbox" id="wcbfc_unsafe_countries"
                                name="wcbfc_unsafe_countries" value="">
                            <?php
                        } else { ?>
                            <input <?php if ( ! empty( $wcbfc_unsafe_countries ) && '1' === $wcbfc_unsafe_countries ) { ?> checked <?php } ?>
                                type="checkbox" id="wcbfc_unsafe_countries"
                                name="wcbfc_unsafe_countries" value="<?php
                            if ( ! empty( $wcbfc_unsafe_countries ) && '1' === $wcbfc_unsafe_countries ) {
                                echo "1";
                            } else {
                                echo "0";
                            } ?>">
                            <?php 
                        } ?>
                        <label for="wcbfc_unsafe_countries"><?php echo esc_html__( 'Check if order originates from any country in the high-risk countries list below:', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                        <?php
                            $countries_obj   = new WC_Countries();
                            $countries       = $countries_obj->__get('countries');
                        ?>
                        <div class="wcblu_rule_field">
                            <select name="wcblu_define_unsafe_countries_list[]" id="wcblu_define_unsafe_countries_list" multiple class="chosen-ducl-select-countries chosen-rtl">
                                <?php foreach( $countries as $key => $value ){ ?>
                                <?php if( in_array( $key, $wcblu_define_unsafe_countries_list ) ){ ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" selected><?php echo esc_html( $value ); ?></option>
                                    <?php } else { ?>
                                        <option value="<?php echo esc_attr( $key ) ?>"><?php echo esc_html( $value ); ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="wcblu_rule_field">
                            <?php
                            if ( empty( $wcbfc_unsafe_countries_ip ) && '' === $wcbfc_unsafe_countries_ip ) { ?>
                                <input checked type="checkbox" id="wcbfc_unsafe_countries_ip"
                                    name="wcbfc_unsafe_countries_ip" value="">
                                <?php
                            } else { ?>
                                <input <?php if ( ! empty( $wcbfc_unsafe_countries_ip ) && '1' === $wcbfc_unsafe_countries_ip ) { ?> checked <?php } ?>
                                    type="checkbox" id="wcbfc_unsafe_countries_ip"
                                    name="wcbfc_unsafe_countries_ip" value="<?php
                                if ( ! empty( $wcbfc_unsafe_countries_ip ) && '1' === $wcbfc_unsafe_countries_ip ) {
                                    echo "1";
                                } else {
                                    echo "0";
                                } ?>">
                                <?php 
                            } ?>
                            <label for="wcbfc_unsafe_countries_ip"><?php echo esc_html__( 'Check high-risk countries using user IP.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                        </div>
                        <div class="wcblu_rule_field">
                            <input name="wcbfc_unsafe_countries_weight" id="wcbfc_unsafe_countries_weight" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_unsafe_countries_weight ); ?>" class="" placeholder="" min="0" step="1" max="100">
                            <label><?php echo esc_html__( 'Rule Weight', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label><?php echo esc_html__( 'Order amount above average?', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                    <?php
                        if ( empty( $wcbfc_order_avg_amount_check ) && '' === $wcbfc_order_avg_amount_check ) { ?>
                            <input checked type="checkbox" id="wcbfc_order_avg_amount_check"
                                name="wcbfc_order_avg_amount_check" value="">
                            <?php
                        } else { ?>
                            <input <?php if ( ! empty( $wcbfc_order_avg_amount_check ) && '1' === $wcbfc_order_avg_amount_check ) { ?> checked <?php } ?>
                                type="checkbox" id="wcbfc_order_avg_amount_check"
                                name="wcbfc_order_avg_amount_check" value="<?php
                            if ( ! empty( $wcbfc_order_avg_amount_check ) && '1' === $wcbfc_order_avg_amount_check ) {
                                echo "1";
                            } else {
                                echo "0";
                            } ?>">
                            <?php 
                        } ?>
                        <label for="wcbfc_order_avg_amount_check"><?php echo esc_html__( 'Check if order significantly exceeds the average order amount for your site', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                        <div class="wcblu_rule_field">
                            <input name="wcbfc_order_avg_amount_time_span" id="wcbfc_order_avg_amount_time_span" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_order_avg_amount_time_span ); ?>" class="" placeholder="" min="0" step="1">
                            <label><?php echo esc_html__( 'The amount over the average transaction value that will trigger the rule (expressed as a multiplier).', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                        <div class="wcblu_rule_field">
                            <input name="wcbfc_order_avg_amount_weight" id="wcbfc_order_avg_amount_weight" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_order_avg_amount_weight ); ?>" class="" placeholder="" min="0" step="1" max="100">
                            <label><?php echo esc_html__( 'Rule Weight', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label><?php echo esc_html__( 'Order exceeds maximum?', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                    <?php
                        if ( empty( $wcbfc_order_amount_check ) && '' === $wcbfc_order_amount_check ) { ?>
                            <input checked type="checkbox" id="wcbfc_order_amount_check" name="wcbfc_order_amount_check" value="">
                            <?php
                        } else { ?>
                            <input <?php if ( ! empty( $wcbfc_order_amount_check ) && '1' === $wcbfc_order_amount_check ) { ?> checked <?php } ?>
                                type="checkbox" id="wcbfc_order_amount_check" name="wcbfc_order_amount_check" value="<?php
                            if ( ! empty( $wcbfc_order_amount_check ) && '1' === $wcbfc_order_amount_check ) {
                                echo "1";
                            } else {
                                echo "0";
                            } ?>">
                            <?php 
                        } ?>
                        <label for="wcbfc_order_amount_check"><?php echo esc_html__( 'Confirm the total amount of the order does not exceed the maxmimum configured below', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                        <div class="wcblu_rule_field">
                            <input name="wcbfc_max_order_attempt_span" id="wcbfc_max_order_attempt_span" type="number" style="width: 8em;" value="<?php echo esc_attr( $wcbfc_max_order_attempt_span ); ?>" class="" placeholder="" min="0" >
                            <?php $currency = get_woocommerce_currency_symbol(); ?>
                            <label><?php echo esc_html__( 'Order amount limit ('.$currency.') - Total maximum order amount accepted.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                        <div class="wcblu_rule_field">
                            <input name="wcbfc_max_order_attempt_weight" id="wcbfc_max_order_attempt_weight" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_max_order_attempt_weight ); ?>" class="" placeholder="" min="0" step="1" max="100">
                            <label><?php echo esc_html__( 'Rule Weight', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="titledesc">
                        <label><?php echo esc_html__( 'Too many order attempts?', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                    </th>
                    <td>
                    <?php
                        if ( empty( $wcbfc_too_many_oa_check ) && '' === $wcbfc_too_many_oa_check ) { ?>
                            <input checked type="checkbox" id="wcbfc_too_many_oa_check" name="wcbfc_too_many_oa_check" value="">
                            <?php
                        } else { ?>
                            <input <?php if ( ! empty( $wcbfc_too_many_oa_check ) && '1' === $wcbfc_too_many_oa_check ) { ?> checked <?php } ?>
                                type="checkbox" id="wcbfc_too_many_oa_check" name="wcbfc_too_many_oa_check" value="<?php
                            if ( ! empty( $wcbfc_too_many_oa_check ) && '1' === $wcbfc_too_many_oa_check ) {
                                echo "1";
                            } else {
                                echo "0";
                            } ?>">
                            <?php 
                        } ?>
                        <label for="wcbfc_too_many_oa_check"><?php echo esc_html__( 'Check if customer attempts to make a purchase too many times within the time period configured below', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
                        <div class="wcblu_rule_field">
                            <input name="wcbfc_too_many_oats_attempt_span" id="wcbfc_too_many_oats_attempt_span" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_too_many_oats_attempt_span ); ?>" class="" placeholder="" min="0" >
                            <label><?php echo esc_html__( 'Time span (hours) to check', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                        <div class="wcblu_rule_field">
                            <input name="wcbfc_too_many_oatos_attempt_span" id="wcbfc_too_many_oatos_attempt_span" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_too_many_oatos_attempt_span ); ?>" class="" placeholder="" min="0" >
                            <label><?php echo esc_html__( 'Maximum number of orders that a user can make in the specified time span', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                        <div class="wcblu_rule_field">
                            <input name="wcbfc_too_many_oa_attempt_weight" id="wcbfc_too_many_oa_attempt_weight" type="number" style="width: 5em;" value="<?php echo esc_attr( $wcbfc_too_many_oa_attempt_weight ); ?>" class="" placeholder="" min="0" step="1" max="100">
                            <label><?php echo esc_html__( 'Rule Weight', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></label>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <p>
            <button type="submit" name="wcblu_gr_submit" class="button button-primary" value="Save Changes"><?php echo esc_html__( 'Save Changes', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ?></button>
        </p>
    </form>
</div>

</div>
</div>
</div>
</div>