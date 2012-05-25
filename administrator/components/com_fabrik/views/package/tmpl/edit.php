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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

$fbConfig = JComponentHelper::getParams('com_fabrik');
$srcs = FabrikHelperHTML::framework();
FabrikHelperHTML::mocha();
$srcs[] = 'media/com_fabrik/js/lib/art.js';
$srcs[] = 'media/com_fabrik/js/icons.js';
$srcs[] = 'media/com_fabrik/js/icongen.js';
$srcs[] = 'media/com_fabrik/js/history.js';
$srcs[] = 'media/com_fabrik/js/keynav.js';
$srcs[] = 'media/com_fabrik/js/tabs.js';
$srcs[] = 'media/com_fabrik/js/pages.js';
$srcs[] = 'media/com_fabrik/js/inline.js';
$srcs[] = 'media/com_fabrik/js/canvas.js';
$srcs[] = 'administrator/components/com_fabrik/views/package/adminpackage.js';


FabrikHelperHTML::script($srcs, $this->js);

JHTML::stylesheet('media/com_fabrik/css/package.css');
?>

<script type="text/javascript">
Joomla.submitbutton = function(task)
{
	if (task == 'package.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
		submitform(task);
	}
	else {
		alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
	}
}

submitform = function(task){
	var json = JSON.encode(PackageCanvas.prepareSave());
	document.id('jform_params_canvas').value = json;
	Joomla.submitform(task, $('adminForm'));
}
</script>
<div id="icons-container"></div>
<form action="<?php JRoute::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="width-100 fltlft">
		<?php foreach ($this->form->getFieldset('json') as $field) :
			echo $field->input;
		endforeach; ?>
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_FABRIK_DETAILS');?></legend>
			<ul class="adminformlist twocols">
				<?php foreach ($this->form->getFieldset('details') as $field): ?>
				<li>
					<?php echo $field->label . $field->input; ?>
				</li>
				<?php endforeach; ?>

				<?php foreach ($this->form->getFieldset('publishing') as $field) :?>
				<li>
					<?php echo $field->label; ?><?php echo $field->input; ?>
				</li>
				<?php endforeach; ?>

			</ul>
			<div class="clr"> </div>

		</fieldset>

	</div>
	<div class="clr"></div>
<!--<a id="undo" href="#">Undo</a> |
<a id="redo" href="#">Redo</a> <br />
-->
<div class="adminform" style="margin:10px;background-color:#999;">
<ul id="packagemenu">

</ul>
<div id="packagepages" style="margin:10px;">

</div>
</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>