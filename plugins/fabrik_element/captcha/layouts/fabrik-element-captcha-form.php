<?php
defined('JPATH_BASE') or die;

$d = $displayData;
?>

<img src="<?php echo $d->url;?>" alt="<?php echo FText::_('security image'); ?>" />

<div class="captcha_input">
	<input class="inputbox <?php echo $d->type;?>"
		type="<?php echo $d->type;?>"
		name="<?php echo $d->name?>"
		id="<?php echo $d->id;?>"  size="<?php echo $d->size; ?>" value="" />
</div>