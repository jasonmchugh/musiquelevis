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