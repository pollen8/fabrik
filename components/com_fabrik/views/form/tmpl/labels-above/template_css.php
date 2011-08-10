<?php
header('Content-type: text/css');
$c = (int)$_REQUEST['c'];
echo "

#form_$c fieldset ul{
	list-style:none;
}

#form_$c fieldset>ul li{
	margin-bottom:10px;
}

#form_$c .fabrikLabel{
	padding-right:15px;
	display:inline;
}

#form_$c .fabrikGroupRepeater{
	float:left;
	width:19%;
}

#form_$c .fabrikSubGroup{
	clear:both;
}

#form_$c .fabrikSubGroupElements{
	width:80%;
	float:left;
}

#form_$c .fabrikElement{
	margin-right:0;
}";?>