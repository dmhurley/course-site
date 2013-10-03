function Loader(settings) {
/********* PUBLIC VARIABLES *********/
	this.settings = {};
	this.buttons = {};
	this.parser = null;


/********** PUBLIC FUNCTION **********/
	this.sendRequest = function(url, post, onload) {
		ajax = new XMLHttpRequest();
		console.log("sending request to: " + url);
		ajax.open('POST', url, true);

		ajax.onload = onload;

		ajax.onerror = ajax.onabort = (function(self) {
			return function() {
				self.failure('Error.');
			}
		})(this);

		ajax.send(post);
	}

	this.postForm = function(url, form, onload) {
		data = new FormData(form);
		this.sendRequest(url, data, (function(self, form, fn) {
			return function() {
				fn(this);
			}
		})(this, form, onload));
	}

	this.success = function(message) {
		console.log(message);
	}

	this.failure = function(message) {
		console.log(message);
	}
/************* PRIVATE FUNCTION ***************/
	this._handleErrors = function(form, event) {
		console.log("handled errors...");
	}	

	this._addRow = function(data) {
		var row = this.settings.table.querySelector('tbody').insertRow();

		var keys = Object.keys(this.settings.buttons);
		for (key in keys) {
			settings = this.settings.buttons[keys[key]];
			if (!settings.unique) {
				var cell = row.insertCell();
				cell.classList.add('link');
				cell.classList.add(keys[key]);
				cell.innerHTML = keys[key];
				cell.id = data.id;
				cell.addEventListener(settings.event?settings.event:'click', (function(button, self, fn) {
					return function(event) {
						eval("("+self.parser.parse(fn.toString(), cell)+")")(event, button, self);
					}
				})(cell, this, settings.fn));
			}
		}

		for(var i = this.settings.columns.length - 1; header = this.settings.columns[i]; i--) {
			row.insertCell().innerHTML = data[header] !== undefined?data[header]:header
		}
	}

	this.generateUrl = function(action, id) {
		var url = this.settings.url + 
			   this.settings.bundle + '/' + 
			   this.settings.entity + '/' + action + (id?('/' + id):'');
		return url;
	}

	// sets the settings by overwriting any defaults with the user defined
	// throws an error if required settings aren't set
	this._setSettings = function(settings) {
		if (!settings ||
			!settings.url ||
			!settings.bundle ||
			!settings.entity ||
			!settings.table ||
			!settings.buttons ) {
			throw "Required settings not set.";
		}

		var defaults = {
			'url': '',
			'space': 'bio',
			'bundle': '',
			'entity': '',
			'buttons': {},
			'table': null,
			'columns': []
		};

		for (key in defaults) {
			this.settings[key] = settings[key]?settings[key]:defaults[key];
		}
		for (key in settings) {
			if (this.settings[key] === undefined) {
				this.settings[key] = settings[key];
			}
		}
		console.log("Settings set...");
	}

	// finds each defined button in a table and adds a corresponding function to the onclick event
	this._registerUniques = function() {
		var keys = Object.keys(this.settings.buttons);

		for (key in keys) {
			var settings = this.settings.buttons[keys[key]];
			if (settings.unique) {
				var unique = document.querySelector(settings.selector);
				unique.addEventListener(settings.event?settings.event:'click', (function(button, self, fn) {
					return function(event) {
						eval("("+self.parser.parse(fn.toString(), button)+")")(event, button, self);
					}
				})(unique, this, settings.fn));
			}
		}
		console.log("Registered uniques...")
	}

	this._getExisting = function(n) {
		console.log("Retrieving existing rows...");
		this.sendRequest(this.generateUrl('get') , n, (function(self) {
			return function() {
				var data = JSON.parse(this.responseText);
				if (data.success) {
					for (var i = 0; row = data.data[i]; i++) {
						self._addRow(row)
					}
					console.log("Displayed existing results...");
				} else {
					self.failure(data.message);
				}
			}
		})(this));
	}

	// sets it all up
	this._init = function(settings) {
		this.parser = new Parser('#{', '}');
		this._setSettings(settings);
		this._registerUniques();
		this._getExisting();
	}
	this._init(settings);
}