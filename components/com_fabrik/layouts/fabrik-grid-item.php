<?php
/**
 * Tabs layout
 */

defined('JPATH_BASE') or die;
use Fabrik\Helpers\Worker;

$d = $displayData;

$thisName = $d->type === 'checkbox' ? FabrikString::rtrimword($d->name, '[]') . '[' . $d->i . ']' : $d->name;
$label    = '<span>' . $d->label . '</span>';

$inputClass = Worker::j3() ? '' : $d->type;

if (array_key_exists('input', $d->classes))
{
	$inputClass .= ' ' . implode(' ', $d->classes['input']);
}

$chx = '<input type="' . $d->type . '" class="fabrikinput ' . $inputClass . '" ' . $d->inputDataAttributes .
	' name="' . $thisName . '" value="' . $d->value . '" ';

$sel = in_array($d->value, $d->selected);
$chx .= $sel ? ' checked="checked" />' : ' />';
$labelClass = Worker::j3() && !$d->buttonGroup ? $d->type : '';

if (array_key_exists('label', $d->classes))
{
	$labelClass .= ' ' . implode(' ', $d->classes['label']);
}

$html = $d->elementBeforeLabel == '1' ? $chx . $label : $label . $chx;
?>
<label class="fabrikgrid_<?php echo $d->value . ' ' . $labelClass; ?>">
	<?php echo $html; ?>
</label>
