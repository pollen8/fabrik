<?php
defined('JPATH_BASE') or die;

$d = $displayData;
?>

<?php
if ($d->href !== '') :
	?>
	<div id="fb-root">
	<fb:comments href="<?php echo $d->href; ?>" nmigrated="1"
		um_posts="<?php echo $d->num;?>" width="<?php echo $d->width;?>" <?php echo $d->colour; ?>></fb:comments>
<?php
else :
	?>
	<?php echo FText::_('PLG_ELEMENT_FBCOMMENT_AVAILABLE_WHEN_SAVED');?>
<?php
endif;
