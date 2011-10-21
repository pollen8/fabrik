<?php
header('Content-type: text/css');
$c = (int)$_REQUEST['c'];
$view = $_REQUEST['view'];
echo "

.container{
	position:relative;
}

#{$view}_$c div.fields{
	width:700px;
}

.event-overview{
	width:250px;
	position:absolute;
	top:0;
	right:0;
}

.fabrik_buttons input[name=submit] {
	background: url(images/check.png) no-repeat 5px 50%, -moz-linear-gradient(right bottom 67deg, #cfcdcd, #e5e5e5 50%);
	padding-left:25px;
	background: url(images/check.png) no-repeat 5px 50%, -webkit-gradient(linear, right bottom, left top, from(#cfcdcd),
		color-stop(50%, #e5e5e5) );
}

.fabrik_buttons input[name=delete] {
	background: url(images/trash.png) no-repeat 5px 50%, -moz-linear-gradient(right bottom 67deg, #cfcdcd, #e5e5e5 50%);
	padding-left:25px;
	background: url(images/trash.png) no-repeat 5px 50%, -webkit-gradient(linear, right bottom, left top, from(#cfcdcd),
		color-stop(50%, #e5e5e5) );
}

.fabrik_buttons input[name=Reset] {
	background: url(images/cross.png) no-repeat 5px 50%, -moz-linear-gradient(right bottom 67deg, #cfcdcd, #e5e5e5 50%);
	padding-left:25px;
	background: url(images/cross.png) no-repeat 5px 50%, -webkit-gradient(linear, right bottom, left top, from(#cfcdcd),
		color-stop(50%, #e5e5e5) );
}



/* tabs */
#{$view}_$c dl.tabs,
.event-overview dl.tabs{
    float: left;
    margin: 0px 0 -1px 0;
    z-index: 50;
}

#{$view}_$c dl.tabs dt,
.event-overview dl.tabs dt {
    float: left;
    padding: 4px 10px;
    border: 1px solid #e0dfdf;
    margin-right: 3px;
    background: #ebebeb;
    color: #fff;
    font-weight:bold;
    font-family:'PT Sans', sans-serif;
    text-transform:uppercase;
    font-size:1.25em;
    border-radius:5px 5px 0 0;
}

#{$view}_$c dl.tabs dt.open {
    background: #f3f3f3;
    border-bottom: 1px solid #f3f3f3;
    z-index: 100;
    color: #de0b1f;
}

#{$view}_$c dl.tabs dt:hover{
    color: #de0b1f;
    background: #f3f3f3;
}

.event-overview dl.tabs dt,
.event-overview dl.tabs dt.open  {
	background:#F3F3F3;
	border-bottom:1px solid #F3F3F3;
	color:#86b6cd;
}

.form-sidebar .fabrikGroup{
padding:10px;
}

.form-sidebar h1{
	padding:0;
	margin:0;
}

.form-sidebar ul{
	list-style:none;
}
.form-sidebar ul, 
.form-sidebar li{
 	padding:0;
 	margin:0;
}



#{$view}_$c div.current,
.event-overview div.current {
  clear: both;
  padding: 10px 10px;
  background-color:#f3f3f3;
	border:1px solid #e0dfdf;
	border-radius:0 5px 5px 5px;
}

#{$view}_$c div.current dd {
    padding: 0;
    margin: 0;
}

#{$view}_$c dd {
    border: 1px solid transparent !important;
}

/**** end tabs ******/

#{$view}_$c .fabrikGroup,
.event-overview .fabrikGroup{
	background-color:#fff;
}

/* podion admin tmpl override */
#{$view}_$c .current input, 
#{$view}_$c .current textarea,
#{$view}_$c .current select{
	float:none;
}

.div.current label, div.current span.faux-label{
	margin-top:0;
} 

#{$view}_$c > ul{
	padding:10px;
}

#{$view}_$c ul{
	list-style-type:none;
}


/** standard ***/


#{$view}_$c fieldset ul,
#details_$c fieldset ul{
	list-style:none;
	padding:0;
	margin:0;
}

#{$view}_$c .fabrikForm .fabrikGroup ul{
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

#{$view}_$c label span{
	padding:0 4px;
}

/* END : label spacing for chxbox, radios */

#{$view}_$c  .linkedTables{
	margin:0.6em 0;
}

#{$view}_$c  .related_data_norecords{
	display:inline;
}

#{$view}_$c .fabrikForm .fabrikGroup ul .fabrikElementContainer,
#details_$c .fabrikElementContainer,
#{$view}_$c .fabrikElementContainer{
	padding:5px 10px;
	margin-top:10px;
	background:none !important;

}

#{$view}_$c .fabrikActions{
	padding-top:15px;
	clear:left;
	position:absolute;
	top:20px;
	right:0;
}

#{$view}_$c .twopane{
	position:relative;
	margin-top:20px;
}

#{$view}_$c h1{
	padding-left:0;
	margin-left:0;
}

#{$view}_$c .fabrikLabel{
	/*ensures label text doesnt overrun validation icons*/
	padding-right:10px;
	z-index:99999;
}

#{$view}_$c .fabrikValidating{
	color: #476767;
	background: #EFFFFF url(../images/ajax-loader.gif) no-repeat right 7px !important;
}

#{$view}_$c .fabrikSuccess{
	color: #598F5B;
	background: #DFFFE0 url(../images/action_check.png) no-repeat right 7px !important;
}

/*** slide out add option
section for dropdowns radio buttons etc**/

#{$view}_$c .addoption dl{
	display:inline;
	width:75%;
}
#{$view}_$c .addoption{
	clear:left;
	padding:8px;
	margin:3px 0;
	background-color:#efefef;
}

#{$view}_$c  a.toggle-addoption, a.toggle-selectoption{
	padding:0 0 0 10px;
}

/*** end slide out add option section **/


#{$view}_$c  .inputbox:focus{
	background-color:#ffffcc;
	border:1px solid #aaaaaa;
}

#{$view}_$c .addoption dd, .addoption dt{
	padding:2px;
	display:inline;
}

#{$view}_$c .fabrikSubGroup{
	clear:both;
}

#{$view}_$c .fabrikSubGroupElements{
	width:80%;
}

#{$view}_$c .geo{
	visibility:hidden;
}



#{$view}_$c .fabrikGroup .readonly,
#{$view}_$c .fabrikGroup .disabled{
	background-color:#DFDFDF !important;
	color:#8F8F8F;
}

/*** fileupload folder select css **/
#{$view}_$c ul.folderselect{
	border:1px dotted #eee;
	background-color:#efefef;
	color:#333;
}

#{$view}_$c .folderselect-container{
	border:1px dotted #666;width:350px;
}

#{$view}_$c .fabrikForm .breadcrumbs{
	background: transparent url(../images/folder_open.png) no-repeat center left;
	padding:2px 2px 2px 26px ;
}

#{$view}_$c .fabrikForm .fabrikGroup li.fileupload_folder{
	background: transparent url(../images/folder.png) no-repeat center left;
	padding:2px 2px 2px 26px ;
	margin:2px;
}

#{$view}_$c .fabrik_characters_left{
clear:left;
}

/** bump calendar above mocha window in mootools 1.2**/
#{$view}_$c div.calendar{
	z-index:115 !important;
}

/** special case for 'display' element with 'show label: no' option **/
#{$view}_$c .fabrikPluginElementDisplayLabel {
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
#{$view}_$c .leftCol,
#details_$c .leftCol,
#{$view}_$c .fabrikSubLabel{
	width: 130px;
}
#details_$c .leftCol{
	color:#999;
}


#{$view}_$c .addbutton {
	background: transparent url(images/add.png) no-repeat left;
	padding: 2px 5px 0 20px;
	margin-left:7px;
}

#{$view}_$c .fabrikError,
#{$view}_$c .fabrikNotice,
#{$view}_$c .fabrikValidating,
#{$view}_$c .fabrikSuccess{
	font-weight: bold;
}

#{$view}_$c .fabrikMainError{
	height:2em;
	line-height:2em;
}

#{$view}_$c .fabrikMainError img{
	padding:0.35em 1em;
	float:left;
}

#{$view}_$c .fabrikNotice{
	color: #009FBF;
	background: #DFFDFF url(images/alert.png) no-repeat center left !important;
}

#{$view}_$c .fabrikError,
#{$view}_$c .fabrikGroup .fabrikError{
	color: #c00;
	background: #EFE7B8;
}

#{$view}_$c .fabrikErrorMessage{
	padding-right: 5px;
}

#{$view}_$c .fabrikLabel {
	min-height:1px; /*for elements with no label txt*/
}

#{$view}_$c .fabrikActions {
	padding-top: 15px;
	clear: left;
	padding-bottom: 15px;
}

#{$view}_$c .fabrikGroupRepeater {
	float: left;
	width: 19%;
}

/** used by password element */
#{$view}_$c .fabrikSubLabel {
	margin-left: -10px;
	clear: left;
	margin-top: 10px;
	float: left;
}

#{$view}_$c .fabrikSubElement {
	display: block;
	margin-top: 10px;
	margin-left: 100px;
}
";?>
