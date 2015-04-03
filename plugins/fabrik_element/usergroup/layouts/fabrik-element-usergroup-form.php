<?php

defined('JPATH_BASE') or die;

$d = $displayData;

if ($d->isEditable) :
	echo $this->input;
else :
	echo $d->selected;
endif;

