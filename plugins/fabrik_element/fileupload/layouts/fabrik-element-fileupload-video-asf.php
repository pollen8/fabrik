<?php
defined('JPATH_BASE') or die;

$d = $displayData;
?>
<object id="MediaPlayer" width="<?php echo $d->width; ?>" height="<?php echo $d->height; ?>"
	classid="CLSID:22D6f312-B0F6-11D0-94AB-0080C74C7E95" standby="Loading Windows Media Player components"
	type="application/x-oleobject" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,4,7,1112">

	<param name="filename" value="http://yourdomain/yourmovie.wmv">
	<param name="Showcontrols" value="true">
	<param name="autoStart" value="false">

	<embed type="application/x-mplayer2" src="<?php echo $d->src; ?>" name="MediaPlayer"
		width="<?php echo $d->width; ?>" height="<?php echo $d->height; ?>"></embed>

</object>