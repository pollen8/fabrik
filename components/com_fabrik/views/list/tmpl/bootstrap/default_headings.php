<tr class="fabrik___heading">
<?php foreach ($this->headings as $key => $heading) :
	$h = $this->headingClass[$key];
	$style = empty($h['style']) ? '' : 'style="' . $h['style'] . '"';?>
	<th class="<?php echo $h['class']?>" <?php echo $style?>>
		<span class="heading">
			<?php echo  $heading; ?>
		</span>
	</th>
<?php endforeach; ?>
</tr>

<?php if ($this->filterMode === 3 || $this->filterMode === 4) :?>
<tr class="fabrikFilterContainer">
	<?php foreach ($this->headings as $key => $heading) :?>
		<th>
		<?php if (array_key_exists($key, $this->filters)) :
			$filter = $this->filters[$key];
			$required = $filter->required == 1 ? ' notempty' : '';
			?>
			<div class="listfilter<?php  echo $required; ?> pull-left">
				<?php echo $filter->element; ?>
			</div>
		<?php endif;?>
		</th>
	<?php endforeach; ?>
</tr>
<?php endif;?>