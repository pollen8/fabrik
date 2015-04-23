<?php

defined('JPATH_BASE') or die;

$d = $displayData;
?>

<input type="text"
	id="<?php echo $d->id; ?>"
	name="<?php echo $d->name; ?>"
	class="<?php echo $d->class; ?>"
	value="<?php echo $d->value; ?>"
	size="<?php echo $d->size; ?>"
	maxlength="<?php echo $d->maxlength; ?>"
/>