<?php
/**
 * Created by PhpStorm.
 * User: rob_000
 * Date: 05/04/2016
 * Time: 07:52
 */

namespace Fabrik\Helpers;

use \DateTime;
use \DateTimeZone;
use \JFactory;

/**
 * very small override to JDate to stop 500 errors occurring (when Jdebug is on) if $date is not a valid date string
 *
 * @package  Fabrik
 * @since    3.0
 */
class Date extends \JDate
{
	/**
	 * GMT Date
	 *
	 * @var DateTimeZone
	 */
	protected static $gmt;

	/**
	 * Default tz date
	 *
	 * @var DateTimeZone
	 */
	protected static $stz;

	/**
	 * Construct
	 *
	 * @param   string $date Date
	 * @param   mixed  $tz   Timezone
	 */
	public function __construct($date = 'now', $tz = null)
	{
		$app  = JFactory::getApplication();
		$orig = $date;
		$date = $this->stripDays($date);
		/* not sure if this one needed?
		 * $date = $this->monthToInt($date);
		 */
		$date = $this->removeDashes($date);

		try
		{
			$dt = new DateTime($date);
		} catch (\Exception $e)
		{
			JDEBUG ? $app->enqueueMessage('date format unknown for ' . $orig . ' replacing with today\'s date', 'notice') : '';
			$date = 'now';
			/* catches 'Failed to parse time string (ublingah!) at position 0 (u)' exception.
			 * don't use this object
			 */
		}

		// Create the base GMT and server time zone objects.
		if (empty(self::$gmt) || empty(self::$stz))
		{
			self::$gmt = new DateTimeZone('GMT');
			self::$stz = new DateTimeZone(@date_default_timezone_get());
		}

		parent::__construct($date, $tz);
	}

	/**
	 * Remove '-' from string
	 *
	 * @param   string $str String to remove - from
	 *
	 * @return  string
	 */
	protected function removeDashes($str)
	{
		$str = StringHelper::ltrimword($str, '-');

		return $str;
	}

	/**
	 * Month name to integer
	 *
	 * @param   string $str Month name
	 *
	 * @return  int  month number
	 */
	protected function monthToInt($str)
	{
		$abbrs = array(true, false);

		for ($a = 0; $a < count($abbrs); $a++)
		{
			for ($i = 0; $i < 13; $i++)
			{
				$month = $this->monthToString($i, $abbrs[$a]);

				if (StringHelper::stristr($str, $month))
				{
					$monthNum = StringHelper::strlen($i) === 1 ? '0' . $i : $i;
					$str      = StringHelper::str_ireplace($month, $monthNum, $str);
				}
			}
		}

		return $str;
	}

	/**
	 * Converts strftime format into PHP date() format
	 *
	 * @param   string &$format Strftime format
	 *
	 * @since   3.0.7
	 *
	 * @return  void
	 */
	static public function strftimeFormatToDateFormat(&$format)
	{
		$app = JFactory::getApplication();

		if (strstr($format, '%C'))
		{
			$app->enqueueMessage('Cant convert %C strftime date format to date format, substituted with Y', 'notice');

			return;
		}

		$search = array('%e', '%j', '%u', '%V', '%W', '%h', '%B', '%C', '%g', '%G', '%M', '%P', '%r', '%R', '%T', '%X', '%z', '%Z', '%D', '%F', '%s',
			'%x', '%A', '%Y', '%m', '%d', '%H', '%S');

		$replace = array('j', 'z', 'w', 'W', 'W', 'M', 'F', 'Y', 'y', 'Y', 'i', 'a', '"g:i:s a', 'H:i', 'H:i:s', 'H:i:s', 'O', 'O', 'm/d/y"', 'Y-m-d', 'U',
			'Y-m-d', 'l', 'Y', 'm', 'd', 'H', 's');

		$format = str_replace($search, $replace, $format);
	}

	/**
	 * Convert strftime to PHP time format
	 *
	 * @param   string &$format Format
	 *
	 * @return  void
	 */
	static public function dateFormatToStrftimeFormat(&$format)
	{
		$search = array('d', 'D', 'j', 'l', 'N', 'S', 'w', 'z', 'W', 'F', 'm', 'M', 'n', 't', 'L', 'o', 'Y',
			'y', 'a', 'A', 'B', 'g', 'G', 'h', 'H', 'i', 's', 'u',
			'I', 'O', 'P', 'T', 'Z', 'c', 'r', 'U');

		$replace = array('%d', '%a', '%e', '%A', '%u', '', '%w', '%j', '%V', '%B', '%m', '%b', '%m', '', '', '%g', '%Y',
			'%y', '%P', '%p', '', '%l', '%H', '%I', '%H', '%M', '%S', '',
			'', '', '', '%z', '', '%c', '%a, %d %b %Y %H:%M:%S %z', '%s');

		// Removed e => %z as that meant, j => %e => %%z (prob could re-implement with a regex if really needed)
		$format = str_replace($search, $replace, $format);
	}

	/**
	 * Strip days
	 *
	 * @param   string $str Date string
	 *
	 * @return  string date without days
	 */
	protected function stripDays($str)
	{
		$abbrs = array(true, false);

		for ($a = 0; $a < count($abbrs); $a++)
		{
			for ($i = 0; $i < 7; $i++)
			{
				$day = $this->dayToString($i, $abbrs[$a]);

				if (StringHelper::stristr($str, $day))
				{
					$str = StringHelper::str_ireplace($day, '', $str);
				}
			}
		}

		return $str;
	}

}
