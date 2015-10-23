<?php
/**
 * Button layout
 */

defined('JPATH_BASE') or die;

$d          = $displayData;
$attributes = isset($d->attributes) ? $d->attributes : '';
$type       = isset($d->type) ? 'type="' . $d->type . '"' : '';
?>

<button <?php echo $type; ?> class="btn <?php echo $d->class; ?>" <?php echo $attributes; ?> name="<?php echo $d->name; ?>">
	<?php echo $d->label; ?>
</button>

