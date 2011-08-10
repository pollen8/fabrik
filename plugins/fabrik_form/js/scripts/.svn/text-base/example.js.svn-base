//class name needs to be the same as the file name
	var example = new Class({

	initialize: function(form)
	{
		this.form = form; //the form js object
	},
	
	//run when submit button is pressed
	onSubmit: function()
	{
		alert('onSubmit');
		//return false if you want the form to stop submission
	},
	
	//run once the form has sucessfully submitted data via ajax
	onAjaxSubmitComplete: function(){
		alert('complete');
	},
	
	onDoElementFX: function(){
		alert('onDoElementFX');
	},
	
	//run at the start of saving a group to the db
	// when you move from one group to another on
	//multipage forms 
	saveGroupsToDb: function(){
		alert('saveGroupsToDb');
	},
	
	//run once the ajax call has completed when moving from one group to another
	//on multipage forms
	onCompleteSaveGroupsToDb: function(){
		alert('onCompleteSaveGroupsToDb');
	},
	
	//run each time you move from one group to another on
	//multipage forms 
	onChangePage: function(){
		alert('onChangePage');
	},
	
	//run if the form has ajax validaton
	//run at start of element validaton that occurs on that elements onblur event
	onStartElementValidation: function(){
		alert('onStartElementValidation');
	},
	
	//run when above element validation's ajax call is completed
	onCompleteElementValidation: function(){
		alert('onCompleteElementValidation');
	},
	
	//called when a repeatable group is deleted
	onDeleteGroup: function(){
		alert('onDeleteGroup');
	},
	
	//called when a repeat group is duplicated
	onDuplicateGroup: function(){
		alert('onDuplicateGroup');
	},
	
	//called when the form gets updated
	onUpdate: function(){
		alert('onUpdate');
	},
	
	//called when the form is reset
	onReset: function(){
	}
	
});