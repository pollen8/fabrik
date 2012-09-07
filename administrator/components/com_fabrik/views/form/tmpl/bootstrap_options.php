<div class="tab-pane" id="tab-options">

    <fieldset class="form-horizontal">
		<?php foreach ($this->form->getFieldset('options') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>

    <fieldset class="form-horizontal">

		<?php foreach ($this->form->getFieldset('cck') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>
</div>
