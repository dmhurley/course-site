function init(time) {
	date = new Date(time);
	updatePage();
	var interval = setInterval(updatePage, 15000);
}

function updatePage() {
	var now = new Date();
	var diff = date.valueOf() - now.valueOf();
	document.getElementById('time').innerHTML = msToText(diff);
	if (diff < 0) {
		clearInterval(interval);
		document.getElementById('hidden').style.display="block";
	}
}

function msToText(ms) {
	if (ms <= 0) {
		return "now";
	}

	var hours = ms/1000/60/60;
	ms = ms%(1000*60*60);
	var minutes = ms/1000/60;

	var string = "in approximately ";
	if (Math.floor(hours) > 1) {
		string+=Math.floor(hours) + " hours, and ";
	} else if (Math.floor(hours) === 1) {
		string+="1 hour, and ";
	}
	
	if (Math.floor(minutes) !== 1) {
		string+=Math.floor(minutes) + " minutes";
	} else {
		string+="1 minute";
	}

	return string;
}