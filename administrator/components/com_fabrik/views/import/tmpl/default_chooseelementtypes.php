<form action="index.php" method="post" name="adminForm">
	<?php if (!empty($this->newHeadings)) {
		if ((int)$this->table->id !== 0) {
		echo "<H3>" . JText::_('COM_FABRIK_IMPORT_NEW_HEADINGS_FOUND') . "</h3>";
		echo JText::sprintf('COM_FABRIK_IMPORT_NEW_HEADINGS_FOUND_DESC', $this->table->label, $this->table->label);
	}?>

		<table class="adminlist">
			<thead>
			<tr>
				<th class="title"><?php echo JText::_('COM_FABRIK_IMPORT_CREATE_ELEMENT');?></th>
				<th class="title"><?php echo JText::_('COM_FABRIK_IMPORT_LABEL');?></th>
				<th class="title"><?php echo JText::_('COM_FABRIK_IMPORT_ELEMENT_TYPE');?></th>
				<?php if ($this->table->db_primary_key == '') {?>
					<th class="title"><?php echo JText::_('COM_FABRIK_PRIMARY_KEY');?></th>
				<?php } ?>
				<th><?php echo JText::_('COM_FABRIK_IMPORT_SAMPLE_DATA');?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			$chx = (int)$this->table->id === 0 ? '' : 'checked="checked"';
			$chx2 = (int)$this->table->id === 0 ? 'checked="checked"' : '';
			for ($i=0; $i < count($this->newHeadings); $i++) {
				$heading = trim($this->newHeadings[$i]);
				foreach ($this->headings as $sKey => $sVal) {
					if(strtolower($heading) == strtolower($sVal)) {
						//$sample = $this->data[0][$sKey];
						//$sample = $this->sample[$sKey];
						$sample = $this->sample[$sKey];
					}
				}
			//	$sample = $this->data[0][$i];
				?>
			<tr>
				<td>
				<label>
					<input type="radio" name="createElements[<?php echo $heading;?>]" value="0" <?php echo $chx?>>
					<?php echo JText::_('JNO');?>
				</label>
				<label>
					<input type="radio" name="createElements[<?php echo $heading;?>]" value="1" <?php echo $chx2?>>
					<?php echo JText::_('JYES');?>
				</label>
			</td>
			<td><?php echo $heading;?></td>
			<td><?php echo $this->elementTypes;?></td>
			<?php if ($this->table->db_primary_key == '') {?>
				<td><input type="checkbox" name="key[<?php echo $heading;?>]" value="1" /></td>
			<?php } ?>
			<td><?php echo $sample;?></td>
		</tr>

<?php }?>
</tbody>
</table>

<?php
			}
			if (!empty($this->matchedHeadings)) {
			?> <?php 	echo "<H3>" . JText::_('EXISTING HEADINGS FOUND') . "</h3>";?>
<table class="adminlist">
	<thead>
	<tr>
		<th class="title"><?php echo JText::_('LABEL');?></th>
		<?php if ($this->table->db_primary_key == '') {?>
			<th class="title"><?php echo JText::_('PRIMARY KEY');?></th>
		<?php } ?>
		<th><?php echo JText::_('SAMPLE DATA');?></th>
	</tr>
	</thead>
	<tbody>
	<?php

			foreach ($this->matchedHeadings as $heading) {

				foreach ($this->headings as $sKey => $sVal) {
					if(strtolower($heading) == strtolower($sVal)) {
						$sample = $this->data[0][$sKey];
					}
				}
	?>
	<tr>
		<td><?php echo $heading;?></td>
		<?php if ($this->table->db_primary_key == '') { ?>
			<td>
			<input type="checkbox" name="key[<?php echo $heading;?>]" value="1" />
			</td>
		<?php } ?>
		<td><?php echo $sample;?></td>
	</tr>
	<?php }?>
	</tbody>
</table>
<?php }
$jform = JRequest::getVar('jform');
?>
	<input type="hidden" name="option" value="com_fabrik" />
	<input type="hidden" name="list_id" value="<?php echo $this->table->id;?>" />
	<input type="hidden" name="task" value="import.makeTableFromCSV" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="jform[drop_data]" value="<?php echo JRequest::getVar('drop_data') ?>" />
	<input type="hidden" name="jform[overwrite]" value="<?php echo JRequest::getVar('overwrite') ?>" />
	<input type="hidden" name="connection_id" value="<?php echo JArrayHelper::getValue($jform, 'connection_id')?>" />
	<input type="hidden" name="label" value="<?php echo JArrayHelper::getValue($jform, 'label')?>" />
	<input type="hidden" name="db_table_name" value="<?php echo JArrayHelper::getValue($jform, 'db_table_name')?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>