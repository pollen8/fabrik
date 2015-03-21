<?php
use \Joomla\Utilities\ArrayHelper;

$d             = $displayData;
$digsig_width  = ArrayHelper::getValue($d, 'digsig_width', 200);
$digsig_height = ArrayHelper::getValue($d, 'digsig_height', 100);
$sig_id        = ArrayHelper::getValue($d, 'sig_id');
?>

<div id="<?php echo $sig_id; ?>" class="oc_sigPad signed">
	<div class="sigWrapper" style="height:150px;" >
		<canvas class="pad" width="<?php echo $digsig_width; ?>"
			height="<?php echo $digsig_height; ?>">
		</canvas>
	</div>
</div>