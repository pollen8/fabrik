<?php
/**
 * Admin Element Edit - Access Tmpl
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       3.0
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="tab-pane" id="tab-access">
    <fieldset class="form-horizontal">
    	<legend>
    		<?php echo JText::_('COM_FABRIK_GROUP_LABAEL_RULES_DETAILS'); ?>
    	</legend>
		<?php
		foreach ($this->form->getFieldset('access') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		foreach ($this->form->getFieldset('access2') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>
</div>
