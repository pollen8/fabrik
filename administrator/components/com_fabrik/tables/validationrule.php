<?php
/**
 * Validation Rulle Fabrik Table
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/tables/fabtable.php';

/**
 * Validation Rulle Fabrik Table
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 * @deprecated  not used?
 */

class FabrikTableValidationrule extends FabTable
{

	/**
	 * Constructor
	 *
	 * @param   object  &$db  database object
	 */

	public function __construct(&$db)
	{
		parent::__construct('#__{package}_validation_rules', 'id', $db);
	}

}
