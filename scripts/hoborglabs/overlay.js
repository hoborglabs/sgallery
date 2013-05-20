define([], function() {
	
	function Overlay(window) {
		this.el = window.document.getElementById('overlay');
		this.panels = null;
		this.currentPanelName = null;
	}

	Overlay.prototype.show = function(panelName) {
		this.activate();
		var panels = this.getPanels();
		var panel = null;

		if (undefined != panels[panelName]) {
			this.currentPanelName = panelName;
			panel = panels[panelName];
			panel.className = 'modal-panel active';
		}

		return panel;
	};

	Overlay.prototype.isPanelActive = function(panelName) {
		return panelName == this.currentPanelName;
	}

	Overlay.prototype.activate = function() {
		this.el.className = 'modal-backdrop';
	};

	Overlay.prototype.deactivate = function() {
		this.el.className = 'modal-backdrop fade';

		var panels = this.getPanels();
		for (var i = 0; i < panels.length; i++) {
			panels.item(i).className = 'modal-panel';
		}
		this.currentPanelName = null;
	};

	Overlay.prototype.getPanels = function() {
		if (this.panels) {
			return this.panels;
		}

		var panels = this.el.getElementsByClassName('modal-panel');
		this.panels = {};
		for (var i = 0; i < panels.length; i++) {
			this.panels[panels.item(i).getAttribute('data-name')] = panels.item(i);
		}

		return this.panels;
	};

	return new Overlay(window);
});