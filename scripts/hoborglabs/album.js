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
		this.el = this.document.getElementById('album');
		
		// buffer div
		this.buffer = this.document.createElement('div');
		this.buffer.style.display = 'none';

		// preview image
		this.previewEl = window.document.getElementById('img-preview');
		this.previewImg = this.previewEl.getElementsByTagName('img').item(0);
		this.previewMsg = this.previewEl.getElementsByClassName('photo-message').item(0);
		var album = this;
		this.previewImg.onload = function() { album.previewMsg.style.display = 'none'; };

		this.config = window.SG.config;
		this.nextBatch = 0;
		this.loadingEl = this.el.getElementsByClassName('well').item(0);

		var album = this;
		bean.on(this.el, 'click', 'a', function(e) { return album.handleClick(e); });

		// bind click on preview
		bean.on(this.document.getElementById('overlay'), 'click', function(e) { overlay.deactivate(); });

		infiniteScroll.addHandler(this.loadImages.bind(this));
		this.loadImages(function() {
			infiniteScroll.start();
			album.currentImg = album.el.getElementsByClassName('photo').item(0);
			album.currentImg.className = 'photo photo-selected';
		});
	}

	/**
	 * handles click on thumb link.
	 * 
	 * @param Event e
	 */
	Album.prototype.handleClick = function(e) {
		var fullImg = e.target.getAttribute('data-full-size');
		if (fullImg) {
			e.stop();
			e.preventDefault();
			e.stopImmediatePropagation();

			// reset styles
			this.currentImg.className = 'photo';
			// preview selected img
			this.previewImage(e.target.parentNode.parentNode);

			return false;
		}

		return true;
	};

	Album.prototype.previewImage = function(photoEl) {
		// show "loading" msg
		this.previewMsg.style.display = 'block';

		var fullImg = photoEl.getElementsByTagName('img').item(0).getAttribute('data-full-size');
		if (fullImg) {
			// slide out current image
			var album = this;
			var oldImg = this.previewImg;
			oldImg.className += ' slide-out';
			setTimeout(function() {
				oldImg.onload = null;
				oldImg.parentNode.removeChild(oldImg);
				album.previewImg.className = 'photo-preview';
			}, 500);

			// create new img tag
			var img = this.document.createElement('img');
			this.previewEl.appendChild(img);
			img.className = 'photo-preview slide-in';
			img.onload = function() { album.previewMsg.style.display = 'none'; };
			img.src = fullImg;

			overlay.show('preview');

			this.previewImg = img;
		}

		photoEl.className = 'photo photo-selected';
		this.currentImg = photoEl;
	}

	Album.prototype.showCurrentImage = function() {
		this.previewImage(this.currentImg);
	};

	Album.prototype.previousImage = function() {
		var previous = this.currentImg.parentNode.previousSibling;
		while (previous && 'LI' != previous.tagName) {
			previous = previous.previousSibling;
		}

		if (previous) {
			this.currentImg.className = 'photo';
			this.currentImg = previous.getElementsByClassName('photo').item(0);
			this.currentImg.className = 'photo photo-selected';

			if (overlay.isPanelActive('preview')) {
				this.previewImage(this.currentImg);
			}
		}
	};

	Album.prototype.nextImage = function() {
		var next = this.currentImg.parentNode.nextSibling;
		while (next && 'LI' != next.tagName) {
			next = next.nextSibling;
		}

		if (!next) {
			// try to load next batch
			var album = this;
			this.loadImages(function() {
				album.nextImage();
			});
			return;
		}

		if (next) {
			this.currentImg.className = 'photo';
			this.currentImg = next.getElementsByClassName('photo').item(0);
			this.currentImg.className = 'photo photo-selected';

			if (overlay.isPanelActive('preview')) {
				this.previewImage(this.currentImg);
			}
		}
	}

	Album.prototype.loadImages = function(callback) {

		if (this.nextBatch >= this.config.photos.length) {
			infiniteScroll.stop();
			return false;
		}

		var photos = document.getElementById('photos');

		// show "loading" bar
		var loadingEl = this.loadingEl;
		var album = this;
		loadingEl.style.display = 'block';

		ajax(this.config.photos[this.nextBatch++], function(data) {
			var batch = JSON.parse(data);
			
			// save HTML to buffer and move to photos EL
			var node = null;
			album.buffer.innerHTML = batch.html;
			while (node = album.buffer.firstChild) {
				photos.appendChild(node);
			}
			//photos.innerHTML = photos.innerHTML + batch.html;
			loadingEl.style.display = 'none';
			album.buffer.innerHTML = '';

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