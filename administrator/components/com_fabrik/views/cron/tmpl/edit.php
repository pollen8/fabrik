<?php
/*
 * @package Joomla.Administrator
 * @subpackage Fabrik
 * @since		1.6
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHTML::stylesheet('administrator/components/com_fabrik/views/fabrikadmin.css');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
$fbConfig =& JComponentHelper::getParams('com_fabrik');
FabrikHelperHTML::script('administrator/components/com_fabrik/views/namespace.js', true);

FabrikHelperHTML::script('administrator/components/com_fabrik/views/cron/admincron.js', true);

$opts = new stdClass();
$opts->plugin = $this->item->plugin;

$js = "
head.ready(function() {
	var options = ".json_encode($opts).";
	var controller = new CronAdmin(options);
});\n";

JFactory::getDocument()->addScriptDeclaration($js);

?>

<form action="<?php JRoute::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="width-100 fltlft">
	<?php foreach ($this->form->getFieldsets() as $fieldset) {?>
		<fieldset class="adminform">
			<legend><?php echo $fieldset->label;?></legend>
			<ul class="adminformlist">
			<?php foreach ($this->form->getFieldset($fieldset->name) as $field) : ?>
				<li>
					<?php if (!$field->hidden): ?>
						<?php echo $field->label; ?>
					<?php endif; ?>
					<?php echo $field->input; ?>
				</li>
			<?php endforeach; ?>
			</ul>

		</fieldset>
<?php }?>
<fieldset class="adminform">
			<legend><?php echo JText::_('COM_FABRIK_OPTIONS');?></legend>
			<div id="plugin-container">
				<?php echo $this->pluginFields;?>
			</div>
		</fieldset>

	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
