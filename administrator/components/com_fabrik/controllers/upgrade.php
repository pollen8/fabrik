<?php
/**
* @version
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controlleradmin');

/**
 * @package		Joomla
 * @subpackage	Fabrik
 */

class FabrikControllerUpgrade extends JControllerAdmin
{

	/**
	 * Constructor
	 */

	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * delete all data from fabrik
	 */

	function check()
	{
		$model = $this->getModel('Upgrade');
	}


}
?>