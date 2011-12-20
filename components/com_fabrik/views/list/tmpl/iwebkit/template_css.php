<?php
header('Content-type: text/css');
$c = (int)$_REQUEST['c'];
echo "

.fabrikList li{
	padding:0;
	background-image:none;
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
";


?>
