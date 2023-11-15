<?php

/**

 * Bravad engine room

 *

 * @package bravad

 */

require_once 'functions/bravad-functions.php';

require_once 'functions/bravad-template-functions.php';

require_once 'functions/bravad-security.php';

require_once 'functions/bravad-media.php';

require_once 'functions/class-bravad-duplicate.php';

require_once 'functions/class-bravad-remove.php';

require_once 'functions/assets.php';

require_once 'functions/post-types-and-taxonomies.php';

require_once 'functions/widgets.php';

require_once 'functions/bravad-ajax.php';

require_once 'functions/bravad-woocommerce.php';

require_once 'functions/bravad-woocommerce-shipping.php';



/**

 * Assign the bravad version to a var

 */

$theme          = wp_get_theme( 'bravad' );

$bravad_version = $theme['Version'];



$bravad = (object) array(

	'version' => $bravad_version,



	/**

	 * Initialize all the things

	 */

	'main' => require_once 'functions/class-bravad.php'

);



function auto_update_specific_plugins ( $update, $item ) {

    // Array of plugin slugs to always auto-update

    $plugins = array();

    if ( in_array( $item->slug, $plugins ) ) {

         // Always update plugins in this array

        return true;

    } else {

        // Else, use the normal API response to decide whether to update or not

        return $update;

    }

}

add_filter( 'auto_update_plugin', 'auto_update_specific_plugins', 10, 2 );



/**

 * Override to reply-to in the email header for only new order

 * 

 */

add_filter( 'woocommerce_email_headers', 'new_order_reply_to_admin_header', 20, 3 );

function new_order_reply_to_admin_header( $header, $email_id, $order ) {



    if ( $email_id === 'new_order' ){

		$order_email = $order->billing_email;

		$biling_name = $order->billing_first_name." ".$order->billing_last_name;

        $email = new WC_Email($email_id);

        $header = "Content-Type: " . $email->get_content_type() . "\r\n";

        $header .= 'Reply-to: ' . __($biling_name) . ' <' . $order_email . ">\r\n";

    }

    return $header;

}

/**

 * Override the shipping price only for flat rate method

 * 

 */

add_filter( 'woocommerce_package_rates', 'override_shipping_flat_rate' , 10 );

function override_shipping_flat_rate( $rates ) {

    global $wpdb;

    $product_skus = array();

	$product_info = array();

    $cart = WC()->cart->get_cart();

    foreach ( $cart as $item ) {

		$sku = $item['data']->sku;

		$sql = "SELECT IdTransaction,sku,shippingPrice,isShippable,productName FROM {$wpdb->prefix}sync_products WHERE sku = '".$sku."'";

		$result = $wpdb->get_row( $sql );

		if($result->shippingPrice > 0){

			$product_skus[] = array("sku" => $sku, "price" => $result->shippingPrice);

			$product_info[$result->IdTransaction] = $result->shippingPrice;


		}

    }

    

	//Get highest shipping price from product skus

	$shipping_price = max(array_column($product_skus, 'price'));

	

	if(count($product_skus) > 0 && $shipping_price > 0){

		

		foreach ( $rates as $rate_key => $rate ){

            //$items = explode("1,",$rate->meta_data["Articles"]);

            // Targeting "flat rate" shipping method

            if( $rate->method_id === 'flat_rate' ){

				//preg_match_all('/||(.*?)||/', $rate->meta_data["Articles"], $matches);

				$get_shipping_price = explode("||",$rate->meta_data["Articles"]);

				$result = array_filter( $get_shipping_price, 

                        function($arrayEntry) { 

                            return is_numeric($arrayEntry);

                        }

                      );

				$result = array_unique($result);

				$ship_amount = 0;

				foreach($result as $value){

					$ship_amount += $product_info[$value];

				}

				//echo "<!-- tesst <pre>";

				//print_r($product_info);

				//print_r($result);

				//echo "</pre> -->";

                // Set the Sync Max Product Shipping Price

				$rates[$rate_key]->cost = $ship_amount;

				//$rates[$rate_key]->cost = $shipping_price * count($items);

            }

        }

	}

    return $rates;        

}

/**

 * Override the cart tax total for flat rate method

 * 

 */

add_filter( 'woocommerce_cart_tax_totals', 'override_shipping_tax_rate',10);

function override_shipping_tax_rate( $tax_totals ) {

	global $wpdb;

    $product_skus = array();

	$cart_item_total = WC()->cart->cart_contents_total;

	$shipping_total = WC()->cart->shipping_total;

    $cart = WC()->cart->get_cart();

    foreach ( $cart as $item ) {

		$sku = $item['data']->sku;

		$sql = "SELECT sku,shippingPrice,isShippable FROM {$wpdb->prefix}sync_products WHERE sku = '".$sku."'";

		$result = $wpdb->get_row( $sql );

		if($result->shippingPrice > 0){

			$product_skus[] = array("sku" => $sku, "price" => $result->shippingPrice);

		}

    }

    

	//Get highest shipping price from product skus

	$shipping_price = max(array_column($product_skus, 'price')) * count($cart);



	if(count($product_skus) > 0){ 



		$get_tax_location = WC_Tax::get_tax_location();

		if(count($get_tax_location) > 0 && $cart_item_total > 0){

			$calculate_tax_for = array();

			$calculate_tax_for['country'] = $get_tax_location[0];

			$calculate_tax_for['state'] = $get_tax_location[1];

			$calculate_tax_for['postcode'] = $get_tax_location[2];

			$calculate_tax_for['city'] = $get_tax_location[3];

			$calculate_tax_for["tax_class"] = WC_Tax::get_tax_location();

			$tax_rates                      = WC_Tax::find_rates( $calculate_tax_for );

			foreach( $tax_totals as $tax_key => $value ){

				if(count($tax_rates) > 0 && isset($tax_rates[$value->tax_rate_id]["shipping"]) && $tax_rates[$value->tax_rate_id]["shipping"] == "yes" && $tax_rates[$value->tax_rate_id]["rate"] > 0){

					$shipping_tax_amount = wc_round_tax_total((($cart_item_total + $shipping_total) * $tax_rates[$value->tax_rate_id]["rate"]) / 100);

					$tax_totals[$tax_key]->amount = wc_round_tax_total($shipping_tax_amount);

					$tax_totals[$tax_key]->formatted_amount = wc_price($shipping_tax_amount);

				} else {

					$shipping_tax_amount = wc_round_tax_total(($cart_item_total * $tax_rates[$value->tax_rate_id]["rate"]) / 100);

					$tax_totals[$tax_key]->amount = wc_round_tax_total($shipping_tax_amount);

					$tax_totals[$tax_key]->formatted_amount = wc_price($shipping_tax_amount);

				}

			}

		} else {

			$tax_totals = WC()->cart->get_total_tax();

		}

	} else {

		$tax_totals = WC()->cart->get_total_tax();

	}

    return $tax_totals;

}

/**

 * Override the cart total

 * 

 */

add_action('woocommerce_cart_total', 'override_cart_total');

function override_cart_total($price){

	global $wpdb;

	$tax_total = 0;

    $product_skus = array();

	$cart_item_total = WC()->cart->cart_contents_total;

	$shipping_total = WC()->cart->shipping_total;

    $cart = WC()->cart->get_cart();

    foreach ( $cart as $item ) {

		$sku = $item['data']->sku;

		$sql = "SELECT sku,shippingPrice,isShippable FROM {$wpdb->prefix}sync_products WHERE sku = '".$sku."'";

		$result = $wpdb->get_row( $sql );

		if($result->shippingPrice > 0){

			$product_skus[] = array("sku" => $sku, "price" => $result->shippingPrice);

		}

    }



	//Get highest shipping price from product skus

	$shipping_price = max(array_column($product_skus, 'price')) * count($cart);



	if(count($product_skus) > 0){



		$get_tax_location = WC_Tax::get_tax_location();

		if(count($get_tax_location) > 0 && $cart_item_total > 0){

			$calculate_tax_for = array();

			$calculate_tax_for['country'] = $get_tax_location[0];

			$calculate_tax_for['state'] = $get_tax_location[1];

			$calculate_tax_for['postcode'] = $get_tax_location[2];

			$calculate_tax_for['city'] = $get_tax_location[3];

			$calculate_tax_for["tax_class"] = WC_Tax::get_tax_location();

			$tax_rates                      = WC_Tax::find_rates( $calculate_tax_for );

			$tax_totals =  WC()->cart->get_tax_totals();

			foreach( $tax_totals as $tax_key => $value ){

				if(count($tax_rates) > 0 && isset($tax_rates[$value->tax_rate_id]["shipping"]) && $tax_rates[$value->tax_rate_id]["shipping"] == "yes" && $tax_rates[$value->tax_rate_id]["rate"] > 0){		$tax_total += wc_round_tax_total((($cart_item_total + $shipping_total) * $tax_rates[$value->tax_rate_id]["rate"]) / 100);

				} else {

					$tax_total += wc_round_tax_total(($cart_item_total * $tax_rates[$value->tax_rate_id]["rate"]) / 100);

				}

			}

		} else {

			$tax_total = WC()->cart->get_total_tax();

		}

	} else {

		$tax_total = WC()->cart->get_total_tax();

	}



	$wp_price = $cart_item_total + $shipping_total + $tax_total;

    return wc_price( $wp_price );

}

/**

 * Override the before cart total

 * 

 */

add_action('woocommerce_before_cart_totals', 'override_before_cart_total');

function override_before_cart_total($price){

	global $wpdb;

	$tax_total = 0;

    $product_skus = array();

	$cart_item_total = WC()->cart->cart_contents_total;

	$shipping_total = WC()->cart->shipping_total;

    $cart = WC()->cart->get_cart();

    foreach ( $cart as $item ) {

		$sku = $item['data']->sku;

		$sql = "SELECT sku,shippingPrice,isShippable FROM {$wpdb->prefix}sync_products WHERE sku = '".$sku."'";

		$result = $wpdb->get_row( $sql );

		if($result->shippingPrice > 0){

			$product_skus[] = array("sku" => $sku, "price" => $result->shippingPrice);

		}

    }



	//Get highest shipping price from product skus

	$shipping_price = max(array_column($product_skus, 'price')) * count($cart);



	if(count($product_skus) > 0){



		$get_tax_location = WC_Tax::get_tax_location();

		if(count($get_tax_location) > 0 && $cart_item_total > 0){

			$calculate_tax_for = array();

			$calculate_tax_for['country'] = $get_tax_location[0];

			$calculate_tax_for['state'] = $get_tax_location[1];

			$calculate_tax_for['postcode'] = $get_tax_location[2];

			$calculate_tax_for['city'] = $get_tax_location[3];

			$calculate_tax_for["tax_class"] = WC_Tax::get_tax_location();

			$tax_rates                      = WC_Tax::find_rates( $calculate_tax_for );

			$tax_totals =  WC()->cart->get_tax_totals();

			foreach( $tax_totals as $tax_key => $value ){

				if(count($tax_rates) > 0 && isset($tax_rates[$value->tax_rate_id]["shipping"]) && $tax_rates[$value->tax_rate_id]["shipping"] == "yes" && $tax_rates[$value->tax_rate_id]["rate"] > 0){		$tax_total += wc_round_tax_total((($cart_item_total + $shipping_total) * $tax_rates[$value->tax_rate_id]["rate"]) / 100);

				} else {

					$tax_total += wc_round_tax_total(($cart_item_total * $tax_rates[$value->tax_rate_id]["rate"]) / 100);

				}

			}

		} else {

			$tax_total = WC()->cart->get_total_tax();

		}

	} else {

		$tax_total = WC()->cart->get_total_tax();

	}



	$wp_price = $cart_item_total + $shipping_total + $tax_total;

    return wc_price( $wp_price );

}

/**

 * Add the Custom Shipping Price Text with Value in the Product List and Product Detail

 * 

 */

add_filter( 'woocommerce_get_price_html', 'additional_custom_shipping_content', 10, 2 ); 

function additional_custom_shipping_content($price, $product) { 

	global $wpdb;

	

	$sql_store = "SELECT p.idStore,s.pa_attributeTermSlug,r.meta_value FROM ".$wpdb->prefix."sync_products AS p INNER JOIN ".$wpdb->prefix."sync_stores AS s ON p.idStore=s.fileMaker_idStore INNER JOIN ".$wpdb->prefix."postmeta AS r ON s.retailers_idPost=r.post_id WHERE p.sku = '".$product->get_sku()."' AND r.meta_key = 'telephone'";

	$result_store = $wpdb->get_row( $sql_store );

			

	$shipping_custom_text = "";

	$store_custom_text = "";

	$sku = $product->get_sku();

	

	$sql = "SELECT IdTransaction,sku,shippingPrice,isShippable FROM {$wpdb->prefix}sync_products WHERE sku = '".$sku."'";

	$result = $wpdb->get_row( $sql );

	

	$store_custom_text = ( count( array($result_store) ) > 0 ) ? '<span class="custom_store swiper-no-swiping">Disponible à la succursale : <b style="color:#000;"> '. strtoupper(str_replace("-"," ",$result_store->pa_attributeTermSlug)) . ' '.strtoupper($result_store->meta_value).'</b></span>' : '';

	

	if($result->isShippable == 1 && $result->shippingPrice > 0 && $sku === $result->sku){

		$shipping_custom_text = "<p class='custom_shipping'>Prix ​​de l'expédition : <span class='amount'>".wc_price($result->shippingPrice)."</span><span style='display:none;'>||".$result->IdTransaction."||</span><br /><span>(SVP appeler pour livraison spéciale ou hors-Québec)</span><br />".$store_custom_text."</p>";

	} else if($result->isShippable == 0 && $result->shippingPrice == 0 && $sku === $result->sku ){

		$shipping_custom_text = "<p class='custom_shipping'>Ramassage<br /><span>(SVP appeler pour livraison spéciale ou hors-Québec)</span><br />".$store_custom_text."</p>";

	}

		

	return ($price) . $shipping_custom_text;

}

/**

 * Add the Custom Shipping Price Text with Value in the Cart and Order

 * 

 */

add_filter( 'woocommerce_before_calculate_totals', 'custom_cart_items_prices', 10, 1 );

function custom_cart_items_prices( $cart ) {

	global $wpdb;

	$shipping_custom_text = "";

	$cart_item_total = $cart->cart_contents_total;

	$shipping_total = $cart->shipping_total;

	$tax_totals =  $cart->get_tax_totals();

	$product_skus = array();

	//echo "<!-- tesst <pre>";

	//print_r($cart->get_cart());

	//echo "</pre> -->";

    // Loop through cart items

    foreach ( $cart->get_cart() as $cart_item ) {



		$sku = $cart_item['data']->sku;

		

		$sql = "SELECT IdTransaction,sku,shippingPrice,isShippable FROM {$wpdb->prefix}sync_products WHERE sku = '".$sku."'";

		$result = $wpdb->get_row( $sql );

		

        // Get an instance of the WC_Product object

        $product = $cart_item['data'];



        // Get the product name (Added Woocommerce 3+ compatibility)

        $original_name = method_exists( $product, 'get_name' ) ? $product->get_name() : $product->post->post_title;

		

		$product_name = ltrim(ltrim($original_name));

		

		if($result->isShippable == 1 && $result->shippingPrice > 0 && $sku === $result->sku ){

			// Set the shipping price

			$shipping_custom_text = "<p class='custom_shipping'>Prix ​​de l'expédition : <span class='amount'>".wc_price($result->shippingPrice)."</span><span style='display:none;'>||".$result->IdTransaction."||</span><br /><span>(SVP appeler pour livraison spéciale ou hors-Québec)</span></p>";

			//$shipping_custom_text = "<p class='custom_shipping'>Prix ​​de l'expédition : <span class='amount'>".wc_price($result->shippingPrice)."<!-- <button data-tooltip='Im the tooltip text.'><i class='fa fa-question-circle'></i></button> --></span><br /><span>(SVP appeler pour livraison spéciale ou hors-Québec)</span></p>";

		} else if($result->isShippable == 0 && $result->shippingPrice == 0 && $sku === $result->sku ){

			$shipping_custom_text = "<p class='custom_shipping'>Ramassage<br /><span>(SVP appeler pour livraison spéciale ou hors-Québec)</span></p>";

		}

		

		//echo "<!-- tesst <pre>";

		//print_r($cart_item['line_tax']);

		//print_r($cart_item);

		//echo "</pre> -->";

	

        // Set the shipping price (WooCommerce versions 2.5.x to 3+)

        if( method_exists( $product, 'set_name' ) )

            $product->set_name( $product_name . $shipping_custom_text );

        else

            $product->post->post_title = $product_name . $shipping_custom_text;

    }

}

/**

 * Add the override order shipping tax rate

 * 

 */

add_filter( 'woocommerce_order_get_tax_totals', 'override_order_shipping_tax_rate', 20 ,1 ); 

function override_order_shipping_tax_rate( $tax_totals ) {

	$order_id = get_query_var('order-received') ? get_query_var('order-received') : 0;

	if($order_id > 0){

		$order = wc_get_order( $order_id );

		$get_total = $order->get_subtotal();

		//$get_taxes = $order->get_taxes();

		//echo "<!-- tesstess <pre>";

		//print_r($get_taxes);

		//echo "</pre> -->";

		$shipping_total = $order->get_shipping_total();

		$get_tax_location = WC_Tax::get_tax_location();

		if(count($get_tax_location) > 0 && $get_total > 0){

			$calculate_tax_for = array();

			$calculate_tax_for['country'] = $get_tax_location[0];

			$calculate_tax_for['state'] = $get_tax_location[1];

			$calculate_tax_for['postcode'] = $get_tax_location[2];

			$calculate_tax_for['city'] = $get_tax_location[3];

			$calculate_tax_for["tax_class"] = WC_Tax::get_tax_location();

			$tax_rates                      = WC_Tax::find_rates( $calculate_tax_for );

			foreach( $tax_totals as $tax_key => $value ){

				if(count($tax_rates) > 0 && isset($tax_rates[$value->rate_id]["shipping"]) && $tax_rates[$value->rate_id]["shipping"] == "yes" && $tax_rates[$value->rate_id]["rate"] > 0){

					$shipping_tax_amount = wc_round_tax_total((($get_total + $shipping_total) * $tax_rates[$value->rate_id]["rate"]) / 100);

					$tax_totals[$tax_key]->amount = wc_round_tax_total($shipping_tax_amount);

					$tax_totals[$tax_key]->formatted_amount = wc_price($shipping_tax_amount);

				} else {

					$shipping_tax_amount = wc_round_tax_total(($get_total * $tax_rates[$value->rate_id]["rate"]) / 100);

					$tax_totals[$tax_key]->amount = wc_round_tax_total($shipping_tax_amount);

					$tax_totals[$tax_key]->formatted_amount = wc_price($shipping_tax_amount);

				}

			}

		}

	}

	return $tax_totals;	

}

/**

 * Add the override order total

 * 

 */

add_filter('woocommerce_order_get_total', 'override_order_total');

function override_order_total($total){

	$tax_total = 0;

	$order_id = get_query_var('order-received') ? get_query_var('order-received') : 0;

	if($order_id > 0){

		$order = wc_get_order( $order_id );

		$get_total = $order->get_subtotal();

		$shipping_total = $order->get_shipping_total();



		$get_tax_location = WC_Tax::get_tax_location();

		if(count($get_tax_location) > 0 && $get_total > 0){

			$calculate_tax_for = array();

			$calculate_tax_for['country'] = $get_tax_location[0];

			$calculate_tax_for['state'] = $get_tax_location[1];

			$calculate_tax_for['postcode'] = $get_tax_location[2];

			$calculate_tax_for['city'] = $get_tax_location[3];

			$calculate_tax_for["tax_class"] = WC_Tax::get_tax_location();

			$tax_rates                      = WC_Tax::find_rates( $calculate_tax_for );

			$tax_totals =  $order->get_tax_totals();

			foreach( $tax_totals as $tax_key => $value ){

				if(count($tax_rates) > 0 && isset($tax_rates[$value->rate_id]["shipping"]) && $tax_rates[$value->rate_id]["shipping"] == "yes" && $tax_rates[$value->rate_id]["rate"] > 0){		

					$tax_total += wc_round_tax_total((($get_total + $shipping_total) * $tax_rates[$value->rate_id]["rate"]) / 100);

				} else {

					$tax_total += wc_round_tax_total(($get_total * $tax_rates[$value->rate_id]["rate"]) / 100);

				}

			}

		} else {

			$tax_total = $order->get_total_tax();

		}

		//echo "<!-- tesstess <pre>";

		//print_r($order->get_shipping_total());

		//echo "</pre> -->";

		$total = $get_total + $shipping_total + $tax_total;

	}

    return round($total,2,PHP_ROUND_HALF_UP);

}

/**

 * Add the override create order tax item amount

 * 

 */

add_action( 'woocommerce_checkout_create_order_tax_item', 'override_create_order_tax_item');

function override_create_order_tax_item($item){

	global $wpdb;

	$tax_total = 0;

    $product_skus = array();

	$cart = WC()->cart;

	$get_cart = $cart->get_cart();

	$get_taxes = $cart->get_taxes();

	$cart_item_total = $cart->cart_contents_total;

	$shipping_total = $cart->shipping_total;

    foreach ( $get_cart as $cart_item ) {

		$sku = $cart_item['data']->sku;

		$sql = "SELECT sku,shippingPrice,isShippable FROM {$wpdb->prefix}sync_products WHERE sku = '".$sku."'";

		$result = $wpdb->get_row( $sql );

		if($result->shippingPrice > 0){

			$product_skus[] = array("sku" => $sku, "price" => $result->shippingPrice);

		}

    }



	//Get highest shipping price from product skus

	$shipping_price = max(array_column($product_skus, 'price'));

	

	$tax_totals = WC()->cart->get_tax_totals();

	$tax_rate_id = $item->get_rate_id();

	$tax_rate_code = $item->get_rate_code();

	$tax_rate_percent = $item->get_rate_percent();

	//$shipped_item_count = $shipping_total / $shipping_price;

	$shipped_item_count = 1;



	$get_tax_location = WC_Tax::get_tax_location();

	if(count($product_skus) > 0 && count($get_tax_location) > 0 && $cart_item_total > 0){

		$calculate_tax_for = array();

		$calculate_tax_for['country'] = $get_tax_location[0];

		$calculate_tax_for['state'] = $get_tax_location[1];

		$calculate_tax_for['postcode'] = $get_tax_location[2];

		$calculate_tax_for['city'] = $get_tax_location[3];

		$calculate_tax_for["tax_class"] = WC_Tax::get_tax_location();

		$tax_rates                      = WC_Tax::find_rates( $calculate_tax_for );

		

		if(count($tax_rates) > 0 && isset($tax_rates[$tax_rate_id]["shipping"]) && $tax_rates[$tax_rate_id]["shipping"] == "yes" && $tax_rates[$tax_rate_id]["rate"] > 0 && isset($tax_totals[$tax_rate_code]) && count($tax_totals[$tax_rate_code]) > 0 && count($get_cart) > 0 && $shipped_item_count > 1){

			$item->set_props(

				array(

					'rate_id'            => $tax_rate_id,

					'tax_total'          =>  $tax_totals[$tax_rate_code]->amount,

					//'shipping_tax_total' => round($cart->get_tax_amount( $tax_rate_id ) * ($tax_rate_percent/100),2,PHP_ROUND_HALF_UP),

					'shipping_tax_total' => $tax_totals[$tax_rate_code]->amount,

					'rate_code'          => WC_Tax::get_rate_code( $tax_rate_id ),

					'label'              => WC_Tax::get_rate_label( $tax_rate_id ),

					'compound'           => WC_Tax::is_compound( $tax_rate_id ),

				)

			);

		} else if(count($tax_rates) > 0 && isset($tax_rates[$tax_rate_id]["shipping"]) && $tax_rates[$tax_rate_id]["shipping"] == "yes" && $tax_rates[$tax_rate_id]["rate"] > 0 && isset($tax_totals[$tax_rate_code]) && count($tax_totals[$tax_rate_code]) > 0 && count($get_cart) > 0 && $shipped_item_count == 1){

			$item->set_props(

				array(

					'rate_id'            => $tax_rate_id,

					'tax_total'          =>  $tax_totals[$tax_rate_code]->amount / 2,

					//'shipping_tax_total' => round($cart->get_tax_amount( $tax_rate_id ) * ($tax_rate_percent/100),2,PHP_ROUND_HALF_UP),

					'shipping_tax_total' => $tax_totals[$tax_rate_code]->amount / 2,

					'rate_code'          => WC_Tax::get_rate_code( $tax_rate_id ),

					'label'              => WC_Tax::get_rate_label( $tax_rate_id ),

					'compound'           => WC_Tax::is_compound( $tax_rate_id ),

				)

			);

		}

	}

	//echo wc_round_tax_total((($cart_item_total + $shipping_total) * $tax_rates[$tax_rate_id]["rate"]) / 100)."<br>";

	//print_r($item->get_rate_id());

	//echo "tesss ".$tax_rates[$tax_rate_id]["shipping"];

	return $item;

}

/**

 * Add the override create order tax item amount

 * 

 */

add_action( 'woocommerce_checkout_create_order', 'order_change_total_on_checking', 20, 1 );

function order_change_total_on_checking( $order ) {

	global $wpdb;

	$tax_total = 0;

    $product_skus = array();

	$cart_item_total = WC()->cart->cart_contents_total;

	$shipping_total = WC()->cart->shipping_total;

    $cart = WC()->cart->get_cart();

    foreach ( $cart as $item ) {

		$sku = $item['data']->sku;

		$sql = "SELECT sku,shippingPrice,isShippable FROM {$wpdb->prefix}sync_products WHERE sku = '".$sku."'";

		$result = $wpdb->get_row( $sql );

		if($result->shippingPrice > 0){

			$product_skus[] = array("sku" => $sku, "price" => $result->shippingPrice);

		}

    }



	//Get highest shipping price from product skus

	$shipping_price = max(array_column($product_skus, 'price'));



	if(count($product_skus) > 0){



		$get_tax_location = WC_Tax::get_tax_location();

		if(count($get_tax_location) > 0 && $cart_item_total > 0){

			$calculate_tax_for = array();

			$calculate_tax_for['country'] = $get_tax_location[0];

			$calculate_tax_for['state'] = $get_tax_location[1];

			$calculate_tax_for['postcode'] = $get_tax_location[2];

			$calculate_tax_for['city'] = $get_tax_location[3];

			$calculate_tax_for["tax_class"] = WC_Tax::get_tax_location();

			$tax_rates                      = WC_Tax::find_rates( $calculate_tax_for );

			$tax_totals =  WC()->cart->get_tax_totals();

			foreach( $tax_totals as $tax_key => $value ){

				if(count($tax_rates) > 0 && isset($tax_rates[$value->tax_rate_id]["shipping"]) && $tax_rates[$value->tax_rate_id]["shipping"] == "yes" && $tax_rates[$value->tax_rate_id]["rate"] > 0){		$tax_total += wc_round_tax_total((($cart_item_total + $shipping_total) * $tax_rates[$value->tax_rate_id]["rate"]) / 100);

				} else {

					$tax_total += wc_round_tax_total(($cart_item_total * $tax_rates[$value->tax_rate_id]["rate"]) / 100);

				}

			}

		} else {

			$tax_total = WC()->cart->get_total_tax();

		}

	} else {

		$tax_total = WC()->cart->get_total_tax();

	}



	$new_total = $cart_item_total + $shipping_total + $tax_total;

    // Get order total

    //$total = $order->get_total();



    // Set the new calculated total

    $order->set_total( $new_total );

}