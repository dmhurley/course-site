(function() {
	window.addEventListener('load', function() {
		document.querySelectorAll('input[type=file]')[0].addEventListener('change', function() {
			this.parentNode.parentNode.children[1].children[1].value = this.files[0].name.substr(0, this.files[0].name.lastIndexOf('.'));
		});
	});
})()