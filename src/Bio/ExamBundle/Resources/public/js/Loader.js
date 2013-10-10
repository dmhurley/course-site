function Loader(settings) {
/********* VARIABLES *********/
	this.settings = {};
/********** UTILITIES **************/
	this.notifications = {
		'self': null,
		'show': function() {
			this.self.settings.loader.classList.add('shown');
		},
		'hide': function() {
			this.self.settings.loader.classList.remove('shown');
		},
		'success': function(message) {
			this.self.settings.loader.classList.remove('failure');
			this.self.settings.loader.classList.add('success');
			this.self.settings.loader.innerHTML = message?message:'Success.';
			this.self.notifications.show();

			_timeout = window.setTimeout((function(self) {
				return function() {
					self.hide();
				}
			})(this), 5000);
		},
		'failure': function(message) {
			this.self.settings.loader.classList.remove('success');
			this.self.settings.loader.classList.add('failure');
			this.self.settings.loader.innerHTML = message?message:'Error.';
			this.self.notifications.show();

			_timeout = window.setTimeout((function(self) {
				return function() {
					self.hide();
				}
			})(this), 5000);
		},
		'wait': function() {
			this.self.settings.loader.classList.add('loading');
			this.self.settings.loader.innerHTML = "Loading...";
			if ('_timeout' in window) {
				window.clearTimeout(_timeout);
			}
			this.show();
		},
		'ready': function() {
			this.hide();
			this.self.settings.loader.classList.remove('loading');
		}
	}

	this.forms = {
		'self': null,
		'form': {
			'type': null,
			'data': null
		},
		'switch': function(type) {
			if (this.form.data !== null) {
				this.close();
			} else if (this.self.settings.form.settings[type]) {
				this.form.type = type;
				this.form.data = this.self.settings.form;
			} else {
				throw "Form type is not set in settings.";
			}
		},
		'open': function() {
			if (this.form.data === null) {
				throw "You must switch to a form first.";
			}
			this.form.data.form.reset();
			this.self._clearErrors(this.form.data.form);
			this.form.data.settings[this.form.type].before(this.form.data.form, this.form.data.container, this.self);
			this.form.data.form.onsubmit = (function(form, container, self) {
				return function(event) {
					self.forms.form.data.settings[self.forms.form.type].onsubmit(event, form, container, self);
				}
			})(this.form.data.form, this.form.data.container, this.self);
		},
		'close': function() {
			this.form.data.settings[this.form.type].after(this.form.data.form, this.form.data.container, this.self);
			this.form.type = this.form.data = null;

		}
	}

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
		ajax.timeout = 10000;
		ajax.onload = onload;
		ajax.ontimeout = (function(self) {
			return function() {
				self.notifications.failure('Operation timed out.');
			}
		})(this);
		ajax.onerror = ajax.onabort = (function(self) {
			return function() {
				self.notifications.failure('Error.');
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
				var obj = JSON.parse(this.responseText);
				if (obj.form !== undefined) {
					self._handleForm(obj.form);
				}
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

	this.addRow = function(data) {
		var row = this.settings.table.querySelector('tbody').insertRow();
		row.id = data.id;
		for (button in this.settings.columns) {
			var fn = this.settings.columns[button];
			var cell = row.insertCell(-1);
			var value = data[button] === undefined? button: fn?fn(data[button], cell):data[button];
			if (value === false) {
				cell.parentNode.removeChild(cell);
			} else {
				cell.innerHTML = value;
			}
		}

		for (button in this.settings.buttons) {
			settings = this.settings.buttons[button];
			var cell = row.insertCell(-1);
			cell.classList.add('link');
			cell.classList.add(button);
			cell.innerHTML = button;
			cell.addEventListener(settings.event?settings.event:'click', (function(cell, self, fn) {
				return function(event) {
					fn(event, cell, self);
				}
			})(cell, this, settings.fn));
		}

	}
/************* PRIVATE FUNCTIONS ***************/

	this._handleForm = function(data) {
		for (var i = 0; field = data[i]; i++) {
			var element = document.getElementById(field.id);
			if (element) {
				if (field.errors.length > 0) {
					element.parentNode.setAttribute('data-tip', field.errors[0]);
					element.parentNode.classList.add('error');
					element.parentNode.classList.add('row_error');
				}

				if (field.children !== undefined) {
					this._handleForm(field.children);
				} else {
					element.value = field.value;
				}
			}
		}
	}

	this._clearErrors = function(form) {
		var errored = form.querySelectorAll('.error.row_error');
		for (var i = 0; element = errored[i]; i++) {
			element.classList.remove('error');
			element.classList.remove('form_error');
			element.removeAttribute('data-tip');
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
			'url': '',		// base crud url
			'space': 'bio', // for something..
			'bundle': '', // eg: exam
			'entity': '', // eg: question
			'buttons': {}, // buttons added to end of row
			'table': null, // allows for multiple tables
			'columns': []  // defines rows to output and any transformers needed..
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
						self.addRow(row)
					}
					console.log("Displayed existing results...");
					self.notifications.ready();
					self.notifications.success('Finished loading.');
				} else {
					self.notifications.failure(data.message);
				}
			}
		})(this));
	}

	// sets it all up
	this._init = function(settings) {
		this._setSettings(settings);
		this.notifications.self = this.forms.self = this;
		this.notifications.wait();
		this.parser = new Parser('#{', '}');
		this._registerListeners();
		this._getExisting(); // calls self.ready()
	}
	this._init(settings);
}