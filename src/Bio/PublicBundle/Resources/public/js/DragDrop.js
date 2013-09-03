(function() {
			function DragDrop(mce) {
				this.tinyMCE = mce;
				this.timeout = null;
				this.dropzones = [];
				this.handleFileSelect = function(evt) {
					evt.stopPropagation();
					evt.preventDefault();
					var reader = new FileReader();
					reader.onload = function() {
						evt.target.editor.insertContent('<img src="'+this.result+'" />');
					}
					reader.readAsDataURL(evt.dataTransfer.files[0]);
					hideDropZones();
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