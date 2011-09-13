<?php if (count($this->groupheadings ) > 1) { ?>


<?php }

$del = $this->headings['fabrik_select'];
unset($this->headings['fabrik_select']);
$this->headings = array('fabrik_select' => $del) + $this->headings;
?>
<div class="fabrik___headings">
<ul class="fabrik___heading list">
	<li class="heading">
		<?php foreach ($this->headings as $key=>$heading) {?>
		<span class="<?php echo $this->headingClass[$key]['class']?> fabrik_element"
		style="<?php $this->headingClass[$key]['style']?>">
			<?php echo $heading; ?>
		</span>
		<?php }?>
	</li>
</ul>

<?php if ($this->showFilters) {?>
<ul class="fabrik___heading list filters fabrikFilterContainer">
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
