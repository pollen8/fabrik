<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * compat with php < 5.1
 */
if (!function_exists('htmlspecialchars_decode') )
{
	function htmlspecialchars_decode($text)
	{
		return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
	}
}

if (!function_exists('array_combine'))
{
	function array_combine($arr1,$arr2) {
		$out = array();
		foreach ($arr1 as $key1 => $value1) {
			$out[$value1] = $arr2[$key1];
		}
		return $out;
	}
}



/*
 * generic tools that all models use
 * This code used to be in models/parent.php
 */

class FabrikWorker {

	public static $database = null;

	protected $finalformat = null;

	/** @var string image file extensions */
	protected $_image_extensions_eregi = 'bmp|gif|jpg|jpeg|png';

	/** @var string audio file extensions */
	protected $_audio_extensions_eregi = 'mp3';

	static protected $_audio_mime_types = array(
		'mp3' => 'audio/x-mpeg'
		);

		/**
		 * returns true if $file has an image extension type
		 * @param string filename
		 * @return bool
		 */
		function isImageExtension($file)
		{
			$path_parts = pathinfo($file);
			return preg_match('/'.self::$_image_extensions_eregi.'/i', $path_parts['extension']);
		}

		/**
		 * returns true if $file has an image extension type
		 * @param string filename
		 * @return bool
		 */
		function isAudioExtension($file)
		{
			$path_parts = pathinfo($file);
			return preg_match('/'.self::$_audio_extensions_eregi.'/i', $path_parts['extension']);
		}

		function getAudioMimeType($file)
		{
			$path_parts = pathinfo($file);
			if (array_key_exists($path_parts['extension'], self::$_audio_mime_types)) {
				return self::$_audio_mime_types[$path_parts['extension']];
			}
			return false;
		}

		/**
		 * format a string to datetime
		 *
		 * http://fr.php.net/strftime
		 * (use as strptime)
		 *
		 * @param string $date
		 * @param string $format
		 * @return array date info
		 */

		function strToDateTime($date, $format)
		{

			$weekdays = array(
			'Sun' => '0',
			'Mon' => '1',
			'Tue' => '2',
			'Wed' => '3',
			'Thu' => '4',
			'Fri' => '5',
			'Sat' => '6'
			);
			$months = array(
			'Jan' => '01',
			'Feb' => '02',
			'Mar' => '03',
			'Apr' => '04',
			'May' => '05',
			'Jun' => '06',
			'Jul' => '07',
			'Aug' => '08',
			'Sep' => '09',
			'Oct' => '10',
			'Nov' => '11',
			'Dec' => '12'
			);
			if (!($date = FabrikWorker::str2Time($date, $format))) {
				return;
			}
			$months 				= array(JText::_('January'), JText::_('February'), JText::_('March'), JText::_('April'), JText::_('May'), JText::_('June'), JText::_('July'), JText::_('August'), JText::_('September'), JText::_('October'), JText::_('November'), JText::_('December'));
			$shortMonths 		= array(JText::_('Jan'), JText::_('Feb'), JText::_('Mar'), JText::_('Apr'), JText::_('May'), JText::_('Jun'), JText::_('Jul'), JText::_('Aug'), JText::_('Sept'), JText::_('Oct'), JText::_('Nov'), JText::_('Dec'));

			//$$$ rob set day default to 1, so that if you have a date format string of %m-%Y the day is set to the first day of the month
			// and not the last day of the previous month (which is what a 0 here would represent)
			$dateTime = array('sec' => 0, 'min' => 0, 'hour' => 0, 'day' => 1, 'mon' => 0, 'year' => 0, 'timestamp' => 0);
			foreach ($date as $key => $val) {
				switch($key) {
					case 'd':
					case 'e':
					case 'j':
						$dateTime['day'] = intval($val);
						break;
					case 'D':
						$dateTime['day'] = intval($weekdays[$val]);
						break;
					case 'm':
					case 'n':
						$dateTime['mon'] = intval($val);
						break;
					case 'b':
						$dateTime['mon'] = $shortMonths[$val] + 1;
						break;
					case 'Y':
						$dateTime['year'] = intval($val);
						break;
					case 'y':
						$dateTime['year'] = intval($val)+2000;
						break;
					case 'G':
					case 'g':
					case 'H':
					case 'h':
						$dateTime['hour'] = intval($val);
						break;
					case 'M':
						$dateTime['min'] = intval($val);
						break;
					case 'i':
						$dateTime['min'] = intval($val);
						break;
					case 's':
					case 'S':
						$dateTime['sec'] = intval($val);
						break;
				}

			}
			$dateTime['timestamp'] = mktime($dateTime['hour'], $dateTime['min'], $dateTime['sec'], $dateTime['mon'], $dateTime['day'], $dateTime['year']);
			return $dateTime;
		}

		//@TODO: use JDate:_strftime() to translate correctly

		/**
		 *
		 * @param string date representation
		 * @param string format that the date should be in
		 * @return array date bits keyed on date representations e.g.  m/d/Y
		 */
		function str2Time($date, $format)
		{
			static $finalformat;
			/**
			 * lets check if we have some special text as per :
			 * http://php.net/strtotime - this means we can use "+2 week" as a url filter
			 * do this before we urldecode the date otherwise the + is replaced with ' ';
			 */

			$matches = array();
			$matches2 = array();
			preg_match("/now/i", $date, $matches); //eg now
			preg_match("/[+|-][0-9]* (week\b|year\b|day\b|month\b)/i", $date, $matches2); //eg +2 Week
			preg_match("/[next|last]* (\monday\b|tuesday\b|wednesday\b|thursday\b|friday\b|saturday\b|sunday\b)/i", $date, $matches3); //eg next wednesday
			$matches = array_merge($matches, $matches, $matches2);
			if (!empty($matches)) {
				$d = JFactory::getDate($date);
				//$date = $d->toMySQL();
				//$$$rob set to $format as we expect $date to already be in $format
				$date = $d->toFormat($format);
			}

			// $$$ - hugh : urldecode (useful when ajax calls, may need better fix)
			// as per http://fabrikar.com/forums/showthread.php?p=43314#post43314
			$date = urldecode($date);
			//strip any textual date representations from the string

			$days = array('%A', '%a');
			foreach ($days as $day) {
				if (strstr($format, $day)) {
					$format = str_replace($day, '', $format);
					$date =  FabrikWorker::_stripDay( $date,  $day == '%a' ? true : false);
				}
			}
			$months = array('%B', '%b', '%h');
			foreach ($months as $month) {
				if (strstr($format, $month)) {
					$format = str_replace($month, '%m', $format);
					$date =  FabrikWorker::_monthToInt($date, $month == '%B' ? false : true);
				}
			}
			//@TODO: some of these arent right for strftime
			$this->finalformat = $format;
			$search = array('%d', '%e', '%D', '%j', // day
                    '%m', '%b', // month
                    '%Y', '%y', // year
                    '%g', '%H', '%h', // hour
                    '%i', '%s', '%S', '%M');

			$replace = array('(\d{2})', '(\d{1,2})', '(\w{3})', '(\d{1,2})', //day
                     '(\d{2})', '(\w{3})', // month
                     '(\d{4})', '(\d{2})', // year
                     '(\d{1,2})', '(\d{2})', '(\d{2})', // hour
                     '(\d{2})', '(\d{2})', '(\d{2})', '(\d{2})');


			$pattern = str_replace($search, $replace, $format);
			if (!preg_match("#$pattern#", $date, $matches)) {
				// lets allow for partial date formats - eg just the date and ignore the time
				$format = explode("%", $format);
				if (empty($format)) {
					//no format left to test so return false
					return false;
				}
				array_pop($format);
				$format = trim(implode('%', $format));
				$this->finalformat = $format;
				return FabrikWorker::str2Time($date, $format);
			}
			$dp = $matches;
			if (!preg_match_all( '#%(\w)#', $format, $matches)) {
				return false;
			}
			$id = $matches['1'];
			if (count($dp) != count($id)+1) {
				return false;
			}
			$ret = array();
			for ($i=0, $j=count($id); $i<$j; $i++) {
				$ret[$id[$i]] = $dp[$i+1];
			}
			return $ret;
		}

		function getFinalDateFormat()
		{
			return $this->finalformat;
		}

		/**
		 * removed day of week name from string
		 *
		 * @access protected
		 * @param string $day The string date
		 * @param bol abbreviated day?
		 * @return string date
		 */
		function _stripDay($date, $abrv = false)
		{
			if ($abrv) {
				$date = str_replace(JText::_('SUN'), '', $date);
				$date = str_replace(JText::_('MON'), '', $date);
				$date = str_replace(JText::_('TUE'), '', $date);
				$date = str_replace(JText::_('WED'), '', $date);
				$date = str_replace(JText::_('THU'), '', $date);
				$date = str_replace(JText::_('FRI'), '', $date);
				$date = str_replace(JText::_('SAT'), '', $date);
			}else{
				$date = str_replace(JText::_('SUNDAY'), '', $date);
				$date = str_replace(JText::_('MONDAY'), '', $date);
				$date = str_replace(JText::_('TUESDAY'), '', $date);
				$date = str_replace(JText::_('WEDNESDAY'), '', $date);
				$date = str_replace(JText::_('THURSDAY'), '', $date);
				$date = str_replace(JText::_('FRIDAY'), '', $date);
				$date = str_replace(JText::_('SATURDAY'), '', $date);
			}
			return $date;
		}

		function _monthToInt($date, $abrv = false)
		{
			if ($abrv) {
				$date = str_replace(JText::_('JANUARY_SHORT'), '01', $date);
				$date = str_replace(JText::_('FEBRUARY_SHORT'), '02', $date);
				$date = str_replace(JText::_('MARCH_SHORT'), '03', $date);
				$date = str_replace(JText::_('APRIL_SHORT'), '04', $date);
				$date = str_replace(JText::_('MAY_SHORT'), '05', $date);
				$date = str_replace(JText::_('JUNE_SHORT'), '06', $date);
				$date = str_replace(JText::_('JULY_SHORT'), '07', $date);
				$date = str_replace(JText::_('AUGUST_SHORT'), '08', $date);
				$date = str_replace(JText::_('SEPTEMBER_SHORT'), '09', $date);
				$date = str_replace(JText::_('OCTOBER_SHORT'), 10, $date);
				$date = str_replace(JText::_('NOVEMBER_SHORT'), 11, $date);
				$date = str_replace(JText::_('DECEMBER_SHORT'), 12, $date);
			}else{
				$date = str_replace(JText::_('JANUARY'), '01', $date);
				$date = str_replace(JText::_('FEBRUARY'), '02', $date);
				$date = str_replace(JText::_('MARCH'), '03', $date);
				$date = str_replace(JText::_('APRIL'), '04', $date);
				$date = str_replace(JText::_('MAY'), '05', $date);
				$date = str_replace(JText::_('JUNE'), '06', $date);
				$date = str_replace(JText::_('JULY'), '07', $date);
				$date = str_replace(JText::_('AUGUST'), '08', $date);
				$date = str_replace(JText::_('SEPTEMBER'), '09', $date);
				$date = str_replace(JText::_('OCTOBER'), 10, $date);
				$date = str_replace(JText::_('NOVEMBER'), 11, $date);
				$date = str_replace(JText::_('DECEMBER'), 12, $date);
			}
			return $date;

		}

		function isReserved($str)
		{
			$_reservedWords = array("task", "view", "layout", "option", "formid", "submit", "ul_max_file_size", "ul_file_types", "ul_directory", "listid", 'rowid', 'itemid', 'adddropdownvalue', 'adddropdownlabel', 'ul_end_dir');
			if (in_array(strtolower($str ), $_reservedWords)) {
				return true;
			}
			return false;
		}

		/**
		 * iterates through string to replace every
		 * {placeholder} with posted data
		 * @param string text to parse
		 * @param array data to search for placeholders (default $_REQUEST)
		 * @param bool if no data found for the place holder do we keep the {...} string in the message
		 * @param bool add slashed to the text?
		 * @param object user to use in replaceWithUserData (defaults to logged in user)
		 */

		function parseMessageForPlaceHolder($msg, $searchData = null, $keepPlaceholders = true, $addslashes = false, $theirUser = null)
		{
			$this->_parseAddSlases = $addslashes;
			if ($msg == '' || is_array($msg) || strpos($msg, '{') === false) {
				return $msg;
			}

			$msg = str_replace(array('%7B', '%7D'), array('{', '}'), $msg);
			if (is_object($searchData)) {
				$searchData = JArrayHelper::fromObject($searchData);
			}
			//$post	= JRequest::get('post');
			//$$$ rob changed to allow request variables to be parsed as well. I have a sneaky feeling this
			// was set to post for a good reason, but I can't see why now.
			$post	= JRequest::get('request');
			$this->_searchData = is_null($searchData) ?  $post : array_merge($post, $searchData);
			$this->_searchData['JUtility::getToken'] = JUtility::getToken();
			$msg = FabrikWorker::_replaceWithUserData($msg);
			if (!is_null($theirUser)) {
				$msg = FabrikWorker::_replaceWithUserData($msg, $theirUser, 'your');
			}
			$msg = FabrikWorker::_replaceWithGlobals($msg);
			$msg = preg_replace("/{}/", "", $msg);
			/* replace {element name} with form data */
			$msg = preg_replace_callback("/{[^}\s]+}/i", array($this, '_replaceWithFormData'), $msg);
			if (!$keepPlaceholders) {
				$msg = preg_replace("/{[^}\s]+}/i", '', $msg);
			}
			return $msg;
		}

		/**
		 * replace {varname} with request data (called from J content plugin
		 * @param $msg string to parse
		 */

		function replaceRequest(&$msg)
		{
			$request =& JRequest::get('request');
			foreach ($request as $key => $val) {
				if (is_string($val)) {
					// $$$ hugh - escape the key so preg_replace won't puke if key contains /
					$key = str_replace('/', '\/', $key);
					$msg = preg_replace("/\{$key\}/", $val, $msg);
				}
			}
		}

		/**
		 * 	DEPRECIATED??
		 * get an ACL group
		 * @param $val
		 * @param $cond string >= decides if we are matching above or below a certain acl level (only 4 native J acl)
		 * @return unknown_type
		 */

		function getACLGroups($val, $cond = '>=')
		{
			return;
		}

		/**
		 * set an ACL value
		 * @param $action
		 * @param $task
		 * @param $coponent
		 * @param $userGroup
		 * @param $a
		 * @param $b
		 * @param $c
		 * @return unknown_type
		 */

		function setACL($action, $task, $coponent, $userGroup, $a=null, $b=null, $c=null)
		{
			return;
		}

		/**
		 * determine if you can use a thing
		 * uses JACL if available
		 * @param object access id
		 * @param string access object identifier
		 * @param string acl direction (inclusive or exclusive - not tested for JACL integration )
		 */

		function getACL($a, $key, $cond = '>=')
		{
			//@TODO for Joomla 1.6 /1.7 perhaps?
			return true;
		}

		/**
		 * PRIVATE:
		 * called from parseMessageForPlaceHolder to iterate through string to replace
		 * {placeholder} with user ($my) data
		 * AND
		 * {$their->var->email} placeholderse
		 *
		 * @param string message to parse
		 * @param object user
		 * @param string key - search string to look for e.g. 'my' to look for {$my->id}
		 * @return string parsed message
		 */

		function _replaceWithUserData($msg, $user = null, $prefix = 'my')
		{
			if (is_null($user)) {
				$user  = &JFactory::getUser();
			}
			if (is_object($user)) {
				foreach ($user as $key=>$val) {
					if (substr($key, 0, 1) != '_') {
						if (!is_object($val) && !is_array($val)) {
							$msg = str_replace('{$'.$prefix.'->' . $key . '}', $val, $msg);
							$msg = str_replace('{$'.$prefix.'-&gt;' . $key . '}', $val, $msg);
						}
					}
				}
			}
			// $$$rob parse another users data into the string:
			//format: is {$their->var->email} where var is the JRequest var to search for
			// e.g url - index.php?owner=62 with placeholder {$their->owner->id}
			// var should be an integer corresponding to the user id to load

			$matches = array();
			preg_match('/{\$their-\>(.*?)}/', $msg, $matches);

			foreach ($matches as $match) {
				$bits = explode('->', str_replace(array('{', '}'), '', $match));
				$userid = JRequest::getInt(JArrayHelper::getValue($bits, 1));
				if ($userid !== 0) {
					$user =& JFactory::getUser($userid);
					$val = $user->get(JArrayHelper::getValue($bits, 2));
					$msg = str_replace($match, $val, $msg);
				}
			}
			return $msg;
		}


		/**
		 * PRIVATE:
		 * called from parseMessageForPlaceHolder to iterate through string to replace
		 * {placeholder} with global data
		 * @param string message to parse
		 * @return string parsed message
		 */

		function _replaceWithGlobals($msg)
		{
			$app = JFactory::getApplication();
			$menuItem = $app->getMenu('site')->getActive();
			$Itemid	= is_object($menuItem) ? $menuItem->id : 0;
			$config		= JFactory::getConfig();
			$msg = str_replace('{$mosConfig_absolute_path}', JPATH_SITE, $msg);
			$msg = str_replace('{$mosConfig_live_site}', JURI::base(), $msg);
			$msg = str_replace('{$mosConfig_offset}', $config->getValue('offset'), $msg);
			$msg = str_replace('{$Itemid}', $Itemid, $msg);
			$msg = str_replace('{$mosConfig_sitename}', $config->getValue('sitename'), $msg);
			$msg = str_replace('{$mosConfig_mailfrom}',$config->getValue('mailfrom'), $msg);
			$msg = str_replace('{where_i_came_from}', JRequest::getVar('HTTP_REFERER', '', 'server'), $msg);
			foreach ($_SERVER as $key=>$val) {
				if (!is_object($val) && !is_array($val)) {
					$msg = str_replace('{$_SERVER->' . $key . '}', $val, $msg);
					$msg = str_replace('{$_SERVER-&gt;' . $key . '}', $val, $msg);
				}
			}
			$session = JFactory::getSession();
			$token = $session->get('session.token');
			$msg = str_replace('{session.token}', $token, $msg);
			return $msg;
		}

		/**
		 * PRVIATE:
		 * called from parseMessageForPlaceHolder to iterate through string to replace
		 * {placeholder} with posted data
		 * @param string placeholder e.g. {placeholder}
		 * @return string posted data that corresponds with placeholder
		 */

		function _replaceWithFormData($matches)
		{
			//merge any join data key val pairs down into the main data array
			$joins = JArrayHelper::getValue($this->_searchData, 'join', array());
			foreach ($joins as $k => $data) {
				foreach ($data as $k => $v) {
					$this->_searchData[$k] = $v;
				}
			}

			$match = $matches[0];
			$orig = $match;
			/* strip the {} */
			$match = substr($match, 1, strlen($match) - 2);

			// $$$ rob test this format searchvalue||defaultsearchvalue
			$bits = explode("||", $match);
			if (count($bits) == 2) {
				$match = FabrikWorker::parseMessageForPlaceHolder("{".$bits[0]."}", $this->_searchData, false);
				$default = $bits[1];
				if ($match == '') {
					// 	$$$ rob seems like bits[1] in fabrik plugin is already matched so return that rather then reparsing
					//$match = FabrikWorker::parseMessageForPlaceHolder("{".$bits[1]."}", $this->_searchData);
					return $bits[1] !== '' ? $bits[1] : $orig;
				} else {
					return $match !== '' ? $match : $orig;
				}
			}

			// $$$ hugh - NOOOOOOO!!  Screws up where people actually have mixed case element names
			//$match = strtolower($match);
			$match = preg_replace("/ /", "_", $match);
			if (!strstr($match, ".")) {
				/* for some reason array_key_exists wasnt working for nested arrays?? */
				$aKeys = array_keys($this->_searchData);
				/* remove the table prefix from the post key */
				$aPrefixFields = array();
				for ($i=0; $i < count($aKeys); $i++) {
					$aKeyParts = explode('___', $aKeys[$i]);

					if (count($aKeyParts) == 2) {
						$tablePrefix = array_shift($aKeyParts);
						$field = array_pop($aKeyParts);
						$aPrefixFields[$field] = $tablePrefix;
					}
				}
				if (array_key_exists($match, $aPrefixFields)) {
					$match =  $aPrefixFields[$match] . '___' . $match;
				}
				//test to see if the made match is in the post key arrays
				$found = in_array($match, $aKeys, true);
				if ($found) {
					/* get the post data */
					$match = $this->_searchData[ $match ];
					if (is_array($match)) {
						$newmatch = '';
						//deal with radio boxes etc inside repeat groups
						foreach ($match as $m) {
							if (is_array($m)) {
								$newmatch .= "," . implode(',', $m);
							} else {
								$newmatch .= "," . $m;
							}
						}
						$match = ltrim($newmatch, ',');
					}
				} else {
					$match = "";
				}

			} else {
				/* could be looking for URL field type eg for $_POST[url][link] the match text will be url.link */
				$aMatch = explode(".", $match);
				$aPost = $this->_searchData;
				foreach ($aMatch as $sPossibleArrayKey) {
					if (is_array($aPost)) {
						if (!isset($aPost[$sPossibleArrayKey])) {
							return $orig;
						} else {
							$aPost = $aPost[$sPossibleArrayKey];
						}
					}
				}
				$match = $aPost;
			}
			if ($this->_parseAddSlases) {
				$match = htmlspecialchars($match, ENT_QUOTES, 'UTF-8');
			}

			return $found ? $match : $orig;
		}

		/**
		 * Internal function to recursive scan directories
		 * @param string Path to scan
		 * @param string root path of this folder
		 * @param array  Value array of all existing folders
		 * @param array  Value array of all existing images
		 * @param bol make options out for the results
		 */

		public function readImages($imagePath, $folderPath, &$folders, &$images, $aFolderFilter, $makeOptions = true)
		{
			$imgFiles = FabrikWorker::fabrikReadDirectory($imagePath, '.', false, false, $aFolderFilter);
			foreach ($imgFiles as $file) {
				$ff_ 	= $folderPath . $file .'/';
				$ff 	= $folderPath . $file;
				$i_f 	= $imagePath .'/'. $file;
				if (is_dir($i_f) && $file != 'CVS' && $file != '.svn') {
					if (!in_array($file, $aFolderFilter)) {
						$folders[] = JHTML::_('select.option', $ff_);
						FabrikWorker::readImages($i_f, $ff_, $folders, $images, $aFolderFilter);
					}
				} else if (preg_match('/bmp|gif|jpg|png/i', $file) && is_file($i_f)) {
					// leading / we don't need
					$imageFile = substr($ff, 1);
					$images[$folderPath][] = $makeOptions ? JHTML::_('select.option', $imageFile, $file) : $file;
				}
			}
		}

		/**
		 * Utility function to read the files in a directory
		 * @param string The file system path
		 * @param string A filter for the names
		 * @param boolean Recurse search into sub-directories
		 * @param boolean True if to prepend the full path to the file name
		 * @param array folder names not to recurse into
		 * @param boolean return a list of folders only (true)
		 * @return array of file/folder names
		 */

		public function fabrikReadDirectory($path, $filter='.', $recurse=false, $fullpath=false, $aFolderFilter=array(), $foldersOnly = false)
		{
			$arr = array();
			if (!@is_dir($path)) {
				return $arr;
			}
			$handle = opendir($path);
			while ($file = readdir($handle)) {

				$dir = JPath::clean($path.'/'.$file);
				$isDir = is_dir($dir);
				if ($file != "." && $file != "..") {

					if (preg_match("/$filter/", $file)) {

						if (($isDir && $foldersOnly) || !$foldersOnly) {
							if ($fullpath) {
								$arr[] = trim(JPath::clean($path.'/'.$file));
							} else {
								$arr[] = trim($file);
							}
						}
					}
					$goDown = true;
					if ($recurse && $isDir) {
						foreach ($aFolderFilter as $sFolderFilter) {
							if (strstr($dir, $sFolderFilter)) {
								$goDown = false;
							}
						}

						if ($goDown) {
							$arr2 = FabrikWorker::fabrikReadDirectory($dir, $filter, $recurse, $fullpath,$aFolderFilter, $foldersOnly);
							$arrDiff = array_diff($arr, $arr2);
							$arr = array_merge($arrDiff);
						}
					}
				}
			}
			closedir($handle);
			asort($arr);
			return $arr;
		}

		/**
		 * @since 2.0.5 - Joomfish translations don't seem to work when you do an ajax call
		 * it seems to load the geographical location language rather than the selected lang
		 * so for ajax calls that need to use jf translated text we need to get the current lang and
		 * send it to the js code which will then append the lang=XX to the ajax querystring
		 * @return first two letters of lang code - e.g. nl from 'nl-NL'
		 */

		public function getJoomfishLang()
		{
			$lang = JFactory::getLanguage();
			return array_shift(explode('-', $lang->getTag()));
		}

		/**
		 * get the contetn filter used both in form and admin pages for content filter
		 * takes values from J content filtering options
		 * @return array(bool should the filter be used, object the filter to use)
		 */

		public function getContentFilter()
		{
			$version = new JVersion();
			$dofilter = false;
			$filter= false;
			// @TODO fix for J1.6 +
			if ($version->RELEASE == '1.5' && $version->DEV_LEVEL >= 12) {
				//for J1.5.12 + use filter settings defined in com_content
				// Filter settings
				jimport('joomla.application.component.helper');
				$config	= JComponentHelper::getParams('com_content');
				$user	= &JFactory::getUser();
				$gid	= $user->get('gid');

				$filterGroups	=  $config->get('filter_groups');

				// convert to array if one group selected
				if ((!is_array($filterGroups) && (int)$filterGroups > 0)) {
					$filterGroups = array($filterGroups);
				}
				if (is_array($filterGroups) && in_array($gid, $filterGroups))
				{
					$filterType		= $config->get('filter_type');
					$filterTags		= preg_split( '#[,\s]+#', trim($config->get('filter_tags')));
					$filterAttrs	= preg_split( '#[,\s]+#', trim($config->get('filter_attritbutes')));
					switch ($filterType)
					{
						case 'NH':
							$filter	= new JFilterInput();
							break;
						case 'WL':
							$filter	= new JFilterInput($filterTags, $filterAttrs, 0, 0, 0);  // turn off xss auto clean
							break;
						case 'BL':
						default:
							$filter	= new JFilterInput($filterTags, $filterAttrs, 1, 1);
							break;
					}
					$dofilter = true;
				} elseif(empty($filterGroups) && $gid != '25') { // no default filtering for super admin (gid=25)
					$filter = new JFilterInput( array(), array(), 1, 1);
					$dofilter = true;
				}
			} else {
				$filter	= JFilterInput::getInstance(null, null, 1, 1);
			}
			//echo $dofilter ? ' do filter ' : 'dont do filter';
			return array($dofilter, $filter);
		}

		/**
		 * raise a J Error notice if the eval'd result is false and there is a error
		 * @param mixed $val evaluated result
		 * @param string $msg error message, should contain %s as we spintf in the error_get_last()'s message property
		 */

		public function logEval($val, $msg)
		{
			if (version_compare( phpversion(), '5.2.0', '>=')) {
				if ($val === false && $error = error_get_last() && (JRequest::getVar('fabrikdebug') ==1 || JDEBUG)) {
					JError::raiseNotice(500, sprintf($msg, $error['message']));
				}
			}
		}

		/**
		 * log  to table jos_fabrik_logs
		 * @param string $type e.g. 'fabrik.fileupload.download'
		 * @param mixed $msg array/object/string
		 * @param bool $jsonEncode
		 */

		public function log($type, $msg, $jsonEncode = true)
		{
			if ($jsonEncode) {
				$msg = json_encode($msg);
			}
			$log =& JTable::getInstance('log', 'Table');
			$log->message_type = $type;
			$log->message = $msg;
			$log->store();
		}

		/**
		 * Get a database object
		 *
		 * Returns the global {@link JDatabase} object, only creating it
		 * if it doesn't already exist.
		 *
		 * @return JDatabase object
		 */
		public static function getDbo()
		{
			if (!self::$database) {
				JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fabrik'.DS.'tables');
				$cn = JTable::getInstance('Connection', 'FabrikTable');
				$cn->load(array('default'=> 1));
				$conf				= JFactory::getConfig();
				$host 			= $cn->host;
				$user 			= $cn->user;
				$password 	= $cn->password;
				$database		= $cn->database;
				if (defined('KOOWA')) {
					$dbprefix = '';
				} else {
					$dbprefix = $conf->getValue('config.dbprefix');
				}
				$driver 		= $conf->getValue('config.dbtype');
				//test for sawpping db table names
				$driver .= '_fab';
				$debug 			= $conf->getValue('config.debug');
				$options		= array('driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $dbprefix);

				self::$database = JDatabase::getInstance($options);
			}
			return self::$database;
		}

		/**
		 * takes a string which may or may not be json and returns either string/array/object
		 * will also turn valGROUPSPLITTERval2 to array
		 * @param string $data
		 * @param bool force data to be an array
		 */

		public function JSONtoData($data, $toArray = false)
		{
			// repeat elements are concatned with the GROUPSPLITTER - conver to json string
			// before continuing.
			if (strstr($data, GROUPSPLITTER)) {
				$data = json_encode(explode(GROUPSPLITTER, $data));
			}
			$json = json_decode($data);
			// only works in PHP5.3
			//$data = (json_last_error() == JSON_ERROR_NONE) ? $json : $data;
			$data = is_null($json) ? $data : $json;
			$data = $toArray ? (array)$data : $data;
			return $data;
		}

		/**
		 * test if a string is a compatible date
		 * @param string $d
		 * @return bool
		 */

		public function isDate($d)
		{
			try {
				$dt = new DateTime($d);
			}
			catch(Exception $e) {
				return false;
			}
			return true;
		}
}

?>