/**
 * @author Robert
 */

/*jshint mootools: true */
/*global Fabrik:true, fconsole:true, Joomla:true, CloneObject:true, $A:true, $H:true,unescape:true,Asset:true */

var FbElement =  new Class({
	
	Implements: [Events, Options],
	
	options : {
		element: null,
		defaultVal: '',
		value: '',
		editable: false
	},
		
	initialize: function (element, options) {
		this.plugin = '';
		options.element = element;
		this.strElement = element;
		this.loadEvents = []; // need to store these for use if the form is reset
		this.setOptions(options);
		this.setElement();
	},
	
	setElement: function () {
		if (document.id(this.options.element)) {
			this.element = document.id(this.options.element);
			this.setorigId();
		}
	},
	
	get: function (v) {
		if (v === 'value') {
			return this.getValue(); 
		}
	},
	
	attachedToForm: function ()
	{
		this.setElement();
		this.alertImage = new Asset.image(this.form.options.images.alert);
		this.alertImage.setStyle('cursor', 'pointer');
		this.successImage = new Asset.image(this.form.options.images.action_check);
		this.loadingImage = new Asset.image(this.form.options.images.ajax_loader);
		//put ini code in here that can't be put in initialize()
		// generally any code that needs to refer to  this.form, which
		//is only set when the element is assigned to the form.
	},

	/** allows you to fire an array of events to element /  subelements, used in calendar to trigger js events when the calendar closes **/
	fireEvents: function (evnts) {
		if (this.hasSubElements()) {
			this._getSubElements().each(function (el) {
				$A(evnts).each(function (e) {
					el.fireEvent(e);
				}.bind(this));
			}.bind(this));
		} else {
			$A(evnts).each(function (e) {
				this.element.fireEvent(e);
			}.bind(this));
		}
	},
	
	getElement: function ()
	{
		//use this in mocha forms whose elements (such as database jons) arent loaded
		//when the class is ini'd
		if (typeOf(this.element) === 'null') {
			this.element = document.id(this.options.element); 
		}
		return this.element;
	},

	//used for elements like checkboxes or radio buttons
	_getSubElements: function () {
		var element = this.getElement();
		if (typeOf(element) === 'null') {
			return false;
		}
		this.subElements = element.getElements('.fabrikinput');
		return this.subElements;
	},
	
	hasSubElements: function () {
		this._getSubElements();
		if (typeOf(this.subElements) === 'array' || typeOf(this.subElements) === 'elements') {
			return this.subElements.length > 0 ? true : false;
		}
		return false;
	},
	
	unclonableProperties: function ()
	{
		return ['form'];
	},
	
	runLoadEvent : function (js, delay) {
		delay = delay ? delay : 0;
		//should use eval and not Browser.exec to maintain reference to 'this'
		if (typeOf(js) === 'function') {
			js.delay(delay);
		} else {
			if (delay === 0) {
				eval(js);
			} else {
				(function () {
					eval(js);
				}.bind(this)).delay(delay);
			}
		}
	},
	
	addNewEvent: function (action, js) {
		if (action === 'load') {
			this.loadEvents.push(js);
			this.runLoadEvent(js);
		} else {
			if (!this.element) {
				this.element = $(this.strElement);
			}
			if (this.element) {
				this.element.addEvent(action, function (e) {
					e.stop();
					var r = typeOf(js) === 'function' ? js.delay(0) :	eval(js);
				});
				
				this.element.addEvent('blur', function (e) {
					this.validate();
				}.bind(this));
			}
		}
	},
	
	validate: function () {},
	
	//store new options created by user in hidden field
	addNewOption: function (val, label)
	{
		var a;
		var added = $(this.options.element + '_additions').value;
		var json = {'val': val, 'label': label};
		if (added !== '') {
			a = JSON.decode(added);
		} else {
			a = [];
		}
		a.push(json);
		var s = '[';
		for (var i = 0; i < a.length; i++) {
			s += JSON.encode(a[i]) + ',';
		}
		s = s.substring(0, s.length - 1) + ']';
		$(this.options.element + '_additions').value = s;
	},
	
	//below functions can override in plugin element classes
	
	update: function (val) {
		if (this.element) {
			if (this.options.editable) {
				this.element.value = val;
			} else {
				this.element.innerHTML = val;
			}
		}
	},
	
	getValue: function () {
		if (this.element) {
			if (this.options.editable) {
				return this.element.value;
			} else {
				return this.options.value;
			}
		}
		return false;
	},
	
	reset: function ()
	{
		this.loadEvents.each(function (js) {
			this.runLoadEvent(js, 100);
		}.bind(this));
		if (this.options.editable === true) {
			this.update(this.options.defaultVal);
		}
	},
	
	clear: function ()
	{
		this.update('');
	},
	
	onsubmit: function () {
		return true;
	},
	
	cloned: function (c) {
		//run when the element is cloned in a repeat group
	},
	
	decloned: function (groupid) {
		//run when the element is decleled from the form as part of a deleted repeat group
	},
	
	//get the wrapper dom element that contains all of the elements dom objects
	getContainer: function ()
	{
		return typeOf(this.element) === 'null' ? false : this.element.getParent('.fabrikElementContainer');
	},
	
	//get the dom element which shows the error messages
	getErrorElement: function ()
	{
		return this.getContainer().getElement('.fabrikErrorMessage');
	},
	
	//get the fx to fade up/down element validation feedback text
	
	getValidationFx: function () {
		if (!this.validationFX) {
			this.validationFX = new Fx.Morph(this.getErrorElement(), {duration: 500, wait: true});
		}
		return this.validationFX;
	},
	
	setErrorMessage: function (msg, classname) {
		var a;
		var classes = ['fabrikValidating', 'fabrikError', 'fabrikSuccess'];
		var container = this.getContainer();
		
		classes.each(function (c) {
			var r = classname === c ? container.addClass(c) : container.removeClass(c);
		});
		switch (classname) {
		case 'fabrikError':
			a = new Element('a', {'href': '#', 'title': msg, 'events': {
				'click': function (e) {
					e.stop();
				}
			}}).adopt(this.alertImage);
			this.getErrorElement().empty().adopt(a);
			Fabrik.tips.attach(a);
			break;
		case 'fabrikSuccess':
			this.getErrorElement().empty().adopt(this.successImage);
			break;
		case 'fabrikValidating':
			this.getErrorElement().empty().adopt(this.loadingImage);
			break;
		}

		this.getErrorElement().removeClass('fabrikHide');
		var parent = this.form;
		if (classname === 'fabrikError' || classname === 'fabrikSuccess') {
			parent.updateMainError();
		}
		
		var fx = this.getValidationFx();
		switch (classname) {
		case 'fabrikValidating':
		case 'fabrikError':
			fx.start({
				'opacity': 1
			});
			break;
		case 'fabrikSuccess':
			fx.start({
				'opacity': 1
			}).chain(function () {
				//only fade out if its still the success message
				if (container.hasClass('fabrikSuccess')) {
					container.removeClass('fabrikSuccess');
					this.start.delay(700, this, {
						'opacity': 0,
						'onComplete': function () {
							parent.updateMainError();
							classes.each(function (c) {
								container.removeClass(c);
							});
						}
					});
				}
			});
			break;
		}
	},
	
	setorigId: function ()
	{
		if (this.options.repeatCounter > 0) {
			var e = this.options.element;
			this.origId = e.substring(0, e.length - 1 - this.options.repeatCounter.toString().length);
		}
	},
	
	decreaseName: function (delIndex) {
		var element = this.getElement();
		if (typeOf(element) === 'null') {
			return false;
		}
		if (this.hasSubElements()) {
			this._getSubElements().each(function (e) {
				e.name = this._decreaseName(e.name, delIndex);
				e.id = this._decreaseId(e.id, delIndex);
			}.bind(this));
		} else {
			this.element.name = this._decreaseName(this.element.name, delIndex);
		}
		this.element.id = this._decreaseId(this.element.id, delIndex);
		return this.element.id;
	},
	
	_decreaseId: function (n, delIndex) {
		var bits = $A(n.split('_'));
		var i = bits.getLast();
		if (i !== i.toInt()) {
			return bits.join('_');
		}
		if (i >= 1  && i > delIndex) {
			i --;
		}
		bits.splice(bits.length - 1, 1, i);
		var r = bits.join('_');
		this.options.element = r;
		return r;
	},

	_decreaseName: function (n, delIndex) {
		var namebits = n.split('][');
		var i = namebits[2].replace(']', '').toInt();
		if (i >= 1  && i > delIndex) {
			i --;
		}
		if (namebits.length === 3) {
			i = i + ']';
		}
		namebits.splice(2, 1, i);
		var r = namebits.join('][');
		return r;
	},
	
	select: function () {},
	focus: function () {}
});

/**
 * @author Rob
 * contains methods that are used by any element which manipulates files/folders
 */

	
var FbFileElement = new Class({
	
	Extends: FbElement,
	ajaxFolder: function ()
	{
		this.folderlist = [];
		if (typeOf(this.element) === 'null') {
			return;
		}
		var el = this.element.getParent('.fabrikElement');
		this.breadcrumbs = el.getElement('.breadcrumbs');
		this.folderdiv = el.getElement('.folderselect');
		this.slider = new Fx.Slide(this.folderdiv, {duration: 500});
		this.slider.hide();
		this.hiddenField = el.getElement('.folderpath');
		el.getElement('.toggle').addEvent('click', function (e) {
			new Event(e).stop();
			this.slider.toggle();
		}.bind(this));
		this.watchAjaxFolderLinks();
	},
	
		
	watchAjaxFolderLinks: function ()
	{
		this.folderdiv.getElements('a').addEvent('click', this.browseFolders.bindWithEvent(this));
		this.breadcrumbs.getElements('a').addEvent('click', this.useBreadcrumbs.bindWithEvent(this));
	},
	
		
	browseFolders: function (e) {
		e = new Event(e).stop();
		var a = $(e.target);
		this.folderlist.push(a.innerHTML);
		var dir = this.options.dir + this.folderlist.join(this.options.ds);
		this.addCrumb(a.innerHTML);
		this.doAjaxBrowse(dir);
	},
	
	useBreadcrumbs: function (e)
	{
		e = new Event(e).stop();
		var found = false;
		var a = $(e.target);
		var c = a.className;
		this.folderlist = [];
		var res = this.breadcrumbs.getElements('a').every(function (link) {
			if (link.className === a.className) {
				return false;
			}
			this.folderlist.push(a.innerHTML);
			return true;
		}, this);
		
		var home = [this.breadcrumbs.getElements('a').shift().clone(),
		this.breadcrumbs.getElements('span').shift().clone()];
		this.breadcrumbs.empty();
		this.breadcrumbs.adopt(home);
		this.folderlist.each(function (txt) {
			this.addCrumb(txt);
		}, this);
		var dir = this.options.dir + this.folderlist.join(this.options.ds);
		this.doAjaxBrowse(dir);
	},
	
	doAjaxBrowse: function (dir) {
		var url = Fabrik.liveSite + "index.php?option=com_fabrik&format=raw&controller=plugin&task=pluginAjax&plugin=fabrikfileupload&method=ajax_getFolders&element_id=" + this.options.id;
	
		new Request({ url: url,
			data: {'dir': dir},
			onComplete: function (r) {
				r = JSON.decode(r);
				this.folderdiv.empty();
				
				r.each(function (folder) {
					new Element('li', {'class': 'fileupload_folder'}).adopt(
					new Element('a', {'href': '#'}).set('text', folder)).inject(this.folderdiv);
				}.bind(this));
				if (r.length === 0) {
					this.slider.hide();
				} else {
					this.slider.slideIn();
				}
				this.watchAjaxFolderLinks();
				this.hiddenField.value =  '/' + this.folderlist.join('/') + '/';
				this.fireEvent('onBrowse');
			}.bind(this)
		}).send();
	},
	
		
	addCrumb: function (txt) {
		this.breadcrumbs.adopt(
		new Element('a', {'href': '#', 'class': 'crumb' + this.folderlist.length}).set('text', txt),
		new Element('span').set('text', ' / ')
		);
	}
});