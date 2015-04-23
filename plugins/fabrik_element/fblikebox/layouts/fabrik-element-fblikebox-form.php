<?php
defined('JPATH_BASE') or die;

$d = $displayData;

echo $d->graphApi;
?>

<fb:like-box id="<?php echo $d->pageid;?>" width="<?php echo $d->width;?>"
	height="<?php echo $d->height;?>" connections="<?php echo $d->connections;?>"
	stream="<?php echo $d->stream;?>" header="<?php echo $d->header;?>" />