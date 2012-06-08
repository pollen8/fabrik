<?php
header('Content-type: text/css');
$c = (int) $_REQUEST['c'];
$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : 'form';
echo "
#{$view}_$c .fabrikForm .fabrikGroup ul{
	list-style:none;
}

#{$view}_$c .fabrikForm .fabrikGroup > ul{
	padding:0;
	margin:0;
}

#{$view}_$c .fabrikForm .fabrikGroup li{
	background:none !important;
}

#{$view}_$c .fabrikForm fieldset,
#{$view}_$c .fabrikForm fieldset li.fabrikElementContainer{
	padding:0 !important;
	border:0;
}

.fabrikElementContainer{
float:none !important;
width:100% !important;
}

#{$view}_$c .addGroup:link {
	text-decoration: none;
}
";?>
