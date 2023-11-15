/*
	Minicart present on main menu.

	Core operations are already managed natively by Woocommerce, here we
	only do custom visual adjustments.
 */
;minicart = (function($) {
	var $cartResult = $('.cart-result'),
		$cartLoading = $('.cart-loading'),
		$addtocartbtn = $('.ajax_add_to_cart');

	/**
	 * INITIALISE
	 * ----------
	 *
	 * @return {undefined}
	 */
	(function init() {
		// manageAddToCartBtn();
		// updateMiniCart();
	})();

	/**
	 * UPDATE MINI CART CONTENT
	 * ----------
	 *
	 * Update the mini-cart amount and item qty when called.
	 */
	function updateMiniCart() {
		var data = {
			'action': 'load_woo_cart'
		};

		$cartResult.css({
			'opacity':'0',
			'width':'30px',
		});

		$cartLoading.fadeIn();

		jQuery.post( woocommerce_params.ajax_url, data, function( response ) {
			$cartLoading.fadeOut();
			$cartResult.css({
				'opacity':'1',
				'width':'150px',
			});
			$cartResult.html(response);
			manageRemoveCartBtn();
		});
	}

	/**
	 * MANAGE REMOVE CART BUTTON
	 * ----------
	 *
	 * Bind listener to remove button, which is not cached
	 * because it is constantly added / removed from DOM
	 */
	function manageRemoveCartBtn() {

		$('.remove_from_cart_button')
			.off('click.manageRemoveCartBtn')
			.on('click.manageRemoveCartBtn', function() {
				var dataProductID = $(this).attr('data-product_id');

				// Update button label (for other instance of this product on the page)
				$('.add_to_cart_button[data-product_id=' + dataProductID + ']').removeClass('added');
				$('.add_to_cart_button[data-product_id=' + dataProductID + ']').text('Ajouter au panier').show();
				$('.add_to_cart_button[data-product_id=' + dataProductID + ']').attr('disabled', false);
				$('.add_to_cart_button[data-product_id=' + dataProductID + ']').siblings('.in-cart').hide();
				
				// Update mini cart
				updateMiniCart();
			});

	}

	/**
	 * MANAGE ADD TO CART BUTTOM
	 * ----------
	 *
	 * Bind “Add to cart” button click listener.
	 * There is 2 cases to manage :
	 * 	- <a> tag with product id as "data-product_id" attribute
	 * 	- <button> tag, with quantity input as sibling
	 */
	function manageAddToCartBtn() {
		$('.add_to_cart_button')
			.off('click.manageAddToCartBtn')
			.on('click.manageAddToCartBtn', function(ev) {
				var dataProductID = $(this).attr('data-product_id');

				// <a> tag with product id as "data-product_id" attribute
				if (dataProductID) {

					// Product is already added
					if( $(this).hasClass('added') ) {
						return false;

					// Prevent product from being added again
					} else {
						$(this).attr('disabled', true);
						$(this).text('Déjà dans votre panier');

						// Update button label (for other instance of this product on the page)
		 				$('.add_to_cart_button[data-product_id=' + dataProductID + ']').addClass('added');
		 				$('.add_to_cart_button[data-product_id=' + dataProductID + ']').text('Déjà dans votre panier').show();
		 				$('.add_to_cart_button[data-product_id=' + dataProductID + ']').attr('disabled', true);

						// Update mini cart
						// @todo : Make sure it happens after Woocommerce async add to cart command
						// @see https://instantcomptant.bravad-dev.com/wp-content/plugins/woocommerce/assets/js/frontend/add-to-cart.min.js (a.prototype.onAddToCart)
						minicart.updateMiniCart();
					}

				// <button> tag, with quantity input as sibling
				} else {

				}


			});

		if ($('.add_to_cart_button').is(':empty')){
		  $(this).hide();
		}

	}

	/*
		Return public methods
	 */
	return {
		updateMiniCart : updateMiniCart
	}

})(jQuery);