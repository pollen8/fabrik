<?php
defined('JPATH_BASE') or die;

$d = $displayData;
echo $d->graphApi;
?>
<fb:recommendations site="<?php echo $d->domain; ?>"
	width="<?php echo $d->width; ?>" height="<?php echo $d->height; ?>"
	header="<?php echo $d->header; ?>"
	colorscheme="<?php echo $d->colorscheme; ?>" font="<?php echo $d->font; ?>"
	border_color="<?php echo $d->border; ?>" />