<?php
/**
 * Button layout
 */

defined('JPATH_BASE') or die;

$d          = $displayData;
$attributes = isset($d->attributes) ? $d->attributes : '';
$type       = isset($d->type) ? 'type="' . $d->type . '"' : '';
$tag        = isset($d->tag) ? $d->tag : 'button'; // button or a
$name       = isset($d->name) ? 'name="' . $d->name . '"' : '';
?>

<<?php echo $tag; ?> <?php echo $type; ?> class="btn <?php echo $d->class; ?>" <?php echo $attributes; ?> <?php echo $name; ?>>
<?php echo $d->label; ?>
</<?php echo $tag; ?>>

