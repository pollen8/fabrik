<?php
defined('JPATH_BASE') or die;

$d = $displayData;

if ($d->hidden) :
	?>
	<!-- <?php echo $d->text; ?> -->
<?php
else :
	echo $d->text;
endif;
