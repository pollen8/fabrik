<?php
defined('JPATH_BASE') or die;

$d = $displayData;

echo JHTML::_('select.genericlist', $d->options, $d->name, $d->attributes, 'value', 'text', $d->default, $d->id);
