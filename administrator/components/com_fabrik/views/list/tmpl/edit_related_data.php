<fieldset>
	<legend>
		<?php echo JHTML::_('tooltip', JText::_('COM_FABRIK_RELATED_DATA_DESC', false), JText::_('COM_FABRIK_RELATED_DATA'), 'tooltip.png', JText::_('COM_FABRIK_RELATED_DATA'));?>
	</legend>
	<?php foreach ($this->form->getFieldset('factedlinks') as $field): ?>
		<?php echo $field->input; ?>
	<?php endforeach; ?>
</fieldset>