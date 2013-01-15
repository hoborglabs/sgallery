
define([], function() {
	
	var exports = {};

	// Detect that we are at bottom of page, and call autoloading function
	var killScroll = false;
	
	var handlers = [];
	
	var myWidth = 0,
		myHeight = 0;
	
	if (typeof(window.innerWidth) == 'number') {
		//Non-IE
		myWidth = window.innerWidth;
		myHeight = window.innerHeight;
	} else if (document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
		//IE 6+ in 'standards compliant mode'
		myWidth = document.documentElement.clientWidth;
		myHeight = document.documentElement.clientHeight;
	}

	window.onscroll = function() {
		if (killScroll) {
			return;
		}

		var scrolledtonum = window.pageYOffset + myHeight + 200;
		var heightofbody = document.body.offsetHeight;
		if (scrolledtonum >= heightofbody) {
			killScroll = true;
			runHandlers();
		}
	};

	function runHandlers() {
		for (var i in handlers) {
			handlers[i]();
		}
		killScroll = false;
	}

	exports.addHandler = function(handler) {
		handlers.push(handler);
	}

	exports.stop = function() {
		window.onscroll = function() {};
	}

	return exports;
});
