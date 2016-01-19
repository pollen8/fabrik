<?php
/**
 * Default list element render
 * Override this file in plugins/fabrik_element/{plugin}/layouts/fabrik-element-{plugin}-list.php
 */

defined('JPATH_BASE') or die;

$d = $displayData;
$props = isset($d->properties) ? $d->properties : '';
?>
<i data-isicon="true" class="<?php echo $d->icon;?>" <?php echo $props;?>></i>