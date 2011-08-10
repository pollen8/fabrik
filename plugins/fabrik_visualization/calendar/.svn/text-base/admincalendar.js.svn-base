
///this was from old calendar code, plus the class name looks suspect?/////////////////
var adminVisualizationCalendar = new Class({
	initialize: function(){
		this.options = Object.extend({
		}, arguments[0] || {});
		
		this.translate = Object.extend({
			'connection':'',
			'table':'',
			'selectConnection':'',
			'startdate':'',
			'enddate':'',
			'selectTable':'',
			'label':'',
			'pleaseSelect':'',
			'del':'',
			'key':'',
			'colour':''
		}, arguments[1] || {});
		this.counter = 0;
		this.watchConnections();
		this.changeConnectionclick = this.changeConnection.bindWithEvent(this);
		this.addDataGroupClick = this.addDataGroup.bindWithEvent(this);
		//$('page-calendar').getElement('.addButton').addEvent('click',this.addDataGroupClick);
		head.ready(function() {
		$('page-calendar').getElement('.addButton').addEvent(function(e){
			alert(e);
		});
		})
	},
	
	_makeCnnDd: function(sel){
		var opts = [];
		opts.push( new Element('option', {'value':'-1'}).appendText(this.translate.pleaseSelect));
		this.options.conections.each(function(o){
			if(o.id == sel){
				opts.push( new Element('option', {'value':o.id, 'selected':'selected'}).appendText(o.text));
			}else{
				opts.push( new Element('option', {'value':o.id}).appendText(o.text));
			}
		});
		
		var list = new Element('select', {
			'name':'params[connection_id][]',
			'class':'inputbox connections'
			}).adopt(opts);
		return list;
	},
	
	_makeTableDd: function(cnnId, sel, sName){
		var opts = [];
		opts.push( new Element('option', {'value':'-1'}).appendText(this.translate.pleaseSelect));
		tables = this.options.tableList[cnnId];
		tables.each(function(o){
			if(o == sel){
				opts.push( new Element('option', {'value':o, 'selected':'selected'}).appendText(o));
			}else{
				opts.push( new Element('option', {'value':o}).appendText(o));
			}
		});
		
		var list = new Element('select', {
			'name':sName,
			'class':'inputbox table'
			}).adopt(opts);
		return list;
	},
	
	_makeFieldDd: function(cnnId, tblName, sel, sName){
		var opts = [];
		opts.push( new Element('option', {'value':'-1'}).appendText(this.translate.pleaseSelect));
		fields = this.options.fieldList[cnnId][tblName];
		fields.each(function(o){
			if(o == sel){
				opts.push( new Element('option', {'value':o, 'selected':'selected'}).appendText(o));
			}else{
				opts.push( new Element('option', {'value':o}).appendText(o));
			}
		});
		
		var list = new Element('select', {
			'name':sName,
			'class':'inputbox table'
			}).adopt(opts);
		return list;
	},
	
	addDataGroup: function(e){
		new Event(e).stop();
		this.makeDataGroup();
	},
	
	watchDelete: function(){
		$$('.deleteDate').each( function(dd){
			dd.removeEvents('click');
		});
		
		$$('.deleteDate').each( function(cn){
			cn.addEvent('click', function(e){
				this.deleteConnection(e);
			}.bind(this));
		}.bind(this))
	},
	
	deleteConnection: function(event){
		var e= new Event(event);
		this.watchDelete();
		e.target.getParent().getParent().getParent().getParent().dispose()
		this.watchConnections();
		e.stop();
	},
	
	makeDataGroup: function(cnnId, tblName,  fldStartDate, fldEndDate, fldLabel, keyLabel, colour){
		var cnnList = this._makeCnnDd(cnnId);
		keyLabel = keyLabel ? keyLabel : '';
		colour = colour ? colour : '#CCCCFF';
		var tblList = tblName ? this._makeTableDd(cnnId, tblName, 'params[table][]') : this.translate.selectConnection;
		
		var fieldList1 = fldStartDate ? this._makeFieldDd(cnnId, tblName, fldStartDate, 'params[table_startdate][]') : this.translate.selectTable;
		var fieldList1b = fldEndDate ? this._makeFieldDd(cnnId, tblName, fldEndDate, 'params[table_enddate][]') : this.translate.selectTable;
		var fieldList2 = fldLabel ? this._makeFieldDd(cnnId, tblName, fldLabel, 'params[table_label][]') : this.translate.selectTable;
		
		var table = new Element('table', {'width':'100%', 'class':'adminform'}).adopt(
			new Element('tbody', {'id':'datesContainer_' + this.counter }).adopt(
			[
				new Element('tr').adopt(
				[
					new Element('td').appendText(this.translate.connection),
					new Element('td').adopt(cnnList),
					new Element('td').adopt(
						new Element('a', {'class':'deleteDate', 'href':'#'}).appendText(this.translate.del)
					)
				]),
				new Element('tr').adopt(
				[
					new Element('td').appendText(this.translate.table),
					new Element('td', {'colspan':'2'}).adopt(tblList)
				]),
				new Element('tr').adopt(
				[
					new Element('td').appendText(this.translate.date),
					new Element('td', {'colspan':'2'}).adopt(fieldList1)
				]),
				new Element('tr').adopt(
				[
					new Element('td').appendText(this.translate.date),
					new Element('td', {'colspan':'2'}).adopt(fieldList1b)
				]),
				new Element('tr').adopt(
				[
					new Element('td').appendText(this.translate.label),
					new Element('td', {'colspan':'2'}).adopt(fieldList2)
				]),
				new Element('tr').adopt(
				[
					new Element('td').appendText(this.translate.key),
					new Element('td', {'colspan':'2'}).adopt(
						new Element('input', {'type':'text','name':'params[key][]','value':keyLabel})
					)
				]),
				new Element('tr').adopt(
				[
					new Element('td').appendText(this.translate.colour),
					new Element('td', {'colspan':'2'}).adopt(
						new Element('input', {'type':'text','name':'params[colour][]','value':colour})
					)
				])
			])
		)
		table.inject($('datesContainer'));
		this.counter ++;
		this.watchDelete();
		this.watchConnections();
	},
	
	watchConnections: function(){
		$$('.connections').each( function(dd){
			dd.removeEvents('change');
		});
		
		$$('.connections').each( function(cn){
			cn.addEvent('change', function(e){
				this.changeConnection(cn);
			}.bind(this));
		}.bind(this))
	},
	
	watchTableDd: function(){
		$$('.table').each( function(tbl){
			tbl.addEvent('change', function(e){
				this.changeTable(tbl);
			}.bind(this));
		}.bind(this))		
	},
	
	changeTable: function(tbl){
		var cid = tbl.getParent().getParent().getPrevious().getChildren()[1].getChildren()[0].get('value');
		var targ = tbl.getParent().getParent().getNext().getChildren()[1];
		var targ1 = tbl.getParent().getParent().getNext().getChildren()[2];
		var targ2 = tbl.getParent().getParent().getNext().getNext().getChildren()[1];
		var tid = tbl.get('value');
		var url = 'index.php?option=com_fabrik&c=table&class=table&format=raw&task=ajax_loadTableDropDown&cid=' + cid + '&table=' + tid + '&name=params[table_startdate][]';
		var myAjax = new Request({url:url,method:'post', 
			onComplete: function(r){
				targ.innerHTML = r;
				this.watchTableDd();
			}.bind(this)}
		).send();
		
		var url = 'index.php?option=com_fabrik&c=table&class=table&format=raw&task=ajax_loadTableDropDown&cid=' + cid + '&table=' + tid + '&name=params[table_enddate][]';
		var myAjax = new Request({url:url, method:'post', 
			onComplete: function(r){
				targ1.innerHTML = r;
				this.watchTableDd();
			}.bind(this)}
		).send();
		
		var url = 'index.php?option=com_fabrik&c=table&class=table&format=raw&task=ajax_loadTableDropDown&cid=' + cid + '&table=' + tid + '&name=params[table_label][]';
		var myAjax = new Request({url:url, method:'post', 
			onComplete: function(r){
				targ2.innerHTML = r;
				this.watchTableDd();
			}.bind(this)}
		).send();
		
	},
	
	changeConnection: function(cn){
		var cid = cn.get('value');
		var targ = cn.getParent().getParent().getNext().getChildren()[1];
		if( cid != -1){
			var url = 'index.php?option=com_fabrik&c=table&class=table&format=raw&task=ajax_loadTableListDropDown&cid=' + cid + '&name=params[table][]';
			var myAjax = new Request({url:url, method:'post', 
				onComplete: function(r){
					targ.innerHTML = r;
					this.watchTableDd();
				}.bind(this)}).send();
		}
	}
});
