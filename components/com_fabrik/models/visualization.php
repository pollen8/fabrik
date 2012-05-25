<?php

/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once(JPATH_SITE . '/components/com_fabrik/models/plugin.php');

class FabrikFEModelVisualization extends JModel
{

	var $_pluginParams = null;

	var $_row = null;

	var $_params = null;

	//@var string url for filter form
	var $getFilterFormURL = null;

	public $srcBase = "plugins/fabrik_visualization/";

	public $pathBase = null;

	function __construct()
	{
		$this->pathBase = JPATH_SITE . '/plugins/fabrik_visualization/';
		parent::__construct();
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
	 * load visualization plugin  params
	 * @access	private - public call = getPluginParams()
	 *
	 * @return	object	visualization plugin parameters
	 */

	function _loadPluginParams()
	{
		$this->getVisualization();
		$pluginParams = new fabrikParams($this->_row->params);
		return $pluginParams;
	}

	function getVisualization()
	{
		if (!isset($this->_row))
		{
			$this->_row = FabTable::getInstance('Visualization', 'FabrikTable');
			$this->_row->load($this->getState('id'));
		}
		return $this->_row;
	}

	function render()
	{
		//overwrite in plugin
	}

	/**
	 * get the vizualizations table models
	 *
	 * @return array table objects
	 */

	function getlistModels()
	{
		if (!isset($this->tables))
		{
			$this->tables = array();
		}
		foreach ($this->listids as $id)
		{
			if (!array_key_exists($id, $this->tables))
			{
				$listModel = JModel::getInstance('List', 'FabrikFEModel');
				$listModel->setId($id);
				$listModel->getTable();
				$this->tables[$id] = $listModel;
			}
		}
		return $this->tables;
	}

	/**
	 * get a list model
	 * @param	int		$id
	 * @return	object	fabrik list model
	 */

	protected function &getlistModel($id)
	{
		$lists = $this->getlistModels();
		return $lists[$id];
	}

	function getGalleryTableId()
	{
		$params = $this->getParams();
		return $params->get('gallery_category_table');
	}

	function getContainerId()
	{
		$viz = $this->getVisualization();
		return $viz->plugin . '_' . $viz->id;
	}
	/**
	 * get all table models filters
	 * @return array table filters
	 */

	function getFilters()
	{
		$params = $this->getParams();
		$name = strtolower(str_replace('fabrikModel', '', get_class($this)));
		$filters = array();
		$showFilters = $params->get($name . '_show_filters', array());
		$listModels = $this->getlistModels();
		$i = 0;
		foreach ($listModels as $listModel)
		{
			$show = (bool)JArrayHelper::getValue($showFilters, $i, true);
			if ($show)
			{
				$filters[$listModel->getTable()->label] = $listModel->getFilters($this->getContainerId(), 'vizualization', $this->getVisualization()->id);
			}
			$i ++;
		}
		$this->getRequireFilterMsg();
		return $filters;
	}

	/**
	 * set the url for the filter form's action
	 * @return string action url
	 */

	public function getFilterFormURL()
	{
		if (isset($this->getFilterFormURL))
		{
			return $this->getFilterFormURL;
		}
		$option = JRequest::getCmd('option');
		// Get the router
		$app = JFactory::getApplication();
		$router = $app->getRouter();

		$uri = clone(JURI::getInstance());
		// $$$ rob force these to be 0 once the menu item has been loaded for the first time
		//subsequent loads of the link should have this set to 0. When the menu item is re-clicked
		//rest filters is set to 1 again
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
		//limitstart gets added in the pageination model
		$action = preg_replace("/limitstart" . $this->getState('id') . "}=(.*)?(&|)/", '', $action);
		$action = FabrikString::rtrimword($action, "&");
		$this->getFilterFormURL	= JRoute::_($action);
		return $this->getFilterFormURL;
	}

	function getRequireFilterMsg()
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
	 * @abstract
	 */

	function getRequiredFiltersFound()
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
	 * @param	array	existing src file
	 * @return	array	js file paths
	 */

	function getPluginJsClasses(&$srcs = array())
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
	 */

	function getPluginJsObjects()
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
	 * @access	public
	 * @param	int	table ID number
	 */

	function setId($id)
	{
		$this->setState('id', $id);
		// $$$ rob not sure why but we need this getState() here
		// when assinging id from admin view
		$this->getState();
	}

	function getParams()
	{
		if (is_null($this->_params))
		{
			$v = $this->getVisualization();
			$this->_params = new fabrikParams($v->params);
			$this->_params->set('show-title', JRequest::getInt('show-title', $this->_params->get('show-title', 1)));
		}
		return $this->_params;
	}

	function getId()
	{
		return $this->getState('id');
	}

}
?>