<div class="tab-pane active" id="tab-details">

    <fieldset class="form-horizontal">
    	<legend><?php echo JText::_('COM_FABRIK_DETAILS')?></legend>
    	<input type="hidden" id="name_orig" name="name_orig" value="<?php echo $this->item->name; ?>" />
		<input type="hidden" id="plugin_orig" name="plugin_orig" value="<?php echo $this->item->plugin; ?>" />

		<div class="span6">
		<?php
		foreach ($this->form->getFieldset('details') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
		</div>
		<div class="span6">
		<?php
		foreach ($this->form->getFieldset('details2') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
		</div>



	</fieldset>

	<fieldset class="form-horizontal">
		<div id="plugin-container">
		<?php echo $this->pluginFields?>
		</div>
	</fieldset>
</div>
