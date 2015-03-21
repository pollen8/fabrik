<?php
use \Joomla\Utilities\ArrayHelper;

$d             = $displayData;
$digsig_width  = ArrayHelper::getValue($d, 'digsig_width', 200);
$digsig_height = ArrayHelper::getValue($d, 'digsig_height', 100);
$link          = ArrayHelper::getValue($d, 'link');
?>

<img src="<?php echo $link; ?>" width="<?php echo $digsig_width; ?>" height="<?php echo $digsig_height; ?>" />