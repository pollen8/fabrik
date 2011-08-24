var FbListInlineEdit = new Class({
	Extends:FbListPlugin,
	
	initialize: function(options) {
		this.parent(options);
		this.defaults = {};
		this.editors = {};
		this.inedit = false;
		//this.spinner = Fabrik.loader.getSpinner();
		this.addbutton = new Asset.image(Fabrik.liveSite+'media/com_fabrik/images/action_check.png', {
			'alt':'save',
			'class':''
		});
		this.cancelbutton = new Asset.image(Fabrik.liveSite+'media/com_fabrik/images/delete.png', {
			'alt':'delete',
			'class':''
		});
		head.ready(function() {
			//assigned in list.js fabrik3
			//this.list = $('list_' + this.options.listid);
			if (typeOf(this.getList().getForm()) == 'null') {
				return false;
			}
			this.listid = this.options.listid;
			this.setUp();
		}.bind(this));
		
		window.addEvent('fabrik.table.clearrows', function(){
			this.cancel();			
		}.bind(this));
		
		window.addEvent('fabrik.list.inlineedit.stopEditing', function(){
			this.stopEditing();
		}.bind(this))
		
		window.addEvent('fabrik.table.updaterows', function(){
			this.watchCells();
		}.bind(this))
		
		window.addEvent('fabrik.table.ini', function(){
			var table = Fabrik.blocks['list_'+this.options.listid];
			var formData = table.form.toQueryString().toObject();
			formData.format = 'raw';
      		var myFormRequest = new Request({'url':'index.php',
      			data: formData,
      			onSuccess: function(json){
      				json = Json.evaluate(json.stripScripts());
      				table.options.data = json.data;
      			}.bind(this)
	       }).send(); 
		}.bind(this))
	},
	
	setUp:function(){
		if(typeOf(this.getList().getForm()) == 'null'){
			return;
		}
		this.scrollFx = new Fx.Scroll(window, {
			'wait':false
		});
		this.watchCells();
		document.addEvent('keydown', this.checkKey.bindWithEvent(this));
	},

	watchCells:function(){
		var firstLoaded = false;
		this.getList().getForm().getElements('.fabrik_element').each(function(td, x){
			if (!firstLoaded && this.options.loadFirst) {
				firstLoaded = this.edit(null, td);
				if (firstLoaded) {
					this.select(null, td);
				}
			}
			if (!this.isEditable(td)) {
				return;
			}
			this.setCursor(td);
			td.removeEvents();
			td.addEvent(this.options.editEvent, this.edit.bindWithEvent(this, [td]));
			td.addEvent('click', this.select.bindWithEvent(this, [td]));
			if(this.canEdit(td)){
				td.addEvent('mouseenter', function(e){
					if(!this.isEditable(td)) {
						td.setStyle('cursor', 'pointer')
					}
					}.bind(this));
				td.addEvent('mouseleave', function(e){td.setStyle('cursor', '')});
			}
		}.bind(this));
	},
	
	checkKey: function(e){
		if (typeOf(this.td) !== 'element') {
			return;
		}
		switch(e.code){
			case 39:
				//right
				if(this.inedit) {
					return;
				}
				if (typeOf(this.td.getNext()) === 'element') {
					e.stop();
					this.select(e, this.td.getNext());
				}
				break;
			case 9:
				//tab
				if(this.inedit) {
					if(this.options.tabSave) {
						if (typeOf(this.editing) === 'element') {
							this.save(e, this.editing);
						} else {
							this.edit(e, this.td);
						}
					}
					//var next = e.shift ? this.td.getPrevious() : this.td.getNext();
					var next = e.shift ? this.getPreviousEditable(this.td) : this.getNextEditable(this.td);
					if (typeOf(next) === 'element') {
						e.stop();
						this.select(e, next);
						this.edit(e, this.td);
					}
					return;
				}
				e.stop();
				if(e.shift){
					if (typeOf(this.td.getPrevious()) === 'element') {
						this.select(e, this.td.getPrevious());
					}
				}else{
					if (typeOf(this.td.getNext()) === 'element') {
						this.select(e, this.td.getNext());
					}
				}
				break;
			case 37: //left
				if(this.inedit) {
					return;
				}
				if (typeOf(this.td.getPrevious()) === 'element') {
					e.stop();
					this.select(e, this.td.getPrevious());
				}
				break;
			case 40:
				//down
				if(this.inedit) {
					return;
				}
				var row = this.td.getParent();
				if(typeOf(row) === 'null'){
					return;
				}
				var index = row.getElements('td').indexOf(this.td);
				if (typeOf(row.getNext()) === 'element') {
					e.stop();
					var nexttds = row.getNext().getElements('td');
					this.select(e, nexttds[index]);
				}
				break;
			case 38:
				//up
				if(this.inedit) {
					return;
				}
				var row = this.td.getParent();
				if(typeOf(row) === 'null'){
					return;
				}
				var index = row.getElements('td').indexOf(this.td);
				if (typeOf(row.getPrevious()) === 'element') {
					e.stop();
					var nexttds = row.getPrevious().getElements('td');
					this.select(e, nexttds[index]);
				}
				break;
			case 27:
				//escape
				e.stop();
				this.select(e, this.editing);
				this.cancel(e);
				break;
			case 13:
				//enter
				e.stop();
				if (typeOf(this.editing) === 'element') {
					this.save(e, this.editing);
				} else {
					this.edit(e, this.td);
				}
				break;
		}
	},
	
	select: function(e, td) {
		if (!this.isEditable(td)) {
			return;
		}
		var element = this.getElementName(td);
		var opts = this.options.elements[element];
		if(typeOf(opts) === false) {
			return;
		}
		if(typeOf(this.td) === 'element'){
			this.td.removeClass(this.options.focusClass);
		}
		this.td = td;
		if (typeOf(this.td) === 'element') {
			this.td.addClass(this.options.focusClass);
		}
		if (typeOf(this.td) === 'null'){
			return;
		}
		var p = this.td.getPosition();

		var x = p.x - (window.getSize().x/2) - (this.td.getSize().x / 2);
		var y = p.y - (window.getSize().y/2) + (this.td.getSize().y / 2);
		this.scrollFx.start(x, y);
	},
	
	getElementName: function(td){
		var c = td.className.split(' ').filter(function(item, index) {
			return item !== 'fabrik_element' && item !== 'fabrik_row';
		});
		var element = c[0].replace('fabrik_row___', '');
		return element;
	},
	
	setCursor: function(td){
		var element = this.getElementName(td);
		var opts = this.options.elements[element];
		if(typeOf(opts) === 'null'){
			return;
		}
		td.addEvent('mouseover', function(e){
			if (this.isEditable(e.target)) {
				e.target.setStyle('cursor', 'pointer');
			}
		});
		td.addEvent('mouseleave', function(e){
			if (this.isEditable(e.target)) {
				e.target.setStyle('cursor', '');
			}
		});
	},
	
	isEditable: function(cell){
		if (cell.hasClass('fabrik_uneditable') || cell.hasClass('fabrik_ordercell') || cell.hasClass('fabrik_select')) {
			return false;
		}
		return true;
	},
	
	getPreviousEditable:function(active){
		var found = false;
		var tds = this.getList().getForm().getElements('.fabrik_element');
		for(var i=tds.length; i>=0; i--){
			if (found) {
				if(this.canEdit(tds[i])){
					return tds[i];
				}
			}
			if(tds[i] === active){
				found = true;
			}
		}
		return false;
	},
	
	getNextEditable:function(active){
		var found = false;
		var next = this.getList().getForm().getElements('.fabrik_element').filter(function(td, i){
			if (found) {
				if (this.canEdit(td)) {
					found = false;
					return true;
				} 
			}
			if (td === active) {
				found = true;
			}
			return false;
		}.bind(this));
		return next.getLast();
	},
	
	canEdit:function(td){
		if (!this.isEditable(td)) {
			return false;
		}
		var element = this.getElementName(td);
		var opts = this.options.elements[element];
		if(typeOf(opts) === 'null'){
			return false;
		}
		return true;
	},
	
	edit: function(e, td) {
		//only one field can be edited at a time
		if (this.inedit) {
			return;
		}
		if (!this.canEdit(td)){
			return false;
		}
		if(typeOf(e) !== 'null'){
			e.stop();
		}
		var element = this.getElementName(td);
		var rowid = td.findClassUp('fabrik_row').id.replace('list_' + this.list.id + '_row_', '');
		//var url = Fabrik.liveSite + 'index.php?option=com_fabrik&task=element.display&format=raw';
		var url = 'index.php?option=com_fabrik&task=element.display&format=raw';
		var opts = this.options.elements[element];
		if(typeOf(opts) === 'null'){
			return;
		}
		this.inedit = true;
		this.editing = td;
		this.defaults[rowid+'.'+opts.elid] = td.innerHTML;
		
		var data = this.getDataFromTable(td);
		if (typeOf(this.editors[opts.elid]) === 'null' || typeOf(Fabrik['inlineedit_'+opts.elid]) == 'null') {
			//td.empty().adopt(this.spinner);
			Fabrik.loader.start(td);
			new Request({
				'evalScripts' :function(script, text){
						this.javascript = script;
					}.bind(this),
				'evalResponse':false,
				'url':url,
				'data':{
					'element':element,
					'elid':opts.elid,
					'plugin':opts.plugin,
					'rowid':rowid,
					'listid':this.options.listid,
					'inlinesave':this.options.showSave,
					'inlinecancel':this.options.showCancel
				},

				'onComplete':function(r){
					Fabrik.loader.stop(td);
					//don't use evalScripts = true as we reuse the js when tabbing to the next element. 
					// so instead set evalScripts to a function to store the js in this.javascript.
					//Previously js was wrapped in delay
					//but now we want to use it with and without the delay
	
					//delay the script to allow time for the dom to be updated
					(function(){
						$exec(this.javascript);
					}.bind(this)).delay(1000);
					td.empty().set('html', 	r);
					r = r+'<script type="text/javascript">'+this.javascript+'</script>';
					this.editors[opts.elid] = r;
					this.watchControls(td);
					this.setFocus(td);
					
				}.bind(this)
			}).send();
		} else {
			//testing trying to re-use old form
			this.javascript;
			var html = this.editors[opts.elid].stripScripts(function(script){
				this.javascript = script;
			}.bind(this));
		
			td.empty().set('html', html);
			//make a new instance of the element js class which will use the new html
			$exec(this.javascript);
			//tell the element obj to update its value
			///triggered from element model
			window.addEvent('fabrik.list.inlineedit.setData', function(){
				Fabrik['inlineedit_'+opts.elid].update(data);
				Fabrik['inlineedit_'+opts.elid].select();
				this.watchControls(td);
				this.setFocus(td);	
			}.bind(this));
			
		}
		return true;
	},
	
	getDataFromTable: function(td)
	{
		var groupedData = Fabrik.blocks['list_'+this.options.listid].options.data;
		var element = this.getElementName(td);
		var ref = td.findClassUp('fabrik_row').id;
		var v = false;
		this.vv = [];
		// $$$rob $H needed when group by applied
		//$H(groupedData).each(function(data){
		groupedData.each(function(data){
			if (typeOf(data) == 'array') {//groued by data in forecasting slotenweb app. Where groupby table plugin applied to data.
				for(var i  =0; i < data.length; i++) {
					if (data[i].id === ref) {
						this.vv.push(data[i]);
					}
				};
			} else {
				var vv = data.filter(function(row){
					return row.id === ref;
				});
			}
		}.bind(this));
		if (this.vv.length > 0) {
			v = this.vv[0].data[element+'_raw'];
		}
		return v;
	},
	
	setTableData:function(row, element, val){
		ref = row.id;
		var groupedData = Fabrik.blocks['list_'+this.options.listid].options.data;
		// $$$rob $H needed when group by applied
		$H(groupedData).each(function(data){
			data.each(function(row){
				if(row.id === ref){
					row.data[element] = val;
				}
			});
		});
	},
	setFocus : function(td){
		if(typeOf(td.getElement('.fabrikinput')) !== 'null') {
			td.getElement('.fabrikinput').focus();
		}
	},
	
	watchControls : function(td) {
		if(typeOf(td.getElement('a.inline-save')) !== 'null') {
			td.getElement('a.inline-save').addEvent('click',  this.save.bindWithEvent(this, [td]));
		}
		if(typeOf(td.getElement('a.inline-cancel')) !== 'null') {
			td.getElement('a.inline-cancel').addEvent('click',  this.cancel.bindWithEvent(this, [td]));
		}
	},
	
	save: function(e, td) {
		window.fireEvent('fabrik.table.updaterows');
		this.inedit = false;
		e.stop();
		var element = this.getElementName(td);
		//var url = Fabrik.liveSite + 'index.php?option=com_fabrik&task=element.save&format=raw';
		var url = 'index.php?option=com_fabrik&task=element.save&format=raw';
		var opts = this.options.elements[element];
		var row = this.editing.findClassUp('fabrik_row');
		var rowid = row.id.replace('list_'+this.list.id + '_row_', '');
		td.removeClass(this.options.focusClass);
		//var eObj = eval('inlineedit_' + opts.elid);
		var eObj = Fabrik['inlineedit_'+opts.elid];
		if (typeOf(eObj) === 'null') {
			fconsole('issue saving from inline edit: eObj not defined');
			this.cancel(e);
			return false;
		}
		delete eObj.element;
		eObj.getElement();
		var value = eObj.getValue();
		var k = 'value';

		this.setTableData(row, element, value);
		var data = {
			'element':element,
			'elid':opts.elid,
			'plugin':opts.plugin,
			'rowid':rowid,
			'listid':this.options.listid
		};
		data[eObj.token]  = 1;
		data[k] = value;
		new Request({url:url,
			'data':data,
			'evalScripts':true,
			'onComplete':function(r){
				td.empty().set('html',r);
			}.bind(this)
		}).send();
		this.editing = null;
	},
	
	stopEditing: function(e) {
		var td = this.editing;
		if (td !== false) {
			td.removeClass(this.options.focusClass);
		}
		this.editing = null;
		this.inedit = false;
	},
	
	cancel: function(e) {
		if(e) {
			e.stop();
		}
		if(typeOf(this.editing) !== 'element') {
			return;
		}
		var row = this.editing.findClassUp('fabrik_row');
		if (row !== false) {
			var rowid = row.id.replace('list_'+this.getList().id + '_row_', '');
		}
		var td = this.editing;
		if (td !== false) {
			//td.removeClass(this.options.focusClass);
			var element = this.getElementName(td);
			var opts = this.options.elements[element];
			var c = this.defaults[rowid+'.'+opts.elid];
			td.set('html',c);
		}
		this.stopEditing();
	}
});