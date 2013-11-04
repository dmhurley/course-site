function Loader(settings) {
/********* VARIABLES *********/
	this.settings = {};
/********** UTILITIES **************/
	this.forms = {
		'self': null,
		'data': {
			'open': false,
			'type': null,
			'form': null,
			'container': null,
			'settings': null
		},

		'open': function(type) {
			if (this.data.open) {
				this.close();
			}
			if (!this.self.settings.form.settings[type]) {
				throw "Form type: " + type + ' not defined in settings.';
			} else {
				this.data.open = true;
				this.data.type = type;
				this.data.form = this.self.settings.form.form;
				this.data.container = this.self.settings.form.container;
				this.data.settings = this.self.settings.form.settings[type];
			}

			this.data.form.reset();
			this.self._clearErrors(this.data.form);
			this.data.settings.before.call(this.data.form, this.data.container, this.self);

			if (this.data.open) {
				if (this.data.container) {
					document.body.classList.add('noscroll');
					this.data.container.classList.add('shown');
				}

				var self = this;
				this.data.form.onsubmit = function(event) {
					self.data.settings.onsubmit.call(self.data.form, event, container, self.self);
				};
			}
		},

		'close': function() {
			document.body.classList.remove('noscroll');
			this.data.container.classList.remove('shown');
			this.data.settings.after.call(this.data.form, this.data.container, this.self);
			this.data.open = false;
			this.data.type = this.data.settings = null;
		}
	}

	this.parser = null;
	this.notify = null; 
	this.lister = null;

	// listener stuff
	this._listeners = {};
	this.addEventListener = function(type, fn) {
		if (!this._listeners[type]) {
			this._listeners[type] = [];
		}
		this._listeners[type].push(fn);
	}

	this.dispatchEvent = function(event) {
		if (this._listeners[event.type]) {
			for(var i = 0, fn = null; fn = this._listeners[event.type][i]; i++) {
				fn.call(this, event);
			}
			return true;
		}
		return false;
	}

/********** PUBLIC FUNCTIONS **********/
	
	this.sendRequest = function(url, post, onload, isForm) {
		var self = this;
		var ajax = new XMLHttpRequest();

		console.log("sending request to: " + url);

		ajax.open(post?'POST':'GET', url, true);
		ajax.timeout = 10000;

		ajax.onload = function(event) {
				var json = {'success': false, 'message': 'Error.', 'data': [], 'form': []};
				if (this.status !== 200) {
					json.message= this.statusText;
				} else {
					try {
						json = JSON.parse(this.responseText);
					} catch (e) {};
				}
				onload.call(json, event, self);
			};

		ajax.ontimeout = function() {
				self.notify.failure('Operation timed out.');
			};
		ajax.onerror = function() {
				self.notify.failure('Error.');
			};

		if (post && !isForm) {
			ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		}

		ajax.send(post);
	}

	this.postForm = function(url, form, onload) {
		var data = new FormData(form);
		this._clearErrors(form);
		this.sendRequest(
			url?url:form.action,
		 	data,
		 	function(event, self) {
				if (this.form !== undefined) {
					self._handleForm(this.form);
				}
				onload.call(this, event, self);
			},
			true
		);
	}

	this.generateUrl = function(action, id) {
		var url = this.settings.url + 
			   this.settings.bundle + '/' + 
			   this.settings.entity + '/' + 
			   action + (id?('/' + id):'')+
			   '.json' ;
		return url;
	}

	this.createObject = function(data) {
		return this.settings.table.createFn.call(this, data);
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
						if (box) {
							box.setContent(field.value, {format: 'raw'});
						}
					} else if (typeof field.value === 'object' && field.value) {
						for(var j = 0, option = null; option = field.value[j]; j++) {
							element.options.namedItem(option).selected = true;
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
			element.classList.remove('row_error');
			element.removeAttribute('data-tip');
		}
	}

	// sets the settings by overwriting any defaults with the user defined
	// throws an error if required settings aren't set
	this._setSettings = function(settings, defaults) {
		for (key in settings) {
			if (defaults[key] !== undefined && settings[key] === undefined) {
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
			var listeners = settings.selector===null?[this]:document.querySelectorAll(settings.selector);
			for(var i = 0; listener = listeners[i]; i++) {
				listener.addEventListener(settings.event?settings.event:'click', (function(button, self, fn) {
					return function(event) {
						fn(event, button, self);
					}
				})(listener, this, settings.fn));
			}
		}
		console.log("Registered listeners...");
	}

	this._getExisting = function(n) {
		console.log("Retrieving existing rows...");
		this.sendRequest(this.generateUrl('all') , n, function(event, self) {
				if (this.success) {
					for (var i = 0; row = this.data[i]; i++) {
						self.lister.add(self.createObject(row));
					}
					console.log("Displayed existing results...");
					self.notify.success('Finished loading.');
				} else {
					self.notify.failure(this.message);
				}
			});
	}

	// sets it all up
	this._init = function(settings) {
		this.settings = this._setSettings(settings, this.defaults);
		console.log('set settings...');

		this.forms.self = this;
		this.notify = new Notify(this.settings.loader);
		this.notify.wait();

		this.parser = new Parser('#{', '}');

		if (this.settings.table) {
			this.lister = new Lister(
				this.settings.table.element.querySelector('tbody'),
				this.settings.table.sortFn
			);
		}

		this.forms.data.form = this.settings.form.form;
		this.forms.data.container = this.settings.form.container;


		this._registerListeners();

		var event = new Event('init');
		event.initEvent('init', false, true);
		this.dispatchEvent(event);

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
			'createFn': function(data) {
				var self = this;

				var row = document.createElement('tr');
				row.data = data;
				row.id = data.id;
				for(button in self.settings.columns) {
					var fn = self.settings.columns[button];
					var cell = row.insertCell(-1);
					var value = data[button] === undefined?fn?fn(null, cell):button: fn?fn(data[button], cell, self.parser):data[button];
					if (value === undefined) {
						cell.parentNode.removeChild(cell);
					} else {
						cell.innerHTML = value;
					}
				}

				for (button in self.settings.buttons) {
					settings = self.settings.buttons[button];
					var cell = row.insertCell(-1);
					cell.classList.add('link');
					cell.classList.add(button);
					cell.innerHTML = button;
					cell.addEventListener(settings.event?settings.event:'click', (function(fn) {
						return function(event) {
							fn.call(cell, event, self);
						}
					})(settings.fn));
				}

				return row;
			},
			'sortFn': function(dataA,dataB) {
				return false;
			}
		},
		'buttons': {
			'edit': {
				'fn': function(event, self) {
					self.forms.data.form.setAttribute('data-id', this.parentNode.id);
					self.forms.open('edit');
				}
			},
			'delete': {
				'fn': function(event, self) {
					self.notify.wait();
					var url = self.generateUrl('delete', this.parentNode.id);

					var button = this;
					self.sendRequest(url, null, function(event, self) {
						if (this.success) {
							self.lister.remove(button.parentNode);
							self.notify.success('Deleted ' + self.settings.entity + '.');
						} else {
							self.notify.failure(this.message);
						}
					});
				}
			}
		},
		'listeners': [
			{
				'selector': '.link.add',
				'fn': function(event, object, self) {
					self.forms.open('add');
				}
			},
			{
				'selector': '.form_layer, .form_exit',
				'fn': function(event, object, self) {
					if (event.target === object)
					self.forms.close();
				}
			},
			{
				'selector': null,
				'event': 'init',
				'fn': function(event, object, self) {
					self._getExisting();
				}
			}
		],
		'loader': document.querySelector('div.notification'),
		'form': {
			'container': document.querySelector('.form_layer'),
			'form': document.querySelector('.form_container form'),
			'settings': {
				'add': {
					'before': function(container, self) {
						this.action = self.generateUrl('create');
						this.classList.add('add');
					},
					'onsubmit': function(event, container, self) {
						event.preventDefault();
						self.notify.wait();
						container.classList.remove('shown');
						self.postForm(null, this, function(event, self) {
							if (this.success) {
								self.lister.add(self.createObject(this.data[0]));
								self.notify.success('Created ' +self.settings.entity+'.');
								self.forms.close();
							} else {
								self.notify.failure(this.message);
								container.classList.add('shown');
							}
						});
					},
					'after': function(container, self) {
						this.action = "";
						this.classList.remove('add');
					}
				},
				'edit': {
					'before': function(container, self) {
						this.classList.add('edit');
						this.action = self.generateUrl('edit', this.getAttribute('data-id'));

						self.notify.wait();
						self.sendRequest(self.generateUrl('get', this.getAttribute('data-id')), null, function(event, self) {
							if (this.success) {
								self.notify.ready();
								self._handleForm(this.form);
							} else {
								self.notify.failure(this.message);
								self.forms.close();
							}
						});
					},
					'onsubmit': function(event, container, self) {
						event.preventDefault();
						self.notify.wait();
						container.classList.remove('shown');

						var form = this;
						self.postForm(null, this, function(event, self) {
							if (this.success) {
								var row = document.getElementById(form.getAttribute('data-id'));
								self.lister.replace(row, self.createObject(this.data[0]));
								self.notify.success('Edited '+self.settings.entity+'.');
								self.forms.close();
							} else {
								self.notify.failure(this.message);
								container.classList.add('shown');
							}
						});

					},
					'after': function(container, self) {
						this.classList.remove('edit');
						this.action = "";
						this.removeAttribute('data-id');
					}
				}
			}
		}
	}

	this._init(settings);
}