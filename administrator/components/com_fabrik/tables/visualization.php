<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/


// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * @package		Joomla
 * @subpackage	Fabrik
 */

class FabrikTableVisualization extends JTable
{
 	/*
 	 * Constructor
 	 */

	function __construct(&$_db)
	{
		parent::__construct('#__{package}_visualizations', 'id', $_db);
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
		parent::bind($array, $ignore);
		return true;
	}

}
?>