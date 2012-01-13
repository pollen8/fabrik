<?php
/**
 * Plugin element to render date picker
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class plgFabrik_ElementDate extends plgFabrik_Element
{

	//@TODO: filter code

	/** @var bol toggle to determine if storedatabaseformat resets the date to GMT*/
	protected $_resetToGMT = true;

	/**
	 * Dates are stored in database as GMT times
	 * i.e. with no offsets
	 * This is to allow us in the future of render dates based
	 * on user tmezone offsets
	 * Dates are displayed in forms and tables with the global timezone
	 * offset applied
	 */

	private function getNullDates()
	{
		$db = FabrikWorker::getDbo();
		return array('0000-00-000000-00-00','0000-00-00 00:00:00','0000-00-00','', $db->getNullDate());
	}

	/**
	 * shows the data formatted for the table view
	 * @param string data (should be in mySQL format already) - except if called from getEmailValue()
	 * @param string element name
	 * @param object all the data in the tables current row
	 * @return string formatted value
	 */

	function renderListData($data, $oAllRowsData)
	{
		if ($data == '') {
			return '';
		}
		//@TODO: deal with time options (currently can be defined in date_table_format param).

		$config = JFactory::getConfig();
		$tzoffset = new DateTimeZone($config->get('offset'));

		$aNullDates = $this->getNullDates();
		$params = $this->getParams();
		$store_as_local = (int)$params->get('date_store_as_local', 0);

		$groupModel = $this->getGroup();
		$data = FabrikWorker::JSONtoData($data, true);

		$f = $params->get('date_table_format', '%Y-%m-%d');

		if ($f == 'Y-m-d') {
			$f = '%Y-%m-%d';
		}
		$format = array();
		foreach ($data as $d) {
			if (!in_array($d, $aNullDates)) {
				$date = JFactory::getDate($d);
				//$$$ rob - if not time selector then the date gets stored as 2009-11-13 00:00:00
				//if we have a -1 timezone then date gets set to 2009-11-12 23:00:00
				//then shown as 2009-11-12 which is wrong
				if ($params->get('date_showtime') && !$store_as_local) {
					$date->setTimeZone($tzoffset);
				}
				if ($f == '{age}') {
					$format[] = date('Y') - $date->toFormat('%Y', true);
				} else {
					$format[] = $date->toFormat($f, true);
				}
			} else {
				$format[] = '';
			}
		}
		$data = json_encode($format);
		return parent::renderListData($data, $oAllRowsData);
	}

	/**
	 * shows the data formatted for the CSV export view
	 * @param string data (should be in mySQL format already)
	 * @param string element name
	 * @param object all the data in the tables current row
	 * @return string formatted value
	 */

	function renderListData_csv($data, $oAllRowsData)
	{
		//@TODO: deal with time options (currently can be defined in date_table_format param).

		$config = JFactory::getConfig();
		$tzoffset = new DateTimeZone($config->get('offset'));

		$db = FabrikWorker::getDbo();
		$aNullDates = $this->getNullDates();
		$params = $this->getParams();
		$element = $this->getElement();
		$store_as_local = (int)$params->get('date_store_as_local', 0);

		$groupModel = $this->getGroup();
		$data = FabrikWorker::JSONtoData($data, true);
		$f = $params->get('date_table_format', '%Y-%m-%d');
		// $$$ hugh - see http://fabrikar.com/forums/showthread.php?p=87507
		// Really don't think we need to worry about 'incraw' here. The raw, GMT/MySQL data will get
		// included in the _raw version of the element if incraw is selected. Here we just want to output
		// the regular non-raw, formatted, TZ'ed version.
		// $incRaw = JRequest::getVar('incraw', true);
		$incRaw = false;

		if ($f == 'Y-m-d') {
			$f = '%Y-%m-%d';
		}
		$format = array();
		foreach ($data as $d) {
			if (!in_array($d, $aNullDates)) {
				if ($incRaw) {
					$format[] = $d;
				}
				else {
					$date = JFactory::getDate($d);
					// $$$ hugh - added the showtime test so we don't get the day offset issue,
					// as per regular table render.
					if ($params->get('date_showtime') && !$store_as_local) {
						$date->setTimeZone($tzoffset);
					}
					if ($f == '{age}') {
						$format[] = date('Y') - $date->toFormat('%Y', true);
					} else {
						$format[] = $date->toFormat($f, true);
					}
				}
			} else {
				$format[] = '';
			}
		}
		if (count($format) > 1) {
			return json_encode($format);
		} else {
			return implode('', $format);
		}
	}

	/**
	 * @abstract
	 * used in things like date when its id is suffixed with _cal
	 * called from getLabel();
	 * @param initial id
	 */

	protected function modHTMLId(&$id){
		$id = $id ."_cal";
	}

	/**
	 * draws the form element
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$this->_data = $data;//need to store this for reuse in getCalOpts 3.0
		$config = JFactory::getConfig();
		$tzoffset = new DateTimeZone($config->get('offset'));
		$db = FabrikWorker::getDbo();
		$aNullDates = $this->getNullDates();
		FabrikHelperHTML::loadcalendar();
		$name = $this->getHTMLName($repeatCounter);
		$id	= $this->getHTMLId($repeatCounter);
		$params = $this->getParams();
		$element = $this->getElement();
		$format = $params->get('date_form_format', $params->get('date_table_format', '%Y-%m-%d'));
		$timeformat = $params->get('date_time_format');
		//value should be in mySQL format
		$value = $this->getValue($data, $repeatCounter);
		$store_as_local = (int)$params->get('date_store_as_local', 0);

		if ($params->get('date_showtime', 0) && !$element->hidden) {
			//cant have names as simply [] as json only picks up the last one
			$timeElName = $name."[time]";
			$name .= '[date]';
		}

		$readonly = $params->get('date_allow_typing_in_field', true) == false ? ' readonly="readonly" ' : "";
		$calopts = array('class'=>'fabrikinput inputbox', 'size'=>$element->width, 'maxlength'=>'19');
		if ($params->get('date_allow_typing_in_field', true) == false) {
			$calopts['readonly'] = 'readonly';
		}

		$str[] = '<div class="fabrikSubElementContainer" id="'.$id.'">';
		if (!in_array($value, $aNullDates) && FabrikWorker::isDate($value)) {

			$oDate = JFactory::getDate($value);
			//if we are coming back from a validation then we don't want to re-offset the date
			if (JRequest::getVar('Submit', '') == '' || $params->get('date_defaulttotoday', 0)) {

				// $$$ rob - if not time selector then the date gets stored as 2009-11-13 00:00:00
				//if we have a -1 timezone then date gets set to 2009-11-12 23:00:00
				//then shown as 2009-11-12 which is wrong
				if ($params->get('date_showtime') && !$store_as_local) {
					$oDate->setTimeZone($tzoffset);
				}
			}
			//get the formatted date
			$date = $oDate->toFormat($format, true);
			if (!$this->_editable) {
				$time = ($params->get('date_showtime', 0)) ? " " .$oDate->toFormat($timeformat, true) : '';
				return $date.$time;
			}

			//get the formatted time
			if ($params->get('date_showtime', 0)) {
				$time = $oDate->toFormat($timeformat, true);
			}
		} else {
			if (!$this->_editable) {
				return '';
			}
			$date = '';
			$time = '';
		}
		$this->formattedDate = $date;
		// $$$ hugh - OK, I am, as usual, confused.  We can't hand calendar() a date formatted in the
		// form/table format.
		$str[] = $this->calendar($date, $name, $id ."_cal", $format, $calopts, $repeatCounter);
		if ($params->get('date_showtime', 0) && !$element->hidden) {
			$timelength = strlen($timeformat);
			FabrikHelperHTML::addPath(COM_FABRIK_BASE.'plugins/fabrik_element/date/images/', 'image', 'form', false);
			$str[] = '<input class="inputbox fabrikinput timeField" '.$readonly.' size="'.$timelength.'" value="'.$time.'" name="'.$timeElName.'" />';
			$str[] = FabrikHelperHTML::image('time.png', 'form', @$this->tmpl, array('alt' => JText::_('PLG_ELEMENT_DATE_TIME'), 'class' => 'timeButton'));
		}
		$str[] = '</div>';
		return implode("\n", $str);
	}

	/**
	 * Enter description here...
	 *
	 * @param string $val
	 * @return string mySQL formatted date
	 */

	private function _indStoreDBFormat($val)
	{
		// $$$ hugh - sometimes still getting $val as an array with date and time,
		// like on AJAX submissions?  Or maybe from getEmailData()?  Or both?
		if (is_array($val)) {
			// $$$ rob do url decode on time as if its passed from ajax save the : is in format %3C or something
			$val = $val['date'].' '.$this->_fixTime(urldecode($val['time']));
		}
		else {
			$val = urldecode($val);
			//var_dump($val);exit;
		}

		$profiler = JProfiler::getInstance('Application');
		JDEBUG ? $profiler->mark('date:_indStoreDBFormat val = ' . $val) : null; // 0.5 sec here!
		$aNullDates = $this->getNullDates();
		if (in_array(trim($val), $aNullDates)) {
			return '';
		}
		jimport('joomla.utilities.date');
		$params = $this->getParams();
		$store_as_local = (int)$params->get('date_store_as_local', 0);
		$listModel = $this->getListModel();
		// $$$ hugh - offset_tz of 1 means 'in MySQL format, GMT'
		// $$$ hugh - offset_tz of 2 means 'in MySQL format, Local TZ'
		if ($listModel->_importingCSV && $params->get('date_csv_offset_tz', '0') == '1') {
			return $val;
		}
		else if ($listModel->_importingCSV && $params->get('date_csv_offset_tz', '0') == '2') {
			return $this->toMySQLGMT(JFactory::getDate($val));
		}

		//test if its already in correct format (or empty)
		// $$$ hugh - should now already be in string format
		//if ((is_string($val) && trim($val) === '') || (is_array($val) && trim(implode('', $val)) === '')) {
		if (trim($val) === '') {
			return '';
		}
		// $$$ rob moved beneath as here $val can be an array which gives errors as getDate expects a string
		/*$orig = JFactory::getDate($val);
		if ($val === $orig->toMySQL()) {
		return $this->toMySQLGMT( $orig);
		}*/
		if ($params->get('date_showtime', 0)) {
			$format = $params->get('date_form_format').' '.$params->get('date_time_format');
			// $$$ hugh - no can do, getDefault already munged $val into a string
			// $$$ rob - erm no! - its an array when submitting from the form, perhaps elsewhere its sent
			// as a string - so added test for array
			// $$$ hugh need to do this earlier, moved it to top of proc
			/*
			if (is_array($val)) {
				// $$$ rob do url decode on time as if its passed from ajax save the : is in format %3C or something
				$val = $val['date'].' '.$this->_fixTime(urldecode($val['time']));
			}
			*/
		} else {
			$format = $params->get('date_form_format', $params->get('date_table_format', '%Y-%m-%d'));
		}
		$orig = new FabDate($val);
		if (!$orig) {
			// if $val was not a valid date string return ''
			return '';
		}
		 if ($val === $orig->toMySQL()) {
		// $$$ rob if your custom form tmpl doesnt contain the date element then its value is already in mySQL format so return it
			return $val;
		}

		$b = FabrikWorker::strToDateTime($val, $format);
		//3.0 can't use timestamp as that gets offset as its taken as numeric by JDate
		//$orig = new FabDate($datebits['timestamp'], 2);
		$bstr = $b['year'].'-'.$b['mon'].'-'.$b['day'].' '.$b['hour'].':'.$b['min'].':'.$b['sec'];
		$orig = new FabDate($bstr);
		$this->_resetToGMT = true;

		if ($val === $orig->toMySQL() && $params->get('date_showtime', 0)) {
			$date = $this->toMySQLGMT($orig);
			return $date;
		}

		//$datebits = FabrikWorker::strToDateTime($val, $format);
		//3.0 produces a double offset in timezone
		//$date = JFactory::getDate($datebits['timestamp']);
		$date = $orig;
		if (!$params->get('date_showtime', 0) || $store_as_local) {
			$this->_resetToGMT = false;
		}
		$date = $this->toMySQLGMT($date);
		$this->_resetToGMT = true;
		return $date;
	}

	/**
	 * reset the date to GMT - inversing the offset
	 *@param date object
	 * @return string mysql formatted date
	 */

	function toMySQLGMT($date)
	{
		if ($this->_resetToGMT) {
			// $$$ rob 3.0 offset is no longer an integer but a timezone string
			$config = JFactory::getConfig();
			$tzoffset = new DateTimeZone($config->get('offset'));
			$hours = $tzoffset->getOffset($date) / (60 * 60);
			$invert = false;
			if ($hours < 0) {
				$invert = true;
				$hours = $hours * -1; //intervals can only be positive - set invert propery
			}
			// 5.3 only
			if (class_exists('DateInterval')) {
				$dateInterval = new DateInterval('PT'.$hours.'H');
				$dateInterval->invert = $invert;
				$date->sub($dateInterval);
			} else {
				$date->modify('+'.$hours.' hour');
			}
			return $date->toMySQL(true);
		}
		return $date->toMySQL();
	}

	/**
	 * formats the posted data for insertion into the database
	 * @param mixed thie elements posted form data
	 * @param array posted form data
	 */

	function storeDatabaseFormat($val, $data)
	{
		if (!is_array($val)) {
			// $$$ hugh - we really need to work out why some AJAX data is not getting urldecoded.
			// but for now ... a bandaid.
			$val = urldecode($val);
		}
		//@TODO: deal with failed validations
		$groupModel = $this->getGroup();
		if ($groupModel->isJoin() && is_array($val)) {
			if (JArrayHelper::getValue($val, 'time') !== '') {
				$val['time'] = $this->_fixTime(urldecode($val['time']));
			}
			$val = implode(" ", $val);
		} else {
			if ($groupModel->canRepeat()) {
				if (is_array($val)) {
					$res = array();
					foreach ($val as $v) {
						$res[] = $this->_indStoreDBFormat($v);

					}
					return json_encode($res);
				}
			}
		}
		return $this->_indStoreDBFormat($val);
	}

	/**
	 * used to format the data when shown in the form's email
	 * @param mixed element's data
	 * @param array form records data
	 * @param int repeat group counter
	 * @return string formatted value
	 */

	function getEmailValue($value, $data = array(), $repeatCounter = 0)
	{
		if ((is_array($value) && empty($value)) || (!is_array($value) && trim($value) == '')) {
			return '';
		}
		$groupModel = $this->getGroup();
		if ($groupModel->isJoin() && $groupModel->canRepeat()) {
			$value = $value[$repeatCounter];
		}
		// $$$ hugh - need to convert to database format so we GMT-ified date
		return $this->renderListData($this->storeDatabaseFormat($value, $data), new stdClass());
	}

	/**
	 * $$$ hugh - added 9/13/2009
	 * determines the label used for the browser title
	 * in the form/detail views
	 * @param array data
	 * @param int when repeating joinded groups we need to know what part of the array to access
	 * @param array options
	 * @return string default value
	 */

	function getTitlePart($data, $repeatCounter = 0, $opts = array())
	{
		$gmt_date = $this->getValue($data, $repeatCounter, $opts);
		// OK, now we've got the GMT date, convert it
		// ripped the following off from renderListData ... SURELY we must have a func
		// somewhere that does this?
		$params = $this->getParams();
		$store_as_local = (int)$params->get('date_store_as_local', 0);
		$config = JFactory::getConfig();
		$tzoffset = new DateTimeZone($config->get('offset'));
		$aNullDates = $this->getNullDates();
		$f = $params->get('date_table_format', '%Y-%m-%d');
		if ($f == 'Y-m-d') {
			$f = '%Y-%m-%d';
		}
		$tz_date = '';
		if (!in_array($gmt_date, $aNullDates)) {
			$date 	= JFactory::getDate($gmt_date);
			if (!$store_as_local) {
				$date->setTimeZone($tzoffset);
			}
			if ($f == '{age}') {
				$tz_date = date('Y') - $date->toFormat('%Y', true);
			} else {
				$tz_date = $date->toFormat($f, true);
			}
		}
		return $tz_date;
	}

	/**
	 * takes a raw value and returns its label equivalent
	 * @param string $v
	 */

	protected function toLabel(&$v)
	{
		$params = $this->getParams();
		$store_as_local = (int)$params->get('date_store_as_local', 0);
		$f = $params->get('date_table_format', '%Y-%m-%d');
		$tzoffset = new DateTimeZone(JFactory::getConfig()->get('offset'));
		$aNullDates = $this->getNullDates();
		$format = array();
		if (!in_array($v, $aNullDates)) {
			$date 	= JFactory::getDate($v);
			//$$$ rob - if not time selector then the date gets stored as 2009-11-13 00:00:00
			//if we have a -1 timezone then date gets set to 2009-11-12 23:00:00
			//then shown as 2009-11-12 which is wrong
			if ($params->get('date_showtime')) {
				$date->setTimeZone($tzoffset);
			}
			if ($f == '{age}') {
				$v = date('Y') - $date->toFormat('%Y', true);
			} else {
				$v = $date->toFormat($f, true);
			}
		} else {
			$v = '';
		}
	}

	/**
	 * ensure the time is in a full length format
	 *
	 * @param string $time
	 * @return formatted time
	 */

	protected function _fixTime($time)
	{
		//if its 5:00 rather than 05:00
		if (!preg_match("/^[0-9]{2}/", $time)) {
			$time = "0".$time;
		}
		//if no seconds
		if (preg_match("/[0-9]{2}:[0-9]{2}/", $time) && strlen($time) <= 5) {
			$time .= ":00";
		}
		//if it doesnt match reset it to 0
		if (!preg_match("/[0-9]{2}:[0-9]{2}:[0-9]{2}/", $time)) {
			$time = "00:00:00";
		}
		return $time;
	}

	/**
	 * Displays a calendar control field
	 *
	 * hacked from behaviour as you need to check if the element exists
	 * it might not as you could be using a custom template
	 * @param	string	The date value
	 * @param	string	The name of the text field
	 * @param	string	The id of the text field
	 * @param	string	The date format
	 * @param	array	Additional html attributes
	 * @param int repeat group counter
	 */

	function calendar($value, $name, $id, $format = '%Y-%m-%d', $attribs = null, $repeatCounter = 0)
	{
		FabrikHelperHTML::loadcalendar();
		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
		}

		/* $document = JFactory::getDocument();
		$opts = $this->_CalendarJSOpts($repeatCounter);
		$opts->ifFormat = $format;
		//$opts = json_encode($opts);

		$validations = $this->getValidations();

		$script = 'head.ready(function() {

		if($("'.$id.'")) { ';

		$subElContainerId = $this->getHTMLId($repeatCounter);

		$formModel = $this->getForm();
		//$$$rob might we get away with just testing if the view is a form or detailed view but for now leave as it is
		if (JRequest::getVar('task') != 'elementFilter' && JRequest::getVar('view') != 'table') {
			$opts = rtrim($opts, "}");
			$opts .= ',"onClose":onclose, "onSelect":onselect, "dateStatusFunc":datechange}';
			$script .= 'var onclose = (function(e) {
			this.hide();
			try{
				form_'.$formModel->getId().'.triggerEvents(\''.$subElContainerId.'\', ["blur", "click", "change"], this);
				window.fireEvent(\'fabrik.date.close\', this);
			}catch(err) {
				fconsole(err);
			};
			';

			if (!empty($validations)) {
				//if we have a validation on the element run it when the calendar closes itself
				//this ensures that alert messages are removed if the new data meets validation criteria
				$script .= 'form_'.$formModel->getId().'.doElementValidation(\''.$subElContainerId.'\');'."\n";
			}
			$script .= "});\n"; //end onclose function

			//onselect function
			$script .= 'var onselect = (function(calendar, date) {
 			$(\''.$id.'\').value = date;
 			 if (calendar.dateClicked) {
  		 calendar.callCloseHandler();
 		 }
			window.fireEvent(\'fabrik.date.select\', this);
				try{
					form_'.$formModel->getId().'.triggerEvents(\''.$subElContainerId.'\', ["click", "focus", "change"], this);
				}catch(err) {
					//fconsole(err);
				};
			});';
			//end onselect function

			//date change function
			$script .= '
			var datechange = (function(date) {
				try{
					return disallowDate(this, date);
				}catch(err) {
					//fconsole(err);
				}
			});
			';
			//end onselect function
		}
		$opts = json_encode($opts);
		$script .= 'Calendar.setup('.$opts.');'.
		'}'. //end if id
		"\n});"; //end domready function
		if (!$this->getElement()->hidden || JRequest::getVar('view') == 'list') {
			FabrikHelperHTML::addScriptDeclaration($script);
		} */
		$paths = FabrikHelperHTML::addPath(COM_FABRIK_BASE.'media/system/images/', 'image', 'form', false);
		$img = FabrikHelperHTML::image('calendar.png', 'form', @$this->tmpl, array('alt' => 'calendar', 'class' => 'calendarbutton', 'id' => $id.'_img'));
		return '<input type="text" name="'.$name.'" id="'.$id.'" value="'.htmlspecialchars($value, ENT_COMPAT, 'UTF-8').'" '.$attribs.' />'.
		$img;
	}

	/**
	 * get the options used for the date elements calendar
	 * @param $int repeat counter
	 * @return object ready for js encoding
	 */

	protected function _CalendarJSOpts($repeatCounter = 0)
	{
		$params = $this->getParams();
		$id = $this->getHTMLId($repeatCounter);
		$opts = new stdClass();
		$opts->inputField = $id;
		$opts->ifFormat = $params->get('date_form_format');
		$opts->button = $id."_img";
		$opts->align = "Tl";
		$opts->singleClick = true;
		$opts->firstDay = intval($params->get('date_firstday'));

		/// testing

		$validations = $this->getValidations();
		$opts->ifFormat = $params->get('date_form_format', $params->get('date_table_format', '%Y-%m-%d'));
		$opts->hasValidations = empty($validations) ? false : true;

		$opts->dateAllowFunc = $params->get('date_allow_func');
		return $opts;
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$params = $this->getParams();
		$element = $this->getElement();
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->hidden = (bool)$this->getElement()->hidden;
		if ($opts->hidden) {
			// $$$ rob 11/10/2011 if its hidden we dont want the defaultval as mysql
			// format as its used by form.js duplcateGroup
			// to set the value of the date element when its repeated.
			$opts->defaultVal = $this->_editable ? $this->formattedDate : '';
		}
		$opts->showtime = $params->get('date_showtime', 0) ? true : false;
		$opts->timelabel = JText::_('time');
		$opts->typing = $params->get('date_allow_typing_in_field', true);
		$opts->timedisplay = $params->get('date_timedisplay', 1);
		$validations = $this->getValidations();
		$opts->validations = empty($validations) ? false : true;
		$opts->dateTimeFormat = $params->get('date_time_format', '');

		//for reuse if element is duplicated in repeat group
		$opts->calendarSetup = $this->_CalendarJSOpts($repeatCounter);
		$opts = json_encode($opts);
		return "new FbDateTime('$id', $opts)";
	}

	/**
	 * get the type of field to store the data in
	 *
	 * @return string field description
	 */

	function getFieldDescription()
	{
		$p = $this->getParams();
		if ($this->encryptMe()) {
			return 'BLOB';
		}
		$groupModel = $this->getGroup();
		if (is_object($groupModel) && !$groupModel->isJoin() && $groupModel->canRepeat()) {
			return "VARCHAR(255)";
		} else {
			return "DATETIME";
		}
	}

	/**
	 *
	 * Examples of where this would be overwritten include timedate element with time field enabled
	 * @param int repeat group counter
	 * @return array html ids to watch for validation
	 */

	function getValidationWatchElements($repeatCounter)
	{
		$params = $this->getParams();
		$return	= array();
		$elName = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$return[] = array(
			'id' => $id,
			'triggerEvent' => 'blur'
		);
		return $return;
	}

	/**
	 * this really does get just the default value (as defined in the element's settings)
	 * @return unknown_type
	 */

	function getDefaultValue($data = array())
	{
		if (!isset($this->_default)) {
			$params = $this->getParams();
			$element = $this->getElement();
			$config = JFactory::getConfig();
			$tzoffset = new DateTimeZone($config->get('offset'));
			$store_as_local = (int)$params->get('date_store_as_local', 0);
			if ($params->get('date_defaulttotoday', 0)) {
				if ($store_as_local) {
					$localDate = date('Y-m-d H:i:s');
					$oTmpDate = JFactory::getDate(strtotime($localDate));
				}
				else {
					$oTmpDate = JFactory::getDate();
				}
				$default = $oTmpDate->toMySQL();
			}
			else {
				// deafult date should always be entered as gmt date e.g. eval'd default of:
				$default = $element->default;
				if ($element->eval == "1") {
					$default = @eval(stripslashes($default));
					FabrikWorker::logEval($default, 'Caught exception on eval in '.$element->name.'::getDefaultValue() : %s');
				}
				if (trim($default) != '') {
					$oTmpDate = JFactory::getDate($default);
					$default = $oTmpDate->toMySQL();
				}
			}
			$this->_default = $default;
		}
		return $this->_default;
	}

	/**
	 * can be overwritten by plugin class
	 * determines the value for the element in the form view
	 * @param array data
	 * @param int when repeating joinded groups we need to know what part of the array to access
	 * @param array options
	 * @return string default date value in GMT time
	 */

	function getValue($data, $repeatCounter = 0, $opts = array())
	{
		//@TODO: allow {now} and {today} to be replaced with current datetime
		if (!isset($this->defaults) || is_null($this->defaults)) {
			$this->defaults = array();
		}
		if (!array_key_exists($repeatCounter, $this->defaults)) {
			$groupModel = $this->getGroup();
			$group = $groupModel->getGroup();
			$joinid	= $group->join_id;
			$element = $this->getElement();
			$params	= $this->getParams();
			$store_as_local = (int)$params->get('date_store_as_local', 0);
			if ($params->get('date_alwaystoday', false)) {
				//$value = JFactory::getDate()->toMySQL(false);
				// $$$ rob fix for http://fabrik.unfuddle.com/projects/17220/tickets/by_number/700?cycle=true
				if ($store_as_local) {
					$localDate = date('Y-m-d H:i:s');
					$date = JFactory::getDate(strtotime($localDate));
				}
				else {
					$date = JFactory::getDate();
				}
				//bea: different than original date_time, local
				//$date = JFactory::getDate();
				//$config = JFactory::getConfig();
				//$tzoffset = $config->get('offset');
				//$date->setTimeZone( $tzoffset );
				$value = $date->toMySQL();
			} else {
				// $$$rob - if no search form data submitted for the search element then the default
				// selecton was being applied instead
				if (array_key_exists('use_default', $opts) && $opts['use_default'] == false) {
					$value = '';
				} else {
					$value = $this->getDefaultValue($data);
				}

				// $$$ hugh - as we now run removeTableNameFromSaveData(), I think we just need the short name?
				$name = $this->getFullName(false, true, false);
				//$name = $element->name;
				if ($groupModel->isJoin()) {
					if ($groupModel->canRepeat()) {
						if (array_key_exists('join', $data) && array_key_exists($joinid, $data['join']) && is_array($data['join'][$joinid]) && array_key_exists($name, $data['join'][$joinid]) && array_key_exists($repeatCounter, $data['join'][$joinid][$name])) {
							$value = $data['join'][$joinid][$name][$repeatCounter];
						}
					} else {
						if (array_key_exists('join', $data) && array_key_exists($joinid, $data['join']) && is_array($data['join'][$joinid]) && array_key_exists($name, $data['join'][$joinid])) {
							$value = $data['join'][$joinid][$name];
						}
					}
				} else {
					if ($groupModel->canRepeat()) {
						//repeat group NO join
						if (array_key_exists($name, $data)) {
							if (is_array($data[$name])) {
								//occurs on form submission for fields at least
								$a = $data[$name];

							} else {
								//occurs when getting from the db
								$a = FabrikWorker::JSONtoData($data[$name], true);
							}
							if (array_key_exists($repeatCounter, $a)) {
								$value = $a[$repeatCounter];
							}
						}

					} else {
						$value = JArrayHelper::getValue($data, $name, $value);
					}
				}

				if (is_array($value)) {
					//TIMEDATE option set - explode with space rather than comma
					//url decode if it comes from ajax calendar form

					if (array_key_exists('time', $value) && $value['time'] != '' && JArrayHelper::getValue($value, 'date') != '') {
						$value['time'] = $this->_fixTime(urldecode($value['time']));
						$value = implode(' ', $value);
					}
					else {
						//$value = '';
						$value = implode('', $value); //for validations in repeat groups with no time selector
					}
				}
				$formModel = $this->getForm();
				//stops this getting called from form validation code as it messes up repeated/join group validations
				if (array_key_exists('runplugins', $opts) && $opts['runplugins'] == 1) {
					FabrikWorker::getPluginManager()->runPlugins('onGetElementDefault', $formModel, 'form', $this);
				}
				//for validations (empty time and date element gives ' ')
				if ($value == ' ') {
					$value = '';
				}
			}
			$this->defaults[$repeatCounter] = $value;
		}
		return $this->defaults[$repeatCounter];
	}

	/**
	 * called on failed form validation.
	 * Ensures submitted form data is converted back into the format
	 * that the form would expect to get it in, if the data had been
	 * draw from the database record
	 * @param string submitted form value
	 * @return string formated value
	 */

	function toDbVal($str, $repeatCounter)
	{
		//only format if not empty otherwise search forms will filter
		//for todays date even when no date entered
		$this->_resetToGMT = false;
		if ($str != '') {
			$str = $this->storeDatabaseFormat($str, array());
		}
		$this->_resetToGMT = true;
		return $str;
	}

	/**
	 * @since 2.1.1
	 * does the format string contain time formatting options
	 * @param string date $format
	 */

	protected function formatContainsTime($format)
	{
		$times = array('%H','%I','%l','%M','%p','%P','%r','%R','%S','%T','%X','%z','%Z');
		foreach ($times as $t) {
			if (strpos($format, $t)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * this builds an array containing the filters value and condition
	 * If no date time option, then we change the filter into a ranged filter to search
	 * the whole day for records.
	 * @param string initial $value
	 * @param string intial $condition
	 * @param string eval - how the value should be handled
	 * @return array (value condition) values should be in mySQL format
	 */

	function getFilterValue($value, $condition, $eval)
	{
		$params = $this->getParams();
		$store_as_local = (int)$params->get('date_store_as_local', 0);
		if (!$params->get('date_showtime', 0) || $store_as_local) {
			$this->_resetToGMT = false;
		}

		$exactTime = $this->formatContainsTime($params->get('date_table_format'));

		$filterType = $this->getElement()->filter_type;
		switch ($filterType) {
			case 'field':
			case 'dropdown':

				if (!$params->get('date_showtime', 0) || $exactTime == false) {

					//$$$ rob turn into a ranged filter to search the entire day
					// values should be in table format and not mySQL as they are set to mySQL in getRangedFilterValue()
					$value = (array)$value;
					$condition = 'BETWEEN';
					//$value[1] = date("Y-m-d H:i:s", strtotime($this->addDays($value[0], 1)) - 1);
					$next = JFactory::getDate(strtotime($this->addDays($value[0], 1)) - 1);
					$value[1] = $next->toFormat($params->get('date_table_format', '%Y-%m-%d'));
				} else {
					//$mysql = $this->tableDateToMySQL($value);
					/* if ($mysql !== false) {
					$value = $mysql;
					} */
				}
				break;

			case 'ranged':
				$value = (array)$value;
				foreach ($value as &$v) {
					$mysql = $this->tableDateToMySQL($v);
					if ($mysql !== false) {
						$v = $mysql;
					}
				}
				break;
		}
		$this->_resetToGMT = true;
		$value = parent::getFilterValue($value, $condition, $eval);
		return $value;
	}

	/**
	 * Get the list filter for the element
	 * @param int filter order
	 * @param bol do we render as a normal filter or as an advanced search filter
	 * if normal include the hidden fields as well (default true, use false for advanced filter rendering)
	 * @return string filter html
	 */

	function getFilter($counter, $normal = true)
	{
		$params = $this->getParams();
		$listModel = $this->getListModel();
		$table = $listModel->getTable();
		$element = $this->getElement();
		$origTable = $table->db_table_name;
		$fabrikDb = $listModel->getDb();
		$elName = $this->getFullName(false, true, false);
		$elName2 = $this->getFullName(false, false, false);

		$ids = $listModel->getColumnData($elName2);
		$v = $this->filterName($counter, $normal);
		//corect default got
		$default = $this->getDefaultFilterVal($normal, $counter);
		$format = $params->get('date_table_format', '%Y-%m-%d');

		$fromTable = $origTable;
		$joinStr = '';
		// $$$ hugh - in advanced search, _aJoins wasn't getting set
		$joins = $listModel->getJoins();
		foreach ($joins as $aJoin) {
			// not sure why the group id key wasnt found - but put here to remove error
			if (array_key_exists('group_id', $aJoin)) {
				if ($aJoin->group_id == $element->group_id && $aJoin->element_id == 0) {
					$fromTable = $aJoin->table_join;
					$joinStr = " LEFT JOIN $fromTable ON ".$aJoin->table_join.".".$aJoin->table_join_key." = ".$aJoin->join_from_table.".".$aJoin->table_key;
					$elName = str_replace($origTable.'.', $fromTable.'.', $elName);
				}
			}
		}
		$where = $listModel->_buildQueryPrefilterWhere($this);
		$elName = FabrikString::safeColName($elName);

		//dont format here as the format string is different between mysql and php's calendar strftime
		$sql = "SELECT DISTINCT($elName) AS text, $elName AS value FROM `$origTable` $joinStr"
		. "\n WHERE $elName IN ('".implode("','", $ids)."')"
		. "\n AND TRIM($elName) <> '' $where GROUP BY text ASC";
		$requestName = $elName."___filter";
		if (array_key_exists($elName, $_REQUEST)) {
			if (is_array($_REQUEST[$elName]) && array_key_exists('value', $_REQUEST[$elName])) {
				$_REQUEST[$requestName] = $_REQUEST[$elName]['value'];
			}
		}
		$htmlid = $this->getHTMLId();
		$tzoffset = new DateTimeZone(JFactory::getConfig()->get('offset'));
		if (in_array($element->filter_type, array('dropdown'))) {
			$rows = $this->filterValueList($normal);
		}
		$calOpts = array('class'=>'inputbox fabrik_filter', 'maxlength'=>'19', 'size'=>16);
		$return = array();
		switch ($element->filter_type)
		{
			case "range":
				FabrikHelperHTML::loadcalendar();
				//@TODO: this messes up if the table date format is different to the form date format
				if (empty($default)) {
					$default = array('', '');
				}
				$return[] = JText::_('COM_FABRIK_DATE_RANGE_BETWEEN') .
				$this->calendar($default[0], $v.'[0]', $this->getHTMLId()."_filter_range_0_".JRequest::getVar('task'), $format, $calOpts);
				$return[] = '<br />'.JText::_('COM_FABRIK_DATE_RANGE_AND') .
				$this->calendar($default[1], $v.'[1]', $this->getHTMLId()."_filter_range_1".JRequest::getVar('task'), $format, $calOpts);

				break;

			case "dropdown":

				// cant do the format in the MySQL query as its not the same formatting
				// e.g. M in mysql is month and J's date code its minute
				jimport('joomla.utilities.date');
				$ddData = array();
				foreach ($rows as $k => $o) {
					if ($fabrikDb->getNullDate() === $o->text) {
						$o->text = '';
						$o->value = '';
					} else {

						$d = new FabDate($o->text);
						//@TODO add an option as to whether we format values or not (if records as timestamps we don't want to format the filter value as running
						// the filter will result in no records found. see http://fabrikar.com/forums/showthread.php?t=10964

						$o->value = $d->toFormat($format); //if we have a table format like %m-%d then we want to remove duplicate full times
						$o->text = $d->toFormat($format);
					}
					if (!array_key_exists($o->value, $ddData)) {
						$ddData[$o->value] = $o;
					}
				}

				array_unshift($ddData, JHTML::_('select.option', '', $this->filterSelectLabel()));

				$return[] = JHTML::_('select.genericlist', $ddData, $v, 'class="inputbox fabrik_filter" size="1" maxlength="19"', 'value', 'text', $default, $htmlid."_filter_range_0");
				break;
			default:
			case "field":
				FabrikHelperHTML::loadcalendar();
			if (is_array($default)) {
				$default = array_shift($default);
			}
			if (get_magic_quotes_gpc()) {
				$default = stripslashes($default);
			}
			$default = htmlspecialchars($default);

			$return[] = $this->calendar($default, $v, $htmlid."_filter_range_0_".JRequest::getVar('task'), $format, $calOpts);
			break;

			case 'hidden':
				if (is_array($default)) {
					$default = array_shift($default);
				}
				if (get_magic_quotes_gpc()) {
					$default = stripslashes($default);
				}
				$default = htmlspecialchars($default);
				$return[] = '<input type="hidden" name="'.$v.'" class="inputbox fabrik_filter" value="'.$default.'" id="'.$htmlid.'" />';
				break;

			case 'auto-complete':
				if (get_magic_quotes_gpc()) {
					$default = stripslashes($default);
				}
				$default = htmlspecialchars($default);
				$return[] = '<input type="hidden" name="'.$v.'" class="inputbox fabrik_filter" value="'.$default.'" id="'.$htmlid.'" />';
				$return[] = '<input type="text" name="'.$v.'-auto-complete" class="inputbox fabrik_filter autocomplete-trigger" value="'.$default.'" id="'.$htmlid.'-auto-complete" />';
				$autoId = '#listform_'.$listModel->getRenderContext().' .'.$id;
				if (!$normal) {
					$autoId = '#advanced-search-table .autocomplete-trigger';
				}
				FabrikHelperHTML::autoComplete($autoId, $this->getElement()->id, 'date');
				break;
		}
		if ($normal) {
			$return[] = $this->getFilterHiddenFields($counter, $elName);
		} else {
			$return[] = $this->getAdvancedFilterHiddenFields();
		}
		return implode("\n", $return);
	}

	/**
	 * @since 3.0 takes a date from the server and applies the timezone offset
	 * probably not the right way to do this but ive been at it all day
	 * @param object FabDate
	 */

	protected function toLocalTime(&$d){
		$tzoffset = new DateTimeZone(JFactory::getConfig()->get('offset'));
		$hours = $tzoffset->getOffset($d) / (60 * 60);
		$dateInterval = new DateInterval('PT'.$hours.'H');
		$d->add($dateInterval);

	}

	public function onAutocomplete_options()
	{
		//needed for ajax update (since we are calling this method via dispatcher element is not set
		$this->_id = JRequest::getInt('element_id');
		$this->getElement(true);
		$listModel = $this->getListModel();
		$table = $listModel->getTable();
		$db = $listModel->getDb();
		$name = $this->getFullName(false, false, false);
		$db->setQuery("SELECT DISTINCT($name) AS value, $name AS text FROM $table->db_table_name WHERE $name LIKE ".$db->Quote('%'.addslashes(JRequest::getVar('value').'%')));
		$tmp = $db->loadObjectList();
		$ddData = array();
		foreach ($tmp as &$t) {
			$this->toLabel($t->text);
			if (!array_key_exists($t->text, $ddData)) {
				$ddData[$t->text] = $t;
			}
		}
		$ddData = array_values($ddData);
		echo json_encode($ddData);
	}

	/**
	 * when importing csv data you can run this function on all the data to
	 * format it into the format that the form would have submitted the date
	 *
	 * @param array data
	 * @param string table column heading
	 * @param bool data is raw
	 */

	function prepareCSVData(&$data, $key, $is_raw = false)
	{
		if ($is_raw) {
			return;
		}
		$params = $this->getParams();
		$format = $params->get('date_form_format', '%Y-%m-%d %H:%S:%I');
		//go through data and turn any dates into unix timestamps
		for ($j = 0; $j < count($data); $j++) {
			$orig_data = $data[$j][$key];
			$date = JFactory::getDate($data[$j][$key]);
			$data[$j][$key] = $date->toFormat($format, true);
			// $$$ hugh - bit of a hack specific to a customer who needs to import dates with year as 1899,
			// which we then change to 1999 using a tablecsv import script (don't ask!). But of course FabDate doesn't
			// like dates outside of UNIX timestamp range, so the previous line was zapping them. So I'm just restoring
			// the date as found in the CSV file. This could have side effects if someone else tries to import invalid dates,
			// but ... ah well.
			if (empty($data[$j][$key]) && !empty($orig_data)) {
				$data[$j][$key] = $orig_data;
			}
		}
	}

	/**
	 * can be overwritten by plugin class
	 *
	 * Examples of where this would be overwritten include drop downs whos "please select" value might be "-1"
	 * @param string data posted from form to check
	 * @param int repeat group counter
	 * @return bol if data is considered empty then returns true
	 */

	function dataConsideredEmpty($data, $repeatCounter)
	{
		return ($data == '') ? true : false;
	}

	/**
	 * format the filter date range into a mySQL format
	 * @see components/com_fabrik/models/plgFabrik_Element#getRangedFilterValue($value)
	 */

	function getRangedFilterValue($value)
	{
		$db = FabrikWorker::getDbo();
		$params = $this->getParams();
		// $$$ hugh - need to convert dates to MySQL format for the query
		$value[0] = $this->tableDateToMySQL($value[0]);
		$value[1] = $this->tableDateToMySQL($value[1]);
		// $$$ hugh - if the first date is later than the second, swap 'em round
		// to keep 'BETWEEN' in the query happy
		if (strtotime($value[0]) > strtotime($value[1])) {
			$tmp_value = $value[0];
			$value[0] = $value[1];
			$value[1] = $tmp_value;
		}

		$exactTime = $this->formatContainsTime($params->get('date_table_format'));
		if (!$params->get('date_showtime', 0) || $exactTime == false) {
			// $$$ hugh - need to back this out by one second, otherwise we're including next day.
			// So ... say we are searching from '2009-07-17' to '2009-07-21', the
			// addDays(1) changes '2009-07-21 00:00:00' to '2009-07-22 00:00:00',
			// but what we really want is '2009-07-21 23:59:59'
			$value[1] = date("Y-m-d H:i:s", strtotime($this->addDays($value[1], 1)) - 1);
		}
		$value = $db->Quote($value[0])." AND ".$db->Quote($value[1]);
		$condition = 'BETWEEN';
		return array($value, $condition);
	}

	/**
	 * convert a table formatted date string into a mySQL formatted date string
	 * (if already in mySQL format returns the date)
	 * @param string date in table view format
	 * @return string date in mySQL format or false if string date could not be converted
	 */

	function tableDateToMySQL($v)
	{
		$params = $this->getParams();
		$store_as_local = (int)$params->get('date_store_as_local', 0);
		$format = $params->get('date_table_format', '%Y-%m-%d');
		$b = FabrikWorker::strToDateTime($v, $format);
		if (!is_array($b)) {
			return false;
		}
		//3.0 can't use timestamp as that gets offset as its taken as numeric by FabDate
		//$orig = new FabDate($datebits['timestamp'], 2);
		$bstr = $b['year'].'-'.$b['mon'].'-'.$b['day'].' '.$b['hour'].':'.$b['min'].':'.$b['sec'];
		$date = JFactory::getDate($bstr);
		if (in_array($v, $this->getNullDates()) || $v === $date->toMySQL()) {
			return $v;
		}

		if ($store_as_local) {
			$this->_resetToGMT = false;
		}
		$retval = $this->toMySQLGMT($date);
		$this->_resetToGMT = true;
		return $retval;
	}

	/**
	 * $$$ rob - not used??? 9/11/2010
	 * set a dates time to 00:00:00
	 * @param mixed $time The initial time for the FabDate object
	 * @return string mysql formatted date
	 */

	function setTimeToZero($date)
	{
		$date = JFactory::getDate($date);
		$thePHPDate = getdate($date->toUnix());
		$thePHPDate['hours'] = 0;
		$thePHPDate['minutes'] = 0;
		$thePHPDate['seconds'] = 0;
		$v = mktime($thePHPDate['hours'], $thePHPDate['minutes'], $thePHPDate['seconds'], $thePHPDate['mon'], $thePHPDate['mday'], $thePHPDate['year']);
		$date = JFactory::getDate($v);
		return $date->toMySQL($v);
	}

	/**
	 * add days to a date
	 * @param mixed $time The initial time for the FabDate object
	 * @param integer number of days to add (negtive to remove days)
	 * @return string mysql formatted date
	 */

	function addDays($date, $add = 0)
	{
		$date = JFactory::getDate($date);
		$thePHPDate = getdate($date->toUnix());
		$thePHPDate['mday'] = $thePHPDate['mday']+$add;
		$v = mktime($thePHPDate['hours'], $thePHPDate['minutes'], $thePHPDate['seconds'], $thePHPDate['mon'], $thePHPDate['mday'], $thePHPDate['year']);
		$date = JFactory::getDate($v);
		return $date->toMySQL($v);
	}

	/**
	 * add hours to a date
	 * @param mixed $time The initial time for the FabDate object
	 * @param integer number of days to add (negtive to remove days)
	 * @return string mysql formatted date
	 */

	function addHours($date, $add = 0)
	{
		$date = JFactory::getDate($date);
		$thePHPDate = getdate($date->toUnix());
		if ($thePHPDate['hours']+$add >= 24) {
			$thePHPDate['hours'] = 0;
			$thePHPDate['mday']++;
		} else if ($thePHPDate['hours']+$add < 0) {
			$thePHPDate['hours'] = 0;
			$thePHPDate['mday']--;
		} else {
			$thePHPDate['hours'] = $thePHPDate['hours']+$add;
		}
		$v = mktime($thePHPDate['hours'], $thePHPDate['minutes'], $thePHPDate['seconds'], $thePHPDate['mon'], $thePHPDate['mday'], $thePHPDate['year']);
		$date = JFactory::getDate($v);
		return $date->toMySQL($v);
	}

	/**
	 * build the query for the avg caclculation - can be overwritten in plugin class (see date element for eg)
	 * @param model $listModel
	 * @param string $label the label to apply to each avg
	 * @return string sql statement
	 */

	protected function getAvgQuery(&$listModel, $label = "'calc'")
	{
		$table 			=& $listModel->getTable();
		$joinSQL 		= $listModel->_buildQueryJoin();
		$whereSQL 	= $listModel->_buildQueryWhere();
		$name 			= $this->getFullName(false, false, false);
		return "SELECT FROM_UNIXTIME(AVG(UNIX_TIMESTAMP($name))) AS value, $label AS label FROM `$table->db_table_name` $joinSQL $whereSQL";
	}

	protected function getSumQuery(&$listModel, $label = "'calc'")
	{
		$table 			=& $listModel->getTable();
		$joinSQL 		= $listModel->_buildQueryJoin();
		$whereSQL 	= $listModel->_buildQueryWhere();
		$name 			= $this->getFullName(false, false, false);
		//$$$rob not actaully likely to work due to the query easily exceeding mySQL's TIMESTAMP_MAX_VALUE value but the query in itself is correct
		return "SELECT FROM_UNIXTIME(SUM(UNIX_TIMESTAMP($name))) AS value, $label AS label FROM `$table->db_table_name` $joinSQL $whereSQL";
	}

	public function simpleAvg($data)
	{
		$avg = $this->simpleSum($data)/count($data);
		return JFactory::getDate($avg)->toMySQL();
	}

	/**
	 * find the sum from a set of data
	 * can be overwritten in plugin - see date for example of averaging dates
	 * @param array $data to sum
	 * @return string sum result
	 */

	public function simpleSum($data)
	{
		$sum = 0;
		foreach ($data as $d) {
			$date = JFactory::getDate($d);
			$sum += $date->toUnix();
		}
		return $sum;
	}

	/**
	 * takes date's time value and turns it into seconds
	 * @param date object $date
	 * @return int seconds
	 */

	protected function toSeconds($date)
	{
		return (int)($date->toFormat('%H') * 60 * 60) + (int)($date->toFormat('%M') * 60) + (int)$date->toFormat('%S');
	}

	/**
	 * takes strftime time formatting - http://fr.php.net/manual/en/function.strftime.php
	 * and converts to format used in mySQL DATE_FORMAT http://dev.mysql.com/doc/refman/5.1/en/date-and-time-functions.html
	 */

	protected function strftimeTFormatToMySQL(&$format)
	{
		//PHP -> MySQL

		// $$$ hugh - can't do direct %x to %y, because str_replace's left to right processing,
		// so (for instance) %B translates to %M, which then gets translated again to %i
		// So ... do %x to ^@y (hopefully nobody will ever use ^@ in their format string!),
		// then replace all ^@'s with %'s.

		$search = array('%e', '%j', '%u', '%V', '%W', '%h', '%B', '%C', '%g',
'%G', '%M', '%P', '%r', '%R', '%T', '%X', '%z', '%Z', '%D', '%F', '%s', '%x', '%A');

		//$replace = array('%c', '%j', '%w', '%U', '%U', '%b', '%M', '', '%y',
		//'%Y', '%i', '%p', '%I:%i:%S %p', '%H:%i', '%H:%i:%S', '', '', '%H:%i:%S', '%m/%c/%y', '%Y-%m-%c', '', '%Y-%m-%c', '%W');
		$replace = array('^@e', '^@j', '^@w', '^@U', '^@U', '^@b', '^@M', '', '^@y',
'^@Y', '^@i', '^@p', '^@I:^@i:^@S ^@p', '^@H:^@i', '^@H:^@i:^@S', '', '', '^@H:^@i:^@S', '^@m/^@c/^@y', '^@Y-^@m-^@c', '', '^@Y-^@m-^@c', '^@W');

		$format = str_replace($search, $replace, $format);
		$format = str_replace('^@', '%', $format);
	}

	/**
	 * build the filter query for the given element.
	 * Can be overwritten in plugin - e.g. see checkbox element which checks for partial matches
	 * @param $key element name in format `tablename`.`elementname`
	 * @param $condition =/like etc
	 * @param $value search string - already quoted if specified in filter array options
	 * @param $originalValue - original filter value without quotes or %'s applied
	 * @param string filter type advanced/normal/prefilter/search/querystring/searchall
	 * @return string sql query part e,g, "key = value"
	 */

	function getFilterQuery($key, $condition, $value, $originalValue, $type = 'normal')
	{
		$this->encryptFieldName($key);
		switch ($condition) {
			case 'earlierthisyear':
				$query = " DAYOFYEAR($key) <= DAYOFYEAR($value) ";
				break;
			case 'laterthisyear':
				$query = " DAYOFYEAR($key) >= DAYOFYEAR($value) ";
				break;
			default:
				$params = $this->getParams();
			$format = $params->get('date_table_format');
			if ($format == '%a' || $format == '%A') {
				//special cases where we want to search on a given day of the week
				//note it wont work with ranged searches
				$this->strftimeTFormatToMySQL($format);
				$key = "DATE_FORMAT( $key , '$format')";
			}
			else if ($format == '%Y %B') {
				// $$$ hugh - testing horrible hack for different languages, initially for andorapro's site
				// Problem is, he has multiple language versions of the site, and needs to filter tables by "%Y %B" dropdown (i.e. "2010 November") in multiple languages.
				// FabDate automagically uses the selected language when we render the date
				// but when we get to this point, month names are still localized, i.e. in French or German
				// which MySQL won't grok (until 5.1.12)
				// So we need to translate them back again, *sigh*
				// FIXME - need to make all this more generic, so we can handle any date format which uses
				// month or day names.
				$matches = array();
				if (preg_match('#\d\d\d\d\s+(\S+)\b#', $value, $matches)) {
					$this_month = $matches[1];
					$en_month = $this->_monthToEnglish($this_month);
					$value = str_replace($this_month, $en_month, $value);
					$this->strftimeTFormatToMySQL($format);
					$key = "DATE_FORMAT( $key , '$format')";
				}
			}
			if ($type == 'querystring' && strtolower($value) == 'now') {
				$value = 'NOW()';
			}
			$query = " $key $condition $value ";
			break;
		}
		return $query;
	}

	/**
	 * called when copy row table plugin called
	 * @param mixed value to copy into new record
	 * @return mixed value to copy into new record
	 */

	public function onCopyRow($val)
	{
		$aNullDates = $this->getNullDates();
		if (empty($val) || in_array($val, $aNullDates)) {
			return $val;
		}
		$params = $this->getParams();
		if ($params->get('date_showtime', 0)) {
			$store_as_local = (int)$params->get('date_store_as_local', 0);
			if (!$store_as_local) {
				$date = JFactory::getDate($val);
				$config = JFactory::getConfig();
				$tzoffset = new DateTimeZone($config->get('offset'));
				$date->setTimeZone($tzoffset);
				$val = $date->toMySQL(true);
			}
		}
		return $val;
	}

	/**
	 * used by validations
	 * @param string this elements data
	 * @param string what condiion to apply
	 * @param string data to compare element's data to (if date already set to Y-m-d H:I:S so no need to apply storeDatabaseForm() on it
	 */

	public function greaterOrLessThan($data, $cond, $compare)
	{
		$data = $this->storeDatabaseFormat($data, null);
		// $$$ rob 30/06/2011 the line below was commented out - but if doing date compare on 2 fields formatting %d/%m/%Y then the compare unix time was not right
		$compare = $this->storeDatabaseFormat($compare, null);
		$data = JFactory::getDate($data)->toUnix();
		$compare = JFactory::getDate($compare)->toUnix();
		if ($cond == '>') {
			return $data > $compare;
		} else {
			return $data < $compare;
		}
	}

	/**
	 * Part of horrible hack for translating non-English words back
	 * to something MySQL will understand.
	 */
	private function _monthToEnglish($month, $abbr = false) {
		//$lang = JFactory::getLanguage();
		if ($abbr) {
			if (strcmp($month, JText::_('JANUARY_SHORT')) === 0) {
				return 'Jan';
			}
			if (strcmp($month, JText::_('FEBRUARY_SHORT')) === 0) {
				return 'Feb';
			}
			if (strcmp($month, JText::_('MARCH_SHORT')) === 0) {
				return 'Mar';
			}
			if (strcmp($month, JText::_('APRIL_SHORT')) === 0) {
				return 'Apr';
			}
			if (strcmp($month, JText::_('MAY_SHORT')) === 0) {
				return 'May';
			}
			if (strcmp($month, JText::_('JUNE_SHORT')) === 0) {
				return 'Jun';
			}
			if (strcmp($month, JText::_('JULY_SHORT')) === 0) {
				return 'Jul';
			}
			if (strcmp($month, JText::_('AUGUST_SHORT')) === 0) {
				return 'Aug';
			}
			if (strcmp($month, JText::_('SEPTEMBER_SHORT')) === 0) {
				return 'Sep';
			}
			if (strcmp($month, JText::_('OCTOBER_SHORT')) === 0) {
				return 'Oct';
			}
			if (strcmp($month, JText::_('NOVEMBER_SHORT')) === 0) {
				return 'Nov';
			}
			if (strcmp($month, JText::_('DECEMBER_SHORT')) === 0) {
				return 'Dec';
			}
		}
		else {
			if (strcmp($month, JText::_('JANUARY')) === 0) {
				return 'January';
			}
			if (strcmp($month, JText::_('FEBRUARY')) === 0) {
				return 'February';
			}
			if (strcmp($month, JText::_('MARCH')) === 0) {
				return 'March';
			}
			if (strcmp($month, JText::_('APRIL')) === 0) {
				return 'April';
			}
			if (strcmp($month, JText::_('MAY')) === 0) {
				return 'May';
			}
			if (strcmp($month, JText::_('JUNE')) === 0) {
				return 'June';
			}
			if (strcmp($month, JText::_('JULY')) === 0) {
				return 'July';
			}
			if (strcmp($month, JText::_('AUGUST')) === 0) {
				return 'August';
			}
			if (strcmp($month, JText::_('SEPTEMBER')) === 0) {
				return 'September';
			}
			if (strcmp($month, JText::_('OCTOBER')) === 0) {
				return 'October';
			}
			if (strcmp($month, JText::_('NOVEMBER')) === 0) {
				return 'November';
			}
			if (strcmp($month, JText::_('DECEMBER')) === 0) {
				return 'December';
			}
		}
		return $month;
	}

	/**
	 * load a new set of default properites and params for the element
	 * @return object element (id = 0)
	 */

	public function getDefaultProperties()
	{
		$item = parent::getDefaultProperties();
		$item->hidden = 1;
		return $item;
	}
}

/**
 * very samll override to JDate to stop 500 errors occuring (when Jdebug is on) if $date is not a valid date string
 */

class FabDate extends JDate{

	protected static $gmt;
	protected static $stz;

	public function __construct($date = 'now', $tz = null)
	{
		if (!date_create($date)) {
			return false;
		}
		// Create the base GMT and server time zone objects.
		if (empty(self::$gmt) || empty(self::$stz)) {
			self::$gmt = new DateTimeZone('GMT');
			self::$stz = new DateTimeZone(@date_default_timezone_get());
		}
		parent::__construct($date, $tz);
	}

}
?>