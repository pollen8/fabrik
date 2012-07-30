<?php foreach ($this->elements as $element) :?>
	<div class="row-fluid">
	<div class="span4"><em><?php echo $element->label_raw ?></em></div>
	<div class="span8"><?php echo $element->element;?></div>
	</div>
<?php endforeach; ?>
