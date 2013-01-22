define([], function() {
	
	function Overlay(window) {
		this.el = window.document.getElementById('overlay');
		this.panels = null;
	}

	Overlay.prototype.show = function(panelName) {
		this.activate();
		var panels = this.getPanels();
		var panel = null;

		for (var i = 0; i < panels.length; i++) {
			if (panelName == panels.item(i).getAttribute('data-name')) {
				panels.item(i).className = 'modal-panel active';
				panel = panels.item(i);
			} else {
				panels.item(i).className = 'modal-panel';
			}
		}

		return panel;
	};

	Overlay.prototype.activate = function() {
		this.el.className = 'modal-backdrop';
	}

	Overlay.prototype.deactivate = function() {
		this.el.className = 'modal-backdrop fade';

		var panels = this.getPanels();
		for (var i = 0; i < panels.length; i++) {
			panels.item(i).className = 'modal-panel';
		};
	}

	Overlay.prototype.getPanels = function() {
		if (this.panels) {
			return this.panels;
		}

		return this.panels = this.el.getElementsByClassName('modal-panel');
	};

	return new Overlay(window);
});