function Lister(parent ,sortFn) {
	this.parent = parent;
	this.sortFn = sortFn;

	this.add = function(child) {
		var i;
		var neighbor;

		for(i = 0; i < this.parent.children.length; i++) {
			neighbor = this.parent.children[i];
			if (this.sortFn(child, neighbor)) {
				break;
			}
		}

		this.parent.insertBefore(child, this.parent.children.item(i));
	}

	this.remove = function(child) {
		console.log(parent);
		console.log(parent.children);
		console.log(child);
		this.parent.removeChild(child);
	}

	this.replace = function(oldChild, newChild) {
		this.remove(oldChild);
		this.add(newChild);
	}

}