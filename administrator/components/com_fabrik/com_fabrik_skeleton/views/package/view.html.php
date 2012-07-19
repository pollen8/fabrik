<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       1.6
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Dummy skeleton view
 *
 * @package     Fabrik
 * @subpackage  Fabrik_skeleton
 * @since       3.0
 */
class SkeletonViewPackage extends JViewLegacy
{

	/**
	 * display
	 *
	 * @param   string  $tpl  template
	 *
	 * @return  null
	 */

	public function display($tpl = null)
	{
		/**
		 * dummy file which is not used
		 * but required to allow the component to be selected from the Joomla
		 * admin menu options.
		 */
	}

}
