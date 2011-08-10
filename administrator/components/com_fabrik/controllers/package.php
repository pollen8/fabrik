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
 * Package controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_fabrik
 * @since		1.6
 */
class FabrikControllerPackage extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_PACKAGE';


	public function export()
	{
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		$model = $this->getModel();
		$model->export($cid);
		$ntext = $this->text_prefix.'_N_ITEMS_EXPORTED';
		$this->setMessage(JText::plural($ntext, count($cid)));
		$this->setRedirect('index.php?option=com_fabrik&view=packages');
	}

}
