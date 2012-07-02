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

jimport('joomla.application.component.controllerform');

/**
 * Element controller class.
 *
 * @package  Fabrik
 * @since    3.0
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
	 * 
	 * @return  null
	 */

	public function display()
	{
		$document = JFactory::getDocument();
		$id = JRequest::getInt('visualizationid');
		$viz = FabTable::getInstance('Visualization', 'FabrikTable');
		$viz->load($id);
		$modelpaths = JModel::addIncludePath(JPATH_SITE . '/plugins/fabrik_visualization/' . $viz->plugin . '/models');
		$model = $this->getModel($viz->plugin);
		$model->setId($id);
		$pluginTask = JRequest::getVar('plugintask', '', 'request');
		if ($pluginTask !== '')
		{
			echo $model->$pluginTask();
		}
	}

	/**
	 * get html for viz plugin
	 * 
	 * @return  null
	 */

	public function getPluginHTML()
	{
		$plugin = JRequest::getCmd('plugin');
		$model = $this->getModel();
		$model->getForm();
		echo $model->getPluginHTML($plugin);
	}

}
