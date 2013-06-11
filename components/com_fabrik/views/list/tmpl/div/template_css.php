<?php
header('Content-type: text/css');
$c = $_REQUEST['c'];
$buttonCount = (int) $_REQUEST['buttoncount'];
$buttonTotal = $buttonCount === 0 ? '100%' : 30 * $buttonCount ."px";
echo "

#listform_$c input[type=checkbox] {
display: none;
}
#listform_$c .well {
	position: relative;
}

#listform_$c .fabrik_action {
	position: absolute;
	top: 10px;
	right: 10px;
";?>