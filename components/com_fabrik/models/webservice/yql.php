<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

class FabrikWebServiceYql extends FabrikWebService
{

	public function __construct($options)
	{
		$this->options = $options;
		ini_set("soap.wsdl_cache_enabled", 0);
	}

	public function getFunctions()
	{
		return false;
	}

	public function get($method, $filters = array(), $startPoint = null, $result = null)
	{
		if (!strstr($method, 'where'))
		{
			$method .= ' where ';
		}
		foreach ($filters as $k => $v)
		{
			$method .= $k . '=\'' . $v . '\'';
		}
		$url = $this->options['endpoint'] . '?q=' . urlencode($method) . '&format=json';

		// Make call with cURL
		$session = curl_init($url);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		$json = curl_exec($session);
		$phpObj = json_decode($json);
		if (!is_null($phpObj->query->results))
		{
			$res = $phpObj->query->results;
			$startPoints = explode('.', $startPoint);
			foreach ($startPoints as $p)
			{
				$res =& $res->$p;
			}
			return $res;
		}
		else
		{
			return array();
		}
	}

}
?>
