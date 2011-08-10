<?php
header('Content-type: text/css');
$c = (int)$_REQUEST['c'];
echo "
#listform_$c .oddRow1{
	background-color:#8dcff3;
}

#listform_$c .oddRow0{
	background-color:#ace2ff;
}

#listform_$c .fabrikList,
#listform_$c  .filtertable{
	margin:5px;
	border-collapse:collapse;
	border:1px solid #666666!important;
	color:#2e2e2e;
	font-weight:bold;
}

#listform_$c .fabrikList a{
    color:#2e2e2e;
}

#listform_$c .fabrikList td{
	border-bottom:1px solid #dddddd!important;
}

#listform_$c .fabrikList td, .fabrikList th,
#listform_$c .filtertable td, .filtertable th{
	padding:4px !important;
}

#listform_$c .fabrikHover{
	background-color:#e7f6fe;
}

#listform_$c .fabrikRowClick{
	background-color:#edf9ff;
}

#listform_$c .fabrikList th,
#listform_$c .filtertable th{
	background: #595959 url(icons/bluesky-heading.gif) bottom left repeat-x !important;
	color:#edf9ff;
	white-space: nowrap;
	padding-bottom:9px !important;
}

#listform_$c .filtertable th a,
#listform_$c .fabrikList th a{
	color:#edf9ff;
}


#listform_$c .emptyDataMessage{
	text-align:center;
	border:2px solid #8dcff3;
	font-size:1.3em;
	font-weight:bold;
	padding:10px;
	background-color:#D8EBF0;
	margin-bottom:20px;
	color:#2e2e2e;
	margin:5px;
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
/*****************************************************/
/********** the context menu that appears ************/
/********** when you click row checkboxes ************/
/*****************************************************/

.floating-tip a:link,
.floating-tip a:visited,
.floating-tip a{
	text-decoration:none;
}
.floating-tip {
	border:1px solid #333;
	padding:0;
	text-align:left;
	font-weight: bold;
	font-size: 11px;
	-moz-border-radius: 6px;
	-webkit-border-radius: 6px;
	border-radius: 6px;
	list-style:none !important;
	box-shadow: 2px 2px 2px #aaa;
	-moz-box-shadow: 2px 2px 2px #aaa;
	-webkit-box-shadow: 2px 2px 2px #aaa;
}
.floating-tip li:first-child{
	-moz-border-radius: 6px 6px 0 0;
	-webkit-border-radius: 6px 6px 0 0;
	border-radius: 6px 6px 0 0;
}

.floating-tip li:last-child{
	-moz-border-radius: 0 0 6px 6px;
	-webkit-border-radius: 0 0 6px 6px;
	border-radius: 0 0 6px 6px;
}

.floating-tip li{
	min-height:19px;
	border-top:1px solid #D7D7D7;
	padding:5px 16px;
	background-color:#fff;
}

.floating-tip li:hover{
	background-color:#DBF5FA;
}

.floating-tip li span{
	margin-left:7px;
}
";?>