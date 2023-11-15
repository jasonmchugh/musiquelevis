<?php
/**
* Plugin Name: SKU search to direct product page
* Description: Woo Sku to Product, If a user search for a product by its SKU user will be redirected to  Product page directly
* Change add to cart text
* Change Proceed to checkout button text
* Version: 1.5
* Author: Xaraar, firdousi
* Author URI: Xaraar.com
*/

if ( !function_exists('wsops_get_product_by_sku')){
	function wsops_get_product_by_sku( $sku ) {
	  global $wpdb;
	  $un_spaced_sku = str_replace(' ', '', $sku);
	  $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND (meta_value='%s'  OR meta_value='%s') LIMIT 1", $sku , $un_spaced_sku ) );

	  if ( $product_id ) {
		return new WC_Product( $product_id );
	  }else{
	  	return null;
	  }
	  	
	}
}

if ( !function_exists('wsops_product_redirect')){
	function wsops_product_redirect() {
	  global $post;
	    if(isset($_GET['s'])) {
			$result = wsops_get_product_by_sku($_GET['s']);
			if( $result != null){
				if($result->post->post_parent != 0 ) {
				
					$parent = new WC_Product( $result->post->post_parent );
					
					wp_redirect( get_permalink($parent->id) );
					exit;
				} else {
					wp_redirect( get_permalink($result->id) );
					exit;
				}
			}
		}
	}
	add_action( 'template_redirect', 'wsops_product_redirect', 0 );
}

add_action( 'admin_menu', 'woobf__add_admin_menu' );
add_action( 'admin_init', 'woobf__settings_init' );


function woobf__add_admin_menu(  ) { 

	add_options_page( 'Woo Basic Features', 'Woo Basic Features', 'manage_options', 'woo_basic_features', 'woobf__options_page' );

}


function woobf__settings_init(  ) { 

	register_setting( 'pluginPage', 'woobf__settings' );

	add_settings_section(
		'woobf__pluginPage_section', 
		__( '', 'woobf' ), 
		'woobf__settings_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'woobf_proceed_to_checkout_button_text_field', 
		__( 'Proceed to Checkout button text', 'woobf' ), 
		'woobf_proceed_to_checkout_button_text', 
		'pluginPage', 
		'woobf__pluginPage_section' 
	);

	add_settings_field( 
		'woobf_add_to_cart_text', 
		__( 'Add to cart text', 'woobf' ), 
		'woobf_add_to_cart_text_render', 
		'pluginPage', 
		'woobf__pluginPage_section' 
	);

	// add_settings_field( 
	// 	'woobf__text_field_2', 
	// 	__( 'Settings field description', 'woobf' ), 
	// 	'woobf__text_field_2_render', 
	// 	'pluginPage', 
	// 	'woobf__pluginPage_section' 
	// );

	// add_settings_field( 
	// 	'woobf__checkbox_field_3', 
	// 	__( 'Settings field description', 'woobf' ), 
	// 	'woobf__checkbox_field_3_render', 
	// 	'pluginPage', 
	// 	'woobf__pluginPage_section' 
	// );

	// add_settings_field( 
	// 	'woobf__radio_field_4', 
	// 	__( 'Settings field description', 'woobf' ), 
	// 	'woobf__radio_field_4_render', 
	// 	'pluginPage', 
	// 	'woobf__pluginPage_section' 
	// );

	// add_settings_field( 
	// 	'woobf__textarea_field_5', 
	// 	__( 'Settings field description', 'woobf' ), 
	// 	'woobf__textarea_field_5_render', 
	// 	'pluginPage', 
	// 	'woobf__pluginPage_section' 
	// );

	add_settings_field( 
		'woobf_disable_shipping_on_cart_page', 
		__( 'Disable shipping info on cart page', 'woobf' ), 
		'woobf_disable_shipping_on_cart_page_render', 
		'pluginPage', 
		'woobf__pluginPage_section' 
	);




}


function woobf_proceed_to_checkout_button_text(  ) { 

	$options = get_option( 'woobf__settings' );
	?>
	<input type='text' name='woobf__settings[woobf_proceed_to_checkout_button_text_field]' value='<?php echo $options['woobf_proceed_to_checkout_button_text_field']; ?>'>
	<span>Leave blank for default</span>
	<?php

}


function woobf_add_to_cart_text_render(  ) { 

	$options = get_option( 'woobf__settings' );
	?>
	<input type='text' name='woobf__settings[woobf_add_to_cart_text]' value='<?php echo @$options['woobf_add_to_cart_text']; ?>'>
	<span>Leave blank for default</span>
	<?php

}


function woobf__text_field_2_render(  ) { 

	$options = get_option( 'woobf__settings' );
	?>
	<input type='text' name='woobf__settings[woobf__text_field_2]' value='<?php echo $options['woobf__text_field_2']; ?>'>
	<?php

}


function woobf__checkbox_field_3_render(  ) { 

	$options = get_option( 'woobf__settings' );
	?>
	<input type='checkbox' name='woobf__settings[woobf__checkbox_field_3]' <?php checked( $options['woobf__checkbox_field_3'], 1 ); ?> value='1'>
	<?php

}


function woobf__radio_field_4_render(  ) { 

	$options = get_option( 'woobf__settings' );
	?>
	<input type='radio' name='woobf__settings[woobf__radio_field_4]' <?php checked( $options['woobf__radio_field_4'], 1 ); ?> value='1'>
	<?php

}


function woobf__textarea_field_5_render(  ) { 

	$options = get_option( 'woobf__settings' );
	?>
	<textarea cols='40' rows='5' name='woobf__settings[woobf__textarea_field_5]'> 
		<?php echo $options['woobf__textarea_field_5']; ?>
 	</textarea>
	<?php

}


function woobf_disable_shipping_on_cart_page_render(  ) { 

	$options = get_option( 'woobf__settings' );
	?>
	<select name='woobf__settings[woobf_disable_shipping_on_cart_page]'>
		<option value='1' <?php selected( $options['woobf_disable_shipping_on_cart_page'], 1 ); ?>>Yes</option>
		<option value='0' <?php selected( $options['woobf_disable_shipping_on_cart_page'], 0 ); ?>>No</option>
	</select>

<?php

}



function woobf__settings_section_callback(  ) { 

	//echo __( 'This section description', 'woobf' );

}


function woobf__options_page(  ) { 

	?>
	<form action='options.php' method='post'>

		<h2>Woo Basic Features</h2>

		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>

	</form>
	<?php

}

function woobfdisable_shipping_calc_on_cart( $show_shipping ) {

    $options = get_option( 'woobf__settings' );
    if( is_cart() && $options['woobf_disable_shipping_on_cart_page'] == 1 ) {
        return false;
    }
    return $show_shipping;
}
add_filter( 'woocommerce_cart_ready_to_calc_shipping', 'woobfdisable_shipping_calc_on_cart', 99 );


function woobfwoocommerce_button_proceed_to_checkout() {
	$options = get_option( 'woobf__settings' );
    if( !empty($options['woobf_proceed_to_checkout_button_text_field']) &&  $options['woobf_proceed_to_checkout_button_text_field']!= '') {


   $checkout_url = WC()->cart->get_checkout_url();
   ?>
   <a href="<?php echo $checkout_url; ?>" class="checkout-button button alt wc-forward"><?php _e( $options['woobf_proceed_to_checkout_button_text_field'], 'woocommerce' ); ?></a>
   <?php
}
}


add_filter( 'add_to_cart_text', 'woobfwoo_custom_single_add_to_cart_text' );                // < 2.1
add_filter( 'woocommerce_product_single_add_to_cart_text', 'woobfwoo_custom_single_add_to_cart_text' );  // 2.1 +
  
function woobfwoo_custom_single_add_to_cart_text() {
  
  $options = get_option( 'woobf__settings' );

    if( !empty($options['woobf_add_to_cart_text']) && $options['woobf_add_to_cart_text']!= '' ) {
	    return __( $options['woobf_add_to_cart_text'], 'woocommerce' );
	}
  
}


?>