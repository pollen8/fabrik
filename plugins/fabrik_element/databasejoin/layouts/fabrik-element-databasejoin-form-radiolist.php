<?php
defined('JPATH_BASE') or die;

$d = $displayData;

if ($d->optsPerRow < 1)
{
	$d->optsPerRow = 1;
}
if ($d->optsPerRow > 12)
{
	$d->optsPerRow = 12;
}

$colSize    = floor(floatval(12) / $d->optsPerRow);
$colCounter = 0;

foreach ($d->options as $option) :
	$d->checked = in_array($option->value, $d->default) ? 'checked="checked"' : '';
	if (($colSize * $colCounter) % 12 === 0  || $colCounter == 0) : ?>
		<div class="row-fluid">
	<?php endif;
	$d->option = $option;
	$d->colCounter = $colCounter;
	if ($d->editable) :
		echo $d->optionLayout->render($d);
	elseif ($d->checked) : ?>
		<span><?php echo $d->option->text;?></span>
	<?php endif;
	$colCounter++;
	if (($colSize * $colCounter) % 12 === 0 || $colCounter == 0) :
		?>
		</div>
	<?php endif; ?>
	<?php
endforeach;
?>
</div>
