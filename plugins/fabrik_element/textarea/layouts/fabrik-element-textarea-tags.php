<?php

defined('JPATH_BASE') or die;

// Add span with id so that element fxs work.
$d = $displayData;

?>
<?php foreach ($d->tags as $tag) : ?>
	<a href="<?php echo $tag->url;?>" class="fabrikTag"><?php echo $tag->icon;?> <?php echo $tag->label;?></a>
<?php endforeach; ?>