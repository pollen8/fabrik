<?php
header('Content-type: text/css');
$c = (int)$_REQUEST['c'];
echo "
#listform_$c .fabrikForm {
	margin-top: 15px;
}

#listform_$c table.fabrikList tr.fabrik_calculations td {
	border: 0 !important;
}

#listform_$c .firstPage,
#listform_$c .previousPage,
#listform_$c .aPage,
#listform_$c .nextPage,
#listform_$c .lastPage {
	display: inline;
	padding: 3px;
}

#listform_$c table.filtertable {
	width: 50%;
}

#listform_$c td.decimal,
#listform_$c td.integer{
	text-align:right;
}

#listform_$c .fabrikHover {
	background-color: #ffffff;
}

/** highlight the last row that was clicked */
#listform_$c .fabrikRowClick {
	background-color: #ffffff;
}

/** highlight the loaded row - package only */
#listform_$c .activeRow {
	background-color: #FFFFCC;
}

#listform_$c .emptyDataMessage {
	background-color: #EFE7B8;
	border-color: #EFD859;
	border-width: 2px 0;
	border-style: solid;
	padding: 5px;
	margin: 10px 0;
	font-size: 1em;
	color: #CC0000;
	font-weight: bold;
}

#listform_$c .tablespacer {
	height: 20px;
}

#listform_$c .fabrikButtons {
	text-align: right;
	padding-top: 10px;
}

#listform_$c ul.fabrikRepeatData {
	padding: 0;
	margin: 0;
	list-style: none;
}

#listform_$c ul.fabrikRepeatData li {
	background-image: none;
	padding: 0;
	margin: 0;
}
/*****************************************************/
/********** default action formatting ****************/
/*****************************************************/

#listform_$c .fabrik_row ul.fabrik_action{
	list-style:none !important;
	border:1px solid #999;
	padding:0;
	text-align:left;
	font-weight: bold;
	font-size: 11px;
	-moz-border-radius: 6px;
	-webkit-border-radius: 6px;
	border-radius: 6px;
	height:23px;
	margin:5px;
}

#listform_$c .fabrik_row ul.fabrik_action span{
	display:none;
}

#listform_$c .fabrik_row .fabrik_action li{
float:left;
	filter: progid : DXImageTransform.Microsoft.gradient ( startColorstr =
		'#cccccc', endColorstr = '#666666' ); /* for IE */
	background: -webkit-gradient(linear, left top, left bottom, from(#ccc),
		to(#666) ); /* for webkit browsers */
	background: -moz-linear-gradient(top, #eee, #ccc);
	padding:2px 3px 0 3px;
	border-left:1px solid #999;
	height:21px;
}

#listform_$c .fabrik_row .fabrik_action li:first-child{
-moz-border-radius: 6px 0 0 6px;
	-webkit-border-radius: 6px 0 0 6px;
	border-radius: 6px 0 0 6px;
	border:0;
}

#listform_$c .fabrik_row .fabrik_action li:last-child{
-moz-border-radius: 0 6px 6px 0;
	-webkit-border-radius: 0 6px 6px 0;
	border-radius: 0 6px 6px 0;
}

";?>