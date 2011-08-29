<?php
header('Content-type: text/css');
$c = (int)$_REQUEST['c'];
echo "
#form_$c .fabrikElement {
	margin-left: 10px;
}



#form_$c .fabrikLabel {
	width: 100px;
	clear: left;
	float: left;
}

#form_$c .fabrikActions {
	padding-top: 15px;
	clear: left;
	padding-bottom: 15px;
}

#form_$c .fabrikGroupRepeater {
	float: left;
	width: 19%;
}

/** used by password element */
#form_$c .fabrikSubLabel {
	margin-left: -10px;
	clear: left;
	margin-top: 10px;
	float: left;
}

.fabrikSubElement {
	display: block;
	margin-top: 10px;
	margin-left: 100px;
}

.example {
	#float: left;
	#width: 33%;
	margin-top: 10px;
	padding: 5px 10px;
}

.example .fabrikElement {
	#margin-right: 20px;
	#margin-left: 0px;
	#margin-bottom: 15px;
}

.example .fabrikLabel {
	#float: none;
	#clear: none;
}
";?>


