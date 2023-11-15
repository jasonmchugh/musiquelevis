/*
	Produits Promo
	------

 */

;(function loadPromos($) {

	if ($('body').hasClass('home')) {

		var str = '&action=LoadProduitPromo';
	    var ajaxurl = '/wp-admin/admin-ajax.php';
	    jQuery.ajax({
	        type: 'GET',
	        dataType: 'html',
	        url: ajaxurl,
	        data: str,
	        success: function(data) {
		    	jQuery('.products__loading--promo').fadeOut();
				jQuery('.products__slider--promo .swiper-wrapper').append(data).css('opacity', '1');

				// Generate the slider with fetched products
				var swiper = new Swiper('.products__swipercontainer--promo', {
				    paginationClickable: true,
				    spaceBetween: 20,
				    autoplay: {
				    	delay: 5000,
				    },
				    slidesPerView: 4,
				    slidesPerGroup: 4,
				    speed: 1000,
				    loop: false,
				    navigation: {
				        nextEl: '.products__button---next2',
				        prevEl: '.products__button---prev2',
				    },
				    breakpoints: {
				        1100: {
				        	slidesPerView: 3,
							spaceBetween: 40,
				        },
				        768: {
				        	slidesPerView: 2,
							spaceBetween: 20,
				        },
				        640: {
					        slidesPerView: 1,
							spaceBetween: 40,
				        }
				    }
				});

				// minicart.manageAddToCartBtn();
				productgrid.thumbResize();

	        }
	    });

    }


    // Related Products
    if ($('body').hasClass('single-product')) {

	    var swipe2 = new Swiper('.products__swipercontainer--promo', {
		    paginationClickable: true,
		    spaceBetween: 20,
		    slidesPerView: 4,
		    loop: false,
		    navigation: {
		        nextEl: '.products__button---next2',
		        prevEl: '.products__button---prev2',
		    },
		    breakpoints: {
		        1100: {
		        	slidesPerView: 3,
					spaceBetween: 40,
		        },
		        768: {
		        	slidesPerView: 2,
					spaceBetween: 20,
		        },
		        640: {
			        slidesPerView: 1,
					spaceBetween: 40,
		        }
		    }
		});

	}

})(jQuery);