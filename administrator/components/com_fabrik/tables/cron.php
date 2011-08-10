<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */


// no direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fabrik'.DS.'tables'.DS.'fabtable.php');

/**
 * @package		Joomla
 * @subpackage	Fabrik
 */
class FabrikTableCron extends FabTable
{

	/*
	 *
	 */

	function __construct(&$_db)
	{
		parent::__construct('#__{package}_cron', 'id', $_db);
	}

	/**
	 * overloaded check function
	 */

	function check()
	{
		return true;
	}

	/**
	 * Overloaded bind function
	 *
	 * @param	array		$hash named array
	 *
	 * @return	null|string	null is operation was satisfactory, otherwise returns an error
	 * @see		JTable:bind
	 * @since	1.5
	 */

	public function bind($array, $ignore = '')
	{

		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}

		return parent::bind($array, $ignore);
	}

}
?>