/**
 * @author Robert 
 */

var FbListPlugin = new Class({
	Implements:[Events, Options],
	options : {
		requireChecked:true
	},
	initialize: function(options) {
		this.setOptions(options);
		this.result = true; //set this to false in window.fireEvents to stop current action (eg stop ordering when fabrik.list.order run)
		head.ready(function() {
			this.listform = this.getList().getForm();
			this.watchButton();
		}.bind(this));
	},
	
	getList: function(){
		return Fabrik.blocks['list_'+this.options.listid];
	},
	
	clearFilter:Function.from(),
	
	watchButton: function() {
		var buttons = document.getElements('.'+this.options.name);
		if(!buttons || buttons.length == 0) {
			return;
		}
		buttons.addEvent('click', function(e) {
			e.stop();
			var row, chx;
			//if the row button is clicked check its associated checkbox
			if (row = e.target.findClassUp('fabrik_row')) {
				if(chx = row.getElement('input[name^=ids]')){
					chx.set('checked', true);
				}
			}
			//check that at least one checkbox is checked
			var ok = false;
			this.listform.getElements('input[name^=ids]').each( function(c) {
				if(c.checked) {
					ok = true;
				}
			});
			if(!ok && this.options.requireChecked) {
				alert(Joomla.JText._('COM_FABRIK_PLEASE_SELECT_A_ROW'));
				return;
			}
			var n = this.options.name.split('-');
			this.list.getForm().getElement('input[name=fabrik_listplugin_name]').value = n[0];
			this.list.getForm().getElement('input[name=fabrik_listplugin_renderOrder]').value = n.getLast();
			this.buttonAction();
		}.bind(this));
	},
	
	buttonAction:function(){
		this.list.submit('doPlugin');
	}
});

var FbListFilter = new Class({
 	
	Implements:[Options, Events],
	
	options:{
    'container': '',
		'type':'list',
		'id':''
  },
  
 	initialize:function(options){
 		this.filters = $H({});
 		this.setOptions(options);
		this.container = document.id(this.options.container);
		
		this.filterContainer = this.container.getElement('.fabrikFilterContainer');
		var b = this.container.getElement('.toggleFilters');
		if (typeOf(b) !== 'null') {
			b.addEvent('click', function(e){
				var dims = b.getPosition();
				e.stop();
				var x = dims.x - this.filterContainer.getWidth();
				var y = dims.y + b.getHeight();
				//this.filterContainer.setStyles({'position':'absolute', 'left':x+'px', 'top':y+'px'});
				this.filterContainer.getStyle('display') == 'none' ? this.filterContainer.show() : this.filterContainer.hide();
				this.filterContainer.fade('toggle');
			}.bind(this));
			
			if (typeOf(this.filterContainer) !== 'null') {
				this.filterContainer.fade('hide').hide();
			}
		}
		
		if(typeOf(this.container) === 'null'){
			return;
		}
		this.getList();
		var c = this.container.getElement('.clearFilters');
		if(typeOf(c) !== 'null'){
			c.removeEvents();
			c.addEvent('click', function(e){
				e.stop();
				this.container.getElements('.fabrik_filter').each(function(f){
					if(f.get('tag') == 'select'){
						f.selectedIndex = 0;
					}else{
						f.value = '';
					}
				});
				this.getList().plugins.each(function(p){
					p.clearFilter();
				})
				new Element('input', {'name':'resetfilters', 'value':1, 'type':'hidden', 'type':'hidden'}).inject(this.container);
				if (this.options.type == 'list') {
					this.list.submit('list.filter');
				}else{
					this.container.getElement('form[name=filter]').submit();
				}
			}.bind(this));
		}
 	},
 	
 	getList:function(){
 		this.list = Fabrik.blocks[this.options.type+'_'+this.options.id];
 		return this.list; 
 	},
 	
 	addFilter:function(plugin, f){
 		if(this.filters.has(plugin) === false){
 			this.filters.set(plugin, []);
 		}
 		this.filters.get(plugin).push(f);
 	},
 	
 	// $$$ hugh - added this primarily for CDD element, so it can get an array to
	// emulate submitted form data
 	// for use with placeholders in filter queries. Mostly of use if you have
	// daisy chained CDD's.
	getFilterData : function() {
		var h = {};
		this.container.getElements('.fabrik_filter').each(function(f){
			if (f.id.test(/value$/)) {
				var key = f.id.match(/(\S+)value$/)[1];
				// $$$ rob added check that something is select - possbly causes js
				// error in ie
				if (f.get('tag') == 'select' && f.selectedIndex !== -1) {
					h[key] = document.id(f.options[f.selectedIndex]).get('text');
				}
				else {
					h[key] = f.get('value');	
				}
				h[key + '_raw'] = f.get('value');
			}
		}.bind(this));
		return h;
	},
 	
 	update: function(){
 		this.filters.each(function(fs, plugin){
 			fs.each(function(f){
 				f.update();
 			}.bind(this));
 		}.bind(this));
 	}
});

 
var FbList = new Class({
	
	Implements:[Options, Events],
	
	options:{
    'admin': false,
		'filterMethod':'onchange',
    'ajax': false,
    'form': 'listform_' + this.id,
    'hightLight': '#ccffff',
    'primaryKey': '',
    'headings': [],
    'labels':{},
    'Itemid': 0,
    'formid': 0,
    'canEdit': true,
    'canView': true,
    'page': 'index.php',
    'actionMethod':'',
    'formels':[], // elements that only appear in the form
    'data': [], // [{col:val, col:val},...] (depreciated)
    'rowtemplate':''
  },
  
  initialize: function(id, options){
    this.id = id;
    //this.listenTo = $A([]);
    this.setOptions(options);
		this.getForm();
    this.list = document.id('list_' + this.id);
    this.actionManager = new FbListActions(this, this.options.actionMethod);
    new FbGroupedToggler(this.form);
    new FbListKeys(this);
    if (this.list) {
      this.tbody = this.list.getElement('tbody');
      if (typeOf(this.tbody) === 'null') {
      	this.tbody = this.list;
      }
			// $$$ rob mootools 1.2 has bug where we cant set('html') on table
			// means that there is an issue if table contains no data
			if (window.ie) {
				this.options.rowtemplate = this.list.getElement('.fabrik_row');
			}
    }
		this.watchAll(false);
		window.addEvent('fabrik.form.submitted', function(){
			this.updateRows();
		}.bind(this))
	},
	
	 setRowTemplate:function(){
  	// $$$ rob mootools 1.2 has bug where we cant setHTML on table
		//means that there is an issue if table contains no data
		if (typeOf(this.options.rowtemplate) === 'string'){
	  	var r = this.list.getElement('.fabrik_row');
			if (window.ie && typeOf(r) !== 'null') {
				this.options.rowtemplate = r;
			}
		}
  },
	
	watchAll: function(ajaxUpdate)
	{
		ajaxUpdate = ajaxUpdate ? ajaxUpdate : false;
		this.watchNav();
		if (!ajaxUpdate) {
			this.watchRows();
		}
		this.watchFilters();
		this.watchOrder();
		this.watchEmpty();
		this.watchButtons();
	},
	
	watchButtons: function()
	{
		this.exportWindowOpts = {
			id: 'exportcsv',
			title: 'Export CSV',
			loadMethod:'html',
			minimizable:false,
			width: 360,
			height: 120,
			content:''		
		};
	
		if(this.form.getElements('.csvExportButton')){
			this.form.getElements('.csvExportButton').each(function(b){
				if(b.hasClass('custom') === false){
					b.addEvent('click', function(e){
						var thisc = this.makeCSVExportForm();
						this.form.getElements('.fabrik_filter').each(function(f){
							var fc = new Element('input', {'type':'hidden','name':f.name,'id':f.id,'value':f.get('value')});
							fc.inject(thisc);
						}.bind(this));
						this.exportWindowOpts.content = thisc;
						this.exportWindowOpts.onContentLoaded = function(){
							this.fitToContent();
						};
						Fabrik.getWindow(this.exportWindowOpts);
					}.bind(this));
				}
			}.bind(this));
		}
	},
	
	makeCSVExportForm:function(){
		// cant build via dom as ie7 doesn't accept checked status
		var rad = "<input type='radio' value='1' name='incfilters' checked='checked' />" + Joomla.JText._('JYES');
		var rad2 = "<input type='radio' value='1' name='incraw' checked='checked' />" + Joomla.JText._('JYES');
		var rad3 = "<input type='radio' value='1' name='inccalcs' checked='checked' />" + Joomla.JText._('JYES');
		var rad4 = "<input type='radio' value='1' name='inctabledata' checked='checked' />" + Joomla.JText._('JYES');
		var rad5 = "<input type='radio' value='1' name='excel' checked='checked' />Excel CSV";
		var url = 'index.php?option=com_fabrik&view=list&listid='+this.id+'&format=csv';

		var divopts = {'styles':{'width':'200px','float':'left'}};
		var c = new Element('form', {'action':url, 'method':'post'}).adopt([
		
		new Element('div', divopts).set('text', Joomla.JText._('COM_FABRIK_FILE_TYPE')),
		new Element('label').set('html', rad5),
		new Element('label').adopt([
			new Element('input', {'type':'radio','name':'excel','value':'0'}), 
			new Element('span').set('text', 'CSV')
		]),
		new Element('br'),
		new Element('br'),
		new Element('div', divopts).appendText(Joomla.JText._('COM_FABRIK_INCLUDE_FILTERS')),
		new Element('label').set('html',rad),
		new Element('label').adopt([
			new Element('input', {'type':'radio','name':'incfilters','value':'0'}), 
			new Element('span').appendText(Joomla.JText._('JNO'))
		]),
		new Element('br'),
		new Element('div', divopts).appendText(Joomla.JText._('COM_FABRIK_INCLUDE_DATA')),
		new Element('label').set('html',rad4),
		new Element('label').adopt([
			new Element('input', {'type':'radio','name':'inctabledata','value':'0'}), 
			new Element('span').appendText(Joomla.JText._('JNO'))
		]),
		new Element('br'),
		new Element('div', divopts).appendText(Joomla.JText._('COM_FABRIK_INCLUDE_RAW_DATA')),
		new Element('label').set('html',rad2),
		new Element('label').adopt([
			new Element('input', {'type':'radio','name':'incraw','value':'0'}), 
			new Element('span').appendText(Joomla.JText._('JNO'))
		]),
		new Element('br'),
		new Element('div', divopts).appendText(Joomla.JText._('COM_FABRIK_INLCUDE_CALCULATIONS')),
		new Element('label').set('html',rad3),
		new Element('label').adopt([
			new Element('input', {'type':'radio','name':'inccalcs','value':'0'}), 
			new Element('span').appendText(Joomla.JText._('JNO'))
		])
		]);
		new Element('h4').appendText(Joomla.JText._('COM_FABRIK_SELECT_COLUMNS_TO_EXPORT')).inject(c);
		var g = '';
		var i = 0;
		$H(this.options.labels).each(function(label, k){
			if (k.substr(0, 7) != 'fabrik_' && k !== '____form_heading') {
	  		var newg = k.split('___')[0];
				if(newg !== g){
					g = newg;
					new Element('h5').set('text', g).inject(c);
				}
				var rad = "<input type='radio' value='1' name='fields["+k+"]' checked='checked' />" + Joomla.JText._('JYES');
				label = label.replace(/<\/?[^>]+(>|$)/g, "");
				var r = new Element('div', divopts).appendText(label);
				r.inject(c);
				new Element('label').set('html',rad).inject(c);
				new Element('label').adopt([
				new Element('input', {'type':'radio','name':'fields['+k+']','value':'0'}), 
				new Element('span').appendText(Joomla.JText._('JNO'))
				]).inject(c);
				new Element('br').inject(c);
	  	}
			i++;
		}.bind(this)); 
		
		// elements not shown in table
		if(this.options.formels.length > 0){ 
			new Element('h5').set('text', Joomla.JText._('COM_FABRIK_FORM_FIELDS')).inject(c);
			this.options.formels.each(function(el){
				var rad = "<input type='radio' value='1' name='fields["+el.name+"]' checked='checked' />" + Joomla.JText._('JYES');
				var r = new Element('div', divopts).appendText(el.label);
				r.inject(c);
				new Element('label').set('html',rad).inject(c);
					new Element('label').adopt([
					new Element('input', {'type':'radio','name':'fields['+el.name+']','value':'0'}), 
					new Element('span').appendText(Joomla.JText._('JNO'))
					]).inject(c);
					new Element('br').inject(c);	
			}.bind(this));
		}
		
		new Element('div', {'styles':{'text-align':'right'}}).adopt(
			new Element('input', {'type':'button','name':'submit','value':Joomla.JText._('COM_FABRIK_EXPORT'), 'class':'button', events:{
				'click':function(e){
					e.stop();
					e.target.disabled = true;
					new Element('div', {'id': 'csvmsg'}).set('html',Joomla.JText._('COM_FABRIK_LOADING')+' <br /><span id="csvcount">0</span> / <span id="csvtotal"></span> '+Joomla.JText._('COM_FABRIK_RECORDS') + '.<br/> '+Joomla.JText._('COM_FABRIK_SAVING_TO')+' <span id="csvfile"></span>').inject(e.target, 'before');
					var url = 'index.php?option=com_fabrik&view=list&format=csv&listid='+this.id;
					this.triggerCSVImport(0, url);
				}.bind(this)
				
			}})
		).inject(c);
		new Element('input', {'type':'hidden','name':'view','value':'table'}).inject(c);
		new Element('input', {'type':'hidden','name':'option','value':'com_fabrik'}).inject(c);
		new Element('input', {'type':'hidden','name':'listid','value':this.id}).inject(c);
		new Element('input', {'type':'hidden','name':'format','value':'csv'}).inject(c);
		new Element('input', {'type':'hidden','name':'c','value':'table'}).inject(c);
		return c;
	},
	
	triggerCSVImport:function(start, url){
		var opts = {};
		if (typeOf(document.id('exportcsv')) !== 'null') {
			$A(['incfilters', 'inctabledata', 'incraw', 'inccalcs', 'excel']).each(function(v){
				var inputs = document.id('exportcsv').getElements('input[name=' + v + ']');
				if (inputs.length > 0) {
					opts[v] = inputs.filter(function(i){
						return i.checked;
					})[0].value;
				}
			});
			// selected fields
			var fields = {};
			document.id('exportcsv').getElements('input[name^=field]').each(function(i){
				if(i.checked){
					var k = i.name.replace('fields[', '').replace(']', '');
					fields[k] = i.get('value');
				}
			});
		}
		opts['fields'] = fields;
		var thisurl = url +'&start='+start;
		var myAjax = new Request.JSON({
			url:thisurl,
			method: 'post',
			data:opts,
			onSuccess: function(res){
				if (res.err) {
					alert(res.err);
				}else{
					if (typeOf(document.id('csvcount')) !== 'null') document.id('csvcount').set('text', res.count);
					if (typeOf(document.id('csvtotal')) !== 'null') document.id('csvtotal').set('text', res.total);
					if (typeOf(document.id('csvfile')) !== 'null') document.id('csvfile').set('text', res.file);
					if (res.count < res.total) {
						this.triggerCSVImport(res.count, url);
					}else{
						var finalurl = Fabrik.liveSite+'index.php?option=com_fabrik&view=list&format=csv&listid='+this.id+'&start='+res.count;
						var msg = Joomla.JText._('COM_FABRIK_CSV_COMPLETE');
						msg += ' <a href="'+finalurl+'">'+Joomla.JText._('COM_FABRIK_CSV_DOWNLOAD_HERE')+'</a>';
						if (typeOf(document.id('csvmsg')) !== 'null') document.id('csvmsg').set('html', msg);
					}
				}
			}.bind(this)
		});
		myAjax.send();
	},
	
	addPlugins:function(a){
		a.each(function(p){
			p.list = this;
		}.bind(this));
		this.plugins = a;
	},
	
	watchEmpty: function(e){
		var b = document.id(this.options.form).getElement('.doempty', this.options.form);
		if (b) {
			b.addEvent('click', function(e){
				e.stop();
				if( confirm(Joomla.JText._('COM_FABRIK_CONFIRM_DROP'))){
					this.submit('list.doempty');
				}
			}.bind(this));
		}
	},
	
	watchOrder: function(){
		var hs = document.id(this.options.form).getElements('.fabrikorder, .fabrikorder-asc, .fabrikorder-desc');
		hs.removeEvents('click');
		hs.each(function(h){
			h.addEvent('click', function(e){
				var orderdir = '';
				var newOrderClass = '';
				// $$$ rob in pageadaycalendar.com h was null so reset to e.target
				var h = document.id(e.target);
				var td = h.findClassUp('fabrik_ordercell');
				if (h.tagName !== 'a') {
					var h = td.getElement('a');
				}
				switch(h.className){
					case 'fabrikorder-asc':
						newOrderClass = 'fabrikorder-desc';
						orderdir = 'desc';
						break;
					case 'fabrikorder-desc':
						newOrderClass = 'fabrikorder';
						orderdir = "-";
						break;
					case 'fabrikorder':
						newOrderClass = 'fabrikorder-asc';
						orderdir = 'asc';
						break;
				}
				td = td.className.split(' ')[2].replace('_order', '').replace(/^\s+/g,'').replace(/\s+$/g,'');//chrome and safari you need to trim whitespace
				h.className = newOrderClass;
				this.fabrikNavOrder(td, orderdir);
				e.stop();
			}.bind(this));
		}.bind(this));
	
	},
	
	watchFilters: function(){
		var e = '';
		if (this.options.filterMethod != 'submitform') {
			document.id(this.options.form).getElements('.fabrik_filter').each(function(f){
				e = f.get('tag') == 'select' ? 'change' : 'blur';
				f.removeEvent(e);
				f.store('initialvalue', f.get('value'));
				f.addEvent(e, function(e){
					e.stop();
					if (e.target.retrieve('initialvalue') !== e.target.get('value')) {
						this.submit('list.filter');
					}
				}.bind(this));
			}.bind(this));
		}else{
			var f = document.id(this.options.form).getElement('.fabrik_filter_submit');
			if (f) {
				f.removeEvents();
				f.addEvent('click', function(e){
					this.submit('list.filter');
				}.bind(this));
			}
		}
		document.id(this.options.form).getElements('.fabrik_filter').addEvent('keydown', function(e){
			if (e.code == 13) {
				e.stop();
				this.submit('list.filter');
			}
		}.bind(this));
	},
  
  // highlight active row, deselect others
  setActive: function(activeTr){
    this.list.getElements('.fabrik_row').each(function(tr){
      tr.removeClass('activeRow');
    });
    activeTr.addClass('activeRow');
  },
  
  watchRows: function(){
    if(!this.list){
			return;
		}
    this.rows = this.list.getElements('.fabrik_row');
		this.links = this.list.getElements('.fabrik___rowlink');
    if (this.options.ajax) {
    	
			this.list.addEvent('click:relay(.fabrik_edit)', function(e){
     		e.stop();
				var row = e.target.findClassUp('fabrik_row');
				this.setActive(row);
				var rowid = row.id.replace('list_' + this.id + '_row_', '');
				var url = Fabrik.liveSite+"index.php?option=com_fabrik&view=form&formid="+this.options.formid+'&rowid='+rowid+'&tmpl=component&ajax=1';
				//make id the same as the add button so we reuse the same form.
				Fabrik.getWindow({'id':'add.'+this.options.formid, 'title':'Edit', 'loadMethod':'xhr', 'contentURL':url});
			}.bind(this));

     	this.list.addEvent('click:relay(.fabrik_view)', function(e){
     		e.stop();
				var row = e.target.findClassUp('fabrik_row');
				this.setActive(row);
				var rowid = row.id.replace('list_' + this.id + '_row_', '');
				var url = Fabrik.liveSite+"index.php?option=com_fabrik&view=details&formid="+this.options.formid+'&rowid='+rowid+'&tmpl=component&ajax=1';
				Fabrik.getWindow({'id':'view.'+'.'+this.options.formid+'.'+rowid,'title':'Details', 'loadMethod':'xhr', 'contentURL':url});
			}.bind(this));
    }
  },
  
  getForm: function(){
		if (!this.form) {
			this.form = document.id(this.options.form);
		}
		return this.form;
  },
  
  submit: function(task){
    this.getForm();
		if (task == 'list.delete') {
			var ok = false;
			this.form.getElements('input[name^=ids]').each(function(c){
				if(c.checked){
					ok = true;
				}
			});
			if(!ok){
				alert(Joomla.JText._('COM_FABRIK_SELECT_ROWS_FOR_DELETION'));
				Fabrik.loader.stop('listform_'+this.id);
				return false;
			}
			if(!confirm(Joomla.JText._('COM_FABRIK_CONFIRM_DELETE'))){
				Fabrik.loader.stop('listform_'+this.id);
				return false;
			}
		} 
		Fabrik.loader.start('listform_'+this.id);
    if (task == 'list.filter') {
			this.form.task.value = task;
      if (this.form['limitstart' + this.id]) {
        this.form.getElement('#limitstart' + this.id).value = 0;
      }
    } else {
      if (task !== '') {
        this.form.task.value = task;
      }
    }
    if (this.options.ajax) {

	    // for module & mambot
			// $$$ rob with modules only set view/option if ajax on
			this.form.getElement('input[name=option]').value = 'com_fabrik';
			this.form.getElement('input[name=view]').value = 'list';
			this.form.getElement('input[name=format]').value = 'raw';
			if (!this.request) {
				this.request = new Request({
       		'url':this.form.get('action'),
       		'data':this.form,
       		onComplete : function(json){
	    			json = JSON.decode(json);
	    			this._updateRows(json);
	    			Fabrik.loader.stop('listform_'+this.id);
	      	}.bind(this)
	       })
       }
    	this.request.send();
      window.fireEvent('fabrik.list.submit', [task, this.form.toQueryString().toObject()]);
    }
    else {
      this.form.submit();
      Fabrik.loader.stop('listform_'+this.id);
    }
    
    return false;
  },
  
  fabrikNav: function(limitStart){
    this.form.getElement('#limitstart' + this.id).value = limitStart;
    // cant do filter as that resets limitstart to 0
    window.fireEvent('fabrik.list.navigate', [this, limitStart]);
    if(!this.result){
    	this.result = true;
    	return false;
    }
    this.submit('list.view');
    return false;
  },
  
  fabrikNavOrder: function(orderby, orderdir){
  	this.form.orderby.value = orderby;
    this.form.orderdir.value = orderdir;
    window.fireEvent('fabrik.list.order', [this, orderby, orderdir]);
    if (!this.result){
    	this.result = true;
    	return false;
    }
		this.submit('list.order');
  },
  
  removeRows: function(rowids){
    // @TODO: try to do this with FX.Elements
    for (i = 0; i < rowids.length; i++) {
      var row = document.id('list_' + this.id + '_row_' + rowids[i]);
      var highlight = new Fx.Morph(row, {
        duration: 1000
      });
      highlight.start({
        'backgroundColor': this.options.hightLight
      }).chain(function(){
        this.start({
          'opacity': 0
        });
      }).chain(function(){
        row.dispose();
        this.checkEmpty();
      }.bind(this));
    }
  },
  
  editRow: function(){
  
  },
  
  clearRows: function(){
  	this.list.getElements('.fabrik_row').each(function(tr){
      tr.dispose();
    });
  },
  
	updateRows: function(){
		new Request.JSON({'url':Fabrik.liveSite+'index.php?option=com_fabrik&view=list&format=raw&listid='+this.id, onSuccess: function(json){
			this._updateRows(json);
			//window.fireEvent('fabrik.list.update', [this, json]);
		}.bind(this)
		}).send();
		
	},
	
  _updateRows: function(data){
		if (data.id == this.id && data.model == 'list') {
			var header = document.id(this.options.form).getElements('.fabrik___heading').getLast();
			var headings = new Hash(data.headings);
			headings.each(function(data, key){
				key = "." + key;
				try{
					if (typeOf(header.getElement(key)) !== 'null') {
					header.getElement(key).set('html', data);
					}
				}catch(err){
					fconsole(err);
				}
			});
			
			this.clearRows();
			var counter = 0;
			var rowcounter = 0;
			trs = [];
			this.options.data = data.data;
			if(data.calculations){
				this.updateCals(data.calculations);
			}
			if (typeOf(this.form.getElement('.fabrikNav')) !== 'null') {
				this.form.getElement('.fabrikNav').set('html', data.htmlnav);
			}
			this.setRowTemplate();
			// $$$ rob was $H(data.data) but that wasnt working ????
			//testing with $H back in again for grouped by data? Yeah works for grouped data!!
			var gdata = this.options.isGrouped ? $H(data.data) : data.data;
			gdata.each(function(groupData, groupKey){
				for(i=0;i<groupData.length;i++){
					if (typeOf(this.options.rowtemplate) == 'string') {
						var container =(!this.options.rowtemplate.match(/\<tr/)) ? 'div' : 'table';
						var thisrowtemplate = new Element(container);
		  			thisrowtemplate.set('html',this.options.rowtemplate);
				  }else{
						container = this.options.rowtemplate.get('tag') == 'tr' ? 'table' : 'div'; 
						var thisrowtemplate = new Element(container);
						// ie tmp fix for mt 1.2 setHTML on table issue
						thisrowtemplate.adopt(this.options.rowtemplate.clone());
					}
					var row = $H(groupData[i]);
					$H(row.data).each(function(val, key){
						var rowk = '.' + key;
						var cell = thisrowtemplate.getElement(rowk);
						if (typeOf(cell) !== 'null') {
							cell.set('html',val);
						}
						rowcounter ++;
          }.bind(this));
					// thisrowtemplate.getElement('.fabrik_row').id = 'list_' + this.id + '_row_' + row.get('__pk_val');
					thisrowtemplate.getElement('.fabrik_row').id = row.id;
					if (typeOf(this.options.rowtemplate) === 'string') {
						var c = thisrowtemplate.getElement('.fabrik_row').clone();
						c.id = row.id;
				  	c.inject(this.tbody);
				  }else{
						var r = thisrowtemplate.getElement('.fabrik_row');
						r.inject(this.tbody);
						thisrowtemplate.empty();
					}
					counter ++;
				}
      }.bind(this));
			
			var fabrikDataContainer = this.list.findClassUp('fabrikDataContainer');
			var emptyDataMessage = this.list.findClassUp('fabrikForm').getElement('.emptyDataMessage');
			if (rowcounter == 0) {
				if(typeOf(fabrikDataContainer)!== 'null')
					fabrikDataContainer.setStyle('display', 'none');
				if(typeOf(emptyDataMessage)!== 'null')
					emptyDataMessage.setStyle('display', '');	
			}else{
				if(typeOf(fabrikDataContainer)!== 'null')
					fabrikDataContainer.setStyle('display', '');
				if(typeOf(emptyDataMessage)!== 'null')
					emptyDataMessage.setStyle('display', 'none');	
			}
			if (typeOf(this.form.getElement('.fabrikNav')) !== 'null') {
				this.form.getElement('.fabrikNav').set('html', data.htmlnav);
			}
      this.watchAll(true);
      window.fireEvent('fabrik.table.updaterows');
      try{
				Slimbox.scanPage();
			}catch(err){
				fconsole('slimbox scan:'+err);
			}
			try{
				Mediabox.scanPage();
			}catch(err){
				fconsole('mediabox scan:'+err);
			}
			window.fireEvent('fabrik.list.update', [this, data]);
		}
		 this.stripe();
		 Fabrik.loader.stop('listform_'+this.id);
  },
  
  addRow: function(obj){
    var r = new Element('tr', {
      'class': 'oddRow1'
    });
    var x = {
      test: 'hi'
    };
    for (var i in obj) {
      if (this.options.headings.indexOf(i) != -1) {
        var td = new Element('td', {}).appendText(obj[i]);
        r.appendChild(td);
      }
    }
    r.inject(this.tbody);
  },
  
  addRows: function(aData){
    for (i = 0; i < aData.length; i++) {
      for (j = 0; j < aData[i].length; j++) {
        this.addRow(aData[i][j]);
      }
    }
    this.stripe();
  },
  
  stripe: function(){
  	var trs = this.list.getElements('.fabrik_row');
    for (i = 0; i < trs.length; i++) {
      if (i !== 0) { // ignore heading
        var row = 'oddRow' + (i % 2);
        trs[i].addClass(row);
      }
    }
  },
  
  checkEmpty: function(){
  	var trs = this.list.getElements('tr');
    if (trs.length == 2) {
      this.addRow({
        'label': Joomla.JText._('COM_FABRIK_NO_RECORDS')
      });
    }
  },
  
  watchCheckAll: function(e){
  	var checkAll = this.form.getElement('input[name=checkAll]');
    if (typeOf(checkAll) !== 'null') {
    	// IE wont fire an event on change until the checkbxo is blurred!
      checkAll.addEvent('click', function(e){
   	  	var c = document.id(e.target); 
        var chkBoxes = this.list.findClassUp('fabrikList').getElements('input[name^=ids]');
				var c = !c.checked ? '' : 'checked';
        for (var i = 0; i < chkBoxes.length; i++) {
          chkBoxes[i].checked = c;
					this.toggleJoinKeysChx(chkBoxes[i]);
        }
        //event.stop(); dont event stop as this stops the checkbox being  selected
      }.bind(this));
    }
		this.form.getElements('input[name^=ids]').each(function(i){
			i.addEvent('change', function(e){
				this.toggleJoinKeysChx(i);
			}.bind(this));
		}.bind(this));
  },
	
	toggleJoinKeysChx:function(i)
	{
		i.getParent().getElements('input[class=fabrik_joinedkey]').each(function(c){
			c.checked = i.checked;
		});
	},
  
  watchNav: function(e){
  	var limitBox = this.form.getElement('#limit'+ this.id);
    if (limitBox) {
    	limitBox.removeEvents();
      limitBox.addEvent('change', function(e){
      	var res = window.fireEvent('fabrik.list.limit', [this]);
      	if(!this.result){
      		this.result = true;
      		return false;
      	}
      	this.submit('list.filter');
      }.bind(this));
    }
    var addRecord = this.form.getElement('.addRecord')
    if (typeOf(addRecord) != 'null' && (this.options.ajax)) {
			 addRecord.removeEvents(); 
			 addRecord.addEvent('click', function(e){
			 	e.stop();
			 //	top.window.fireEvent('fabrik.list.add', this);//for packages?
			 	Fabrik.getWindow({'id':'add-'+this.id, 'title':'Add', 'loadMethod':'xhr', 'contentURL':addRecord.href});
			 }.bind(this));
			 
    }
    //var del = this.form.getElement('input[name=delete]');
    var del = document.getElements('.fabrik_delete a');
    if (del) {
    	del.addEvent('click', function(e){
    		var r = e.target.findClassUp('fabrik_row');
    		if (r) {
    			var chx = r.getElement('input[type=checkbox][name*=id]');
					if (typeOf(chx) !== 'null') { // if delete link is in hover box the we cant find the associated chx box   		
	    			this.form.getElements('input[type=checkbox][name*=id], input[type=checkbox][name=checkAll]').each(function(c){c.checked = false;});
	    		}
	    		if (typeOf(chx) !== 'null') { chx.checked = true; }
    		} else {
    			//checkAll
    			this.form.getElements('input[type=checkbox][name*=id], input[type=checkbox][name=checkAll]').each(function(c){c.checked = true;});
    		}
    		if (!this.submit('list.delete')) {
    			e.stop();
    		}
    	}.bind(this))
    }
    
		if(document.id('fabrik__swaptable')){
			document.id('fabrik__swaptable').addEvent('change', function(e){
				window.location = 'index.php?option=com_fabrik&task=list.view&cid=' + e.target.get('value');
			}.bind(this));
		}
		if(this.options.ajax){
			if(typeOf(this.form.getElement('.pagination')) !== 'null'){
				this.form.getElement('.pagination').getElements('.pagenav').each(function(a){
					a.addEvent('click', function(e){
						e.stop();
						if(a.get('tag') == 'a'){
							var o = a.href.toObject();
							this.fabrikNav(o['limitstart' + this.id]);
						}
					}.bind(this));
				}.bind(this));
			}
		}
    this.watchCheckAll();
  },
  /*
  // @todo use window.fire/addEvent
  addListenTo: function(blockId){
    this.listenTo.push(blockId);
  },
  
  receiveMessage: function(senderBlock, task, taskStatus, data){
    if (this.listenTo.indexOf(senderBlock) != -1) {
      switch (task) {
        case 'delete':
        	this.updateRows();
          break;
        case 'processForm':
          this.addRows(data);
          break;
        case 'navigate':
        case 'list.filter':
        case 'updateRows':
        case 'order':
        case 'doPlugin':
        	// only update rows if no errors returned
        	if ($H(data.errors).getKeys().length === 0){
          	this.updateRows();
          }
          break;
      }
    }
  },*/
 
  /** currently only called from element raw view when using inline edit plugin
   *  might need to use for ajax nav as well?
   */
  updateCals : function(json){
  	var types = ['sums', 'avgs', 'count', 'medians'];
  	this.form.getElements('.fabrik_calculations').each(function(c){
  		types.each(function(type){
  			$H(json[type]).each(function(val, key){
	  			var target = c.getElement('.fabrik_row___'+key);
	  			if (typeOf(target) !== 'null') {
	  				target.set('html', val);
	  			}
	  		});
  		});
  	});
  }
});

/**
 * observe keyboard short cuts
 */

var FbListKeys = new Class({
	initialize:function(list){
		window.addEvent('keyup', function(e){
			if (e.alt){
				switch(e.key){
					case Joomla.JText._('COM_FABRIK_LIST_SHORTCUTS_ADD'):
					var a = list.form.getElement('.addRecord');
						if (list.options.ajax) {
							a.fireEvent('click');
						}
						if(a.getElement('a')) {
							list.options.ajax ? a.getElement('a').fireEvent('click') : document.location = a.getElement('a').get('href');
						} else {
							if (!list.options.ajax) {
								document.location = a.get('href');
							}
						}
						break;
						
					case Joomla.JText._('COM_FABRIK_LIST_SHORTCUTS_EDIT'):
						console.log('edit')
						break;
					case Joomla.JText._('COM_FABRIK_LIST_SHORTCUTS_DELETE'):
						console.log('delete')
						break;
					case Joomla.JText._('COM_FABRIK_LIST_SHORTCUTS_FILTER'):
						console.log('filter')
						break;
				}
			}
			
		}.bind(this))
	}	
});

/**
 * toggle grouped data by click on the grouped headings icon
 */

var FbGroupedToggler = new Class({
	initialize: function(container) {
		container.addEvent('click:relay(.fabrik_groupheading a.toggle)', function(e){
			e.stop();
			var img = e.target.get('tag') === 'a' ? e.target.getElement('img') : e.target;
			var state = img.retrieve('showgroup', true);
			var h = img.findClassUp('fabrik_groupheading');
			var rows = h.getParent().getNext();
			state ? rows.hide() : rows.show();
			if (state) {
				img.src = img.src.replace('orderasc', 'orderneutral');	
			}else{
				img.src = img.src.replace('orderneutral', 'orderasc');
			}
			state = state ? false : true;
			img.store('showgroup',state);
		});
	}
})

/**
 * set up and show/hide list actions for each row
 */
var FbListActions = new Class({
	
	initialize: function(list, method){
		method = method ? method : '';
		this.list = list; // main list js object
		this.actions = [];
		this.setUpSubMenus();
		this.method = method;
		
		window.addEvent('fabrik.list.update', function(list, json){
			this.observe();
		}.bind(this));
		this.observe();
	},
	
	observe: function(){
		switch(this.method){
			default:
			this.setUpDefault();
			break;
			case 'floating':
				this.setUpFloating();
				break;
		}
	},
	setUpSubMenus:function(){
		this.actions = this.list.form.getElements('ul.fabrik_action');
		this.actions.each(function(ul){
			//sub menus ie group by options
			if (ul.getElement('ul')) {
				var el = ul.getElement('ul');
				var c = el.clone();
				c.inject(document.body);
				c.fade('hide');
				var trigger = el.getParent();
				trigger.store('trigger', c);
				c.setStyles({'position':'absolute'});
				trigger.addEvent('click', function(e){
					e.stop();
					var c = trigger.retrieve('trigger');
					c.setStyle('top', trigger.getTop()  + trigger.getHeight());
					c.setStyle('left', trigger.getLeft() +trigger.getWidth()/1.5);
					c.fade('toggle');
				});
				el.dispose();
			}
		});
	},
	
	setUpDefault:function(){
		this.actions = this.list.form.getElements('ul.fabrik_action');
		this.actions.each(function(ul){
			if(ul.getParent().hasClass('fabrik_buttons')) {
				return;
			}
			ul.fade(0.6);
			var r = ul.findClassUp('fabrik_row') ? ul.findClassUp('fabrik_row') : ul.findClassUp('fabrik___heading');
			if (r) {
				// $$$ hugh - for some strange reason, if we use 1 the object disappears in Chrome and Safari!
				r.addEvents({'mouseenter':function(e){
					ul.fade(0.99);
				},
				'mouseleave':function(e){
					ul.fade(0.6);
				}});
			}
		});
	},
	
	setUpFloating: function(){
		this.list.form.getElements('ul.fabrik_action').each(function(ul){
			if(ul.findClassUp('fabrik_row')) {
				ul.addClass('floating-tip');
				var c = ul.clone().inject(document.body, 'inside');
				this.actions.push(c);
				c.fade('out');
				c.addClass('fabrik_row');
				c.setStyle('position', 'absolute');
				ul.findClassUp('fabrik_row').getElement('input[type=checkbox]').addEvent('click', function(e){
					this.toggleWidget(e, c);
				}.bind(this));
				ul.dispose();
			}
		}.bind(this));
		
		//hide markup that contained the actions
		if(this.list.list.getElements('.fabrik_actions')){
			this.list.list.getElements('.fabrik_actions').hide();
		}
		if(this.list.list.getElements('.fabrik_calculation')) {
			var calc = this.list.list.getElements('.fabrik_calculation').getLast();
			if (typeOf(calc) !== 'null') {
				calc.hide();
			}
		}
	
		//watch the top/master chxbox
		var chxall = this.list.form.getElement('input[name=checkAll]');
		if (typeOf(chxall) !== 'null') {
			chxall.addEvent('click', function(e){
				this.toggleWidget(e, this.actions[0], true);
			}.bind(this));
		}
	},
	
	toggleRowSpecific: function(c, sauron) {
		var edit = c.getElement('.fabrik_edit');
		var view = c.getElement('.fabrik_view');
		if (sauron) {
			if (typeOf(edit) !== 'null'){edit.hide();}
			if (typeOf(view) !== 'null'){view.hide();}
		}else{
			if (typeOf(edit) !== 'null'){edit.show();}
			if (typeOf(view) !== 'null'){view.show();}
		}
	},
	
	toggleWidget: function(e, c, sauron){
		sauron = sauron ? sauron : false;
		if(e.target.checked) {
			this.actions.each(function(a){
				if (a !== c) {
					a.fade('out');
				}
			}.bind(this));
			this.toggleRowSpecific(c, sauron);
			var xOffset = 10;
			//is the checkbox right on the rhside of the screen?
			if(e.target.getPosition().x + c.getWidth() + xOffset >  window.getSize().x) {
				xOffset = (xOffset * -1) - c.getWidth(); 
			}
			var p = {'left':(e.page.x+xOffset)+'px', 'top':e.page.y+'px'};
			c.setStyles(p);
			c.fade('in');
		}else{
			c.fade('out');
		}
	}
});