
require([
	'libs/microajax',
	'libs/json2',
	'libs/microinfinitescroll'
], function(ajax, JSON, infiniteScroll) {

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
		this.nextBatch = 0;

		infiniteScroll.addHandler(this.loadImages.bind(this));
		this.loadImages(function() {
			infiniteScroll.start();
		});
	};

	Page.prototype.loadImages = function(callback) {
		var photos = document.getElementById('photos');
		ajax(this.config.photos[this.nextBatch++], function(data) {
			var batch = JSON.parse(data);
			photos.innerHTML = photos.innerHTML + batch.html;
			if (callback) {
				callback();
			} else {
				infiniteScroll.done();
			}
		});

		if (this.nextBatch >= this.config.photos.length) {
			infiniteScroll.stop();
		}
	};

	app.page = new Page(window);
});
