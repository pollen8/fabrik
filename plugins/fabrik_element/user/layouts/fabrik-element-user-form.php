<?php

defined('JPATH_BASE') or die;

$d = $displayData;

/**
 *  If the table database is not the same as the joomla database then
 *  we should simply return a hidden field with the user id in it.
 */
if (!$d->inJDb) :
	?>
	<input type="hidden" name="<?php echo $d->name; ?>" value="<?php echo $d->value;?>" id="<?php echo $d->id;?>" />
<?php
else :
	if ($d->isEditable) :
		if ($d->hidden) :
			?>
			<input type="hidden" name="<?php echo $d->name; ?>" value="<?php echo $d->value;?>" id="<?php echo $d->id;?>" />
		<?php
		else :
			?>
			<div class="input-append">
				<?php echo $d->input;?>
				<span class="add-on"><span class="icon-user"></span></span>
			</div>
		<?php
		endif;
	else :
		echo $d->readOnly;
	endif;
endif;
