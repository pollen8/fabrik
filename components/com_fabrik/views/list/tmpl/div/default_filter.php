<div class="fabrikFilterContainer">
<!--- no 'under headings' filter in div template, always 'above'-->
<ul class="filtertable fabrikList">
	
	<li class="fabrik___heading">
			<span style="text-align:left"><?php echo JText::_('COM_FABRIK_SEARCH');?>:</span>
			
	</li>

	<?php
	$c = 0;
	foreach ($this->filters as $filter) {
		$required = $filter->required == 1 ? ' notempty' : '';?>
		<li class="fabrik_row oddRow<?php echo ($c % 2) . $required;?>">
			<span><?php echo $filter->label;?></span>
			<span style="text-align:right;"><?php echo $filter->element;?></span>
		</li>
	<?php $c ++;
	} ?>
	<?php if ($this->filter_action != 'onchange') {?>
	<li class="fabrik_row oddRow<?php echo $c % 2;?>">
		<span style="text-align:right;">
		<input type="button" class="fabrik_filter_submit button" value="<?php echo JText::_('COM_FABRIK_GO');?>"
			name="filter" />
		</span>
	</li>
	<?php }?>
</ul>
</div>