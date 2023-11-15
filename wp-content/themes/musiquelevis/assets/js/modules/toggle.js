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