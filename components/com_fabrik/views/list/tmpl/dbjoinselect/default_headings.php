<?php if (count($this->groupheadings) > 1) { ?>
	<tr class="fabrik___heading">
	<?php
	$t = 0;
	foreach ($this->groupheadings as $label=>$colspan) {
	$t += $colspan;?>
		<th colspan="<?php echo $colspan;?>">
			<?php echo $label; ?>
		</th>
	<?php }
		$t ++;
		if ($t < count($this->headings)) {?>
			<th colspan="<?php echo count($this->headings) - count($this->groupheadings)?>"></th>
		<?php
		}?>
	</tr>
<?php } ?>
<tr class="fabrik___heading x">
<?php foreach ($this->headings as $key=>$heading) {?>
	<th class="<?php echo $this->headingClass[$key]['class']?>"
	 style="<?php $this->headingClass[$key]['style']?>">
		<?php echo $heading; ?>
	</th>
	<?php }?>
</tr>