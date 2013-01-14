
require([
	'libs/microajax',
	'libs/json2'
], function(ajax, JSON) {

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

	var app = window.app = {};

	function Page(window) {
		this.document = window.document;
		this.config = window.SG.config;
		this.onLoadCallbacks = [];

		window.onload = this.onLoad.bind(this);
		this.attachToPageOnLoad(this.loadImages.bind(this));
		this.onLoad();
	};

	Page.prototype.onLoad = function(e) {
		for (var i in this.onLoadCallbacks) {
			this.onLoadCallbacks[i](e);
		}
	};

	Page.prototype.attachToPageOnLoad = function(callback) {
		this.onLoadCallbacks.push(callback);
	};

	Page.prototype.loadImages = function() {
		var photos = document.getElementById('photos');
		ajax(this.config.photos[0], function(data) {
			var batch = JSON.parse(data);
			photos.innerHTML = photos.innerHTML + batch.html;
		});
	};

	app.page = new Page(window);

});