<?php

defined('JPATH_BASE') or die;

$d = $displayData;
?>
<div class="fabrikRating" style="width:101px;position:relative;">
	<?php
	$imgOpts = array('icon-class' => 'starRating', 'style' => $d->css);

	for ($s = 0; $s < $d->avg; $s++)
	{
		$imgOpts['data-rating'] = $s + 1;
		echo FabrikHelperHTML::image("star", 'list', $d->tmpl, $imgOpts);
	}

	for ($s = $d->avg; $s < 5; $s++)
	{
		$imgOpts['data-rating'] = $s + 1;
		echo FabrikHelperHTML::image("star-empty", 'list', $d->tmpl, $imgOpts);
	}

	?>
    <span class="ratingScore badge badge-info"><?php echo $d->avg; ?></span>
    <div class="ratingMessage">
    </div>
</div>
