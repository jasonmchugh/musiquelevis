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
/**
 * MEDIA QUERIES
 * 
 * Trigger custom events attached to $(window) on every breakpoint change.
 * Media queries are fetched from body:before CSS property, and don't need 
 * to be redeclared to work in JS.
 * 
 * Examples on github : https://github.com/mathieubeauregard/javascript/tree/master/media-queries
 *
 * @return {object} Public methods : setConfig & getCurrentBreakpoint
 * @requires jQuery http://jquery.com/
 */
var bv = bv || {};

bv.mq = (function($) {
		// {Object} Module configuration (editable via setConfig method)
	var config = {
		// {Boolean} Add class with breakpoint name to body ?
			manageBodyClasses : false,
		// {Boolean} Logs events that get triggered
			debug : false,
		},
		// {Boolean} Does browser supports pseudo :before ?
		browserIsSupported = window.getComputedStyle && window.getComputedStyle(document.querySelector('body'), ':before').getPropertyValue('content'),
		// {Object} jQuery cache
		$bg = $('[data-bg-xl], [data-bg-xl-1x], [data-bg-lg], [data-bg-lg-1x], [data-bg-md], [data-bg-md-1x], [data-bg-sm], [data-bg-sm-1x], [data-bg-xs], [data-bg-xs-1x]'),
		$window,
		$body,
		// {String} Current breakpoint name, as declared in CSS
		currentBreakpoint;

	/**
	 * GET CURRENT BREAKPOINT
	 * Fetch current breakpoint value from body:before.
	 *
	 * @public
	 * @return {String} Breakpoint name, as declared in CSS.
	 */
	function getCurrentBreakpoint() {
		var breakpoint = browserIsSupported ? window.getComputedStyle(document.querySelector('body'), ':before').getPropertyValue('content').replace(/\"/g, '') : 'unsupportedBrowser';

		// Sanitize breakpoint name
		breakpoint = breakpoint.replace('\'', '');
		breakpoint = breakpoint.replace('\'', '');
		breakpoint = breakpoint.replace('"', '');
		breakpoint = breakpoint.replace('"', '');

		return breakpoint;
	}

	/**
	 * UPDATE BREAKPOINTS STATE
	 * Checks if breakpoint changed changed since last check,
	 * trigger custom events if needed
	 *
	 * @private
	 * @param {Boolean} forceUpdate Force events to be triggered if true
	 */
	function updateBreakpointsState(forceUpdate) {
		var lastBreakpoint = currentBreakpoint,
			debugMsg = 'Triggered window events : ';

		if (browserIsSupported) {
			currentBreakpoint = getCurrentBreakpoint();
		}

		// Breakpoint has changed, or forced to update
		if (currentBreakpoint != lastBreakpoint || forceUpdate === true) {

			if (currentBreakpoint != lastBreakpoint) {
				$window.trigger('bpExit_' + lastBreakpoint);

				if (config.manageBodyClasses) {
					$body.removeClass('bp_' + lastBreakpoint);
				}

				if (config.debug) {
					debugMsg += '"bpExit_' + lastBreakpoint + '", ';
				}

			}

			$window
				.trigger('bpEnter_' + currentBreakpoint)
				.trigger('bpChange', [currentBreakpoint]);

			updateBackgrounds();

			if (config.manageBodyClasses) {
				$body.addClass('bp_' + currentBreakpoint);
			}

			if (config.debug) {
				debugMsg += '"bpEnter_' + currentBreakpoint + '", "bpChange".';
				console.log(debugMsg);
			}
		}
	}

	/**
	 * UPDATE BACKGROUNDS
	 * 
	 * Update background image for elements which have a
	 * data-bg-currentBreakpointName attribute specified.
	 *
	 * If pixel ratio is specified, it will be used
	 *
	 * Examples
	 * 		<div data-bg-lg="image-lg.jpg" data-bg-md="image-md.jpg"></div>
	 * 		<div data-bg-lg-2x="image-lg@2x.jpg"></div>
	 * 		...
	 *
	 * @return {undefined}
	 */
	function updateBackgrounds() {
		// var density = window.devicePixelRatio || 1;
		var density = 1;

		$bg.each(function() {
			var bg = '';

			if ($(this).attr('data-bg-' + currentBreakpoint + '-' + density + 'x')) {
				bg = $(this).attr('data-bg-' + currentBreakpoint + '-' + density + 'x');
			} else if ($(this).attr('data-bg-' + currentBreakpoint)) {
				bg = $(this).attr('data-bg-' + currentBreakpoint);
			}

			$(this).css({
				'background-image' : bg
			});

		});
	}

	/**
	 * TRIGGER BREAKPOINT EVENTS
	 * Trigger custom events attached to current breakpoint.
	 *
	 * @public
	 */
	function triggerBreakpointEvents() {
		updateBreakpointsState(true);
	}

	/**
	 * SET CONFIG
	 * Update module's configuration from provided values
	 * 
	 * @public
	 * @param {Object} args Properties of config object to update
	 * @peoperty {Boolean} args.manageBodyClasses Add class with breakpoint name to body ?
	 * @peoperty {Boolean} args.debug Log events that get triggered ?
	 */
	function setConfig(args) {
		config.manageBodyClasses = typeof args.manageBodyClasses == 'boolean' ? args.manageBodyClasses : config.manageBodyClasses;
		config.debug = typeof args.debug == 'boolean' ? args.debug : config.debug;

		// Add or reset body classes ?
		if (typeof args.manageBodyClasses == 'boolean' && args.manageBodyClasses === true) {
			$body.addClass('bp_' + currentBreakpoint);
		} else if (typeof args.manageBodyClasses == 'boolean' && args.manageBodyClasses === false) {
			$body.removeClass('bp_' + currentBreakpoint);
		}
	}

	/**
	 * INIT
	 * Initialises module.
	 * Self-invoking.
	 *
	 * @private
	 */
	(function init() {
		// Set module globals
		$window = $window ? $window : $(window);
		$body = $body ? $body : $('body');
		
		currentBreakpoint = getCurrentBreakpoint();

		// Set window resize listener and trigger once to call attached event handlers (on load)
		if (browserIsSupported) {
			$window
				.on('resize.bv_mq', updateBreakpointsState)
				.on('load', function() {
					updateBreakpointsState(true);
				});

		// Unsupported browser
		} else {
			currentBreakpoint = config.unsupportedBrowserDefault ? config.unsupportedBrowserDefault : currentBreakpoint;
		}
	})();

	// Return public methods
	return {
		setConfig : setConfig,
		getCurrentBreakpoint : getCurrentBreakpoint,
		triggerBreakpointEvents : triggerBreakpointEvents
	};
})(jQuery);

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
/*
	CATEGORIES MENU
	------

	Sidebar category menu (/produits).
 */
;(function sidebarCategories($) {

	$('.cat-parent .children').hide();
	$('.cat-parent').append('<a class="js-catToggle"></a>');

	// Open parents toggles on sub-category page
	$('.cat-parent').each(function() {
		if( $(this).hasClass('current-cat-parent') ){
			$(this).children('.children').show();
			$(this).children('.js-catToggle').addClass('open');
		}
	});

	// Toggle
	$('.js-catToggle').click(function() {
		$(this).toggleClass('open');
		$(this).siblings('.children').slideToggle();
	});

})(jQuery);

/*
	HEADER
	------

	Set header classes on scroll.
	Available classes :
		"nav-down" : User is scrolling up, the nav is down
		"nav-up" : User is scrolling down, the nav is up
 */
;(function header($) {

	/**
	 * SCROLL
	 *
	*/
	var didScroll;
	var lastScrollTop = 0;
	var delta = 5;
	var navbarHeight = 0;
	var win = $(window).height();

	$(window).scroll(function(event){
	    didScroll = true;
	});

	setInterval(function() {
	    if (didScroll) {
	        hasScrolled();
	        didScroll = false;
	    }
	}, 250);

	function hasScrolled() {
	    var st = $(this).scrollTop();

	    if(Math.abs(lastScrollTop - st) <= delta)
	        return;

	    // If they scrolled down and are past the navbar, add class .nav-up.
	    if($(window).width() >= 980){
		    if (st > lastScrollTop && st > navbarHeight){
		        // Scroll Down
		        $('#header').removeClass('nav-down').addClass('nav-up');
		        $('#menu-main-nav').css('padding-left', '5.313em');
		        $('.logo-small').fadeIn('fast');
		    } else {
		        // Scroll Up
		        if(st + $(window).height() < $(document).height()) {
		            $('#header').removeClass('nav-up').addClass('nav-down');
		            $('.logo-small').fadeOut('fast');
		            $('#menu-main-nav').css('padding-left', '0');
		        }
		    }
		    lastScrollTop = st;
	    }
	}

	$('#menu-main-nav').prepend('<a class="logo-small" href="/" style="display:none;"></a>');

})(jQuery);
/*
	Map
	------

	Google Maps Hacks and tricky tricks

 */
;(function mapHacks($) {

	// $('#map__canvas').click(function(){
	// 	$('.gm-style-iw').hide();
	// });

})(jQuery);
/*
	MOBILE MENU
	------

	Mobile Menu function

 */
;(function mobileMenu($) {

	$(".main-nav__btn").on('click', function(){
		$('.js-main-nav-wrap').toggleClass('open');
		$(this).toggleClass('opened');
	});

	$(".main-nav").find(".menu-item-has-children").prepend('<span class="submenu-button"></span>');
	$(".main-nav").find('.submenu-button').on('click', function() {
        $(this).toggleClass('opened');
        if ($(this).siblings('ul').hasClass('open')) {
        	$(this).siblings('ul').removeClass('open').slideUp("slow");
        }
        else {
        	$(this).siblings('ul').addClass('open').slideDown("slow");
        }
    });

	// Close mobile menu after clicking on a submenu anchor
    $('#menu-menu-principal li ul a').click(function(){

		if($(window).width() <= 991){
			$('#menu-button').trigger("click");
		}

	});

})(jQuery);
/*
	PARALLAX
	------

	Adding a subtile parallax effect to background images

 */
;(function parallaxBg($) {

	$(window).scroll(function() {
		var scrollTop = $(window).scrollTop();
		var divam = -10;
		$(".js-parallax").css({
			"background-position":"center "+scrollTop/divam+"px"
		});
    });


})(jQuery);
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
/*
	SWIPER
	------

	Calling Swiper on the home page

 */
;(function homeSwiper($) {

	// Kickstart the progress bar
	jQuery('.progressbar__fill').css({'width': '100%', 'transition' : '6s'});

	// Home page
	var swiper = new Swiper('.swiper-container', {
	    paginationClickable: true,
	    spaceBetween: 0,
	    loop: true,
		autoplay: {
	        delay: 6000,
	        disableOnInteraction: false,
	    },
	    navigation: {
	        nextEl: '.swiper-button-next',
	        prevEl: '.swiper-button-prev',
	    },
	});
	swiper.on('slideChangeTransitionStart', function () {
		jQuery('.progressbar__fill').css({'width': '0%', 'transition' : 'none'});
	});
	swiper.on('slideChangeTransitionEnd', function () {
		jQuery('.progressbar__fill').css({'width': '100%', 'transition' : '6s'});
	});

})(jQuery);
/*
	TOGGLE
	------

	Ajouter la classe js-toggle sur le titre et placer le contenu à “toggler” directement après.

	Markup Example:
	<h2 class="js-toggle">Click me!</h2>
	<div>
		Content to toggle.
	</div>

 */
;(function toggle($) {

	$('.js-toggle').click( function() {
		$(this).next().slideToggle();
		$(this).toggleClass('closed');
	});

	$('.widget-title').click( function() {
		$(this).next().slideToggle();
		$(this).toggleClass('closed');
	});


	$(window).on("resize", function () {
	 //    if ($(window).width() < 761) {
		//    $('.js-toggle').addClass('closed');
		//    $('.js-toggle').next().hide();

		//    $('.widget-title').addClass('closed');
		//    $('.widget-title').next().hide();
		// }
	}).resize();


})(jQuery);
jQuery(document).ready(function($) {

var is_xs, is_sm, is_md, is_lg, is_xl;

$( function() {
    document.addEventListener( 'touchstart', function() {}, true );

    document.addEventListener( 'gesturestart', function(e) {
        e.preventDefault();
    });
});

function bravadRefreshBp() {
    is_xs = false;
    is_sm = false;
    is_md = false;
    is_lg = false;
    is_xl = false;
}

$( window ).on( 'bpEnter_xs', function() {
    bravadRefreshBp();

    is_xs = true;

    $( '.js-toggle, .widget-title' ).each( function() {
    	var me = $( this );

    	me.next().hide();
    });
});

$( window ).on( 'bpEnter_sm', function() {
    bravadRefreshBp();
    
    is_sm = true;
});

$( window ).on( 'bpEnter_md', function() {
    bravadRefreshBp();
    
    is_md = true;
});

$( window ).on( 'bpEnter_lg', function() {
    bravadRefreshBp();
    
    is_lg = true;
});

$( window ).on( 'bpEnter_xl', function() {
    bravadRefreshBp();
    
    is_xl = true;
});

$( window ).on( 'load', function() {
	bravadRefreshBp();
    svg4everybody();
});

/* ====================================================================================================

	Toggle

==================================================================================================== */



/* ====================================================================================================

	Hide Header on on scroll down

==================================================================================================== */
var didScroll;
var lastScrollTop = 0;
var delta = 5;
var navbarHeight = 0;
var win = $(window).height();

$(window).scroll(function(event){
    didScroll = true;
});

setInterval(function() {
    if (didScroll) {
        hasScrolled();
        didScroll = false;
    }
}, 250);

function hasScrolled() {
    var st = $(this).scrollTop();

    // Make sure they scroll more than delta
    if(Math.abs(lastScrollTop - st) <= delta)
        return;

    // If they scrolled down and are past the navbar, add class .nav-up.
    // This is necessary so you never see what is "behind" the navbar.
    if (st > lastScrollTop && st > navbarHeight){
        // Scroll Down
        $('header').removeClass('nav-down').addClass('nav-up');
    } else {
        // Scroll Up
        if(st + $(window).height() < $(document).height()) {
            $('header').removeClass('nav-up').addClass('nav-down');
        }
    }
    lastScrollTop = st;
}


// Scrolled Color
$(function() {
    var header = $("#header");
    $(window).load(function() {
	    var scroll = $(window).scrollTop();
	    if (scroll >= 65) {
            header.addClass("scrolled");
        } else {
            header.removeClass("scrolled");
        }
    });

    $(window).scroll(function() {
	    var scroll = $(window).scrollTop();
        if (scroll >= 65) {
            header.addClass("scrolled");
        } else {
            header.removeClass("scrolled");
        }
    });
});

// Sub Menu dynamic width
function submenuSizing() {
	$('.sub-menu').width( $('#menu-menu-principal').width() );
}

submenuSizing();

$( window ).resize(function() {
	submenuSizing();
});



/* ====================================================================================================

	Tooltip

==================================================================================================== */
	$( function() {
	    // $( document ).tooltip();
	} );


/* ====================================================================================================

	Swiper

==================================================================================================== */

	// Home Primary
	var galleryTop = new Swiper('.gallery-top', {
        spaceBetween: 0,
    });

    var galleryThumbs = new Swiper('.gallery-thumbs', {
        slideToClickedSlide: true,
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
    });
    galleryTop.params.control = galleryThumbs;
    galleryThumbs.params.control = galleryTop;


    // Home Latest Blog Posts
    var swiper = new Swiper('.latest_articles', {
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
        slidesPerView: 3,
        paginationClickable: true,
        spaceBetween: 0,
        breakpoints: {
            991: {
                slidesPerView: 1,
                spaceBetween: 0
            }
        }
    })

    // Slider flexible
    var swiper2 = new Swiper('.carrousel', {
        slidesPerView: 4,
        paginationClickable: true,
        spaceBetween: 0,
        loop: true,
		autoplay: 5000,
        breakpoints: {
            991: {
                slidesPerView: 2,
            },
            640: {
                slidesPerView: 1,
            }
        }
    });

    // Trousse
    var swiper3 = new Swiper('.trousse', {
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
        slidesPerView: 3,
        direction: 'vertical',
        paginationClickable: true,
        spaceBetween: 50,
        breakpoints: {
            768: {
                slidesPerView: 1,
                spaceBetween: 0,
                direction: 'horizontal',
            }
        }
    });


/* ====================================================================================================

	Lines width

==================================================================================================== */
	setTimeout(function()  {
		$('.team_title .border-gris.bottom').css('width', $('.team_title .border-gris.top').width() );
	}, 1000);


/* ====================================================================================================

	Mobile Menu

==================================================================================================== */
	$("#menu-button").click(function(){
		$('#header').toggleClass('open');
		$('#menu').toggleClass('active');
		$('body').toggleClass('fixed');
		$(this).toggleClass('menu-opened');
	});

	$("#cssmenu").find('li ul').parent().addClass('has-sub');
	$("#cssmenu").find(".has-sub").prepend('<span class="submenu-button"><img class="svg" src="/wp-content/themes/bravad/assets/dist/img/arrow.svg"></span>');
	$("#cssmenu").find('.submenu-button').on('click', function() {
        $(this).toggleClass('submenu-opened');
        if ($(this).siblings('ul').hasClass('open')) {
        	$(this).siblings('ul').removeClass('open').slideToggle("slow");
        } else {
        	$(this).siblings('ul').addClass('open').slideToggle("slow");
        }
    });


	// Mobile menu fix
	if (!$("body").hasClass("page-template-extranet")) {
		var divs = $("#menu > div");
		for(var i = 0; i < divs.length; i+=2) {
		  divs.slice(i, i+2).wrapAll("<div class='row'><div class='row-inner'></div></div>");
		}
	}


/* ====================================================================================================

	Videos Slider

==================================================================================================== */

	// Fancybox
	if ($("body").hasClass("home")) {
		$(".video").click(function() {
			var addressValue = $(this).attr("href");
			$.fancybox({
				'transitionIn'	: 'fade',
				'transitionOut'	: 'fade',
				'title'			: this.title,
				'href'			: addressValue.replace(new RegExp("watch\\?v=", "i"), 'v/'),
				'type'			: 'iframe',
			});
			return false;
		});
	}

	// Pagers
	$('#pager1').addClass('active');
	$("[id^=pager]").click(function() {
		$(".pager").removeClass('active');
		$(".bloc_video").fadeOut('slow');
		$('#video'+ this.id.match(/\d+/) ).fadeIn("slow");
		$(this).addClass('active');
	});


/* ====================================================================================================

	Fancybox

==================================================================================================== */
	$(".fancybox").fancybox({
		openEffect	: 'fade',
		closeEffect	: 'fade',
		autoResize : true,
		maxWidth : '60%'
	});


/* ====================================================================================================

	Newsletter Validate

==================================================================================================== */
	$( 'form.validate, form.validate1' ).on( 'submit', function() {
	    if( !$( this ).valid() ) {
	        var me = $( this ),
	            msg = me.find( '.alert' ).attr( 'data-msg' );
	            me.find( '.alert' )
	                .text( msg )
	                .addClass( 'invalid show' );
	        return false;
	    }
	});

	$( 'form.validate' ).validate({
	    rules: {
	        email: {
	            required: true,
	            new_email: true
	        },
	    },
	    errorElement: 'span',
	    errorClass: 'invalid',
	    messages: {
	        required: $( this ).attr( 'data-msg' )
	    }
	});

	$( 'form.validate1' ).validate({
	    rules: {
	        email: {
	            required: true,
	            new_email: true
	        },
	    },
	    errorElement: 'span',
	    errorClass: 'invalid',
	    messages: {
	        required: $( this ).attr( 'data-msg' )
	    }
	});

	jQuery.validator.addMethod( 'new_email', function( value, element ) {
        return this.optional( element ) || /^[a-z0-9._-]+@[a-z0-9.-]{2,}[.][a-z]{2,3}$/.test( value );
    }, "L’adresse courriel n’est pas valide.");





/* ====================================================================================================

	Flexible content

==================================================================================================== */
$('.side_img_wrap').css('height', $('#pageheader').height() );


/* ====================================================================================================

	Goto

==================================================================================================== */
	if($(window).width() >= 992){
		if( $( '.sidebar_texte a' ).length > 0 ) {
			$( '.sidebar_texte a[href^="#"]' ).on( 'click', function() {
				var me =$( this ),
					the_id = me.attr( 'href' );

				$( 'html, body' ).animate( {
					scrollTop: $( the_id ).offset().top-100
				}, 'slow' );
				return false;
			});
		}
	}

	if($(window).width() <= 991){
		if( $( '.sidebar_texte a' ).length > 0 ) {
			$( '.sidebar_texte a[href^="#"]' ).on( 'click', function() {
				var me =$( this ),
					the_id = me.attr( 'href' );
				$( 'html, body' ).animate( {
					scrollTop: $( the_id ).offset().top-65
				}, 'slow' );
				return false;
			});
		}
	}

});



/* ====================================================================================================

	FAQ Accordion

==================================================================================================== */
jQuery(document).ready(function($) {

	$("[id^=toggle-title]").click(function(e) {

		e.preventDefault();

	    var $this = $(this).parent().find('[id^=toggle-inner]');
	    var $thisimage = $('#image-symptomes-'+ this.id.match(/\d+/) );

	    $(this).toggleClass('active');

	    $('.toggle [id^=toggle-inner]').not($this).slideUp('slow');
	    $('[id^=image-symptomes]').not($thisimage).fadeOut('slow');

	    $this.slideToggle('slow');
	    $thisimage.fadeIn('slow');

	});

	$('#toggle-title-1').trigger('click');

});



/*
if( jQuery(".toggle .toggle-title").hasClass('active') ){

		jQuery(".toggle .toggle-title.active").closest('.toggle').find('.toggle-inner').show();
		jQuery(".toggle .toggle-title.active").closest('.image-symptomes').find('.toggle-inner').show();



	}

	jQuery(".toggle .toggle-title").click(function(){

		if( jQuery(this).hasClass('active') ){

			jQuery(this).removeClass("active").closest('.toggle').find('.toggle-inner').slideUp(200);

		} else {
			jQuery(this).addClass("active").closest('.toggle').find('.toggle-inner').slideDown(200);
			jQuery(this).closest('.image-symptomes').find('.toggle-inner').show();
		}



});
*/



jQuery(".toggle .toggle-title").click(function() {
	jQuery(this).find('.plus').toggleClass('active');
});





/* ====================================================================================================

	Smooth scroll

==================================================================================================== */
jQuery('.scrollTo').on('click', function() {
	var page = jQuery(this).attr('href');
	var speed = 750;
	jQuery('html, body').animate( { scrollTop: jQuery('#scroll').offset().top }, speed ); // Go
	return false;
});
//# sourceMappingURL=data:application/json;charset=utf8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIl9taW5pLWNhcnQuanMiLCJwcm9kdWN0LWdyaWQuanMiLCJhY2NvdW50LW5hdi5qcyIsImFqYXgtcHJvZHVpdHMtcHJvbW8uanMiLCJhamF4LXByb2R1aXRzLXZlZGV0dGUuanMiLCJicmVha3BvaW50LmpzIiwiY2FydC5qcyIsImNhdGVnb3JpZXMtbWVudS5qcyIsImZpbHRlcnMuanMiLCJoZWFkZXIuanMiLCJtYXAuanMiLCJtb2JpbGVtZW51LmpzIiwicGFyYWxsYXguanMiLCJzbGlkZXItcHJvZHVjdC5qcyIsInNsaWRlci5qcyIsInRvZ2dsZS5qcyIsImZ1bmN0aW9ucy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FDeElBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQ3BFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQ1pBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUMzRkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUN6REE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FDeE1BO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUMvQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQ3pCQTtBQ0FBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQzNEQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FDYkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQ2xDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQ2xCQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQzdDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQ2pDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FDckNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJmaWxlIjoiZnVuY3Rpb25zLmpzIiwic291cmNlc0NvbnRlbnQiOlsiLypcblx0TWluaWNhcnQgcHJlc2VudCBvbiBtYWluIG1lbnUuXG5cblx0Q29yZSBvcGVyYXRpb25zIGFyZSBhbHJlYWR5IG1hbmFnZWQgbmF0aXZlbHkgYnkgV29vY29tbWVyY2UsIGhlcmUgd2Vcblx0b25seSBkbyBjdXN0b20gdmlzdWFsIGFkanVzdG1lbnRzLlxuICovXG47bWluaWNhcnQgPSAoZnVuY3Rpb24oJCkge1xuXHR2YXIgJGNhcnRSZXN1bHQgPSAkKCcuY2FydC1yZXN1bHQnKSxcblx0XHQkY2FydExvYWRpbmcgPSAkKCcuY2FydC1sb2FkaW5nJyksXG5cdFx0JGFkZHRvY2FydGJ0biA9ICQoJy5hamF4X2FkZF90b19jYXJ0Jyk7XG5cblx0LyoqXG5cdCAqIElOSVRJQUxJU0Vcblx0ICogLS0tLS0tLS0tLVxuXHQgKlxuXHQgKiBAcmV0dXJuIHt1bmRlZmluZWR9XG5cdCAqL1xuXHQoZnVuY3Rpb24gaW5pdCgpIHtcblx0XHQvLyBtYW5hZ2VBZGRUb0NhcnRCdG4oKTtcblx0XHQvLyB1cGRhdGVNaW5pQ2FydCgpO1xuXHR9KSgpO1xuXG5cdC8qKlxuXHQgKiBVUERBVEUgTUlOSSBDQVJUIENPTlRFTlRcblx0ICogLS0tLS0tLS0tLVxuXHQgKlxuXHQgKiBVcGRhdGUgdGhlIG1pbmktY2FydCBhbW91bnQgYW5kIGl0ZW0gcXR5IHdoZW4gY2FsbGVkLlxuXHQgKi9cblx0ZnVuY3Rpb24gdXBkYXRlTWluaUNhcnQoKSB7XG5cdFx0dmFyIGRhdGEgPSB7XG5cdFx0XHQnYWN0aW9uJzogJ2xvYWRfd29vX2NhcnQnXG5cdFx0fTtcblxuXHRcdCRjYXJ0UmVzdWx0LmNzcyh7XG5cdFx0XHQnb3BhY2l0eSc6JzAnLFxuXHRcdFx0J3dpZHRoJzonMzBweCcsXG5cdFx0fSk7XG5cblx0XHQkY2FydExvYWRpbmcuZmFkZUluKCk7XG5cblx0XHRqUXVlcnkucG9zdCggd29vY29tbWVyY2VfcGFyYW1zLmFqYXhfdXJsLCBkYXRhLCBmdW5jdGlvbiggcmVzcG9uc2UgKSB7XG5cdFx0XHQkY2FydExvYWRpbmcuZmFkZU91dCgpO1xuXHRcdFx0JGNhcnRSZXN1bHQuY3NzKHtcblx0XHRcdFx0J29wYWNpdHknOicxJyxcblx0XHRcdFx0J3dpZHRoJzonMTUwcHgnLFxuXHRcdFx0fSk7XG5cdFx0XHQkY2FydFJlc3VsdC5odG1sKHJlc3BvbnNlKTtcblx0XHRcdG1hbmFnZVJlbW92ZUNhcnRCdG4oKTtcblx0XHR9KTtcblx0fVxuXG5cdC8qKlxuXHQgKiBNQU5BR0UgUkVNT1ZFIENBUlQgQlVUVE9OXG5cdCAqIC0tLS0tLS0tLS1cblx0ICpcblx0ICogQmluZCBsaXN0ZW5lciB0byByZW1vdmUgYnV0dG9uLCB3aGljaCBpcyBub3QgY2FjaGVkXG5cdCAqIGJlY2F1c2UgaXQgaXMgY29uc3RhbnRseSBhZGRlZCAvIHJlbW92ZWQgZnJvbSBET01cblx0ICovXG5cdGZ1bmN0aW9uIG1hbmFnZVJlbW92ZUNhcnRCdG4oKSB7XG5cblx0XHQkKCcucmVtb3ZlX2Zyb21fY2FydF9idXR0b24nKVxuXHRcdFx0Lm9mZignY2xpY2subWFuYWdlUmVtb3ZlQ2FydEJ0bicpXG5cdFx0XHQub24oJ2NsaWNrLm1hbmFnZVJlbW92ZUNhcnRCdG4nLCBmdW5jdGlvbigpIHtcblx0XHRcdFx0dmFyIGRhdGFQcm9kdWN0SUQgPSAkKHRoaXMpLmF0dHIoJ2RhdGEtcHJvZHVjdF9pZCcpO1xuXG5cdFx0XHRcdC8vIFVwZGF0ZSBidXR0b24gbGFiZWwgKGZvciBvdGhlciBpbnN0YW5jZSBvZiB0aGlzIHByb2R1Y3Qgb24gdGhlIHBhZ2UpXG5cdFx0XHRcdCQoJy5hZGRfdG9fY2FydF9idXR0b25bZGF0YS1wcm9kdWN0X2lkPScgKyBkYXRhUHJvZHVjdElEICsgJ10nKS5yZW1vdmVDbGFzcygnYWRkZWQnKTtcblx0XHRcdFx0JCgnLmFkZF90b19jYXJ0X2J1dHRvbltkYXRhLXByb2R1Y3RfaWQ9JyArIGRhdGFQcm9kdWN0SUQgKyAnXScpLnRleHQoJ0Fqb3V0ZXIgYXUgcGFuaWVyJykuc2hvdygpO1xuXHRcdFx0XHQkKCcuYWRkX3RvX2NhcnRfYnV0dG9uW2RhdGEtcHJvZHVjdF9pZD0nICsgZGF0YVByb2R1Y3RJRCArICddJykuYXR0cignZGlzYWJsZWQnLCBmYWxzZSk7XG5cdFx0XHRcdCQoJy5hZGRfdG9fY2FydF9idXR0b25bZGF0YS1wcm9kdWN0X2lkPScgKyBkYXRhUHJvZHVjdElEICsgJ10nKS5zaWJsaW5ncygnLmluLWNhcnQnKS5oaWRlKCk7XG5cdFx0XHRcdFxuXHRcdFx0XHQvLyBVcGRhdGUgbWluaSBjYXJ0XG5cdFx0XHRcdHVwZGF0ZU1pbmlDYXJ0KCk7XG5cdFx0XHR9KTtcblxuXHR9XG5cblx0LyoqXG5cdCAqIE1BTkFHRSBBREQgVE8gQ0FSVCBCVVRUT01cblx0ICogLS0tLS0tLS0tLVxuXHQgKlxuXHQgKiBCaW5kIOKAnEFkZCB0byBjYXJ04oCdIGJ1dHRvbiBjbGljayBsaXN0ZW5lci5cblx0ICogVGhlcmUgaXMgMiBjYXNlcyB0byBtYW5hZ2UgOlxuXHQgKiBcdC0gPGE+IHRhZyB3aXRoIHByb2R1Y3QgaWQgYXMgXCJkYXRhLXByb2R1Y3RfaWRcIiBhdHRyaWJ1dGVcblx0ICogXHQtIDxidXR0b24+IHRhZywgd2l0aCBxdWFudGl0eSBpbnB1dCBhcyBzaWJsaW5nXG5cdCAqL1xuXHRmdW5jdGlvbiBtYW5hZ2VBZGRUb0NhcnRCdG4oKSB7XG5cdFx0JCgnLmFkZF90b19jYXJ0X2J1dHRvbicpXG5cdFx0XHQub2ZmKCdjbGljay5tYW5hZ2VBZGRUb0NhcnRCdG4nKVxuXHRcdFx0Lm9uKCdjbGljay5tYW5hZ2VBZGRUb0NhcnRCdG4nLCBmdW5jdGlvbihldikge1xuXHRcdFx0XHR2YXIgZGF0YVByb2R1Y3RJRCA9ICQodGhpcykuYXR0cignZGF0YS1wcm9kdWN0X2lkJyk7XG5cblx0XHRcdFx0Ly8gPGE+IHRhZyB3aXRoIHByb2R1Y3QgaWQgYXMgXCJkYXRhLXByb2R1Y3RfaWRcIiBhdHRyaWJ1dGVcblx0XHRcdFx0aWYgKGRhdGFQcm9kdWN0SUQpIHtcblxuXHRcdFx0XHRcdC8vIFByb2R1Y3QgaXMgYWxyZWFkeSBhZGRlZFxuXHRcdFx0XHRcdGlmKCAkKHRoaXMpLmhhc0NsYXNzKCdhZGRlZCcpICkge1xuXHRcdFx0XHRcdFx0cmV0dXJuIGZhbHNlO1xuXG5cdFx0XHRcdFx0Ly8gUHJldmVudCBwcm9kdWN0IGZyb20gYmVpbmcgYWRkZWQgYWdhaW5cblx0XHRcdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRcdFx0JCh0aGlzKS5hdHRyKCdkaXNhYmxlZCcsIHRydWUpO1xuXHRcdFx0XHRcdFx0JCh0aGlzKS50ZXh0KCdEw6lqw6AgZGFucyB2b3RyZSBwYW5pZXInKTtcblxuXHRcdFx0XHRcdFx0Ly8gVXBkYXRlIGJ1dHRvbiBsYWJlbCAoZm9yIG90aGVyIGluc3RhbmNlIG9mIHRoaXMgcHJvZHVjdCBvbiB0aGUgcGFnZSlcblx0XHQgXHRcdFx0XHQkKCcuYWRkX3RvX2NhcnRfYnV0dG9uW2RhdGEtcHJvZHVjdF9pZD0nICsgZGF0YVByb2R1Y3RJRCArICddJykuYWRkQ2xhc3MoJ2FkZGVkJyk7XG5cdFx0IFx0XHRcdFx0JCgnLmFkZF90b19jYXJ0X2J1dHRvbltkYXRhLXByb2R1Y3RfaWQ9JyArIGRhdGFQcm9kdWN0SUQgKyAnXScpLnRleHQoJ0TDqWrDoCBkYW5zIHZvdHJlIHBhbmllcicpLnNob3coKTtcblx0XHQgXHRcdFx0XHQkKCcuYWRkX3RvX2NhcnRfYnV0dG9uW2RhdGEtcHJvZHVjdF9pZD0nICsgZGF0YVByb2R1Y3RJRCArICddJykuYXR0cignZGlzYWJsZWQnLCB0cnVlKTtcblxuXHRcdFx0XHRcdFx0Ly8gVXBkYXRlIG1pbmkgY2FydFxuXHRcdFx0XHRcdFx0Ly8gQHRvZG8gOiBNYWtlIHN1cmUgaXQgaGFwcGVucyBhZnRlciBXb29jb21tZXJjZSBhc3luYyBhZGQgdG8gY2FydCBjb21tYW5kXG5cdFx0XHRcdFx0XHQvLyBAc2VlIGh0dHBzOi8vaW5zdGFudGNvbXB0YW50LmJyYXZhZC1kZXYuY29tL3dwLWNvbnRlbnQvcGx1Z2lucy93b29jb21tZXJjZS9hc3NldHMvanMvZnJvbnRlbmQvYWRkLXRvLWNhcnQubWluLmpzIChhLnByb3RvdHlwZS5vbkFkZFRvQ2FydClcblx0XHRcdFx0XHRcdG1pbmljYXJ0LnVwZGF0ZU1pbmlDYXJ0KCk7XG5cdFx0XHRcdFx0fVxuXG5cdFx0XHRcdC8vIDxidXR0b24+IHRhZywgd2l0aCBxdWFudGl0eSBpbnB1dCBhcyBzaWJsaW5nXG5cdFx0XHRcdH0gZWxzZSB7XG5cblx0XHRcdFx0fVxuXG5cblx0XHRcdH0pO1xuXG5cdFx0aWYgKCQoJy5hZGRfdG9fY2FydF9idXR0b24nKS5pcygnOmVtcHR5Jykpe1xuXHRcdCAgJCh0aGlzKS5oaWRlKCk7XG5cdFx0fVxuXG5cdH1cblxuXHQvKlxuXHRcdFJldHVybiBwdWJsaWMgbWV0aG9kc1xuXHQgKi9cblx0cmV0dXJuIHtcblx0XHR1cGRhdGVNaW5pQ2FydCA6IHVwZGF0ZU1pbmlDYXJ0XG5cdH1cblxufSkoalF1ZXJ5KTsiLCIvKlxuXHRQUk9EVUNUIEdSSURcblx0LS0tLS0tXG5cbiAqL1xuXG5cbjtwcm9kdWN0Z3JpZCA9IChmdW5jdGlvbigkKSB7XG5cblxuXHQvKipcblx0ICogSU5JVElBTElTRVxuXHQgKiAtLS0tLS0tLS0tXG5cdCAqXG5cdCAqIEByZXR1cm4ge3VuZGVmaW5lZH1cblx0ICovXG5cdChmdW5jdGlvbiBpbml0KCkge1xuXHRcdHRodW1iUmVzaXplKCk7XG5cdH0pKCk7XG5cblxuXHQvKipcblx0ICogVEhVTUJOQUlMIFJFU0laRVxuXHQgKiAtLS0tLS0tLS0tXG5cdCAqXG5cdCAqIE1ha2VzIHN1cmUgdGhhdCB0aGUgcHJvZHVjdCB0aHVtYm5haWxzIGFyZSBhbGwgc3F1YXJlXG5cdCAqL1xuXHRmdW5jdGlvbiB0aHVtYlJlc2l6ZSgpIHtcblx0XHQkKCcucHJvZHVjdF9fdGh1bWInKS5oZWlnaHQoICQoJy5wcm9kdWN0X190aHVtYicpLndpZHRoKCkgKTtcbi8vIFx0XHQkKCcud29vY29tbWVyY2UtcHJvZHVjdC1nYWxsZXJ5X19pbWFnZScpLmhlaWdodCggJCgnLnByb2R1Y3RfX3RodW1iJykud2lkdGgoKSApO1xuXHR9XG5cblx0Ly8gQ2FsbCBmdW5jdGlvbiBvbiBwYWdlIHJlc2l6ZVxuXHQkKHdpbmRvdykub24oJ3Jlc2l6ZScsIGZ1bmN0aW9uKCl7XG5cdFx0dGh1bWJSZXNpemUoKTtcblx0fSk7XG5cblxuXHQvLyBDYWxsIGZ1bmN0aW9uIHdoZW4gZmlsdGVyaW5nIHByb2R1Y3RzXG5cdCQoIFwiLndpZGdldCBsYWJlbFwiICkuY2xpY2soZnVuY3Rpb24oKSB7XG5cdFx0Y2hlY2tGb3JDaGFuZ2VzKCk7XG5cdH0pO1xuXG5cblx0LyoqXG5cdCAqIEdSSUQgQ09OVEFJTkVSIFNJWkUgTE9PS1VQXG5cdCAqIC0tLS0tLS0tLS1cblx0ICpcblx0ICogV2F0Y2ggdGhlIGNvbnRhaW5lciBmb3Igc2l6ZSBjaGFuZ2VzLiBSZXNpemUgdGhlIHRodW1ibmFpbHMgd2hlbiBpdCBoYXBwZW5zLlxuXHQgKi9cblx0dmFyICRwcm9kdWN0R3JpZCA9ICQoXCIucHJvZHVjdHNcIik7XG5cdHZhciBsYXN0SGVpZ2h0ID0gJChcIi5wcm9kdWN0c1wiKS5jc3MoJ2hlaWdodCcpO1xuXG5cdGZ1bmN0aW9uIGNoZWNrRm9yQ2hhbmdlcygpIHtcblx0ICAgIGlmIChwcm9kdWN0R3JpZC5jc3MoJ2hlaWdodCcpICE9IGxhc3RIZWlnaHQpIHtcblx0ICAgICAgIHRodW1iUmVzaXplKCk7XG5cdCAgICB9XG5cdFx0c2V0VGltZW91dChjaGVja0ZvckNoYW5nZXMsIDUwMCk7XG5cdH1cblxuXG5cdC8qXG5cdFx0UmV0dXJuIHB1YmxpYyBtZXRob2RzXG5cdCAqL1xuXHRyZXR1cm4ge1xuXHRcdHRodW1iUmVzaXplIDogdGh1bWJSZXNpemUsXG5cdH1cblxufSkoalF1ZXJ5KTsiLCIvKlxuXHRBQ0NPVU5UIE5BVlxuXHQtLS0tLS1cblxuXHRNb2JpbGUgTmF2IGZvciB0aGUgV29vY29tbWVyY2UgYWNjb3VudFxuICovXG47KGZ1bmN0aW9uIGFjY291bnROYXYoJCkge1xuXG5cdCQoXCJzZWxlY3Qud29vX19zaWRlYmFyLS1saW5rc1wiKS5jaGFuZ2UoZnVuY3Rpb24oKSB7XG5cdCAgd2luZG93LmxvY2F0aW9uID0gJCh0aGlzKS5maW5kKFwib3B0aW9uOnNlbGVjdGVkXCIpLnZhbCgpO1xuXHR9KTtcblxufSkoalF1ZXJ5KTsiLCIvKlxuXHRQcm9kdWl0cyBQcm9tb1xuXHQtLS0tLS1cblxuICovXG5cbjsoZnVuY3Rpb24gbG9hZFByb21vcygkKSB7XG5cblx0aWYgKCQoJ2JvZHknKS5oYXNDbGFzcygnaG9tZScpKSB7XG5cblx0XHR2YXIgc3RyID0gJyZhY3Rpb249TG9hZFByb2R1aXRQcm9tbyc7XG5cdCAgICB2YXIgYWpheHVybCA9ICcvd3AtYWRtaW4vYWRtaW4tYWpheC5waHAnO1xuXHQgICAgalF1ZXJ5LmFqYXgoe1xuXHQgICAgICAgIHR5cGU6ICdHRVQnLFxuXHQgICAgICAgIGRhdGFUeXBlOiAnaHRtbCcsXG5cdCAgICAgICAgdXJsOiBhamF4dXJsLFxuXHQgICAgICAgIGRhdGE6IHN0cixcblx0ICAgICAgICBzdWNjZXNzOiBmdW5jdGlvbihkYXRhKSB7XG5cdFx0ICAgIFx0alF1ZXJ5KCcucHJvZHVjdHNfX2xvYWRpbmctLXByb21vJykuZmFkZU91dCgpO1xuXHRcdFx0XHRqUXVlcnkoJy5wcm9kdWN0c19fc2xpZGVyLS1wcm9tbyAuc3dpcGVyLXdyYXBwZXInKS5hcHBlbmQoZGF0YSkuY3NzKCdvcGFjaXR5JywgJzEnKTtcblxuXHRcdFx0XHQvLyBHZW5lcmF0ZSB0aGUgc2xpZGVyIHdpdGggZmV0Y2hlZCBwcm9kdWN0c1xuXHRcdFx0XHR2YXIgc3dpcGVyID0gbmV3IFN3aXBlcignLnByb2R1Y3RzX19zd2lwZXJjb250YWluZXItLXByb21vJywge1xuXHRcdFx0XHQgICAgcGFnaW5hdGlvbkNsaWNrYWJsZTogdHJ1ZSxcblx0XHRcdFx0ICAgIHNwYWNlQmV0d2VlbjogMjAsXG5cdFx0XHRcdCAgICBhdXRvcGxheToge1xuXHRcdFx0XHQgICAgXHRkZWxheTogNTAwMCxcblx0XHRcdFx0ICAgIH0sXG5cdFx0XHRcdCAgICBzbGlkZXNQZXJWaWV3OiA0LFxuXHRcdFx0XHQgICAgc2xpZGVzUGVyR3JvdXA6IDQsXG5cdFx0XHRcdCAgICBzcGVlZDogMTAwMCxcblx0XHRcdFx0ICAgIGxvb3A6IGZhbHNlLFxuXHRcdFx0XHQgICAgbmF2aWdhdGlvbjoge1xuXHRcdFx0XHQgICAgICAgIG5leHRFbDogJy5wcm9kdWN0c19fYnV0dG9uLS0tbmV4dDInLFxuXHRcdFx0XHQgICAgICAgIHByZXZFbDogJy5wcm9kdWN0c19fYnV0dG9uLS0tcHJldjInLFxuXHRcdFx0XHQgICAgfSxcblx0XHRcdFx0ICAgIGJyZWFrcG9pbnRzOiB7XG5cdFx0XHRcdCAgICAgICAgMTEwMDoge1xuXHRcdFx0XHQgICAgICAgIFx0c2xpZGVzUGVyVmlldzogMyxcblx0XHRcdFx0XHRcdFx0c3BhY2VCZXR3ZWVuOiA0MCxcblx0XHRcdFx0ICAgICAgICB9LFxuXHRcdFx0XHQgICAgICAgIDc2ODoge1xuXHRcdFx0XHQgICAgICAgIFx0c2xpZGVzUGVyVmlldzogMixcblx0XHRcdFx0XHRcdFx0c3BhY2VCZXR3ZWVuOiAyMCxcblx0XHRcdFx0ICAgICAgICB9LFxuXHRcdFx0XHQgICAgICAgIDY0MDoge1xuXHRcdFx0XHRcdCAgICAgICAgc2xpZGVzUGVyVmlldzogMSxcblx0XHRcdFx0XHRcdFx0c3BhY2VCZXR3ZWVuOiA0MCxcblx0XHRcdFx0ICAgICAgICB9XG5cdFx0XHRcdCAgICB9XG5cdFx0XHRcdH0pO1xuXG5cdFx0XHRcdC8vIG1pbmljYXJ0Lm1hbmFnZUFkZFRvQ2FydEJ0bigpO1xuXHRcdFx0XHRwcm9kdWN0Z3JpZC50aHVtYlJlc2l6ZSgpO1xuXG5cdCAgICAgICAgfVxuXHQgICAgfSk7XG5cbiAgICB9XG5cblxuICAgIC8vIFJlbGF0ZWQgUHJvZHVjdHNcbiAgICBpZiAoJCgnYm9keScpLmhhc0NsYXNzKCdzaW5nbGUtcHJvZHVjdCcpKSB7XG5cblx0ICAgIHZhciBzd2lwZTIgPSBuZXcgU3dpcGVyKCcucHJvZHVjdHNfX3N3aXBlcmNvbnRhaW5lci0tcHJvbW8nLCB7XG5cdFx0ICAgIHBhZ2luYXRpb25DbGlja2FibGU6IHRydWUsXG5cdFx0ICAgIHNwYWNlQmV0d2VlbjogMjAsXG5cdFx0ICAgIHNsaWRlc1BlclZpZXc6IDQsXG5cdFx0ICAgIGxvb3A6IGZhbHNlLFxuXHRcdCAgICBuYXZpZ2F0aW9uOiB7XG5cdFx0ICAgICAgICBuZXh0RWw6ICcucHJvZHVjdHNfX2J1dHRvbi0tLW5leHQyJyxcblx0XHQgICAgICAgIHByZXZFbDogJy5wcm9kdWN0c19fYnV0dG9uLS0tcHJldjInLFxuXHRcdCAgICB9LFxuXHRcdCAgICBicmVha3BvaW50czoge1xuXHRcdCAgICAgICAgMTEwMDoge1xuXHRcdCAgICAgICAgXHRzbGlkZXNQZXJWaWV3OiAzLFxuXHRcdFx0XHRcdHNwYWNlQmV0d2VlbjogNDAsXG5cdFx0ICAgICAgICB9LFxuXHRcdCAgICAgICAgNzY4OiB7XG5cdFx0ICAgICAgICBcdHNsaWRlc1BlclZpZXc6IDIsXG5cdFx0XHRcdFx0c3BhY2VCZXR3ZWVuOiAyMCxcblx0XHQgICAgICAgIH0sXG5cdFx0ICAgICAgICA2NDA6IHtcblx0XHRcdCAgICAgICAgc2xpZGVzUGVyVmlldzogMSxcblx0XHRcdFx0XHRzcGFjZUJldHdlZW46IDQwLFxuXHRcdCAgICAgICAgfVxuXHRcdCAgICB9XG5cdFx0fSk7XG5cblx0fVxuXG59KShqUXVlcnkpOyIsIi8qXG5cdFByb2R1aXRzIFZlZGV0dGVcblx0LS0tLS0tXG5cbiAqL1xuXG47KGZ1bmN0aW9uIGxvYWRWZWRldHRlKCQpIHtcblxuXHRpZiAoJCgnYm9keScpLmhhc0NsYXNzKCdob21lJykpIHtcblxuXHRcdHZhciBzdHIgPSAnJmFjdGlvbj1Mb2FkUHJvZHVpdFZlZGV0dGUnO1xuXHQgICAgdmFyIGFqYXh1cmwgPSAnL3dwLWFkbWluL2FkbWluLWFqYXgucGhwJztcblx0ICAgIGpRdWVyeS5hamF4KHtcblx0ICAgICAgICB0eXBlOiAnR0VUJyxcblx0ICAgICAgICBkYXRhVHlwZTogJ2h0bWwnLFxuXHQgICAgICAgIHVybDogYWpheHVybCxcblx0ICAgICAgICBkYXRhOiBzdHIsXG5cdCAgICAgICAgc3VjY2VzczogZnVuY3Rpb24oZGF0YSkge1xuXHRcdCAgICBcdGpRdWVyeSgnLnByb2R1Y3RzX19sb2FkaW5nLS12ZWRldHRlJykuZmFkZU91dCgpO1xuXHRcdFx0XHRqUXVlcnkoJy5wcm9kdWN0c19fc2xpZGVyLS12ZWRldHRlIC5zd2lwZXItd3JhcHBlcicpLmFwcGVuZChkYXRhKS5jc3MoJ29wYWNpdHknLCAnMScpO1xuXG5cdFx0XHRcdC8vIEdlbmVyYXRlIHRoZSBzbGlkZXIgd2l0aCBmZXRjaGVkIHByb2R1Y3RzXG5cdFx0XHRcdHZhciBzd2lwZXIgPSBuZXcgU3dpcGVyKCcucHJvZHVjdHNfX3N3aXBlcmNvbnRhaW5lci0tdmVkZXR0ZScsIHtcblx0XHRcdFx0ICAgIHBhZ2luYXRpb25DbGlja2FibGU6IHRydWUsXG5cdFx0XHRcdCAgICBzcGFjZUJldHdlZW46IDIwLFxuXHRcdFx0XHQgICAgc2xpZGVzUGVyVmlldzogNCxcblx0XHRcdFx0ICAgIHNsaWRlc1Blckdyb3VwOiA0LFxuXHRcdFx0XHQgICAgc3BlZWQ6IDEwMDAsXG5cdFx0XHRcdCAgICBsb29wOiBmYWxzZSxcblx0XHRcdFx0ICAgIG5hdmlnYXRpb246IHtcblx0XHRcdFx0ICAgICAgICBuZXh0RWw6ICcucHJvZHVjdHNfX2J1dHRvbi0tLW5leHQxJyxcblx0XHRcdFx0ICAgICAgICBwcmV2RWw6ICcucHJvZHVjdHNfX2J1dHRvbi0tLXByZXYxJyxcblx0XHRcdFx0ICAgIH0sXG5cdFx0XHRcdCAgICBicmVha3BvaW50czoge1xuXHRcdFx0XHQgICAgICAgIDExMDA6IHtcblx0XHRcdFx0ICAgICAgICBcdHNsaWRlc1BlclZpZXc6IDMsXG5cdFx0XHRcdFx0XHRcdHNwYWNlQmV0d2VlbjogNDAsXG5cdFx0XHRcdCAgICAgICAgfSxcblx0XHRcdFx0ICAgICAgICA3Njg6IHtcblx0XHRcdFx0ICAgICAgICBcdHNsaWRlc1BlclZpZXc6IDIsXG5cdFx0XHRcdFx0XHRcdHNwYWNlQmV0d2VlbjogNDAsXG5cdFx0XHRcdCAgICAgICAgfSxcblx0XHRcdFx0ICAgICAgICA2NDA6IHtcblx0XHRcdFx0XHQgICAgICAgIHNsaWRlc1BlclZpZXc6IDEsXG5cdFx0XHRcdFx0XHRcdHNwYWNlQmV0d2VlbjogNDAsXG5cdFx0XHRcdCAgICAgICAgfVxuXHRcdFx0XHQgICAgfVxuXHRcdFx0XHR9KTtcblxuXHRcdFx0XHQvLyBtaW5pY2FydC5tYW5hZ2VBZGRUb0NhcnRCdG4oKTtcblx0XHRcdFx0cHJvZHVjdGdyaWQudGh1bWJSZXNpemUoKTtcblxuXHQgICAgICAgIH1cblx0ICAgIH0pO1xuXG4gICAgfVxuXG59KShqUXVlcnkpOyIsIi8qKlxuICogTUVESUEgUVVFUklFU1xuICogXG4gKiBUcmlnZ2VyIGN1c3RvbSBldmVudHMgYXR0YWNoZWQgdG8gJCh3aW5kb3cpIG9uIGV2ZXJ5IGJyZWFrcG9pbnQgY2hhbmdlLlxuICogTWVkaWEgcXVlcmllcyBhcmUgZmV0Y2hlZCBmcm9tIGJvZHk6YmVmb3JlIENTUyBwcm9wZXJ0eSwgYW5kIGRvbid0IG5lZWQgXG4gKiB0byBiZSByZWRlY2xhcmVkIHRvIHdvcmsgaW4gSlMuXG4gKiBcbiAqIEV4YW1wbGVzIG9uIGdpdGh1YiA6IGh0dHBzOi8vZ2l0aHViLmNvbS9tYXRoaWV1YmVhdXJlZ2FyZC9qYXZhc2NyaXB0L3RyZWUvbWFzdGVyL21lZGlhLXF1ZXJpZXNcbiAqXG4gKiBAcmV0dXJuIHtvYmplY3R9IFB1YmxpYyBtZXRob2RzIDogc2V0Q29uZmlnICYgZ2V0Q3VycmVudEJyZWFrcG9pbnRcbiAqIEByZXF1aXJlcyBqUXVlcnkgaHR0cDovL2pxdWVyeS5jb20vXG4gKi9cbnZhciBidiA9IGJ2IHx8IHt9O1xuXG5idi5tcSA9IChmdW5jdGlvbigkKSB7XG5cdFx0Ly8ge09iamVjdH0gTW9kdWxlIGNvbmZpZ3VyYXRpb24gKGVkaXRhYmxlIHZpYSBzZXRDb25maWcgbWV0aG9kKVxuXHR2YXIgY29uZmlnID0ge1xuXHRcdC8vIHtCb29sZWFufSBBZGQgY2xhc3Mgd2l0aCBicmVha3BvaW50IG5hbWUgdG8gYm9keSA/XG5cdFx0XHRtYW5hZ2VCb2R5Q2xhc3NlcyA6IGZhbHNlLFxuXHRcdC8vIHtCb29sZWFufSBMb2dzIGV2ZW50cyB0aGF0IGdldCB0cmlnZ2VyZWRcblx0XHRcdGRlYnVnIDogZmFsc2UsXG5cdFx0fSxcblx0XHQvLyB7Qm9vbGVhbn0gRG9lcyBicm93c2VyIHN1cHBvcnRzIHBzZXVkbyA6YmVmb3JlID9cblx0XHRicm93c2VySXNTdXBwb3J0ZWQgPSB3aW5kb3cuZ2V0Q29tcHV0ZWRTdHlsZSAmJiB3aW5kb3cuZ2V0Q29tcHV0ZWRTdHlsZShkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCdib2R5JyksICc6YmVmb3JlJykuZ2V0UHJvcGVydHlWYWx1ZSgnY29udGVudCcpLFxuXHRcdC8vIHtPYmplY3R9IGpRdWVyeSBjYWNoZVxuXHRcdCRiZyA9ICQoJ1tkYXRhLWJnLXhsXSwgW2RhdGEtYmcteGwtMXhdLCBbZGF0YS1iZy1sZ10sIFtkYXRhLWJnLWxnLTF4XSwgW2RhdGEtYmctbWRdLCBbZGF0YS1iZy1tZC0xeF0sIFtkYXRhLWJnLXNtXSwgW2RhdGEtYmctc20tMXhdLCBbZGF0YS1iZy14c10sIFtkYXRhLWJnLXhzLTF4XScpLFxuXHRcdCR3aW5kb3csXG5cdFx0JGJvZHksXG5cdFx0Ly8ge1N0cmluZ30gQ3VycmVudCBicmVha3BvaW50IG5hbWUsIGFzIGRlY2xhcmVkIGluIENTU1xuXHRcdGN1cnJlbnRCcmVha3BvaW50O1xuXG5cdC8qKlxuXHQgKiBHRVQgQ1VSUkVOVCBCUkVBS1BPSU5UXG5cdCAqIEZldGNoIGN1cnJlbnQgYnJlYWtwb2ludCB2YWx1ZSBmcm9tIGJvZHk6YmVmb3JlLlxuXHQgKlxuXHQgKiBAcHVibGljXG5cdCAqIEByZXR1cm4ge1N0cmluZ30gQnJlYWtwb2ludCBuYW1lLCBhcyBkZWNsYXJlZCBpbiBDU1MuXG5cdCAqL1xuXHRmdW5jdGlvbiBnZXRDdXJyZW50QnJlYWtwb2ludCgpIHtcblx0XHR2YXIgYnJlYWtwb2ludCA9IGJyb3dzZXJJc1N1cHBvcnRlZCA/IHdpbmRvdy5nZXRDb21wdXRlZFN0eWxlKGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJ2JvZHknKSwgJzpiZWZvcmUnKS5nZXRQcm9wZXJ0eVZhbHVlKCdjb250ZW50JykucmVwbGFjZSgvXFxcIi9nLCAnJykgOiAndW5zdXBwb3J0ZWRCcm93c2VyJztcblxuXHRcdC8vIFNhbml0aXplIGJyZWFrcG9pbnQgbmFtZVxuXHRcdGJyZWFrcG9pbnQgPSBicmVha3BvaW50LnJlcGxhY2UoJ1xcJycsICcnKTtcblx0XHRicmVha3BvaW50ID0gYnJlYWtwb2ludC5yZXBsYWNlKCdcXCcnLCAnJyk7XG5cdFx0YnJlYWtwb2ludCA9IGJyZWFrcG9pbnQucmVwbGFjZSgnXCInLCAnJyk7XG5cdFx0YnJlYWtwb2ludCA9IGJyZWFrcG9pbnQucmVwbGFjZSgnXCInLCAnJyk7XG5cblx0XHRyZXR1cm4gYnJlYWtwb2ludDtcblx0fVxuXG5cdC8qKlxuXHQgKiBVUERBVEUgQlJFQUtQT0lOVFMgU1RBVEVcblx0ICogQ2hlY2tzIGlmIGJyZWFrcG9pbnQgY2hhbmdlZCBjaGFuZ2VkIHNpbmNlIGxhc3QgY2hlY2ssXG5cdCAqIHRyaWdnZXIgY3VzdG9tIGV2ZW50cyBpZiBuZWVkZWRcblx0ICpcblx0ICogQHByaXZhdGVcblx0ICogQHBhcmFtIHtCb29sZWFufSBmb3JjZVVwZGF0ZSBGb3JjZSBldmVudHMgdG8gYmUgdHJpZ2dlcmVkIGlmIHRydWVcblx0ICovXG5cdGZ1bmN0aW9uIHVwZGF0ZUJyZWFrcG9pbnRzU3RhdGUoZm9yY2VVcGRhdGUpIHtcblx0XHR2YXIgbGFzdEJyZWFrcG9pbnQgPSBjdXJyZW50QnJlYWtwb2ludCxcblx0XHRcdGRlYnVnTXNnID0gJ1RyaWdnZXJlZCB3aW5kb3cgZXZlbnRzIDogJztcblxuXHRcdGlmIChicm93c2VySXNTdXBwb3J0ZWQpIHtcblx0XHRcdGN1cnJlbnRCcmVha3BvaW50ID0gZ2V0Q3VycmVudEJyZWFrcG9pbnQoKTtcblx0XHR9XG5cblx0XHQvLyBCcmVha3BvaW50IGhhcyBjaGFuZ2VkLCBvciBmb3JjZWQgdG8gdXBkYXRlXG5cdFx0aWYgKGN1cnJlbnRCcmVha3BvaW50ICE9IGxhc3RCcmVha3BvaW50IHx8IGZvcmNlVXBkYXRlID09PSB0cnVlKSB7XG5cblx0XHRcdGlmIChjdXJyZW50QnJlYWtwb2ludCAhPSBsYXN0QnJlYWtwb2ludCkge1xuXHRcdFx0XHQkd2luZG93LnRyaWdnZXIoJ2JwRXhpdF8nICsgbGFzdEJyZWFrcG9pbnQpO1xuXG5cdFx0XHRcdGlmIChjb25maWcubWFuYWdlQm9keUNsYXNzZXMpIHtcblx0XHRcdFx0XHQkYm9keS5yZW1vdmVDbGFzcygnYnBfJyArIGxhc3RCcmVha3BvaW50KTtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdGlmIChjb25maWcuZGVidWcpIHtcblx0XHRcdFx0XHRkZWJ1Z01zZyArPSAnXCJicEV4aXRfJyArIGxhc3RCcmVha3BvaW50ICsgJ1wiLCAnO1xuXHRcdFx0XHR9XG5cblx0XHRcdH1cblxuXHRcdFx0JHdpbmRvd1xuXHRcdFx0XHQudHJpZ2dlcignYnBFbnRlcl8nICsgY3VycmVudEJyZWFrcG9pbnQpXG5cdFx0XHRcdC50cmlnZ2VyKCdicENoYW5nZScsIFtjdXJyZW50QnJlYWtwb2ludF0pO1xuXG5cdFx0XHR1cGRhdGVCYWNrZ3JvdW5kcygpO1xuXG5cdFx0XHRpZiAoY29uZmlnLm1hbmFnZUJvZHlDbGFzc2VzKSB7XG5cdFx0XHRcdCRib2R5LmFkZENsYXNzKCdicF8nICsgY3VycmVudEJyZWFrcG9pbnQpO1xuXHRcdFx0fVxuXG5cdFx0XHRpZiAoY29uZmlnLmRlYnVnKSB7XG5cdFx0XHRcdGRlYnVnTXNnICs9ICdcImJwRW50ZXJfJyArIGN1cnJlbnRCcmVha3BvaW50ICsgJ1wiLCBcImJwQ2hhbmdlXCIuJztcblx0XHRcdFx0Y29uc29sZS5sb2coZGVidWdNc2cpO1xuXHRcdFx0fVxuXHRcdH1cblx0fVxuXG5cdC8qKlxuXHQgKiBVUERBVEUgQkFDS0dST1VORFNcblx0ICogXG5cdCAqIFVwZGF0ZSBiYWNrZ3JvdW5kIGltYWdlIGZvciBlbGVtZW50cyB3aGljaCBoYXZlIGFcblx0ICogZGF0YS1iZy1jdXJyZW50QnJlYWtwb2ludE5hbWUgYXR0cmlidXRlIHNwZWNpZmllZC5cblx0ICpcblx0ICogSWYgcGl4ZWwgcmF0aW8gaXMgc3BlY2lmaWVkLCBpdCB3aWxsIGJlIHVzZWRcblx0ICpcblx0ICogRXhhbXBsZXNcblx0ICogXHRcdDxkaXYgZGF0YS1iZy1sZz1cImltYWdlLWxnLmpwZ1wiIGRhdGEtYmctbWQ9XCJpbWFnZS1tZC5qcGdcIj48L2Rpdj5cblx0ICogXHRcdDxkaXYgZGF0YS1iZy1sZy0yeD1cImltYWdlLWxnQDJ4LmpwZ1wiPjwvZGl2PlxuXHQgKiBcdFx0Li4uXG5cdCAqXG5cdCAqIEByZXR1cm4ge3VuZGVmaW5lZH1cblx0ICovXG5cdGZ1bmN0aW9uIHVwZGF0ZUJhY2tncm91bmRzKCkge1xuXHRcdC8vIHZhciBkZW5zaXR5ID0gd2luZG93LmRldmljZVBpeGVsUmF0aW8gfHwgMTtcblx0XHR2YXIgZGVuc2l0eSA9IDE7XG5cblx0XHQkYmcuZWFjaChmdW5jdGlvbigpIHtcblx0XHRcdHZhciBiZyA9ICcnO1xuXG5cdFx0XHRpZiAoJCh0aGlzKS5hdHRyKCdkYXRhLWJnLScgKyBjdXJyZW50QnJlYWtwb2ludCArICctJyArIGRlbnNpdHkgKyAneCcpKSB7XG5cdFx0XHRcdGJnID0gJCh0aGlzKS5hdHRyKCdkYXRhLWJnLScgKyBjdXJyZW50QnJlYWtwb2ludCArICctJyArIGRlbnNpdHkgKyAneCcpO1xuXHRcdFx0fSBlbHNlIGlmICgkKHRoaXMpLmF0dHIoJ2RhdGEtYmctJyArIGN1cnJlbnRCcmVha3BvaW50KSkge1xuXHRcdFx0XHRiZyA9ICQodGhpcykuYXR0cignZGF0YS1iZy0nICsgY3VycmVudEJyZWFrcG9pbnQpO1xuXHRcdFx0fVxuXG5cdFx0XHQkKHRoaXMpLmNzcyh7XG5cdFx0XHRcdCdiYWNrZ3JvdW5kLWltYWdlJyA6IGJnXG5cdFx0XHR9KTtcblxuXHRcdH0pO1xuXHR9XG5cblx0LyoqXG5cdCAqIFRSSUdHRVIgQlJFQUtQT0lOVCBFVkVOVFNcblx0ICogVHJpZ2dlciBjdXN0b20gZXZlbnRzIGF0dGFjaGVkIHRvIGN1cnJlbnQgYnJlYWtwb2ludC5cblx0ICpcblx0ICogQHB1YmxpY1xuXHQgKi9cblx0ZnVuY3Rpb24gdHJpZ2dlckJyZWFrcG9pbnRFdmVudHMoKSB7XG5cdFx0dXBkYXRlQnJlYWtwb2ludHNTdGF0ZSh0cnVlKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBTRVQgQ09ORklHXG5cdCAqIFVwZGF0ZSBtb2R1bGUncyBjb25maWd1cmF0aW9uIGZyb20gcHJvdmlkZWQgdmFsdWVzXG5cdCAqIFxuXHQgKiBAcHVibGljXG5cdCAqIEBwYXJhbSB7T2JqZWN0fSBhcmdzIFByb3BlcnRpZXMgb2YgY29uZmlnIG9iamVjdCB0byB1cGRhdGVcblx0ICogQHBlb3BlcnR5IHtCb29sZWFufSBhcmdzLm1hbmFnZUJvZHlDbGFzc2VzIEFkZCBjbGFzcyB3aXRoIGJyZWFrcG9pbnQgbmFtZSB0byBib2R5ID9cblx0ICogQHBlb3BlcnR5IHtCb29sZWFufSBhcmdzLmRlYnVnIExvZyBldmVudHMgdGhhdCBnZXQgdHJpZ2dlcmVkID9cblx0ICovXG5cdGZ1bmN0aW9uIHNldENvbmZpZyhhcmdzKSB7XG5cdFx0Y29uZmlnLm1hbmFnZUJvZHlDbGFzc2VzID0gdHlwZW9mIGFyZ3MubWFuYWdlQm9keUNsYXNzZXMgPT0gJ2Jvb2xlYW4nID8gYXJncy5tYW5hZ2VCb2R5Q2xhc3NlcyA6IGNvbmZpZy5tYW5hZ2VCb2R5Q2xhc3Nlcztcblx0XHRjb25maWcuZGVidWcgPSB0eXBlb2YgYXJncy5kZWJ1ZyA9PSAnYm9vbGVhbicgPyBhcmdzLmRlYnVnIDogY29uZmlnLmRlYnVnO1xuXG5cdFx0Ly8gQWRkIG9yIHJlc2V0IGJvZHkgY2xhc3NlcyA/XG5cdFx0aWYgKHR5cGVvZiBhcmdzLm1hbmFnZUJvZHlDbGFzc2VzID09ICdib29sZWFuJyAmJiBhcmdzLm1hbmFnZUJvZHlDbGFzc2VzID09PSB0cnVlKSB7XG5cdFx0XHQkYm9keS5hZGRDbGFzcygnYnBfJyArIGN1cnJlbnRCcmVha3BvaW50KTtcblx0XHR9IGVsc2UgaWYgKHR5cGVvZiBhcmdzLm1hbmFnZUJvZHlDbGFzc2VzID09ICdib29sZWFuJyAmJiBhcmdzLm1hbmFnZUJvZHlDbGFzc2VzID09PSBmYWxzZSkge1xuXHRcdFx0JGJvZHkucmVtb3ZlQ2xhc3MoJ2JwXycgKyBjdXJyZW50QnJlYWtwb2ludCk7XG5cdFx0fVxuXHR9XG5cblx0LyoqXG5cdCAqIElOSVRcblx0ICogSW5pdGlhbGlzZXMgbW9kdWxlLlxuXHQgKiBTZWxmLWludm9raW5nLlxuXHQgKlxuXHQgKiBAcHJpdmF0ZVxuXHQgKi9cblx0KGZ1bmN0aW9uIGluaXQoKSB7XG5cdFx0Ly8gU2V0IG1vZHVsZSBnbG9iYWxzXG5cdFx0JHdpbmRvdyA9ICR3aW5kb3cgPyAkd2luZG93IDogJCh3aW5kb3cpO1xuXHRcdCRib2R5ID0gJGJvZHkgPyAkYm9keSA6ICQoJ2JvZHknKTtcblx0XHRcblx0XHRjdXJyZW50QnJlYWtwb2ludCA9IGdldEN1cnJlbnRCcmVha3BvaW50KCk7XG5cblx0XHQvLyBTZXQgd2luZG93IHJlc2l6ZSBsaXN0ZW5lciBhbmQgdHJpZ2dlciBvbmNlIHRvIGNhbGwgYXR0YWNoZWQgZXZlbnQgaGFuZGxlcnMgKG9uIGxvYWQpXG5cdFx0aWYgKGJyb3dzZXJJc1N1cHBvcnRlZCkge1xuXHRcdFx0JHdpbmRvd1xuXHRcdFx0XHQub24oJ3Jlc2l6ZS5idl9tcScsIHVwZGF0ZUJyZWFrcG9pbnRzU3RhdGUpXG5cdFx0XHRcdC5vbignbG9hZCcsIGZ1bmN0aW9uKCkge1xuXHRcdFx0XHRcdHVwZGF0ZUJyZWFrcG9pbnRzU3RhdGUodHJ1ZSk7XG5cdFx0XHRcdH0pO1xuXG5cdFx0Ly8gVW5zdXBwb3J0ZWQgYnJvd3NlclxuXHRcdH0gZWxzZSB7XG5cdFx0XHRjdXJyZW50QnJlYWtwb2ludCA9IGNvbmZpZy51bnN1cHBvcnRlZEJyb3dzZXJEZWZhdWx0ID8gY29uZmlnLnVuc3VwcG9ydGVkQnJvd3NlckRlZmF1bHQgOiBjdXJyZW50QnJlYWtwb2ludDtcblx0XHR9XG5cdH0pKCk7XG5cblx0Ly8gUmV0dXJuIHB1YmxpYyBtZXRob2RzXG5cdHJldHVybiB7XG5cdFx0c2V0Q29uZmlnIDogc2V0Q29uZmlnLFxuXHRcdGdldEN1cnJlbnRCcmVha3BvaW50IDogZ2V0Q3VycmVudEJyZWFrcG9pbnQsXG5cdFx0dHJpZ2dlckJyZWFrcG9pbnRFdmVudHMgOiB0cmlnZ2VyQnJlYWtwb2ludEV2ZW50c1xuXHR9O1xufSkoalF1ZXJ5KTtcbiIsIi8qXG5cdENBUlQgUEFHRVxuXHQtLS0tLS1cblxuICovXG47KGZ1bmN0aW9uIGNhcnRQYWdlKCQpIHtcblx0Ly8gRm9yY2UgcGFnZSByZWxvYWQgdG8gZm9yY2UgbWluaS1jYXJ0IHRpIGJlIHJlZnJlc2hlZFxuXHQvLyBZZXMsIHRoZSBtZXRob2QgaXMgZGlzY3V0YWJsZS5cblx0JCgnLnByb2R1Y3QtcmVtb3ZlIC5yZW1vdmUnKS5vbignY2xpY2snLCBmdW5jdGlvbigpIHtcblx0XHQvLyBXYWl0IGZvciBXb29jb21tZXJjZSBBamF4IHJlcXVlc3QgdG8gYmUgc2VudFxuXHRcdHNldFRpbWVvdXQoZnVuY3Rpb24oKSB7XG5cdFx0XHR3aW5kb3cubG9jYXRpb24gPSB3aW5kb3cubG9jYXRpb247XG5cdFx0fSwgNTAwKTtcblxuXHR9KTtcblxuXHQvLyBSZW1vdmUgdGhlIGFtb3VudHMgaW4gdGhlIFNoaXBwaW5nIHBhY2thZ2UgZGVzY3JpcHRpb25cblx0JCgnLndvb2NvbW1lcmNlLXNoaXBwaW5nLWNvbnRlbnRzIHNtYWxsJykuZWFjaChmdW5jdGlvbigpe1xuXHRcdCQodGhpcykuaHRtbCgkKHRoaXMpLmh0bWwoKS5zcGxpdChcIiDDlzFcIikuam9pbihcIlwiKSk7XG5cdH0pO1xuXG5cdC8vIFNoaXBwaW5nXG5cdCQoJyNzaGlwLXRvLWRpZmZlcmVudC1hZGRyZXNzJykuY2xpY2soIGZ1bmN0aW9uKCkge1xuXHRcdCQoJyNzaGlwLXRvLWRpZmZlcmVudC1hZGRyZXNzLWNoZWNrYm94JykudHJpZ2dlcignY2xpY2snKTtcblx0fSk7XG5cblx0Ly8gQ3LDqWF0aW9uIGRlIGNvbXB0ZVxuXHQkKCcud29vY29tbWVyY2UtYWNjb3VudC1maWVsZHMgbGFiZWwnKS5jbGljayggZnVuY3Rpb24oKSB7XG5cdFx0JCgnI2NyZWF0ZWFjY291bnQnKS50cmlnZ2VyKCdjbGljaycpO1xuXHR9KTtcblxufSkoalF1ZXJ5KTsiLCIvKlxuXHRDQVRFR09SSUVTIE1FTlVcblx0LS0tLS0tXG5cblx0U2lkZWJhciBjYXRlZ29yeSBtZW51ICgvcHJvZHVpdHMpLlxuICovXG47KGZ1bmN0aW9uIHNpZGViYXJDYXRlZ29yaWVzKCQpIHtcblxuXHQkKCcuY2F0LXBhcmVudCAuY2hpbGRyZW4nKS5oaWRlKCk7XG5cdCQoJy5jYXQtcGFyZW50JykuYXBwZW5kKCc8YSBjbGFzcz1cImpzLWNhdFRvZ2dsZVwiPjwvYT4nKTtcblxuXHQvLyBPcGVuIHBhcmVudHMgdG9nZ2xlcyBvbiBzdWItY2F0ZWdvcnkgcGFnZVxuXHQkKCcuY2F0LXBhcmVudCcpLmVhY2goZnVuY3Rpb24oKSB7XG5cdFx0aWYoICQodGhpcykuaGFzQ2xhc3MoJ2N1cnJlbnQtY2F0LXBhcmVudCcpICl7XG5cdFx0XHQkKHRoaXMpLmNoaWxkcmVuKCcuY2hpbGRyZW4nKS5zaG93KCk7XG5cdFx0XHQkKHRoaXMpLmNoaWxkcmVuKCcuanMtY2F0VG9nZ2xlJykuYWRkQ2xhc3MoJ29wZW4nKTtcblx0XHR9XG5cdH0pO1xuXG5cdC8vIFRvZ2dsZVxuXHQkKCcuanMtY2F0VG9nZ2xlJykuY2xpY2soZnVuY3Rpb24oKSB7XG5cdFx0JCh0aGlzKS50b2dnbGVDbGFzcygnb3BlbicpO1xuXHRcdCQodGhpcykuc2libGluZ3MoJy5jaGlsZHJlbicpLnNsaWRlVG9nZ2xlKCk7XG5cdH0pO1xuXG59KShqUXVlcnkpOyIsIiIsIi8qXG5cdEhFQURFUlxuXHQtLS0tLS1cblxuXHRTZXQgaGVhZGVyIGNsYXNzZXMgb24gc2Nyb2xsLlxuXHRBdmFpbGFibGUgY2xhc3NlcyA6XG5cdFx0XCJuYXYtZG93blwiIDogVXNlciBpcyBzY3JvbGxpbmcgdXAsIHRoZSBuYXYgaXMgZG93blxuXHRcdFwibmF2LXVwXCIgOiBVc2VyIGlzIHNjcm9sbGluZyBkb3duLCB0aGUgbmF2IGlzIHVwXG4gKi9cbjsoZnVuY3Rpb24gaGVhZGVyKCQpIHtcblxuXHQvKipcblx0ICogU0NST0xMXG5cdCAqXG5cdCovXG5cdHZhciBkaWRTY3JvbGw7XG5cdHZhciBsYXN0U2Nyb2xsVG9wID0gMDtcblx0dmFyIGRlbHRhID0gNTtcblx0dmFyIG5hdmJhckhlaWdodCA9IDA7XG5cdHZhciB3aW4gPSAkKHdpbmRvdykuaGVpZ2h0KCk7XG5cblx0JCh3aW5kb3cpLnNjcm9sbChmdW5jdGlvbihldmVudCl7XG5cdCAgICBkaWRTY3JvbGwgPSB0cnVlO1xuXHR9KTtcblxuXHRzZXRJbnRlcnZhbChmdW5jdGlvbigpIHtcblx0ICAgIGlmIChkaWRTY3JvbGwpIHtcblx0ICAgICAgICBoYXNTY3JvbGxlZCgpO1xuXHQgICAgICAgIGRpZFNjcm9sbCA9IGZhbHNlO1xuXHQgICAgfVxuXHR9LCAyNTApO1xuXG5cdGZ1bmN0aW9uIGhhc1Njcm9sbGVkKCkge1xuXHQgICAgdmFyIHN0ID0gJCh0aGlzKS5zY3JvbGxUb3AoKTtcblxuXHQgICAgaWYoTWF0aC5hYnMobGFzdFNjcm9sbFRvcCAtIHN0KSA8PSBkZWx0YSlcblx0ICAgICAgICByZXR1cm47XG5cblx0ICAgIC8vIElmIHRoZXkgc2Nyb2xsZWQgZG93biBhbmQgYXJlIHBhc3QgdGhlIG5hdmJhciwgYWRkIGNsYXNzIC5uYXYtdXAuXG5cdCAgICBpZigkKHdpbmRvdykud2lkdGgoKSA+PSA5ODApe1xuXHRcdCAgICBpZiAoc3QgPiBsYXN0U2Nyb2xsVG9wICYmIHN0ID4gbmF2YmFySGVpZ2h0KXtcblx0XHQgICAgICAgIC8vIFNjcm9sbCBEb3duXG5cdFx0ICAgICAgICAkKCcjaGVhZGVyJykucmVtb3ZlQ2xhc3MoJ25hdi1kb3duJykuYWRkQ2xhc3MoJ25hdi11cCcpO1xuXHRcdCAgICAgICAgJCgnI21lbnUtbWFpbi1uYXYnKS5jc3MoJ3BhZGRpbmctbGVmdCcsICc1LjMxM2VtJyk7XG5cdFx0ICAgICAgICAkKCcubG9nby1zbWFsbCcpLmZhZGVJbignZmFzdCcpO1xuXHRcdCAgICB9IGVsc2Uge1xuXHRcdCAgICAgICAgLy8gU2Nyb2xsIFVwXG5cdFx0ICAgICAgICBpZihzdCArICQod2luZG93KS5oZWlnaHQoKSA8ICQoZG9jdW1lbnQpLmhlaWdodCgpKSB7XG5cdFx0ICAgICAgICAgICAgJCgnI2hlYWRlcicpLnJlbW92ZUNsYXNzKCduYXYtdXAnKS5hZGRDbGFzcygnbmF2LWRvd24nKTtcblx0XHQgICAgICAgICAgICAkKCcubG9nby1zbWFsbCcpLmZhZGVPdXQoJ2Zhc3QnKTtcblx0XHQgICAgICAgICAgICAkKCcjbWVudS1tYWluLW5hdicpLmNzcygncGFkZGluZy1sZWZ0JywgJzAnKTtcblx0XHQgICAgICAgIH1cblx0XHQgICAgfVxuXHRcdCAgICBsYXN0U2Nyb2xsVG9wID0gc3Q7XG5cdCAgICB9XG5cdH1cblxuXHQkKCcjbWVudS1tYWluLW5hdicpLnByZXBlbmQoJzxhIGNsYXNzPVwibG9nby1zbWFsbFwiIGhyZWY9XCIvXCIgc3R5bGU9XCJkaXNwbGF5Om5vbmU7XCI+PC9hPicpO1xuXG59KShqUXVlcnkpOyIsIi8qXG5cdE1hcFxuXHQtLS0tLS1cblxuXHRHb29nbGUgTWFwcyBIYWNrcyBhbmQgdHJpY2t5IHRyaWNrc1xuXG4gKi9cbjsoZnVuY3Rpb24gbWFwSGFja3MoJCkge1xuXG5cdC8vICQoJyNtYXBfX2NhbnZhcycpLmNsaWNrKGZ1bmN0aW9uKCl7XG5cdC8vIFx0JCgnLmdtLXN0eWxlLWl3JykuaGlkZSgpO1xuXHQvLyB9KTtcblxufSkoalF1ZXJ5KTsiLCIvKlxuXHRNT0JJTEUgTUVOVVxuXHQtLS0tLS1cblxuXHRNb2JpbGUgTWVudSBmdW5jdGlvblxuXG4gKi9cbjsoZnVuY3Rpb24gbW9iaWxlTWVudSgkKSB7XG5cblx0JChcIi5tYWluLW5hdl9fYnRuXCIpLm9uKCdjbGljaycsIGZ1bmN0aW9uKCl7XG5cdFx0JCgnLmpzLW1haW4tbmF2LXdyYXAnKS50b2dnbGVDbGFzcygnb3BlbicpO1xuXHRcdCQodGhpcykudG9nZ2xlQ2xhc3MoJ29wZW5lZCcpO1xuXHR9KTtcblxuXHQkKFwiLm1haW4tbmF2XCIpLmZpbmQoXCIubWVudS1pdGVtLWhhcy1jaGlsZHJlblwiKS5wcmVwZW5kKCc8c3BhbiBjbGFzcz1cInN1Ym1lbnUtYnV0dG9uXCI+PC9zcGFuPicpO1xuXHQkKFwiLm1haW4tbmF2XCIpLmZpbmQoJy5zdWJtZW51LWJ1dHRvbicpLm9uKCdjbGljaycsIGZ1bmN0aW9uKCkge1xuICAgICAgICAkKHRoaXMpLnRvZ2dsZUNsYXNzKCdvcGVuZWQnKTtcbiAgICAgICAgaWYgKCQodGhpcykuc2libGluZ3MoJ3VsJykuaGFzQ2xhc3MoJ29wZW4nKSkge1xuICAgICAgICBcdCQodGhpcykuc2libGluZ3MoJ3VsJykucmVtb3ZlQ2xhc3MoJ29wZW4nKS5zbGlkZVVwKFwic2xvd1wiKTtcbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgXHQkKHRoaXMpLnNpYmxpbmdzKCd1bCcpLmFkZENsYXNzKCdvcGVuJykuc2xpZGVEb3duKFwic2xvd1wiKTtcbiAgICAgICAgfVxuICAgIH0pO1xuXG5cdC8vIENsb3NlIG1vYmlsZSBtZW51IGFmdGVyIGNsaWNraW5nIG9uIGEgc3VibWVudSBhbmNob3JcbiAgICAkKCcjbWVudS1tZW51LXByaW5jaXBhbCBsaSB1bCBhJykuY2xpY2soZnVuY3Rpb24oKXtcblxuXHRcdGlmKCQod2luZG93KS53aWR0aCgpIDw9IDk5MSl7XG5cdFx0XHQkKCcjbWVudS1idXR0b24nKS50cmlnZ2VyKFwiY2xpY2tcIik7XG5cdFx0fVxuXG5cdH0pO1xuXG59KShqUXVlcnkpOyIsIi8qXG5cdFBBUkFMTEFYXG5cdC0tLS0tLVxuXG5cdEFkZGluZyBhIHN1YnRpbGUgcGFyYWxsYXggZWZmZWN0IHRvIGJhY2tncm91bmQgaW1hZ2VzXG5cbiAqL1xuOyhmdW5jdGlvbiBwYXJhbGxheEJnKCQpIHtcblxuXHQkKHdpbmRvdykuc2Nyb2xsKGZ1bmN0aW9uKCkge1xuXHRcdHZhciBzY3JvbGxUb3AgPSAkKHdpbmRvdykuc2Nyb2xsVG9wKCk7XG5cdFx0dmFyIGRpdmFtID0gLTEwO1xuXHRcdCQoXCIuanMtcGFyYWxsYXhcIikuY3NzKHtcblx0XHRcdFwiYmFja2dyb3VuZC1wb3NpdGlvblwiOlwiY2VudGVyIFwiK3Njcm9sbFRvcC9kaXZhbStcInB4XCJcblx0XHR9KTtcbiAgICB9KTtcblxuXG59KShqUXVlcnkpOyIsIi8qXG5cdFNMSURFUiBQUk9EVUNUIFBBR0Vcblx0LS0tLS0tXG5cbiAqL1xuOyhmdW5jdGlvbiBzbGlkZXJQcm9kdWN0KCQpIHtcblxuXHRpZiAoJCgnYm9keScpLmhhc0NsYXNzKCdzaW5nbGUtcHJvZHVjdCcpKSB7XG5cblx0XHRzZXRUaW1lb3V0KCBmdW5jdGlvbigpIHtcblxuXHRcdFx0JCgnLmZsZXgtY29udHJvbC1uYXYnKS53cmFwSW5uZXIoJzxkaXYgY2xhc3M9XCJzd2lwZXItd3JhcHBlclwiPjwvZGl2PicpO1xuXHRcdFx0JCgnLmZsZXgtY29udHJvbC1uYXYgbGknKS53cmFwKCc8ZGl2IGNsYXNzPVwic3dpcGVyLXNsaWRlXCI+PC9kaXY+Jyk7XG5cdFx0XHQkKCcuZmxleC1jb250cm9sLW5hdicpLmZhZGVJbigpO1xuXHRcdFx0JCgnLmZsZXgtY29udHJvbC1uYXYnKS5hcHBlbmQoJzxkaXYgY2xhc3M9XCJwcm9kdWN0c19fYnV0dG9uLS0tbmV4dDNcIj48L2Rpdj48ZGl2IGNsYXNzPVwicHJvZHVjdHNfX2J1dHRvbi0tLXByZXYzXCI+PC9kaXY+Jyk7XG5cblx0XHQgICAgdmFyIHN3aXBlMyA9IG5ldyBTd2lwZXIoJy5mbGV4LWNvbnRyb2wtbmF2Jywge1xuXHRcdFx0ICAgIHBhZ2luYXRpb25DbGlja2FibGU6IHRydWUsXG5cdFx0XHQgICAgc3BhY2VCZXR3ZWVuOiAyMCxcblx0XHRcdCAgICBzbGlkZXNQZXJWaWV3OiA0LFxuXHRcdFx0ICAgIGxvb3A6IGZhbHNlLFxuXHRcdFx0ICAgIG5hdmlnYXRpb246IHtcblx0XHRcdCAgICAgICAgbmV4dEVsOiAnLnByb2R1Y3RzX19idXR0b24tLS1uZXh0MycsXG5cdFx0XHQgICAgICAgIHByZXZFbDogJy5wcm9kdWN0c19fYnV0dG9uLS0tcHJldjMnLFxuXHRcdFx0ICAgIH0sXG5cdFx0XHQgICAgYnJlYWtwb2ludHM6IHtcblx0XHRcdCAgICAgICAgMTEwMDoge1xuXHRcdFx0ICAgICAgICBcdHNsaWRlc1BlclZpZXc6IDMsXG5cdFx0XHRcdFx0XHRzcGFjZUJldHdlZW46IDQwLFxuXHRcdFx0ICAgICAgICB9LFxuXHRcdFx0ICAgICAgICA3Njg6IHtcblx0XHRcdCAgICAgICAgXHRzbGlkZXNQZXJWaWV3OiA1LFxuXHRcdFx0XHRcdFx0c3BhY2VCZXR3ZWVuOiAyMCxcblx0XHRcdCAgICAgICAgfSxcblx0XHRcdCAgICAgICAgNjQwOiB7XG5cdFx0XHRcdCAgICAgICAgc2xpZGVzUGVyVmlldzogNCxcblx0XHRcdFx0XHRcdHNwYWNlQmV0d2VlbjogNDAsXG5cdFx0XHQgICAgICAgIH1cblx0XHRcdCAgICB9XG5cdFx0XHR9KTtcblxuXHRcdH0sIDEwMDApO1xuXG5cdH1cblxufSkoalF1ZXJ5KTsiLCIvKlxuXHRTV0lQRVJcblx0LS0tLS0tXG5cblx0Q2FsbGluZyBTd2lwZXIgb24gdGhlIGhvbWUgcGFnZVxuXG4gKi9cbjsoZnVuY3Rpb24gaG9tZVN3aXBlcigkKSB7XG5cblx0Ly8gS2lja3N0YXJ0IHRoZSBwcm9ncmVzcyBiYXJcblx0alF1ZXJ5KCcucHJvZ3Jlc3NiYXJfX2ZpbGwnKS5jc3Moeyd3aWR0aCc6ICcxMDAlJywgJ3RyYW5zaXRpb24nIDogJzZzJ30pO1xuXG5cdC8vIEhvbWUgcGFnZVxuXHR2YXIgc3dpcGVyID0gbmV3IFN3aXBlcignLnN3aXBlci1jb250YWluZXInLCB7XG5cdCAgICBwYWdpbmF0aW9uQ2xpY2thYmxlOiB0cnVlLFxuXHQgICAgc3BhY2VCZXR3ZWVuOiAwLFxuXHQgICAgbG9vcDogdHJ1ZSxcblx0XHRhdXRvcGxheToge1xuXHQgICAgICAgIGRlbGF5OiA2MDAwLFxuXHQgICAgICAgIGRpc2FibGVPbkludGVyYWN0aW9uOiBmYWxzZSxcblx0ICAgIH0sXG5cdCAgICBuYXZpZ2F0aW9uOiB7XG5cdCAgICAgICAgbmV4dEVsOiAnLnN3aXBlci1idXR0b24tbmV4dCcsXG5cdCAgICAgICAgcHJldkVsOiAnLnN3aXBlci1idXR0b24tcHJldicsXG5cdCAgICB9LFxuXHR9KTtcblx0c3dpcGVyLm9uKCdzbGlkZUNoYW5nZVRyYW5zaXRpb25TdGFydCcsIGZ1bmN0aW9uICgpIHtcblx0XHRqUXVlcnkoJy5wcm9ncmVzc2Jhcl9fZmlsbCcpLmNzcyh7J3dpZHRoJzogJzAlJywgJ3RyYW5zaXRpb24nIDogJ25vbmUnfSk7XG5cdH0pO1xuXHRzd2lwZXIub24oJ3NsaWRlQ2hhbmdlVHJhbnNpdGlvbkVuZCcsIGZ1bmN0aW9uICgpIHtcblx0XHRqUXVlcnkoJy5wcm9ncmVzc2Jhcl9fZmlsbCcpLmNzcyh7J3dpZHRoJzogJzEwMCUnLCAndHJhbnNpdGlvbicgOiAnNnMnfSk7XG5cdH0pO1xuXG59KShqUXVlcnkpOyIsIi8qXG5cdFRPR0dMRVxuXHQtLS0tLS1cblxuXHRBam91dGVyIGxhIGNsYXNzZSBqcy10b2dnbGUgc3VyIGxlIHRpdHJlIGV0IHBsYWNlciBsZSBjb250ZW51IMOgIOKAnHRvZ2dsZXLigJ0gZGlyZWN0ZW1lbnQgYXByw6hzLlxuXG5cdE1hcmt1cCBFeGFtcGxlOlxuXHQ8aDIgY2xhc3M9XCJqcy10b2dnbGVcIj5DbGljayBtZSE8L2gyPlxuXHQ8ZGl2PlxuXHRcdENvbnRlbnQgdG8gdG9nZ2xlLlxuXHQ8L2Rpdj5cblxuICovXG47KGZ1bmN0aW9uIHRvZ2dsZSgkKSB7XG5cblx0JCgnLmpzLXRvZ2dsZScpLmNsaWNrKCBmdW5jdGlvbigpIHtcblx0XHQkKHRoaXMpLm5leHQoKS5zbGlkZVRvZ2dsZSgpO1xuXHRcdCQodGhpcykudG9nZ2xlQ2xhc3MoJ2Nsb3NlZCcpO1xuXHR9KTtcblxuXHQkKCcud2lkZ2V0LXRpdGxlJykuY2xpY2soIGZ1bmN0aW9uKCkge1xuXHRcdCQodGhpcykubmV4dCgpLnNsaWRlVG9nZ2xlKCk7XG5cdFx0JCh0aGlzKS50b2dnbGVDbGFzcygnY2xvc2VkJyk7XG5cdH0pO1xuXG5cblx0JCh3aW5kb3cpLm9uKFwicmVzaXplXCIsIGZ1bmN0aW9uICgpIHtcblx0IC8vICAgIGlmICgkKHdpbmRvdykud2lkdGgoKSA8IDc2MSkge1xuXHRcdC8vICAgICQoJy5qcy10b2dnbGUnKS5hZGRDbGFzcygnY2xvc2VkJyk7XG5cdFx0Ly8gICAgJCgnLmpzLXRvZ2dsZScpLm5leHQoKS5oaWRlKCk7XG5cblx0XHQvLyAgICAkKCcud2lkZ2V0LXRpdGxlJykuYWRkQ2xhc3MoJ2Nsb3NlZCcpO1xuXHRcdC8vICAgICQoJy53aWRnZXQtdGl0bGUnKS5uZXh0KCkuaGlkZSgpO1xuXHRcdC8vIH1cblx0fSkucmVzaXplKCk7XG5cblxufSkoalF1ZXJ5KTsiLCJqUXVlcnkoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uKCQpIHtcblxudmFyIGlzX3hzLCBpc19zbSwgaXNfbWQsIGlzX2xnLCBpc194bDtcblxuJCggZnVuY3Rpb24oKSB7XG4gICAgZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lciggJ3RvdWNoc3RhcnQnLCBmdW5jdGlvbigpIHt9LCB0cnVlICk7XG5cbiAgICBkb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCAnZ2VzdHVyZXN0YXJ0JywgZnVuY3Rpb24oZSkge1xuICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgfSk7XG59KTtcblxuZnVuY3Rpb24gYnJhdmFkUmVmcmVzaEJwKCkge1xuICAgIGlzX3hzID0gZmFsc2U7XG4gICAgaXNfc20gPSBmYWxzZTtcbiAgICBpc19tZCA9IGZhbHNlO1xuICAgIGlzX2xnID0gZmFsc2U7XG4gICAgaXNfeGwgPSBmYWxzZTtcbn1cblxuJCggd2luZG93ICkub24oICdicEVudGVyX3hzJywgZnVuY3Rpb24oKSB7XG4gICAgYnJhdmFkUmVmcmVzaEJwKCk7XG5cbiAgICBpc194cyA9IHRydWU7XG5cbiAgICAkKCAnLmpzLXRvZ2dsZSwgLndpZGdldC10aXRsZScgKS5lYWNoKCBmdW5jdGlvbigpIHtcbiAgICBcdHZhciBtZSA9ICQoIHRoaXMgKTtcblxuICAgIFx0bWUubmV4dCgpLmhpZGUoKTtcbiAgICB9KTtcbn0pO1xuXG4kKCB3aW5kb3cgKS5vbiggJ2JwRW50ZXJfc20nLCBmdW5jdGlvbigpIHtcbiAgICBicmF2YWRSZWZyZXNoQnAoKTtcbiAgICBcbiAgICBpc19zbSA9IHRydWU7XG59KTtcblxuJCggd2luZG93ICkub24oICdicEVudGVyX21kJywgZnVuY3Rpb24oKSB7XG4gICAgYnJhdmFkUmVmcmVzaEJwKCk7XG4gICAgXG4gICAgaXNfbWQgPSB0cnVlO1xufSk7XG5cbiQoIHdpbmRvdyApLm9uKCAnYnBFbnRlcl9sZycsIGZ1bmN0aW9uKCkge1xuICAgIGJyYXZhZFJlZnJlc2hCcCgpO1xuICAgIFxuICAgIGlzX2xnID0gdHJ1ZTtcbn0pO1xuXG4kKCB3aW5kb3cgKS5vbiggJ2JwRW50ZXJfeGwnLCBmdW5jdGlvbigpIHtcbiAgICBicmF2YWRSZWZyZXNoQnAoKTtcbiAgICBcbiAgICBpc194bCA9IHRydWU7XG59KTtcblxuJCggd2luZG93ICkub24oICdsb2FkJywgZnVuY3Rpb24oKSB7XG5cdGJyYXZhZFJlZnJlc2hCcCgpO1xuICAgIHN2ZzRldmVyeWJvZHkoKTtcbn0pO1xuXG4vKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG5cblx0VG9nZ2xlXG5cbj09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0gKi9cblxuXG5cbi8qID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT1cblxuXHRIaWRlIEhlYWRlciBvbiBvbiBzY3JvbGwgZG93blxuXG49PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09ICovXG52YXIgZGlkU2Nyb2xsO1xudmFyIGxhc3RTY3JvbGxUb3AgPSAwO1xudmFyIGRlbHRhID0gNTtcbnZhciBuYXZiYXJIZWlnaHQgPSAwO1xudmFyIHdpbiA9ICQod2luZG93KS5oZWlnaHQoKTtcblxuJCh3aW5kb3cpLnNjcm9sbChmdW5jdGlvbihldmVudCl7XG4gICAgZGlkU2Nyb2xsID0gdHJ1ZTtcbn0pO1xuXG5zZXRJbnRlcnZhbChmdW5jdGlvbigpIHtcbiAgICBpZiAoZGlkU2Nyb2xsKSB7XG4gICAgICAgIGhhc1Njcm9sbGVkKCk7XG4gICAgICAgIGRpZFNjcm9sbCA9IGZhbHNlO1xuICAgIH1cbn0sIDI1MCk7XG5cbmZ1bmN0aW9uIGhhc1Njcm9sbGVkKCkge1xuICAgIHZhciBzdCA9ICQodGhpcykuc2Nyb2xsVG9wKCk7XG5cbiAgICAvLyBNYWtlIHN1cmUgdGhleSBzY3JvbGwgbW9yZSB0aGFuIGRlbHRhXG4gICAgaWYoTWF0aC5hYnMobGFzdFNjcm9sbFRvcCAtIHN0KSA8PSBkZWx0YSlcbiAgICAgICAgcmV0dXJuO1xuXG4gICAgLy8gSWYgdGhleSBzY3JvbGxlZCBkb3duIGFuZCBhcmUgcGFzdCB0aGUgbmF2YmFyLCBhZGQgY2xhc3MgLm5hdi11cC5cbiAgICAvLyBUaGlzIGlzIG5lY2Vzc2FyeSBzbyB5b3UgbmV2ZXIgc2VlIHdoYXQgaXMgXCJiZWhpbmRcIiB0aGUgbmF2YmFyLlxuICAgIGlmIChzdCA+IGxhc3RTY3JvbGxUb3AgJiYgc3QgPiBuYXZiYXJIZWlnaHQpe1xuICAgICAgICAvLyBTY3JvbGwgRG93blxuICAgICAgICAkKCdoZWFkZXInKS5yZW1vdmVDbGFzcygnbmF2LWRvd24nKS5hZGRDbGFzcygnbmF2LXVwJyk7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgLy8gU2Nyb2xsIFVwXG4gICAgICAgIGlmKHN0ICsgJCh3aW5kb3cpLmhlaWdodCgpIDwgJChkb2N1bWVudCkuaGVpZ2h0KCkpIHtcbiAgICAgICAgICAgICQoJ2hlYWRlcicpLnJlbW92ZUNsYXNzKCduYXYtdXAnKS5hZGRDbGFzcygnbmF2LWRvd24nKTtcbiAgICAgICAgfVxuICAgIH1cbiAgICBsYXN0U2Nyb2xsVG9wID0gc3Q7XG59XG5cblxuLy8gU2Nyb2xsZWQgQ29sb3JcbiQoZnVuY3Rpb24oKSB7XG4gICAgdmFyIGhlYWRlciA9ICQoXCIjaGVhZGVyXCIpO1xuICAgICQod2luZG93KS5sb2FkKGZ1bmN0aW9uKCkge1xuXHQgICAgdmFyIHNjcm9sbCA9ICQod2luZG93KS5zY3JvbGxUb3AoKTtcblx0ICAgIGlmIChzY3JvbGwgPj0gNjUpIHtcbiAgICAgICAgICAgIGhlYWRlci5hZGRDbGFzcyhcInNjcm9sbGVkXCIpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgaGVhZGVyLnJlbW92ZUNsYXNzKFwic2Nyb2xsZWRcIik7XG4gICAgICAgIH1cbiAgICB9KTtcblxuICAgICQod2luZG93KS5zY3JvbGwoZnVuY3Rpb24oKSB7XG5cdCAgICB2YXIgc2Nyb2xsID0gJCh3aW5kb3cpLnNjcm9sbFRvcCgpO1xuICAgICAgICBpZiAoc2Nyb2xsID49IDY1KSB7XG4gICAgICAgICAgICBoZWFkZXIuYWRkQ2xhc3MoXCJzY3JvbGxlZFwiKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIGhlYWRlci5yZW1vdmVDbGFzcyhcInNjcm9sbGVkXCIpO1xuICAgICAgICB9XG4gICAgfSk7XG59KTtcblxuLy8gU3ViIE1lbnUgZHluYW1pYyB3aWR0aFxuZnVuY3Rpb24gc3VibWVudVNpemluZygpIHtcblx0JCgnLnN1Yi1tZW51Jykud2lkdGgoICQoJyNtZW51LW1lbnUtcHJpbmNpcGFsJykud2lkdGgoKSApO1xufVxuXG5zdWJtZW51U2l6aW5nKCk7XG5cbiQoIHdpbmRvdyApLnJlc2l6ZShmdW5jdGlvbigpIHtcblx0c3VibWVudVNpemluZygpO1xufSk7XG5cblxuXG4vKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG5cblx0VG9vbHRpcFxuXG49PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09ICovXG5cdCQoIGZ1bmN0aW9uKCkge1xuXHQgICAgLy8gJCggZG9jdW1lbnQgKS50b29sdGlwKCk7XG5cdH0gKTtcblxuXG4vKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG5cblx0U3dpcGVyXG5cbj09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0gKi9cblxuXHQvLyBIb21lIFByaW1hcnlcblx0dmFyIGdhbGxlcnlUb3AgPSBuZXcgU3dpcGVyKCcuZ2FsbGVyeS10b3AnLCB7XG4gICAgICAgIHNwYWNlQmV0d2VlbjogMCxcbiAgICB9KTtcblxuICAgIHZhciBnYWxsZXJ5VGh1bWJzID0gbmV3IFN3aXBlcignLmdhbGxlcnktdGh1bWJzJywge1xuICAgICAgICBzbGlkZVRvQ2xpY2tlZFNsaWRlOiB0cnVlLFxuICAgICAgICBuZXh0QnV0dG9uOiAnLnN3aXBlci1idXR0b24tbmV4dCcsXG4gICAgICAgIHByZXZCdXR0b246ICcuc3dpcGVyLWJ1dHRvbi1wcmV2JyxcbiAgICB9KTtcbiAgICBnYWxsZXJ5VG9wLnBhcmFtcy5jb250cm9sID0gZ2FsbGVyeVRodW1icztcbiAgICBnYWxsZXJ5VGh1bWJzLnBhcmFtcy5jb250cm9sID0gZ2FsbGVyeVRvcDtcblxuXG4gICAgLy8gSG9tZSBMYXRlc3QgQmxvZyBQb3N0c1xuICAgIHZhciBzd2lwZXIgPSBuZXcgU3dpcGVyKCcubGF0ZXN0X2FydGljbGVzJywge1xuICAgICAgICBuZXh0QnV0dG9uOiAnLnN3aXBlci1idXR0b24tbmV4dCcsXG4gICAgICAgIHByZXZCdXR0b246ICcuc3dpcGVyLWJ1dHRvbi1wcmV2JyxcbiAgICAgICAgc2xpZGVzUGVyVmlldzogMyxcbiAgICAgICAgcGFnaW5hdGlvbkNsaWNrYWJsZTogdHJ1ZSxcbiAgICAgICAgc3BhY2VCZXR3ZWVuOiAwLFxuICAgICAgICBicmVha3BvaW50czoge1xuICAgICAgICAgICAgOTkxOiB7XG4gICAgICAgICAgICAgICAgc2xpZGVzUGVyVmlldzogMSxcbiAgICAgICAgICAgICAgICBzcGFjZUJldHdlZW46IDBcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgIH0pXG5cbiAgICAvLyBTbGlkZXIgZmxleGlibGVcbiAgICB2YXIgc3dpcGVyMiA9IG5ldyBTd2lwZXIoJy5jYXJyb3VzZWwnLCB7XG4gICAgICAgIHNsaWRlc1BlclZpZXc6IDQsXG4gICAgICAgIHBhZ2luYXRpb25DbGlja2FibGU6IHRydWUsXG4gICAgICAgIHNwYWNlQmV0d2VlbjogMCxcbiAgICAgICAgbG9vcDogdHJ1ZSxcblx0XHRhdXRvcGxheTogNTAwMCxcbiAgICAgICAgYnJlYWtwb2ludHM6IHtcbiAgICAgICAgICAgIDk5MToge1xuICAgICAgICAgICAgICAgIHNsaWRlc1BlclZpZXc6IDIsXG4gICAgICAgICAgICB9LFxuICAgICAgICAgICAgNjQwOiB7XG4gICAgICAgICAgICAgICAgc2xpZGVzUGVyVmlldzogMSxcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgIH0pO1xuXG4gICAgLy8gVHJvdXNzZVxuICAgIHZhciBzd2lwZXIzID0gbmV3IFN3aXBlcignLnRyb3Vzc2UnLCB7XG4gICAgICAgIG5leHRCdXR0b246ICcuc3dpcGVyLWJ1dHRvbi1uZXh0JyxcbiAgICAgICAgcHJldkJ1dHRvbjogJy5zd2lwZXItYnV0dG9uLXByZXYnLFxuICAgICAgICBzbGlkZXNQZXJWaWV3OiAzLFxuICAgICAgICBkaXJlY3Rpb246ICd2ZXJ0aWNhbCcsXG4gICAgICAgIHBhZ2luYXRpb25DbGlja2FibGU6IHRydWUsXG4gICAgICAgIHNwYWNlQmV0d2VlbjogNTAsXG4gICAgICAgIGJyZWFrcG9pbnRzOiB7XG4gICAgICAgICAgICA3Njg6IHtcbiAgICAgICAgICAgICAgICBzbGlkZXNQZXJWaWV3OiAxLFxuICAgICAgICAgICAgICAgIHNwYWNlQmV0d2VlbjogMCxcbiAgICAgICAgICAgICAgICBkaXJlY3Rpb246ICdob3Jpem9udGFsJyxcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgIH0pO1xuXG5cbi8qID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT1cblxuXHRMaW5lcyB3aWR0aFxuXG49PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09ICovXG5cdHNldFRpbWVvdXQoZnVuY3Rpb24oKSAge1xuXHRcdCQoJy50ZWFtX3RpdGxlIC5ib3JkZXItZ3Jpcy5ib3R0b20nKS5jc3MoJ3dpZHRoJywgJCgnLnRlYW1fdGl0bGUgLmJvcmRlci1ncmlzLnRvcCcpLndpZHRoKCkgKTtcblx0fSwgMTAwMCk7XG5cblxuLyogPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PVxuXG5cdE1vYmlsZSBNZW51XG5cbj09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0gKi9cblx0JChcIiNtZW51LWJ1dHRvblwiKS5jbGljayhmdW5jdGlvbigpe1xuXHRcdCQoJyNoZWFkZXInKS50b2dnbGVDbGFzcygnb3BlbicpO1xuXHRcdCQoJyNtZW51JykudG9nZ2xlQ2xhc3MoJ2FjdGl2ZScpO1xuXHRcdCQoJ2JvZHknKS50b2dnbGVDbGFzcygnZml4ZWQnKTtcblx0XHQkKHRoaXMpLnRvZ2dsZUNsYXNzKCdtZW51LW9wZW5lZCcpO1xuXHR9KTtcblxuXHQkKFwiI2Nzc21lbnVcIikuZmluZCgnbGkgdWwnKS5wYXJlbnQoKS5hZGRDbGFzcygnaGFzLXN1YicpO1xuXHQkKFwiI2Nzc21lbnVcIikuZmluZChcIi5oYXMtc3ViXCIpLnByZXBlbmQoJzxzcGFuIGNsYXNzPVwic3VibWVudS1idXR0b25cIj48aW1nIGNsYXNzPVwic3ZnXCIgc3JjPVwiL3dwLWNvbnRlbnQvdGhlbWVzL2JyYXZhZC9hc3NldHMvZGlzdC9pbWcvYXJyb3cuc3ZnXCI+PC9zcGFuPicpO1xuXHQkKFwiI2Nzc21lbnVcIikuZmluZCgnLnN1Ym1lbnUtYnV0dG9uJykub24oJ2NsaWNrJywgZnVuY3Rpb24oKSB7XG4gICAgICAgICQodGhpcykudG9nZ2xlQ2xhc3MoJ3N1Ym1lbnUtb3BlbmVkJyk7XG4gICAgICAgIGlmICgkKHRoaXMpLnNpYmxpbmdzKCd1bCcpLmhhc0NsYXNzKCdvcGVuJykpIHtcbiAgICAgICAgXHQkKHRoaXMpLnNpYmxpbmdzKCd1bCcpLnJlbW92ZUNsYXNzKCdvcGVuJykuc2xpZGVUb2dnbGUoXCJzbG93XCIpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICBcdCQodGhpcykuc2libGluZ3MoJ3VsJykuYWRkQ2xhc3MoJ29wZW4nKS5zbGlkZVRvZ2dsZShcInNsb3dcIik7XG4gICAgICAgIH1cbiAgICB9KTtcblxuXG5cdC8vIE1vYmlsZSBtZW51IGZpeFxuXHRpZiAoISQoXCJib2R5XCIpLmhhc0NsYXNzKFwicGFnZS10ZW1wbGF0ZS1leHRyYW5ldFwiKSkge1xuXHRcdHZhciBkaXZzID0gJChcIiNtZW51ID4gZGl2XCIpO1xuXHRcdGZvcih2YXIgaSA9IDA7IGkgPCBkaXZzLmxlbmd0aDsgaSs9Mikge1xuXHRcdCAgZGl2cy5zbGljZShpLCBpKzIpLndyYXBBbGwoXCI8ZGl2IGNsYXNzPSdyb3cnPjxkaXYgY2xhc3M9J3Jvdy1pbm5lcic+PC9kaXY+PC9kaXY+XCIpO1xuXHRcdH1cblx0fVxuXG5cbi8qID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT1cblxuXHRWaWRlb3MgU2xpZGVyXG5cbj09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0gKi9cblxuXHQvLyBGYW5jeWJveFxuXHRpZiAoJChcImJvZHlcIikuaGFzQ2xhc3MoXCJob21lXCIpKSB7XG5cdFx0JChcIi52aWRlb1wiKS5jbGljayhmdW5jdGlvbigpIHtcblx0XHRcdHZhciBhZGRyZXNzVmFsdWUgPSAkKHRoaXMpLmF0dHIoXCJocmVmXCIpO1xuXHRcdFx0JC5mYW5jeWJveCh7XG5cdFx0XHRcdCd0cmFuc2l0aW9uSW4nXHQ6ICdmYWRlJyxcblx0XHRcdFx0J3RyYW5zaXRpb25PdXQnXHQ6ICdmYWRlJyxcblx0XHRcdFx0J3RpdGxlJ1x0XHRcdDogdGhpcy50aXRsZSxcblx0XHRcdFx0J2hyZWYnXHRcdFx0OiBhZGRyZXNzVmFsdWUucmVwbGFjZShuZXcgUmVnRXhwKFwid2F0Y2hcXFxcP3Y9XCIsIFwiaVwiKSwgJ3YvJyksXG5cdFx0XHRcdCd0eXBlJ1x0XHRcdDogJ2lmcmFtZScsXG5cdFx0XHR9KTtcblx0XHRcdHJldHVybiBmYWxzZTtcblx0XHR9KTtcblx0fVxuXG5cdC8vIFBhZ2Vyc1xuXHQkKCcjcGFnZXIxJykuYWRkQ2xhc3MoJ2FjdGl2ZScpO1xuXHQkKFwiW2lkXj1wYWdlcl1cIikuY2xpY2soZnVuY3Rpb24oKSB7XG5cdFx0JChcIi5wYWdlclwiKS5yZW1vdmVDbGFzcygnYWN0aXZlJyk7XG5cdFx0JChcIi5ibG9jX3ZpZGVvXCIpLmZhZGVPdXQoJ3Nsb3cnKTtcblx0XHQkKCcjdmlkZW8nKyB0aGlzLmlkLm1hdGNoKC9cXGQrLykgKS5mYWRlSW4oXCJzbG93XCIpO1xuXHRcdCQodGhpcykuYWRkQ2xhc3MoJ2FjdGl2ZScpO1xuXHR9KTtcblxuXG4vKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG5cblx0RmFuY3lib3hcblxuPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PSAqL1xuXHQkKFwiLmZhbmN5Ym94XCIpLmZhbmN5Ym94KHtcblx0XHRvcGVuRWZmZWN0XHQ6ICdmYWRlJyxcblx0XHRjbG9zZUVmZmVjdFx0OiAnZmFkZScsXG5cdFx0YXV0b1Jlc2l6ZSA6IHRydWUsXG5cdFx0bWF4V2lkdGggOiAnNjAlJ1xuXHR9KTtcblxuXG4vKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG5cblx0TmV3c2xldHRlciBWYWxpZGF0ZVxuXG49PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09ICovXG5cdCQoICdmb3JtLnZhbGlkYXRlLCBmb3JtLnZhbGlkYXRlMScgKS5vbiggJ3N1Ym1pdCcsIGZ1bmN0aW9uKCkge1xuXHQgICAgaWYoICEkKCB0aGlzICkudmFsaWQoKSApIHtcblx0ICAgICAgICB2YXIgbWUgPSAkKCB0aGlzICksXG5cdCAgICAgICAgICAgIG1zZyA9IG1lLmZpbmQoICcuYWxlcnQnICkuYXR0ciggJ2RhdGEtbXNnJyApO1xuXHQgICAgICAgICAgICBtZS5maW5kKCAnLmFsZXJ0JyApXG5cdCAgICAgICAgICAgICAgICAudGV4dCggbXNnIClcblx0ICAgICAgICAgICAgICAgIC5hZGRDbGFzcyggJ2ludmFsaWQgc2hvdycgKTtcblx0ICAgICAgICByZXR1cm4gZmFsc2U7XG5cdCAgICB9XG5cdH0pO1xuXG5cdCQoICdmb3JtLnZhbGlkYXRlJyApLnZhbGlkYXRlKHtcblx0ICAgIHJ1bGVzOiB7XG5cdCAgICAgICAgZW1haWw6IHtcblx0ICAgICAgICAgICAgcmVxdWlyZWQ6IHRydWUsXG5cdCAgICAgICAgICAgIG5ld19lbWFpbDogdHJ1ZVxuXHQgICAgICAgIH0sXG5cdCAgICB9LFxuXHQgICAgZXJyb3JFbGVtZW50OiAnc3BhbicsXG5cdCAgICBlcnJvckNsYXNzOiAnaW52YWxpZCcsXG5cdCAgICBtZXNzYWdlczoge1xuXHQgICAgICAgIHJlcXVpcmVkOiAkKCB0aGlzICkuYXR0ciggJ2RhdGEtbXNnJyApXG5cdCAgICB9XG5cdH0pO1xuXG5cdCQoICdmb3JtLnZhbGlkYXRlMScgKS52YWxpZGF0ZSh7XG5cdCAgICBydWxlczoge1xuXHQgICAgICAgIGVtYWlsOiB7XG5cdCAgICAgICAgICAgIHJlcXVpcmVkOiB0cnVlLFxuXHQgICAgICAgICAgICBuZXdfZW1haWw6IHRydWVcblx0ICAgICAgICB9LFxuXHQgICAgfSxcblx0ICAgIGVycm9yRWxlbWVudDogJ3NwYW4nLFxuXHQgICAgZXJyb3JDbGFzczogJ2ludmFsaWQnLFxuXHQgICAgbWVzc2FnZXM6IHtcblx0ICAgICAgICByZXF1aXJlZDogJCggdGhpcyApLmF0dHIoICdkYXRhLW1zZycgKVxuXHQgICAgfVxuXHR9KTtcblxuXHRqUXVlcnkudmFsaWRhdG9yLmFkZE1ldGhvZCggJ25ld19lbWFpbCcsIGZ1bmN0aW9uKCB2YWx1ZSwgZWxlbWVudCApIHtcbiAgICAgICAgcmV0dXJuIHRoaXMub3B0aW9uYWwoIGVsZW1lbnQgKSB8fCAvXlthLXowLTkuXy1dK0BbYS16MC05Li1dezIsfVsuXVthLXpdezIsM30kLy50ZXN0KCB2YWx1ZSApO1xuICAgIH0sIFwiTOKAmWFkcmVzc2UgY291cnJpZWwgbuKAmWVzdCBwYXMgdmFsaWRlLlwiKTtcblxuXG5cblxuXG4vKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG5cblx0RmxleGlibGUgY29udGVudFxuXG49PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09ICovXG4kKCcuc2lkZV9pbWdfd3JhcCcpLmNzcygnaGVpZ2h0JywgJCgnI3BhZ2VoZWFkZXInKS5oZWlnaHQoKSApO1xuXG5cbi8qID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT1cblxuXHRHb3RvXG5cbj09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0gKi9cblx0aWYoJCh3aW5kb3cpLndpZHRoKCkgPj0gOTkyKXtcblx0XHRpZiggJCggJy5zaWRlYmFyX3RleHRlIGEnICkubGVuZ3RoID4gMCApIHtcblx0XHRcdCQoICcuc2lkZWJhcl90ZXh0ZSBhW2hyZWZePVwiI1wiXScgKS5vbiggJ2NsaWNrJywgZnVuY3Rpb24oKSB7XG5cdFx0XHRcdHZhciBtZSA9JCggdGhpcyApLFxuXHRcdFx0XHRcdHRoZV9pZCA9IG1lLmF0dHIoICdocmVmJyApO1xuXG5cdFx0XHRcdCQoICdodG1sLCBib2R5JyApLmFuaW1hdGUoIHtcblx0XHRcdFx0XHRzY3JvbGxUb3A6ICQoIHRoZV9pZCApLm9mZnNldCgpLnRvcC0xMDBcblx0XHRcdFx0fSwgJ3Nsb3cnICk7XG5cdFx0XHRcdHJldHVybiBmYWxzZTtcblx0XHRcdH0pO1xuXHRcdH1cblx0fVxuXG5cdGlmKCQod2luZG93KS53aWR0aCgpIDw9IDk5MSl7XG5cdFx0aWYoICQoICcuc2lkZWJhcl90ZXh0ZSBhJyApLmxlbmd0aCA+IDAgKSB7XG5cdFx0XHQkKCAnLnNpZGViYXJfdGV4dGUgYVtocmVmXj1cIiNcIl0nICkub24oICdjbGljaycsIGZ1bmN0aW9uKCkge1xuXHRcdFx0XHR2YXIgbWUgPSQoIHRoaXMgKSxcblx0XHRcdFx0XHR0aGVfaWQgPSBtZS5hdHRyKCAnaHJlZicgKTtcblx0XHRcdFx0JCggJ2h0bWwsIGJvZHknICkuYW5pbWF0ZSgge1xuXHRcdFx0XHRcdHNjcm9sbFRvcDogJCggdGhlX2lkICkub2Zmc2V0KCkudG9wLTY1XG5cdFx0XHRcdH0sICdzbG93JyApO1xuXHRcdFx0XHRyZXR1cm4gZmFsc2U7XG5cdFx0XHR9KTtcblx0XHR9XG5cdH1cblxufSk7XG5cblxuXG4vKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG5cblx0RkFRIEFjY29yZGlvblxuXG49PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09ICovXG5qUXVlcnkoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uKCQpIHtcblxuXHQkKFwiW2lkXj10b2dnbGUtdGl0bGVdXCIpLmNsaWNrKGZ1bmN0aW9uKGUpIHtcblxuXHRcdGUucHJldmVudERlZmF1bHQoKTtcblxuXHQgICAgdmFyICR0aGlzID0gJCh0aGlzKS5wYXJlbnQoKS5maW5kKCdbaWRePXRvZ2dsZS1pbm5lcl0nKTtcblx0ICAgIHZhciAkdGhpc2ltYWdlID0gJCgnI2ltYWdlLXN5bXB0b21lcy0nKyB0aGlzLmlkLm1hdGNoKC9cXGQrLykgKTtcblxuXHQgICAgJCh0aGlzKS50b2dnbGVDbGFzcygnYWN0aXZlJyk7XG5cblx0ICAgICQoJy50b2dnbGUgW2lkXj10b2dnbGUtaW5uZXJdJykubm90KCR0aGlzKS5zbGlkZVVwKCdzbG93Jyk7XG5cdCAgICAkKCdbaWRePWltYWdlLXN5bXB0b21lc10nKS5ub3QoJHRoaXNpbWFnZSkuZmFkZU91dCgnc2xvdycpO1xuXG5cdCAgICAkdGhpcy5zbGlkZVRvZ2dsZSgnc2xvdycpO1xuXHQgICAgJHRoaXNpbWFnZS5mYWRlSW4oJ3Nsb3cnKTtcblxuXHR9KTtcblxuXHQkKCcjdG9nZ2xlLXRpdGxlLTEnKS50cmlnZ2VyKCdjbGljaycpO1xuXG59KTtcblxuXG5cbi8qXG5pZiggalF1ZXJ5KFwiLnRvZ2dsZSAudG9nZ2xlLXRpdGxlXCIpLmhhc0NsYXNzKCdhY3RpdmUnKSApe1xuXG5cdFx0alF1ZXJ5KFwiLnRvZ2dsZSAudG9nZ2xlLXRpdGxlLmFjdGl2ZVwiKS5jbG9zZXN0KCcudG9nZ2xlJykuZmluZCgnLnRvZ2dsZS1pbm5lcicpLnNob3coKTtcblx0XHRqUXVlcnkoXCIudG9nZ2xlIC50b2dnbGUtdGl0bGUuYWN0aXZlXCIpLmNsb3Nlc3QoJy5pbWFnZS1zeW1wdG9tZXMnKS5maW5kKCcudG9nZ2xlLWlubmVyJykuc2hvdygpO1xuXG5cblxuXHR9XG5cblx0alF1ZXJ5KFwiLnRvZ2dsZSAudG9nZ2xlLXRpdGxlXCIpLmNsaWNrKGZ1bmN0aW9uKCl7XG5cblx0XHRpZiggalF1ZXJ5KHRoaXMpLmhhc0NsYXNzKCdhY3RpdmUnKSApe1xuXG5cdFx0XHRqUXVlcnkodGhpcykucmVtb3ZlQ2xhc3MoXCJhY3RpdmVcIikuY2xvc2VzdCgnLnRvZ2dsZScpLmZpbmQoJy50b2dnbGUtaW5uZXInKS5zbGlkZVVwKDIwMCk7XG5cblx0XHR9IGVsc2Uge1xuXHRcdFx0alF1ZXJ5KHRoaXMpLmFkZENsYXNzKFwiYWN0aXZlXCIpLmNsb3Nlc3QoJy50b2dnbGUnKS5maW5kKCcudG9nZ2xlLWlubmVyJykuc2xpZGVEb3duKDIwMCk7XG5cdFx0XHRqUXVlcnkodGhpcykuY2xvc2VzdCgnLmltYWdlLXN5bXB0b21lcycpLmZpbmQoJy50b2dnbGUtaW5uZXInKS5zaG93KCk7XG5cdFx0fVxuXG5cblxufSk7XG4qL1xuXG5cblxualF1ZXJ5KFwiLnRvZ2dsZSAudG9nZ2xlLXRpdGxlXCIpLmNsaWNrKGZ1bmN0aW9uKCkge1xuXHRqUXVlcnkodGhpcykuZmluZCgnLnBsdXMnKS50b2dnbGVDbGFzcygnYWN0aXZlJyk7XG59KTtcblxuXG5cblxuXG4vKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09XG5cblx0U21vb3RoIHNjcm9sbFxuXG49PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09ICovXG5qUXVlcnkoJy5zY3JvbGxUbycpLm9uKCdjbGljaycsIGZ1bmN0aW9uKCkge1xuXHR2YXIgcGFnZSA9IGpRdWVyeSh0aGlzKS5hdHRyKCdocmVmJyk7XG5cdHZhciBzcGVlZCA9IDc1MDtcblx0alF1ZXJ5KCdodG1sLCBib2R5JykuYW5pbWF0ZSggeyBzY3JvbGxUb3A6IGpRdWVyeSgnI3Njcm9sbCcpLm9mZnNldCgpLnRvcCB9LCBzcGVlZCApOyAvLyBHb1xuXHRyZXR1cm4gZmFsc2U7XG59KTsiXX0=
