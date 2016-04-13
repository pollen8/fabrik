<?php

defined('JPATH_BASE') or die;

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Text;

$d = $displayData;
$readOnly = $d->timerReadOnly ? 'readonly=\"readonly\"' : '';
$kls = $d->timerReadOnly ? 'readonly' : '';

if ($d->elementError != '') :
	$kls .= ' elementErrorHighlight';
endif;
?>

<?php
if (!$d->timerReadOnly) :
?>
	<div class="input-append">
<?php
endif;
?>
<input type="<?php echo $d->type;?>"
	class="fabrikinput input-small inputbox text <?php echo $kls;?>"
	name="<?php echo $d->name; ?>"
	<?php echo $readOnly;?>
	id="<?php echo $d->id; ?>" size="<?php echo $d->size; ?>"
	value="<?php echo $d->value; ?>" />

	<?php
	if (!$d->timerReadOnly) :
	?>
	<button class="btn" id="<?php echo $d->id; ?>_button">
		<?php echo Html::icon($d->icon); ?>
		 <span><?php echo Text::_('PLG_ELEMENT_TIMER_START'); ?></span>
	</button>
</div>
<?php
endif;
?>
