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

class FabrikFEModel extends JModel
{

	/**
	 * Requires that the child object has the corrent 'mambo' fields for
	 * publsihing - ie state, publish_up, publish_down.
	 *
	 * @deprecated
	 *
	 * @return  bool  can show the published item or not
	 */

	public function canPublish()
	{
		$app = JFactory::getApplication();
		$config = JFactory::getConfig();
		if ($app->isAdmin())
		{
			return true;
		}
		$now = date('Y-m-d H:i:s', time() + $config->getValue('offset') * 60 * 60);
		/* set the publish down date into the future */
		if (trim($this->publish_down) == '0000-00-00 00:00:00')
		{
			$this->publish_down = $now + 30;
		}
		/* set the publish up date into the past */
		if (trim($this->publish_up) == '0000-00-00 00:00:00')
		{
			$this->publish_up = $now - 30;
		}
		if ($this->state == '1' and $now >= $this->publish_up and $now <= $this->publish_down)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Required for compatibility with mambo 4.5.4
	 *
	 * @param   string  $value  value to reset to
	 *
	 * @deprecated
	 *
	 * @return  void
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
