<?php
/**
 * Abstract Visualization Controller
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

require_once COM_FABRIK_FRONTEND . '/helpers/params.php';
require_once COM_FABRIK_FRONTEND . '/helpers/string.php';

/**
 * Abstract Visualization Controller
 *
 * @static
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       1.5
 */

class FabrikControllerVisualization extends JControllerLegacy
{

	/**
	 * Id used from content plugin when caching turned on to ensure correct element rendered
	 *
	 * @var  int
	 */
	public $cacheId = 0;

	/**
	 * Display the view
	 *
	 * @return  null
	 */

	public function display()
	{
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$input = $app->input;
		$viewName = str_replace('FabrikControllerVisualization', '', get_class($this));
		if ($viewName == '')
		{
			/**
			 * if we are using a url like http://localhost/fabrik3.0.x/index.php?option=com_fabrik&view=visualization&id=6
			 * then we need to ascertain which viz to use
			 */
			$viewName = $this->getViewName();
		}
		$viewType = $document->getType();

		// Set the default view name from the Request
		$view = $this->getView($viewName, $viewType);

		// Push a model into the view
		$model = $this->getModel($viewName);
		if (!JError::isError($model))
		{
			$view->setModel($model, true);
		}
		// Display the view
		$view->assign('error', $this->getError());

		// F3 cache with raw view gives error
		if (in_array($app->get('format'), array('raw', 'csv')))
		{
			$view->display();
		}
		else
		{
			// Build unique cache id on url, post and user id
			$user = JFactory::getUser();
			$uri = JFactory::getURI();
			$uri = $uri->toString(array('path', 'query'));
			$cacheid = serialize(array($uri, $app->post, $user->get('id'), get_class($view), 'display', $this->cacheId));
			$cache = JFactory::getCache('com_fabrik', 'view');
			$cache->get($view, 'display', $cacheid);
		}
	}

	/**
	 * If loading via id then we want to get the view name and add the plugin view and model paths
	 *
	 * @return   string  view name
	 */

	protected function getViewName()
	{
		$viz = FabTable::getInstance('Visualization', 'FabrikTable');
		$app = JFactory::getApplication();
		$viz->load($app->input->getInt('id'));
		$viewName = $viz->plugin;
		$this->addViewPath(JPATH_SITE . '/plugins/fabrik_visualization/' . $viewName . '/views');
		JModelLegacy::addIncludePath(JPATH_SITE . '/plugins/fabrik_visualization/' . $viewName . '/models');
		return $viewName;
	}

	/**
	 * Override of j!'s getView
	 *
	 * Method to get a reference to the current view and load it if necessary.
	 *
	 * @param   string  $name    The view name. Optional, defaults to the controller name.
	 * @param   string  $type    The view type. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for view. Optional.
	 *
	 * @return  object  Reference to the view or an error.
	 */

	public function getView($name = '', $type = '', $prefix = '', $config = array())
	{
		$viewName = str_replace('FabrikControllerVisualization', '', get_class($this));
		$viewName = $viewName == '' ? $this->getViewName() : $name;
		return parent::getView($viewName, $type, $prefix, $config);
	}

}
