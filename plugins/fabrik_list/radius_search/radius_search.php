<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.radiussearch
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-list.php';

/**
 * Add a radius search option to the list filters
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.radiussearch
 * @since       3.0
 */

class plgFabrik_ListRadius_search extends plgFabrik_List
{

	/**
	 * Place coordinates
	 *
	 * @var array
	 */
	protected $placeCoordinates = null;

	/**
	 * Called when the list HTML filters are loaded
	 *
	 * @param   object  $params  plugin params
	 * @param   object  &$model  list model
	 *
	 * @return  void
	 */

	public function onMakeFilters($params, &$model)
	{
		if (!is_object($this->getMapElement()))
		{
			return;
		}
		$app = JFactory::getApplication();
		$baseContext = $this->getSessionContext();
		$this->model = $model;
		$f = new stdClass;
		$f->label = $params->get('radius_label', 'Radius search');
		$class = "class=\"inputbox fabrik_filter autocomplete-trigger\"";
		$type = $app->getUserStateFromRequest($baseContext . 'radius_search_type', 'radius_search_type', array('mylocation'));
		$style = $type[0] == 'place' ? 'display:block' : 'display:none';

		$placeElement = $this->getPlaceElement()->getElement();
		$context = $baseContext . 'radius_search_place-auto-complete';
		$name = "radius_search_place{$this->_counter}-auto-complete";
		$place = $app->getUserStateFromRequest($context, $name);

		$strPlace = "<div class=\"radius_search_place_container\" style=\"$style;position:relative;\">
		<input name=\"$name\" id=\"$name\" $class value=\"$place\"/>";

		$context = $baseContext . 'radius_search_place';
		$name = 'radius_search_place';
		$placeValue = $app->getUserStateFromRequest($context, $name);
		$strPlace .= "
		<input type=\"hidden\" name=\"$name\" id=\"$name{$this->_counter}\" $class value=\"$placeValue\"/>
		</div>";

		$style = $type[0] == 'latlon' ? 'display:block' : 'display:none';
		$lat = $app->getUserStateFromRequest($baseContext . 'lat', 'radius_search_lat');
		$lon = $app->getUserStateFromRequest($baseContext . 'lon', 'radius_search_lon');
		$strLatLon = "<div class=\"radius_search_coords_container\" style=\"$style\">
		<table><tr><td><label for=\"radius_search_lat\">" . JText::_('PLG_VIEW_RADIUS_LATITUDE')
			. "</label></td><td><input name=\"radius_search_lat\" value=\"$lat\" id=\"radius_search_lat\" $class size=\"6\"/></td></tr>
		<label><tr><td><label for=\"radius_search_lon\">" . JText::_('PLG_VIEW_RADIUS_LONGITUDE')
			. "</label></td><td><input name=\"radius_search_lon\" value=\"$lon\" id=\"radius_search_lon\" $class size=\"6\"/></td></tr></table></div>";

		$strSlider = $this->slider();

		$checked = $type[0] == 'mylocation' ? 'checked="checked"' : '';
		$options = "<label>" . JText::_('PLG_VIEW_RADIUS_MY_LOCATION')
			. "<input type=\"radio\" name=\"radius_search_type[]\" value=\"mylocation\" $checked /></label><br />";
		$checked = $type[0] == 'place' ? 'checked="checked"' : '';
		$options .= "<label>" . strip_tags($placeElement->label)
			. "<input type=\"radio\" name=\"radius_search_type[]\" value=\"place\" $checked /></label><br />";
		$checked = $type[0] == 'latlon' ? 'checked="checked"' : '';
		$options .= "<label>" . JText::_('PLG_VIEW_RADIUS_COORDINATES')
			. "<input type=\"radio\" name=\"radius_search_type[]\" value=\"latlon\" $checked /></label><br />";

		$active = $app->getUserStateFromRequest($baseContext . 'radius_serach_active', 'radius_search_active');
		if ($active[0] == 1)
		{
			$yessel = "checked=\"checked\"";
			$nosel = "";
		}
		else
		{
			$yessel = "";
			$nosel = "checked=\"checked\"";
		}
		$str = "<div class=\"radus_search\">

		<label>" . JText::_('PLG_VIEW_RADIUS_ACTIVE')
			. "<input type=\"radio\" $yessel name=\"radius_search_active[]\" value=\"1\" /></label>
		<label>" . JText::_('PLG_VIEW_RADIUS_INACTIVE')
			. "<input type=\"radio\" $nosel name=\"radius_search_active[]\" value=\"0\" /></label>
		<div class=\"radius_search_options\">
		<table class=\"radius_table\" style=\"width:100%\">
			<tr>
				<td>" . JText::_('PLG_VIEW_RADIUS_DISTANCE') . "</td>
				<td>$strSlider</td>
			<tr>
				<td>" . JText::_('PLG_VIEW_RADIUS_FROM')
			. ":<br />$options</td>
				<td style=\"text-align:left\">$strPlace $strLatLon
			</tr>
		</table>
		</div>
		";

		$str .= "</div>";
		$f->element = $str;
		$f->required = '';
		FabrikHelperHTML::addStyleDeclaration("table.radius_table{border-collapse:collapse;border:0;}
		table.radius_table td{border:0;}");
		$model->viewfilters[] = $f;
	}

	/**
	 * Get the coordinates for a place
	 *
	 * @param   string  $place  value selected in widget
	 *
	 * @return  array
	 */

	private function placeCoordinates($place)
	{
		if (isset($this->placeCoordinates))
		{
			return $this->placeCoordinates;
		}
		$mapElement = $this->getMapElement();
		$mapName = $mapElement->getFullName(false, true, false);
		$placeElement = $this->getPlaceElement()->getElement();
		$db = $this->model->getDb();
		$usekey = JRequest::getVar('usekey');
		JRequest::setVar('usekey', $placeElement->name);
		$row = $this->model->getRow($place);
		JRequest::SetVar('usekey', $usekey);
		if (is_object($row))
		{
			$coords = explode(':', str_replace(array('(', ')'), '', $row->$mapName));
			$this->placeCoordinates = explode(',', $coords[0]);
		}
		else
		{
			// No exact match lets unset the query and try to find a partial match
			// (perhaps the user didnt select anything from the dropdown?)
			unset($this->model->getForm()->query);
			$row = $this->model->findRow($placeElement->name, $place);
			if (is_object($row))
			{
				$coords = explode(':', str_replace(array('(', ')'), '', $row->$mapName));
				$this->placeCoordinates = explode(',', $coords[0]);
			}
			else
			{
				$this->placeCoordinates = array('', '');
			}
		}
		return $this->placeCoordinates;
	}

	/**
	 * This is used to put the radius search data into the listfilter model
	 * called from its getPostFilters() method. The data is then sent to tableModel->_request
	 * which is then stored in the session for future use
	 *
	 * @param   object  $params  plug-in params
	 * @param   object  &$model  list model
	 * @param   array   &$args   filters created from listfilter::getPostFilters();
	 *
	 * @return  void
	 */

	public function onGetPostFilter($params, &$model, &$args)
	{
		$this->model = $model;
		$filters = $model->tmpFilters;
		$v = JRequest::getVar('radius_search_distance');
		if ($v == '')
		{
			// V is empty for radius search = not adding in filters n onGetPostFilter() <br><br>';
			return;
		}

		$active = JRequest::getVar('radius_search_active', array(1));
		if ($active[0] == 0)
		{
			// Need to clear out any session filter (occurs when you search with r filter, then deactivate the filter
			$filterModel = $model->getFilterModel();
			$index = array_key_exists('elementid', $filters) ? array_search('radius_search', (array) $filters['elementid']) : false;
			if ($index !== false)
			{
				$filterModel->clearAFilter($filters, $index);
			}
			return;
		}

		$v = (int) $v;
		$key = $this->onGetFilterKey();

		$filters['value'][$key] = $v;
		$filters['condition'][$key] = '=';
		$filters['join'][$key] = 'AND';
		$filters['no-filter-setup'][$key] = 0;
		$filters['hidden'][$key] = 0;
		$filters['key'][$key] = $key;
		$filters['search_type'][$key] = 'normal';
		$filters['match'][$key] = 0;
		$filters['full_words_only'][$key] = 0;
		$filters['eval'][$key] = 0;
		$filters['required'][$key] = 0;
		$filters['access'][$key] = 0;
		$filters['grouped_to_previous'][$key] = 0;
		$filters['label'][$key] = $params->get('radius_label', 'Radius search');
		$filters['elementid'][$key] = $key;
		$query = $this->getQuery($params);
		$filters['sqlCond'][$key] = $query;
	}

	/**
	 * Build the sql query to filter the data
	 *
	 * @param   object  $params  plugin params
	 *
	 * @return  string  query's where statement
	 */

	protected function getQuery($params)
	{
		$app = JFactory::getApplication();
		$baseContext = $this->getSessionContext();
		$values = JArrayHelper::getValue($filters, 'value', array());
		$type = JRequest::getVar('radius_search_type');
		if ($type[0] == 'place')
		{
			$place = $app->getUserStateFromRequest($baseContext . 'radius_search_place', 'radius_search_place');
			list($latitude, $longitude) = $this->placeCoordinates($place);
		}
		else
		{

			$latitude = $app->getUserStateFromRequest($baseContext . 'lat', 'radius_search_lat');
			$longitude = $app->getUserStateFromRequest($baseContext . 'lon', 'radius_search_lon');
		}
		if (trim($latitude) === '' && trim($longitude) === '')
		{
			JRequest::setVar('radius_search_active', array(0));
			return;
		}
		$el = $this->getMapElement();
		$el = FabrikString::safeColName($el->getFullName(false, false, false));

		// Crazy sql to get the lat/lon from google map element
		$latfield = "SUBSTRING_INDEX(TRIM(LEADING '(' FROM $el), ',', 1)";
		$lonfield = "SUBSTRING_INDEX(SUBSTRING_INDEX($el, ',', -1), ')', 1)";
		$v = $this->getValue();
		if ($params->get('radius_unit', 'km') == 'km')
		{
			$query = "(((acos(sin((" . $latitude . "*pi()/180)) * sin(($latfield *pi()/180))+cos((" . $latitude
				. "*pi()/180)) * cos(($latfield *pi()/180)) * cos(((" . $longitude . "- $lonfield)*pi()/180))))*180/pi())*60*1.1515*1.609344) <= "
				. $v;
		}
		else
		{
			$query = "(((acos(sin((" . $latitude . "*pi()/180)) * sin(($latfield *pi()/180))+cos((" . $latitude
				. "*pi()/180)) * cos(($latfield *pi()/180)) * cos(((" . $longitude . "- $lonfield)*pi()/180))))*180/pi())*60*1.1515) <= " . $v;
		}
		return $query;
	}

	/**
	 * onFiltersGot method - run after the list has created filters
	 *
	 * @param   object  $params  plugin params
	 * @param   object  &$model  list
	 *
	 * @return bol currently ignored
	 */

	public function onFiltersGot($params, &$model)
	{
		$this->model = $model;
		$key = $this->onGetFilterKey();
		$app = JFactory::getApplication();
		$active = JRequest::getVar('radius_search_active', array(0));
		if ($active[0] == 0)
		{
			return;
		}

		$v = $this->getValue();
		$query = $this->getQuery($params);

		$model->filters['elementid'][] = null;
		$model->filters['value'][] = $v;
		$model->filters['condition'][] = '=';
		$model->filters['join'][] = 'AND';
		$model->filters['no-filter-setup'][] = 0;
		$model->filters['hidden'][] = 0;
		$model->filters['key'][] = $key;
		$model->filters['search_type'][] = 'normal';
		$model->filters['match'][] = 0;
		$model->filters['full_words_only'][] = 0;
		$model->filters['eval'][] = 0;
		$model->filters['required'][] = 0;
		$model->filters['access'][] = 0;
		$model->filters['grouped_to_previous'][] = 0;
		$model->filters['label'][] = $params->get('radius_label', 'Radius search');
		$model->filters['sqlCond'][] = $query;
		$model->filters['origvalue'][] = $v;
		$model->filters['filter'][] = $v;
	}

	/**
	 * Get radius search distance value
	 *
	 * @return number
	 */

	protected function getValue()
	{
		$baseContext = $this->getSessionContext();
		$app = JFactory::getApplication();
		$v = $app->getUserStateFromRequest($baseContext . 'radius_search_distance', 'radius_search_distance', '');
		if ($v == '')
		{
			return;
		}
		$v = (int) $v;
		return $v;
	}

	/**
	 * Build the html for the distance slider
	 *
	 * @return  string
	 */

	private function slider()
	{
		$v = $this->getValue();
		FabrikHelperHTML::stylesheet('plugins/fabrik_list/radius_search/radius_search.css');
		$str = "<div class=\"slider_cont\" style=\"width:200px;\">\n";
		$str .= "<div class=\"fabrikslider-line\" style=\"width:200px\">";
		$str .= "\n<div class=\"knob\"></div>\n";
		$str .= "</div>\n";
		$str .= "<input type=\"hidden\" class=\"radius_search_distance\" name=\"radius_search_distance\" value=\"$v\"/>\n";
		$str .= "<div class=\"slider_output\">$v" . $this->getParams()->get('radius_unit', 'km') . "</div>\n";
		$str .= "</div>";
		return $str;
	}

	/**
	 * Can the plug-in select list rows
	 *
	 * @return  bool
	 */

	public function canSelectRows()
	{
		return false;
	}

	/**
	 * Get the place element model
	 *
	 * @return  object  place element model
	 */

	private function getPlaceElement()
	{
		if (isset($this->placeElement))
		{
			return $this->placeElement;
		}
		$elements = $this->model->getElements('id', false);
		$params = $this->getParams();
		if (!array_key_exists($params->get('radius_placeelement'), $elements))
		{
			JError::raiseError(500, 'No place element found for radius search plugin');
		}
		else
		{
			$this->placeElement = $elements[$params->get('radius_placeelement')];
			return $this->placeElement;
		}
	}

	/**
	 * Get the map element model
	 *
	 * @return  object  element model
	 */

	private function getMapElement()
	{
		if (isset($this->mapElement))
		{
			return $this->mapElement;
		}
		$elements = $this->model->getElements('id');
		$params = $this->getParams();
		$this->mapElement = $elements[$params->get('radius_mapelement')];
		return $this->mapElement;
	}

	/**
	 * Load the javascript class that manages plugin interaction
	 * should only be called once
	 *
	 * @return  string  javascript class file
	 */

	public function loadJavascriptClass()
	{
		$el = $this->getPlaceElement();
		$mapelement = $this->getMapElement();
		if (!is_object($mapelement))
		{
			return;
		}
		$opts = array();
		$opts['container'] = 'radius_search_place_container';
		$listid = $this->model->get('id');
		$formid = $this->model->getFormModel()->get('id');
		$shortkey = $el->getElement()->name;
		if (!is_object($mapelement))
		{
			JError::raiseNotice(500, JText::_('Radius search plug-in active but map element unpublished'));
			return;
		}
		$mapfullkey = $mapelement->getFullName(false, true, false);
		FabrikHelperHTML::autoComplete("radius_search_place{$this->_counter}", $el->getElement()->id, $el->getElement()->plugin, $opts);
		FabrikHelperHTML::script('components/com_fabrik/libs/geo-location/geo.js');
		parent::loadJavascriptClass();
	}

	/**
	 * Return the javascript to create an instance of the class defined in formJavascriptClass
	 *
	 * @param   object  $params  plugin parameters
	 * @param   object  $model   list model
	 * @param   array   $args    array [0] => string table's form id to contain plugin
	 *
	 * @return bool
	 */

	public function onLoadJavascriptInstance($params, $model, $args)
	{
		parent::onLoadJavascriptInstance($params, $model, $args);
		if (!is_object($this->getMapElement()))
		{
			return false;
		}
		$opts = $this->getElementJSOptions($model);
		$opts->steps = (int) $params->get('radius_max', 100);
		$opts->unit = $params->get('radius_unit', 'km');
		$opts->value = $this->getValue();
		$opts = json_encode($opts);
		$this->jsInstance = "new FbListRadiusSearch($opts)";
		return true;
	}

}
