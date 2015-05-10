<?php

defined('JPATH_BASE') or die;

// Add span with id so that element fxs work.
$d = $displayData;

?>

<button class="<?php echo $d->class; ?>" id="<?php echo $d->id; ?>" name="<?php echo $d->name; ?>">
	<?php if ($d->icon !== '') : ?>
		<span class="<?php echo $d->icon; ?>"></span>
	<?php endif; ?>
	<?php echo $d->label; ?>
</button>