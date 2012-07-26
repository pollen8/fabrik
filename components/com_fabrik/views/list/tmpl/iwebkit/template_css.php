<?php
header('Content-type: text/css');
$c = $_REQUEST['c'];
echo "

.fabrikList li{
	padding:0;
	background-image:none;
}

#listform_$c li.decimal,
#listform_$c li.integer{
	text-align:right;
}

#listform_$c li {
	position: relative;
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
