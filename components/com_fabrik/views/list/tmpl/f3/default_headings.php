<div class="fabrik___headings">
<ul class="fabrik___heading list">
	<li class="heading">
		<?php foreach ($this->headings as $key=>$heading) {?>
		<span class="<?php echo $this->headingClass[$key]['class']?> fabrik_element"
		style="<?php echo $this->headingClass[$key]['style']?>">
			<?php echo $heading; ?>
		</span>
		<?php }?>
	</li>
</ul>

<?php if ($this->showFilters) {?>
<ul class="fabrik___heading list filters">
	<li class="heading">
	<?php
	$this->found_filters = array();
	foreach ($this->headings as $key=>$heading) {?>
		<span class="<?php echo $this->headingClass[$key]['class']?> fabrik_element">
		<?php $filter = JArrayHelper::getValue($this->filters, $key, null);
		if(!is_null($filter)) {
			$this->found_filters[] = $key;
			echo $filter->element;
		} ?></span>
		<?php }?>
	</li>
</ul>
<?php } ?>
</div>
