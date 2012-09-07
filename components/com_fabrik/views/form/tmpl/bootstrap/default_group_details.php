<?php foreach ($this->elements as $element) :
	 if ($element->hidden) {
		//echo "<pre>";print_r($element);echo "</pre>";
		?>

		<?php
	} else {
		if ($element->startRow) :?>
				<div clas="row-fluid">
			<?php
			endif;
			?>

	<div class="span<?php echo $element->span;?>">
		<div class="row-fluid">
			<div class="span4"><em><?php echo $element->label_raw ?></em></div>
			<div class="span8"><?php echo $element->element;?></div>
		</div>
	</div>

	<?php
	if ($element->endRow) :
	?>
		</div>
	<?php
	endif;
	}?>
<?php endforeach; ?>
