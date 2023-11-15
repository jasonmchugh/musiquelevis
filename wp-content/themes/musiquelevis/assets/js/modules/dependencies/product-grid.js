/*
	PRODUCT GRID
	------

 */


;productgrid = (function($) {


	/**
	 * INITIALISE
	 * ----------
	 *
	 * @return {undefined}
	 */
	(function init() {
		thumbResize();
	})();


	/**
	 * THUMBNAIL RESIZE
	 * ----------
	 *
	 * Makes sure that the product thumbnails are all square
	 */
	function thumbResize() {
		$('.product__thumb').height( $('.product__thumb').width() );
// 		$('.woocommerce-product-gallery__image').height( $('.product__thumb').width() );
	}

	// Call function on page resize
	$(window).on('resize', function(){
		thumbResize();
	});


	// Call function when filtering products
	$( ".widget label" ).click(function() {
		checkForChanges();
	});


	/**
	 * GRID CONTAINER SIZE LOOKUP
	 * ----------
	 *
	 * Watch the container for size changes. Resize the thumbnails when it happens.
	 */
	var $productGrid = $(".products");
	var lastHeight = $(".products").css('height');

	function checkForChanges() {
	    if (productGrid.css('height') != lastHeight) {
	       thumbResize();
	    }
		setTimeout(checkForChanges, 500);
	}


	/*
		Return public methods
	 */
	return {
		thumbResize : thumbResize,
	}

})(jQuery);