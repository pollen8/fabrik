var FbFileUpload = new Class({
	Extends : FbFileElement,
	initialize : function (element, options) {
		this.plugin = 'fileupload';
		this.parent(element, options);
		this.toppath = this.options.dir;
		if (this.options.folderSelect === 1 && this.options.editable === 1) {
			this.ajaxFolder();
		}
		
		this.submitEvent = function (form, json) {
			this.onSubmit(form);
		}.bind(this);
		
		Fabrik.addEvent('fabrik.form.submit.start', this.submitEvent);
		if (this.options.ajax_upload && this.options.editable !== false) {
			this.watchAjax();
			this.options.files = $H(this.options.files);
			if (this.options.files.getLength() !== 0) {
				this.uploader.trigger('FilesAdded', this.options.files);
				this.startbutton.addClass('plupload_disabled');
				this.options.files.each(function (file) {
					var response = {
						'filepath' : file.path,
						uri : file.url
					};
					this.uploader.trigger('UploadProgress', file);
					this.uploader.trigger('FileUploaded', file, {
						response : JSON.encode(response)
					});
					document.id(file.id).getElement('.plupload_file_status').set('text', '100%');
					document.id(file.id).getElement('.plupload_file_size').set('text', file.size);					
				}.bind(this));
				//this.uploader.trigger('Init'); //no as this creates a second div interface
				// hack to reposition the hidden input field over the 'ad' button
				var c = document.id(this.options.element + '_container');
				var diff = document.id(this.options.element + '_browseButton').getPosition().y - c.getPosition().y;
				c.getParent('.fabrikElement').getElement('input[type=file]').getParent().setStyle('top', diff);
			}
		}
	},

	/**
	 * when in ajax form, on submit the list will call this, so we can remove the submit event
	 * if we dont do that, upon a second form submission the original submitEvent is used causing a js error
	 * as it still references the files uploaded in the first form
	 */
	removeCustomEvents: function () {
		Fabrik.removeEvent('fabrik.form.submit.start', this.submitEvent);
	},
	
	cloned : function () {
		// replaced cloned image with default image
		if (typeOf(this.element.getParent('.fabrikElement')) === 'null') {
			return;
		}
		var i = this.element.getParent('.fabrikElement').getElement('img');
		if (i) {
			i.src = Fabrik.liveSite + this.options.defaultImage;
		}
	},

	decloned : function (groupid) {
		var f = document.id('form_' + this.form.id);
		var i = f.getElement('input[name=fabrik_deletedimages[' + groupid + ']');
		if (typeOf(i) === 'null') {
			new Element('input', {
				'type' : 'hidden',
				'name' : 'fabrik_fileupload_deletedfile[' + groupid + '][]',
				'value' : this.options.value
			}).inject(f);
		}
	},

	update : function (val) {
		if (this.element) {
			var i = this.element.getElement('img');
			if (typeOf(i) !== 'null') {
				i.src = val;
			}
		}
	},

	watchAjax : function () {
		if (this.options.editable === false) {
			return;
		}
		var c = this.getElement().getParent('.fabrikSubElementContainer');
		this.container = c;
		var canvas = c.getElement('canvas');
		if (typeOf(canvas) === 'null') {
			return;
		}
		this.widget = new ImageWidget(canvas, {
			'cropdim' : {
				w: this.options.cropwidth,
				h: this.options.cropheight,
				x: this.options.cropwidth / 2,
				y: this.options.cropheight / 2
			},
			crop: this.options.crop
		});
		this.pluploadContainer = c.getElement('.plupload_container');
		this.pluploadFallback = c.getElement('.plupload_fallback');
		this.droplist = c.getElement('.plupload_filelist');
		this.startbutton = c.getElement('.plupload_start');
		var plupopts = {
			runtimes: this.options.ajax_runtime,
			browse_button: this.element.id + '_browseButton',
			container: this.element.id + '_container',
			drop_element: this.element.id + '_dropList',
			url: 'index.php?option=com_fabrik&format=raw&task=plugin.pluginAjax&plugin=fileupload&method=ajax_upload&element_id=' + this.options.elid,
			max_file_size: this.options.max_file_size + 'kb',
			unique_names: false,
			flash_swf_url: '/plugins/element/fileupload/plupload/js/plupload.flash.swf',
			silverlight_xap_url: '/plugins/element/fileupload/plupload/js/plupload.silverlight.xap',
			chunk_size: this.options.ajax_chunk_size + 'kb',
			multipart: true
		};
		this.uploader = new plupload.Uploader(plupopts);

		// (1) INIT ACTIONS
		this.uploader.bind('Init', function (up, params) {
			// FORCEFULLY NUKE GRACEFUL DEGRADING FALLBACK ON INIT
			this.pluploadFallback.destroy();
			this.pluploadContainer.removeClass("fabrikHide");
		}.bind(this));

		this.uploader.bind('FilesRemoved', function (up, files) {
		});

		// (2) ON FILES ADDED ACTION
		this.uploader.bind('FilesAdded', function (up, files) {
			var txt = this.droplist.getElement('.plupload_droptext');
			if (typeOf(txt) !== 'null') {
				txt.destroy();
			}
			var count = this.droplist.getElements('li').length;
			this.startbutton.removeClass('plupload_disabled');
			files.each(function (file, idx) {
				if (count >= this.options.ajax_max) {
					alert(Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_MAX_UPLOAD_REACHED'));
				} else {
					count++;
					var del = new Element('div', {
						'class' : 'plupload_file_action'
					}).adopt(new Element('a', {
						'href' : '#',
						'style' : 'display:block',
						events : {
							'click' : this.pluploadRemoveFile.bindWithEvent(this)
						}
					}));
					var a = new Element('a', {
						'href' : '#',
						alt : Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_RESIZE'),
						events : {
							'click' : this.pluploadResize.bindWithEvent(this)
						}
					});
					if (this.options.crop) {
						a.set('html', this.options.resizeButton);
					} else {
						a.set('html', this.options.previewButton);
					}
					var filename = new Element('div', {
						'class' : 'plupload_file_name'
					}).adopt([ new Element('span').set('text', file.name), new Element('div', {
						'class' : 'plupload_resize',
						style : 'display:none'
					}).adopt(a) ]);
					var innerli = [ filename, del, new Element('div', {
						'class' : 'plupload_file_status'
					}).set('text', '0%'), new Element('div', {
						'class' : 'plupload_file_size'
					}).set('text', file.size), new Element('div', {
						'class' : 'plupload_clearer'
					}) ];
					this.droplist.adopt(new Element('li', {
						id : file.id,
						'class' : 'plupload_delete'
					}).adopt(innerli));
				}
			}.bind(this));
		}.bind(this));

		// (3) ON FILE UPLOAD PROGRESS ACTION
		this.uploader.bind('UploadProgress', function (up, file) {
			console.log('progress', up, file);
			var f = document.id(file.id);
			if (typeOf(f) !== 'null') {
				document.id(file.id).getElement('.plupload_file_status').set('text', file.percent + '%');
			}
		});

		this.uploader.bind('Error', function (up, err) {
			fconsole('Error:' + err);
		});

		this.uploader.bind('ChunkUploaded', function (up, file, response) {
			response = JSON.decode(response.response);
			if (typeOf(response) !== 'null') {
				if (response.error) {
					fconsole(response.error.message);
				}
			}
		});

		this.uploader.bind('FileUploaded', function (up, file, response) {
			response = JSON.decode(response.response);
			if (response.error) {
				alert(response.error);
				document.id(file.id).destroy();
				return;
			}
			var f = document.id(file.id);
			if (typeOf(f) === 'null') {
				console.log('Filuploaded didnt find: ' + file.id);
				return;
			}
			document.id(file.id).getElement('.plupload_resize').show();
			var resizebutton = document.id(file.id).getElement('.plupload_resize').getElement('a');
			resizebutton.href = response.uri;
			resizebutton.id = 'resizebutton_' + file.id;
			resizebutton.store('filepath', response.filepath);
			console.log('upload response, ', response);
			this.widget.setImage(response.uri, response.filepath, file.params);
			new Element('input', {
				'type' : 'hidden',
				name : this.options.elementName + '[crop][' + response.filepath + ']',
				'id' : 'coords_' + file.id,
				'value' : JSON.encode(file.params)
			}).inject(this.pluploadContainer, 'after');
			var idvalue = $pick(file.recordid, '0');
			new Element('input', {
				'type' : 'hidden',
				name : this.options.elementName + '[id][' + response.filepath + ']',
				'id' : 'id_' + file.id,
				'value' : idvalue
			}).inject(this.pluploadContainer, 'after');

			document.id(file.id).removeClass('plupload_file_action').addClass('plupload_done');
		}.bind(this));

		// (4) UPLOAD FILES FIRE STARTER
		c.getElement('.plupload_start').addEvent('click', function (e) {
			e.stop();
			this.uploader.start();
		}.bind(this));
		// (5) KICK-START PLUPLOAD
		this.uploader.init();
	},

	pluploadRemoveFile : function (e) {
		e.stop();
		var id = e.target.getParent().getParent().id.split('_').getLast();// alreadyuploaded_8_13
		var f = e.target.getParent().getParent().getElement('.plupload_file_name span').get('text');
		new Request({
			url: '',
			data: {
				'option': 'com_fabrik',
				'format': 'raw',
				'task': 'plugin.pluginAjax',
				'plugin': 'fileupload',
				'method': 'ajax_deleteFile',
				'element_id': this.options.id,
				'file': f,
				'recordid': id
			}
		}).send();
		var li = e.target.getParent('.plupload_delete');
		li.destroy();
		// remove hidden fields as well
		if (document.id('id_alreadyuploaded_' + this.options.id + '_' + id)) {
			document.id('id_alreadyuploaded_' + this.options.id + '_' + id).destroy();
		}
		if (document.id('coords_alreadyuploaded_' + this.options.id + '_' + id)) {
			document.id('coords_alreadyuploaded_' + this.options.id + '_' + id).destroy();
		}
		/*
		 * if (this.droplist.getChildren().length === 0) {
		 * this.startbutton.addClass('plupload_disabled'); this.droplist.adopt(new
		 * Element('li', { 'class' : 'plupload_droptext' }).set('text',
		 * Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_DRAG_FILES_HERE'))); }
		 */
	},

	pluploadResize : function (e) {
		e.stop();
		var a = e.target.getParent();
		this.widget.setImage(a.href, a.retrieve('filepath'));
	},

	onSubmit : function (form) {
		if (!this.allUploaded()) {
			alert(Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_UPLOAD_ALL_FILES'));
			form.result = false;
			return false;
		}
		if (typeOf(this.widget) !== 'null') {
			this.widget.images.each(function (image, key) {
				key = key.split('\\').getLast();
				var f = document.getElements('input[name*=' + key + ']');
				f = f[1];
				// $$$ rob - seems reloading ajax fileupload element in ajax form (e.g. from db join add record)
				// is producing odd effects where old fileupload object constains info to previously uploaded image?
				if (typeOf(f) !== 'null') {
					f.value = JSON.encode(image);
				}
			});
		}
		return true;
	},

	allUploaded : function () {
		var uploaded = true;
		if (this.uploader) {
			this.uploader.files.each(function (file) {
				if (file.loaded === 0) {
					uploaded = false;
				}
			}.bind(this));
		}
		return uploaded;
	}
});

var ImageWidget = new Class({

	initialize : function (canvas, opts) {
		this.canvas = canvas;

		this.imageDefault = {
			'rotation': 0,
			'scale': 100,
			'imagedim': {
				x: 200,
				y: 200,
				w: 400,
				h: 400
			},
			'cropdim': {
				x: 75,
				y: 25,
				w: 150,
				h: 50
			}
		};

		$extend(this.imageDefault, opts);

		this.windowopts = {
			'id': this.canvas.id + '-mocha',
			'type': 'modal',
			content: this.canvas.getParent(),
			loadMethod: 'html',
			width: 420,
			height: 540,
			storeOnClose: true,
			createShowOverLay: false,
			crop: opts.crop,
			onClose : function () {
				document.id('modalOverlay').hide();
			},
			onContentLoaded : function () {
				this.center();
			}
		};
		this.windowopts.title = opts.crop ? Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_CROP_AND_SCALE') : Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_PREVIEW');
		this.showWin();
		this.images = $H({});
		var parent = this;
		this.CANVAS = new FbCanvas({
			canvasElement: document.id(this.canvas.id),
			enableMouse: true,
			cacheCtxPos: false
		});

		this.CANVAS.layers.add(new Layer({
			id: 'bg-layer'
		}));
		this.CANVAS.layers.add(new Layer({
			id: 'image-layer'
		}));
		if (opts.crop) {
			this.CANVAS.layers.add(new Layer({
				id: 'overlay-layer'
			}));
			this.CANVAS.layers.add(new Layer({
				id: 'crop-layer'
			}));
		}
		var bg = new CanvasItem({
			id: 'bg',
			scale: 1,
			events: {
				onDraw: function (ctx) {
					if (typeOf(ctx) === 'null') {
						//return;
						ctx = this.CANVAS.ctx;
					}
					ctx.fillStyle = "#DFDFDF";
					ctx.fillRect(0, 0, 400 / this.scale, 400 / this.scale);
				}.bind(this)
			}
		});

		this.CANVAS.layers.get('bg-layer').add(bg);
		if (opts.crop) {
			this.overlay = new CanvasItem({
				id: 'overlay',
				events: {
					onDraw: function (ctx) {
						if (typeOf(ctx) === 'null') {
							ctx = this.CANVAS.ctx;
						}
						this.withinCrop = true;
						if (this.withinCrop) {
							var top = {
								x: 0,
								y: 0
							};
							var bottom = {
								x: 400,
								y: 400
							};
							ctx.fillStyle = "rgba(0, 0, 0, 0.3)";
							var cropper = this.cropperCanvas;
							ctx.fillRect(top.x, top.y, bottom.x, cropper.y - (cropper.h / 2));// top
							ctx.fillRect(top.x - (cropper.w / 2), top.y + cropper.y - (cropper.h / 2), top.x + cropper.x, cropper.h);// left
							ctx.fillRect(top.x + cropper.x + cropper.w - (cropper.w / 2), top.y + cropper.y - (cropper.h / 2), bottom.x, cropper.h);// right
							ctx.fillRect(top.x, top.y + (cropper.y + cropper.h) - (cropper.h / 2), bottom.x, bottom.y);// bottom
						}
					}.bind(this)
				}
			});
	
			this.CANVAS.layers.get('overlay-layer').add(this.overlay);
		}

		this.imgCanvas = this.makeImgCanvas();

		this.CANVAS.layers.get('image-layer').add(this.imgCanvas);

		this.cropperCanvas = this.makeCropperCanvas();
		if (opts.crop) {
			// add an item
			this.CANVAS.layers.get('crop-layer').add(this.cropperCanvas);
		}
		this.makeThread();
		this.watchZoom();
		this.watchRotate();
		this.watchClose();
		this.win.close();
	},
	
	setImage : function (uri, filepath, params) {
		this.activeFilePath = filepath;
		if (this.img && this.img.src === uri) {
			this.showWin();
			return;
		}
		
		this.img = Asset.image(uri);

		var el = new Element('img', {
			src : uri
		});
		if (filepath) {
			el.store('filepath', filepath);
		} else {
			filepath = el.retrieve('filepath');
		}
		el.injectInside(document.body).hide();

		(function () {
			var show, imagew, imageh, imagex, imagey, i;
			if (!this.images.has(filepath)) {
				show = false;
				params = params ? params : new CloneObject(this.imageDefault, true, []);
				this.images.set(filepath, params);
				var s = el.getDimensions(true);
				imagew = s.width;
				imageh = s.height;
				
				// as imagedim is changed when the image is scaled, but we still want to store the original
				// image dimensions for when we come to re-edit it.
				// not sure we actually need it - but seems a good idea to have a reference to the original image size
				params.mainimagedim = params.imagedim;
				params.mainimagedim.w = imagew;
				params.mainimagedim.h = imageh;
				imagex = params.imagedim.x;
				imagey = params.imagedim.y;
			} else {
				show = true;
				i = this.images.get(filepath);
				imagew = 400;
				imageh = 400;
				imagex = i.imagedim.x;
				imagey = i.imagedim.y;
			}

			i = this.images.get(filepath);
			if (this.scaleSlide) {
				this.scaleSlide.set(i.scale);
			}
			if (this.rotateSlide) {
				this.rotateSlide.set(i.rotation);
			}
			if (this.cropperCanvas) {
				this.cropperCanvas.x = i.cropdim.x;
				this.cropperCanvas.y = i.cropdim.y;
				this.cropperCanvas.w = i.cropdim.w;
				this.cropperCanvas.h = i.cropdim.h;
			}
			this.imgCanvas.w = imagew;
			this.imgCanvas.h = imageh;
			this.imgCanvas.x = imagex;
			this.imgCanvas.y = imagey;
			this.imgCanvas.rotation = i.rotation;
			this.imgCanvas.scale = i.scale / 100;
			if (show) {
				this.showWin();
			}

			el.destroy();
		}.bind(this)).delay(500);

	},
	
	makeImgCanvas: function () {
		var parent = this;
		return new CanvasItem({
			id: 'imgtocrop',
			w: 400,
			h: 400,
			x: 200,
			y: 200,
			interactive: true,
			rotation: 0,
			scale: 1,
			offset: [ 0, 0 ],
			events: {
				onMousemove: function (x, y) {
					if (this.dragging) {
						var w = this.w * this.scale;
						var h = this.h * this.scale;
						this.x = x - this.offset[0] + w * 0.5;
						this.y = y - this.offset[1] + h * 0.5;
					}
				},
				onDraw : function (ctx) {
					ctx = parent.CANVAS.ctx;
					if (typeOf(parent.img) === 'null') {
						//console.log('no parent img', parent);
						return;
					}
						
					var w = this.w * this.scale;
					var h = this.h * this.scale;
					var x = this.x - w * 0.5;
					var y = this.y - h * 0.5;

					// standard Canvas rotation operation
					ctx.save();
					ctx.translate(this.x, this.y);
					ctx.rotate(this.rotation * Math.PI / 180);

					this.hover ? ctx.strokeStyle = '#f00' : ctx.strokeStyle = '#000'; // red/black
					ctx.strokeRect(w * -0.5, h * -0.5, w, h);
					if (typeOf(parent.img) !== 'null') {
						try {
							ctx.drawImage(parent.img, w * -0.5, h * -0.5, w, h);
						} catch (err) {
							// only show this for debugging as if we upload a pdf then we get shown lots of these errors.
							//fconsole(err, parent.img, w * -0.5, h * -0.5, w, h);
						}
					}
					ctx.restore();
					if (typeOf(parent.img) !== 'null' && parent.images.get(parent.activeFilePath)) {
						parent.images.get(parent.activeFilePath).imagedim = {
							x : this.x,
							y : this.y,
							w : w,
							h : h
						};

					}
					this.setDims(x, y, w, h);
				},

				onMousedown : function (x, y) {
					parent.CANVAS.setDrag(this);
					this.offset = [ x - this.dims[0], y - this.dims[1] ];
					this.dragging = true;
				},

				onMouseup : function () {
					parent.CANVAS.clearDrag();
					this.dragging = false;
				},

				onMouseover : function () {
					parent.overImg = true;
					document.body.style.cursor = "move";
				},

				onMouseout : function () {
					parent.overImg = false;
					if (!parent.overCrop) {
						document.body.style.cursor = "default";
					}
				}
			}
		});
	},
	
	makeCropperCanvas: function () {
		var parent = this;
		return new CanvasItem({
			id: 'item',
			x: 175,
			y: 175,
			w: 150,
			h: 50,
			interactive: true,
			offset: [ 0, 0 ],
			events: {
				onDraw: function (ctx) {
					ctx = parent.CANVAS.ctx;
					if (typeOf(ctx) === 'null') {
						return;
					}
					/*
					 * calculate dimensions locally because they are have to be translated
					 * in order to use translate and rotate with the desired effect:
					 * rotate the item around its visual center
					 */

					var w = this.w;
					var h = this.h;
					var x = this.x - w * 0.5;
					var y = this.y - h * 0.5;

					// standard Canvas rotation operation

					ctx.save();
					ctx.translate(this.x, this.y);

					this.hover ? ctx.strokeStyle = '#f00' : ctx.strokeStyle = '#000'; // red/black
					ctx.strokeRect(w * -0.5, h * -0.5, w, h);
					ctx.restore();

					/*
					 * used to determine the whether the mouse is over an item or not.
					 */

					if (typeOf(parent.img) !== 'null' && parent.images.get(parent.activeFilePath)) {
						parent.images.get(parent.activeFilePath).cropdim = {
							x : this.x,
							y : this.y,
							w : w,
							h : h
						};
					}
					this.setDims(x, y, w, h);
				},

				onMousedown : function (x, y) {
					parent.CANVAS.setDrag(this);
					this.offset = [ x - this.dims[0], y - this.dims[1] ];
					this.dragging = true;
					parent.overlay.withinCrop = true;
				},

				onMousemove : function (x, y) {
					document.body.style.cursor = "move";
					if (this.dragging) {
						var w = this.w;
						var h = this.h;
						this.x = x - this.offset[0] + w * 0.5;
						this.y = y - this.offset[1] + h * 0.5;
					}
				},

				onMouseup : function () {
					parent.CANVAS.clearDrag();
					this.dragging = false;
					parent.overlay.withinCrop = false;
				},

				onMouseover : function () {
					this.hover = true;
					parent.overCrop = true;

				},

				onMouseout : function () {
					if (!parent.overImg) {
						document.body.style.cursor = "default";
					}
					parent.overCrop = false;
					this.hover = false;
				}
			}
		});
	},
	
	makeThread: function () {
		this.CANVAS.addThread(new Thread({
			id : 'myThread',
			onExec : function () {
				if (typeOf(this.CANVAS) !== 'null') {
					if (typeOf(this.CANVAS.ctxEl) !== 'null') {
						this.CANVAS.clear().draw();
					}
				}
			}.bind(this)
		}));
	},
	
	/**
	 * watch the close button
	 */
	
	watchClose: function () {
		var w = document.id(this.windowopts.id);
		w.getElement('input[name=close-crop]').addEvent('click', function (e) {
			this.win.close();
		}.bind(this));
	},
	
	/**
	 * set up and wath the zoom slide and input field
	 */
	
	watchZoom: function () {
		var w = document.id(this.windowopts.id);
		if (!this.windowopts.crop) {
			return;
		}
		this.scaleField = w.getElement('input[name=zoom-val]');
		this.scaleSlide = new Slider(w.getElement('.fabrikslider-line'), w.getElement('.knob'), {
			range : [ 20, 300 ],
			onChange : function (pos) {
				this.imgCanvas.scale = pos / 100;
				if (typeOf(this.img) !== 'null') {
					try {
						this.images.get(this.activeFilePath).scale = pos;
					} catch (err) {
						fconsole('didnt get active file path:' + this.activeFilePath);
					}
				}
				this.scaleField.value = pos;
			}.bind(this)
		}).set(100);

		this.scaleField.addEvent('keyup', function (e) {
			this.scaleSlide.set(e.target.get('value'));
		}.bind(this));
	},
	
	/**
	 * set up and wath the rotate slide and input field
	 */
	
	watchRotate: function () {
		var w = document.id(this.windowopts.id);
		if (!this.windowopts.crop) {
			return;
		}
		var r = w.getElement('.rotate');
		this.rotateField = r.getElement('input[name=rotate-val]');
		this.rotateSlide = new Slider(r.getElement('.fabrikslider-line'), r.getElement('.knob'), {
			onChange : function (pos) {
				this.imgCanvas.rotation = pos;
				if (typeOf(this.img) !== 'null') {
					try {
						this.images.get(this.activeFilePath).rotation = pos;
					} catch (err) {
						fconsole('rorate err' + this.activeFilePath);
					}
				}
				this.rotateField.value = pos;
			}.bind(this),
			steps : 360
		}).set(0);
		this.rotateField.addEvent('keyup', function (e) {
			this.rotateSlide.set(e.target.get('value'));
		}.bind(this));
	},
	
	showWin : function () {
		this.win = Fabrik.getWindow(this.windowopts);
		if (typeOf(this.CANVAS) === 'null') {
			return;
		}
		if (typeOf(this.CANVAS.ctxEl) !== 'null') {
			this.CANVAS.ctxPos = document.id(this.CANVAS.ctxEl).getPosition();
		}
		
		if (typeOf(this.CANVAS.threads) !== 'null') {
			if (typeOf(this.CANVAS.threads.get('myThread')) !== 'null') {
				//fixes issue where sometime canvas thread is not started/running so nothing is drawn
				this.CANVAS.threads.get('myThread').start();
			}
		}
	}
});