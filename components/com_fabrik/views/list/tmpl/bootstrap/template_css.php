<?php
header('Content-type: text/css');
$c = $_REQUEST['c'];
$buttonCount = (int) $_REQUEST['buttoncount'];
$buttonTotal = $buttonCount === 0 ? '100%' : 30 * $buttonCount ."px";
echo "

.fabrikDataContainer {
clear:both;padding-top:30px;
}

.fabrikDataContainer .pagination a{
	float: left;
}
";?>