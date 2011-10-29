<tr class="fabrik___heading">
<?php foreach ($this->headings as $key=>$heading) {?>
	<th class="<?php echo $this->headingClass[$key]['class']?>" style="<?php $this->headingClass[$key]['style']?>">
	<?php echo $heading; ?>
		</th>
	<?php }?>
</tr>