<?php

defined('JPATH_BASE') or die;

$d = $displayData;

echo JHtml::_('access.level', $d->name, $d->selected, 'class="inputbox" size="6"', $d->options, $d->id);
