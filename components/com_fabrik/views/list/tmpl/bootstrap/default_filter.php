<div class="fabrikFilterContainer">
<?php if ($this->filterMode === 3 || $this->filterMode === 4) {?>


<?php
} else {?>

<table class="filtertable fabrikList">
	<thead>
	<tr class="fabrik___heading">
			<th style="text-align:left"><?php echo JText::_('COM_FABRIK_SEARCH');?>:</th>
			<th style="text-align:right"></th>
		</tr>
	</thead>
	<?php
	$c = 0;
	foreach ($this->filters as $filter) {
			$required = $filter->required == 1 ? ' notempty' : '';?>
			<tr class="fabrik_row oddRow<?php echo ($c % 2) . $required;?>">
			<td><?php echo $filter->label;?></td>
			<td style="text-align:right;"><?php echo $filter->element;?></td>
		</tr>
	<?php $c ++;
	} ?>
</table>

<?php }?>
</div>