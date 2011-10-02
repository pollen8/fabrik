<fieldset class="adminform">
			<ul class="adminformlist">
				<?php foreach ($this->form->getFieldset('templates') as $field) :?>
				<li>
					<?php echo $field->label; ?><?php echo $field->input; ?>
				</li>
				<?php endforeach; ?>
				<?php foreach ($this->form->getFieldset('templates2') as $field) :?>
				<li>
					<?php echo $field->label; ?><?php echo $field->input; ?>
				</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>
		
<fieldset class="adminform">
	<ul class="adminformlist">
		<?php foreach ($this->form->getFieldSet('admintemplates') as $field) :?>
		<li>
			<?php echo $field->label; ?><?php echo $field->input; ?>
		</li>
		<?php endforeach?>	
	</ul>
</fieldset>