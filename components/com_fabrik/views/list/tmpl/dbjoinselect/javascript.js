/**
 * @author Robert
 */

var TableRowSelect = new Class({
	initialize:function(triggerEl, formid){
		this.triggerEl = triggerEl;
		this.formid = formid;
		head.ready(function() {
			this.setUp();
		}.bind(this));	
	},

	setUp : function() {
		document.getElements('.fabrikList').each(function(tbl) {
			if (!tbl.hasClass('filtertable')) {
				this.listid = tbl.id.replace('list_', '');
				tbl.getElements('.fabrik_row').each(function(r) {

					$(r).addEvent('mouseover', function(e) {
						if (r.hasClass('oddRow0') || r.hasClass('oddRow1')) {
							r.addClass('fabrikHover');
						}
					}, r);

					$(r).addEvent('mouseout', function(e) {
						r.removeClass('fabrikHover');
					}, r);
				});
			
			
				tbl.getElements('.fabrik_row').each(function(r) {
					$(r).addEvent('click', function(e) {
						var d = $A(r.id.split('_'));
						var data = {};
						data[this.triggerEl] = d.getLast();
						var json = {
								'errors' : {},
								'data' : data,
								'rowid':d.getLast(),
								formid:this.formid
							};
						Fabrik.fireEvent('fabrik.list.row.selected', json);
					}.bind(this));
				}.bind(this));
			}
		}.bind(this));
	}
});