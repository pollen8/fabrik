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
 * @package		Joomla.Administrator
 * @subpackage	Fabrik
 * @since		3.0
 */

class FabrikAdminControllerVisualization extends JControllerForm
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var		string
	 */

	protected $text_prefix = 'COM_FABRIK_VISUALIZATION';

	/**
	 * Called via ajax to perform viz ajax task (defined by plugintask method)
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  A JController object to support chaining.
	 */

	public function display($cachable = false, $urlparams = false)
	{
		$document = JFactory::getDocument();
		$id = JRequest::getInt('visualizationid');
		$viz = FabTable::getInstance('Visualization', 'FabrikTable');
		$viz->load($id);
		$modelpaths = JModelLegacy::addIncludePath(JPATH_SITE . '/plugins/fabrik_visualization/' . $viz->plugin . '/models');
		$model = $this->getModel($viz->plugin);
		$model->setId($id);
		$pluginTask = JRequest::getVar('plugintask', '', 'request');
		if ($pluginTask !== '')
		{
			echo $model->$pluginTask();
		}
		return $this;
	}

	/**
	 * Get html for viz plugin
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
