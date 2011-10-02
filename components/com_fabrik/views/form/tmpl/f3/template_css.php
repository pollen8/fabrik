<?php
header('Content-type: text/css');
$c = (int)$_REQUEST['c'];
echo "

#form_$c fieldset ul{
	list-style:none;
}

#form_$c {
	border-radius: 40px;
	background: none repeat scroll 0 0 rgba(0, 0, 0, 0.1);
	border-bottom: 1px solid rgba(0, 0, 0, 0.07);
	box-shadow: 0 1px 0 rgba(0, 0, 0, 0.15) inset, 0 1px 4px rgba(0, 0, 0, 0.1) inset, 0 1px 0 rgba(255, 255, 255, 0.05);
	color: #CCCCCC;
	text-shadow: 0 1px 0 rgba(0, 0, 0, 0.5);
	padding: 19px;
	position: relative;
}

#form_$c fieldset{
	border:1px solid;
	border-radius: 10px;
	padding-bottom:20px;
}

#form_$c h1{
	font-family:\"UbuntuRegular\";
	font-size: 2em;
	font-weight: bold;
	text-transform: uppercase;
}

#form_$c legend{
	background: -moz-linear-gradient(center top , #1C1C1C, transparent) repeat scroll 0 0 transparent;
	border-radius: 10px 10px 10px 10px;
	display: block;
	font-size: 1.5em;
	height: 30px;
	padding: 15px 10px 10px 27px;
	position: absolute;
	width: 873px;
	font-family:\"UbuntuRegular\";
	text-transform:uppercase;
	font-weight:bold;
}

#form_$c ul{
	list-style:none;
	padding:27px 20px;
}
.floating-tip {
	background-color: black;
	padding: 5px 15px;
	color: #dddddd;
	font-weight: bold;
	font-size: 11px;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	z-index:10001;
}

#form_$c  .linkedTables{
	margin:0.6em 0;
}

#form_$c  .related_data_norecords{
	display:inline;
}

#form_$c .fabrikForm .fabrikGroup ul,
#form_$c .fabrikForm .fabrikGroup li{
	padding:0;
	margin:0;
}


#form_$c .fabrikForm .fabrikGroup ul li.fabrikElementContainer,
#form_$c li.fabrikElementContainer{
	padding:5px 10px;
	margin-top:10px;
	background:none !important;
}

#form_$c .fabrikActions{
	padding-top:15px;
	clear:left;
	padding-bottom:15px;
}


#form_$c .fabrikElement{
	clear:left;
}

#form_$c .fabrikLabel{
float:left;
	padding-bottom:5px;
	min-height:1px; /*for elements with no label txt*/

	/*ensures label text doesnt overrun validation icons*/
	padding-right:10px;
	z-index:99999;
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


#form_$c  .inputbox:focus{
	background-color:#ffffcc;
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

/** autocomplete container inject in doc body not iin #forn_$c */
.auto-complete-container{
	overflow-y: hidden;
	border:1px solid #ddd;
	z-index:100;
}

.auto-complete-container ul{list-style:none;}

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

#form_$c .addbutton {
	background: transparent url(images/add.png) no-repeat left;
	padding: 2px 5px 0 20px;
	margin-left:7px;
}

#form_$c .fabrikError,#form_$c .fabrikNotice,#form_$c .fabrikValidating,#form_$c .fabrikSuccess{
	margin: 0;
	font-weight: bold;
	margin-bottom: 10px;
	padding:7px;
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
#form_$c .fabrikGroup li.fabrikError{
	color: #c00;
	background: #EFE7B8;
}

/** for checkboxes etc with multiple columns, the error was too squashed */
#form_$c .fabrikError .fabrikSubElementContainer{
	margin-top: 20px;
}

#form_$c .fabrikErrorMessage{
	padding-right: 5px;
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
";
?>