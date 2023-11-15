/*
	Produits Vedette
	------

 */

;(function loadVedette($) {

	if ($('body').hasClass('home')) {

		var str = '&action=LoadProduitVedette';
	    var ajaxurl = '/wp-admin/admin-ajax.php';
	    jQuery.ajax({
	        type: 'GET',
	        dataType: 'html',
	        url: ajaxurl,
	        data: str,
	        success: function(data) {
		    	jQuery('.products__loading--vedette').fadeOut();
				jQuery('.products__slider--vedette .swiper-wrapper').append(data).css('opacity', '1');

				// Generate the slider with fetched products
				var swiper = new Swiper('.products__swipercontainer--vedette', {
				    paginationClickable: true,
				    spaceBetween: 20,
				    slidesPerView: 4,
				    slidesPerGroup: 4,
				    speed: 1000,
				    loop: false,
				    navigation: {
				        nextEl: '.products__button---next1',
				        prevEl: '.products__button---prev1',
				    },
				    breakpoints: {
				        1100: {
				        	slidesPerView: 3,
							spaceBetween: 40,
				        },
				        768: {
				        	slidesPerView: 2,
							spaceBetween: 40,
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

})(jQuery);