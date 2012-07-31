<?php
/*
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since		1.6
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

$fbConfig = JComponentHelper::getParams('com_fabrik');
$srcs = FabrikHelperHTML::framework();
FabrikHelperHTML::script($srcs);
?>

<form action="<?php JRoute::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="row-fluid">
		<div class="span5">
			<fieldset class="form-horizontal">
		    	<legend>
		    		<?php echo JText::_('COM_FABRIK_DETAILS');?>
		    	</legend>
				<?php foreach ($this->form->getFieldset('details') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>

		<div class="span7">

			<fieldset class="form-horizontal">
		    	<legend>
		    		<?php echo JText::_('COM_FABRIK_REPEAT');?>
		    	</legend>
				<?php foreach ($this->form->getFieldset('repeat') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>

			<fieldset class="form-horizontal">
		    	<legend>
		    		<?php echo JText::_('COM_FABRIK_LAYOUT');?>
		    	</legend>
				<?php foreach ($this->form->getFieldset('layout') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>

			<fieldset class="form-horizontal">
		    	<legend>
		    		<?php echo JText::_('COM_FABRIK_GROUP_MULTIPAGE');?>
		    	</legend>
				<?php foreach ($this->form->getFieldset('pagination') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>

		</div>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
