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
FabrikHelperHTML::script('media/com_fabrik/js/mootools-ext.js');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

$fbConfig = JComponentHelper::getParams('com_fabrik');
FabrikHelperHTML::script('administrator/components/com_fabrik/views/namespace.js');

FabrikHelperHTML::script('administrator/components/com_fabrik/views/visualization/adminvisualization.js');

$opts = new stdClass();
$opts->plugin = $this->item->plugin;

$js = "
head.ready(function() {
	var options = ".json_encode($opts).";
	var controller = new AdminVisualization(options);
});\n";

JFactory::getDocument()->addScriptDeclaration($js);
?>

<form action="<?php JRoute::_('index.php?option=com_fabik'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="width-50 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_FABRIK_DETAILS');?></legend>
			<ul class="adminformlist">
			<?php foreach($this->form->getFieldset('details') as $field): ?>
				<li>
					<?php if (!$field->hidden): ?>
						<?php echo $field->label; ?>
					<?php endif; ?>
					<?php echo $field->input; ?>
				</li>
			<?php endforeach; ?>
			</ul>

		</fieldset>

		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_FABRIK_OPTIONS');?></legend>
			<div id="plugin-container">
				<?php echo $this->pluginFields;?>
			</div>
		</fieldset>
	</div>

<div class="width-50 fltrt">

	<?php echo JHtml::_('sliders.start','list-sliders-'.$this->item->id, array('useCookie'=>1)); 
	//echo JHtml::_('tabs.start','table-tabs-'.$this->item->id, array('useCookie'=>1));?>
	<?php echo JHtml::_('sliders.panel', JText::_('COM_FABRIK_GROUP_LABEL_PUBLISHING_DETAILS'), 'details'); 
	//echo JHtml::_('tabs.panel',JText::_('COM_FABRIK_GROUP_LABEL_PUBLISHING_DETAILS'));?>

		<fieldset class="adminform">
		<ul class="adminformlist">
			<?php foreach($this->form->getFieldset('publishing') as $field): ?>
				<li>
					<?php if (!$field->hidden): ?>
						<?php echo $field->label; ?>
					<?php endif; ?>
					<?php echo $field->input; ?>
				</li>
			<?php endforeach; ?>
			</ul>
		</fieldset>
		
		<?php echo JHtml::_('sliders.panel', JText::_('COM_FABRIK_VISUALIZATION_LABEL_VISUALIZATION_DETAILS'), 'more');  
		//echo JHtml::_('tabs.panel',JText::_('COM_FABRIK_VISUALIZATION_LABEL_VISUALIZATION_DETAILS'));?>
		
				<fieldset class="adminform">
				<ul class="adminformlist">
					<?php foreach($this->form->getFieldset('more') as $field): ?>
						<li>
							<?php if (!$field->hidden): ?>
								<?php echo $field->label; ?>
							<?php endif; ?>
							<?php echo $field->input; ?>
						</li>
					<?php endforeach; ?>
					</ul>
				</fieldset>

	<?php echo JHtml::_('sliders.end');
	//echo JHtml::_('tabs.end','table-tabs-'.$this->item->id, array('useCookie'=>1)); ?>
</div>



	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
