function Notify(object) {
	this.object = object;
	this.timeout = null;

	this.wait = function() {
		var loader = this._clear();
		this.object.classList.add('loading');
		this.object.innerHTML = "Loading...";
		this._show();
	},
	this.ready = function() {
		this._hide();
		this._clear();
	},
	this.success = function(message) {
		var loader = this._clear();
		loader.classList.add('success');
		loader.innerHTML = message?message:'Success.';
		this._show();

		this._addTimeout(5000);
	},
	this.failure = function(message) {
		var loader = this._clear();
		loader.classList.add('failure');
		loader.innerHTML = message?message:'Error.';
		this._show();

		this._addTimeout(5000);
	},
	this._addTimeout = function(n) {
		var self = this;
		this.timeout = window.setTimeout(function() {
			self._hide();
		}, n);
	}
	this._show = function() {
		this.object.classList.add('shown');
	},
	this._hide = function() {
		this.object.classList.remove('shown');
	},
	this._clear = function() {
		var loader = this.object;
		loader.classList.remove('success');
		loader.classList.remove('failure');
		loader.classList.remove('loading');
		window.clearTimeout(this.timeout);

		return loader;
	}
}