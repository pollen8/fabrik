<?php
/**
 * Import View
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Text;

$url = JRoute::_('index.php');
JHtml::_('behavior.tooltip');
Html::formvalidation();
$action = JRoute::_('index.php?option=com_fabrik');
$app = JFactory::getApplication();
$listId = $app->input->getInt('listid');
?>
<form enctype="multipart/form-data" action="<?php echo $action ?>" method="post" name="adminForm" id="fabrik-form" class="form-validate">
<div class="width-100 fltlft">
	<input type="hidden" name="listid" value="<?php echo $listId; ?>" />
	<h2><?php echo Text::sprintf('COM_FABRIK_CSV_IMPORT_HEADING', $this->listName); ?></h2>
	<?php foreach ($this->fieldsets as $fieldset) :?>
	<fieldset class="adminform">
		<ul>
		<?php foreach ($this->form->getFieldset($fieldset) as $field) :?>
			<li>
				<?php echo $field->label . $field->input; ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
	<?php endforeach;?>

	<input type="hidden" name="task" value="import.doimport" />
  	<?php echo JHTML::_('form.token');
	echo JHTML::_('behavior.keepalive'); ?>
	<input type="submit" value="<?php echo Text::_('COM_FABRIK_IMPORT_CSV')?>" />
	</div>
</form>
