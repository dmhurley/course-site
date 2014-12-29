window.addEventListener('load', function() {
	questions = document.getElementsByClassName('question');
	input = document.getElementById("tag");

	input.addEventListener('keyup', function() {
		var search = input.value.toLowerCase().split(" ");
		for (var i = 0; i < questions.length; i++) {
			question = questions[i];
			var tags = question.attributes.tags.value.toLowerCase();
			var innerHTML = question.getElementsByTagName('label')[0].innerHTML.toLowerCase(); //
			hasTags = true;
			for (var j = 0; j < search.length; j++) {
				if (tags.indexOf(search[j]) === -1 && innerHTML.indexOf(search[j]) === -1) { //
					hasTags = false;
					break;
				}
			}

			if (hasTags) {
				question.style.display = 'table-row';
			} else {
				question.style.display = 'none';
			}
		}
	});
});