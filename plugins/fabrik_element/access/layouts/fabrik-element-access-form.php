<?php
defined('JPATH_BASE') or die;

$d = $displayData;
?>

<select name="<?php echo $d->name; ?>" id="<?php echo $d->id; ?>">
	<?php foreach ($d->options as $opt) :
		$selected = $opt->value === $d->selected ? ' selected="selected" ' : ''?>
		<option value="<?php echo $opt->value;?>" <?php echo $selected;?>>
			<?php echo JText::_($opt->text);?>
		</option>
	<?php endforeach; ?>
</select>
