<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// ROB dont think this is used - use fabrik.php model instead

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Extend JModel with Fabrik specific methods
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 */

class FabrikFEModel extends JModelLegacy
{

	/**
	 * required for compatibility with mambo 4.5.4
	 *
	 * @param   mixed  $value  value to set all properties to default null
	 *
	 * @return  null
	 */

	public function reset($value = null)
	{
		$keys = $this->getProperties();
		foreach ($keys as $k)
		{
			$this->$k = $value;
		}
	}
}
