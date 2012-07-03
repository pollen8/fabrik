<?php

/**
 * @package     Joomla
 * @subpackage  Fabrik
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

class plgFabrik_Cron extends FabrikPlugin
{
	
	/** @var object plugin table row **/
	protected $row = null;
	
	/** @var string log */
	protected $log = null;
	
	/**
	 * get the db row 
* @param   bool	$force
	 * @return  object
	 */
	
	public function &getTable($force = false)
	{
		if (!$this->row || $force)
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fabrik/tables');
			$row = FabTable::getInstance('Cron', 'FabrikTable');
			$row->load($this->row);
			$this->row = $row;
		}
		return $this->row;
	}
	
	/**
	* whether cron should automagically load table data
	* @return  bool
	*/
	
	public function requiresTableData()
	{
		return true;
	}
	
	/**
	 * get the log out put
	 * @return string
	 */
	
	public function getLog()
	{
		return $this->log;
	}
	
	/**
	* only applicable to cron plugins but as there's no sub class for them
	* the methods here for now
	* deterimes if the cron plug-in should be run - if require_qs is true
	* then fabrik_cron=1 needs to be in the querystring
	* @return  bool
	*/
	
	public function queryStringActivated()
	{
		$params = $this->getParams();
		if (!$params->get('require_qs', false))
		{
			// querystring not required so plugin should be activated
			return true;
		}
		return JRequest::getInt('fabrik_cron', 0);
	}
}
?>