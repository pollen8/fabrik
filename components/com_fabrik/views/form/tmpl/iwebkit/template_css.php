<?php
header('Content-type: text/css');
$c = (int)$_REQUEST['c'];
echo "
#form_$c .fabrikForm .fabrikGroup ul{
	list-style:none;
}

#form_$c .fabrikForm .fabrikGroup > ul{
	padding:0;
	margin:0;
}

#form_$c .fabrikForm .fabrikGroup li{
	background:none !important;
}

#form_$c .fabrikForm fieldset,
#form_$c .fabrikForm fieldset li.fabrikElementContainer{
	padding:0 !important;
	border:0;
}

.fabrikElementContainer{
float:none !important;
width:100% !important;
}
";?>
