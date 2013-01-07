<?php
header('Content-type: text/css');
$c = $_REQUEST['c'];
$buttonCount = (int) $_REQUEST['buttoncount'];
$buttonTotal = $buttonCount === 0 ? '100%' : 30 * $buttonCount ."px";
echo "

.fabrikDataContainer {
clear:both;
}

.fabrikDataContainer .pagination a{
	float: left;
}
";?>