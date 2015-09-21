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
	$checked = in_array($option->value, $d->default) ? 'checked="checked"' : '';
	if (($colSize * $colCounter) % 12 === 0) : ?>
		<div class="row-fluid">
	<?php endif; ?>
	<div class="span<?php echo $colSize; ?>">
		<label class="radio">
			<input type="radio" value="<?php echo $option->value; ?>" name="<?php echo $d->name; ?>" class="fabrikinput" <?php echo $checked; ?> />
			<?php echo $option->text; ?>
		</label>
	</div>

	<?php
	if (($colSize * $colCounter) % 12 === 0 && $colCounter !== 0) :
		?>
		</div>
	<?php endif; ?>
	<?php
	$colCounter++;
endforeach;
?>
</div>
