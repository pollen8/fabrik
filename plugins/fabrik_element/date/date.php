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

	/** @var bool toggle to determine if storedatabaseformat resets the date to GMT*/
	protected $_resetToGMT = true;

	protected $rangeFilterSet = false;

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
	 * @param	string	data (should be in mySQL format already)
	 * @param	object	all the data in the tables current row
	 * @return	string	formatted value
	 */

	public function renderListData($data, &$thisRow)
	{
		if ($data == '')
		{
			return '';
		}
		//@TODO: deal with time options (currently can be defined in date_table_format param).
		$timeZone = new DateTimeZone(JFactory::getConfig()->get('offset'));
		$aNullDates = $this->getNullDates();
		$params = $this->getParams();
		$store_as_local = (int) $params->get('date_store_as_local', 0);
		$groupModel = $this->getGroup();
		$data = FabrikWorker::JSONtoData($data, true);
		$f = $params->get('date_table_format', '%Y-%m-%d');
		if ($f == 'Y-m-d')
		{
			$f = '%Y-%m-%d';
		}
		$format = array();
		foreach ($data as $d)
		{
			if (!in_array($d, $aNullDates))
			{
				$date = JFactory::getDate($d);
				//$$$ rob - dates always stored with time (and hence timezone offset) so, unless stored_as_local
				// we must set the timezone
				if (!$store_as_local)
				{
					$date->setTimeZone($timeZone);
				}
				if ($f == '{age}')
				{
					$format[] = date('Y') - $date->format('Y', true);
				}
				else
				{
					$format[] = $date->toFormat($f, true);
				}
			}
			else
			{
				$format[] = '';
			}
		}
		$data = json_encode($format);
		return parent::renderListData($data, $thisRow);
	}

	/**
	 * (non-PHPdoc)
	 * @see plgFabrik_Element::renderListData_csv()
	 */

	public function renderListData_csv($data, &$thisRow)
	{
		//@TODO: deal with time options (currently can be defined in date_table_format param).
		$timeZone = new DateTimeZone(JFactory::getConfig()->get('offset'));
		$db = FabrikWorker::getDbo();
		$aNullDates = $this->getNullDates();
		$params = $this->getParams();
		$element = $this->getElement();
		$store_as_local = (int) $params->get('date_store_as_local', 0);

		$groupModel = $this->getGroup();
		$data = FabrikWorker::JSONtoData($data, true);
		$f = $params->get('date_table_format', '%Y-%m-%d');
		// $$$ hugh - see http://fabrikar.com/forums/showthread.php?p=87507
		// Really don't think we need to worry about 'incraw' here. The raw, GMT/MySQL data will get
		// included in the _raw version of the element if incraw is selected. Here we just want to output
		// the regular non-raw, formatted, TZ'ed version.
		// $incRaw = JRequest::getVar('incraw', true);
		$incRaw = false;

		if ($f == 'Y-m-d')
		{
			$f = '%Y-%m-%d';
		}
		$format = array();
		foreach ($data as $d)
		{
			if (!in_array($d, $aNullDates))
			{
				if ($incRaw)
				{
					$format[] = $d;
				}
				else
				{
					$date = JFactory::getDate($d);
					// $$$ hugh - added the showtime test so we don't get the day offset issue,
					// as per regular table render.
					if ($params->get('date_showtime') && !$store_as_local)
					{
						$date->setTimeZone($timeZone);
					}
					if ($f == '{age}')
					{
						$format[] = date('Y') - $date->format('Y', true);
					}
					else
					{
						$format[] = $date->toFormat($f, true);
					}
				}
			}
			else
			{
				$format[] = '';
			}
		}
		if (count($format) > 1)
		{
			return json_encode($format);
		}
		else
		{
			return implode('', $format);
		}
	}

	/**
	 * used in things like date when its id is suffixed with _cal
	 * called from getLabel();
	 * @param	string	initial	id
	 * @return	string	modified id
	 */

	protected function modHTMLId(&$id)
	{
		$id = $id . '_cal';
	}

	/**
	 * draws the form element
	 * @param	array	data to preopulate element with
	 * @param	int		repeat group counter
	 * @return	string	returns field element
	 */

	function render($data, $repeatCounter = 0)
	{
		$timeZone = new DateTimeZone(JFactory::getConfig()->get('offset'));
		$this->offsetDate = '';
		$aNullDates = $this->getNullDates();
		FabrikHelperHTML::loadcalendar();
		$name = $this->getHTMLName($repeatCounter);
		$id	= $this->getHTMLId($repeatCounter);
		$params = $this->getParams();
		$element = $this->getElement();
		$format = $params->get('date_form_format', $params->get('date_table_format', '%Y-%m-%d'));
		$timeformat = $params->get('date_time_format');
		//value is in mySQL format
		$value = $this->getValue($data, $repeatCounter);
		$store_as_local = (bool)$params->get('date_store_as_local', 0);

		if ($params->get('date_showtime', 0) && !$element->hidden)
		{
			//cant have names as simply [] as json only picks up the last one
			$timeElName = $name."[time]";
			$name .= '[date]';
		}

		$readonly = $params->get('date_allow_typing_in_field', true) == false ? ' readonly="readonly" ' : '';
		$calopts = array('class' => 'fabrikinput inputbox', 'size' => $element->width, 'maxlength' => '19');
		if ($params->get('date_allow_typing_in_field', true) == false)
		{
			$calopts['readonly'] = 'readonly';
		}

		$str[] = '<div class="fabrikSubElementContainer" id="' . $id . '">';
		if (!in_array($value, $aNullDates) && FabrikWorker::isDate($value))
		{
			$oDate = JFactory::getDate($value);
			//if we are coming back from a validation then we don't want to re-offset the date
			if (JRequest::getVar('Submit', '') == '' || $params->get('date_defaulttotoday', 0))
			{
				// $$$ rob - date is always stored with time now, so always apply tz unless store_as_local set
				// or if we are defaulting to today
				$showLocale = ($params->get('date_defaulttotoday', 0) && JRequest::getInt('rowid') == 0 || $params->get('date_alwaystoday', false));
				if (!$store_as_local || $showLocale)
				{
					$oDate->setTimeZone($timeZone);
				}
			}
			//get the formatted date
			$local = true;//$store_as_local;
			$date = $oDate->toFormat($format, true);
			$this->offsetDate = $oDate->toSql(true);
			if (!$this->editable)
			{
				$time = ($params->get('date_showtime', 0)) ? ' ' .$oDate->toFormat($timeformat, true) : '';
				return $date.$time;
			}

			//get the formatted time
			if ($params->get('date_showtime', 0))
			{
				$time = $oDate->toFormat($timeformat, true);
			}
		}
		else
		{
			if (!$this->editable)
			{
				return '';
			}
			$date = '';
			$time = '';
		}
		$this->formattedDate = $date;
		// $$$ hugh - OK, I am, as usual, confused.  We can't hand calendar() a date formatted in the
		// form/table format.
		// $$$rob - its because the calendar js code takes the formatted value in the field and the $format and builds its date objects from
		// the two.
		$str[] = $this->calendar($date, $name, $id . '_cal', $format, $calopts, $repeatCounter);
		if ($params->get('date_showtime', 0) && !$element->hidden)
		{
			$timelength = strlen($timeformat);
			FabrikHelperHTML::addPath(COM_FABRIK_BASE.'plugins/fabrik_element/date/images/', 'image', 'form', false);
			$str[] = '<input class="inputbox fabrikinput timeField" ' . $readonly . ' size="' . $timelength . '" value="' . $time . '" name="' . $timeElName . '" />';
			$str[] = FabrikHelperHTML::image('time.png', 'form', @$this->tmpl, array('alt' => JText::_('PLG_ELEMENT_DATE_TIME'), 'class' => 'timeButton'));
		}
		$str[] = '</div>';
		return implode("\n", $str);
	}

	/**
	 * @param	string	$val
	 * @return	string	mySQL formatted date
	 */

	private function _indStoreDBFormat($val)
	{
		// $$$ hugh - sometimes still getting $val as an array with date and time,
		// like on AJAX submissions?  Or maybe from getEmailData()?  Or both?
		if (is_array($val))
		{
			// $$$ rob do url decode on time as if its passed from ajax save the : is in format %3C or something
			//$val = $val['date'].' '.$this->_fixTime(urldecode($val['time']));
			// $$$ rob 'date' should contain the time
			$val = JArrayHelper::getValue($val, 'date', '');
		}
		else
		{
			$val = urldecode($val);
		}

		if (in_array(trim($val), $this->getNullDates()))
		{
			return '';
		}
		jimport('joomla.utilities.date');
		$params = $this->getParams();
		$store_as_local = (bool) $params->get('date_store_as_local', false);

		$listModel = $this->getListModel();
		// $$$ hugh - offset_tz of 1 means 'in MySQL format, GMT'
		// $$$ hugh - offset_tz of 2 means 'in MySQL format, Local TZ'
		if ($listModel->importingCSV && $params->get('date_csv_offset_tz', '0') == '1')
		{
			return $val;
		}
		else if ($listModel->importingCSV && $params->get('date_csv_offset_tz', '0') == '2')
		{
			return $this->toMySQLGMT(JFactory::getDate($val));
		}

		//$$$ rob - as the date js code formats to the db format - just return the value.
		$timeZone = new DateTimeZone(JFactory::getConfig()->get('offset'));
		$val = JFactory::getDate($val, $timeZone)->toSql($store_as_local);
		return $val;
	}

	/**
	 * reset the date to GMT - inversing the offset
	 * @param	object	date
	 * @return	string	mysql formatted date
	 */

	function toMySQLGMT($date)
	{
		if ($this->_resetToGMT)
		{
			// $$$ rob 3.0 offset is no longer an integer but a timezone string
			$timeZone = new DateTimeZone(JFactory::getConfig()->get('offset'));
			$hours = $timeZone->getOffset($date) / (60 * 60);
			$invert = false;
			if ($hours < 0)
			{
				$invert = true;
				$hours = $hours * -1; //intervals can only be positive - set invert propery
			}
			// 5.3 only
			if (class_exists('DateInterval'))
			{
				$dateInterval = new DateInterval('PT'.$hours.'H');
				$dateInterval->invert = $invert;
				$date->sub($dateInterval);
			}
			else
			{
				$date->modify('+' . $hours . ' hour');
			}
			return $date->toSql(true);
		}
		return $date->toSql();
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
		if ($groupModel->isJoin() && is_array($val))
		{
			// $$$ rob 23/01/2012 - $val 'date' should already contain time
			/* if (JArrayHelper::getValue($val, 'time') !== '') {
			$val['time'] = $this->_fixTime(urldecode($val['time']));
			}
			$val = implode(" ", $val);
			*/
			$val = JArrayHelper::getValue($val, 'date', '');
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
	 * @param	mixed	element's raw data
	 * @param	array	form records data
	 * @param	int		repeat group counter
	 * @return	string	formatted value
	 */

	function getEmailValue($value, $data = array(), $repeatCounter = 0)
	{
		if ((is_array($value) && empty($value)) || (!is_array($value) && trim($value) == ''))
		{
			return '';
		}
		$groupModel = $this->getGroup();
		if ($groupModel->isJoin() && $groupModel->canRepeat())
		{
			$value = $value[$repeatCounter];
		}
		if (is_array($value))
		{
			$date = JArrayHelper::getValue($value, 'date');
			$d = JFactory::getDate($date);
			$time = JArrayHelper::getValue($value, 'time', '');
			if ($time !== '')
			{
				list($h, $m, $s) = explode(':', $time);
				$d->setTime($h, $m, $s);
			}
			$value = $d->toSql();
		}
		// $$$ hugh - need to convert to database format so we GMT-ified date
		return $this->renderListData($value, new stdClass());
		// $$$ rob - no need to covert to db format now as its posted as db format already.
		//return $this->renderListData($this->storeDatabaseFormat($value, $data), new stdClass());
	}

	/**
	 * $$$ hugh - added 9/13/2009
	 * determines the label used for the browser title
	 * in the form/detail views
	 * @param	array	data
	 * @param	int		when repeating joinded groups we need to know what part of the array to access
	 * @param	array	options
	 * @return	string	default value
	 */

	function getTitlePart($data, $repeatCounter = 0, $opts = array())
	{
		$gmt_date = $this->getValue($data, $repeatCounter, $opts);
		// OK, now we've got the GMT date, convert it
		// ripped the following off from renderListData ... SURELY we must have a func
		// somewhere that does this?
		$params = $this->getParams();
		$store_as_local = (int) $params->get('date_store_as_local', 0);
		$timeZone = new DateTimeZone(JFactory::getConfig()->get('offset'));
		$aNullDates = $this->getNullDates();
		$f = $params->get('date_table_format', '%Y-%m-%d');
		if ($f == 'Y-m-d')
		{
			$f = '%Y-%m-%d';
		}
		$tz_date = '';
		if (!in_array($gmt_date, $aNullDates))
		{
			$date = JFactory::getDate($gmt_date);
			if (!$store_as_local)
			{
				$date->setTimeZone($timeZone);
			}
			if ($f == '{age}')
			{
				$tz_date = date('Y') - $date->format('Y', true);
			}
			else
			{
				$tz_date = $date->toFormat($f, true);
			}
		}
		return $tz_date;
	}

	/**
	 * takes a raw value and returns its label equivalent
	 * @param	string	value
	 */

	protected function toLabel(&$v)
	{
		$params = $this->getParams();
		$store_as_local = (int) $params->get('date_store_as_local', 0);
		$f = $params->get('date_table_format', '%Y-%m-%d');
		$timeZone = new DateTimeZone(JFactory::getConfig()->get('offset'));
		$aNullDates = $this->getNullDates();
		$format = array();
		if (!in_array($v, $aNullDates))
		{
			$date = JFactory::getDate($v);
			//$$$ rob - if not time selector then the date gets stored as 2009-11-13 00:00:00
			//if we have a -1 timezone then date gets set to 2009-11-12 23:00:00
			//then shown as 2009-11-12 which is wrong
			if ($params->get('date_showtime'))
			{
				$date->setTimeZone($timeZone);
			}
			if ($f == '{age}')
			{
				$v = date('Y') - $date->format('Y', true);
			}
			else
			{
				$v = $date->toFormat($f, true);
			}
		}
		else
		{
			$v = '';
		}
	}

	/**
	 * ensure the time is in a full length format
	 *
	 * @param	string	$time
	 * @return	formatted	time
	 */

	protected function _fixTime($time)
	{
		//if its 5:00 rather than 05:00
		if (!preg_match("/^[0-9]{2}/", $time))
		{
			$time = "0".$time;
		}
		//if no seconds
		if (preg_match("/[0-9]{2}:[0-9]{2}/", $time) && strlen($time) <= 5)
		{
			$time .= ":00";
		}
		//if it doesnt match reset it to 0
		if (!preg_match("/[0-9]{2}:[0-9]{2}:[0-9]{2}/", $time))
		{
			$time = "00:00:00";
		}
		return $time;
	}

	/**
	 * Displays a calendar control field
	 *
	 * hacked from behaviour as you need to check if the element exists
	 * it might not as you could be using a custom template
	 * @param	string	The date value (must be in the same format as supplied by $format)
	 * @param	string	The name of the text field
	 * @param	string	The id of the text field
	 * @param	string	The date format
	 * @param	array	Additional html attributes
	 * @param	int		repeat group counter
	 */

	function calendar($value, $name, $id, $format = '%Y-%m-%d', $attribs = null, $repeatCounter = 0)
	{
		FabrikHelperHTML::loadcalendar();
		if (is_array($attribs))
		{
			$attribs = JArrayHelper::toString($attribs);
		}
		$paths = FabrikHelperHTML::addPath(COM_FABRIK_BASE . 'media/system/images/', 'image', 'form', false);
		$img = FabrikHelperHTML::image('calendar.png', 'form', @$this->tmpl, array('alt' => 'calendar', 'class' => 'calendarbutton', 'id' => $id . '_cal_img'));
		return '<input type="text" name="' . $name . '" id="' . $id . '" value="'.htmlspecialchars($value, ENT_COMPAT, 'UTF-8').'" ' . $attribs . ' />' . $img;
	}

	/**
	 * get the options used for the date elements calendar
	 * @param $int repeat counter
	 * @return object ready for js encoding
	 */

	protected function _CalendarJSOpts($id)
	{
		$params = $this->getParams();
		$opts = new stdClass();
		$opts->inputField = $id;
		$opts->ifFormat = $params->get('date_form_format');
		$opts->button = $id."_cal_img";
		$opts->align = "Tl";
		$opts->singleClick = true;
		$opts->firstDay = intval($params->get('date_firstday'));
		$validations = $this->getValidations();
		$opts->ifFormat = $params->get('date_form_format', $params->get('date_table_format', '%Y-%m-%d'));
		$opts->hasValidations = empty($validations) ? false : true;
		$opts->dateAllowFunc = $params->get('date_allow_func');

		//test
		$opts->range = array(1066, 2999);
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
		$opts->defaultVal = $this->offsetDate;
		$opts->showtime = (!$element->hidden && $params->get('date_showtime', 0)) ? true : false;
		$opts->timelabel = JText::_('time');
		$opts->typing = $params->get('date_allow_typing_in_field', true);
		$opts->timedisplay = $params->get('date_timedisplay', 1);
		$validations = $this->getValidations();
		$opts->validations = empty($validations) ? false : true;
		$opts->dateTimeFormat = $params->get('date_time_format', '');
		//for reuse if element is duplicated in repeat group
		$opts->calendarSetup = $this->_CalendarJSOpts($id);
		$opts->advanced = $params->get('date_advanced', '0') == '1';
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
		if (!isset($this->default))
		{
			$params = $this->getParams();
			$element = $this->getElement();
			$timeZone = new DateTimeZone(JFactory::getConfig()->get('offset'));
			$store_as_local = (int) $params->get('date_store_as_local', 0);
			if ($params->get('date_defaulttotoday', 0))
			{
				if ($store_as_local)
				{
					$localDate = date('Y-m-d H:i:s');
					$oTmpDate = JFactory::getDate(strtotime($localDate));
				}
				else
				{
					$oTmpDate = JFactory::getDate();
				}
				$default = $oTmpDate->toSql();
			}
			else
			{
				// deafult date should always be entered as gmt date e.g. eval'd default of:
				$default = $element->default;
				if ($element->eval == "1")
				{
					$default = @eval(stripslashes($default));
					FabrikWorker::logEval($default, 'Caught exception on eval in '.$element->name.'::getDefaultValue() : %s');
				}
				if (trim($default) != '')
				{
					$oTmpDate = JFactory::getDate($default);
					$default = $oTmpDate->toSql();
				}
			}
			$this->default = $default;
		}
		return $this->default;
	}

	/**
	 * determines the value for the element in the form view
	 * @param array data
	 * @param int when repeating joinded groups we need to know what part of the array to access
	 * @param array options
	 * @return string default date value in *** GMT time ****
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
			$store_as_local = (int) $params->get('date_store_as_local', 0);
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
					//'date' should now contain the time, as we include in on js onsubmit() method
					$value = JArrayHelper::getValue($value, 'date', JArrayHelper::getValue($value, 0));
					/*
					 //TIMEDATE option set - explode with space rather than comma
					//url decode if it comes from ajax calendar form

					if (array_key_exists('time', $value) && $value['time'] != '' && JArrayHelper::getValue($value, 'date') != '') {
					$value['time'] = $this->_fixTime(urldecode($value['time']));
					$value = implode(' ', $value);
					}
					else {
					//$value = '';
					$value = implode('', $value); //for validations in repeat groups with no time selector
					} */
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
	 * @param	string	submitted form value
	 * @return	string	formated value
	 */

	public function toDbVal($str)
	{
		//only format if not empty otherwise search forms will filter
		//for todays date even when no date entered
		$this->_resetToGMT = false;
		if ($str != '')
		{
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
	 * @param	string	initial $value all filters should submit as sql format EXCEPT for special string in search all (e.g. 'last week');
	 * @param	string	intial $condition
	 * @param	string	eval - how the value should be handled
	 * @return	array	(value condition) values in sql format
	 */

	function getFilterValue($value, $condition, $eval)
	{
		// if its a search all value it may not be a date - so use parent method.
		// see http://fabrikar.com/forums/showthread.php?t=25255
		if (!FabrikWorker::isDate($value))
		{
			if (($this->rangeFilterSet))
			{
				// its alreay been set as a range expression - so split that into an array
				$condition = 'between';
				$value = explode(' AND ', $value);
				foreach ($value as &$v)
				{
					$v = str_replace(array("'", '"'), '', $v);
				}
			}
			return parent::getFilterValue($value, $condition, FABRIKFILTER_QUERY);
		}
		$params = $this->getParams();
		$store_as_local = (int) $params->get('date_store_as_local', 0);
		if (!$params->get('date_showtime', 0) || $store_as_local)
		{
			$this->_resetToGMT = false;
		}

		$exactTime = $this->formatContainsTime($params->get('date_table_format'));
		// $$$ rob if filtering in querystring and ranged value set then force filter type to range
		$filterType = is_array($value) ? 'range' : $this->getElement()->filter_type;
		switch ($filterType)
		{
			case 'range':
				// ranged dates should be sent in sql format
				break;
			case 'field':
			case 'dropdown':
			case 'auto-complete':
			default:
				//odity when filtering from qs
				$value = str_replace("'", '', $value);

				// parse through JDate, to allow for special filters such as 'now' 'tomorrow' etc

				// for searches on simply the year - JDate will presume its a timestamp and mung the results
				// so we have to use this specific format string to get now and next
				if (is_numeric($value) && strlen($value) == 4)
				{
					// will only work on php 5.3.6
					$value = JFactory::getDate('first day of January ' . $value)->toSql();
					$next = JFactory::getDate('first day of January ' . ($value + 1));
				}
				elseif ($this->isMonth($value))
				{
					$value = JFactory::getDate('first day of ' . $this->untranslateMonth($value))->toSql();
					$next = JFactory::getDate('last day of ' . $this->untranslateMonth($value))->setTime(23, 59, 59);
				}
				elseif (trim(strtolower($value)) === 'last week')
				{
					$value = JFactory::getDate('last week')->toSql();
					$next = JFactory::getDate();
				}
				elseif (trim(strtolower($value)) === 'last month')
				{
					$value = JFactory::getDate('last month')->toSql();
					$next = JFactory::getDate();
				}
				elseif (trim(strtolower($value)) === 'last year')
				{
					$value = JFactory::getDate('last year')->toSql();
					$next = JFactory::getDate();
				}
				elseif (trim(strtolower($value)) === 'next week')
				{
					$value = JFactory::getDate()->toSql();
					$next = JFactory::getDate('next week');
				}
				elseif (trim(strtolower($value)) === 'next month')
				{
					$value = JFactory::getDate()->toSql();
					$next = JFactory::getDate('next month');
				}
				elseif (trim(strtolower($value)) === 'next year')
				{
					$value = JFactory::getDate()->toSql();
					$next = JFactory::getDate('next year');
				}
				else
				{
					$value = JFactory::getDate($value)->toSql();
					// $$$ hugh - strip time if not needed.  Specific case is element filter,
					// first time submitting filter from list, will have arbitrary "now" time.
					// Dunno if this will break anything else!
					if (!$exactTime)
					{
						$value = $this->setMySQLTimeToZero($value);
					}
					$next = JFactory::getDate(strtotime($this->addDays($value, 1)) - 1);
					// $$$ now we need to reset $value to GMT.
					// Probably need to take $store_as_local into account here?
					$this->_resetToGMT = true;
					$value = $this->toMySQLGMT(JFactory::getDate($value));
					$this->_resetToGMT = false;
				}

				// only set to a range if condition is matching (so dont set to range for < or > conditions)
				if ($condition == 'contains' || $condition == '=' || $condition == 'REGEXP')
				{
					if (!$params->get('date_showtime', 0) || $exactTime == false)
					{
						//$$$ rob turn into a ranged filter to search the entire day
						// values should be in sql format
						$value = (array) $value;
						$condition = 'BETWEEN';
						$value[1] = $next->toSql();
						// set a flat to stop getRangedFilterValue from adding an additional day to end value
						$this->rangeFilterSet = true;
					}
				}
				break;
		}
		$this->_resetToGMT = true;
		$value = parent::getFilterValue($value, $condition, $eval);
		return $value;
	}

	/**
	 * is a string a month?
	 * @param	string  $test
	 * @return	bool
	 */
	protected function isMonth($test)
	{
		$months = array(JText::_('JANUARY_SHORT'), JText::_('JANUARY'), JText::_('FEBRUARY_SHORT'), JText::_('FEBRUARY'),
		JText::_('MARCH_SHORT'), JText::_('MARCH'), JText::_('APRIL'), JText::_('APRIL_SHORT'), JText::_('MAY_SHORT'), JText::_('MAY'),
		JText::_('JUNE_SHORT'), JText::_('JUNE'), JText::_('JULY_SHORT'), JText::_('JULY'), JText::_('AUGUST_SHORT'), JText::_('AUGUST'),
		JText::_('SEPTEMBER_SHORT'), JText::_('SEPTEMBER'), JText::_('OCTOBER_SHORT'), JText::_('OCTOBER'), JText::_('NOVEMBER_SHORT'),
		JText::_('NOVEMBER'), JText::_('DECEMBER_SHORT'), JText::_('DECEMBER'));
		return in_array($test, $months);
	}

	/**
	 * return english name for translated month
	 * @param	string	$test
	 * @return string|boolean
	 */
	protected function untranslateMonth($test)
	{
		switch ($test)
		{
			case JText::_('JANUARY_SHORT'):
			case JText::_('JANUARY'):
				return 'January';
				break;
			case JText::_('FEBRUARY_SHORT'):
			case JText::_('FEBRUARY'):
				return 'February';
				break;
			case JText::_('MARCH_SHORT'):
			case JText::_('MARCH'):
				return 'March';
				break;
			case JText::_('APRIL_SHORT'):
			case JText::_('APRIL'):
				return 'April';
				break;
			case JText::_('MAY_SHORT'):
			case JText::_('MAY'):
				return 'May';
				break;
			case JText::_('JUNE_SHORT'):
			case JText::_('JUNE'):
				return 'June';
				break;
			case JText::_('JULY_SHORT'):
			case JText::_('JULY'):
				return 'July';
				break;
			case JText::_('AUGUST_SHORT'):
			case JText::_('AUGUST'):
				return 'August';
				break;
			case JText::_('SEPTEMBER_SHORT'):
			case JText::_('SEPTEMBER'):
				return 'September';
				break;
			case JText::_('OCTOBER_SHORT'):
			case JText::_('OCTOBER'):
				return 'October';
				break;
			case JText::_('NOVEMBER_SHORT'):
			case JText::_('NOVEMBER'):
				return 'November';
				break;
			case JText::_('DECEMBER_SHORT'):
			case JText::_('DECEMBER'):
				return 'December';
				break;
		}
		return false;
	}

	/**
	 * Get the list filter for the element
	 * @param int filter order
	 * @param bol do we render as a normal filter or as an advanced search filter
	 * if normal include the hidden fields as well (default true, use false for advanced filter rendering)
	 * @return string filter html
	 */

	public function getFilter($counter = 0, $normal = true)
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
		foreach ($joins as $aJoin)
		{
			// not sure why the group id key wasnt found - but put here to remove error
			if (array_key_exists('group_id', $aJoin))
			{
				if ($aJoin->group_id == $element->group_id && $aJoin->element_id == 0)
				{
					$fromTable = $aJoin->table_join;
					$joinStr = " LEFT JOIN $fromTable ON ".$aJoin->table_join.".".$aJoin->table_join_key." = ".$aJoin->join_from_table.".".$aJoin->table_key;
					$elName = str_replace($origTable.'.', $fromTable.'.', $elName);
				}
			}
		}
		$where = $listModel->buildQueryPrefilterWhere($this);
		$elName = FabrikString::safeColName($elName);

		//dont format here as the format string is different between mysql and php's calendar strftime
		$sql = "SELECT DISTINCT($elName) AS text, $elName AS value FROM `$origTable` $joinStr"
		. "\n WHERE $elName IN ('".implode("','", $ids)."')"
		. "\n AND TRIM($elName) <> '' $where GROUP BY text ASC";
		$requestName = $elName."___filter";
		if (array_key_exists($elName, $_REQUEST))
		{
			if (is_array($_REQUEST[$elName]) && array_key_exists('value', $_REQUEST[$elName]))
			{
				$_REQUEST[$requestName] = $_REQUEST[$elName]['value'];
			}
		}
		$htmlid = $this->getHTMLId();
		$timeZone = new DateTimeZone(JFactory::getConfig()->get('offset'));
		if (in_array($element->filter_type, array('dropdown')))
		{
			$rows = $this->filterValueList($normal);
		}
		$calOpts = $this->filterCalendarOpts();
		$return = array();
		switch ($element->filter_type)
		{
			case "range":
				FabrikHelperHTML::loadcalendar();
				if (empty($default))
				{
					$default = array('', '');
				}
				else
				{
					$default[0] = JFactory::getDate($default[0])->toFormat($format);
					$default[1] = JFactory::getDate($default[1])->toFormat($format);
				}
				// add wrapper div for list filter toggeling
				$return[] = '<div class="fabrik_filter_container">';
				$return[] = JText::_('COM_FABRIK_DATE_RANGE_BETWEEN') .
				$this->calendar($default[0], $v . '[0]', $this->getFilterHtmlId(0), $format, $calOpts);
				$return[] = '<br />'.JText::_('COM_FABRIK_DATE_RANGE_AND') .
				$this->calendar($default[1], $v . '[1]', $this-> getFilterHtmlId(1), $format, $calOpts);
				$return[] = '</div>';
				break;

			case "dropdown":
				// cant do the format in the MySQL query as its not the same formatting
				// e.g. M in mysql is month and J's date code its minute
				jimport('joomla.utilities.date');
				$ddData = array();
				foreach ($rows as $k => $o)
				{
					if ($fabrikDb->getNullDate() === $o->text)
					{
						$o->text = '';
						$o->value = '';
					}
					else
					{
						$d = new FabDate($o->text);
						$o->value = $d->toSql();
						$o->text = $d->toFormat($format);
					}
					if (!array_key_exists($o->value, $ddData))
					{
						$ddData[$o->value] = $o;
					}
				}

				array_unshift($ddData, JHTML::_('select.option', '', $this->filterSelectLabel()));
				$return[] = JHTML::_('select.genericlist', $ddData, $v, 'class="inputbox fabrik_filter" size="1" maxlength="19"', 'value', 'text', $default, $htmlid . '0');
				break;
			default:
			case "field":
				FabrikHelperHTML::loadcalendar();
				if (is_array($default))
				{
					$default = array_shift($default);
				}
				if ($default !== '')
				{
					$default = JFactory::getDate($default)->toFormat($format);
				}
				// add wrapper div for list filter toggeling
				$return[] = '<div class="fabrik_filter_container">';
				$return[] = $this->calendar($default, $v, $this->getFilterHtmlId(0), $format, $calOpts);
				$return[] = '</div>';
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

				$autoId = '#' . $htmlid . '-auto-complete';
				if (!$normal) {
					$autoId = '.advanced-search-list .autocomplete-trigger';
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

	protected function getFilterHtmlId($range)
	{
		$counter = JRequest::getVar('counter', 0);
		return $this->getHTMLId() . '_filter_range_' . $range . '_' . JRequest::getVar('task') . '.' . $counter;
	}

	/**
	 * @since 3.0 takes a date from the server and applies the timezone offset
	 * probably not the right way to do this but ive been at it all day
	 * @param object FabDate
	 */

	protected function toLocalTime(&$d){
		$timeZone = new DateTimeZone(JFactory::getConfig()->get('offset'));
		$hours = $timeZone->getOffset($d) / (60 * 60);
		$dateInterval = new DateInterval('PT'.$hours.'H');
		$d->add($dateInterval);

	}

	public function onAutocomplete_options()
	{
		//needed for ajax update (since we are calling this method via dispatcher element is not set
		$this->id = JRequest::getInt('element_id');
		$this->getElement(true);
		$listModel = $this->getListModel();
		$table = $listModel->getTable();
		$db = $listModel->getDb();
		$name = $this->getFullName(false, false, false);
		$db->setQuery("SELECT DISTINCT($name) AS value, $name AS text FROM $table->db_table_name WHERE $name LIKE ".$db->quote('%'.addslashes(JRequest::getVar('value').'%')));
		$tmp = $db->loadObjectList();
		$ddData = array();
		foreach ($tmp as &$t)
		{
			$this->toLabel($t->text);
			if (!array_key_exists($t->text, $ddData))
			{
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
	 * @param	array	data
	 * @param	string	table column heading
	 * @param	bool	data is raw
	 */

	function prepareCSVData(&$data, $key, $is_raw = false)
	{
		if ($is_raw)
		{
			return;
		}
		$params = $this->getParams();
		$format = $params->get('date_form_format', '%Y-%m-%d %H:%S:%I');
		//go through data and turn any dates into unix timestamps
		for ($j = 0; $j < count($data); $j++)
		{
			$orig_data = $data[$j][$key];
			$date = JFactory::getDate($data[$j][$key]);
			$data[$j][$key] = $date->toFormat($format, true);
			// $$$ hugh - bit of a hack specific to a customer who needs to import dates with year as 1899,
			// which we then change to 1999 using a tablecsv import script (don't ask!). But of course FabDate doesn't
			// like dates outside of UNIX timestamp range, so the previous line was zapping them. So I'm just restoring
			// the date as found in the CSV file. This could have side effects if someone else tries to import invalid dates,
			// but ... ah well.
			if (empty($data[$j][$key]) && !empty($orig_data))
			{
				$data[$j][$key] = $orig_data;
			}
		}
	}

	/**
	 * Examples of where this would be overwritten include drop downs whos "please select" value might be "-1"
	 * @param	string	data posted from form to check
	 * @param	int		repeat group counter
	 * @return	bool	if data is considered empty then returns true
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
		// $$$ hugh - not any more, since we changed to always submit in MySQL format
		// $$$ hugh - removing the MySQL conversion has broken 'special' range handling,
		// which used to happen in the MySQL conversion function.  So ...
		// Created new helper funcion specialStrToMySQL() which turns things
		// like 'midnight yesterday' etc into MySQL dates, defaulting to GMT.
		// This lets us do ranged query string and content plugin filters like ...
		// table___date[value][]=midnight%20yesterday&table___date[value][]=midnight%20today&table___date[condition]=BETWEEN
		$value[0] = FabrikWorker::specialStrToMySQL(JArrayHelper::getValue($value, 0));
		$value[1] = FabrikWorker::specialStrToMySQL(JArrayHelper::getValue($value, 1));
		// $$$ hugh - if the first date is later than the second, swap 'em round
		// to keep 'BETWEEN' in the query happy
		if (strtotime($value[0]) > strtotime($value[1]))
		{
			$tmp_value = $value[0];
			$value[0] = $value[1];
			$value[1] = $tmp_value;
		}

		$exactTime = $this->formatContainsTime($params->get('date_table_format'));
		if (!$params->get('date_showtime', 0) || $exactTime == false)
		{
			// range values could already have been set in getFilterValue
			if (!$this->rangeFilterSet)
			{

				// $$$ hugh - need to back this out by one second, otherwise we're including next day.
				// So ... say we are searching from '2009-07-17' to '2009-07-21', the
				// addDays(1) changes '2009-07-21 00:00:00' to '2009-07-22 00:00:00',
				// but what we really want is '2009-07-21 23:59:59'

				$value[1] = date("Y-m-d H:i:s", strtotime($this->addDays($value[1], 1)) - 1);
			}

		}
		$value = $db->quote($value[0]) . ' AND ' . $db->quote($value[1]);
		$condition = 'BETWEEN';
		return array($value, $condition);
	}

	/**
	 * convert a table formatted date string into a mySQL formatted date string
	 * (if already in mySQL format returns the date)
	 * @param	string	date in table view format
	 * @return	string	date in mySQL format or false if string date could not be converted
	 */

	function tableDateToMySQL($v)
	{
		$params = $this->getParams();
		$store_as_local = (int) $params->get('date_store_as_local', 0);
		$format = $params->get('date_table_format', '%Y-%m-%d');
		$b = FabrikWorker::strToDateTime($v, $format);
		if (!is_array($b))
		{
			return false;
		}
		//3.0 can't use timestamp as that gets offset as its taken as numeric by FabDate
		//$orig = new FabDate($datebits['timestamp'], 2);
		$bstr = $b['year'] . '-' . $b['mon'] . '-' . $b['day'] . ' ' . $b['hour'] . ':' . $b['min'] . ':' . $b['sec'];
		$date = JFactory::getDate($bstr);
		if (in_array($v, $this->getNullDates()) || $v === $date->toMySQL())
		{
			return $v;
		}
		if ($store_as_local)
		{
			$this->_resetToGMT = false;
		}
		$retval = $this->toMySQLGMT($date);
		$this->_resetToGMT = true;
		return $retval;
	}

	/**
	 * $$$ rob - not used??? 9/11/2010
	 * set a dates time to 00:00:00
	 * @param	mixed	$time The initial time for the FabDate object
	 * @return	string	mysql formatted date
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
	* simple minded method to set a MySQL formatted date's time to 00:00:00
	* @param	string	$date in MySQL format
	* @return	string	mysql formatted date with time set to 0
	*/

	function setMySQLTimeToZero($date)
	{
		$date_array = explode(' ', $date);
		$date_array[1] = '00:00:00';
		return implode(' ', $date_array);
	}

	/**
	 * add days to a date
	 * @param	mixed	$time The initial time for the FabDate object
	 * @param	integer	number of days to add (negtive to remove days)
	 * @return	string	mysql formatted date
	 */

	function addDays($date, $add = 0)
	{
		$date = JFactory::getDate($date);
		$thePHPDate = getdate($date->toUnix());
		$thePHPDate['mday'] = $thePHPDate['mday']+$add;
		$v = mktime($thePHPDate['hours'], $thePHPDate['minutes'], $thePHPDate['seconds'], $thePHPDate['mon'], $thePHPDate['mday'], $thePHPDate['year']);
		$date = JFactory::getDate($v);
		return $date->toSql($v);
	}

	/**
	 * add hours to a date
	 * @param	mixed	$time The initial time for the FabDate object
	 * @param	integer	number of days to add (negtive to remove days)
	 * @return	string	mysql formatted date
	 */

	function addHours($date, $add = 0)
	{
		$date = JFactory::getDate($date);
		$thePHPDate = getdate($date->toUnix());
		if ($thePHPDate['hours'] + $add >= 24)
		{
			$thePHPDate['hours'] = 0;
			$thePHPDate['mday'] ++;
		}
		else if ($thePHPDate['hours'] + $add < 0)
		{
			$thePHPDate['hours'] = 0;
			$thePHPDate['mday'] --;
		}
		else
		{
			$thePHPDate['hours'] = $thePHPDate['hours'] + $add;
		}
		$v = mktime($thePHPDate['hours'], $thePHPDate['minutes'], $thePHPDate['seconds'], $thePHPDate['mon'], $thePHPDate['mday'], $thePHPDate['year']);
		$date = JFactory::getDate($v);
		return $date->toMySQL($v);
	}

	/**
	 * build the query for the avg caclculation
	 * @param	model	$listModel
	 * @param	string	$label the label to apply to each avg
	 * @return	string	sql statement
	 */

	protected function getAvgQuery(&$listModel, $label = "'calc'")
	{
		$table = $listModel->getTable();
		$db = $listModel->getDb();
		$joinSQL = $listModel->buildQueryJoin();
		$whereSQL = $listModel->buildQueryWhere();
		$name = $this->getFullName(false, false, false);
		return 'SELECT FROM_UNIXTIME(AVG(UNIX_TIMESTAMP(' . $name . '))) AS value, ' . $label . ' AS label FROM ' . $db->quoteName($table->db_table_name) . ' ' . $joinSQL . ' ' . $whereSQL;
	}

	protected function getSumQuery(&$listModel, $label = "'calc'")
	{
		$table = $listModel->getTable();
		$db = $listModel->getDb();
		$joinSQL = $listModel->buildQueryJoin();
		$whereSQL = $listModel->buildQueryWhere();
		$name 	= $this->getFullName(false, false, false);
		//$$$rob not actaully likely to work due to the query easily exceeding mySQL's TIMESTAMP_MAX_VALUE value but the query in itself is correct
		return 'SELECT FROM_UNIXTIME(SUM(UNIX_TIMESTAMP(' . $name . '))) AS value, ' . $label . ' AS label FROM ' . $db->quoteName($table->db_table_name) . ' ' . $joinSQL . ' ' . $whereSQL;
	}

	public function simpleAvg($data)
	{
		$avg = $this->simpleSum($data) / count($data);
		return JFactory::getDate($avg)->toMySQL();
	}

	/**
	 * find the sum from a set of data
	 * can be overwritten in plugin - see date for example of averaging dates
	 * @param	array	$data to sum
	 * @return	string	sum result
	 */

	public function simpleSum($data)
	{
		$sum = 0;
		foreach ($data as $d)
		{
			$date = JFactory::getDate($d);
			$sum += $date->toUnix();
		}
		return $sum;
	}

	/**
	 * takes date's time value and turns it into seconds
	 * @param	date	object $date
	 * @return	int		seconds
	 */

	protected function toSeconds($date)
	{
		return (int)($date->toFormat('%H') * 60 * 60) + (int) ($date->toFormat('%M') * 60) + (int) $date->toFormat('%S');
	}

	/**
	 * takes strftime time formatting - http://fr.php.net/manual/en/function.strftime.php
	 * and converts to format used in mySQL DATE_FORMAT http://dev.mysql.com/doc/refman/5.1/en/date-and-time-functions.html
	 * @param	string	format
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
			case 'today':
				$query = " ($key >= CURDATE() and $key < CURDATE() + INTERVAL 1 DAY) ";
				break;
			case 'yesterday':
				$query = " ($key >= CURDATE() - INTERVAL 1 DAY and $key < CURDATE()) ";
				break;
			case 'tomorrow':
				$query = " ($key >= CURDATE() + INTERVAL 1 DAY  and $key < CURDATE() + INTERVAL 2 DAY ) ";
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
			$store_as_local = (int) $params->get('date_store_as_local', 0);
			if (!$store_as_local) {
				$date = JFactory::getDate($val);
				$timeZone = new DateTimeZone(JFactory::getConfig()->get('offset'));
				$date->setTimeZone($timeZone);
				$val = $date->toSql(true);
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
		/*
		if ($cond == '>') {
			return $data > $compare;
		} else {
			return $data < $compare;
		}
		*/
		return parent::greaterOrLessThan($data, $cond, $compare);
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

	public function fromXMLFormat($v)
	{
		return JFactory::getDate($v)->toSql();
	}

	/**
	 * if used as a filter add in some JS code to watch observed filter element's changes
	 * when it changes update the contents of this elements dd filter's options
	 * @param bol is the filter a normal (true) or advanced filter
	 * @param string container
	 */

	public function filterJS($normal, $container)
	{

		$element = $this->getElement();
		if ($normal && ($element->filter_type !== 'field' && $element->filter_type !== 'range'))
		{
			return;
		}
		$htmlid = $this->getHTMLId();
		$params = $this->getParams();
		$id = $this->getFilterHtmlId(0);
		$id2 =$this->getFilterHtmlId(1);

		$opts = $this->_CalendarJSOpts($id);

		$opts->calendarSetup->ifFormat = $params->get('date_table_format', '%Y-%m-%d');
		$opts->type = $element->filter_type;
		$opts->ids = $element->filter_type == 'field' ? array($id) : array($id, $id2);
		$opts->buttons = $element->filter_type == 'field' ? array($id . '_cal_img') : array($id . '_cal_img', $id2 . '_cal_img');
		$opts = json_encode($opts);

		$script = 'Fabrik.filter_' . $container . '.addFilter(\'' . $element->plugin . '\', new DateFilter(' . $opts . '));' . "\n";
		if ($normal)
		{
			FabrikHelperHTML::script('plugins/fabrik_element/date/filter.js');
			return $script;
		}
		else
		{
			FabrikHelperHTML::script('plugins/fabrik_element/date/filter.js', $script);
		}
	}

	protected function filterCalendarOpts()
	{
		$params = $this->getParams();
		$calOpts = array('class' => 'inputbox fabrik_filter', 'maxlength' => '19', 'size' => 16);
		if ($params->get('date_allow_typing_in_field', true) == false)
		{
			$calopts['readonly'] = 'readonly';
		}
		return $calOpts;
	}

	/**
	* @param array of scripts previously loaded (load order is important as we are loading via head.js
	* and in ie these load async. So if you this class extends another you need to insert its location in $srcs above the
	* current file
	*
	* get the class to manage the form element
	* if a plugin class requires to load another elements class (eg user for dbjoin then it should
	* call FabrikModelElement::formJavascriptClass('plugins/fabrik_element/databasejoin/databasejoin.js', true);
	* to ensure that the file is loaded only once
	*/

	function formJavascriptClass(&$srcs, $script = '')
	{
		$prefix = JDEBUG ? '' : '-min';
		$params = $this->getParams();
		if ($params->get('date_advanced', '0') == '1')
		{
			if (empty($prefix))
			{
				parent::formJavascriptClass($srcs, 'media/com_fabrik/js/lib/datejs/date' . $prefix . '.js');
				parent::formJavascriptClass($srcs, 'media/com_fabrik/js/lib/datejs/core' . $prefix . '.js');
				parent::formJavascriptClass($srcs, 'media/com_fabrik/js/lib/datejs/parser' . $prefix . '.js');
				parent::formJavascriptClass($srcs, 'media/com_fabrik/js/lib/datejs/extras' . $prefix . '.js');
			}
			else
			{
				parent::formJavascriptClass($srcs, 'media/com_fabrik/js/lib/datejs/date.js');
				parent::formJavascriptClass($srcs, 'media/com_fabrik/js/lib/datejs/extras.js');
			}
		}
		parent::formJavascriptClass($srcs);
		// return false, as we need to be called on per-element (not per-plugin) basis
		return false;
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
		$orig = $date;
		$date = $this->stripDays($date);
		//not sure if this one needed?
		//	$date = $this->monthToInt($date);
		$date = $this->removeDashes($date);
		try {
			$dt = new DateTime($date);
		}
		catch(Exception $e) {
			JError::raiseNotice(500, 'date format unknown for ' . $orig . ' replacing with todays date');
			$date = 'now';
			// catches 'Failed to parse time string (ublingah!) at position 0 (u)' exception.
			// don't use this object
		}
		// Create the base GMT and server time zone objects.
		if (empty(self::$gmt) || empty(self::$stz)) {
			self::$gmt = new DateTimeZone('GMT');
			self::$stz = new DateTimeZone(@date_default_timezone_get());
		}
		parent::__construct($date, $tz);
	}

	protected function removeDashes($str)
	{
		$str = FabrikString::ltrimword($str, '-');
		return $str;
	}

	protected function monthToInt($str)
	{
		$abbrs = array(true, false);
		for ($a = 0; $a < count($abbrs); $a ++ ) {
			for ($i = 0; $i < 13; $i ++) {
				$month = $this->monthToString($i, $abbrs[$a]);
				if (stristr($str, $month)) {
					$monthNum = strlen($i) === 1 ? '0'.$i : $i;
					$str = str_ireplace($month, $monthNum, $str);
				}
			}
		}
		return $str;
	}

	protected function stripDays($str)
	{
		$abbrs = array(true, false);
		for ($a = 0; $a < count($abbrs); $a ++ ) {
			for ($i = 0; $i < 7; $i ++) {
				$day = $this->dayToString($i, $abbrs[$a]);
				//echo "day = $day <br>";
				if (stristr($str, $day)) {
					$str = str_ireplace($day, '', $str);
				}
			}
		}
		return $str;
	}

}
?>