<?php
defined('JPATH_BASE') or die;

use Joomla\Utilities\ArrayHelper;

$d    = $displayData;
$from = $d->from;
$to   = $d->to;

$calOpts = ArrayHelper::toString($d->calOpts);

if ($d->j3) :
	$from->img = '<button id ="' . $from->id . '_cal_img" class="btn calendarbutton">' . $from->img . '</button>';
	$to->img   = '<button id ="' . $to->id . '_cal_img" class="btn calendarbutton">' . $to->img . '</button>';
endif;

$prepend = $d->j3 ? '<div class="input-append">' : '';
$append  = $d->j3 ? '</div>' : '';

if ($d->filterType === 'range-hidden') :
	?>
	<input type="hidden" name="<?php echo $from->name; ?>"
		class="<?php echo $d->class; ?>"
		value="<?php echo $from->value; ?>"
		id="<?php echo $d->htmlId; ?>-0" />

	<input type="hidden" name="<?php echo $to->name; ?>"
		class="<?php echo $d->class; ?>"
		value="<?php echo $to->value; ?>"
		id="<?php echo $d->htmlId; ?>-1" />
<?php
else :
	?>

	<?php echo FText::_('COM_FABRIK_DATE_RANGE_BETWEEN') . ' '; ?>
	<?php echo $prepend; ?>
	<input type="text" name="<?php echo $from->name; ?>" id="<?php echo $from->id; ?>"
		value="<?php echo $from->value; ?>"<?php echo $calOpts; ?> />
	<?php echo $from->img; ?>
	<?php echo $append; ?>
	<br />

	<?php echo FText::_('COM_FABRIK_DATE_RANGE_AND') . ' '; ?>
	<?php echo $prepend; ?>
	<input type="text" name="<?php echo $to->name; ?>" id="<?php echo $to->id; ?>"
		value="<?php echo $to->value; ?>"<?php echo $calOpts; ?> />
	<?php echo $to->img; ?>
	<?php echo $append; ?>

<?php
endif;