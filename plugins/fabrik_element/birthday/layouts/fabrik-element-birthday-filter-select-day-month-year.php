<?php
defined('JPATH_BASE') or die;

$d = $displayData;

// Order of the selects is important - do not change
?>

<select name="<?php echo $d->name . '[year]';?>" class="input-small fabrikinput">
	<?php foreach ($d->years as $item) :
		$selected = $d->default[0] == $item->value ? 'selected' : ''?>
		<option value="<?php echo $item->value;?>" <?php echo $selected;?>>
			<?php echo $item->text;?>
		</option>
	<?php endforeach; ?>
</select>

<select name="<?php echo $d->name . '[month]';?>" class="input-small fabrikinput">
	<?php foreach ($d->months as $item) :
		$selected = $d->default[1] == $item->value ? 'selected' : ''?>
		<option value="<?php echo $item->value;?>" <?php echo $selected;?>>
			<?php echo $item->text;?>
		</option>
	<?php endforeach; ?>
</select>

<select name="<?php echo $d->name . '[day]';?>" class="input-small fabrikinput">
	<?php foreach ($d->days as $item) :
		$selected = $d->default[2] == $item->value ? 'selected' : ''?>
		<option value="<?php echo $item->value;?>" <?php echo $selected;?>>
			<?php echo $item->text;?>
		</option>
	<?php endforeach; ?>
</select>
