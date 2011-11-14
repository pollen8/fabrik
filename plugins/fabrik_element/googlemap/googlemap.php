<?php
/**
 * Plugin element to render fields
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models'.DS.'element.php');

class plgFabrik_ElementGooglemap extends plgFabrik_Element {

	protected static $geoJs = null;
	
	protected static $usestatic = null;
	
	/**
	 * shows the data formatted for the table view
	 * @param string data
	 * @param object all the data in the tables current row
	 * @return string formatted value
	 */

	function renderListData($data, $oAllRowsData)
	{
		$str = '';
		$params = $this->getParams();
		$w = $params->get('fb_gm_table_mapwidth');
		$h = $params->get('fb_gm_table_mapheight');
		$z = $params->get('fb_gm_table_zoomlevel');
		$data = FabrikWorker::JSONtoData($data, true);
		foreach ($data as $d) {
			if ($params->get('fb_gm_staticmap_tableview')) {
				$str .= $this->_staticMap($d, $w, $h);
			}
			else if ($params->get('fb_gm_staticmap_tableview_type_coords', 'num') == 'dms') {
				$str .= $this->_dmsformat($d);
			} else {
				$str .= $this->_microformat($d);
			}
		}
		return $str;
	}

	function renderListData_feed($data, $oAllRowsData)
	{
		$str = '';
		$data = FabrikWorker::JSONtoData($data, true);
		foreach ($data as $d) {
			$str .= $this->_georss($d);
		}
		return $str;
	}

	/**
	 * format the data as a georss
	 *
	 * @param string $data
	 * @return string html microformat markup
	 */

	function _georss($data)
	{
		if (strstr($data, '<georss:point>')) {
			return $data;
		}
		$o = $this->_strToCoords($data, 0);
		if($data != '') {
			$lon = trim($o->coords[1]);
			$lat = trim($o->coords[0]);
			$data = "<georss:point>{$lat},{$lon}</georss:point>";
		}
		return $data;
	}

	/**
	 * format the data as a microformat
	 *
	 * @param string $data
	 * @return string html microformat markup
	 */

	function _microformat($data)
	{
		$o = $this->_strToCoords($data, 0);
		if($data != '') {
			$data = "<div class=\"geo\">
			<span class=\"latitude\">{$o->coords[0]}</span>,
			<span class=\"longitude\">{$o->coords[1]}</span>
			</div>
			";
		}
		return $data;
	}

	/**
	 * $$$tom format the data as DMS
	 * [N,S,E,O] Degrees, Minutes, Seconds
	 *
	 * @param string $data
	 * @return string html DMS markup
	 */

	function _dmsformat($data)
	{
		$dms = $this->_strToDMS($data);
		if($data != '') {
			$data = "<div class=\"geo\">
			<span class=\"latitude\">{$dms->coords[0]}</span>,
			<span class=\"longitude\">{$dms->coords[1]}</span>
			</div>
			";
		}
		return $data;
	}

	/**
	 * as different map instances may or may not load geo.js we shouldnt put it in
	 * formJavascriptClass() but call this code from elementJavascript() instead.
	 * The files are still only loaded when needed and only once
	 */

	protected function geoJs()
	{
		if (!isset(self::$geoJs)) {
			$document = JFactory::getDocument();
			$params = $this->getParams();
			if ($params->get('fb_gm_defaultloc')) {
				$document->addScript("http://code.google.com/apis/gears/gears_init.js");
				FabrikHelperHTML::script('components/com_fabrik/libs/geo-location/geo.js');
				self::$geoJs = true;
			}
		}
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @param int repeat group counter
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$params = $this->getParams();
		$id = $this->getHTMLId($repeatCounter);
		$element = $this->getElement();
		$data = $this->_form->_data;
		$v = $this->getValue($data, $repeatCounter);
		$zoomlevel = (int)$params->get('fb_gm_zoomlevel');
		$o = $this->_strToCoords($v, $zoomlevel);
		$dms = $this->_strToDMS($v);
		$opts = $this->getElementJSOptions($repeatCounter);
		$this->geoJs();
		$opts->lat = (float)$o->coords[0];
		$opts->lon = (float)$o->coords[1];
		$opts->lat_dms = (float)$dms->coords[0];
		$opts->rowid = (int)JArrayHelper::getValue($data, 'rowid');
		$opts->lon_dms = (float)$dms->coords[1];
		$opts->zoomlevel = (int)$o->zoomlevel;
		$opts->control = $params->get('fb_gm_mapcontrol');
		$opts->scalecontrol = (bool)$params->get('fb_gm_scalecontrol');
		$opts->maptypecontrol = (bool)$params->get('fb_gm_maptypecontrol');
		$opts->overviewcontrol = (bool)$params->get('fb_gm_overviewcontrol');
		$opts->drag = (bool)($this->_form->_editable);
		$opts->staticmap = $this->_useStaticMap() ? true : false;
		$opts->maptype = $params->get('fb_gm_maptype');
		$opts->key = $params->get('fb_gm_key');
		$opts->scrollwheel = (bool)$params->get('fb_gm_scroll_wheel');
		$opts->streetView = (bool)$params->get('fb_gm_street_view');
		$opts->latlng           = $this->_editable ? $params->get('fb_gm_latlng', false) : false;
		$opts->sensor = (bool)$params->get('fb_gm_sensor', false);
		$opts->latlng_dms       = $this->_editable ? $params->get('fb_gm_latlng_dms', false) : false;
		$opts->geocode          = $params->get('fb_gm_geocode', false);
		$opts->geocode_event 	= $params->get('fb_gm_geocode_event', 'button');
		$opts->geocode_fields	= array();
		$opts->auto_center = (bool)$params->get('fb_gm_auto_center', false);
		if ($opts->geocode == '2') {
			foreach (array('addr1','addr2','city','state','zip','country') as $which_field) {
				$field_id = '';
				if ($field_id = $this->_getGeocodeFieldId($which_field, $repeatCounter)) {
					$opts->geocode_fields[] = $field_id;
				}
			}
		}
		$opts->reverse_geocode = $params->get('fb_gm_reverse_geocode', '0') == '0' ? false : true;
		if ($opts->reverse_geocode) {
			foreach (array('route' => 'addr1','neighborhood' => 'addr2','locality' => 'city','administrative_area_level_1' => 'state','postal_code' => 'zip','country' => 'country') as $google_field => $which_field) {
				$field_id = '';
				if ($field_id = $this->_getGeocodeFieldId($which_field, $repeatCounter)) {
					$opts->reverse_geocode_fields[$google_field] = $field_id;
				}
			}
		}
		$opts->center = (int)$params->get('fb_gm_defaultloc', 0);
		$opts = json_encode($opts);

		return "new FbGoogleMap('$id', $opts)";
	}

	function _getGeocodeFieldId($which_field, $repeatCounter = 0)
	{
		$listModel = $this->getlistModel();
		$params = $this->getParams();
		$field = $params->get('fb_gm_geocode_' . $which_field, false);
		if ($field) {
			$elementModel = FabrikWorker::getPluginManager()->getElementPlugin($field);
			return $elementModel->getHTMLId($repeatCounter);
		}
		return false;
	}

	/**
	 * determine if we use a google static map
	 * Option has to be turned on and element un-editable
	 *
	 * @return bol
	 */

	function _useStaticMap()
	{
		if (!isset(self::$usestatic)) {
			$params = $this->getParams();
			//requires you to have installed the pda plugin
			//http://joomup.com/blog/2007/10/20/pdaplugin-joomla-15/
			if (array_key_exists('ispda', $GLOBALS) && $GLOBALS['ispda'] == 1) {
				self::$usestatic = true;
			} else {
				self::$usestatic = ($params->get('fb_gm_staticmap') == '1' && !$this->_editable);
			}
		}
		return self::$usestatic;
	}

	/**
	 * util function to turn the saved string into coordinate array
	 *@param string coordinates
	 * @param int default zoom level
	 * @return object coords array and zoomlevel int
	 */

	function _strToCoords($v, $zoomlevel = 0)
	{
		$o = new stdClass();
		$o->coords = array('', '');
		$o->zoomlevel = (int)$zoomlevel;
		if (strstr($v, ",")) {
			$ar = explode(":", $v);
			$o->zoomlevel = count($ar) == 2 ? array_pop($ar) : 4;
			$v = FabrikString::ltrimword($ar[0], "(");
			$v = rtrim($v, ")");
			$o->coords = explode(",", $v);
		} else {
			$o->coords = array(0,0);
		}
		return $o;
	}

	/**
	 * $$$tom : util function to turn the saved string into DMS coordinate array
	 * @param string coordinates
	 * @param int default zoom level
	 * @return object coords array and zoomlevel int
	 */

	function _strToDMS($v)
	{
		$dms = new stdClass();
		$dms->coords = array('', '');
		if (strstr($v, ",")) {
			$ar = explode(":", $v);
			$v = FabrikString::ltrimword($ar[0], "(");
			$v = rtrim($v, ")");
			$dms->coords = explode(",", $v);

			// Latitude
			if (strstr($dms->coords[0], '-')) {
				$dms_lat_dir = 'S';
			} else {
				$dms_lat_dir = 'N';
			}
			$dms_lat_deg = abs((int)$dms->coords[0]);
			$dms_lat_min_float = 60 * (abs($dms->coords[0]) - $dms_lat_deg);
			$dms_lat_min = (int)$dms_lat_min_float;
			$dms_lat_sec_float = 60 * ($dms_lat_min_float - $dms_lat_min);

			//Round the secs
			$dms_lat_sec = round($dms_lat_sec_float, 0);
			//$dms_lat_sec = $dms_lat_sec_float;
			if ($dms_lat_sec == 60) {
				$dms_lat_min += 1;
				$dms_lat_sec = 0;
			}
			if ($dms_lat_min == 60) {
				$dms_lat_deg += 1;
				$dms_lat_min = 0;
			}

			//@TODO $$$tom Maybe add the possibility to "construct" our own format:
			// W87Â°43'41"
			// W 87Â° 43' 41" (with the spacing)
			// 87Â°43'41" W (Direction at the end)
			// etc.
			//
			// Also: for the seconds: use 1 quote (") or 2 single quotes ('') ? Right now: 1 quote

			// Currently W87Â°43'41"

			$dms->coords[0] = $dms_lat_dir.$dms_lat_deg.'&deg;'.$dms_lat_min.'&rsquo;'.$dms_lat_sec.'&quot;';

			// Longitude
			if (strstr($dms->coords[1], '-')) {
				$dms_long_dir = 'W';
			} else {
				$dms_long_dir = 'E';
			}
			$dms_long_deg = abs((int)$dms->coords[1]);
			$dms_long_min_float = 60 * (abs($dms->coords[1]) - $dms_long_deg);
			$dms_long_min = (int)$dms_long_min_float;
			$dms_long_sec_float = 60 * ($dms_long_min_float - $dms_long_min);

			//Round the secs
			$dms_long_sec = round($dms_long_sec_float, 0);
			//$dms_long_sec = $dms_long_sec_float;
			if ($dms_long_sec == 60) {
				$dms_long_min += 1;
				$dms_long_sec = 0;
			}
			if ($dms_long_min == 60) {
				$dms_long_deg += 1;
				$dms_long_min = 0;
			}

			$dms->coords[1] = $dms_long_dir.$dms_long_deg.'&deg;'.$dms_long_min.'&rsquo;'.$dms_long_sec.'&quot;';


		} else {
			$dms->coords = array(0, 0);
		}
		return $dms;
	}

	/**
	 * @access private
	 * get a static map
	 *
	 * @param string coordinates
	 * @param int width
	 * @param int height
	 * @param int zoom level
	 * @param int $repeatCounter
	 * @param bool is the static map in the table view
	 * @return string static map html
	 */

	function _staticMap($v, $w=null, $h=null, $z=null, $repeatCounter = 0, $tableView = false)
	{
		$id	= $this->getHTMLId($repeatCounter);
		$params = $this->getParams();
		if (is_null($w)) {
			$w = $params->get('fb_gm_mapwidth');
		}
		if (is_null($h)) {
			$h = $params->get('fb_gm_mapheight');
		}
		if (is_null($z)) {
			$z = $params->get('fb_gm_zoomlevel');
		}
		$k = $params->get('fb_gm_key');
		$icon = urlencode($params->get('fb_gm_staticmap_icon'));
		$o = $this->_strToCoords($v, $z);
		$lat = trim($o->coords[0]);
		$lon = trim($o->coords[1]);

		//$src = "http://maps.google.com/staticmap?center=$lat,$lon&zoom={$z}&size={$w}x{$h}&maptype=mobile&markers=$lat,$lon,&key={$k}";
		// new api3 url:
		$markers = '';
		if ($icon !== '') {
			$markers .="icon:$icon|";
		}
		$markers .= "$lat,$lon";
		$src = "http://maps.google.com/maps/api/staticmap?center=$lat,$lon&amp;zoom={$z}&amp;size={$w}x{$h}&amp;maptype=mobile&amp;markers=$markers&amp;sensor=false";
		$id = $tableView ? '' : "id=\"{$id}\"";
		$str =  "<div $id class=\"gmStaticMap\"><img src=\"$src\" alt=\"static map\" />";
		$str .= "</div>";
		return $str;
	}
	
	/**
	 * draws the form element
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		require_once(COM_FABRIK_FRONTEND.DS.'libs'.DS.'mobileuseragent'.DS.'mobileuseragent.php');
		require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'string.php');
		$ua 				= new MobileUserAgent();
		$id					= $this->getHTMLId($repeatCounter);
		$name 			= $this->getHTMLName($repeatCounter);
		$groupModel = $this->_group;
		$element 		= $this->getElement();
		$val 				= $this->getValue($data, $repeatCounter);

		$params 		= $this->getParams();
		$w 					= $params->get('fb_gm_mapwidth');
		$h 					= $params->get('fb_gm_mapheight');

		if ($this->_useStaticMap()) {
			return $this->_staticMap($val, null, null, null, $repeatCounter);
		} else {
			$val = JArrayHelper::getValue($data, $name, $val);//(array_key_exists($name, $data) && !empty($data[$name])) ? $data[$name] : $val;
			if ($element->hidden == '1') {
				return $this->getHiddenField($name, $data[$name], $id);
			}
			$str = '<div class="fabrikSubElementContainer" id="'.$id.'">';
			//if its not editable and theres no val don't show the map
			if ((!$this->_editable && $val !='') || $this->_editable) {
				if ($this->_editable && $params->get('fb_gm_geocode') != '0') {
					$str .= '<div style="margin-bottom:5px">';
				}
				if ($this->_editable && $params->get('fb_gm_geocode') == 1) {
					$str .= '<input class="geocode_input inputbox" style="margin-right:5px"/>';
				}

				if ($params->get('fb_gm_geocode') != '0' && $params->get('fb_gm_geocode_event', 'button') == 'button' && $this->_editable) {
					$str .= '<input class="button geocode" type="button" value="'.JText::_('PLG_ELEMENT_GOOGLE_MAP_GEOCODE').'" />';
				}
				if ($this->_editable && $params->get('fb_gm_geocode') != '0') {
					$str .= '</div>';
				}
				$str .= '<div class="map" style="width:'.$w.'px; height:'.$h.'px"></div>';
				$str .= '<input type="hidden" class="fabrikinput" name="'.$name.'" value="'.htmlspecialchars($val, ENT_QUOTES).'" />';
				if (($this->_editable || $params->get('fb_gm_staticmap') == '2') && $params->get('fb_gm_latlng') == '1') {
					$arrloc = explode(',', $val);
					$arrloc[0] = str_replace("(", "", $arrloc[0]);
					$arrloc[1] = array_key_exists(1, $arrloc ) ? str_replace(")", "", array_shift(explode(":", $arrloc[1]))) : '';
					$edit = $this->_editable ? '' : 'disabled="true"';
					$str .= '<div class="coord" style="margin-top:5px;">
					<input '.$edit.' size="23" value="'.$arrloc[0].' ° N" style="margin-right:5px" class="inputbox lat"/>
					<input '.$edit.' size="23" value="'.$arrloc[1].' ° E"  class="inputbox lng"/></div>';
				}
				if (($this->_editable || $params->get('fb_gm_staticmap') == '2') && $params->get('fb_gm_latlng_dms') == '1') {
					$dms = $this->_strToDMS($val);
					$edit = $this->_editable ? '' : 'disabled="true"';
					$str .= '<div class="coord" style="margin-top:5px;">
					<input '.$edit.' size=\"23\" value="'.$dms->coords[0].'" style="margin-right:5px" class="latdms"/>
					<input '.$edit.' size=\"23\" value="'.$dms->coords[1].'"  class="lngdms"/></div>';
				}
				$str .= '</div>';
			} else {
				$str .= JText::_('PLG_ELEMENT_GOOGLEMAP_NO_LOCATION_SELECTED');
			}
			$str .= $this->_microformat($val);
			return $str;
		}
	}

	/**
	 * can be overwritten in the plugin class - see database join element for example
	 * @param array
	 * @param array
	 * @param array options
	 */

	function getAsField_html(&$aFields, &$aAsFields, $opts = array())
	{
		$dbtable = $this->actualTableName();
		$db = FabrikWorker::getDbo();
		$listModel = $this->getlistModel();
		$table 		=& $listModel->getTable();

		$fullElName = JArrayHelper::getValue($opts, 'alias', "$dbtable" . "___" . $this->_element->name);
		$dbtable = $db->nameQuote($dbtable);
		$str = $dbtable.".".$db->nameQuote($this->_element->name)." AS ".$db->nameQuote($fullElName);
		if ($table->db_primary_key == $fullElName) {
			array_unshift($aFields, $fullElName);
			array_unshift($aAsFields, $fullElName);
		} else {
			$aFields[] 	= $str;
			$aAsFields[] =  $db->nameQuote($fullElName);
			$rawName = "$fullElName". "_raw";
			$aFields[]				= $dbtable.".".$db->nameQuote($this->_element->name)." AS ".$db->nameQuote($rawName);
			$aAsFields[]			= $db->nameQuote($rawName);
		}
	}

	/**
	 * this really does get just the default value (as defined in the element's settings)
	 * @return unknown_type
	 */

	function getDefaultValue($data = array())
	{
		if (!isset($this->_default)) {
			$params = $this->getParams();
			// $$$ hugh - added parens around lat,long for consistancy!
			$this->_default = '(' . $params->get('fb_gm_lat') . ',' . $params->get('fb_gm_long') . ')' . ':' . $params->get('fb_gm_zoomlevel');
		}
		return $this->_default;
	}


	function getValue($data, $repeatCounter = 0, $opts = array())
	{
		if (is_null($this->defaults)) {
			$this->defaults = array();
		}
		if (!array_key_exists($repeatCounter, $this->defaults)) {
			$groupModel = $this->getGroup();
			$formModel = $this->getForm();
			$element = $this->getElement();
			$listModel = $this->getlistModel();
			$params = $this->getParams();

			// $$$rob - if no search form data submitted for the search element then the default
			// selection was being applied instead
			if (array_key_exists('use_default', $opts) && $opts['use_default'] == false) {
				$value = '';
			} else {
				$value = $this->getDefaultValue($data);
			}

			$table = $listModel->getTable();
			if ($groupModel->canRepeat() == '1') {
				$fullName = $table->db_table_name . $formModel->_joinTableElementStep . $element->name;
				if (isset($data[$fullName])) {
					if (is_array($data[$fullName])) {
						$value = $data[$fullName][0];
					} else {
						$value = $data[$fullName];
					}
					//$value = explode(GROUPSPLITTER, $value);
					$value = FabrikWorker::JSONtoData($value, true);
					if (array_key_exists($repeatCounter, $value)) {
						$value = $value[$repeatCounter];
						if (is_array($value)) {
							$value = implode(',', $value);
						}
						return $value;
					}
				}
			}
			if ($groupModel->isJoin()) {
				$fullName = $this->getFullName(false, true, false);
				$joinid = $groupModel->getGroup()->join_id;
				if (isset($data['join'][$joinid][$fullName])) {
					$value = $data['join'][$joinid][$fullName];
					if (is_array($value) && array_key_exists($repeatCounter, $value)) {
						$value = $value[$repeatCounter];
					}
				} else {
					// $$$ rob - prob not used but im leaving in just in case
					if (isset($data[$fullName])) {
						$value = $data[$fullName];
						if (is_array($value) && array_key_exists($repeatCounter, $value)) {
							$value = $value[$repeatCounter];
						}
					}
				}
			} else {
				$fullName = $table->db_table_name . $formModel->_joinTableElementStep . $element->name;
				if (isset($data[$fullName])) {
					/* drop down  */
					if (is_array($data[$fullName])) {

						if (isset($data[$fullName ][0])) {
							/* if not its a file upload el */
							$value = $data[$fullName][0];
						}
					} else {
						$value = $data[$fullName];
					}
				}
			}
			if ($value === '') { //query string for joined data
				$value = JArrayHelper::getValue($data, $fullName);
			}
			//stops this getting called from form validation code as it messes up repeated/join group validations
			if (array_key_exists('runplugins', $opts) && $opts['runplugins'] == 1) {
				FabrikWorker::getPluginManager()->runPlugins('onGetElementDefault', $formModel, 'form', $this);
			}
			/** ensure that the data is a string **/
			if (is_array($value)) {
				$value  = implode(',', $value);
			}
			$this->defaults[$repeatCounter] = $value;
		}
		return $this->defaults[$repeatCounter];
	}
}
?>