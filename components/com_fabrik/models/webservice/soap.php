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

class FabrikWebServiceSoap extends FabrikWebService
{

	public function __construct($options)
	{
		ini_set("soap.wsdl_cache_enabled", 0);
		$this->client = new SoapClient($options['endpoint']);
	}

	public function getFunctions()
	{
		$functions = $this->client->__getFunctions();
	}

	/**
	 * (non-PHPdoc)
	 * @see FabrikWebService::get()
	 */

	public function get($method, $filters = array(), $startPoint = null, $result = null)
	{
		if (is_null($result))
		{
			$result = $method . 'Result';
		}
		$xml = $this->client->$method($filters);
		$data = JFactory::getXML($xml->$result, false);
		if (!is_null($startPoint))
		{
			$data = $data->xpath($startPoint);
		}
		$return = array();
		// convert xml nodes into simple objects
		foreach ($data as $xmlElement)
		{
			$json = json_encode($xmlElement);
			$return[] = json_decode($json);
		}
		return $return;
	}

}
?>