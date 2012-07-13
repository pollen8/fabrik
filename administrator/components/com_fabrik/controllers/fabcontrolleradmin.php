<?php
/**
 * Extends JControllerAdmin allowing for confirmation of removal of
 * items, along with call to model to perform additional
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       1.6
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * List controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	Fabrik
 * @since		3.0
 */

class FabControllerAdmin extends JControllerAdmin
{
	/**
	 * actally delete the requested items forms etc
	 *
	 * @return null
	 */

	public function dodelete()
	{
		parent::delete();
	}
}
