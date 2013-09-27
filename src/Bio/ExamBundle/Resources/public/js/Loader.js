function Loader(settings) {
/********* PUBLIC VARIABLES *********/
	this.settings = {};
	this.buttons = {};


/********** PUBLIC FUNCTION **********/
	this.sendRequest = function(url, post, onload) {
		ajax = new XMLHttpRequest();
		console.log(url);
		ajax.open('POST', url, true);
		ajax.onload = onload;
		ajax.send(post);
	}

	this.success = function(message) {

	}

	this.failure = function(message) {

	}
/************* PRIVATE FUNCTION ***************/

	this._addRow = function(data) {
		console.log(data);
		row = this.settings.table.querySelector('tbody').insertRow();

		for (var i = this.settings.buttons.length - 1; button = this.settings.buttons[i]; i--) {
			var cell = row.insertCell();
			cell.classList.add('link');
			cell.classList.add(button);
			cell.innerHTML = button;
			cell.setAttribute('data-id', data.id)
			cell.addEventListener('click', (function(b, bn, s) {
						return function() {
							s._callFunction(bn, b, s);
						}
					})(cell, button, this));
			}

		for(var i = this.settings.columns.length - 1; header = this.settings.columns[i]; i--) {
			row.insertCell().innerHTML = data[header]?data[header]:header
		}
	}

	this._generateUrl = function(action, id) {
		console.log(id);
		var url = this.settings.url + 
			   this.settings.bundle + '/' + 
			   this.settings.entity + '/' + action + (id?('/' + id):'');

		console.log('generated ' + url);
		// return 'http://localhost/~nick/course-site/web/app_dev.php/';
		return url;
	}

	// calls a function found in the settings matching fn
	// calls fn and passes two arguments arg1 and arg2
	this._callFunction = function(fn, arg1, arg2) {
		console.log('calling ' + fn + '...');
		if (this.settings[fn] instanceof Function) {
			return this.settings[fn](arg1, arg2);
		} else {
			throw fn + " function not defined in settings!";
		}
	}

	// sets the settings by overwriting any defaults with the user defined
	// throws an error if required settings aren't set
	this._setSettings = function(settings) {
		if (!settings ||
			!settings.bundle ||
			!settings.entity ||
			!settings.table ||
			!settings.columns ) {
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
				var url = self._generateUrl('delete', button.attributes['data-id'].value);
				self.sendRequest(url, null, function() {
					button.parentNode.parentNode.removeChild(button.parentNode);
				});
			},
			'edit': function() {
				console.log('editing');
			},
			'columns': []
		};

		for (key in defaults) {
			this.settings[key] = settings[key]?settings[key]:defaults[key];
		}
		console.log("Settings set...");
	}

	// finds each defined button in a table and adds a corresponding function to the onclick event
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

	this._getExisting = function(n) {
		this.sendRequest(this._generateUrl('get') , n, (function(self) {
			return function() {
				console.log(this.responseText);
				var data = JSON.parse(this.responseText);
				if (data.success) {
					for (var i = 0; row = data.data[i]; i++) {
						self._addRow(row)
					}
				} else {
					self.failure(data.message);
				}
			}
		})(this));
		console.log("Retrieving existing rows...")
	}

	// sets it all up
	this._init = function(settings) {
		this._setSettings(settings);
		this._registerButtons();
		this._getExisting();
	}
	this._init(settings);
}