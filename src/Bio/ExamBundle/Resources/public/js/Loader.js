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
			document.body.classList.add('noscroll');
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
			document.body.classList.remove('noscroll');
			this.form.data.settings[this.form.type].after(this.form.data.form, this.form.data.container, this.self);
			this.form.type = this.form.data = null;

		}
	}

	this.parser = null;

	this.rows = {
		'self': null,
		'rows': [],
		'create': function(data) {
			var row = document.createElement('tr');
			row.data = data;
			row.id = data.id;
			for(button in this.self.settings.columns) {
				var fn = this.self.settings.columns[button];
				var cell = row.insertCell(-1);
				var value = data[button] === undefined?fn?fn(null, cell):button: fn?fn(data[button], cell):data[button];
				if (value === undefined) {
					cell.parentNode.removeChild(cell);
				} else {
					cell.innerHTML = value;
				}
			}

			for (button in this.self.settings.buttons) {
				settings = this.self.settings.buttons[button];
				var cell = row.insertCell(-1);
				cell.classList.add('link');
				cell.classList.add(button);
				cell.innerHTML = button;
				cell.addEventListener(settings.event?settings.event:'click', (function(cell, self, fn) {
					return function(event) {
						fn(event, cell, self);
					}
				})(cell, this.self, settings.fn));
			}

			this.add(row);
		},
		'replace': function(oldRow, newRow) {
			this.remove(oldRow);
			this.add(newRow);
		},
		'add': function(row) {
			var i;
			var neighbor;
			console.log(row);
			for(i = 0; i < this.rows.length; i++) {
				neighbor = this.rows[i];
				if (this.self.settings.table.sortFn(neighbor.data, row.data)) {
					break;
				}
			}
			this.rows.splice(i, 0, row);
			this.self.settings.table.element.querySelector('tbody').insertBefore(row, this.self.settings.table.element.querySelector('tbody').children.item(i));
		},
		'remove': function(row) {
			var index = this.rows.indexOf(row);
			if (index > -1) {
				this.rows.splice(index, 1)
			}
			row.parentNode.removeChild(row);
		}
	}

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
					if ('tinyMCE' in window && element.classList.contains('tinymce')) {
						var box = tinyMCE.get(field.id);
						console.log(field.id);
						if (box) {
							box.setContent(field.value, {format: 'raw'});
						}
					} else {
						element.value = field.value;
					}
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
	this._setSettings = function(settings, defaults) {
		for (key in settings) {
			if (defaults[key] && settings[key] === undefined) {
				delete defaults[key];
			} else if (settings[key].constructor === Object && defaults[key]) {
				defaults[key] = this._setSettings(settings[key], defaults[key]);
			} else {
				defaults[key] = settings[key];
			}
		}
		return defaults;
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
						self.rows.create(row)
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
		this.settings = this._setSettings(settings, this.defaults);

		this.notifications.self = this.forms.self = this.rows.self = this;

		this.notifications.wait();
		this.parser = new Parser('#{', '}');
		this._registerListeners();
		this._getExisting(); // calls self.ready()
	}

/*******************************************************************************************************
											DEFAULTS
*******************************************************************************************************/
	this.defaults = {
		'url': '',
		'bundle': '',
		'entity': '',
		'table': {
			'element': document.querySelector('table'),
			'sortFn': function(dataA,dataB) {
				return false;
			}
		},
		'buttons': {
			'edit': {
				'fn': function(event, button, self) {
					self.forms.switch('edit');
					self.forms.form.data.form.setAttribute('data-id', button.parentNode.id);
					self.forms.open();
				}
			},
			'delete': {
				'fn': function(event, button, self) {
					self.notifications.wait();
					var url = self.generateUrl('delete', button.parentNode.id);
					self.sendRequest(url, null, function() {
						var data = JSON.parse(this.responseText);
						if (data.success) {
							self.rows.remove(button.parentNode);
							self.notifications.ready();
							self.notifications.success('Deleted ' + self.settings.entity + '.');
						} else {
							self.notifications.ready();
							self.notifications.failure(data.message);
						}
					});
				}
			}
		},
		'listeners': [
			{
				'selector': '.link.add',
				'fn': function(event, object, self) {
					self.forms.switch('add');
					self.forms.open();
				}
			},
			{
				'selector': '.form_layer, .form_exit',
				'fn': function(event, object, self) {
					if (event.target === object)
					self.forms.close();
				}
			}
		],
		'loader': document.querySelector('div.notification'),
		'form': {
			'container': document.querySelector('.form_layer'),
			'form': document.querySelector('.form_container form'),
			'settings': {
				'add': {
					'before': function(form, container, self) {
						form.action = self.generateUrl('create');
						form.classList.add('add');
						container.classList.add('shown');
					},
					'onsubmit': function(event, form, container, self) {
						event.preventDefault();
						self.notifications.wait();
						container.classList.remove('shown');
						self.postForm(null, form, function(ajax) {
							var data = JSON.parse(ajax.responseText);
							self.notifications.ready();
							if (data.success) {
								self.rows.create(data.data[0]);
								self.notifications.success('Created ' +self.settings.entity+'.');
								self.forms.close();
							} else {
								self.notifications.failure(data.message);
								container.classList.add('shown');
							}
						});
					},
					'after': function(form, container, self) {
						container.classList.remove('shown');
						form.action = "";
						form.classList.remove('add');
					}
				},
				'edit': {
					'before': function(form, container, self) {
						form.classList.add('edit');
						form.action = self.generateUrl('edit', form.getAttribute('data-id'));

						self.notifications.wait();
						self.sendRequest(self.generateUrl('get', form.getAttribute('data-id')), null, (function(self, container) {
							return function() {
								var data = JSON.parse(this.responseText);
								self.notifications.ready();
								if (data.success) {
									self._handleForm(data.form);
									container.classList.add('shown');
								} else {
									self.notification.failure(data.message);
									self.forms.close();
								}
							}
						})(self, container));
					},
					'onsubmit': function(event, form, container, self) {
						event.preventDefault();
						self.notifications.wait();
						container.classList.remove('shown');
						self.postForm(null, form, function(ajax) {
							var data = JSON.parse(ajax.responseText);
							self.notifications.ready();
							if (data.success) {
								var row = document.getElementById(form.getAttribute('data-id'));
								self.rows.remove(row);
								self.rows.create(data.data[0]);
								self.notifications.success('Edited '+self.settings.entity+'.');
								self.forms.close();
							} else {
								self.notifications.failure(data.message);
								container.classList.add('shown');
							}
						});

					},
					'after': function(form, container, self) {
						container.classList.remove('shown');
						form.classList.remove('edit');
						form.action = "";
						form.removeAttribute('data-id');
					}
				}
			}
		}
	}

	this._init(settings);
}