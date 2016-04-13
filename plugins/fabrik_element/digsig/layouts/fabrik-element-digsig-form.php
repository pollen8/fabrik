<?php
defined('JPATH_BASE') or die;

use Fabrik\Helpers\Text;

$d = $displayData;
?>

<div id="<?php echo $d->id; ?>" class="fabrikSubElementContainer">
	<div class="ccms_form_element cfdiv_custom spad_container_div" id="<?php echo $d->id; ?>_oc_spad">
		<ul class="sigNav">
			<li class="clearButton">
				<a href="#clear"><?php echo Text::_('PLG_ELEMENT_DIGSIG_CLEAR'); ?></a>
			</li>
		</ul>
		<div class="sig sigWrapper">
			<canvas class="pad" id="<?php echo $d->id; ?>_oc_pad" width="<?php echo $d->digsig_width; ?>"
				height="<?php echo $d->digsig_height; ?>"></canvas>
		</div>
	</div>
	<input type="hidden" class="fabrikinput" id="<?php echo $d->sig_id; ?>" name="<?php echo $d->name; ?>"
		value="<?php echo $d->val; ?>" />
</div>