<?php

defined('JPATH_BASE') or die;

use Fabrik\Helpers\Html;

$d = $displayData;
?>

<div id="<?php echo $d->id; ?>_div" class="fabrikSubElementContainer">
	<?php
	$imgOpts = array('icon-class' => 'small', 'style' => $d->css, 'data-rating' => -1);
	$clearImg = Html::image('remove.png', 'list', $d->tmpl, $imgOpts);

	if ($d->ratingNoneFirst && $d->canRate)
	{
		echo $d->clearImg;
	}

	$imgOpts = array('icon-class' => 'starRating', 'style' => $d->css);

	for ($s = 0; $s < $d->avg; $s++)
	{
		$imgOpts['data-rating'] = $s + 1;
		echo Html::image("star.png", 'list', $d->tmpl, $imgOpts);
	}

	for ($s = $d->avg; $s < 5; $s++)
	{
		$imgOpts['data-rating'] = $s + 1;
		echo Html::image("star-empty.png", 'list', $d->tmpl, $imgOpts);
	}

	if (!$d->ratingNoneFirst && $d->canRate)
	{
		echo  $d->clearImg;
	}
	?>
		<span class="ratingScore badge badge-info"><?php echo $d->avg; ?></span>
		<div class="ratingMessage">
		</div>
		<input class="fabrikinput input" type="hidden" name="<?php echo $d->name;?>" id="<?php echo $d->id; ?>" value="<?php echo $d->value; ?>" />
	</div>
