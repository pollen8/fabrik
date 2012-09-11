<div class="tab-pane active" id="tab-details">

	    <fieldset class="form-horizontal">
			<?php foreach ($this->form->getFieldset('details') as $this->field) :
				echo $this->loadTemplate('control_group');
			endforeach;
			?>
		</fieldset>

	    <fieldset class="form-horizontal">

			<?php foreach ($this->form->getFieldset('details2') as $this->field) :
				echo $this->loadTemplate('control_group');
			endforeach;
			?>
		</fieldset>

</div>
