<?php
header('Content-type: text/css');
$c = (int)$_REQUEST['c'];
$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : 'form';
echo "

#{$view}_$c fieldset ul{
	list-style:none;
}

#{$view}_$c .fabrikElement{
	margin-left:112px;
}

#{$view}_$c .fabrikLabel{
	width:100px;
	clear:left;
	float:left;
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
";
?>