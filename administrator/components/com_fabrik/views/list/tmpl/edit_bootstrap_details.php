<div class="tab-pane active" id="details">
    <fieldset class="form-horizontal">
		<?php foreach ($this->form->getFieldset('details2') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>


    <fieldset class="form-horizontal">
    	<legend>
    		<?php echo JText::_('COM_FABRIK_FILTERS');?>
    	</legend>
		<?php
		foreach ($this->form->getFieldset('main_filter') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		foreach ($this->form->getFieldset('filters') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>

	 <fieldset class="form-horizontal">
    	<legend>
    		<?php echo JText::_('COM_FABRIK_NAVIGATION');?>
    	</legend>
		<?php
		foreach ($this->form->getFieldset('main_nav') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		foreach ($this->form->getFieldset('navigation') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>

	 <fieldset class="form-horizontal">
    	<legend>
    		<?php echo JText::_('COM_FABRIK_LAYOUT');?>
    	</legend>
		<?php
		foreach ($this->form->getFieldset('main_template') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		foreach ($this->form->getFieldset('layout') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;

		?>
	</fieldset>

	<fieldset class="form-horizontal">
    	<legend>
    		<?php echo JText::_('COM_FABRIK_LINKS');?>
    	</legend>
		<?php foreach ($this->form->getFieldset('links') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>

	<fieldset class="form-horizontal">
    	<legend>
    		<?php echo JText::_('COM_FABRIK_NOTES');?>
    	</legend>
		<?php foreach ($this->form->getFieldset('notes') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>

	<fieldset class="form-horizontal">
    	<legend>
    		<?php echo JText::_('COM_FABRIK_ADVANCED');?>
    	</legend>
		<?php foreach ($this->form->getFieldset('advanced') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>
</div>