<?php
/**
 * Admin Bootstrap Form Edit: Buttons Tmpl
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       3.0
 */
?>
<div class="tab-pane" id="tab-buttons">

	<fieldset class="form-horizontal">
		<legend><?php echo JText::_('COM_FABRIK_BUTTONS');?></legend>
		<?php foreach ($this->form->getFieldset('buttons') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>

</div>
