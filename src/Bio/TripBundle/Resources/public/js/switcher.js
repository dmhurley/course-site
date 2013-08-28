(function() {
	window.addEventListener('load', function() {
		var formContainer = document.getElementById('formContainer');
		var globalContainer = document.getElementById('globalContainer');
		var formButton = document.getElementById('formButton');
		varglobalButton = document.getElementById('globalButton');

		formButton.addEventListener('click', function() {
			formContainer.style.display="block";
			globalContainer.style.display="none";
		});

		globalButton.addEventListener('click', function() {
			formContainer.style.display="none";
			globalContainer.style.display="block";
		});
	});
})();