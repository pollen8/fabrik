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
