<?php
$c = 0;
foreach($this->group as $row) {?>
<tr id="<?php echo $row->id;?>" class="<?php echo $row->class;?> row<?php echo $c % 2;?>">
	<?php foreach ($this->headings as $heading=>$label) {
		$style = empty($this->cellClass[$heading]['style']) ? '' : 'style="'.$this->cellClass[$heading]['style'].'"';?>
		<td class="<?php echo $this->cellClass[$heading]['class']?>" <?php echo $style?>>
			<?php echo @$row->data->$heading;?>
		</td>
	<?php }?>
</tr>
<?php
$c++;
}
?>
