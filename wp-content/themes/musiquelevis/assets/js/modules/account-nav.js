/*
	ACCOUNT NAV
	------

	Mobile Nav for the Woocommerce account
 */
;(function accountNav($) {

	$("select.woo__sidebar--links").change(function() {
	  window.location = $(this).find("option:selected").val();
	});

})(jQuery);