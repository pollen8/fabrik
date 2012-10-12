<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/tables/fabtable.php';

/**
 * Fubsubs plan table
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.1
 */

class FabrikTablePlan extends JTable
{

	/**
	 * Constructor
	 *
	 * @param   object  &$db  database object
	 */

	public function __construct(&$db)
	{
		parent::__construct('#__fabrik_subs_plans', 'id', $db);
	}

}
