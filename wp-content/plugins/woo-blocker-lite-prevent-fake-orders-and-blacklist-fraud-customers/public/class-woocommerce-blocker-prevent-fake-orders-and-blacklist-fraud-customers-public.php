<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.multidots.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers
 * @subpackage Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers/public
 */
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers
 * @subpackage Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers/public
 * @author     multidots <info@multidots.in>
 */
class Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Public
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private  $plugin_name ;
    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private  $version ;
    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version     The version of this plugin.
     *
     * @since    1.0.0
     *
     */
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }
    
    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        /**`
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Woocommerce_Blocker_Prevent_Fake_Orders_And_Blacklist_Fraud_Customers_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        
        if ( is_checkout() ) {
            wp_enqueue_style(
                $this->plugin_name . '-public',
                plugin_dir_url( __FILE__ ) . 'css/woocommerce-blocker-prevent-fake-orders-and-blacklist-fraud-customers-public.css',
                array(),
                $this->version,
                'all'
            );
            $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' );
            wp_enqueue_script(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'js/woocommerce-blocker-prevent-fake-orders-and-blacklist-fraud-customers-public' . $suffix . '.js',
                array( 'jquery' ),
                $this->version,
                false
            );
        }
    
    }
    
    // woocommerce checkout page functionality
    /**
     * Function to return email and domain validation
     */
    public function woo_email_domain_validation()
    {
        global  $wpdb ;
        $getpluginoption = get_option( 'wcblu_option' );
        $getpluginoptionarray = json_decode( $getpluginoption, true );
        $getplaceordertype = ( !empty($getpluginoptionarray['wcblu_place_order_type']) ? $getpluginoptionarray['wcblu_place_order_type'] : '' );
        $getaddresstype = ( !empty($getpluginoptionarray['wcblu_address_type']) ? $getpluginoptionarray['wcblu_address_type'] : '' );
        $wc_first_name_relation = ( !empty($getpluginoptionarray['wcblu_first_name_relation']) ? $getpluginoptionarray['wcblu_first_name_relation'] : '' );
        $wc_last_name_relation = ( !empty($getpluginoptionarray['wcblu_last_name_relation']) ? $getpluginoptionarray['wcblu_last_name_relation'] : '' );
        $flagForEnterUserToBannedList = 0;
        $email = '';
        
        if ( isset( $getplaceordertype ) && !empty($getplaceordertype) && '1' === $getplaceordertype ) {
            // return if billing email is empty
            $billing_email = ( isset( $_POST['billing_email'] ) && !empty($_POST['billing_email']) && !wp_verify_nonce( sanitize_email( $_POST['billing_email'] ), 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) ? sanitize_email( $_POST['billing_email'] ) : '' );
            if ( !$billing_email ) {
                wc_add_notice( esc_html__( 'Please add email address to place order', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ), 'error' );
            }
            $email = trim( $billing_email );
            // return if billing email is unvalid
            if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
                wc_add_notice( esc_html__( 'Please add valid email address to place order', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ), 'error' );
            }
            // validate billing email
            $errorEmail = $this->verify_email( $email );
            
            if ( $errorEmail ) {
                wc_add_notice( $errorEmail, 'error' );
                $flagForEnterUserToBannedList = 1;
            }
            
            // validate email domain
            
            if ( !empty($email) ) {
                $errorDomain = $this->verify_domain( $email );
                
                if ( $errorDomain ) {
                    wc_add_notice( $errorDomain, 'error' );
                    $flagForEnterUserToBannedList = 1;
                }
            
            }
            
            // validate IP (Test if it is a shared client)
            $http_client_ip = filter_input( INPUT_SERVER, 'HTTP_CLIENT_IP', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $http_x_forwarded_for = filter_input( INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $remote_addr = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $http_client_ip = filter_var( $http_client_ip, FILTER_VALIDATE_IP );
            $http_x_forwarded_for = filter_var( $http_x_forwarded_for, FILTER_VALIDATE_IP );
            $remote_addr = filter_var( $remote_addr, FILTER_VALIDATE_IP );
            
            if ( !empty($http_client_ip) ) {
                $ip = $http_client_ip;
                //Is it a proxy address
            } elseif ( !empty($http_x_forwarded_for) ) {
                $ip = $http_x_forwarded_for;
            } else {
                $ip = $remote_addr;
            }
            
            $errorIp = $this->verify_ip( $ip );
            
            if ( $errorIp ) {
                wc_add_notice( $errorIp, 'error' );
                $flagForEnterUserToBannedList = 1;
            }
            
            
            if ( isset( $ship_to_different_address ) && "1" !== $ship_to_different_address || "shipping_address_type" !== $getaddresstype ) {
                $state = filter_input( INPUT_POST, 'billing_state', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                $country = filter_input( INPUT_POST, 'billing_country', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                // validate billing state
                $errorState = '';
                if ( isset( $state ) && !empty($state) ) {
                    $errorState = $this->verify_state( $country, $state );
                }
                
                if ( $errorState ) {
                    wc_add_notice( $errorState, 'error' );
                    $flagForEnterUserToBannedList = 1;
                }
                
                // validate billing zip
                $zip = filter_input( INPUT_POST, 'billing_postcode', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                $errorzip = '';
                if ( isset( $zip ) && !empty($zip) ) {
                    $errorzip = $this->verify_zip( $zip );
                }
                
                if ( $errorzip ) {
                    wc_add_notice( $errorzip, 'error' );
                    $flagForEnterUserToBannedList = 1;
                }
            
            }
            
            // end here
        } else {
            $createaccount = filter_input( INPUT_POST, 'createaccount', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            
            if ( '1' === $createaccount ) {
                // return if billing email doesn't exists
                $billing_email = filter_input( INPUT_POST, 'billing_email', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                if ( !$billing_email ) {
                    return;
                }
                $email = trim( $billing_email );
                // return if billing email is unvalid
                if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
                    return;
                }
                $errorEmail = $this->verify_email( $email );
                
                if ( $errorEmail ) {
                    wc_add_notice( $errorEmail, 'error' );
                    $flagForEnterUserToBannedList = 1;
                }
                
                // validate email domain
                
                if ( !empty($email) ) {
                    $errorDomain = $this->verify_domain( $email );
                    
                    if ( $errorDomain ) {
                        wc_add_notice( $errorDomain, 'error' );
                        $flagForEnterUserToBannedList = 1;
                    }
                
                }
                
                //Test if it is a shared client
                $http_client_ip = filter_input( INPUT_SERVER, 'HTTP_CLIENT_IP', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                $http_x_forwarded_for = filter_input( INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                $remote_addr = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                $http_client_ip = filter_var( $http_client_ip, FILTER_VALIDATE_IP );
                $http_x_forwarded_for = filter_var( $http_x_forwarded_for, FILTER_VALIDATE_IP );
                $remote_addr = filter_var( $remote_addr, FILTER_VALIDATE_IP );
                
                if ( !empty($http_client_ip) ) {
                    $ip = $http_client_ip;
                    //Is it a proxy address
                } elseif ( !empty($http_x_forwarded_for) ) {
                    $ip = $http_x_forwarded_for;
                } else {
                    $ip = $remote_addr;
                }
                
                $errorIp = $this->verify_ip( $ip );
                
                if ( $errorIp ) {
                    wc_add_notice( $errorIp, 'error' );
                    $flagForEnterUserToBannedList = 1;
                }
                
                $state = filter_input( INPUT_POST, 'billing_state', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                $country = filter_input( INPUT_POST, 'billing_country', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                if ( isset( $state ) && !empty($state) ) {
                    $errorState = $this->verify_state( $country, $state );
                }
                
                if ( $errorState ) {
                    wc_add_notice( $errorState, 'error' );
                    $flagForEnterUserToBannedList = 1;
                }
                
                $zip = filter_input( INPUT_POST, 'billing_postcode', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                if ( isset( $zip ) && !empty($zip) ) {
                    $errorzip = $this->verify_zip( $zip );
                }
                
                if ( $errorzip ) {
                    wc_add_notice( $errorzip, 'error' );
                    $flagForEnterUserToBannedList = 1;
                }
                
                // end here
            }
        
        }
        
        
        if ( 1 === $flagForEnterUserToBannedList ) {
            $billing_email = filter_input( INPUT_POST, 'billing_email', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $email = trim( $billing_email );
            $query = wp_cache_get( 'blocked_user_data_key' );
            
            if ( false === $query ) {
                $args = array(
                    'post_status' => 'publish',
                    'post_type'   => 'blocked_user',
                    's'           => $email,
                );
                $args_query = new WP_Query( $args );
                
                if ( !empty($args_query->posts) ) {
                    $get_posts = $args_query->posts;
                    $query = $get_posts[0]->ID;
                }
                
                wp_cache_set( 'blocked_user_data_key', $query );
            }
            
            $query = wp_cache_get( 'blocked_user_data_key' );
            
            if ( false !== $query ) {
                $post_id = wp_cache_get( 'blocked_user_data_key_for_post_id' );
                
                if ( false === $post_id ) {
                    $args_for_post_id = array(
                        'post_status' => 'publish',
                        'post_type'   => 'blocked_user',
                        's'           => $email,
                    );
                    $args_for_post_id_query = new WP_Query( $args_for_post_id );
                    
                    if ( !empty($args_for_post_id_query->posts) ) {
                        $get_posts = $args_for_post_id_query->posts;
                        $post_id = $get_posts[0]->ID;
                    }
                    
                    wp_cache_set( 'blocked_user_data_key_for_post_id', $post_id );
                }
                
                $meta = get_post_meta( $post_id, 'Attempt', true );
                $meta++;
                update_post_meta( $post_id, 'Attempt', $meta );
                update_post_meta( $post_id, 'First Name', filter_input( INPUT_POST, 'billing_first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'Last Name', filter_input( INPUT_POST, 'billing_last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'City', filter_input( INPUT_POST, 'billing_city', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'Country', filter_input( INPUT_POST, 'billing_country', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'Phone', filter_input( INPUT_POST, 'billing_phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'Company', filter_input( INPUT_POST, 'billing_company', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'Postcode', filter_input( INPUT_POST, 'billing_postcode', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'Address 1', filter_input( INPUT_POST, 'billing_address_1', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'Address 2', filter_input( INPUT_POST, 'billing_address_2', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'State', filter_input( INPUT_POST, 'billing_state', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'WhereUserBanned', 'Place Order' );
                $post_status = get_post_status( $post_id );
                if ( 'trash' === $post_status ) {
                    wp_update_post( array(
                        'ID'          => $post_id,
                        'post_status' => 'publish',
                    ) );
                }
            } else {
                $user = array(
                    'post_title'  => $email,
                    'post_status' => 'publish',
                    'post_type'   => 'blocked_user',
                );
                $post_id = wp_insert_post( $user );
                update_post_meta( $post_id, 'First Name', filter_input( INPUT_POST, 'billing_first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'Last Name', filter_input( INPUT_POST, 'billing_last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'City', filter_input( INPUT_POST, 'billing_city', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'Country', filter_input( INPUT_POST, 'billing_country', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'Phone', filter_input( INPUT_POST, 'billing_phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'Company', filter_input( INPUT_POST, 'billing_company', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'Postcode', filter_input( INPUT_POST, 'billing_postcode', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'Address 1', filter_input( INPUT_POST, 'billing_address_1', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'Address 2', filter_input( INPUT_POST, 'billing_address_2', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'State', filter_input( INPUT_POST, 'billing_state', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
                update_post_meta( $post_id, 'Attempt', '1' );
                update_post_meta( $post_id, 'WhereUserBanned', 'Place Order' );
            }
            
            //compatible with WooCommerce PayPal Payments Plugin Start
            
            if ( is_plugin_active( 'woocommerce-paypal-payments/woocommerce-paypal-payments.php' ) ) {
                $reset_paypal_obj = new WooCommerce\PayPalCommerce\Session\SessionHandler();
                if ( !isset( $reset_paypal_obj ) || empty($reset_paypal_obj->order()) ) {
                    WC()->session->set( 'reload_checkout', 'true' );
                }
                WC()->session->set( 'ppcp', $reset_paypal_obj );
            }
            
            //compatible with WooCommerce PayPal Payments Plugin End
        }
    
    }
    
    /**
     * @param $email
     *
     * @return string
     * function to return error notice if blacklisted, otherwise false
     */
    private function verify_email( $email )
    {
        $getpluginoption = get_option( 'wcblu_option' );
        $getpluginoptionarray = json_decode( $getpluginoption, true );
        $status = '';
        //get blacklisted domains from database
        $fetchSelectedEmail = ( !empty($getpluginoptionarray['wcblu_block_email']) ? $getpluginoptionarray['wcblu_block_email'] : '' );
        
        if ( '' !== $fetchSelectedEmail ) {
            // check if user email is blacklisted
            if ( is_array( $fetchSelectedEmail ) ) {
                foreach ( $fetchSelectedEmail as $blacklist ) {
                    $blacklist = strtolower( $blacklist );
                    $email = strtolower( $email );
                    
                    if ( $blacklist === $email ) {
                        $status = convert_smilies( ( empty($getpluginoptionarray['wcblu_email_msg']) ? __( 'This email has been blacklisted. Please try another email address.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) : $getpluginoptionarray['wcblu_email_msg'] ) );
                        break;
                    }
                
                }
            }
        } else {
            $status = '';
        }
        
        return $status;
    }
    
    /**
     * @param $email
     *
     * @return string
     * Function to return validate domain
     */
    private function verify_domain( $email )
    {
        // store only the domain part of email address
        $email = explode( '@', $email );
        $email = $email[1];
        $getpluginoption = get_option( 'wcblu_option' );
        $getpluginoptionarray = json_decode( $getpluginoption, true );
        $status = '';
        //get blacklisted domains from database
        $fetchSelecetedDomain = ( !empty($getpluginoptionarray['wcblu_block_domain']) ? $getpluginoptionarray['wcblu_block_domain'] : array() );
        
        if ( '' !== $fetchSelecetedDomain ) {
            // add external domains
            
            if ( !empty($getpluginoptionarray['wcblu_enable_ext_bl']) && '1' === $getpluginoptionarray['wcblu_enable_ext_bl'] ) {
                $external_list = $this->get_external_blacklisted_domains__premium_only();
                
                if ( !empty($external_list) && '' !== $external_list ) {
                    $blacklists = array_merge( $fetchSelecetedDomain, $external_list );
                } else {
                    $blacklists = array();
                }
            
            } else {
                $blacklists = $fetchSelecetedDomain;
            }
            
            // check if user email is blacklisted
            if ( is_array( $blacklists ) ) {
                foreach ( $blacklists as $blacklist ) {
                    $blacklist = strtolower( $blacklist );
                    $email = strtolower( $email );
                    $exEmail = explode( ".", $email );
                    $fEmail = $exEmail[count( $exEmail ) - 2] . "." . $exEmail[count( $exEmail ) - 1];
                    
                    if ( $fEmail === $blacklist ) {
                        $status = convert_smilies( ( empty($getpluginoptionarray['wcblu_domain_msg']) ? __( 'This email domain has been blacklisted. Please try another email address', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) : $getpluginoptionarray['wcblu_domain_msg'] ) );
                        break;
                    }
                
                }
            }
        } else {
            // add external domains
            
            if ( !empty($getpluginoptionarray['wcblu_enable_ext_bl']) && '1' === $getpluginoptionarray['wcblu_enable_ext_bl'] ) {
                $external_list = $this->get_external_blacklisted_domains__premium_only();
                // check if user email is blacklisted
                if ( is_array( $external_list ) ) {
                    foreach ( $external_list as $blacklist ) {
                        $blacklist = strtolower( $blacklist );
                        $email = strtolower( $email );
                        $exEmail = explode( ".", $email );
                        $fEmail = $exEmail[count( $exEmail ) - 2] . "." . $exEmail[count( $exEmail ) - 1];
                        
                        if ( $email === $blacklist ) {
                            $status = convert_smilies( ( empty($getpluginoptionarray['wcblu_domain_msg']) ? __( 'This email domain has been blacklisted. Please try another email address', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) : $getpluginoptionarray['wcblu_domain_msg'] ) );
                            break;
                        }
                    
                    }
                }
            } else {
                $status = '';
            }
        
        }
        
        return $status;
    }
    
    /**
     * @param $ip
     *
     * @return string
     * Function to return verify ip address
     */
    private function verify_ip( $ip )
    {
        $getpluginoption = get_option( 'wcblu_option' );
        $getpluginoptionarray = json_decode( $getpluginoption, true );
        $status = '';
        //get blacklisted domains from database
        $fetchSelectedIpAddress = ( !empty($getpluginoptionarray['wcblu_block_ip']) ? $getpluginoptionarray['wcblu_block_ip'] : array() );
        
        if ( '' !== $fetchSelectedIpAddress ) {
            if ( is_array( $fetchSelectedIpAddress ) ) {
                foreach ( $fetchSelectedIpAddress as $ipSingle ) {
                    /**
                     * Check the IP range validation
                     */
                    
                    if ( strpos( $ipSingle, '-' ) !== false ) {
                        $customer_ip_array = explode( ', ', $ip );
                        if ( !empty($customer_ip_array) && is_array( $customer_ip_array ) ) {
                            foreach ( $customer_ip_array as $ip_val ) {
                                $customer_ip_slots_array = explode( '.', $ip_val );
                                /**
                                 * Create IP range array
                                 */
                                $saved_ip_range_array = explode( '-', $ipSingle );
                                /**
                                 * Create IP slot array
                                 */
                                $saved_ip_start_range_array = explode( '.', $saved_ip_range_array[0] );
                                $saved_ip_end_range_array = explode( '.', $saved_ip_range_array[1] );
                                /**
                                 * Here checking the first three slot of the IP is same or not and then
                                 * checking the IP last slot range is in between or not
                                 */
                                
                                if ( $saved_ip_start_range_array[0] === $customer_ip_slots_array[0] && $saved_ip_start_range_array[1] === $customer_ip_slots_array[1] && $saved_ip_start_range_array[2] === $customer_ip_slots_array[2] && ($saved_ip_start_range_array[3] <= $customer_ip_slots_array[3] && $saved_ip_end_range_array[3] >= $customer_ip_slots_array[3]) ) {
                                    $status = convert_smilies( ( empty($getpluginoptionarray['wcblu_ip_msg']) ? __( 'Your IP address has been blacklisted.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) : $getpluginoptionarray['wcblu_ip_msg'] ) );
                                    break;
                                }
                            
                            }
                        }
                    } else {
                        /** IP is not in range then go for simple checking */
                        
                        if ( strpos( $ip, ',' ) !== false ) {
                            $network_id_array = explode( ', ', $ip );
                            if ( is_array( $network_id_array ) ) {
                                foreach ( $network_id_array as $network_ip ) {
                                    
                                    if ( $ipSingle === $network_ip ) {
                                        $status = convert_smilies( ( empty($getpluginoptionarray['wcblu_ip_msg']) ? __( 'Your IP address has been blacklisted.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) : $getpluginoptionarray['wcblu_ip_msg'] ) );
                                        break;
                                    }
                                
                                }
                            }
                        } else {
                            
                            if ( $ipSingle === $ip ) {
                                $status = convert_smilies( ( empty($getpluginoptionarray['wcblu_ip_msg']) ? __( 'Your IP address has been blacklisted.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) : $getpluginoptionarray['wcblu_ip_msg'] ) );
                                break;
                            }
                        
                        }
                    
                    }
                
                }
            }
        } else {
            $status = '';
        }
        
        return $status;
    }
    
    /**
     * @param $country
     * @param $state
     *
     * @return string
     * function to retirn verify state
     */
    private function verify_state( $country, $state )
    {
        $getpluginoption = get_option( 'wcblu_option' );
        $getpluginoptionarray = json_decode( $getpluginoption, true );
        $status = '';
        //get blacklisted domains from database
        $fetchSelecetedState = ( !empty($getpluginoptionarray['wcblu_block_state']) ? array_filter( $getpluginoptionarray['wcblu_block_state'] ) : '' );
        
        if ( isset( $fetchSelecetedState ) && !empty($fetchSelecetedState) ) {
            $stateFullName = strtolower( WC()->countries->states[$country][$state] );
            if ( is_array( $fetchSelecetedState ) ) {
                foreach ( $fetchSelecetedState as $singleList ) {
                    $singleList = strtolower( $singleList );
                    $stateFullName = strtolower( $stateFullName );
                    
                    if ( $stateFullName === $singleList ) {
                        $status = convert_smilies( ( empty($getpluginoptionarray['wcblu_state_msg']) ? __( 'Your State has been blacklisted.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) : $getpluginoptionarray['wcblu_state_msg'] ) );
                        break;
                    }
                
                }
            }
        } else {
            $status = '';
        }
        
        return $status;
    }
    
    /**
     * @param $zip
     *
     * @return string
     * function to return verify zip
     */
    private function verify_zip( $zip )
    {
        $getpluginoption = get_option( 'wcblu_option' );
        $getpluginoptionarray = json_decode( $getpluginoption, true );
        $status = '';
        $fetchSelecetedZip = ( !empty($getpluginoptionarray['wcblu_block_zip']) ? $getpluginoptionarray['wcblu_block_zip'] : '' );
        
        if ( isset( $fetchSelecetedZip ) && !empty($fetchSelecetedZip) ) {
            if ( is_array( $fetchSelecetedZip ) ) {
                foreach ( $fetchSelecetedZip as $singleZip ) {
                    $zip = strtolower( $zip );
                    $singleZip = strtolower( $singleZip );
                    
                    if ( $singleZip === $zip ) {
                        $status = convert_smilies( ( empty($getpluginoptionarray['wcblu_zpcode_msg']) ? __( 'Your Zip has been blacklisted.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) : $getpluginoptionarray['wcblu_zpcode_msg'] ) );
                        break;
                    }
                
                }
            }
        } else {
            $status = '';
        }
        
        return $status;
    }
    
    /**
     * @param $validation_error
     * @param $username
     * @param $password
     * @param $email
     * function to return valudate extra field for registration
     */
    public function wooc_validate_extra_register_fields(
        $validation_error,
        $username,
        $password,
        $email
    )
    {
        global  $wpdb ;
        $getpluginoption = get_option( 'wcblu_option' );
        $getpluginoptionarray = json_decode( $getpluginoption, true );
        $getregistertype = ( !empty($getpluginoptionarray['wcblu_register_type']) ? $getpluginoptionarray['wcblu_register_type'] : '' );
        
        if ( isset( $getregistertype ) && !empty($getregistertype) && '1' === $getregistertype ) {
            $flagForEnterUserToBannedList = 0;
            //Test if it is a shared client
            $http_client_ip = filter_input( INPUT_SERVER, 'HTTP_CLIENT_IP', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $http_x_forwarded_for = filter_input( INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $remote_addr = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $http_client_ip = filter_var( $http_client_ip, FILTER_VALIDATE_IP );
            $http_x_forwarded_for = filter_var( $http_x_forwarded_for, FILTER_VALIDATE_IP );
            $remote_addr = filter_var( $remote_addr, FILTER_VALIDATE_IP );
            
            if ( !empty($http_client_ip) ) {
                $ip = $http_client_ip;
                //Is it a proxy address
            } elseif ( !empty($http_x_forwarded_for) ) {
                $ip = $http_x_forwarded_for;
            } else {
                $ip = $remote_addr;
            }
            
            $errorIp = $this->verify_ip_register( $ip );
            
            if ( $errorIp ) {
                $validation_error_msg = ( empty($getpluginoptionarray['wcblu_ip_msg']) ? __( 'Your IP address has been blacklisted.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) : $getpluginoptionarray['wcblu_ip_msg'] );
                $validation_error->add( 'error', $validation_error_msg );
                $flagForEnterUserToBannedList = 1;
            }
            
            $email = filter_input( INPUT_POST, 'email', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            // return if billing email doesn't exists
            
            if ( !$email ) {
                $validation_error_msg = __( 'Please enter email address.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
                $validation_error->add( 'error', $validation_error_msg );
                return $validation_error;
            }
            
            $email = trim( $email );
            // return if billing email is unvalid
            
            if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
                $validation_error_msg = __( 'Please enter valid email address', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' );
                $validation_error->add( 'error', $validation_error_msg );
                return $validation_error;
            }
            
            $errorEmail = $this->verify_email_register( $email );
            
            if ( $errorEmail ) {
                $validation_error_msg = ( empty($getpluginoptionarray['wcblu_email_msg']) ? __( 'This email has been blacklisted. Please try another email address.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) : $getpluginoptionarray['wcblu_email_msg'] );
                $validation_error->add( 'error', $validation_error_msg );
                $flagForEnterUserToBannedList = 1;
            }
            
            $errorDomain = $this->verify_domain_register( $email );
            
            if ( $errorDomain ) {
                $validation_error_msg = ( empty($getpluginoptionarray['wcblu_domain_msg']) ? __( 'This email domain has been blacklisted. Please try another email address', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) : $getpluginoptionarray['wcblu_domain_msg'] );
                $validation_error->add( 'error', $validation_error_msg );
                $flagForEnterUserToBannedList = 1;
            }
            
            
            if ( 1 === $flagForEnterUserToBannedList ) {
                $query = wp_cache_get( 'blocked_another_user_data_key' );
                
                if ( false === $query ) {
                    $args = array(
                        'post_status' => 'publish',
                        'post_type'   => 'blocked_user',
                        's'           => $email,
                    );
                    $args_query = new WP_Query( $args );
                    
                    if ( !empty($args_query->posts) ) {
                        $get_posts = $args_query->posts;
                        $query = $get_posts[0]->ID;
                    }
                    
                    wp_cache_set( 'blocked_another_user_data_key', $query );
                }
                
                $query = wp_cache_get( 'blocked_another_user_data_key' );
                
                if ( false !== $query ) {
                    $post_id = wp_cache_get( 'blocked_another_user_data_key_for_post_id' );
                    
                    if ( false === $post_id ) {
                        $args_for_post_id = array(
                            'post_status' => 'publish',
                            'post_type'   => 'blocked_user',
                            's'           => $email,
                        );
                        $args_for_post_id_query = new WP_Query( $args_for_post_id );
                        
                        if ( !empty($args_for_post_id_query->posts) ) {
                            $get_posts = $args_for_post_id_query->posts;
                            $post_id = $get_posts[0]->ID;
                        }
                        
                        wp_cache_set( 'blocked_another_user_data_key_for_post_id', $post_id );
                    }
                    
                    $meta = get_post_meta( $post_id, 'Attempt', true );
                    $meta++;
                    update_post_meta( $post_id, 'Attempt', $meta );
                    update_post_meta( $post_id, 'WhereUserBanned', 'Register' );
                } else {
                    $user = array(
                        'post_title'  => $email,
                        'post_status' => 'publish',
                        'post_type'   => 'blocked_user',
                    );
                    $post_id = wp_insert_post( $user );
                    update_post_meta( $post_id, 'Attempt', '1' );
                    update_post_meta( $post_id, 'WhereUserBanned', 'Register' );
                }
            
            }
        
        }
        
        return $validation_error;
    }
    
    /**
     * @param $ip
     *
     * @return string|void
     * function to return verify ip for registration.
     */
    private function verify_ip_register( $ip )
    {
        $getpluginoption = get_option( 'wcblu_option' );
        $getpluginoptionarray = json_decode( $getpluginoption, true );
        $status = '';
        //get blacklisted domains from database
        $fetchSelectedIpAddress = ( !empty($getpluginoptionarray['wcblu_block_ip']) ? $getpluginoptionarray['wcblu_block_ip'] : array() );
        
        if ( '' !== $fetchSelectedIpAddress ) {
            if ( is_array( $fetchSelectedIpAddress ) ) {
                foreach ( $fetchSelectedIpAddress as $ipSingle ) {
                    if ( $ip === $ipSingle ) {
                        $status = ( empty($getpluginoptionarray['wcblu_ip_msg']) ? __( 'Your IP address has been blacklisted.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) : $getpluginoptionarray['wcblu_ip_msg'] );
                    }
                }
            }
        } else {
            $status = '';
        }
        
        return $status;
    }
    
    /**
     * @param $email
     *
     * @return string|void
     * function to return verify emails in registration
     */
    private function verify_email_register( $email )
    {
        $getpluginoption = get_option( 'wcblu_option' );
        $getpluginoptionarray = json_decode( $getpluginoption, true );
        $status = '';
        //get blacklisted domains from database
        $fetchSelectedEmail = ( !empty($getpluginoptionarray['wcblu_block_email']) ? $getpluginoptionarray['wcblu_block_email'] : '' );
        
        if ( '' !== $fetchSelectedEmail ) {
            if ( is_array( $fetchSelectedEmail ) ) {
                // check if user email is blacklisted
                foreach ( $fetchSelectedEmail as $blacklist ) {
                    $blacklist = strtolower( $blacklist );
                    $email = strtolower( $email );
                    if ( $email === $blacklist ) {
                        $status = ( empty($getpluginoptionarray['wcblu_email_msg']) ? __( 'This email has been blacklisted. Please try another email address.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) : $getpluginoptionarray['wcblu_email_msg'] );
                    }
                }
            }
        } else {
            $status = '';
        }
        
        return $status;
    }
    
    public function get_client_ip()
    {
        $ipaddress = '';
        $HTTP_CLIENT_IP = filter_input( INPUT_SERVER, 'HTTP_CLIENT_IP', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $HTTP_X_FORWARDED_FOR = filter_input( INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $HTTP_X_FORWARDED = filter_input( INPUT_SERVER, 'HTTP_X_FORWARDED', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $HTTP_FORWARDED_FOR = filter_input( INPUT_SERVER, 'HTTP_FORWARDED_FOR', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $HTTP_FORWARDED = filter_input( INPUT_SERVER, 'HTTP_FORWARDED', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $REMOTE_ADDR = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        
        if ( isset( $HTTP_CLIENT_IP ) ) {
            $ipaddress = $HTTP_CLIENT_IP;
        } else {
            
            if ( isset( $HTTP_X_FORWARDED_FOR ) ) {
                $ipaddress = $HTTP_X_FORWARDED_FOR;
            } else {
                
                if ( isset( $HTTP_X_FORWARDED ) ) {
                    $ipaddress = $HTTP_X_FORWARDED;
                } else {
                    
                    if ( isset( $HTTP_FORWARDED_FOR ) ) {
                        $ipaddress = $HTTP_FORWARDED_FOR;
                    } else {
                        
                        if ( isset( $HTTP_FORWARDED ) ) {
                            $ipaddress = $HTTP_FORWARDED;
                        } else {
                            
                            if ( isset( $REMOTE_ADDR ) ) {
                                $ipaddress = $REMOTE_ADDR;
                            } else {
                                $ipaddress = 'UNKNOWN';
                            }
                        
                        }
                    
                    }
                
                }
            
            }
        
        }
        
        return $ipaddress;
    }
    
    /**
     * @param $email
     *
     * @return string|void
     * function to return verify domain for registration
     */
    private function verify_domain_register( $email )
    {
        // store only the domain part of email address
        $email = explode( '@', $email );
        $email = $email[1];
        $getpluginoption = get_option( 'wcblu_option' );
        $getpluginoptionarray = json_decode( $getpluginoption, true );
        //get blacklisted domains from database
        $fetchSelecetedDomain = ( !empty($getpluginoptionarray['wcblu_block_domain']) ? $getpluginoptionarray['wcblu_block_domain'] : array() );
        $status = '';
        
        if ( '' !== $fetchSelecetedDomain ) {
            // add external domains
            
            if ( !empty($getpluginoptionarray['wcblu_enable_ext_bl']) && '1' === $getpluginoptionarray['wcblu_enable_ext_bl'] ) {
                $external_list = $this->get_external_blacklisted_domains__premium_only();
                $blacklists = array_merge( $fetchSelecetedDomain, $external_list );
            } else {
                $blacklists = $fetchSelecetedDomain;
            }
            
            // check if user email is blacklisted
            if ( is_array( $blacklists ) ) {
                foreach ( $blacklists as $blacklist ) {
                    $blacklist = strtolower( $blacklist );
                    $email = strtolower( $email );
                    $exEmail = explode( ".", $email );
                    $fEmail = $exEmail[count( $exEmail ) - 2] . "." . $exEmail[count( $exEmail ) - 1];
                    if ( $email === $blacklist ) {
                        $status = ( empty($getpluginoptionarray['wcblu_domain_msg']) ? __( 'This email domain has been blacklisted. Please try another email address', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) : $getpluginoptionarray['wcblu_domain_msg'] );
                    }
                }
            }
        } else {
            // add external domains
            
            if ( !empty($getpluginoptionarray['wcblu_enable_ext_bl']) && '1' === $getpluginoptionarray['wcblu_enable_ext_bl'] ) {
                $external_list = $this->get_external_blacklisted_domains__premium_only();
                // check if user email is blacklisted
                if ( is_array( $external_list ) ) {
                    foreach ( $external_list as $blacklist ) {
                        $blacklist = strtolower( $blacklist );
                        $email = strtolower( $email );
                        $exEmail = explode( ".", $email );
                        $fEmail = $exEmail[count( $exEmail ) - 2] . "." . $exEmail[count( $exEmail ) - 1];
                        if ( $email === $blacklist ) {
                            $status = ( empty($getpluginoptionarray['wcblu_domain_msg']) ? __( 'This email domain has been blacklisted. Please try another email address', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ) : $getpluginoptionarray['wcblu_domain_msg'] );
                        }
                    }
                }
            } else {
                $status = '';
            }
        
        }
        
        return $status;
    }

}