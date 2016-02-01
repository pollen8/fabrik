<?php

defined('JPATH_BASE') or die;

$d = $displayData;

/*

<object width="<?php echo $d->width;?>" height="<?php echo $d->height; ?>">
	<param name="movie" value="<?php echo $d->value;?>" >
	<param name="allowFullScreen" value="true" />
	<param name="allowscriptaccess" value="always" />
	<embed src="<?php echo $d->value;?>"
		type="application/x-shockwave-flash"
		allowscriptaccess="always"
		allowfullscreen="true"
		width="<?php echo $d->width;?>"
		height="<?php echo $d->height; ?>"></embed>
</object>

*/
?>

<iframe
	id="ytplayer"
	type="text/html"
	width="<?php echo $d->width;?>"
	height="<?php echo $d->height; ?>"
    src="http://www.youtube.com/embed/<?php echo $d->vid; ?>?autoplay=<?php echo $d->autoplay; ?>&fs=<?php echo $d->fs; ?>"
    frameborder="0"
></iframe>