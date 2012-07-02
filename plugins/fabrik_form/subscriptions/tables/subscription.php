<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */


// No direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR . '/components/com_fabrik/tables/fabtable.php');

/**
 * @package     Joomla
 * @subpackage  Fabrik
 */

class FabrikTableSubscription extends JTable
{

	/*
	 * Constructor
	 */

	function __construct(&$_db)
	{
		parent::__construct('#__fabrik_subs_subscriptions', 'id', $_db);
	}

}
?>
