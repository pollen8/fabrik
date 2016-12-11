<?php

defined('JPATH_BASE') or die;

$d      = $displayData;
$uri    = JUri::getInstance();
$scheme = $uri->getScheme();

?>

<iframe
	id="ytplayer"
	type="text/html"
	width="<?php echo $d->width;?>"
	height="<?php echo $d->height; ?>"
    src="<?php echo $scheme; ?>://www.youtube.com/embed/<?php echo $d->vid; ?>?autoplay=<?php echo $d->autoplay; ?>&fs=<?php echo $d->fs; ?>"
    frameborder="0"
></iframe>
