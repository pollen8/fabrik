	<?php if ($this->filter_action != 'onchange') {?>
	<div class="submitfilter">
	<a href="#" name="filter" class="fabrik_filter_submit"></a>
			</div>
	<?php }?>

<?php
	echo "<div class=\"searchall\">";
	//echo $this->filters['all']->label;
	echo $this->clearFliterLink . ' |' ;
	if (array_key_exists('all', $this->filters)) {
		echo $this->filters['all']->element;
	}
	echo "</div>";
?>
<?php if ($this->filter_action == '') {?>
	<input type="button" class="fabrik_filter_submit button" value="<?php //echo JText::_('GO');?>"
			name="filter" />
<?php }?>
