function Loader(settings) {
/********* PUBLIC VARIABLES *********/
	this.settings = {};
	this.buttons = {};
	this.parser = null;


/********** PUBLIC FUNCTION **********/
	this.sendRequest = function(url, post, onload) {
		ajax = new XMLHttpRequest();
		console.log(url);
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
		this.sendRequest(url, form, (function(self, form) {
			return function(event) {
				self._handleErrors(form, event);
				onload(event);
			}
		})(this, form));
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
		row = this.settings.table.querySelector('tbody').insertRow();

		for (var i = this.settings.buttons.length - 1; button = this.settings.buttons[i]; i--) {
			var cell = row.insertCell();
			cell.classList.add('link');
			cell.classList.add(button);
			cell.innerHTML = button;
			cell.id = data.id;
			cell.addEventListener('click', (function(b, bn, s) {
						return function() {
							s._callFunction(bn, b, s);
						}
					})(cell, button, this));
			}

		for(var i = this.settings.columns.length - 1; header = this.settings.columns[i]; i--) {
			row.insertCell().innerHTML = data[header] !== undefined?data[header]:header
		}
	}

	this._generateUrl = function(action, id) {
		console.log(id);
		var url = this.settings.url + 
			   this.settings.bundle + '/' + 
			   this.settings.entity + '/' + action + (id?('/' + id):'');

		console.log('generated ' + url);
		return url;
	}

	// calls a function found in the settings matching fn
	// calls fn and passes two arguments arg1 and arg2
	this._callFunction = function(fn, arg1, arg2) {
		console.log('calling ' + fn + '...');
		if (this.settings[fn] instanceof Function) {

			return eval("(" + this.parser.parse(this.settings[fn].toString(), arg1) + ")")(arg1, arg2);
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
				var url = self._generateUrl('delete', button.id);
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
		for (key in settings) {
			if (this.settings[key] === undefined) {
				this.settings[key] = settings[key];
			}
		}
		console.log("Settings set...");
	}

	// finds each defined button in a table and adds a corresponding function to the onclick event
	this._registerForms = function() {
		var forms = document.querySelectorAll('form');
		for (var i = 0; form = forms[i]; i++) {
			form.addEventListener('submit', (function(self) {
				return function(event) {
					event.preventDefault();
					self.postForm(this.action, this, function(event) {console.log("SUCCESS")});
				}
			})(this));
		}
		console.log('Registered forms...');
	}

	this._getExisting = function(n) {
		console.log("Retrieving existing rows...");
		this.sendRequest(this._generateUrl('get') , n, (function(self) {
			return function() {
				console.log(this.responseText);
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
		this._registerForms();
		this._getExisting();
	}
	this._init(settings);
}