function Parser(start, end) {
	this.start = start;
	this.end = end;
	this.re = new RegExp(this.start+'(.*?)'+this.end, 'g');

	this.parse = function(string, entity) {
		while (results = this.re.exec(string)) {
			if (entity[results[1]] !== undefined) {
				string = string.replace(results[0], entity[results[1]]);
			}
		}
		return string;
	}
}