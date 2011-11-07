<tr id="<?php echo $this->_row->id;?>" class="<?php echo $this->_row->class;?>">
	<?php foreach ($this->headings as $heading=>$label) {	
		$style = empty($this->cellClass[$heading]['style']) ? '' : 'style="'.$this->cellClass[$heading]['style'].'"';?>
		<td class="<?php echo $this->cellClass[$heading]['class']?>" <?php echo $style?>>
			<?php echo @$this->_row->data->$heading;?>
		</td>
	<?php }?>
</tr>

