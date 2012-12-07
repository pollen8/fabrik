<?php
/**
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Dummy skeleton view
 *
 * @package		Joomla.Site
 * @subpackage	Fabrik_skeleton
 * @since		1.6
 */
class SkeletonViewList extends JView
{

	public function display($tpl = null)
	{
		// dummy file which is not used
		// but required to allow the component to be selected from the Joomla
		// admin menu options.
	}

}