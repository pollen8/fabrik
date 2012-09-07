<div class="tab-pane" id="tab-groups">
    <fieldset class="form-horizontal">
		<?php foreach ($this->form->getFieldset('groups') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>
</div>
