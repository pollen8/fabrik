<?php $filter = JFilterInput::getInstance(array('p'), array(), 1);?>
<div class="fabrik___headings">
<ul class="fabrik___heading list">
	<li class="heading">
		<?php foreach ($this->headings as $key=>$heading) {?>
		<div class="<?php echo $this->headingClass[$key]['class']?> fabrik_element"
		style="<?php echo $this->headingClass[$key]['style']?>">
			<?php echo $filter->clean($heading, 'HTML'); ?>
		</div>
		<?php }?>
	</li>
</ul>

<?php if ($this->showFilters) {?>
<ul class="fabrik___heading list filters">
	<li class="heading">
	<?php
	$this->found_filters = array();
	foreach ($this->headings as $key=>$heading) {?>
		<div class="<?php echo $this->headingClass[$key]['class']?> fabrik_element">
		<?php $filter = JArrayHelper::getValue($this->filters, $key, null);
		if(!is_null($filter)) {
			$this->found_filters[] = $key;
			echo $filter->element;
		} ?></div>
		<?php }?>
	</li>
</ul>
<?php } ?>
</div>
