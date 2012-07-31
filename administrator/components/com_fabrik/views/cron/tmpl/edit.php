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
JHTML::stylesheet('administrator/components/com_fabrik/views/fabrikadmin.css');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
$fbConfig = JComponentHelper::getParams('com_fabrik');
$srcs = FabrikHelperHTML::framework();
$srcs[] = 'administrator/components/com_fabrik/views/namespace.js';
$srcs[] = 'administrator/components/com_fabrik/views/cron/admincron.js';

$opts = new stdClass;
$opts->plugin = $this->item->plugin;

$js = "\tvar options = ".json_encode($opts).";\n";
$js .= "\tvar controller = new CronAdmin(options);";

FabrikHelperHTML::script($srcs, $js);

?>

<form action="<?php JRoute::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

	<div class="row-fluid">
		<div class="span6">
			<fieldset class="form-horizontal">
		    	<legend>
		    		<?php echo JText::_('COM_FABRIK_DETAILS');?>
		    	</legend>
				<?php foreach ($this->form->getFieldset('details') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>

			<fieldset class="form-horizontal">
		    	<legend>
		    		<?php echo JText::_('COM_FABRIK_OPTIONS');?>
		    	</legend>
				<div id="plugin-container">
					<?php echo $this->pluginFields;?>
				</div>
			</fieldset>
		</div>

		<div clas="span5">

			<fieldset class="form-horizontal">
		    	<legend>
		    		<?php echo JText::_('COM_FABRIK_RUN');?>
		    	</legend>
				<?php foreach ($this->form->getFieldset('run') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>

			<fieldset class="form-horizontal">
		    	<legend>
		    		<?php echo JText::_('COM_FABRIK_LOG');?>
		    	</legend>
				<?php foreach ($this->form->getFieldset('log') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
