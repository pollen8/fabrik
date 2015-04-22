<?php

defined('JPATH_BASE') or die;

// Add span with id so that element fxs work.
$d = $displayData;
$view = $d->view;
$id = $d->view === 'list' ? '' : 'id="' . $d->id . '"';
?>

<div <?php echo $id;?> class="gmStaticMap">
	<img src="<?php echo $d->src;?>" alt="static map" />
</div>