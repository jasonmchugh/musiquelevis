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
