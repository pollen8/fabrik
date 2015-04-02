/*! fabrik */
var FbDisplay=new Class({Extends:FbElement,initialize:function(a,b){this.parent(a,b)},update:function(a){this.getElement()&&(this.element.innerHTML=a)}});