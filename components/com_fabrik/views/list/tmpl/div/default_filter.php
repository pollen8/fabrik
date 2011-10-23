<div class="fabrikFilterContainer">
<table class="filtertable fabrikList">
	<thead>
	<tr class="fabrik___heading">
			<th style="text-align:left"><?php echo JText::_('COM_FABRIK_SEARCH');?>:</th>
			<th style="text-align:right"><?php echo $this->clearFliterLink;?></th>
		</tr>
	</thead>

	<?php
	$c = 0;
	foreach ($this->filters as $filter) {
			$required = $filter->required == 1 ? ' notempty' : '';?>
			<tr class="fabrik_row oddRow<?php echo ($c % 2). $required;?>">
			<td><?php echo $filter->label;?></td>
			<td style="text-align:right;"><?php echo $filter->element;?></td>
		</tr>
	<?php $c ++;
	} ?>
	<?php if ($this->filter_action != 'onchange') {?>
	<tr class="fabrik_row oddRow<?php echo $c % 2;?>">
		<td colspan="2" style="text-align:right;">
		<input type="button" class="fabrik_filter_submit button" value="<?php echo JText::_('COM_FABRIK_GO');?>"
			name="filter" />
		</td>
	</tr>
	<?php }?>
</table>
</div>