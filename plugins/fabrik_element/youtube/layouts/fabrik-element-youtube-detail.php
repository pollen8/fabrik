<?php

defined('JPATH_BASE') or die;

$d = $displayData;
?>

<object width="<?php echo $d['width'];?>" height="<?php echo $d['height']; ?>">
	<param name="movie" value="<?php echo $d['value'];?>"></param>
	<param name="allowFullScreen" value="true"></param>
	<param name="allowscriptaccess" value="always"></param>
	<embed src="<?php echo $d['value'];?>"
		type="application/x-shockwave-flash"
		allowscriptaccess="always"
		allowfullscreen="true"
		width="<?php echo $d['width'];?>"
		height="<?php echo $d['height']; ?>"></embed>
</object>
