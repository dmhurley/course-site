function Parser(start, end) {
	this.start = start || '#{';
	this.end = end || '}';
	this.re = new RegExp(this.start+'(.*?)'+this.end, 'g');

	this.parse = function(string, entity) {
		var results;
		var newString = string;
		while (results = this.re.exec(string)) {
			if (entity[results[1]] !== undefined) {
				newString = newString.replace(results[0], entity[results[1]]);
			}
		}
		return newString;
	}

	this.parseDate = function(string, date) {
		var entity = {
			'Y': date.getFullYear(),
			'y': date.getFullYear()%100,
			'M': ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][date.getMonth()],
			'm': date.getMonth() + 1,
			'd': date.getDate()<10?'0'+date.getDate():date.getDate(),
			'H': date.getHours(),
			'h': date.getHours()%12===0?12:date.getHours()%12,
			'a': date.getHours() >= 12?'pm':'am',
			'i': date.getMinutes()<10?'0'+date.getMinutes():date.getMinutes(),
			's': date.getSeconds()<10?'0'+date.getSeconds():date.getSeconds()
		}
		return this.parse(string, entity);
	}

	this.time = function(date) {
		date = date || new Date();
		return this.parseDate('#{h}:#{i} #{a}', date);
	}

	this.date = function(date) {
		date = date || new Date();
		return this.parseDate('#{M} #{d}, #{Y}', date);
	}

	this.datetime = function(date) {
		date = date || new Date();
		return this.date(date) + ' at ' + this.time(date);
	}

	this.test = function() {
		return this.parseDate('#{Y}-#{m}-#{d} #{h}:#{i}:#{s} #{a}', new Date());
	}
}