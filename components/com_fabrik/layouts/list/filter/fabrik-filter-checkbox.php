<?php
defined('JPATH_BASE') or die;

$d    = $displayData;
$inputDataAttribs = array('data-filter-name="' . $d->elementName . '"');

echo implode("\n",
				FabrikHelperHTML::grid($d->values, $d->labels, $d->default, $d->name,
	'checkbox', false, 1, array('input' => array('fabrik_filter')), false, array(), $inputDataAttribs)
		);
