<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */


// ROB dont think this is used - use fabrik.php model instead

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

class FabrikFEModel extends JModel
{

	/** @var string The null/zero date string */
	var $_nullDate		= '0000-00-00 00:00:00';

	/** @var object */
	var $_pluginManger = null;

	/**
	 * requires that the child object has the corrent 'mambo' fields for
	 * publsihing - ie state, publish_up, publish_down.
	 * @return bol can show the published item or not
	 */

	function canPublish()
	{
		$app = JFactory::getApplication();
		$config		= JFactory::getConfig();
		if ($app->isAdmin()) {
			return true;
		}
		$now = date( 'Y-m-d H:i:s', time() + $config->getValue('offset') * 60 * 60);
		/* set the publish down date into the future */
		if (trim($this->publish_down) == '0000-00-00 00:00:00') { $this->publish_down = $now + 30;}
		/* set the publish up date into the past */
		if (trim($this->publish_up) == '0000-00-00 00:00:00') { $this->publish_up = $now - 30;}
		if ($this->state == '1' and $now >=$this->publish_up and $now <= $this->publish_down) {
			return true;
		} else {
			return false;
		}
	}

	function replace_num_entity($ord)
	{
		$ord = $ord[1];
		if (preg_match('/^x([0-9a-f]+)$/i', $ord, $match)) {
			$ord = hexdec($match[1]);
		} else {
			$ord = intval($ord);
		}
		$no_bytes = 0;
		$byte = array();
		if ($ord < 128) {
			return chr($ord);
		}
		elseif ($ord < 2048)
		{
			$no_bytes = 2;
		}
		elseif ($ord < 65536)
		{
			$no_bytes = 3;
		}
		elseif ($ord < 1114112)
		{
			$no_bytes = 4;
		}
		else
		{
			return;
		}

		switch($no_bytes)
		{
			case 2:
				{
					$prefix = array(31, 192);
					break;
				}
			case 3:
				{
					$prefix = array(15, 224);
					break;
				}
			case 4:
				{
					$prefix = array(7, 240);
				}
		}
		for ($i = 0; $i < $no_bytes; $i++) {
			$byte[$no_bytes - $i - 1] = (($ord & (63 * pow(2, 6 * $i))) / pow(2, 6 * $i)) & 63 | 128;
		}
		$byte[0] = ($byte[0] & $prefix[0]) | $prefix[1];
		$ret = '';
		for ($i = 0; $i < $no_bytes; $i++) {
			$ret .= chr($byte[$i]);
		}
		return $ret;
	}

	/**
	 * required for compatibility with mambo 4.5.4
	 */

	function reset($value=null)
	{
		$keys = $this->getProperties();
		foreach ($keys as $k) {
			$this->$k = $value;
		}
	}
}
?>