<?php
/*
 * @package Joomla.Administrator
 * @subpackage Fabrik
 * @since		1.6
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Element controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_fabrik
 * @since		1.6
 */
class FabrikControllerElement extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_ELEMENT';

	protected $default_view = 'element';

	/**
	 * called via ajax to load in a given plugin's HTML settings
	 */

	public function getPluginHTML()
	{
		$plugin = JRequest::getCmd('plugin');
		$model = $this->getModel();
		$model->setState('element.id', JRequest::getInt('id'));
		$model->getForm();
		echo $model->getPluginHTML($plugin);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JControllerForm::save()
	 */

	public function save($key = null, $urlVar = null)
	{
		$listModel = $this->getModel('list', 'FabrikFEModel');
		$listModel->setId(JRequest::getInt('listid'));
		$rowId = JRequest::getVar('rowid');
		$key = JRequest::getVar('element');
		$key = array_pop(explode('___', $key));
		$value = JRequest::getVar('value');
		$listModel->storeCell($rowId, $key, $value);
		$this->mode = 'readonly';
		$this->display();
	}

}