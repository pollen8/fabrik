<?php
header('Content-type: text/css');
$c = (int)$_REQUEST['c'];
echo "

#form_$c legend{
	background-color: #c0c0c0;
	-moz-user-select: none;
	border-bottom: 1px solid #B7B7B7;
	/* border-top: 1px solid #B7B7B7; */
	color: #777777;
	font-weight: bold;
	margin: 0;
	padding:0;
	text-shadow: 0 1px 0 #FFFFFF;
  zoom: 1;
	text-transform: uppercase;
	width:100%;
	background: -moz-linear-gradient(center top , #F3F3F3, #D7D7D7) repeat scroll 0 0 #E7E7E7;
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#D7D7D7', endColorstr='#F3F3F3'); /* for IE */
	background: -webkit-gradient(linear, left top, left bottom, from(#F3F3F3),
		to(#D7D7D7) );
		
		position:absolute;
}

#form_$c legend span{
	padding:5px;
	display:block;
}

#form_$c{
	width:100%;
	background-color:#FAFAFA;
	border:1px solid #DDDDDD;
}

#form_$c h1{
	paddiing-left:10px;
	margin:0;
}

#form_$c fieldset{
	margin:5px 10px;
	position:relative;
	padding:0;
	border:1px solid #DDDDDD;
}

#form_$c fieldset ul,
#details_$c fieldset ul{
	list-style:none;
	padding:40px 10px 20px 10px;
	margin:0;
}

#form_$c .fabrikForm .fabrikGroup ul{
	list-style:none;
}

#details_$c .fabrikGalleryImage{
	border:1px solid #ccc;
	margin:5px;
	padding:5px;
}

/* START: align google map sub elements vertically */

.googlemap .fabrikSubElementContainer{
	-moz-box-orient:vertical;
	-webkit-box-orient:vertical;
	box-orient:vertical;
}

.googlemap .fabrikSubElementContainer > div{
	-mox-box-flex: 1;
	-webkit-box-flex: 1;
	box-flex: 1;
}

/* END: align google map sub elements vertically */
/* START : label spacing for chxbox, radios */

#form_$c label span{
	padding:0 4px;
}

/* END : label spacing for chxbox, radios */

.floating-tip {
	background-color: #fff;
	font-weight: bold;
	font-size: 11px;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
}

#form_$c .linkedTables{
	margin:0.6em 0;
}

#form_$c  .related_data_norecords{
	display:inline;
}

#form_$c .fabrikForm .fabrikGroup ul .fabrikElementContainer,
#details_$c .fabrikElementContainer,
#form_$c .fabrikElementContainer{
	padding:5px 10px;
	margin-top:10px;
	background:none !important;
	display:-webkit-box;
	display:-moz-box;
	display:box;
	width:50%;
}

#form_$c .fabrikActions{
	padding:10px;
	clear:left;
	margin:5px 10px;
	border:1px solid #DDDDDD;
}
#form_$c .fabrikActions input{
	margin-right:7px;
}

#form_$c .fabrikValidating{
	color: #476767;
	background: #EFFFFF url(../images/ajax-loader.gif) no-repeat right 7px !important;
}

#form_$c .fabrikSuccess{
	color: #598F5B;
	background: #DFFFE0 url(../images/action_check.png) no-repeat right 7px !important;
}

/*** slide out add option
section for dropdowns radio buttons etc**/

#form_$c .addoption dl{
	display:inline;
	width:75%;
}
#form_$c .addoption{
	clear:left;
	padding:8px;
	margin:3px 0;
	background-color:#efefef;
}

#form_$c  a.toggle-addoption, a.toggle-selectoption{
	padding:0 0 0 10px;
}


/*** end slide out add option section **/

#form_$c input,
#form_$c select{
	border:1px solid #DDDDDD;
	border-radius:3px;
	padding:3px;
}

#form_$c  .inputbox:focus{
	background-color:#ffffcc;
	border:1px solid #aaaaaa;
}

#form_$c .addoption dd, .addoption dt{
	padding:2px;
	display:inline;
}

#form_$c .fabrikSubGroup{
	clear:both;
}

#form_$c .fabrikSubGroupElements{
	width:80%;
	float:left;
}

#form_$c .geo{
	visibility:hidden;
}


#form_$c .fabrikGroup .readonly,
#form_$c .fabrikGroup .disabled{
	background-color:#DFDFDF !important;
	color:#8F8F8F;
}

/*** fileupload folder select css **/
#form_$c ul.folderselect{
	border:1px dotted #eee;
	background-color:#efefef;
	color:#333;
}

#form_$c .folderselect-container{
	border:1px dotted #666;width:350px;
}

#form_$c .fabrikForm .breadcrumbs{
	background: transparent url(../images/folder_open.png) no-repeat center left;
	padding:2px 2px 2px 26px ;
}

#form_$c .fabrikForm .fabrikGroup li.fileupload_folder{
	background: transparent url(../images/folder.png) no-repeat center left;
	padding:2px 2px 2px 26px ;
	margin:2px;
}

#form_$c .fabrik_characters_left{
clear:left;
}

/** bump calendar above mocha window in mootools 1.2**/
#form_$c div.calendar{
	z-index:115 !important;
}

/** special case for 'display' element with 'show label: no' option **/
#form_$c .fabrikPluginElementDisplayLabel {
	width: 100% !important;
}

/** autocomplete container inject in doc body not in #forn_$c */
.auto-complete-container{
	overflow-y: hidden;
	border:1px solid #ddd;
	z-index:100;
}

.auto-complete-container ul{
list-style:none;
padding:0;
margin:0;
}

.auto-complete-container li.unselected{
	padding:2px 10px !important;
	background-color:#fff !important;
	margin:0 !important;
	border-top:1px solid #ddd;
	cursor:pointer;
}

.auto-complete-container li:hover,
.auto-complete-container li.selected{
	background-color:#DFFAFF !important;
	cursor:pointer;
}
#form_$c .leftCol,
#details_$c .leftCol,
#form_$c .fabrikSubLabel{
	width: 130px;
}
#details_$c .leftCol{
	color:#999;
}

#form_$c .fabrikElement {
	margin-left: 10px;
	-webkit-box-flex:1;
	-moz-box-flex:1;
	box-flex:1;
}

#form_$c .addbutton {
	background: transparent url(images/add.png) no-repeat left;
	padding: 2px 5px 0 20px;
	margin-left:7px;
}

#form_$c .fabrikError,
#form_$c .fabrikNotice,
#form_$c .fabrikValidating,
#form_$c .fabrikSuccess{
	font-weight: bold;
}

#form_$c .fabrikMainError{
	height:2em;
	line-height:2em;
}

#form_$c .fabrikMainError img{
	padding:0.35em 1em;
	float:left;
}

#form_$c .fabrikNotice{
	color: #009FBF;
	background: #DFFDFF url(images/alert.png) no-repeat center left !important;
}

#form_$c .fabrikError,
#form_$c .fabrikGroup .fabrikError{
	color: #c00;
	background: #EFE7B8;
}

#form_$c .fabrikErrorMessage{
	padding-right: 5px;
}



#form_$c .fabrikLabel {
	min-height:1px; /*for elements with no label txt*/
}

#form_$c .fabrikActions {
	padding-top: 15px;
	clear: left;
	padding-bottom: 15px;
}

#form_$c .fabrikGroupRepeater {
	float: left;
	width: 19%;
}

/** used by password element */
#form_$c .fabrikSubLabel {
	margin-left: -10px;
	clear: left;
	margin-top: 10px;
	float: left;
}

#form_$c .fabrikSubElement {
	display: block;
	margin-top: 10px;
	margin-left: 100px;
}

/*
some fun with fancy buttons not ready for prime time

#form_$c .button{
background: -moz-linear-gradient(center top , #ccc 0%, #777) repeat scroll 0 0 transparent;
border: 1px solid #614337;
border-radius: 6px 6px 6px 6px;
box-shadow: 0 1px 2px rgba(0, 0, 0, 0.5), 0 0 2px rgba(255, 255, 255, 0.6) inset;
color: #FFFFFF;
margin: 10px;
padding: 5px 20px;

}

#form_$c .button:hover{
background: -moz-linear-gradient(center top , #E88801 0%, #C93C00) repeat scroll 0 0 transparent; /* orange */
background: -moz-linear-gradient(center top , #8EC400 0%, #558A01) repeat scroll 0 0 transparent; /* green */
text-shadow: 0 -1px 0 #000000, 0 1px 0 rgba(255, 255, 255, 0.2);
box-shadow: 0 1px 1px rgba(0, 0, 0, 0.5), 0 0 1px rgba(255, 255, 255, 0.6) inset;

}

#form_$c .button[name=delete]:hover{
background: -moz-linear-gradient(center top , #E88801 0%, #C93C00) repeat scroll 0 0 transparent;
}

#form_$c .button[name=Reset]:hover{
background: -moz-linear-gradient(center top , #E3EB01 0%, #B19F01) repeat scroll 0 0 transparent;
} */
";
?>