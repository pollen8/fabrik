
var ListPluginManager = new Class({
	
	Extends:PluginManager,

	initialize: function(plugins){
		this.parent(plugins);
		this.opts['type'] = 'list';
	},
	
	getPluginTop:function(plugin, loc, when){
		return new Element('tr').adopt(
				new Element('td').adopt([
				    new Element('input', {'value':Joomla.JText._('COM_FABRIK_ACTION'), 'size':6,'readonly':true, 'class':'readonly'}),
					this._makeSel('inputbox elementtype', 'jform[params][plugins][]', this.plugins, plugin)
				])
		);
	}
});

var ListForm = new Class({
	
	Implements:[Options],
	
	options:{},
	
	initialize: function(options){
		this.setOptions(options);
		this.watchTableDd();
		this.addAJoinClick = this.addAJoin.bindWithEvent(this);
		if(document.id('addAJoin')){
			document.id('addAJoin').addEvent('click', this.addAJoinClick);
		}
		this.joinCounter = 0;
		this.watchOrderButtons();
		this.watchDbName();
	},
	
	watchOrderButtons:function(){
		$$('.addOrder').removeEvents('click');
		$$('.deleteOrder').removeEvents('click');
		$$('.addOrder').addEvent('click', this.addOrderBy.bindWithEvent(this));
		$$('.deleteOrder').addEvent('click', this.deleteOrderBy.bindWithEvent(this));
	},
	
	addOrderBy: function(e)
	{
		if(e){
			var t= e.target.findClassUp('orderby_container');
		} else {
			t = document.getElement('.orderby_container');
		}
		t.clone().inject(t, 'after');
		this.watchOrderButtons();
	},
	
	deleteOrderBy: function(e){
		if($$('.orderby_container').length >1){
			e.target.findClassUp('orderby_container').dispose();
			this.watchOrderButtons();
		}
	},
	
	watchDbName: function(){
		if(document.id('database_name')){
			document.id('database_name').addEvent('blur', function(e){
				if(document.id('database_name').get('value') == ''){
					document.id('tablename').disabled = false;
				}else{
					document.id('tablename').disabled = true;
				}
			});
		}
	},
	
	_buildOptions: function(data, sel){
		var opts = [];
		if(data.length > 0 ){
			if(typeof(data[0]) == 'object'){
				data.each(function(o){
					if(o[0] == sel){
						opts.push( new Element('option', {'value':o[0], 'selected':'selected'}).set('text', o[1]));
					}else{
						opts.push( new Element('option', {'value':o[0]}).set('text', o[1]));
					}
				});
			}else{
				data.each(function(o){
					if(o == sel){
						opts.push( new Element('option', {'value':o, 'selected':'selected'}).set('text', o));
					}else{
						opts.push( new Element('option', {'value':o}).set('text', o));
					}
				});
			}
		}
		return opts;	
	},
	
	addAJoin:function(e){
		this.addJoin();
		e.stop();
	},
	
	watchTableDd: function(){
		if(document.id('tablename')){
		document.id('tablename').addEvent('change', function(e){
			var cid = document.getElement('input[name*=connection_id]').get('value');
			var table = document.id('tablename').get('value');
			var url = 'index.php?option=com_fabrik&format=raw&task=list.ajax_updateColumDropDowns&cid=' + cid + '&table=' + table;
			var myAjax = new Request({url:url, method:'post', 
				onComplete: function(r){
				eval(r);
				}}).send();
		});
		}
	},
		
	watchFieldList: function(name){
		$A(document.getElementsByName(name)).each(function(dd){
			dd.addEvent('change', function(e){
				var sel = e.target.parentNode.parentNode.parentNode.parentNode;
				var activeJoinCounter = sel.id.replace('join', '');
				this.updateJoinStatement(activeJoinCounter);
			}.bind(this));
		}.bind(this));	
	},
	
	_findActiveTables: function(){
		var t = $$('.join_from').combine($$('.join_to'));
		t.each(function(sel){
			var v = sel.get('value');
			if(this.options.activetableOpts.indexOf(v) === -1){
				this.options.activetableOpts.push(v);
			}
		}.bind(this));
		this.options.activetableOpts.sort();
	},
	
	addJoin:function(groupId, joinId, joinType, joinToTable, thisKey, joinKey, joinFromTable, joinFromFields, joinToFields, repeat){
		joinType = joinType ? joinType : 'left';
		joinFromTable = joinFromTable ? joinFromTable : '';
		joinToTable = joinToTable ? joinToTable : '';
		thisKey = thisKey ? thisKey : '';
		joinKey = joinKey ? joinKey : '';
		groupId = groupId ? groupId : '';
		joinId = joinId ? joinId : '';
		repeat = repeat ? repeat : false;
		if (repeat){
			var repeaton = "checked=\"checked\"";
			var repeatoff= "";
		}else{
			var repeatoff = "checked=\"checked\"";
			var repeaton = "";
		}
		this._findActiveTables();
		joinFromFields = joinFromFields ? joinFromFields : [['-', '']];
		joinToFields = joinToFields ? joinToFields : [['-', '']];
		
		var tbody = new Element('tbody');
		var sContent = new Element('table', {'class':'adminform', 'id':'join' + this.joinCounter}).adopt([
			new Element('thead').adopt([
			new Element('tr', {events:{
				'click':function(e){
					e.stop();
					e.target.findClassUp('adminform').getElement('tbody').slide('toggle');
				}
			},
			'styles':{'cursor':'pointer'}
			}).adopt(
					new Element('td', {'colspan':'2'}).adopt(
						new Element('div', {
							 'id':'join-desc-'+ this.joinCounter,
							 'styles': {'margin':'5px','background-color':'#fefefe','padding':'5px','border':'1px dotted #666666'}
						})
					)
				)
			]),
			tbody.adopt([
			
			new Element('tr').adopt([
 				new Element('td').set('text', 'id'),
 				new Element('td').adopt(new Element('input', {'type':'field', 'readonly':'readonly', 'size':'2', 'class':'disabled readonly', 'name':'jform[params][join_id][]','value':joinId}))
  			]),
			new Element('tr').adopt(
				[
					new Element('td').adopt(
						[
							new Element('input', {'type':'hidden', 'name':'group_id[]','value':groupId})
						]
					).set('text', Joomla.JText._('COM_FABRIK_JOIN_TYPE')),
					
					new Element('td').adopt(
						new Element('select', {
							'name':'jform[params][join_type][]',
							'class':'inputbox'
							}).adopt(this._buildOptions(this.options.joinOpts, joinType)
						)
					)
				]
			),
			
			new Element('tr').adopt(
				[
					new Element('td').set('text', Joomla.JText._('COM_FABRIK_FROM')),
					new Element('td').adopt(
						new Element('select', {
							'name':'jform[params][join_from_table][]',
							'class':'inputbox join_from'}).adopt(this._buildOptions(this.options.activetableOpts, joinFromTable)
						)
					)
				]
			),
			
			new Element('tr').adopt(
				[
					new Element('td').set('text', Joomla.JText._('COM_FABRIK_TO')),
					new Element('td').adopt(
						new Element('select', {
							'name':'jform[params][table_join][]',
							'class':'inputbox join_to'}).adopt(this._buildOptions(this.options.tableOpts, joinToTable)
						)
					)
				]
			),
			
			new Element('tr').adopt(
				[
					new Element('td').set('text', Joomla.JText._('COM_FABRIK_FROM_COLUMN')),
					new Element('td', {'id':'joinThisTableId' + this.joinCounter }).adopt(
						new Element('select', {
							'name':'jform[params][table_key][]',
							'class':'table_key inputbox'}).adopt(this._buildOptions(joinFromFields, thisKey)
						)
					)
				]
			),
			
			new Element('tr').adopt(
				[
					new Element('td').set('text', Joomla.JText._('COM_FABRIK_TO_COLUMN')),
					new Element('td', {'id':'joinJoinTableId' + this.joinCounter }).adopt(
						new Element('select', {
							'name':'jform[params][table_join_key][]',
							'class':'table_join_key inputbox'}).adopt(this._buildOptions(joinToFields, joinKey)
						)
					)
				]
			),
			
			new Element('tr').set('html', "<td>"+Joomla.JText._('COM_FABRIK_REPEAT_GROUP_BUTTON_LABEL')+"</td><td>" +
					"<fieldset class=\"radio\">" +
					"<input type=\"radio\" id=\"joinrepeat"+this.joinCounter+"\" value=\"1\" name=\"jform[params][join_repeat][]\" "+repeaton +"/><label for=\"joinrepeat"+this.joinCounter+"\">" + Joomla.JText._('JYES') + "</label>" +
					"<input type=\"radio\" id=\"joinrepeatno"+this.joinCounter+"\" value=\"0\" name=\"jform[params][join_repeat][]\" "+repeatoff +"/><label for=\"joinrepeatno"+this.joinCounter+"\">" + Joomla.JText._('JNO') + "</label>" +
					"</fieldset></td>"),
			
			new Element('tr').adopt(
				[
					new Element('td', {'colspan':'2'}).adopt(
						[
							new Element('a', {
								'href':'#',
								'class':'removeButton',
								'events': {
									'click': function(e){
									    this.deleteJoin(e);
										return false;
									}.bind(this)
								}
							}).set('text', Joomla.JText._('COM_FABRIK_DELETE'))
						]
					)
				]
			)
		])]);
		var d = new Element('div', {'id':'join'}).adopt(sContent);
		d.inject(document.id('joindtd'));  
		if (thisKey !== '') {
			tbody.slide('hide');
		}
		this.updateJoinStatement(this.joinCounter);
		this.watchJoins();
		this.joinCounter++;
	},
			
	deleteJoin:function(e){
		e.stop();
		var t = document.id(e.target.up(4)); //was 3 but that was the tbody	
		var myfx = new Fx.Tween(t, {property:'opacity', duration:500});
		myfx.start(1, 0).chain(function(){t.dispose();});
	},
	
	watchJoins: function(){
		$$('.join_from').each(function(dd){
			dd.removeEvents('change');
			dd.addEvent('change', function(e){
				var sel = e.target.parentNode.parentNode.parentNode.parentNode;
				var activeJoinCounter = sel.id.replace('join', '');
				this.updateJoinStatement(activeJoinCounter);
				var table = e.target.get('value');
				var conn = document.getElement('input[name*=connection_id]').get('value');
		
				var url = 'index.php?option=com_fabrik&format=raw&task=list.ajax_loadTableDropDown&table=' + table + '&conn=' + conn;
					var myAjax = new Request.HTML({url:url,method:'post', 
					update: document.id('joinThisTableId' + activeJoinCounter),
					onComplete: function(r){
						this.watchFieldList('jform[params][table_key][]');
					}.bind(this)}).send();
			}.bind(this));
		}.bind(this));	
		
		$$('.join_to').each(function(dd){
			dd.removeEvents('change');
			dd.addEvent('change', function(e){
				var sel = e.target.parentNode.parentNode.parentNode.parentNode;
				var activeJoinCounter = sel.id.replace('join', '');
				this.updateJoinStatement(activeJoinCounter);
				var table = e.target.get('value');
				var conn = document.getElement('input[name*=connection_id]').get('value');
				var url = 'index.php?name=jform[params][table_join_key][]&option=com_fabrik&format=raw&task=list.ajax_loadTableDropDown&table=' + table + '&conn=' + conn;
								
				var myAjax = new Request.HTML({url:url, method:'post', 
				update: document.id('joinJoinTableId' + activeJoinCounter),
				onComplete: function(r){
					this.watchFieldList('jform[params][table_join_key][]');
				}.bind(this)}).send();
			}.bind(this));
		}.bind(this));	
	
		this.watchFieldList('jform[params][join_type][]');
		this.watchFieldList('jform[params][table_join_key][]');
		this.watchFieldList('jform[params][table_key][]');
	},
	
	updateJoinStatement:function(activeJoinCounter){
		var fields = $$('#join' + activeJoinCounter + ' .inputbox');
		var type = fields[0].get('value');
		var fromTable = fields[1].get('value');
		var toTable = fields[2].get('value');
		var fromKey = fields[3].get('value');
		var toKey = fields[4].get('value');
		var str = type + " JOIN " + toTable + " ON " + fromTable + "." + fromKey + " = " + toTable + "." + toKey;
		document.id('join-desc-'+ activeJoinCounter).set('html', str);				
	}

});

////////////////////////////////////////////

var adminFilters = new Class({
	
	Implements:[Options],
	
	options:{},
	
	initialize: function(el, fields, options) {
		this.el = document.id(el);
		this.fields = fields;
		this.setOptions(options);
		this.filters = new Array();
		this.counter = 0;
		this.onDeleteClick = this.deleteFilterOption.bindWithEvent(this);
	},
	
	addHeadings: function(){
		var thead = new Element('thead').adopt(new Element('tr', {'id':'filterTh', 'class':'title'}).adopt(
			new Element('th').set('text', Joomla.JText._('COM_FABRIK_JOIN')),
			new Element('th').set('text', Joomla.JText._('COM_FABRIK_FIELD')),
			new Element('th').set('text', Joomla.JText._('COM_FABRIK_CONDITION')),
			new Element('th').set('text', Joomla.JText._('COM_FABRIK_VALUE')),
 			new Element('th').adopt(
	 			new Element('span', {'class':'editlinktip'}).adopt(
					new Element('span', {}).set('text', Joomla.JText._('COM_FABRIK_APPLY_FILTER_TO'))
				)
			),
			new Element('th').set('text', Joomla.JText._('COM_FABRIK_DELETE'))			 
		));
		thead.inject(document.id('filterContainer'), 'before');
	},
	
	deleteFilterOption: function(e){
		e.stop();
		var element = e.target;
		element.removeEvent("click", this.onDeleteClick);
    	var tr = element.parentNode.parentNode;
    	var table = tr.parentNode;
    	table.removeChild(tr);
    	this.counter --;
    	if(this.counter == 0){
    		document.id('filterTh').dispose();
    	}
	},
	
		_makeSel: function(c, name, pairs, sel){
	//@TODO refactor this as its duplicated everywhere!
		var opts = [];
		opts.push(new Element('option', {'value':''}).set('text', Joomla.JText._('COM_FABRIK_PLEASE_SELECT')));
		pairs.each(function(pair){
			if(pair.value == sel){
				opts.push(new Element('option', {'value':pair.value, 'selected':'selected'}).set('text', pair.label));
			}else{
				opts.push(new Element('option', {'value':pair.value}).set('text', pair.label));
			}
		});
		return new Element('select', {'class':c,'name':name}).adopt(opts);
	},
	
	addFilterOption: function(selJoin, selFilter, selCondition, selValue, selAccess, eval, grouped){
		if(this.counter <= 0){
			this.addHeadings();
		}
		selJoin = selJoin ? selJoin : '';
		selFilter = selFilter ? selFilter : '';
		selCondition = selCondition ? selCondition : '';
		selValue = selValue ? selValue : '';
		selAccess = selAccess ? selAccess : '';
		grouped = grouped ? grouped: '';
		var conditionsDd = this.options.filterCondDd;					
		var tr = new Element('tr');
		if(this.counter > 0){
			var opts = {'type':'radio', 'name':'jform[params][filter-grouped][' + this.counter + ']', 'value':'1' };
			opts.checked = (grouped == "1") ? "checked" : "";
			var groupedYes = new Element('label').adopt(
				new Element('input', opts)
			).set('text', Joomla.JText._('JYES'));
			//need to redeclare opts for ie8 otherwise it renders a field!
			opts = {'type':'radio', 'name':'jform[params][filter-grouped][' + this.counter + ']', 'value':'0' };
			opts.checked = (grouped != "1") ? "checked" : "";
			var groupedNo = new Element('label').adopt(
				new Element('input', opts)
			).set('text', Joomla.JText._('JNO'));
		}
		if( this.counter == 0){
			var joinDd = new Element('span').set('text', 'WHERE').adopt(
				new Element('input', {'type':'hidden','id':'paramsfilter-join', 'class':'inputbox','name':'jform[params][filter-join][]','value':selJoin}));
		}else{
			if(selJoin == 'AND'){
				var and =  new Element('option', {'value':'AND','selected':'selected'}).set('text', 'AND');
				var or = new Element('option', {'value':'OR'}).set('text', 'OR');
			}else{
				var and =  new Element('option', {'value':'AND'}).set('text', 'AND');
				var or = new Element('option', {'value':'OR','selected':'selected'}).set('text', 'OR');
			}
			var joinDd = new Element('select', {'id':'paramsfilter-join', 'class':'inputbox','name':'jform[params][filter-join][]'}).adopt(
		[and, or]);
		}
					
		var td = new Element('td');
		
		if(this.counter <= 0){
			td.appendChild(new Element('input', {'type':'hidden', 'name':'jform[params][filter-grouped][' + this.counter + ']', 'value':'0'}));
		}else{
			
			td.appendChild(new Element('span').set('text', Joomla.JText._('COM_FABRIK_GROUPED')));
			td.appendChild(new Element('br'));
			td.appendChild(groupedNo);
			td.appendChild(groupedYes);
			td.appendChild(new Element('br'));
		}
		td.appendChild(joinDd);
		
		var td1 = new Element('td');
		td1.innerHTML = this.fields;
		var td2 = new Element('td');
		td2.innerHTML = conditionsDd;
		var td3 = new Element('td');
		var td4 = new Element('td');
		td4.innerHTML = this.options.filterAccess;
		var td5 = new Element('td');
		
		var textArea = new Element('textarea', {'name':'jform[params][filter-value][]', 'cols':17, 'rows':4 }).set('text', selValue);
		td3.appendChild(textArea);
		td3.appendChild(new Element('br'));
		
		var evalopts = [{'value':0,'label':Joomla.JText._('COM_FABRIK_TEXT')}, {'value':1,'label':Joomla.JText._('COM_FABRIK_EVAL')}, {'value':2,'label':Joomla.JText._('COM_FABRIK_QUERY')}, {'value':3,'label':Joomla.JText._('COM_FABRIK_NO_QUOTES')}];
		td3.adopt(
			new Element('label').adopt([
				new Element('span').set('text', Joomla.JText._('COM_FABRIK_TYPE')),
				this._makeSel('inputbox elementtype', 'jform[params][filter-eval][' + this.counter + ']', evalopts, eval)
			])
		);

		
		if( selJoin!='' || selFilter!='' || selCondition!='' || selValue!=''){
			var checked = true;
		}else{
			var checked = false;
		}
		var delId = this.el.id + "-del-" + this.counter;
		var a = new Element('a', {href:'#', 'id':delId, 'class':'removeButton'});
		td5.appendChild(a);
		tr.appendChild(td);
		tr.appendChild(td1);
		tr.appendChild(td2);
		tr.appendChild(td3);
		tr.appendChild(td4);
		tr.appendChild(td5);

		this.el.appendChild(tr);
		
		document.id(delId).addEvent('click', this.onDeleteClick);
		
		document.id(this.el.id + "-del-" + this.counter).click = this.onDeleteClick;
		
		/*set default values*/ 
		if( selJoin != ''){
			var sels = $A(td.getElementsByTagName('SELECT'));
			if(sels.length >= 1){
				for(i=0;i<sels[0].length;i++){
					if(sels[0][i].value == selJoin){
						sels[0].options.selectedIndex = i;
					}
				}
			}
		}
		if( selFilter != ''){
			var sels = $A(td1.getElementsByTagName('SELECT'));
			if(sels.length >= 1){
				for(var i=0;i<sels[0].length;i++){
					if(sels[0][i].value == selFilter){
						sels[0].options.selectedIndex = i;
					}
				}
			}
		}				

		if( selCondition != ''){
			var sels = $A(td2.getElementsByTagName('SELECT'));
			if(sels.length >= 1){
				for(var i=0;i<sels[0].length;i++){
					if(sels[0][i].value == selCondition){
						sels[0].options.selectedIndex = i;
					}
				}
			}
		}	
		
		if( selAccess != ''){
			var sels = $A(td4.getElementsByTagName('SELECT'));
			if(sels.length >= 1){
				for(var i=0;i<sels[0].length;i++){
					if(sels[0][i].value == selAccess){
						sels[0].options.selectedIndex = i;
					}
				}
			}
		}					
		this.counter ++;
	}
	
});