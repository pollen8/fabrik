<?php
defined('JPATH_BASE') or die;

$d = $displayData;
?>

<fb:like <?php echo $d->href; ?> layout="<?php echo $d->layout; ?>"
	show_faces="<?php echo $d->showfaces;?>" width="<?php echo $d->width;?>"
	action="<?php echo $d->action; ?>" font="<?php echo $d->font;?>"
	colorscheme="<?php echo $d->colorscheme;?>" />