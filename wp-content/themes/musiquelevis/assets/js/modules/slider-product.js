/*
	SLIDER PRODUCT PAGE
	------

 */
;(function sliderProduct($) {

	if ($('body').hasClass('single-product')) {

		setTimeout( function() {

			$('.flex-control-nav').wrapInner('<div class="swiper-wrapper"></div>');
			$('.flex-control-nav li').wrap('<div class="swiper-slide"></div>');
			$('.flex-control-nav').fadeIn();
			$('.flex-control-nav').append('<div class="products__button---next3"></div><div class="products__button---prev3"></div>');

		    var swipe3 = new Swiper('.flex-control-nav', {
			    paginationClickable: true,
			    spaceBetween: 20,
			    slidesPerView: 4,
			    loop: false,
			    navigation: {
			        nextEl: '.products__button---next3',
			        prevEl: '.products__button---prev3',
			    },
			    breakpoints: {
			        1100: {
			        	slidesPerView: 3,
						spaceBetween: 40,
			        },
			        768: {
			        	slidesPerView: 5,
						spaceBetween: 20,
			        },
			        640: {
				        slidesPerView: 4,
						spaceBetween: 40,
			        }
			    }
			});

		}, 1000);

	}

})(jQuery);