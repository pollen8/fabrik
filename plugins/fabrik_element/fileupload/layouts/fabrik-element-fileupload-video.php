<?php
defined('JPATH_BASE') or die;

$d = $displayData;
?>

<video
	width="<?php echo $d->width; ?>" height="<?php echo $d->height; ?>"
	controls
	src="<?php echo $d->src; ?>"
	>
	<object width="<?php echo $d->width; ?>" height="<?php echo $d->height; ?>"
		classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"
		codebase="http://www.apple.com/qtactivex/qtplugin.cab"
		>
		<param name="src" value="<?php echo $d->src; ?>">
		<param name="autoplay" value="false">
		<param name="controller" value="true">
		<embed src="<?php echo $d->src; ?>" width="<?php echo $d->height; ?>" height="<?php echo $d->height; ?>"
			autoplay="false" controller="true"
			pluginspage="http://www.apple.com/quicktime/download/"
			>
		</embed>
	</object>
</video>