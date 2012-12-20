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

class FabrikFEModelVisualization extends JModel
{

	var $_pluginParams = null;

	var $_row = null;

	var $_params = null;

	//@var string url for filter form
	var $getFilterFormURL = null;

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
	 * load visualization plugin  params
	 * @access	private - public call = getPluginParams()
	 *
	 * @return	object	visualization plugin parameters
	 */

	function _loadPluginParams()
	{
		$this->getVisualization();
		$pluginParams = new JRegistry($this->getVisualization()->params);
		return $pluginParams;
	}

	/**
	 * get the item
	 *
	 * @return  FabrikTableVisualization
	 */

	public function getVisualization()
	{
		if (!isset($this->_row))
		{
			$this->_row = FabTable::getInstance('Visualization', 'FabrikTable');
			$this->_row->load($this->getState('id'));
			$this->setListIds();
		}
		return $this->_row;
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
	 * Create advanced search links
	 *
	 * @since    3.0.7
	 *
	 * @return   string
	 */

	public function getAdvancedSearchLink()
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$links = array();
		$listModels = $this->getlistModels();
		$js = array();
		$i = 0;
		foreach ($listModels as $listModel)
		{
			$params = $listModel->getParams();
			if ($params->get('advanced-filter', '0'))
			{
				$table = $listModel->getTable();
				$tmpl = $listModel->getTmpl();
				$url = COM_FABRIK_LIVESITE . 'index.php?option=com_' . $package . '&amp;view=list&amp;layout=_advancedsearch&amp;tmpl=component&amp;listid='
					. $table->id . '&amp;nextview=' . $app->input->get('view', 'list')
					. '&scope&amp;=' . $app->scope;

				$url .= '&amp;tkn=' . JSession::getFormToken();
				$links[$table->label] = $url;
			}
		}
		$title = '<span>' . JText::_('COM_FABRIK_ADVANCED_SEARCH') . '</span>';
		$opts = array('alt' => JText::_('COM_FABRIK_ADVANCED_SEARCH'), 'class' => 'fabrikTip', 'opts' => "{notice:true}", 'title' => $title);
		$img = FabrikHelperHTML::image('find.png', 'list', $tmpl, $opts);

		if (count($links) === 1)
		{
			return '<a href="' . array_pop($links) . '" class="advanced-search-link">' . $img . '</a>';
		}
		else
		{
			$str = $img . '<ul>';
			foreach ($links as $label => $url)
			{
				$str .= '<li><a href="' . $url . '" class="advanced-search-link">' . $label . '</a></li>';
			}
			$str = '</ul>';
		}
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
		$input = $app->input;
		$id = $this->getId();

		// Calendar in content plugin - choose event form needs to know its from a content plugin.
		return $input->get('renderContext', $id . '_' . JFactory::getApplication()->scope . '_' . $id);
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
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
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
		if ($option !== 'com_' . $package)
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
		if (is_null($this->_params))
		{
			$v = $this->getVisualization();
			$this->_params = new JRegistry($v->params);
			$this->_params->set('show-title', JRequest::getInt('show-title', $this->_params->get('show-title', 1)));
		}
		return $this->_params;
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
