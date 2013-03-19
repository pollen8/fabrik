<?php
/**
 * Upgrade controller
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       1.6
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controlleradmin');

require_once 'fabcontrollerform.php';

/**
 * Upgrade controller
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.0
*/

class FabrikAdminControllerUpgrade extends JControllerAdmin
{

	/**
	 * Delete all data from fabrik
	 *
	 * @return  null
	 */

	public function check()
	{
		$model = $this->getModel();
		$model->run();
		
	}
	
		/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    model name
	 * @param   string  $prefix  model prefix
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  J model
	 */

	public function getModel($name = 'Upgrade', $prefix = 'FabrikAdminModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

}
