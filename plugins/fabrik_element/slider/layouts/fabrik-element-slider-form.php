<?php

defined('JPATH_BASE') or die;

use Fabrik\Helpers\Html;

$d = $displayData;
?>
<div id="<?php echo $d->id; ?>" class="fabrikSubElementContainer">
<?php
	if ($d->showNone) :
		if ($d->j3) :?>
		<button class="btn btn-mini clearslider pull-left" style="margin-right:10px"><?php echo Html::icon('icon-remove'); ?></button>
		<?php
		else:
			?>
		<div class="clearslider_cont">
			<img src="<?php echo $d->outSrc; ?>" style="cursor:pointer;padding:3px;"
				alt="<?php echo FText::_('PLG_ELEMENT_SLIDER_CLEAR'); ?>" class="clearslider" />
		</div>
		<?php
		endif;
	endif;
?>

	<div class="slider_cont" style="width:<?php echo $d->width; ?>px;">
		<div class="fabrikslider-line" style="width:<?php echo $d->width; ?>px">
			<div class="knob"></div>
			</div>
		<?php
		if (count($d->labels) > 0 && $d->labels[0] !== '') : ?>
		<ul class="slider-labels" style="width:<?php echo $d->width; ?>px;">
			<?php
			for ($i = 0; $i < count($d->labels); $i++) :
				?>
				<li style="width:<?php echo $d->spanWidth;?>px;text-align:<?php echo $d->align[$i]; ?>"><?php echo $d->labels[$i]; ?></li>
			<?php
			endfor;
			?>
			</ul>
		<?php
		endif;
		?>
		<input type="hidden" class="fabrikinput" name="<?php echo $d->name; ?>" value="<?php echo $d->value; ?>" />
		</div>
		<span class="slider_output badge badge-info"><?php echo $d->value;?></span>
	</div>