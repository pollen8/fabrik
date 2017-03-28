<?php
defined('JPATH_BASE') or die;

$d = $displayData;
?>

<div class="fabrikSubElementContainer" id="<?php echo $d->id;?>">

	<select name="<?php echo $d->day_name; ?>" id="<?php echo $d->day_id; ?>" <?php echo $d->attribs; ?>>
		<?php foreach ($d->day_options as $opt) :
			$selected = (int) $opt->value === (int) $d->day_value ? ' selected="selected" ' : ''?>
			<option value="<?php echo $opt->value;?>" <?php echo $selected;?>><?php echo JText::_($opt->text);?></option>
		<?php endforeach; ?>
	</select>
	<?php echo $d->separator;?>

	<select name="<?php echo $d->month_name?>" id="<?php echo $d->month_id;?>" <?php echo $d->attribs; ?>>
		<?php foreach ($d->month_options as $opt) :
			$selected = (int) $opt->value === (int) $d->month_value ? ' selected="selected" ' : ''?>
			<option value="<?php echo $opt->value;?>" <?php echo $selected;?>><?php echo JText::_($opt->text);?></option>
		<?php endforeach; ?>
	</select>
	<?php echo $d->separator;?>

	<select name="<?php echo $d->year_name;?>" id="<?php echo $d->year_id;?>" <?php echo $d->attribs;?>>
		<?php foreach ($d->year_options as $opt) :
			$selected = (int) $opt->value === (int) $d->year_value ? ' selected="selected" ' : ''?>
			<option value="<?php echo $opt->value;?>" <?php echo $selected;?>><?php echo JText::_($opt->text);?></option>
		<?php endforeach; ?>
	</select>

</div>
