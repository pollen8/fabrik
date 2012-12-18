<div class="fabrikFilterContainer">
<?php
if ($this->filterMode === 3 || $this->filterMode === 4) :
?>
<?php
else:
?>
<div class="row-fluid">
	<div class="span6">
<table class="filtertable table table-striped">
	<tfoot>
		<tr>
		<td colspan="2"></td>
		</tr>
	</tfoot>
	<thead>
		<tr class="fabrik___heading">
			<th><?php echo JText::_('COM_FABRIK_SEARCH');?>:</th>
			<th style="text-align:right">
			<?php if ($this->showClearFilters) :?>
				<a class="clearFilters" href="#">
					<i class="icon-refresh"></i>
					<?php echo JText::_('COM_FABRIK_CLEAR')?>
					</a>
			<?php endif ?>
			</th>
		</tr>
	</thead>
	<?php
	$c = 0;
	foreach ($this->filters as $key => $filter) :
			if ($key !== 'all') :
				$required = $filter->required == 1 ? ' notempty' : '';?>
				<tr class="fabrik_row oddRow<?php echo ($c % 2) . $required;?>">
				<td><?php echo $filter->label;?></td>
				<td style="text-align:right;"><?php echo $filter->element;?></td>
			</tr>

	<?php
	endif;
	$c ++;
	endforeach;
	?>
</table>
<?php
endif;
?>
</div>
</div>
</div>