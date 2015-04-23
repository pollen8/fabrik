<?php
defined('JPATH_BASE') or die;

$d = $displayData;

/**
 * Theoretically this should make sure browsers that support HTML 5 use it, if no HTML5 then IE will use 'object'
 * and everything else will use 'embed'.  Trying to make sure nothing does autoplay, but FF is being difficult!
 */
?>

<audio src="<?php echo $d->file;?>"  controls>
	<!--[if !ie]> -->
	<object data="<?php echo $d->file;?>"
		type="audio/x-mpeg">
		<param name="autoplay"
			value="false" />
		<param name="width"
			value="140" />
		<param name="height"
			value="40" />
		<param name="controller"
			value="true" />
		<param name="autostart"
			value="0" />
		Oops!
	</object>
	<!--<![endif]-->
	<!--[if ie]>
	<embed
		src="<?php echo $d->file;?>"
		autostart="false"
		playcount="true"
		loop="false"
		height="50"
		width="200"
		type="audio/mpeg"
		/>
	<![endif]-->
</audio>