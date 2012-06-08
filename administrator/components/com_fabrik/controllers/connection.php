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
 * Connection controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_fabrik
 * @since		1.6
 */
class FabrikControllerConnection extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_CONNECTION';

	/**
	 * trys to connection to the database
	 * @return string connection message
	 */

	function test()
	{
		JRequest::checkToken() or die('Invalid Token');
		$cid = JRequest::getVar('cid', array(0), 'method', 'array');
		$cid = array((int) $cid[0]);
		$link = 'index.php?option=com_fabrik&view=connections';
		foreach ($cid as $id)
		{
			$model = JModel::getInstance('Connection', 'FabrikFEModel');
			$model->setId($id);
			if ($model->testConnection() == false)
			{
				JError::raiseWarning(500,  JText::_('COM_FABRIK_UNABLE_TO_CONNECT'));
				$this->setRedirect($link);
				return;
			}
		}
		$this->setRedirect($link, JText::_('COM_FABRIK_CONNECTION_SUCESSFUL'));
	}

}
