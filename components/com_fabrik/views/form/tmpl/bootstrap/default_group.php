<?php

foreach ($this->elements as $element) :
	$this->element = $element;
	$this->class = 'fabrikErrorMessage';
	if (trim($element->error) !== '') :
		$element->error = '<i class=" icon-warning"></i> ' . $element->error;
		$element->containerClass .= ' error';
		$this->class .= ' help-inline';
	endif;

	if ($element->startRow) : ?>
			<div class="row-fluid">
		<?php
		endif;
		if ($element->span == 'span12') :
			echo $this->loadTemplate('group_labels_side');
		else :

			// Multi columns - best to use simplified layout with labels above field
			echo $this->loadTemplate('group_labels_above');
		endif;
		if ($element->endRow) :?>
		</div><!-- end row-fluid -->
	<?php endif;
endforeach;

// If the last element was not closing the row add an additional div
if (!$element->endRow) :?>
</div><!-- end row-fluid for open row -->
<?php endif;?>

