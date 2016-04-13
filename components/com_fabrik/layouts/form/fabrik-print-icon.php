<?php
/**
 * Email form layout
 */

defined('JPATH_BASE') or die;

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Text;

$d = $displayData;

?>
<a class="btn btn-default" data-fabrik-print href="<?php echo $d->link;?>">
	<?php echo Html::icon('icon-print', Text::_('COM_FABRIK_PRINT'));?>
</a>