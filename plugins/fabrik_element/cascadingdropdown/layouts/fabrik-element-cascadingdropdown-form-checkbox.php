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
$label = isset($d->option) ? $d->option->text : '';
$value = isset($d->option) ? $d->option->value : '';
$checked = isset($d->option) ? $d->option->checked : '';
$name =  isset($d->colCounter) ? $d->name . '[' . $d->colCounter . ']' : $d->name . '[]';
$colSize    = floor(floatval(12) / $d->optsPerRow);
?>
<div class="span<?php echo $colSize; ?>" data-role="suboption">
	<label class="checkbox">
		<input type="checkbox" value="<?php echo $value;?>" data-role="fabrikinput" name="<?php echo $name; ?>" class="fabrikinput" <?php echo $checked;?> />
		<span><?php echo $label;?></span>
	</label>
</div>

