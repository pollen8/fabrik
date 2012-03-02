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

class FabrikWebServiceRest extends FabrikWebService
{

	function __construct($options)
	{
		$this->options = $options;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see FabrikWebService::get()
	 */
	
	public function get($method, $filters = array(), $startPoint = null, $result = null)
	{
		$url = $this->options['endpoint'];
		if (!strstr($url, '?'))
		{
			$url .= '?';
		}
		foreach ($filters as $k => $v)
		{
			$url .= '&' . $k . '=' . $v;
		}
		$session = curl_init($url);
	    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	    $json = curl_exec($session);
		$phpObj = json_decode($json);
		
	    if (!is_null($phpObj))
	    {
	    	$startPoints = explode('.', $startPoint);
	    	foreach ($startPoints as $p)
	    	{
	    		$phpObj =& $phpObj->$p;
	    	}
	    	return $phpObj;
	    }
	    else
	    {
	    	return array();
	    }
	}

}
?>