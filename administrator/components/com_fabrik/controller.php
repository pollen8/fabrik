<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Fabrik master display controller.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_fabrik
 * @since		1.6
 */

class FabrikController extends JController
{
	/**
	 * Method to display a view.
	 */

	public function display()
	{
		$this->default_view = 'home';
		require_once JPATH_COMPONENT.'/helpers/fabrik.php';
		parent::display();
		// Load the submenu.
		FabrikHelper::addSubmenu(JRequest::getWord('view', 'lists'));
	}

	/**
	 * Method to load and return a model object.
	 *
	 * @param	string  The name of the model.
	 * @param	string	Optional model prefix.
	 * @param	array	Configuration array for the model. Optional.
	 * @return	mixed	Model object on success; otherwise null failure.
	 * @since	1.6		Replaces _createModel.
	 */

	protected function createModel($name, $prefix = '', $config = array())
	{
		// use true so that we always use the Joomla db when in admin.
		// otherwise if alt cnn set to default that is loaded and the fabrik tables are not found
		$db = FabrikWorker::getDbo(true);
		$config['dbo'] = $db;
		$r = parent::createModel($name, $prefix, $config);
		return $r;
	}
}
