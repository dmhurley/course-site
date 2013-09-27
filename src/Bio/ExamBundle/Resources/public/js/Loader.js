function Loader(settings) {
	this.settings = {};
	this.buttons = {};

	this.sendRequest = function(url, post, onload) {
		var ajax = new XMLHttpRequest();
		console.log(url);
		ajax.open('POST', url, true);
		ajax.onLoad = onload;
		ajax.onerror = onload;
		ajax.onabort = onload;
		ajax.send(post);
	}

	this._callFunction = function(fn, arg1, arg2) {
		console.log('calling ' + fn + '...');
		if (this.settings[fn] instanceof Function) {
			return this.settings[fn](arg1, arg2);
		} else {
			throw fn + " function not defined in settings!";
		}
	}

	this._setSettings = function(settings) {
		if (!settings ||
			!settings.bundle ||
			!settings.entity ||
			!settings.table ) {
			throw "Required settings not set.";
		}

		var defaults = {
			'url': '',
			'space': 'bio',
			'bundle': '',
			'entity': '',
			'buttons': ['delete', 'edit'],
			'table': null,
			'delete': function(button, self) {
				a = button;
				var url = self.settings.url + '/' + 
						  self.settings.bundle + '/' + 
						  self.settings.entity + '/' + 
						  'delete' + '/' + button.attributes['data-id'].value;

				self.sendRequest(url, null, function() {
					button.parentNode.parentNode.removeChild(button.parentNode);
				});
			},
			'edit': function() {
				console.log('editing');
			}
		};

		for (key in defaults) {
			this.settings[key] = settings[key]?settings[key]:defaults[key];
		}
		console.log("Settings set...");
	}

	this._registerButtons = function() {
		for(index in this.settings.buttons ) {
			var buttonName = this.settings.buttons[index];
			this.buttons[buttonName] = document.querySelectorAll('td.link.'+buttonName);
			for (var i = 0; button = this.buttons[buttonName][i]; i++) {
				button.addEventListener('click', (function(b, bn, s) {
					return function() {
						s._callFunction(bn, b, s);
					}
				})(button, buttonName, this));
			}
		}


		console.log("Buttons registered...");
	}

	this._init = function(settings) {
		this._setSettings(settings);
		this._registerButtons()

	}
	this._init(settings);
}