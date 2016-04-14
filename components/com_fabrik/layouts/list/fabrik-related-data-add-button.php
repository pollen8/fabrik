<?php
/**
 * List related data add button.
 */

defined('JPATH_BASE') or die;

$d = $displayData;
$trigger = $d->popUp ? 'data-fabrik-view="form"' : '';
?>

<?php if ($d->canAdd) :?>
<span class="addbutton">
	<a <?php echo $trigger; ?> href="<?php echo $d->url ;?>" title="<?php echo $d->label; ?>">
	<?php echo FabrikHelperHTML::icon('icon-plus', $d->label); ?>
	</a>
</span></a>
<?php else :?>
	<div style="text-align:center">
		<a title="<?php echo FText::_('JERROR_ALERTNOAUTHOR'); ?>">
			<img src="<?php echo COM_FABRIK_LIVESITE; ?>media/com_fabrik/images/login.png"
				alt="<?php echo FText::_('JERROR_ALERTNOAUTHOR'); ?>" />
		</a>
	</div>
<?php endif; ?>
