<?php
/**
 * Email form layout
 */

defined('JPATH_BASE') or die;

$d = $displayData;

if ($d->popup) :
?>
<a class="printlink" href="javascript:void(0)" onclick="javascript:window.print(); return false"
	title="<?php echo FText::_('COM_FABRIK_PRINT');?>">
<?php else: ?>
<a href="#" class="printlink" onclick="window.open('<?php echo $d->link;?>','win2','<?php echo $d->status?>;');return false;"
	title="<?php echo FText::_('COM_FABRIK_PRINT'); ?>">
<?php endif;?>
<?php echo $displayData->image;?>
</a>