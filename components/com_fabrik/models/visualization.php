<?php

/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/plugin.php';

/**
 * Fabrik Visualization Model
 *
 * @package  Fabrik
 * @since    3.0
 */

class FabrikFEModelVisualization extends JModelLegacy
{

	protected $pluginParams = null;

	protected $row = null;

	/** @var object params*/
	protected $params = null;

	// @var string url for filter form
	protected $getFilterFormURL = null;

	public $srcBase = "plugins/fabrik_visualization/";

	public $pathBase = null;

	/** @var string js code to ini list filters */
	protected $filterJs = null;

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @since   11.1
	 */

	public function __construct($config = array())
	{
		$this->pathBase = JPATH_SITE . '/plugins/fabrik_visualization/';
		parent::__construct($config);
	}

	/**
	 * Set an array of list id's whose data is used inside the visualaziation
	 *
	 * @return  void
	 */

	protected function setListIds()
	{
		if (!isset($this->listids))
		{
			$this->listids = array();
		}
	}

	function getPluginParams()
	{
		if (!isset($this->_pluginParams))
		{
			$this->_pluginParams = $this->_loadPluginParams();
		}
		return $this->_pluginParams;
	}

	/**
	 * alais to getVisualization()
	 *
	 * @since	3.0.6
	 *
	 * @return  FabTable viz
	 */

	public function getRow()
	{
		return $this->getVisualization();
	}

	/**
	 * get the item
	 *
	 * @return  FabrikTableVisualization
	 */

	public function getVisualization()
	{
		if (!isset($this->row))
		{
			$this->row = FabTable::getInstance('Visualization', 'FabrikTable');
			$this->row->load($this->getState('id'));
			$this->setListIds();
		}
		return $this->row;
	}

	/**
	 * Render the visualization
	 *
	 * @return  void
	 */

	public function render()
	{
		// Overwrite in plugin
	}

	/**
	 * get the visualizations list models
	 *
	 * @return array table objects
	 */

	public function getlistModels()
	{
		if (!isset($this->tables))
		{
			$this->tables = array();
		}
		foreach ($this->listids as $id)
		{
			if (!array_key_exists($id, $this->tables))
			{
				$listModel = JModelLegacy::getInstance('List', 'FabrikFEModel');
				$listModel->setId($id);
				$listModel->getTable();
				$this->tables[$id] = $listModel;
			}
		}
		return $this->tables;
	}

	/**
	 * get a list model
	 *
	 * @param   int  $id  list model id
	 *
	 * @return  object	fabrik list model
	 */

	protected function &getlistModel($id)
	{
		$lists = $this->getlistModels();
		return $lists[$id];
	}

	/**
	 * Make HTML container div id
	 *
	 * @return string
	 */

	public function getContainerId()
	{
		return $this->getJSRenderContext();
	}

	/**
	 * get all list model's filters
	 *
	 * @return array table filters
	 */

	public function getFilters()
	{
		$params = $this->getParams();
		$name = JString::strtolower(str_replace('fabrikModel', '', get_class($this)));
		$filters = array();
		$showFilters = $params->get($name . '_show_filters', array());
		$listModels = $this->getlistModels();
		$js = array();
		$i = 0;
		foreach ($listModels as $listModel)
		{
			$show = (bool) JArrayHelper::getValue($showFilters, $i, true);
			if ($show)
			{
				$ref = $this->getRenderContext();
				$id = $this->getId();
				$filters[$listModel->getTable()->label] = $listModel->getFilters($this->getContainerId(), 'visualization', $id, $ref);
				$js[] = $listModel->filterJs;
			}
			$i++;
		}
		$this->filterJs = implode("\n", $js);
		$this->getRequireFilterMsg();
		return $filters;
	}

	/**
	 * Get the JS code to ini the list filters
	 *
	 * @since   3.0.6
	 *
	 * @return  string  js code
	 */

	public function getFilterJs()
	{
		if (is_null($this->filterJs))
		{
			$this->getFilters();
		}
		return $this->filterJs;
	}

	/**
	 * Get Viz render contenxt
	 *
	 * @since   3.0.6
	 *
	 * @return  string  render context
	 */

	public function getRenderContext()
	{
		$app = JFactory::getApplication();
		$id = $this->getId();
		return $id . '_' . JFactory::getApplication()->scope . '_' . $id;
	}

	/**
	 * Get the JS unique name that is assigned to the viz JS object
	 *
	 * @since   3.0.6
	 *
	 * @return  string  js viz id
	 */

	public function getJSRenderContext()
	{
		return 'visualization_' . $this->getRenderContext();
	}

	/**
	 * set the url for the filter form's action
	 *
	 * @return  string	action url
	 */

	public function getFilterFormURL()
	{
		if (isset($this->getFilterFormURL))
		{
			return $this->getFilterFormURL;
		}
		$app = JFactory::getApplication();
		$input = $app->input;
		$option = $input->get('option');

		// Get the router
		$router = $app->getRouter();

		$uri = clone (JURI::getInstance());
		/**
		 * $$$ rob force these to be 0 once the menu item has been loaded for the first time
		 * subsequent loads of the link should have this set to 0. When the menu item is re-clicked
		 * rest filters is set to 1 again
		 */
		$router->setVar('resetfilters', 0);
		if ($option !== 'com_fabrik')
		{
			// $$$ rob these can't be set by the menu item, but can be set in {fabrik....}
			$router->setVar('clearordering', 0);
			$router->setVar('clearfilters', 0);
		}
		$queryvars = $router->getVars();
		$page = 'index.php?';
		foreach ($queryvars as $k => $v)
		{
			$qs[] = $k . '=' . $v;
		}
		$action = $page . implode("&amp;", $qs);

		// Limitstart gets added in the pageination model
		$action = preg_replace("/limitstart" . $this->getState('id') . "}=(.*)?(&|)/", '', $action);
		$action = FabrikString::rtrimword($action, "&");
		$this->getFilterFormURL = JRoute::_($action);
		return $this->getFilterFormURL;
	}

	/**
	 * Get List Model's Required Filter message
	 *
	 * @return  void
	 */

	protected function getRequireFilterMsg()
	{
		$listModels = $this->getlistModels();
		foreach ($listModels as $model)
		{
			if (!$model->gotAllRequiredFilters())
			{
				JError::raiseNotice(500, $model->getRequiredMsg());
			}
		}
	}

	/**
	 * should be overwritten in plugin viz model
	 *
	 * @return  bool
	 */

	public function getRequiredFiltersFound()
	{
		$listModels = $this->getListModels();
		$filters = array();
		foreach ($listModels as $listModel)
		{
			if (!$listModel->getRequiredFiltersFound())
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * load in any table plugin classes
	 * needed for radius search filter
	 *
	 * @param   array  &$srcs  existing src file
	 *
	 * @return  array	js file paths
	 */

	public function getPluginJsClasses(&$srcs = array())
	{
		$listModels = $this->getListModels();
		foreach ($listModels as $model)
		{
			$paths = $model->getPluginJsClasses($srcs);
		}
		return $srcs;
	}

	/**
	 * get the js code to create instances of js table plugin classes
	 * needed for radius search filter
	 *
	 * @return  string
	 */

	public function getPluginJsObjects()
	{
		$str = array();
		$listModels = $this->getListModels();
		foreach ($listModels as $model)
		{
			$tmp = $model->getPluginJsObjects($this->getContainerId());
			foreach ($tmp as $t)
			{
				$str[] = $t;
			}
		}
		return implode("\n", $str);
	}

	/**
	 * Method to set the table id
	 *
	 * @param   int  $id  viz id
	 *
	 * @return  void
	 */

	public function setId($id)
	{
		$this->setState('id', $id);

		// $$$ rob not sure why but we need this getState() here when assinging id from admin view
		$this->getState();
	}

	/**
	 * Get viz params
	 *
	 * @return  object  params
	 */

	public function getParams()
	{
		if (is_null($this->params))
		{
			$v = $this->getVisualization();
			$app = JFactory::getApplication();
			$input = $app->input;
			$this->params = new JRegistry($v->params);
			$this->params->set('show-title', $input->getInt('show-title', $this->params->get('show-title', 1)));
		}
		return $this->params;
	}

	/**
	 * Get viz id
	 *
	 * @return  int  id
	 */

	public function getId()
	{
		return $this->getState('id');
	}

}
