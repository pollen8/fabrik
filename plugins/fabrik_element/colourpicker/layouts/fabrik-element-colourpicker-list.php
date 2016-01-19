<?php
defined('JPATH_BASE') or die;

$d = $displayData;

foreach ($d->data as $d)
{
	echo  '<div style="width:15px;height:15px;background-color:rgb(' . $d . ')"></div>';
}
