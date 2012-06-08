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
class FabrikTablePackage extends JTable
{

 	/*
 	 *
 	 */

	function __construct(&$_db)
	{
		parent::__construct('#__{package}_packages', 'id', $_db);
	}

}
?>
