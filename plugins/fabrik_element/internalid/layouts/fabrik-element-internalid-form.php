<?php
defined('JPATH_BASE') or die;

$d = $displayData;

?>
<input type="text" class="<?php echo $d->class;?>"
	name="<?php echo $d->name; ?>" value="<?php echo $d->value; ?>"
	id="<?php echo $d->id; ?>" />