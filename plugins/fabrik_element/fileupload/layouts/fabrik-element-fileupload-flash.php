<?php
defined('JPATH_BASE') or die;

$d = $displayData;

// $$$ hugh - if they've enabled thumbnails, for Flash content we'll take that to mean they don't
// want to play the content inline in the table, and use mediabox (if available) to open it instead.
if ($d->useThumbs)
{
	?>
	<a href="<?php echo $d->file; ?>" rel="lightbox[flash <?php echo $d->width . ' ' . $d->height; ?>]">
		<img src="<?php echo $d->thumb; ?>" alt="Full Size">
	</a>
<?php
}
elseif ($d->inDetailedView)
{
	$file = str_replace("", "/", COM_FABRIK_LIVESITE . $d->file);
	?>
	<object width="<?php echo $d->width;?>" height="<?php echo $d->height;?>">
		<param name="movie" value="<?php echo $file;?>">
		<embed src="<?php echo $file;?>" width="<?php echo $d->width;?>" height="<?php echo $d->height;?>">
		</embed>
	</object>
<?php
}
else
{
	$file = str_replace("", "/", COM_FABRIK_LIVESITE . $d->file);
	?>
	$this->output = "
	<object width="<?php echo $d->width;?>" height="<?php echo $d->height;?>">
		<param name="movie" value="<?php echo $file;?>">
		<embed src="<?php echo $file;?>" width="<?php echo $d->width;?>" height="<?php echo $d->height;?>">
		</embed>
	</object>
<?php

}
?>

