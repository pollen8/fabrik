<?php
/*
 * @package Joomla.Administrator
 * @subpackage Fabrik
 * @since		1.6
 * @copyright Copyright (C) 2005 - 2010 Rob Clayburn. All rights reserved.
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
class FabrikControllerVisualization extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_VISUALIZATION';

	/**
	 * called via ajax to perform viz ajax task (defined by plugintask method)
	 */

	public function display()
	{
		$document = JFactory::getDocument();
		$id = JRequest::getInt('visualizationid');
		$viz = FabTable::getInstance('Visualization', 'FabrikTable');
		$viz->load($id);
		$modelpaths = JModel::addIncludePath(JPATH_SITE.DS.'plugins'.DS.'fabrik_visualization'.DS.$viz->plugin.DS.'models');
		$model = $this->getModel($viz->plugin);
		$model->setId($id);
		$pluginTask = JRequest::getVar('plugintask', '', 'request');
		if ($pluginTask !== '') {
			echo $model->$pluginTask();
		}
	}

	public function getPluginHTML()
	{
		$plugin = JRequest::getCmd('plugin');
		$model = $this->getModel();
		$model->getForm();
		echo $model->getPluginHTML($plugin);
	}

}