(function() {
			function DragDrop(mce) {
				this.tinyMCE = mce;
				this.timeout = null;
				this.dropzones = [];
				this.handleFileSelect = function(evt) {
					evt.stopPropagation();
					evt.preventDefault();
					file = evt.dataTransfer.files[0];
					if (file && file.type.indexOf('image') === 0) {
						var reader = new FileReader();
						reader.onloadend = function() {

							var canvas = document.createElement("canvas");
							var ctx = canvas.getContext("2d");

							var img = new Image();
							var dataUrl = '';

							img.onload = function() {
								canvas.width = img.width;
								canvas.height = img.height;
								ctx.drawImage(img, 0,0);

								if (img.width > 800) {
									canvas.width = 800;
									canvas.height = img.height/img.width*800;
									ctx.drawImage(img, 0, 0, img.width, img.height, 0, 0, canvas.width, canvas.height);
									dataUrl = canvas.toDataURL();
								} else {
									canvas.width = img.width;
									canvas.height = img.height;
									ctx.drawImage(img, 0,0);
									dataUrl = canvas.toDataURL();
								}
								evt.target.editor.insertContent('<img src="'+dataUrl+'" />');
							}
							img.src = reader.result;
						}
						reader.readAsDataURL(file);
					} else {
						evt.target.editor.insertContent('<p>'+evt.dataTransfer.getData('Text')+'</p>');
					}
					clearTimeout(this.timeout);
				}

				this.handleDragOver = function(e) {
					e.stopPropagation();
					e.preventDefault();
					
					for(var i = 0; i < this.dropzones.length; i++) {
						this.dropzones[i].style.display = 'block';

					}

					clearTimeout(this.timeout);
					this.timeout = setTimeout((function(self) {
						return function() {
							for (var i = 0; i < self.dropzones.length; i++) {
								self.dropzones[i].style.display = 'none';
							}
						}
					})(this), 100);
				}

				this.init = function() {
					for (var i = 0; i < this.tinyMCE.editors.length/2; i++) {
						var editor = this.tinyMCE.get(i);
						var dropzone = editor.getContainer();
						var div = document.createElement('div');
							div.classList.add('dropzone');
							div.editor = editor;
							div.addEventListener('dragover', function(e) {
								e.preventDefault();
							});
							div.addEventListener('dragenter', function(e) {
								e.preventDefault();
							});
							div.addEventListener('drop', this.handleFileSelect);
							this.dropzones[this.dropzones.length] = div;
						dropzone.appendChild(div);
					}

					window.addEventListener('drop', function(e) {
						e.preventDefault();
					});

					document.body.addEventListener('dragover', (function(self) {
						return function(e) {
							self.handleDragOver(e);
						}
					})(this));
				}
				this.init();
			}
			window.addEventListener('load', function() {
				new DragDrop(tinyMCE);
			});
		})();