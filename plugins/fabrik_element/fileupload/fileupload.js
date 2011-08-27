var FbFileUpload = new Class({
	Extends : FbFileElement,
	initialize : function(element, options) {
		this.plugin = 'fileupload';
		this.parent(element, options);
		this.toppath = this.options.dir;
		if (this.options.folderSelect == 1 && this.options.editable == 1) {
			this.ajaxFolder();
		}
		
		window.addEvent('fabrik.form.submitted', function(form){
			this.onSubmit(form);
		}.bind(this))
		if (this.options.ajax_upload == 1 && this.options.editable !== false) {
			this.watchAjax();
			this.options.files = $H(this.options.files);
			if (this.options.files.getLength() !== 0) {
				this.uploader.trigger('FilesAdded', this.options.files);
				this.startbutton.addClass('plupload_disabled');
				this.options.files.each(function(file) {
					var response = {
						'filepath' : file.path,
						uri : file.url
					};
					this.uploader.trigger('UploadProgress', file);
					this.uploader.trigger('FileUploaded', file, {
						response : JSON.encode(response)
					});
					$(file.id).getElement('.plupload_file_status').set('text', '100%');
				}.bind(this));
				this.uploader.trigger('Init');
				// hack to reposition the hidden input field over the 'ad' button
				var c = $(this.options.element + '_container');
				var diff = $(this.options.element + '_browseButton').getPosition().y - c.getPosition().y;
				c.getElement('input[type=file]').getParent().setStyle('top', diff);
			}
		}
	},

	cloned : function() {
		// replaced cloned image with default image
		if (typeOf(this.element.findClassUp('fabrikElement')) === 'null') {
			return;
		}
		var i = this.element.findClassUp('fabrikElement').getElement('img');
		if (i) {
			i.src = Fabrik.liveSite + this.options.defaultImage;
		}
	},

	decloned : function(groupid) {
		var f = $('form_' + this.form.id);
		var i = f.getElement('input[name=fabrik_deletedimages[' + groupid + ']');
		if ($type(i) == false) {
			new Element('input', {
				'type' : 'hidden',
				'name' : 'fabrik_fileupload_deletedfile[' + groupid + '][]',
				'value' : this.options.value
			}).inject(f);
		}
	},

	update : function(val) {
		if (this.element) {
			var i = this.element.getElement('img');
			if (typeOf(i) !== 'null') {
				i.src = val;
			}
		}
	},

	watchAjax : function() {
		if (this.options.editable === false) {
			return;
		}
		var c = this.element.findClassUp('fabrikSubElementContainer');
		this.container = c;
		if (this.options.crop == 1) {
			this.widget = new ImageWidget(c.getElement('canvas'), {
				'cropdim' : {
					w : this.options.cropwidth,
					h : this.options.cropheight,
					x : this.options.cropwidth / 2,
					y : this.options.cropheight / 2
				}
			});
		}
		this.pluploadContainer = c.getElement('.plupload_container');
		this.pluploadFallback = c.getElement('.plupload_fallback');
		this.droplist = c.getElement('.plupload_filelist');
		this.startbutton = c.getElement('.plupload_start');
		this.uploader = new plupload.Uploader({
			runtimes : this.options.ajax_runtime,
			browse_button : this.element.id + '_browseButton',
			container : this.element.id + '_container',
			drop_element : this.element.id + '_dropList',
			url : Fabrik.liveSite + 'index.php?option=com_fabrik&format=raw&task=plugin.pluginAjax&plugin=fileupload&method=ajax_upload&element_id='
					+ this.options.elid,
			max_file_size : this.options.max_file_size + 'kb',
			unique_names : false,
			flash_swf_url : 'plugins/element/fileupload/plupload/js/plupload.flash.swf',
			silverlight_xap_url : 'plugins/element/fileupload/plupload/js/plupload.silverlight.xap',
			chunk_size : this.options.ajax_chunk_size + 'kb',
			multipart : true
		});

		// (1) INIT ACTIONS
		this.uploader.bind('Init', function(up, params) {
			// FORCEFULLY NUKE GRACEFUL DEGRADING FALLBACK ON INIT
			this.pluploadFallback.destroy();
			this.pluploadContainer.removeClass("fabrikHide");
		}.bind(this));

		this.uploader.bind('FilesRemoved', function(up, files) {
		});

		// (2) ON FILES ADDED ACTION
		this.uploader.bind('FilesAdded', function(up, files) {
			var txt = this.droplist.getElement('.plupload_droptext');
			if ($type(txt) !== false) {
				txt.destroy();
			}
			var count = this.droplist.getElements('li').length;
			this.startbutton.removeClass('plupload_disabled');
			files.each(function(file, idx) {
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
		this.uploader.bind('UploadProgress', function(up, file) {
			$(file.id).getElement('.plupload_file_status').set('text', file.percent + '%');
		});

		this.uploader.bind('Error', function(up, err) {
			fconsole('Error:' + err);
		});

		this.uploader.bind('ChunkUploaded', function(up, file, response) {
			response = JSON.decode(response.response);
			if ($type(response) !== false) {
				if (response.error) {
					fconsole(response.error.message);
				}
			}
		});

		this.uploader.bind('FileUploaded', function(up, file, response) {
			response = JSON.decode(response.response);
			if (this.options.crop) {
				$(file.id).getElement('.plupload_resize').show();
				var resizebutton = $(file.id).getElement('.plupload_resize').getElement('a');
				resizebutton.href = response.uri;
				resizebutton.id = 'resizebutton_' + file.id;
				resizebutton.store('filepath', response.filepath);
				this.widget.setImage(response.uri, response.filepath, file.params);
			}
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
		c.getElement('.plupload_start').addEvent('click', function(e) {
			e.stop();
			this.uploader.start();
		}.bind(this));

		// (5) KICK-START PLUPLOAD
		this.uploader.init();
	},

	pluploadRemoveFile : function(e) {
		e.stop();
		var id = e.target.getParent().getParent().id.split('_').getLast();// alreadyuploaded_8_13
		var f = e.target.getParent().getParent().getElement('.plupload_file_name span').get('text');
		var url = Fabrik.liveSite + 'index.php?option=com_fabrik&format=raw&&task=plugin.pluginAjax&plugin=fileupload&method=ajax_deleteFile&element_id='
				+ this.options.id;
		new Request({
			url : url,
			data : {
				'file' : f,
				'recordid' : id
			}
		}).send();
		var li = e.target.findClassUp('plupload_delete');
		li.destroy();
		// remove hidden fields as well
		if ($('id_alreadyuploaded_' + this.options.id + '_' + id)) {
			$('id_alreadyuploaded_' + this.options.id + '_' + id).destroy();
		}
		if ($('coords_alreadyuploaded_' + this.options.id + '_' + id)) {
			$('coords_alreadyuploaded_' + this.options.id + '_' + id).destroy();
		}
		/*
		 * if (this.droplist.getChildren().length == 0) {
		 * this.startbutton.addClass('plupload_disabled'); this.droplist.adopt(new
		 * Element('li', { 'class' : 'plupload_droptext' }).set('text',
		 * Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_DRAG_FILES_HERE'))); }
		 */
	},

	pluploadResize : function(e) {
		e.stop();
		var a = e.target;
		if (this.options.crop) {
			this.widget.setImage(e.target.href, e.target.retrieve('filepath'));
		}
	},

	onSubmit : function(form) {
		if (!this.allUploaded()) {
			alert(Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_UPLOAD_ALL_FILES'));
			form.result = false;
			return false;
		}
		if (this.options.crop) {
			this.widget.images.each(function(image, key) {
				key = key.split('\\').getLast();
				var f = document.getElements('input[name*=' + key + ']');
				var f = f[1];
				f.value = JSON.encode(image);
			});
		}
		return true;
	},

	allUploaded : function() {
		var uploaded = true;
		if (this.uploader) {
			this.uploader.files.each(function(file) {
				if (file.loaded == 0) {
					uploaded = false;
				}
			}.bind(this));
		}
		return uploaded;
	}
});

var ImageWidget = new Class({

	setImage : function(uri, filepath, params) {
		this.activeFilePath = filepath;
		if (this.img && this.img.src == uri) {
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

		(function() {
			if (!this.images.has(filepath)) {
				var show = false;
				params = params ? params : new CloneObject(this.imageDefault, true, []);
				this.images.set(filepath, params);
				var s = el.getDimensions(true);
				var imagew = s.width;
				var imageh = s.height;
				// var imagex = imagew / 2;
				var imagex = params.imagedim.x;
				// var imagey = imageh / 2;
				var imagey = params.imagedim.y;
			} else {
				show = true;
				var i = this.images.get(filepath);
				imagew = 400;
				imageh = 400;
				imagex = i.imagedim.x;
				imagey = i.imagedim.y;
			}

			var i = this.images.get(filepath);
			this.scaleSlide.set(i.scale);
			this.rotateSlide.set(i.rotation);
			this.cropperCanvas.x = i.cropdim.x;
			this.cropperCanvas.y = i.cropdim.y;
			this.cropperCanvas.w = i.cropdim.w;
			this.cropperCanvas.h = i.cropdim.h;

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

	showWin : function() {
		this.win = Fabrik.getWindow(this.windowopts);
		if (typeOf(CANVAS) !== 'null' && typeOf(CANVAS.ctxEl) !== 'null') {
			CANVAS.ctxPos = $(CANVAS.ctxEl).getPosition();
		}
	},

	initialize : function(canvas, opts) {
		this.canvas = canvas;

		this.imageDefault = {
			'rotation' : 0,
			'scale' : 100,
			'imagedim' : {
				x : 200,
				y : 200,
				w : 400,
				h : 400
			},
			'cropdim' : {
				x : 75,
				y : 25,
				w : 150,
				h : 50
			}
		};

		$extend(this.imageDefault, opts);

		this.windowopts = {
			'id' : this.canvas.id + '-mocha',
			'type':'modal',
			title : 'Crop and scale',
			content : this.canvas.getParent(),
			loadMethod : 'html',
			width : 420,
			height : 500,
			storeOnClose : true,
			createShowOverLay: false,
			onClose : function() {
				$('modalOverlay').hide();
			}
		};
		this.showWin();
		
		this.images = $H({});
		var parent = this;
		CANVAS.init({
			canvasElement : $(this.canvas.id),
			enableMouse : true,
			cacheCtxPos : false
		});

		CANVAS.layers.add(new Layer({
			id : 'bg-layer'
		}));
		CANVAS.layers.add(new Layer({
			id : 'image-layer'
		}));
		CANVAS.layers.add(new Layer({
			id : 'overlay-layer'
		}));
		CANVAS.layers.add(new Layer({
			id : 'crop-layer'
		}));

		var bg = new CanvasItem({
			id : 'bg',
			scale : 1,
			events : {
				onDraw : function(ctx) {
					ctx.fillStyle = "#DFDFDF";
					ctx.fillRect(0, 0, 400 / this.scale, 400 / this.scale);
				}
			}
		});

		CANVAS.layers.get('bg-layer').add(bg);

		var overlay = new CanvasItem({
			id : 'overlay',
			events : {
				onDraw : function(ctx) {
					this.withinCrop = true;
					if (this.withinCrop) {
						var top = {
							x : 0,
							y : 0
						};
						var bottom = {
							x : 400,
							y : 400
						};
						ctx.fillStyle = "rgba(0, 0, 0, 0.3)";
						var cropper = parent.cropperCanvas;
						ctx.fillRect(top.x, top.y, bottom.x, cropper.y - (cropper.h / 2));// top
						ctx.fillRect(top.x - (cropper.w / 2), top.y + cropper.y - (cropper.h / 2), top.x + cropper.x, cropper.h);// left
						ctx.fillRect(top.x + cropper.x + cropper.w - (cropper.w / 2), top.y + cropper.y - (cropper.h / 2), bottom.x, cropper.h);// right
						ctx.fillRect(top.x, top.y + (cropper.y + cropper.h) - (cropper.h / 2), bottom.x, bottom.y);// bottom
					}
				}
			}
		});

		CANVAS.layers.get('overlay-layer').add(overlay);

		this.imgCanvas = new CanvasItem({
			id : 'imgtocrop',
			w : 400,
			h : 400,
			x : 200,
			y : 200,
			interactive : true,
			rotation : 0,
			scale : 1,
			offset : [ 0, 0 ],
			events : {
				onMousemove : function(x, y) {
					if (this.dragging) {
						var w = this.w * this.scale;
						var h = this.h * this.scale;
						this.x = x - this.offset[0] + w * .5;
						this.y = y - this.offset[1] + h * .5;
					}
				},
				onDraw : function(ctx) {
					var w = this.w * this.scale;
					var h = this.h * this.scale;
					var x = this.x - w * .5;
					var y = this.y - h * .5;

					// standard Canvas rotation operation
					ctx.save();
					ctx.translate(this.x, this.y);
					ctx.rotate(this.rotation * Math.PI / 180);

					this.hover ? ctx.strokeStyle = '#f00' : ctx.strokeStyle = '#000'; // red/black
					ctx.strokeRect(w * -0.5, h * -0.5, w, h);

					if ($type(parent.img) !== false) {
						try {
							ctx.drawImage(parent.img, w * -0.5, h * -0.5, w, h);
						} catch (err) {
							fconsole(err);
						}
					}
					ctx.restore();
					if ($type(parent.img) != false && parent.images.get(parent.activeFilePath)) {
						parent.images.get(parent.activeFilePath).imagedim = {
							x : this.x,
							y : this.y,
							w : w,
							h : h
						};

					}
					this.setDims(x, y, w, h);
				},

				onMousedown : function(x, y) {
					CANVAS.setDrag(this);
					this.offset = [ x - this.dims[0], y - this.dims[1] ];
					this.dragging = true;
				},

				onMouseup : function() {
					CANVAS.clearDrag();
					this.dragging = false;
				},

				onMouseover : function() {
					parent.overImg = true;
					document.body.style.cursor = "move";
				},

				onMouseout : function() {
					parent.overImg = false;
					if (!parent.overCrop) {
						document.body.style.cursor = "default";
					}
				}
			}
		});

		CANVAS.layers.get('image-layer').add(this.imgCanvas);

		// add an item
		this.cropperCanvas = new CanvasItem({
			id : 'item',
			x : 175,
			y : 175,
			w : 150,
			h : 50,
			interactive : true,
			offset : [ 0, 0 ],
			events : {
				onDraw : function(ctx) {
					/*
					 * calculate dimensions locally because they are have to be translated
					 * in order to use translate and rotate with the desired effect:
					 * rotate the item around its visual center
					 */

					var w = this.w;
					var h = this.h;
					var x = this.x - w * .5;
					var y = this.y - h * .5;

					// standard Canvas rotation operation

					ctx.save();
					ctx.translate(this.x, this.y);

					this.hover ? ctx.strokeStyle = '#f00' : ctx.strokeStyle = '#000'; // red/black
					ctx.strokeRect(w * -0.5, h * -0.5, w, h);
					ctx.restore();

					/*
					 * used to determine the whether the mouse is over an item or not.
					 */

					if ($type(parent.img) != false && parent.images.get(parent.activeFilePath)) {
						parent.images.get(parent.activeFilePath).cropdim = {
							x : this.x,
							y : this.y,
							w : w,
							h : h
						};
					}
					this.setDims(x, y, w, h);
				},

				onMousedown : function(x, y) {
					CANVAS.setDrag(this);
					this.offset = [ x - this.dims[0], y - this.dims[1] ];
					this.dragging = true;
					overlay.withinCrop = true;
				},

				onMousemove : function(x, y) {
					document.body.style.cursor = "move";
					if (this.dragging) {
						var w = this.w;
						var h = this.h;
						this.x = x - this.offset[0] + w * .5;
						this.y = y - this.offset[1] + h * .5;
					}
				},

				onMouseup : function() {
					CANVAS.clearDrag();
					this.dragging = false;
					overlay.withinCrop = false;
				},

				onMouseover : function() {
					this.hover = true;
					parent.overCrop = true;

				},

				onMouseout : function() {
					if (!parent.overImg) {
						document.body.style.cursor = "default";
					}
					parent.overCrop = false;
					this.hover = false;
				}
			}
		});

		CANVAS.layers.get('crop-layer').add(this.cropperCanvas);

		CANVAS.addThread(new Thread({
			id : 'myThread',
			onExec : function() {
				if (typeOf(CANVAS.ctxEl) !== 'null') {
					CANVAS.clear().draw();
				}
			}
		}));

		var w = $(this.windowopts.id);
		this.scaleField = w.getElement('input[name=zoom-val]');
		this.scaleSlide = new Slider(w.getElement('.fabrikslider-line'), w.getElement('.knob'), {
			range : [ 20, 300 ],
			onChange : function(pos) {
				this.imgCanvas.scale = pos / 100;
				if ($type(this.img) != false) {
					try {
						this.images.get(this.activeFilePath).scale = pos;
					} catch (err) {
						fconsole('didnt get active file path:' + ths.activeFilePath);
					}
				}
				this.scaleField.value = pos;
			}.bind(this)
		}).set(100);

		this.scaleField.addEvent('keyup', function(e) {
			this.scaleSlide.set($(e.target).get('value'));
		}.bind(this));

		var r = w.getElement('.rotate');
		this.rotateField = r.getElement('input[name=rotate-val]');
		this.rotateSlide = new Slider(r.getElement('.fabrikslider-line'), r.getElement('.knob'), {
			onChange : function(pos) {
				this.imgCanvas.rotation = pos;
				if ($type(this.img) != false) {
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
		this.rotateField.addEvent('keyup', function(e) {
			this.rotateSlide.set($(e.target).get('value'));
		}.bind(this));

		w.getElement('input[name=close-crop]').addEvent('click', function(e) {
			this.win.close();
		}.bind(this));
		this.win.close();
	}
});