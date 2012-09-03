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

JHtml::script('media/com_fabrik/js/mootools-ext.js');
$fbConfig = JComponentHelper::getParams('com_fabrik');

$document = JFactory::getDocument();
$srcs = FabrikHelperHTML::framework();
$srcs[] = 'administrator/components/com_fabrik/views/namespace.js';
$srcs[] = 'administrator/components/com_fabrik/views/pluginmanager.js';
$srcs[] = 'administrator/components/com_fabrik/views/list/tmpl/adminlist.js';

FabrikHelperHTML::script($srcs, $this->js);
?>
<script type="text/javascript">

	Joomla.submitbutton = function(task) {
		if (task !== 'list.cancel'  && !controller.canSaveForm()) {
			alert('Please wait - still loading');
			return false;
		}
		if (task == 'list.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {

			Joomla.submitform(task, document.getElementById('adminForm'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php JRoute::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="row-fluid">

		<div class="span2" id="sidebar">

				<ul class="nav nav-list">
					<li class="active">
				    	<a data-toggle="tab" href="#details">
				    		<?php echo JText::_('COM_FABRIK_DETAILS')?>
				    	</a>
				    </li>
				    <li>
				    	<a data-toggle="tab" href="#data">
				    		<?php echo JText::_('COM_FABRIK_DATA')?>
				    	</a>
				    </li>
				    <li>
				    	<a data-toggle="tab" href="#publishing">
				    		<?php echo JText::_('COM_FABRIK_GROUP_LABEL_PUBLISHING_DETAILS')?>
				    	</a>
				    </li>
				    <li>
				    	<a data-toggle="tab" href="#tabplugins">
				    		<?php echo JText::_('COM_FABRIK_GROUP_LABAEL_PLUGINS_DETAILS')?>
				    	</a>
				    </li>
				    <li>
				    	<a data-toggle="tab" href="#access">
				    		<?php echo JText::_('COM_FABRIK_GROUP_LABAEL_RULES_DETAILS')?>
				    	</a>
				    </li>
				</ul>

		</div>


	    <div class="tab-content span10">
	    	<?php
	    	echo $this->loadTemplate('bootstrap_details');
	    	echo $this->loadTemplate('bootstrap_data');
	    	echo $this->loadTemplate('bootstrap_publishing');
	    	echo $this->loadTemplate('bootstrap_plugins');
	    	echo $this->loadTemplate('bootstrap_access');
	    	?>
	    </div>


		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
