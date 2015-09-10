<?php
/**
 * Layout: Yes/No field list view
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2014 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.2
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$d = $displayData;

?>

<div class="radius_search" id="radius_search<?php echo $d->renderOrder; ?>" style="left:-100000px;position:absolute;">
	<input type="hidden" name="radius_search_active<?php echo $d->renderOrder; ?>[]" value="<?php echo $d->active[0]; ?>" />

	<div class="radius_search_options">
		<input type="hidden" name="geo_code_def_zoom" value="<?php echo $d->defaultZoom; ?>" />
		<input type="hidden" name="geo_code_def_lat" value="<?php $d->defaultLat; ?>" />
		<input type="hidden" name="geo_code_def_lon" value="<?php $d->defaultLon; ?>" />

		<div class="row-fluid">
			<div class="span4">
				<?php echo FText::_('PLG_VIEW_RADIUS_DISTANCE'); ?>
			</div>
			<div class="span8">
				<?php echo $d->slider; ?>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span4">
				<?php echo FText::_('PLG_VIEW_RADIUS_FROM'); ?>
			</div>
			<div class="span8">
				<?php echo $d->select; ?>
			</div>
		</div>
	</div>
	<div class="radius_table fabrikList table">

		<div class="radius_search_place_container" style="<?php echo $d->type[0] == 'place' ? 'display:block' : 'display:none'; ?>;position:relative;">
			<input type="text" name="radius_search_place<?php echo $d->renderOrder; ?>-auto-complete"
				id="radius_search_place<?php echo $d->renderOrder; ?>-auto-complete" class="inputbox fabrik_filter autocomplete-trigger" value="<?php echo $d->place; ?>" />
			<input type="hidden" name="radius_search_place<?php echo $d->renderOrder; ?>"
				id="radius_search_place<?php echo $d->renderOrder; ?>" class="inputbox fabrik_filter autocomplete-trigger" value="<?php echo $d->placeValue; ?>" />
		</div>

		<div class="radius_search_coords_container" style="<?php echo $d->type[0] == 'latlon' ? 'display:block' : 'display:none'; ?>">
			<div class="row-fluid">
				<div class="span4">
					<label for="radius_search_lat_<?php echo $d->renderOrder; ?>"><?php echo FText::_('PLG_VIEW_RADIUS_LATITUDE'); ?></label>
				</div>
				<div class="span8">
					<input type="text" name="radius_search_lat" value="<?php echo $d->lat; ?>" id="radius_search_lat_<?php echo $d->renderOrder; ?>" size="6" />
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="radius_search_lon_<?php echo $d->renderOrder; ?>"><?php echo FText::_('PLG_VIEW_RADIUS_LONGITUDE'); ?></label>
				</div>
				<div class="span8">
					<input type="text" name="radius_search_lon" value="<?php echo $d->lon; ?>" id="radius_search_lon_<?php echo $d->renderOrder; ?>" size="6" />
				</div>
			</div>
		</div>
		<?php
		$style = $d->hasGeoCode && $d->type[0] == 'geocode' ? '' : 'position:absolute;left:-10000000px;';
		$style = ''; ?>
		<div class="radius_search_geocode" style="<?php echo $style; ?>">
			<div class="input-append">
				<input type="text" class="radius_search_geocode_field"
					name="radius_search_geocode_field<?php echo $d->renderOrder; ?>" value="<?php echo $d->address; ?>" />
				<?php
				if (!$d->geoCodeAsYouType) :
					?>
					<button class="btn button"><?php echo FText::_('COM_FABRIK_SEARCH'); ?></button>
					<?php
				endif;
				?>
			</div>
			<div class="radius_search_geocode_map" id="radius_search_geocode_map<?php echo $d->renderOrder; ?>"></div>
			<input type="hidden" name="radius_search_gedcode_lat<?php echo $d->renderOrder; ?>" value="<?php echo $d->searchLatitude; ?>" />
			<input type="hidden" name="radius_search_geocode_lon<?php echo $d->renderOrder; ?>" value="<?php echo $d->searchLongitude; ?>" />
		</div>

		<div class="radius_search_buttons" id="radius_search_buttons<?php echo $d->renderOrder; ?>">
			<input type="button" class="btn btn-link cancel" value="<?php echo FText::_('COM_FABRIK_CANCEL'); ?>" />
			<input type="button" name="filter" value="Go" class="fabrik_filter_submit button btn btn-primary">
		</div>
	</div>
	<input type="hidden" name="radius_prefilter" value="1" />

</div>