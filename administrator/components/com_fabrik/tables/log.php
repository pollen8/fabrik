<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * @package     Joomla
 * @subpackage  Fabrik
 */
class FabrikTableLog extends JTable
{

	/*
	 *
	 */

	function __construct(&$_db)
	{
		parent::__construct('#__{package}_log', 'id', $_db);
	}


}
?>