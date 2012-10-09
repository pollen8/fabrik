<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit an element (inline list plugin when editing in admin).
 *
 * @package		Joomla.Administrator
 * @subpackage	Fabrik
 * @since		3.0
 */
class FabrikViewElement extends JView
{

	/**
	 * This is called for both editing and saving inline edit elements whilst list rendered
	 * in administration.
	 */

	public function display($tpl = null)
	{
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		$app = JFactory::getApplication();
		$input = $app->input;
		$ids = $input->get('plugin', array(), 'array');
		foreach ($ids as $id)
		{
			//$plugin = $pluginManager->getElementPlugin($id);
		}
		$formModel = JModel::getInstance('Form', 'FabrikFEModel');
		$formModel->setId($input->getInt('formid'));
		$formModel->inLineEdit();
		/* $elementid = $input->getInt('elid');
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		$className = $input->get('plugin');
		$plugin =& $pluginManager->getPlugIn($className, 'element');
		$plugin->setId($elementid);
		$plugin->inLineEdit();

		$task = $input->get('task');
		if ($task !== 'element.save' && $task !== 'save') {
		    JFactory::getCache('com_fabrik')->clean();
		} */
	}

}
