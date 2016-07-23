<?php
defined('JPATH_BASE') or die;

$d = $displayData;

?>

<div>
	<canvas id="<?php echo $d->id; ?>-widget" width="<?php echo $d->width ?>" height="<?php echo $d->height; ?>"></canvas>

	<?php if ($d->canCrop) : ?>
		<div class="row-fluid" style="margin-top:20px">
			<div class="zoom span6">
				<?php echo FText::_('PLG_ELEMENT_FILEUPLOAD_ZOOM'); ?>:
				<div class="fabrikslider-line" style="width: 100px;float:left;">
					<div class="knob"></div>
				</div><br />
				<input type="number" name="zoom-val" value="" size="3" class="input-mini">
			</div>
			<div class="rotate span5"><?php echo FText::_('PLG_ELEMENT_FILEUPLOAD_ROTATE'); ?>:
				<div class="fabrikslider-line" style="width: 100px;float:left;">
					<div class="knob"></div>
				</div><br />
				<input type="number" name="rotate-val" value="" size="3" class="input-mini">
			</div>
		</div>
	<?php endif; ?>
	<?php if ($d->canvasSupport) : ?>
		<div >
			<input type="button" class="button btn btn-primary" name="close-crop" value="<?php echo FText::_('PLG_ELEMENT_FILEUPLOAD_CLOSE'); ?>">
		</div>
	<?php endif; ?>
</div>
