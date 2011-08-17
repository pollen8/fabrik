 /**
 * @author Robert
 */

var FbForm = new Class( {

	Implements:[Options, Events, Plugins],
	
	options:{
		'admin':false,
		'ajax':false,
		'primaryKey':null,
		'error':'',
		'delayedEvents':false,
		'updatedMsg':'Form saved',
		'pages':[],
		'start_page':0,
		'ajaxValidation':false,
		'customJsAction':'',
		'plugins':[],
		'ajaxmethod':'post',
		'inlineMessage':true,
		'images':{
			'alert':'',
			'action_check':'',
			'ajax_loader':''
		}
	},
	
	initialize : function(id, options) {
		this.id = id;
		this.setOptions(options);
		this.plugins = this.options.plugins;
		this.options.pages = $H(this.options.pages);
		this.subGroups = $H({});
		this.currentPage = this.options.start_page;
		this.formElements = $H({});
		this.bufferedEvents = $A([]);
		this.duplicatedGroups = $H({});
		this.clickDeleteGroup = this.deleteGroup.bindWithEvent(this);
		this.clickDuplicateGroup = this.duplicateGroup.bindWithEvent(this);
	
		this.fx = {};
		this.fx.elements = [];
		this.fx.validations = {};
		head.ready(function() {
		 this.setUpAll()
		}.bind(this));	
	},
	
	setUpAll: function()
	{
		this.setUp();
		this.winScroller = new Fx.Scroll(window);
		this.watchAddOptions();
		$H(this.options.hiddenGroup).each(function(v, k){
			if(v == true && typeOf(document.id('group'+k)) !== 'null'){
				var subGroup = document.id('group'+k).getElement('.fabrikSubGroup');
				this.subGroups.set(k, subGroup.cloneWithIds());
				this.hideLastGroup(k, subGroup);
			}
		}.bind(this));

		// get an int from which to start incrementing for each repeated group id
		// dont ever decrease this value when deleteing a group as it will cause all sorts of
		// reference chaos with cascading dropdowns etc
		this.repeatGroupMarkers = $H({});
		this.form.getElements('.fabrikGroup').each(function(group) {
			var id = group.id.replace('group', '');
			var c = group.getElements('.fabrikSubGroup').length;
			this.repeatGroupMarkers.set(id, c);
		}.bind(this));

		// testing prev/next buttons
		var v = this.options.editable === true ? 'form' : 'details';
		var editopts = {
			option : 'com_fabrik',
			'view' : v,
			'controller' : 'form',
			'task' : 'getNextRecord',
			'fabrik' : this.id,
			'rowid' : this.form.getElement('input[name=rowid]').value,
			'format' : 'raw',
			'task' : 'paginate',
			'dir' : 1
		};
		[ '.previous-record', '.next-record' ].each(function(b, dir) {
			editopts.dir = dir;
			if (this.form.getElement(b)) {

				var myAjax = new Request({
					url : 'index.php',
					method : this.options.ajaxmethod,
					data : editopts,
					onComplete : function(r) {
						Fabrik.loader.stop(null, this.options.inlineMessage);
						r = JSON.decode(r);
						this.update(r);
						this.form.getElement('input[name=rowid]').value = r.post.rowid;
					}.bind(this)
				});

				this.form.getElement(b).addEvent('click', function(e) {
					myAjax.options.data.rowid = this.form.getElement('input[name=rowid]').value;
					e.stop();
					Fabrik.loader.start('loading', this.options.inlineMessage);
					myAjax.send();
				}.bind(this));
			}
		}.bind(this));
	},

	watchAddOptions : function() {
		this.fx.addOptions = [];
		this.getForm().getElements('.addoption').each( function(d) {
			var a = d.getParent().getElement('.toggle-addoption');
			var mySlider = new Fx.Slide(d, {
				duration :500
			});
			mySlider.hide();
				a.addEvent('click', function(e) {
					e.stop();
					mySlider.toggle();
			});
		});
	},

	setUp : function() {
		this.form = this.getForm();
		this.watchGroupButtons();
		if (this.options.editable) {
			this.watchSubmit();
		}
		this.createPages();
		this.watchClearSession();
	},

	getForm : function() {
		this.form = document.id(this.getBlock());
		return this.form;
	},

	getBlock : function() {
		return this.options.editable == true ? 'form_' + this.id : 'details_' + this.id;
	},

	// id is the element or group to apply the fx TO, triggered from another
	// element
	addElementFX : function(id, method) {
		id = id.replace('fabrik_trigger_', '');
		if (id.slice(0, 6) == 'group_') {
			id = id.slice(6, id.length);
			var k = id;
			var c = $(id);
		} else {
			id = id.slice(8, id.length);
			k = 'element' + id;
			if (!document.id(id)) {
				return;
			}
			c = document.id(id).findClassUp('fabrikElementContainer');
		}
		if (c) {
			// c will be the <li> element - you can't apply fx's to this as it makes the
			// DOM squiffy with
			// multi column rows, so get the li's content and put it inside a div which
			// is injected into c
			// apply fx to div rather than li - damn im good
			if ((c).get('tag') == 'li') {
				var fxdiv = new Element('div').adopt(c.getChildren());
				c.empty();
				fxdiv.inject(c);
			} else {
				fxdiv = c;
			}

			var opts = {
				duration : 800,
				transition : Fx.Transitions.Sine.easeInOut
			};
			this.fx.elements[k] = {};
			this.fx.elements[k].css = fxdiv.effect('opacity', opts);
			if (typeOf(fxdiv) !== 'null' && (method == 'slide in' || method == 'slide out' || method == 'slide toggle')) {
				this.fx.elements[k]['slide'] = new Fx.Slide(fxdiv, opts);
			} else {
				this.fx.elements[k]['slide'] = null;
			}
		}
	},

	doElementFX : function(id, method) {
		id = id.replace('fabrik_trigger_', '');
		if (id.slice(0, 6) == 'group_') {
			id = id.slice(6, id.length);
			// wierd fix?
			if (id.slice(0, 6) == 'group_')
				id = id.slice(6, id.length);
			var k = id;
			var groupfx = true;
		} else {
			groupfx = false;
			id = id.slice(8, id.length);
			k = 'element' + id;
		}
		var fx = this.fx.elements[k];
		if (!fx) {
			return;
		}
		var fxElement = groupfx ? fx.css.element : fx.css.element.findClassUp('fabrikElementContainer');
		switch (method) {
			case 'show':
				fxElement.removeClass('fabrikHide');
				fx.css.set(1);
				fx.css.element.show();
				if (groupfx) {
					// strange fix for ie8
					// http://fabrik.unfuddle.com/projects/17220/tickets/by_number/703?cycle=true
					document.id(id).getElements('.fabrikinput').setStyle('opacity', '1');
				}
				break;
			case 'hide':
				fxElement.addClass('fabrikHide');
				fx.css.set(0);
				fx.css.element.hide();
				break;
			case 'fadein':
				fxElement.removeClass('fabrikHide');
				if (fx.css.lastMethod !== 'fadein') {
					fx.css.element.show();
					fx.css.start(0, 1);
				}
				break;
			case 'fadeout':
				if (fx.css.lastMethod !== 'fadeout') {
					fx.css.start(1, 0).chain(function() {
						fx.css.element.hide();
						fxElement.addClass('fabrikHide');
					});
				}
				break;
			case 'slide in':
				fx.slide.slideIn();
				break;
			case 'slide out':
				fx.slide.slideOut();
				fxElement.removeClass('fabrikHide');
				break;
			case 'slide toggle':
				fx.slide.toggle();
				break;
		}
		fx.lastMethod = method;
		this.runPlugins('onDoElementFX', null);
	},

	watchClearSession : function() {
		if (this.form && this.form.getElement('.clearSession')) {
			this.form.getElement('.clearSession').addEvent('click', function(e) {
				e.stop();
				this.form.getElement('input[name=task]').value = 'removeSession';
				this.clearForm();
				this.form.submit();
			}.bind(this));
		}
	},

	createPages : function() {
		if (this.options.pages.getKeys().length > 1) {
			// wrap each page in its own div
			this.options.pages.each(function(page, i) {
				var p = new Element('div', {
					'class' : 'page',
					'id' : 'page_' + i
				});
				p.inject(document.id('group' + page[0]), 'before');
				page.each(function(group) {
					p.adopt(document.id('group' + group));
				});
			});
			var submit = this._getButton('submit');
			if (submit && this.options.rowid == '') {
				submit.disabled = "disabled";
				submit.setStyle('opacity', 0.5);
			}
			this.form.getElement('.fabrikPagePrevious').disabled = "disabled";
			this.form.getElement('.fabrikPageNext').addEvent('click', this._doPageNav.bindWithEvent(this, [ 1 ]));
			this.form.getElement('.fabrikPagePrevious').addEvent('click', this._doPageNav.bindWithEvent(this, [ -1 ]));
			this.setPageButtons();
			this.hideOtherPages();
		}
	},

	_doPageNav : function(e, dir) {
		if (this.options.editable) {
			this.form.getElement('.fabrikMainError').addClass('fabrikHide');
			//if tip shown at bottom of long page and next page shorter we need to move the tip to
			//the top of the page to avoid large space appearing at the bottom of the page.
			if(typeOf(document.getElement('.tool-tip')) !== 'null'){
				document.getElement('.tool-tip').setStyle('top', 0);
			}
			var url = Fabrik.liveSite
				+ 'index.php?option=com_fabrik&format=raw&task=form.ajax_validate&form_id='
				+ this.id;
			Fabrik.loader.start('validating', this.options.inlineMessage);
	
			// only validate the current groups elements, otherwise validations on
			// other pages cause the form to show an error.
	
			var groupId = this.options.pages.get(this.currentPage.toInt());
	
			var d = $H(this.getFormData());
			//d.set('view', 'form');
			d.set('task', 'form.ajax_validate');
			d.set('fabrik_ajax', '1');
			d.set('format', 'raw');
	
			d = this._prepareRepeatsForAjax(d);

			var myAjax = new Request({
				'url':url,
				method :this.options.ajaxmethod,
				data :d,
				onComplete : function(r) {
					Fabrik.loader.stop(null, this.options.inlineMessage);
					r = JSON.decode(r);
					if (this._showGroupError(r, d) == false) {
						this.changePage(dir);
						this.saveGroupsToDb();
					}
					new Fx.Scroll(window).toElement(this.form);
				}.bind(this)
			}).send();
		}
		else {
			this.changePage(dir);
		}
		e.stop();
	},

	saveGroupsToDb : function() {
		if (this.options.multipage_save !== true) {
			return;
		}
		if (!this.runPlugins('saveGroupsToDb', null)) {
			return;
		}
		var orig = this.form.getElement('input[name=format]').value;
		var origprocess = this.form.getElement('input[name=task]').value;
		this.form.getElement('input[name=format]').value = 'raw';
		this.form.getElement('input[name=task]').value = 'savepage';

		var url = Fabrik.liveSite + 'index.php?option=com_fabrik&format=raw&page=' + this.currentPage;
		Fabrik.loader.start('saving page', this.options.inlineMessage);
		var data = this.getFormData();
		new Request({
			url:url,
			method : this.options.ajaxmethod,
			data : data,
			onComplete : function(r) {
				if (!this.runPlugins('onCompleteSaveGroupsToDb', null)) {
					return;
				}
				this.form.getElement('input[name=format]').value = orig;
				this.form.getElement('input[name=task]').value = origprocess;
				if (this.options.ajax) {
					window.fireEvent('fabrik.form.submitted', json);
				}
				Fabrik.loader.stop(null, this.options.inlineMessage);
			}.bind(this)
		}).send();
	},

	changePage : function(dir) {
		this.changePageDir = dir;
		if (!this.runPlugins('onChangePage', null)) {
			return;
		}
		this.currentPage = this.currentPage.toInt();
		if (this.currentPage + dir >= 0 && this.currentPage + dir < this.options.pages.getKeys().length) {
			this.currentPage = this.currentPage + dir;
			if (!this.pageGroupsVisible()) {
				this.changePage(dir);
			}
		}

		this.setPageButtons();
		document.id('page_' + this.currentPage).setStyle('display', '');
		this.hideOtherPages();
		if (!this.runPlugins('onEndChangePage', null)) {
			return;
		}
	},

	pageGroupsVisible : function() {
		var visible = false;
		this.options.pages.get(this.currentPage).each(function(gid) {
			if (document.id('group' + gid).getStyle('display') != 'none') {
				visible = true;
			}
		});
		return visible;
	},

	/**
	 * hide all groups except those in the active page
	 */
	hideOtherPages : function() {
		this.options.pages.each(function(gids, i) {
			if (i != this.currentPage) {
				document.id('page_' + i).setStyle('display', 'none');
			}
		}.bind(this));
	},

	setPageButtons : function() {
		var submit = this._getButton('submit');
		var prev = this.form.getElement('.fabrikPagePrevious');
		var next =this.form.getElement('.fabrikPageNext');
		if (this.currentPage == this.options.pages.getKeys().length - 1) {
			if (typeOf(submit) !== 'null') {
				submit.disabled = "";
				submit.setStyle('opacity', 1);
			}
			next.disabled = "disabled";
			next.setStyle('opacity', 0.5);
		} else {
			if (typeOf(submit) !== 'null' && this.options.rowid == '') {
				submit.disabled = "disabled";
				submit.setStyle('opacity', 0.5);
			}
			next.disabled = "";
			next.setStyle('opacity', 1);
		}
		if (this.currentPage === 0) {
			prev.disabled = "disabled";
			prev.setStyle('opacity', 0.5);
		} else {
			prev.disabled = "";
			prev.setStyle('opacity', 1);
		}
	},

	addElements : function(a) {
		a = $H(a);
		a.each(function(elements, gid) {
			elements.each(function(el) {
				if (typeOf(el) !== 'null') {
					this.addElement(el, el.options.element, gid);
				}
			}.bind(this));
		}.bind(this));
		// $$$ hugh - moved attachedToForm calls out of addElement to separate loop, to fix forward reference issue,
		// i.e. calc element adding events to other elements which come after itself, which won't be in formElements
		// yet if we do it in the previous loop ('cos the previous loop is where elements get added to formElements)
		a.each(function(elements) {
			elements.each(function(el) {
				if (typeOf(el) !== 'null') {
					try {
						el.attachedToForm();
					} catch (err) {
						fconsole(el.options.element + ' attach to form:' + err );
					}
				}
			}.bind(this));
		}.bind(this));
		window.fireEvent('fabrik.form.elements.added', [this]);
	},

	addElement : function(oEl, elId, gid) {
		elId = elId.replace('[]', '');
		var ro = elId.substring(elId.length - 3, elId.length) === '_ro';
		oEl.form = this;
		oEl.groupid = gid;
		this.formElements.set(elId, oEl);
		// $$$ hugh - moved this to addElements, see comment above
		/*
		try {
			oEl.attachedToForm();
		} catch (err) {
			fconsole(elId + ' attach to form:' + err );
		}
		*/
		if (ro) {
			elId = elId.substr(0, elId.length - 3);
			this.formElements.set(elId, oEl);
		}
	},

	// we have to buffer the events in a pop up window as
	// the dom inserted when the window loads appears after the ajax evalscripts

	dispatchEvent : function(elementType, elementId, action, js) {
		if (!this.options.delayedEvents) {
			var el = this.formElements.get(elementId);
			if (el && js != '') {
				el.addNewEvent(action, js);
			}
		} else {
			this.bufferEvent(elementType, elementId, action, js);
		}
	},

	bufferEvent : function(elementType, elementId, action, js) {
		this.bufferedEvents.push([ elementType, elementId, action, js ]);
	},

	// call this after the popup window has loaded
	processBufferEvents : function() {
		this.setUp();
		this.options.delayedEvents = false;
		this.bufferedEvents.each(function(r) {
			// refresh the element ref
			var elementId = r[1];
			var el = this.formElements.get(elementId);
			el.element = document.id(elementId);
			this.dispatchEvent(r[0], elementId, r[2], r[3]);
		}.bind(this));
	},

	action : function(task, element) {
		var oEl = this.formElements.get(el);
		eval('oEl.' + task + '()');
	},

	triggerEvents : function(el) {
		this.formElements.get(el).fireEvents(arguments[1]);
	},

	/**
	 * @param string
	 *          element id to observe
	 * @param string
	 *          error div for element
	 * @param string
	 *          parent element id - eg for datetime's time field this is the date
	 *          fields id
	 */
	watchValidation : function(id, triggerEvent) {
		if (this.options.ajaxValidation == false) {
			return;
		}
		if (document.id(id).className == 'fabrikSubElementContainer') {
			// check for things like radio buttons & checkboxes
			document.id(id).getElements('.fabrikinput').each(function(i) {
				i.addEvent(triggerEvent, this.doElementValidation.bindWithEvent(this, [ true ]));
			}.bind(this));
			return;
		}
		document.id(id).addEvent(triggerEvent, this.doElementValidation.bindWithEvent(this, [ false ]));
	},

	// as well as being called from watchValidation can be called from other
	// element js actions, e.g. date picker closing
	doElementValidation : function(e, subEl, replacetxt) {
		if (this.options.ajaxValidation == false) {
			return;
		}
		replacetxt = typeOf(replacetxt) === 'null' ? '_time' : replacetxt;
		if (typeOf(e) == 'event' || typeOf(e) == 'object') { // type object in
			var id = e.target.id;
			// for elements with subelements eg checkboxes radiobuttons
			if (subEl == true) {
				id = document.id(e.target).findClassUp('fabrikSubElementContainer').id;
			}
		} else {
			// hack for closing date picker where it seems the event object isnt
			// available
			id = e;
		}
		// for elements with subelements eg checkboxes radiobuttons
		/*if (subEl == true) {
			id = $(e.target).findClassUp('fabrikSubElementContainer').id;
		}*/
		if(typeOf(document.id(id)) === 'null') {
			return;
		}
		if(document.id(id).getProperty('readonly') === true || document.id(id).getProperty('readonly') == 'readonly') {
			// stops date element being validated
			// return;
		}
		var el = this.formElements.get(id);
		if (!el) {
			//silly catch for date elements you cant do the usual method of setting the id in the 
			//fabrikSubElementContainer as its required to be on the date element for the calendar to work
			id = id.replace(replacetxt, '');
			el = this.formElements.get(id);
			if (!el) {
				return;
			}
		}
		if (!this.runPlugins('onStartElementValidation', e)) {
			return;
		}
		el.setErrorMessage(Joomla.JText._('COM_FABRIK_VALIDATING'), 'fabrikValidating');

		var d = $H(this.getFormData());
		d.set('task', 'form.ajax_validate');
		d.set('fabrik_ajax', '1');
		d.set('format', 'raw');

		d = this._prepareRepeatsForAjax(d);

		var origid = el.origId ? el.origId : id;
		el.options.repeatCounter = el.options.repeatCounter ? el.options.repeatCounter : 0;
		var url = Fabrik.liveSite + 'index.php?option=com_fabrik&form_id=' + this.id;
		var myAjax = new Request({
			url:url,
			method :this.options.ajaxmethod,
			data :d,
			onComplete :this._completeValidaton.bindWithEvent(this, [ id, origid ])
		}).send();
	},

	_completeValidaton : function(r, id, origid) {
		r = JSON.decode(r);
		if (!this.runPlugins('onCompleteElementValidation', null)) {
			return;
		}
		var el = this.formElements.get(id);
		if ((r.modified[origid] != undefined)) {
			el.update(r.modified[origid]);
		}
		if (typeOf(r.errors[origid]) !== 'null') {
			this._showElementError(r.errors[origid][el.options.repeatCounter], id);
		} else {
			this._showElementError([], id);
		}
	},

	_prepareRepeatsForAjax : function(d) {
		this.getForm();
		//ensure we are dealing with a simple object
		if (typeOf(d) === 'hash') {
			d = d.getClean();
		}
		//data should be key'd on the data stored in the elements name between []'s which is the group id
		this.form.getElements('input[name^=fabrik_repeat_group]').each(
				function(e) {
					var c = e.name.match(/\[(.*)\]/)[1];
					d['fabrik_repeat_group[' + c + ']'] = e.get('value');
				}
		);
		return d;
	},

	_showGroupError : function(r, d) {
		var gids = $A(this.options.pages.get(this.currentPage.toInt()));
		var err = false;
		$H(d).each(function(v, k) {
			k = k.replace(/\[(.*)\]/, '');// for dropdown validations
			if(this.formElements.has(k)){
				var el = this.formElements.get(k);
				if (gids.contains(el.groupid.toInt())) {
					if (r.errors[k]) {
					// prepare error so that it only triggers for real errors and not sucess
					// msgs
			
						var msg = '';
						if (typeOf(r.errors[k]) !== 'null') {
							msg = r.errors[k].flatten().join('<br />');
						}
						if (msg !== '') {
							tmperr = this._showElementError(r.errors[k], k);
							if (err == false) {
								err = tmperr;
							}
						}else{
							el.setErrorMessage('', '');
						}
					}
					if (r.modified[k]) {
						if (el) {
							el.update(r.modified[k]);
						}
					}
				}
			}
		}.bind(this));

		return err;
	},

	_showElementError : function(r, id) {
		// r should be the errors for the specific element, down to its repeat group
		// id.
		var msg = '';
		if (typeOf(r) !== 'null') {
			msg = r.flatten().join('<br />');
		}
		var classname = (msg === '') ? 'fabrikSuccess' : 'fabrikError';
		if (msg === '')
			msg = Joomla.JText._('COM_FABRIK_SUCCESS');
		this.formElements.get(id).setErrorMessage(msg, classname);
		return (classname === 'fabrikSuccess') ? false : true;
	},

	updateMainError : function() {
		var mainEr = this.form.getElement('.fabrikMainError');
		mainEr.set('html',this.options.error);
		var activeValidations = this.form.getElements('.fabrikError').filter(
				function(e, index) {
			return !e.hasClass('fabrikMainError');
		});
		if (activeValidations.length > 0 && mainEr.hasClass('fabrikHide')) {
			mainEr.removeClass('fabrikHide');
			var myfx = new Fx.Tween(mainEr, {property:'opacity',
				duration : 500
			}).start(0, 1);
		}
		if (activeValidations.length === 0) {
			myfx = new Fx.Tween(mainEr, {property:'opacity',
				duration : 500,
				onComplete : function() {
					mainEr.addClass('fabrikHide');
				}
			}).start(1, 0);
		}
	},
	
	/** @since 3.0 get a form button name */
	_getButton : function(name){
		return this.form.getElement('input[type=button][name='+name+']'); 
	},

	watchSubmit : function() {
		var submit = this._getButton('submit');
		if (!submit) {
			return;
		}
		var apply = this._getButton('apply');
		if (this.form.getElement('input[name=delete]')) {
			this.form.getElement('input[name=delete]').addEvent('click', function(e) {
				if (confirm(Joomla.JText._('COM_FABRIK_CONFIRM_DELETE'))) {
					this.form.getElement('input[name=task]').value = 'delete';
				} else {
					return false;
				}
			}.bind(this));
		}
		if (this.options.ajax) {
			$A([apply, submit]).each(function(btn){
				if (typeOf(btn) !== 'null') {
					btn.addEvent('click', this.doSubmit.bindWithEvent(this, [btn]));
				}
			}.bind(this));
			
		}
		this.form.addEvent('submit', this.doSubmit.bindWithEvent(this));
	},

	doSubmit : function(e, btn) {
		var ret = this.runPlugins('onSubmit', e);
		this.elementsBeforeSubmit(e);
		if (ret == false) {
			e.stop();
			// update global status error
			this.updateMainError();
		}
		//insert a hidden element so we can reload the last page if validation vails
		if (this.options.pages.getKeys().length > 1) {
			this.form.adopt(new Element('input', {'name':'currentPage','value':this.currentPage.toInt(), 'type':'hidden'}));
		}
		if (ret) {
			if (this.options.ajax) {
				//do ajax val only if onSubmit val ok
				if (this.form) {
					Fabrik.loader.start(Joomla.JText._('COM_FABRIK_LOADING'), this.options.inlineMessage);
					this.elementsBeforeSubmit(e);
					// get all values from the form
					var data = $H(this.getFormData());
					data = this._prepareRepeatsForAjax(data);
					data.fabrik_ajax = '1';
					data.format = 'raw';
					var myajax = new Request.JSON({
						'url' : this.form.action,
						'data' : data,
						'method' : this.options.ajaxmethod,
						onError : function(text, error){
							fconsole(text);
						},
						
						onComplete  : function(json, txt) {
							if (typeOf(json) === 'null') {
								// stop spinner
								fconsole('error in returned json', json, txt);
								return;
							}
							// process errors if there are some
							var errfound = false;
							if (json.errors != undefined) {
								// for every element of the form update error message
								$H(json.errors).each(function(errors, key) {
									// replace join[id][label] with join___id___label
									key = key.replace(/(\[)|(\]\[)/g, '___').replace(/\]/, '');
									if (this.formElements.has(key) && errors.flatten().length > 0) {
										errfound = true;
										this._showElementError(errors, key);
									}
									;
								}.bind(this));
	
								// this.runPlugins('onAjaxSubmitComplete'); don't run it I guess
							}
							// update global status error
							this.updateMainError();
	
							if (errfound === false) {
								var keepOverlay = btn.name == 'apply' ? true : false;
								//keepOverlay -works but is hdiden afterwards
								Fabrik.loader.stop(null, this.options.inlineMessage, keepOverlay);
								var saved_msg = $defined(json.msg) ? json.msg :Joomla.JText._('COM_FABRIK_FORM_SAVED');
								if (json.baseRedirect !== true) {
									if ($defined(json.url)) {
										Fabrik.getWindow({'id':'redirect', 'type':'redirect', contentURL:json.url, caller:this.getBlock(), 'height':400});
									}else{
										alert(saved_msg);
									}
								}
								//query the list to get the updated data
								window.fireEvent('fabrik.form.submitted', [this, json]);
								
								this.runPlugins('onAjaxSubmitComplete', e);
								if (btn.name !=='apply') {
									this.clearForm();
									//if the form was loaded in a Fabrik.Window close the window.
									if (Fabrik.Windows[this.options.fabrik_window_id]) {
										Fabrik.Windows[this.options.fabrik_window_id].close();
									}
								}
							} else {
								// stop spinner
								Fabrik.loader.stop(Joomla.JText._('COM_FABRIK_VALIDATION_ERROR'), this.options.inlineMessage);
							}
						}.bind(this)
					}).send();
				}
			}
			else {
				var end_ret = this.runPlugins('onSubmitEnd', e);
				if (end_ret == false) {
					e.stop();
					// update global status error
					this.updateMainError();
				}
			}
		}
	},

	elementsBeforeSubmit : function(e) {
		this.formElements.each(function(el, key) {
			if (!el.onsubmit()) {
				e.stop();
			}
		});
	},

	// used to get the querystring data and
	// for any element overwrite with its own data definition
	// required for empty select lists which return undefined as their value if no
	// items
	// available

	getFormData : function() {
		this.getForm();
		var s = this.form.toQueryString();
		var h = {};
		s = s.split('&');
		var arrayCounters = $H({});
		s.each(function(p) {
			p = p.split('=');
			var k = p[0];
			// $$$ rob deal with checkboxes
			if (k.substring(k.length - 2) === '[]') {
				k = k.substring(0, k.length - 2);
				if (!arrayCounters.has(k)) {
					// rob for ajax validation on repeat element this is required to be set to 0
					arrayCounters.set(k, 0);
				} else {
					arrayCounters.set(k, arrayCounters.get(k) + 1);
				}
				k = k + '[' + arrayCounters.get(k) + ']';
			}
			h[k] = p[1];
		});

		// toQueryString() doesn't add in empty data - we need to know that for the
		// validation on multipages
		var elKeys = this.formElements.getKeys();
		this.formElements.each(function(el, key) {
			//fileupload data not included in querystring
			if (el.plugin == 'fabrikfileupload') {
				h[key] = el.get('value');
			}
			if (typeOf(h[key]) === 'null') {
				// search for elementname[*] in existing data (search for * as datetime
				// elements aren't keyed numerically)
				var found = false;
				$H(h).each(function(val, dataKey) {
					dataKey = unescape(dataKey); // 3.0 ajax submission [] are escaped
					dataKey = dataKey.replace(/\[(.*)\]/, '');
					if (dataKey == key) {
						found = true;
					}
				}.bind(this));
				if (!found) {
					h[key] = '';
				}
			}
		}.bind(this));
		return h;
	},

	// $$$ hugh - added this, so far only used by cascading dropdown JS
	// to populate 'data' for the AJAX update, so custom cascade 'where' clauses
	// can use {placeholders}. Initially tried to use getFormData for this, but because
	// it adds ALL the query string args from the page, the AJAX call from cascade ended
	// up trying to submit the form. So, this func does what the commented out code in
	// getFormData used to do, and only fecthes actual form element data.

	getFormElementData : function() {
		var h = {};
		this.formElements.each(function(el, key) {
			if (el.element) {
				h[key] = el.getValue();
				h[key + '_raw'] = h[key];
			}
		}.bind(this));
		return h;
	},

	watchGroupButtons : function() {
		this.unwatchGroupButtons();
		this.form.getElements('.deleteGroup').each(function(g, i) {
			g.addEvent('click', this.clickDeleteGroup);
		}.bind(this));
		this.form.getElements('.addGroup').each(function(g, i) {
			g.addEvent('click', this.clickDuplicateGroup);
		}.bind(this));
		this.form.getElements('.fabrikSubGroup').each(function(subGroup) {
			var r = subGroup.getElement('.fabrikGroupRepeater');
			if (r) {
				subGroup.addEvent('mouseenter', function(e) {
					r.fade(1);
				});
				subGroup.addEvent('mouseleave', function(e) {
					r.fade(0.2);
				});
			}
		});
	},

	unwatchGroupButtons : function() {
		this.form.getElements('.deleteGroup').each(function(g, i) {
			g.removeEvent('click', this.clickDeleteGroup);
		}.bind(this));
		this.form.getElements('.addGroup').each(function(g, i) {
			g.removeEvent('click', this.clickDuplicateGroup);
		}.bind(this));
		this.form.getElements('.fabrikSubGroup').each(function(subGroup) {
			subGroup.removeEvents('mouseenter');
			subGroup.removeEvents('mouseleave');
		});
	},

	deleteGroup : function(e) {
		if (!this.runPlugins('onDeleteGroup', e)) {
			return;
		}
		e.stop();
		var group = e.target.findClassUp('fabrikGroup');
		// find which repeat group was deleted
		var delIndex = 0;
		group.getElements('.deleteGroup').each(function(b, x) {
			if (b.getElement('img') === e.target) {
				delIndex = x;
			}
		}.bind(this));
		var i = group.id.replace('group', '');
		delete this.duplicatedGroups.i;
		if(document.id('fabrik_repeat_group_' + i + '_counter').value == '0') {
			return;
		}
		var subgroups = group.getElements('.fabrikSubGroup');

		var subGroup = e.target.findClassUp('fabrikSubGroup');
		this.subGroups.set(i, subGroup.clone());
		if (subgroups.length <= 1) {
			this.hideLastGroup(i, subGroup);

		} else {

			var toel = subGroup.getPrevious();

			var myFx = new Fx.Tween(subGroup, {'property':'opacity',
				duration : 300,
				onComplete : function() {
					if (subgroups.length > 1) {
						subGroup.dispose();
					}

					this.formElements.each(function(e, k) {
						if (typeOf(e.element) !== 'null') {
							if(typeOf(document.id(e.element.id)) === 'null') {
								e.decloned(i);
								delete this.formElements.k;
							}
						}
					}.bind(this));
					
					subgroups = group.getElements('.fabrikSubGroup');// minus the removed
																														// group
					var nameMap = {};
					this.formElements.each(function(e, k) {
						if (e.groupid == i) {
							nameMap[k] = e.decreaseName(delIndex);
						}
					}.bind(this));
					// ensure that formElements' keys are the same as their object's ids
					// otherwise delete first group, add 2 groups - ids/names in last
					// added group are not updated
					$H(nameMap).each(function(newKey, oldKey) {
						if (oldKey !== newKey) {
							this.formElements[newKey] = this.formElements[oldKey];
							delete this.formElements[oldKey];
						}
					}.bind(this));
				}.bind(this)
			}).start(1, 0);
			if (toel) {
				// Only scroll the window if the previous element is not visible
				var win_scroll = $(window).getScroll().y;
				var obj = toel.getCoordinates();
				// If the top of the previous repeat goes above the top of the visible
				// window,
				// scroll down just enough to show it.
				if (obj.top < win_scroll) {
					var new_win_scroll = obj.top;
					this.winScroller.scrollTo(0, new_win_scroll);
				}
			}
		}
		// update the hidden field containing number of repeat groups
		document.id('fabrik_repeat_group_' + i + '_counter').value = document.id('fabrik_repeat_group_' + i + '_counter').get('value').toInt() - 1;
		this.repeatGroupMarkers.set(i, this.repeatGroupMarkers.get(i) - 1);
	},

	hideLastGroup : function(groupid, subGroup) {
		var sge = subGroup.getElement('.fabrikSubGroupElements');
		sge.setStyle('display', 'none');
		new Element('div', { 'class' :'fabrikNotice' }).appendText(Joomla.JText._('COM_FABRIK_NO_REPEAT_GROUP_DATA')).inject(sge, 'after');
	},

	isFirstRepeatSubGroup : function(group) {
		var subgroups = group.getElements('.fabrikSubGroup');
		return subgroups.length == 1 && subgroups[0].getElement('.fabrikNotice');
	},

	getSubGroupToClone : function(groupid) {
		var group = document.id('group' + groupid);
		var subgroup = group.getElement('.fabrikSubGroup');
		if (!subgroup) {
			subgroup = this.subGroups.get(groupid);
		}

		var clone = null;
		var found = false;
		if (this.duplicatedGroups.has(groupid)) {
			found = true;
		}
		if (!found) {
			clone = subgroup.cloneNode(true);
			this.duplicatedGroups.set(groupid, clone);
		} else {
			if (!subgroup) {
				clone = this.duplicatedGroups.get(groupid);
			} else {
				clone = subgroup.cloneNode(true);
			}
		}
		return clone;
	},

	repeatGetChecked : function(group) {
		// /stupid fix for radio buttons loosing their checked value
		var tocheck = [];
		group.getElements('.fabrikinput').each(function(i) {
			if (i.type == 'radio' && i.getProperty('checked')) {
				tocheck.push(i);
			}
		});
		return tocheck;
	},

	/* duplicates the groups sub group and places it at the end of the group */

	duplicateGroup : function(e) {
		if (!this.runPlugins('onDuplicateGroup', e)) {
			return;
		}
		if (e) e.stop();
		var i = e.target.findClassUp('fabrikGroup').id.replace('group', '');
		var group = document.id('group' + i);
		var c = this.repeatGroupMarkers.get(i);
		if (c >= this.options.maxRepeat[i] && this.options.maxRepeat[i] !== 0) {
			return ;
		}
		document.id('fabrik_repeat_group_' + i + '_counter').value = document.id('fabrik_repeat_group_' + i + '_counter').get('value').toInt() + 1;

		if (this.isFirstRepeatSubGroup(group)) {
			var subgroups = group.getElements('.fabrikSubGroup');
			// user has removed all repeat groups and now wants to add it back in
			// remove the 'no groups' notice
			subgroups[0].getElement('.fabrikNotice').dispose();
			subgroups[0].getElement('.fabrikSubGroupElements').show();
			this.repeatGroupMarkers.set(i, this.repeatGroupMarkers.get(i) + 1);
			return;
		}
		var clone = this.getSubGroupToClone(i);
		var tocheck = this.repeatGetChecked(group);

		group.appendChild(clone);
		tocheck.each(function(i) {
			i.setProperty('checked', true);
		});
		// remove values and increment ids
		var newElementControllers = [];
		this.subelementCounter = 0;
		var hasSubElements = false;
		var inputs = clone.getElements('.fabrikinput');
		var lastinput = null;
		this.formElements.each(function(el) {
			var formElementFound = false;
			subElementContainer = null;
			var subElementCounter = -1;
			inputs.each(function(input) {

				hasSubElements = el.hasSubElements();

				// for all instances of the call to findClassUp use el.element rather
				// than input (HMM SEE LINE 912 - PERHAPS WE CAN REVERT TO USING INPUT
				// NOW?)
				var testid = (hasSubElements) ? input.findClassUp('fabrikSubElementContainer').id : input.id;

				if (el.options.element == testid) {
					lastinput = input;
					formElementFound = true;

					if (hasSubElements) {
						subElementCounter++;
						// the line below meant that we updated the orginal groups id @ line
						// 942 - which in turn meant when we cleared the values we were
						// clearing the orignal elements values
						// not sure how this fits in with comments above which state we
						// should use el.element.findClassUp('fabrikSubElementContainer');
						// REAL ISSUE WAS THAT inputs CONTAINED ADD OPTIONS
						// (elementmodel->getAddOptionFields) WHICH HAD ELEMENTS WITH THE
						// CLASS fabrikinput THIS CLASS IS RESERVERED FOR ACTUAL DATA
						// ELEMENTS
						// subElementContainer =
						// el.element.findClassUp('fabrikSubElementContainer');

						subElementContainer = input.findClassUp('fabrikSubElementContainer');
						// clone the first inputs event to all subelements
						input.cloneEvents(document.id(testid).getElement('input'));

						// id set out side this each() function
					} else {
						input.cloneEvents(el.element);

						// update the element id use el.element.id rather than input.id as
						// that may contain _1 at end of id
						var bits = $A(el.element.id.split('_'));
						bits.splice(bits.length - 1, 1, c);
						input.id = bits.join('_');

						// update labels for non sub elements
						var l = input.findClassUp('fabrikElementContainer').getElement('label');
						if (l) {
							l.setProperty('for', input.id);
						}
					}

					input.name = input.name.replace('[0]', '[' + (c) + ']');
				}
			}.bind(this));
	
			if (formElementFound) {
				if (hasSubElements && typeOf(subElementContainer) !== 'null' ) {
					// if we are checking subelements set the container id after they have all
					// been processed
					// otherwise if check only works for first subelement and no further
					// events are cloned
					
					// $$$ rob fix for date element
					var bits = $A(el.options.element.split('_'));
					bits.splice(bits.length - 1, 1, c);
					subElementContainer.id = bits.join('_');
				}
				var origelid = el.options.element;
				// clone js element controller, set form to be passed by reference and
				// not cloned
				var ignore = el.unclonableProperties();
				var newEl = new CloneObject(el, true, ignore);

				newEl.container = null;
				newEl.options.repeatCounter = c;
				newEl.origId = origelid;
				
				if (hasSubElements && typeOf(subElementContainer) !== 'null' ) {
					newEl.element = document.id(subElementContainer);
					newEl.options.element = subElementContainer.id;
					newEl._getSubElements();
				} else {
					newEl.element = document.id(lastinput.id);
					newEl.options.element = lastinput.id;
				}
				newEl.reset();
				newElementControllers.push(newEl);
			}
		}.bind(this));

		newElementControllers.each(function(newEl) {
			newEl.cloned(c);
		});
		var o = {};
		o[i] = newElementControllers;
		this.addElements(o);

		// Only scroll the window if the new element is not visible
		var win_size = window.getHeight();
		var win_scroll = $(window).getScroll().y;
		var obj = clone.getCoordinates();
		// If the bottom of the new repeat goes below the bottom of the visible
		// window,
		// scroll up just enough to show it.
		if (obj.bottom > (win_scroll + win_size)) {
			var new_win_scroll = obj.bottom - win_size;

			this.winScroller.scrollTo(0, new_win_scroll);
		}

		var myFx = new Fx.Tween(clone, { 'property' : 'opacity', 
			duration : 500
		}).set(0);

		clone.fade(1);
		// $$$ hugh - added groupid (i) and repeatCounter (c) as args
		// note I commented out the increment of c a few lines above
		this.runPlugins('onDuplicateGroupEnd', e, i, c);
		this.repeatGroupMarkers.set(i, this.repeatGroupMarkers.get(i) + 1);
		this.unwatchGroupButtons();
		this.watchGroupButtons();
	},

	update : function(o) {
		if (!this.runPlugins('onUpdate', null)) {
			return;
		}
		var leaveEmpties = arguments[1] || false;
		var data = o.data;
		this.getForm();
		if (this.form) { // test for detailed view in module???
			var rowidel = this.form.getElement('input[name=rowid]');
			if (rowidel && data.rowid) {
				rowidel.value = data.rowid;
			}
		}
		this.formElements.each( function(el, key) {
			// if updating from a detailed view with prev/next then data's key is in
			// _ro format
			if (typeOf(data[key]) === 'null') {
				if (key.substring(key.length - 3, key.length) == '_ro') {
					key = key.substring(0, key.length - 3);
				}
			}
			// this if stopped the form updating empty fields. Element update()
			// methods
			// should test for null
			// variables and convert to their correct values
			// if (data[key]) {
			if (typeOf(data[key]) === 'null') {
				// only update blanks if the form is updating itself
				// leaveEmpties set to true when this form is called from updateRows
				if (o.id == this.id && !leaveEmpties) {
					el.update('');
				}
			} else {
				el.update(data[key]);
			}
		}.bind(this));
	},

	reset : function() {
		this.addedGroups.each(function(subgroup){
			subgroup.remove();
		});
		this.addedGroups = [];
		if (!this.runPlugins('onReset', null)) {
			return;
		}
		this.formElements.each(function(el, key) {
			el.reset();
		}.bind(this));
	},

	showErrors : function(data) {
		var d = null;
		if (data.id == this.id) {
			// show errors
			var errors = new Hash(data.errors);
			if (errors.getKeys().length > 0) {
				if (typeOf(this.form.getElement('.fabrikMainError')) !== 'null') {
					this.form.getElement('.fabrikMainError').set('html', this.options.error);
					this.form.getElement('.fabrikMainError').removeClass('fabrikHide');
				}
				errors.each(function(a, key) {
					if (typeOf(document.id(key + '_error')) !== 'null') {
						var e = document.id(key + '_error');
						var msg = new Element('span');
						for ( var x = 0; x < a.length; x++) {
							for ( var y = 0; y < a[x].length; y++) {
								d = new Element('div').appendText(a[x][y]).inject(e);
							}
						}
					} else {
						fconsole(key + '_error' + ' not found (form show errors)');
					}
				});
			}
		}
	},

	/** add additional data to an element - e.g database join elements */
	appendInfo : function(data) {
		this.formElements.each(function(el, key) {
			if (el.appendInfo) {
				el.appendInfo(data, key);
			}
		}.bind(this));
	},
/*
	addListenTo : function(blockId) {
		this.listenTo.push(blockId);
	},
*/
	clearForm : function() {
		this.getForm();
		if (!this.form) {
			return;
		}
		this.formElements.each(function(el, key) {
			if (key == this.options.primaryKey) {
				this.form.getElement('input[name=rowid]').value = '';
			}
			el.update('');
		}.bind(this));
		// reset errors
		this.form.getElements('.fabrikError').empty();
		this.form.getElements('.fabrikError').addClass('fabrikHide');
	}/*,

	receiveMessage : function(senderBlock, task, taskStatus, data) {
		if (this.listenTo.indexOf(senderBlock) != -1) {
			if (task == 'processForm') {

			}
			// a row from the table has been loaded
			if (task == 'update') {
				this.update(data);
			}
			if (task == 'clearForm') {
				this.clearForm();
			}
		}
		// a form has been submitted which contains data that should be updated in
		// this
		// form
		// currently for updating database join drop downs, data is used just as a
		// test to see if the dd needs
		// updating. If found a new ajax call is made from within the dd to update
		// itself
		// $$$ hugh - moved showErrors() so it only runs if data.errors has content
		if (task == 'updateRows' && typeOf(data) !== 'null') {
			if ($H(data.errors).getKeys().length === 0) {
				if (typeOf(data.data) !== 'null') {
					this.appendInfo(data);
					this.update(data, true);
				}
			} else {
				this.showErrors(data);
			}
		}
	}*/
});
