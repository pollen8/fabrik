<fieldset class="adminform">
			<ul class="adminformlist">
				<?php foreach ($this->form->getFieldset('options') as $field) :?>
				<li>
					<?php echo $field->label; ?><?php echo $field->input; ?>
				</li>
				<?php endforeach; ?>
			</ul>

			<ul class="adminformlist">
				<?php foreach ($this->form->getFieldset('cck') as $field) :?>
				<li>
					<?php echo $field->label; ?><?php echo $field->input; ?>
				</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>