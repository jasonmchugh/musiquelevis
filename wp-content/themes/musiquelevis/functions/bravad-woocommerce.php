<?php
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
    /**
     * Get current users preference
     * @return int
     */
    function bv_get_products_per_page(){

        global $woocommerce;

        $default = 24;
        $count = $default;
        $options = bv_get_products_per_page_options();

        // capture form data and store in session
        if(isset($_POST['bv-woocommerce-products-per-page'])){

            // set products per page from dropdown
            $products_max = intval($_POST['bv-woocommerce-products-per-page']);
            if($products_max != 0 && $products_max >= -1){

            	if(is_user_logged_in()){

            		$user_id = get_current_user_id();
    		    	$limit = get_user_meta( $user_id, '_product_per_page', true );

    		    	if(!$limit){
    		    		add_user_meta( $user_id, '_product_per_page', $products_max);
    		    	}else{
    		    		update_user_meta( $user_id, '_product_per_page', $products_max, $limit);
    		    	}
            	}

                $woocommerce->session->bv_product_per_page = $products_max;
                return $products_max;
            }
        }

        // load product limit from user meta
        if(is_user_logged_in() && !isset($woocommerce->session->bv_product_per_page)){

            $user_id = get_current_user_id();
            $limit = get_user_meta( $user_id, '_product_per_page', true );

            if(array_key_exists($limit, $options)){
                $woocommerce->session->bv_product_per_page = $limit;
                return $limit;
            }
        }

        // load product limit from session
        if(isset($woocommerce->session->bv_product_per_page)){

            // set products per page from woo session
            $products_max = intval($woocommerce->session->bv_product_per_page);
            if($products_max != 0 && $products_max >= -1){
                return $products_max;
            }
        }

        return $count;
    }
    add_filter('loop_shop_per_page','bv_get_products_per_page');

    /**
     * Fetch list of avaliable options
     * @return array
     */
    function bv_get_products_per_page_options(){
    	$options = apply_filters( 'bv_products_per_page', array(
    		15 => __('15', 'woocommerce'),
    		30 => __('30', 'woocommerce'),
    		45 => __('45', 'woocommerce'),
    		60 => __('60', 'woocommerce')
        ));

    	return $options;
    }


    /**
	 * Woocommerce Login Redirect
	 *
	 */
	add_filter('woocommerce_login_redirect', 'wc_login_redirect');

	function wc_login_redirect( $redirect_to ) {
	     $redirect_to = '/mon-compte';
	     return $redirect_to;
	}


	/**
	 * Enabling Woocommerce default Gallery options
	 *
	 */
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );


	/**
	 * Change the add to cart text on single product pages
	 */
	add_filter('woocommerce_product_single_add_to_cart_text', 'woo_custom_cart_button_text');
	function woo_custom_cart_button_text() {

		foreach( WC()->cart->get_cart() as $cart_item_key => $values ) {
			$product = $values['data'];

			if( $product->get_stock_quantity() > 1 ) {

			} else {
				if( get_the_ID() == $product->get_id() ) {
					echo '<span class="in-cart">' . __('Déjà dans votre panier', 'bravad') . '</span>';
					return;
				}
			}
		}

		return __('Ajouter au panier', 'bravad');
	}


	/**
	 * Change the add to cart text on product archives
	 */
	add_filter( 'woocommerce_product_add_to_cart_text', 'woo_archive_custom_cart_button_text' );
	function woo_archive_custom_cart_button_text() {

		foreach( WC()->cart->get_cart() as $cart_item_key => $values ) {
			$product = $values['data'];

			if( $product->get_stock_quantity() > 1 ) {

			} else {
				if( get_the_ID() == $product->get_id() ) {
					echo '<span class="in-cart">' . __('Déjà dans votre panier', 'bravad') . '</span>';
					return;
				}
			}
		}

		return __('Ajouter au panier', 'bravad');
	}


	/**
	 * Woocommerce Thumbnails size
	 */
	function bravad_woocommerce_image_dimensions() {
		global $pagenow;

		if ( ! isset( $_GET['activated'] ) || $pagenow != 'themes.php' ) {
			return;
		}
	  	$catalog = array(
			'width' 	=> '400',	// px
			'height'	=> '400',	// px
			'crop'		=> 1 		// true
		);
		$single = array(
			'width' 	=> '800',	// px
			'height'	=> '800',	// px
			'crop'		=> 1 		// true
		);
		$thumbnail = array(
			'width' 	=> '120',	// px
			'height'	=> '120',	// px
			'crop'		=> 0 		// false
		);
		// Image sizes
		update_option( 'shop_catalog_image_size', $catalog ); 		// Product category thumbs
		update_option( 'shop_single_image_size', $single ); 		// Single product image
		update_option( 'shop_thumbnail_image_size', $thumbnail ); 	// Image gallery thumbs
	}
	add_action( 'after_switch_theme', 'bravad_woocommerce_image_dimensions', 1 );


	/**
	 * Update Cart via Ajax
	 */
	add_action( 'wp_ajax_nopriv_load_woo_cart', 'load_woo_cart' );
	add_action( 'wp_ajax_load_woo_cart', 'load_woo_cart' );

	function load_woo_cart(){
		echo get_template_part( 'views/modules/cart-result' );
		die();
	}


	/**
	 * Quantity Selector
	 */
	add_filter( 'woocommerce_loop_add_to_cart_link', 'quantity_inputs_for_woocommerce_loop_add_to_cart_link', 10, 2 );
	function quantity_inputs_for_woocommerce_loop_add_to_cart_link( $html, $product ) {

		if( $product->get_stock_quantity() > 1 ) {
			$html = '<form action="' . esc_url( $product->add_to_cart_url() ) . '" class="cart" method="post" enctype="multipart/form-data">';
			$html .= woocommerce_quantity_input( array(), $product, false );
			$html .= '<button type="submit" class="button alt add_to_cart_button">' . esc_html( $product->add_to_cart_text() ) . '</button>';
			$html .= '</form>';
		}
		return $html;

	}

	/**
	 * Update order meta
	 */
	add_action( 'woocommerce_admin_order_item_headers', 'bravad_admin_order_item_headers' );

	function bravad_admin_order_item_headers() {
	    echo '<th>Succursale</th>';
	}

	add_action( 'woocommerce_admin_order_item_values', 'bravad_admin_order_item_values', 10, 3 );

	function bravad_admin_order_item_values($_product, $item, $item_id = null) {
		$succ = '';
		if ( isset( $_product ) ) {
			$succ = $_product->get_attribute( 'pa_succursale' );	
		}

	    echo '<td>' . $succ . '</td>';
	}

	/**
	 * Add recipient
	 */
	add_filter( 'woocommerce_email_recipient_new_order', 'bravad_conditional_email_recipient', 10, 2 );

	function bravad_conditional_email_recipient( $recipient, $order ) {
		$page = $_GET['page'] = isset( $_GET['page'] ) ? $_GET['page'] : '';
		if ( 'wc-settings' === $page ) {
			return $recipient; 
		}
		
		if ( ! $order instanceof WC_Order ) {
			return $recipient; 
		}

		// if ( current_user_can( 'manage_options' ) ) {
		// 	return;
		// }

		$recipients = explode( ',', $recipient );

		$items = $order->get_items();
		
		foreach ( $items as $item ) {
			$product = $order->get_product_from_item( $item );
			$succ = array_shift( wc_get_product_terms( $product->id, 'pa_succursale', array( 'fields' => 'slugs' ) ) );

			$retailer = get_post_by_slug( $succ );
			$post_id = $retailer->ID;

			$email = get_field( 'courriel', $post_id );

			// add our extra recipient if there's a shipped product - commas needed!
			// we can bail if we've found one, no need to add the recipient more than once
			$recipients[] = $email;

			// return $recipient;
		}
		
		return implode( ', ', array_unique( $recipients ) );
	}

	function get_post_by_slug( $slug ) {
	    $posts = get_posts( array(
			'name'           => $slug,
			'posts_per_page' => 1,
			'post_type'      => 'retailers',
			'post_status'    => 'publish'
	    ));
	    
	    return $posts[0];
	}

	add_filter( 'woocommerce_catalog_orderby', 'bravad_catalog_orderby' );

	function bravad_catalog_orderby( $options ) {
		unset( $options['rating'] );

		return $options;
	}

	add_filter( 'woocommerce_default_catalog_orderby', 'misha_default_catalog_orderby' );

	function misha_default_catalog_orderby( $sort_by ) {
		return 'rand';
	}


?>