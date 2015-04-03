<?php

defined('JPATH_BASE') or die;

$d = $displayData;

// Only deals with the link rendering - video player rendering is in the detail layout
?>

<?php
if ($d->link == 1) : ?>
	<a href="<?php echo $d->value; ?>" target="blank"><?php echo $d->label; ?></a>
	<?php
elseif  ($d->link == 2) :
	?>
	<a href="<?php echo $d->value; ?>" rel="lightbox[social <?php echo $d->width; ?> <?php $d->height; ?>]"
		title="<?php echo $d->title ?>"><?php echo $d->label; ?></a>
	<?php
else :
	?>
	<a href="<?php echo $d->value; ?>"><?php echo $d->label; ?></a>
	<?php
endif;
