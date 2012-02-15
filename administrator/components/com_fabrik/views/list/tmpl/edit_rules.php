<?php echo JHtml::_('tabs.panel',JText::_('COM_FABRIK_GROUP_LABAEL_RULES_DETAILS'), 'list-rules-panel');?>
<fieldset class="adminform">
	<ul class="adminformlist">
	<?php
		foreach ($this->form->getFieldset('access') as $field) :?>
		<li>
			<?php echo $field->label; ?><?php echo $field->input; ?>
		</li>
		<?php endforeach;
		?>
		<?php
		foreach ($this->form->getFieldset('access2') as $field) :?>
		<li>
			<?php echo $field->label; ?><?php echo $field->input; ?>
		</li>
		<?php endforeach;
		?>
	</ul>
</fieldset>