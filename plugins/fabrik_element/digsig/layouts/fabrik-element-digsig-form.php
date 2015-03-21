<?php
// Add span with id so that element fxs work.
use \Joomla\Utilities\ArrayHelper;

$d             = $displayData;
$id            = ArrayHelper::getValue($d, 'id');
$digsig_width  = ArrayHelper::getValue($d, 'digsig_width', 200);
$digsig_height = ArrayHelper::getValue($d, 'digsig_height', 100);
$sig_id        = ArrayHelper::getValue($d, 'sig_id');
$name          = ArrayHelper::getValue($d, 'name');
$val           = ArrayHelper::getValue($d, 'val');
?>

<div id="<?php echo $id; ?>" class="fabrikSubElementContainer">
	<div class="ccms_form_element cfdiv_custom spad_container_div" id="<?php echo $id; ?>_oc_spad">
		<ul class="sigNav">
			<li class="drawIt">
				<a href="#draw-it"><?php echo JText::_('PLG_ELEMENT_DIGSIG_DRAW_IT'); ?></a>
			</li>
			<li class="clearButton">
				<a href="#clear"><?php echo JText::_('PLG_ELEMENT_DIGSIG_CLEAR'); ?></a>
			</li>
		</ul>
		<div class="sig sigWrapper">
			<div class="typed"></div>
			<canvas class="pad" id="<?php echo $id; ?>_oc_pad" width="<?php echo $digsig_width; ?>"
				height="<?php echo $digsig_height; ?>"></canvas>
		</div>
	</div>
	<input type="hidden" class="fabrikinput" id="<?php echo $sig_id; ?>" name="<?php echo $name; ?>"
		value="<?php echo $val; ?>" />
</div>