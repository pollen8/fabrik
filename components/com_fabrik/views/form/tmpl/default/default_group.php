<ul>
<?php foreach ($this->elements as $element) {
	?>
	<li <?php echo $element->column;?> class="<?php echo $element->containerClass;?>">
	<div class="displayBox">
		<div class="leftCol">
			<?php echo $element->label;?>
			<?php echo $element->errorTag; ?>
		</div>
		<div class="fabrikElement">
			<?php echo $element->element;?>
		</div>
		</div>
	</li>
	<?php }?>
</ul>