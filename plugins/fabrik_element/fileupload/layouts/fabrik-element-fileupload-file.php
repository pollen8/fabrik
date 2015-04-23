<?php
defined('JPATH_BASE') or die;

$d = $displayData;

// $$$ hugh - using 'make_thumbnail' to mean 'use default $ext.png as an icon
// instead of just putting the filename.

?>

<?php
if ($d->useThumb) :
	?>
	<a class="download-archive fabrik-filetype-<?php echo $d->ext;?>" title="<?php echo $d->file; ?>" href="<?php echo $d->file; ?>">
		<img src="<?php echo $d->thumb;?>" alt="<?php echo $d->filename; ?>">
	</a>
<?php
else :
	?>
	<a class="download-archive fabrik-filetype-<?php echo $d->ext;?>" title="<?php echo $d->file; ?>" href="<?php echo $d->file; ?>">
		<?php echo $d->filename; ?>
	</a>
<?php
endif;

