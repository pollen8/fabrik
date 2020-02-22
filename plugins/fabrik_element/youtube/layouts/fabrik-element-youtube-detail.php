<?php

defined('JPATH_BASE') or die;

$d      = $displayData;
$uri    = JUri::getInstance();
$scheme = $uri->getScheme();

if ($d->type === 'youtube') :
?>

<iframe
	id="ytplayer"
	type="text/html"
	width="<?php echo $d->width;?>"
	height="<?php echo $d->height; ?>"
    src="<?php echo $d->url; ?><?php echo $d->vid; ?>?autoplay=<?php echo $d->autoplay; ?>&fs=<?php echo $d->fs; ?>"
    frameborder="0"
></iframe>
<?php
elseif ($d->type === 'twitch') :
?>
	<iframe
		src="<?php echo $d->url; ?><?php echo $d->vid; ?>"
		width="<?php echo $d->width;?>"
		height="<?php echo $d->height; ?>"
		frameborder="0"
		scrolling="no"
		allowfullscreen="true">
	</iframe>
<?php
endif;
?>