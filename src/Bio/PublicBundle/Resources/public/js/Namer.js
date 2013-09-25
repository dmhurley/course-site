(function() {
	window.addEventListener('load', function() {
		document.querySelectorAll('input[type=file]')[0].addEventListener('change', function() {

			this.parentNode.parentNode.children[1].children[1].value = clean(this.files[0].name);
		});
	});

	function clean(name) {
		return name.substr(0, name.lastIndexOf('.')).
		replace(/_/g , ' ');
	}
})()