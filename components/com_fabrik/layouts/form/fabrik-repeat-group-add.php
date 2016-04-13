<?php
/**
 * Repeat group add button
 */

defined('JPATH_BASE') or die;

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Text;

$d = $displayData;
?>
<a class="addGroup btn btn-small btn-success" href="#">
	<?php echo  Html::icon('icon-plus fabrikTip tip-small', '', 'data-role="fabrik_duplicate_group" opts="{trigger: \'hover\'}" title="' . Text::_('COM_FABRIK_ADD_GROUP'). '"');?>
</a>