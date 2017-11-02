/*! Fabrik */

define(["jquery","element/radiobutton/radiobutton"],function(a,b){return window.FbYesno=new Class({Extends:b,initialize:function(a,b){this.setPlugin("fabrikyesno"),this.parent(a,b)},checkEventAction:function(a){return"change"===a&&(a="click"),a},getChangeEvent:function(){return this.options.changeEvent}}),window.FbYesno});