<div class="tab-pane" id="tab-publishing">
    <fieldset class="form-horizontal">
		<?php foreach ($this->form->getFieldset('publishing') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>
</div>
