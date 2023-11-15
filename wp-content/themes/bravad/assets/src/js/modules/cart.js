/*
	CART PAGE
	------

 */
;(function cartPage($) {
	// Force page reload to force mini-cart ti be refreshed
	// Yes, the method is discutable.
	$('.product-remove .remove').on('click', function() {
		// Wait for Woocommerce Ajax request to be sent
		setTimeout(function() {
			window.location = window.location;
		}, 500);

	});

	// Remove the amounts in the Shipping package description
	$('.woocommerce-shipping-contents small').each(function(){
		$(this).html($(this).html().split(" ×1").join(""));
	});

	// Shipping
	$('#ship-to-different-address').click( function() {
		$('#ship-to-different-address-checkbox').trigger('click');
	});

	// Création de compte
	$('.woocommerce-account-fields label').click( function() {
		$('#createaccount').trigger('click');
	});

})(jQuery);