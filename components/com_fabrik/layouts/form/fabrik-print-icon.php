<?php
/**
 * Email form layout
 */

defined('JPATH_BASE') or die;

use Fabrik\Helpers\Html;

$d = $displayData;

?>
<a class="btn btn-default" data-fabrik-print href="<?php echo $d->link;?>">
	<?php echo Html::icon('icon-print', FText::_('COM_FABRIK_PRINT'));?>
</a>