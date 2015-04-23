<?php
defined('JPATH_BASE') or die;
$d = $displayData;
?>

<div id="<?php echo $d->sig_id; ?>" class="oc_sigPad signed">
	<div class="sigWrapper" style="height:150px;">
		<canvas class="pad" width="<?php echo $d->digsig_width; ?>"
			height="<?php echo $d->digsig_height; ?>">
		</canvas>
	</div>
</div>