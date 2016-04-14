<?php
/**
 * Email form layout
 */

defined('JPATH_BASE') or die;

$d = $displayData;

?>
<a class="btn btn-default" data-fabrik-print href="<?php echo $d->link;?>">
	<?php echo FabrikHelperHTML::icon('icon-print', FText::_('COM_FABRIK_PRINT'));?>
</a>