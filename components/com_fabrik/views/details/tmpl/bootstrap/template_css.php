<?php
header('Content-type: text/css');
$c = (int) $_REQUEST['c'];
$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : 'form';
$rowid = isset($_REQUEST['rowid']) ? $_REQUEST['rowid'] : '';
$form = $view . '_' . $c;

if ($rowid !== '')
{
	$form .= '_' . $rowid;
}

echo <<<EOT
/* missing from some bootstrap templates (like JoomlArt) */

.row-fluid:before,
.row-fluid:after {
	display: table;
	content: "";
	line-height: 0;
}

.row-fluid:after {
	clear: both;
}
EOT;
