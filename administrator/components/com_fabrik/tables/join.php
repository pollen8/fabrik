<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Pollen 8 Design Ltd. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR . '/components/com_fabrik/tables/fabtable.php');

class FabrikTableJoin extends FabTable
{

 	/*
 	 * Construct
 	 */

	function __construct(&$_db)
	{
		parent::__construct('#__{package}_joins', 'id', $_db);
	}

}
?>