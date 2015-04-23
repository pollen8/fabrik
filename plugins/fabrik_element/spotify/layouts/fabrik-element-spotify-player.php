<?php

defined('JPATH_BASE') or die;

$d = $displayData;
?>

<iframe <?php echo implode(' ', $d->attributes);?> frameborder="0" allowtransparency="true"></iframe>