<?php

defined('JPATH_BASE') or die;

// Add span with id so that element fxs work.
$d = $displayData;

$append = $d->geoCodeEvent === 'button' ? '' : 'input-append';

// Allow for 100% width
if ($d->width !== '')
{
	$d->width = 'width:' . $d->width;
	$d->width .= !strstr($d->width, '%') ? 'px; ' : '';
}

$edit = $d->editable ? '' : 'disabled="true"';

?>

<div class="fabrikSubElementContainer" id="<?php echo $d->id; ?>">
<?php if ($d->editable && $d->geocode != '0') : ?>

<div style="margin-bottom:5px" class="control-group <?php echo $append;?>">
<?php
endif;
?>
<?php if ($d->editable && $d->geocode == '1') : ?>
	<input type="text" class="geocode_input inputbox" />
<?php
endif;
?>

<?php if ($d->geocode != '0' && $d->geoCodeEvent == 'button' && $d->editable) : ?>
	<button class="button btn btn-info geocode" type="button">
		<?php echo FText::_('PLG_ELEMENT_GOOGLE_MAP_GEOCODE');?>
	</button>
<?php
endif;
?>

<?php if ($d->editable && $d->geocode != '0') : ?>
	</div>
<?php
endif;
?>


<div class="map" style="<?php echo $d->width;?>; height:<?php echo $d->height;?>px"></div>
<input type="hidden" class="fabrikinput" name="<?php echo $d->name;?>"
	value="<?php echo htmlspecialchars($d->value, ENT_QUOTES); ?>" />

<?php
if (($d->editable || $d->staticmap == '2') && $d->showlatlng) :
?>
	<div class="coord" style="margin-top:5px;">
		<input <?php echo $edit;?> size="23" value="<?php echo $d->coords[0];?> ° N" style="margin-right:5px" class="inputbox lat"/>
		<input <?php echo $edit;?> size="23" value="<?php echo $d->coords[1];?> ° E" class="inputbox lng"/></div>
<?php
endif;
?>

<?php
if (($d->editable || $d->staticmap == '2') && $d->showdms == '1') :
?>
	<div class="coord" style="margin-top:5px;">
		<input <?php echo $edit;?> size="23" value="<?php echo $d->dms->coords[0];?>" style="margin-right:5px" class="latdms"/>
		<input <?php echo $edit;?> size="23" value="<?php echo $d->dms->coords[1];?>" class="lngdms"/></div>
<?php
endif;
?>

<?php
if (($d->editable || $d->staticmap == '2') && $d->showosref == '1') :
?>
	<div class="coord" style="margin-top:5px;">
		<input <?php echo $edit;?> size="30" value="<?php echo $d->osref;?>" style="margin-right:5px" class="osref"/>
<?php
endif;
?>


</div>