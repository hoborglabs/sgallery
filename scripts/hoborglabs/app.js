
require([
	'libs/microajax',
	'libs/json2'
], function(ajax, JSON) {

	console.log(1, JSON);
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
		console.log(2, this.onLoad.bind);
		this.document = window.document;
		this.config = window.SG.config;
		this.onLoadCallbacks = [];
		console.log(this);

		window.onload = this.onLoad.bind(this);
		this.attachToPageOnLoad(this.loadImages.bind(this));
		this.onLoad();
	};

	Page.prototype.onLoad = function(e) {
		console.log(3);
		for (var i in this.onLoadCallbacks) {
			this.onLoadCallbacks[i](e);
		}
	};

	Page.prototype.attachToPageOnLoad = function(callback) {
		console.log(4);
		this.onLoadCallbacks.push(callback);
	};

	Page.prototype.loadImages = function() {
		console.log(5);
		console.log(this.config.photos);
		var photos = document.getElementById('photos');
		ajax(this.config.photos[0], function(data) {
			console.log('data', JSON.parse(data));
			var batch = JSON.parse(data);
			for (var i in batch.images) {
				console.log(batch.images[i].src);
				photos.innerHTML = photos.innerHTML + '<li class="span6"><img class="img-polaroid" src ="' + batch.images[i].src + '" /></li>'
			}
		});
	};

	app.page = new Page(window);

});