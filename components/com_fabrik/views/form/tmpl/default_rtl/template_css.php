<?php
header('Content-type: text/css');
$c = (int)$_REQUEST['c'];
echo "

#form_$c fieldset ul{
	list-style:none;
}

#form_$c .fabrikElement{
	margin-left:10px;
	text-align:right;
	/*float:left;*/
}

#form_$c .fabrikSubElementContainer{
	margin-left:100px;
}

#form_$c .fabrikLabel{
	width:100px;
	clear:right;
	float:right;
}

#form_$c .fabrikActions{
	padding-top:15px;
	clear:left;
	padding-bottom:15px;
}

#form_$c .fabrikGroupRepeater{
	float:left;
	width:19%;
}

#form_$c .fabrik_subelement{
	float:right !important;
}";?>