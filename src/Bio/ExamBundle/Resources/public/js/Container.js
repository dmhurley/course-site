function Container(settings, parent) {
	var self = this;

	settings.listeners = settings.listeners || []; 	// required
	settings.elementType = settings.type || 'tr';
	settings.classes = settings.classes || ['entry'];
	settings.attributes = settings.attributes || [];
	settings.pass = settings.pass || [];
	settings.sortFn = settings.sortFn || function(a,b) {return false;};
	settings.text = settings.text || '';

	self.element = settings.element || document.createElement(settings.elementType);
	self.config = settings;
	self.parent = parent;
	self.children = [];
	self.data = settings.data || {}
	self.sortFn = settings.sortFn;

	self.element.innerHTML = settings.text;

	for(var i = 0, cls = null; cls = settings.classes[i]; i++) {
		self.element.classList.add(cls)
	}

	for (listener in settings.listeners) {
		self.element.addEventListener(listener, function(event) {
			self.config.pass.unshift(event)
			settings.listeners[listener].apply(self, self.config.pass);
			self.config.pass.shift();
		});
	}

	for (attribute in self.config.attributes) {
		self.element.setAttribute(attribute, self.config.attributes[attribute]);
	}

	this.addChild = function(child) {
		var i;
		var neighbor;

		for(i = 0; i < this.children.length; i++) {
			neighbor = this.children[i];
			if (this.sortFn(child, neighbor)) {
				break;
			}
		}

		this.addChildAt(child, i);
	}

	this.appendChild = function(child) {
		self.children.push(child);
		child.parent = self;
		self.element.appendChild(child.element);

		return this;
	}

	this.prependChild = function(child) {
		self.children.unshift(child);
		child.parent = self;
		self.element.insertBefore(child.element, self.element.firstChild);

		return this;
	}

	this.removeChild = function(child) {
		self.children.splice(self.children.indexOf(child), 1);
		child.parent = null;
		self.element.removeChild(child.element);

		// return child?
		return this;
	}

	this.addChildAt = function(child, index) {
		this.children.splice(index, 0, child);
		child.parent = self;
		this.element.insertBefore(child.element, this.element.children.item(index));

		return this;
	}

	this.removeSelf = function() {
		if (self.parent) {
			self.parent.removeChild(self);
		}
	}

	this.createChildren = function(data, context) {
		self.config.createChildren.call(self, data, context);
		return this;
	}

	this.createChild = function(data, context) {
		self.createChildren([data], context);
		return this;
	}
}