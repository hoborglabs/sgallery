
require([
	'libs/ready',
	'libs/mousetrap',
	'hoborglabs/album',
	'hoborglabs/overlay'
], function(ready, mousetrap, Album, Overlay) {

	if (!Function.prototype.bind) {
		Function.prototype.bind = function (oThis) {
			if (typeof this !== "function") {
				// closest thing possible to the ECMAScript 5 internal IsCallable function
				throw new TypeError("Function.prototype.bind - what is trying to be bound is not callable");
			}
	
			var aArgs = Array.prototype.slice.call(arguments, 1), 
				fToBind = this, 
				fNOP = function () {},
				fBound = function () {
					return fToBind.apply(this instanceof fNOP && oThis
							? this
							: oThis, aArgs.concat(Array.prototype.slice.call(arguments)));
				};
	
			fNOP.prototype = this.prototype;
			fBound.prototype = new fNOP();
	
			return fBound;
		};
	}

	ready(function() {
		var album = new Album.Album(window);
		mousetrap.bind('?', function() {Overlay.show('help'); });
		mousetrap.bind('esc', function() {Overlay.deactivate(); });
	});

});
