<?php

foreach ($this->elements as $element) :
	$class = 'fabrikErrorMessage';
	if (trim($element->error) !== '') :
		$element->error = '<i class=" icon-warning"></i> ' . $element->error;
		$element->containerClass .= ' error';
		$class .= ' help-inline';
	endif;

	if ($element->startRow) : ?>
			<div class="row-fluid">
	<?php endif; ?>

	<div class="control-group <?php echo $element->containerClass . $element->span; ?>">
		<?php echo $element->label;?>
		<div class="controls">

			<?php if ($this->tipLocation == 'above') : ?>
				<p class="help-block"><?php echo $element->tipAbove ?></p>
			<?php endif ?>

			<div class="fabrikElement">
				<?php echo $element->element;?>
			</div>

			<div class="<?php echo $class?>">
				<?php echo $element->error ?>
			</div>

			<?php if ($this->tipLocation == 'side') : ?>
				<p class="help-block"><?php echo $element->tipAbove ?></p>
			<?php endif ?>

		</div>

		<?php if ($this->tipLocation == 'below') :?>
			<p class="help-block"><?php echo $element->tipAbove ?></p>
		<?php endif ?>

	</div>

	<?php
	if ($element->endRow) :?>
		</div>
	<?php endif;
endforeach;

