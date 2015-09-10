/*! Fabrik */
FbYesno=new Class({Extends:FbRadio,initialize:function(a,b){this.plugin="fabrikyesno",this.parent(a,b)},getChangeEvent:function(){return this.options.changeEvent}});