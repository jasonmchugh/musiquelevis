<?php
	global $bv;
	global $woocommerce;

	/*
		USER
	 */
	$bv['user'] = array(
		'isLoggedIn' => false,
	);

	if (is_user_logged_in()) {
		global $current_user;

		$bv['user']['isLoggedIn'] = true;
		$bv['user']['firstname'] = $current_user->user_firstname;
	}

	/*
		PERMALINKS
	 */
	$bv['permalinks'] = array();

	// My account
 	$bv['permalinks']['account'] = esc_url(wc_get_account_endpoint_url('account'));

	// Log out
	if (is_user_logged_in()) {
		$bv['permalinks']['logout'] = wp_logout_url(get_home_url());
	}

	/*
		CART
	 */
	$bv['cart']['permalink'] = wc_get_cart_url();
	$bv['cart']['count'] = $woocommerce->cart->get_cart_contents_count();


?>