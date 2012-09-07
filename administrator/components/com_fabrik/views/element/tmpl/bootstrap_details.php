<div class="tab-pane active" id="tab-details">
    <fieldset class="form-horizontal">
    	<input type="hidden" id="name_orig" name="name_orig" value="<?php echo $this->item->name; ?>" />
		<input type="hidden" id="plugin_orig" name="plugin_orig" value="<?php echo $this->item->plugin; ?>" />

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('css'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('css'); ?>
			</div>
		</div>

		<?php
		foreach ($this->form->getFieldset('details') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>

	<fieldset class="form-horizontal">

		<legend><?php echo JText::_('COM_FABRIK_OPTIONS')?></legend>
		<div id="plugin-container">
		<?php echo $this->pluginFields?>
		</div>
	</fieldset>
</div>
