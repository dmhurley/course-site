function Loader(settings) {
/********* VARIABLES *********/
	this.settings = {};
	this.buttons = {};
	this.parser = null;

/********** PUBLIC FUNCTIONS **********/
	
	/**
	 * @param url the url to send the request to
	 * @param post the optional post data
	 * @callback onload
	 * 		@this the ajax response
	 *		@event the event
	 * calls this.failure('Error.') on error or abort
	 */
	this.sendRequest = function(url, post, onload) {
		ajax = new XMLHttpRequest();
		console.log("sending request to: " + url);
		ajax.open('POST', url, true);

		ajax.onload = onload;

		ajax.onerror = ajax.onabort = (function(self) {
			return function() {
				// console.log(this);
				self.failure('Error.');
			}
		})(this);

		ajax.send(post);
	}

	/* @param url the url to send the request to
	 * @param form the form object to be sent
	 * @callback onload
	 *		@param the ajax response
	 */
	this.postForm = function(url, form, onload) {
		data = new FormData(form);
		this._clearErrors(form);
		this.sendRequest(url?url:form.action, data, (function(self, form, fn) {
			return function() {
				a = this;
				self._handleErrors(form, this);
				fn(this);
			}
		})(this, form, onload));
	}

	/*
	 * generates a url in the format url/bundle/entity/action(/id)
	 * @param action
	 * @param id optional variable
	 */
	this.generateUrl = function(action, id) {
		var url = this.settings.url + 
			   this.settings.bundle + '/' + 
			   this.settings.entity + '/' + 
			   action + (id?('/' + id):'')+
			   '.json' ;
		return url;
	}

	this.show = function() {
		this.settings.loader.classList.add('shown');
	}

	this.hide = function() {
		this.settings.loader.classList.remove('shown');
	}

	this.success = function(message) {
		this.settings.loader.classList.remove('failure');
		this.settings.loader.classList.add('success');
		this.settings.loader.innerHTML = message;
		this.show();

		timeout = window.setTimeout((function(self) {
			return function() {
				self.hide();
			}
		})(this), 5000);
	}

	this.failure = function(message) {
		this.settings.loader.classList.remove('success');
		this.settings.loader.classList.add('failure');
		this.settings.loader.innerHTML = message;
		this.show();

		timeout = window.setTimeout((function(self) {
			return function() {
				self.hide();
			}
		})(this), 5000);
	}

	this.wait = function(timeout) {
		this.settings.loader.classList.add('loading');
		this.settings.loader.innerHTML = "Loading...";
		this.show();
	}

	this.ready = function() {
		this.hide();
		this.settings.loader.classList.remove('loading');
	}
/************* PRIVATE FUNCTIONS ***************/
	this._handleErrors = function(form, ajax) {
		var data = JSON.parse(ajax.responseText);
		if (!data.success && data.errors) {
			for (field in data.errors) {
				var input = document.querySelector('#form_'+field+'');
				if (input.tagName !== 'div') {
					input = input.parentElement;
				}
				input.setAttribute('data-tip', data.errors[field][0]);
				input.classList.add('error');
				input.classList.add('row_error');
			}
		}
	}
	this._clearErrors = function(form) {
		var errored = form.querySelectorAll('.error.row_error');
		for (var i = 0; element = errored[i]; i++) {
			element.classList.remove('error');
			element.classList.remove('form_error');
			element.setAttribute('data-tip', null);
		}
	}	

	this._addRow = function(data) {
		var row = this.settings.table.querySelector('tbody').insertRow();
		row.id = data.id;

		var keys = Object.keys(this.settings.buttons).reverse();
		for (key in keys) {
			settings = this.settings.buttons[keys[key]];
			var cell = row.insertCell();
			cell.classList.add('link');
			cell.classList.add(keys[key]);
			cell.innerHTML = keys[key];
			cell.addEventListener(settings.event?settings.event:'click', (function(button, self, fn) {
				return function(event) {
					fn(event, button, self);
				}
			})(cell, this, settings.fn));
		}

		var keys = Object.keys(this.settings.columns).reverse();
		console.log(keys);
		for (key in keys) {
			var fn = this.settings.columns[keys[key]];
			row.insertCell().innerHTML = data[keys[key]] === undefined? keys[key]: fn?fn(data[keys[key]]):data[keys[key]];
		}
	}

	// sets the settings by overwriting any defaults with the user defined
	// throws an error if required settings aren't set
	this._setSettings = function(settings) {
		if (
			!settings ||
			!settings.url ||
			!settings.bundle ||
			!settings.entity ||
			!settings.table 
		){
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
	this._registerListeners = function() {
		var keys = Object.keys(this.settings.listeners);

		for (key in keys) {
			var settings = this.settings.listeners[keys[key]];
			var listeners = document.querySelectorAll(settings.selector);
			for(var i = 0; listener = listeners[i]; i++) {
				listener.addEventListener(settings.event?settings.event:'click', (function(button, self, fn) {
					return function(event) {
						fn(event, button, self);
					}
				})(listener, this, settings.fn));
			}
		}
		console.log("Registered listeners...")
	}

	this._getExisting = function(n) {
		console.log("Retrieving existing rows...");
		this.sendRequest(this.generateUrl('all') , n, (function(self) {
			return function() {
				var data = JSON.parse(this.responseText);
				if (data.success) {
					for (var i = 0; row = data.data[i]; i++) {
						self._addRow(row)
					}
					console.log("Displayed existing results...");
					self.ready();
					self.success('Finished loading.');
				} else {
					self.failure(data.message);
				}
			}
		})(this));
	}

	// sets it all up
	this._init = function(settings) {
		this._setSettings(settings);
		this.wait();
		this.parser = new Parser('#{', '}');
		this._registerListeners();
		this._getExisting(); // calls self.ready()
	}
	this._init(settings);
}