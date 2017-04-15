<?php
defined('JPATH_BASE') or die;

$d = $displayData;

?>

<a
		class="fabrikTip"
		<?php echo $d->target; ?>
		href="<?php echo $d->href; ?>"
		opts='<?php echo $d->opts; ?>'
		title="<?php echo $d->title; ?>"
		data-trigger="hover"
>
	<?php echo $d->img; ?>
</a>