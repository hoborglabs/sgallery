define([
	'libs/microajax',
	'libs/json2',
	'libs/microinfinitescroll',
	'libs/bean',
	'hoborglabs/overlay'
], function(ajax, JSON, infiniteScroll, bean, overlay) {

	var exports = {};

	function Album(window) {
		this.document = window.document;
		this.previewImg = window.document.getElementById('img-preview'); 
		this.previewImgBaseUrl = '/img-proxy.php';
		this.config = window.SG.config;
		this.nextBatch = 0;
		this.el = this.document.getElementById('album')
		this.loadingEl = this.el.getElementsByClassName('well').item(0);

		var album = this;
		bean.on(this.el, 'click', 'a', function(e) { return album.handleClick(e); });

		infiniteScroll.addHandler(this.loadImages.bind(this));
		this.loadImages(function() {
			infiniteScroll.start();
		});
	}

	Album.prototype.handleClick = function(e) {

		var fullImg = e.target.getAttribute('data-full-size');
		if (fullImg) {
			e.stop();
			e.preventDefault();
			e.stopImmediatePropagation();
			overlay.show('preview');
			this.previewImg.src = this.previewImgBaseUrl + fullImg;

			return false;
		}

		return true;
	};

	Album.prototype.loadImages = function(callback) {

		if (this.nextBatch >= this.config.photos.length) {
			infiniteScroll.stop();
			return;
		}

		var photos = document.getElementById('photos');

		// show "loading" bar
		var loadingEl = this.loadingEl;
		loadingEl.style.display = 'block';

		ajax(this.config.photos[this.nextBatch++], function(data) {
			var batch = JSON.parse(data);
			photos.innerHTML = photos.innerHTML + batch.html;
			loadingEl.style.display = 'none';

			if (callback) {
				callback();
			} else {
				infiniteScroll.done();
			}
		});
	};

	exports.Album = Album;
	return exports;
})