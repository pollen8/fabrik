<?php

defined('JPATH_BASE') or die;

// Add span with id so that element fxs work.
$d = $displayData;

?>
<?php echo $d->editor;?>

<?php if ($d->showCharsLeft) : ?>
	<?php echo $this->sublayout('charsleft', $d);?>
<?php endif; ?>
