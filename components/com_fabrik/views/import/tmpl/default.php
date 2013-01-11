<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
$url = JRoute::_('index.php');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$action = JRoute::_('index.php?option=com_fabrik');
$app = JFactory::getApplication();
$listId = $app->input->getInt('listid');
?>
<form enctype="multipart/form-data" action="<?php echo $action ?>" method="post" name="adminForm" id="fabrik-form" class="form-validate">
<div class="width-100 fltlft">
	<input type="hidden" name="listid" value="<?php echo $listId; ?>" />
	<h2><?php echo JText::sprintf('COM_FABRIK_CSV_IMPORT_HEADING', $this->listName); ?></h2>
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
	<input type="submit" value="<?php echo JText::_('COM_FABRIK_IMPORT_CSV')?>" />
	</div>
</form>
