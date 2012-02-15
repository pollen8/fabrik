<tr class="fabrik___heading">
<?php foreach ($this->headings as $key => $heading) {?>
	<th class="<?php echo $this->headingClass[$key]['class']?>" style="<?php echo $this->headingClass[$key]['style']?>">
		<span class="heading"><?php echo  $heading; ?></span>
		<?php if (array_key_exists($key, $this->filters) && ($this->filterMode === 3 || $this->filterMode === 4)) {
			$filter = $this->filters[$key];
			$required = $filter->required == 1 ? ' notempty' : '';
			echo '<div class="listfilter '.$required.'">
			<span>'.$filter->element.'</span></div>';
		}?>
	</th>
	<?php }?>
</tr>