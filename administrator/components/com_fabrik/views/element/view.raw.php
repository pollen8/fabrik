<?php
/**
 * View to edit an element (inline list plugin when editing in admin).
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit an element (inline list plugin when editing in admin).
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.0
 */
class FabrikAdminViewElement extends JViewLegacy
{

	/**
	 * This is called for both editing and saving inline edit elements whilst list rendered
	 * in administration.
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return  void
	 */

	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$formModel = JModelLegacy::getInstance('Form', 'FabrikFEModel');
		$formModel->setId($input->getInt('formid'));
		$formModel->inLineEdit();
	}

}
