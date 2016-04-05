<?php
defined('JPATH_BASE') or die;

use Fabrik\Helpers\Html;

$d    = $displayData;

echo implode("\n", Html::grid($d->values, $d->labels, $d->default, $d->name,
	'checkbox', false, 1, array('input' => array('fabrik_filter'))));
