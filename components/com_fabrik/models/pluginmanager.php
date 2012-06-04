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
jimport('joomla.filesystem.file');

class FabrikFEModelPluginmanager extends JModel{

	/** @var array plugins */
	var $_plugIns = array();
	var $_loading = null;
	var $_group = null;
	var $_runPlugins = 0;

	var $_paths = array();

	/** @var array element lists */
	var $_elementLists = array();

	/** @var array containing out put from run plugins */
	var $_data = array();

	/**
	 * constructor
	 */

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * get a html drop down list of the elment types with this objs element type selected as default
	 * @param	string	default selected option
	 * @param	string	html name for drop down
	 * @param	string	extra info for drop down
	 * @return	string	html element type list
	 */

	function getElementTypeDd($default, $name='plugin', $extra='class="inputbox elementtype"  size="1"', $defaultlabel='')
	{
		$hash = $default . $name . $extra . $defaultlabel;
		if (!array_key_exists($hash, $this->_elementLists))
		{
			if ($defaultlabel == '')
			{
				$defaultlabel = JText::_('COM_FABRIK_PLEASE_SELECT');
			}
			$a = array(JHTML::_('select.option', '', $defaultlabel));
			$elementstypes = $this->_getList();
			$elementstypes = array_merge($a, $elementstypes);
			$this->_elementLists[$hash] = JHTML::_('select.genericlist', $elementstypes, $name, $extra , 'value', 'text', $default);
		}
		return $this->_elementLists[$hash];
	}

	function canUse()
	{
		return true;
	}

	/**
	 * get an unordered list of plugins
	 * @param	string	plugin group
	 * @param	string	ul id
	 */

	function getList($group, $id)
	{
		$str = '<ul id="' . $id . '">';
		$elementstypes = $this->_getList();
		foreach ($elementstypes as $plugin)
		{
			$str .= '<li>' . $plugin->text . '</li>';
		}
		$str .= '</ul>';
		return $str;
	}

	/**
	 * get a list of plugin ids/names for usin in a drop down list
	 * if no group set defaults to element list
	 * @return	array	plugin list
	 */

	protected function _getList()
	{
		$db = FabrikWorker::getDbo(true);
		if (is_null($this->_group))
		{
			$this->_group = 'element';
		}
		$query = $db->getQuery(true);
		$folder = $db->quote('fabrik_' . $this->_group);
		$query->select('element AS value, name AS text')->from('#__extensions')->where('folder =' . $folder);
		$db->setQuery($query);
		$elementstypes = $db->loadObjectList();
		return $elementstypes;
	}

	/**
	 * get a certain group of plugins
	 * @param	string	plugin group to load
	 * @return	array	plugins
	 */

	function &getPlugInGroup($group)
	{
		if (array_key_exists($group, $this->_plugIns))
		{
			return $this->_plugIns[$group];
		}
		else
		{
			return $this->loadPlugInGroup($group);
		}
	}

	/**
	 * add to the document head all element js files
	 * used in calendar to ensure all element js files are loaded from unserialized form
	 */

	function loadJS()
	{
		JHtml::_('script', 'media/com_fabrik/js/head/head.min.js');
		$plugins = JFolder::folders(JPATH_SITE . '/plugins/fabrik_element', '.', false, false);
		$files = array();
		foreach ($plugins as $plugin)
		{
			$files[] =  JPATH_SITE . '/plugins/fabrik_element/' . $plugin . '/' . $plugin . '.js';
		}
		foreach ($files as $f)
		{
			$f =  str_replace("\\", "/", str_replace(JPATH_SITE, '', $f));
			$file = basename($f);
			$folder = dirname($f);
			$folder = FabrikString::ltrimword($folder, '/') .'/';
			FabrikHelperHTML::script($folder . $file);
		}
	}

	/**
	 *@param	string	plugin type - element/form/table/validationrule supported
	 *loads ABSTRACT version of a plugin group
	 */

	function &loadPlugInGroup($group)
	{
		// $$$ rob 16/12/2011 - this was setting $this->_plugIns, but if you had 2 lists as admin modules
		// and the first list had plugins, then the second list would remove that plugin when this method was run
		$folder = 'fabrik_' . $group;
		$this->_AbstractplugIns[$group] = array();
		$plugins = JPluginHelper::getPlugin($folder);
		foreach ($plugins as $plugin)
		{
			$this->_AbstractplugIns[$group][$plugin->name] = $plugin;
		}
		return $this->_AbstractplugIns[$group];
	}

	/**
	 * @param	string	plugin name e.g. fabrikfield
	 * @param	string	plugin type element/ form or list
	 * @return	object	plugin
	 */

	function getPlugIn($className = '', $group)
	{
		if ($className != '' && (array_key_exists($group, $this->_plugIns) && array_key_exists($className, $this->_plugIns[$group])))
		{
			return $this->_plugIns[$group][$className];
		}
		else
		{
			// $$$ rob 04/06/2011 hmm this was never caching the plugin so we were always loading it
			//return $this->loadPlugIn($className, $group);
			$this->_plugIns[$group][$className] = $this->loadPlugIn($className, $group);
			return $this->_plugIns[$group][$className];
		}
	}

	/**
	 * load in the actual plugin objects for a given group
	 * @param	string	$group
	 * @return	array	plugins
	 */
	
	public function getPlugInGroupPlugins($group)
	{
		$plugins = $this->getPlugInGroup($group);
		$r = array();
		foreach ($plugins as $plugin)
		{
			$r[] = $this->loadPlugIn($plugin->name, $group);
		}
		return $r;
	}
	
	/**
	 * @param	string	plugin name e.g. fabrikfield
	 * @param	string	plugin type element/ form or list
	 * @return	mixed	false if not loaded - otherwise plugin object
	 */

	public function loadPlugIn($className = '', $group)
	{
		if ($group == 'table')
		{
			$group = 'list';
		}
		$group = strtolower($group);
		$ok = JPluginHelper::importPlugin('fabrik_' . strtolower($group));
		$dispatcher = JDispatcher::getInstance();
		if ($className != '')
		{
			if (JFile::exists(JPATH_PLUGINS . '/fabrik_' . $group . '/' . $className . '/' . $className . '.php'))
			{
				require_once(JPATH_PLUGINS . '/fabrik_' . $group . '/' . $className . '/' . $className . '.php');
			}
			else
			{
				if (JFile::exists((JPATH_PLUGINS . '/fabrik_' . $group . '/' . $className . '/models/' . $className . '.php')))
				{
					require_once(JPATH_PLUGINS . '/fabrik_' . $group . '/' . $className . '/models/' . $className . '.php');
				}
				else
				{
					return false;
				}
			}
		}
		$class = 'plgFabrik_' . JString::ucfirst($group) . JString::ucfirst($className);
		$conf = array();
		$conf['name'] = strtolower($className);
		$conf['type'] = strtolower('fabrik_'.$group);
		$plugIn = new $class($dispatcher, $conf);

		//needed for viz
		$client	= JApplicationHelper::getClientInfo(0);
		$lang = JFactory::getLanguage();
		$folder = 'fabrik_' . $group;
		$langFile = 'plg_' . $folder . '_' . $className;
		$langPath = $client->path . '/plugins/' . $folder . '/' . $className;
		$lang->load($langFile, $langPath, null, false, false)
				 ||	$lang->load($langFile, $langPath, $lang->getDefault(), false, false);
		return $plugIn;
	}

	/**
	 * load all the forms element plugins
	 * @param	object	form model
	 * @return	array	of group objects with plugin objects loaded in group->elements
	 */

	function getFormPlugins(&$form)
	{
		$profiler = JProfiler::getInstance('Application');
		if (!isset($this->formplugins))
		{
			$this->formplugins = array();
		}
		$sig = $form->get('id');
		JDEBUG ? $profiler->mark('pluginmanager:getFormPlugins:start - ' . $sig) : null;
		if (!array_key_exists($sig, $this->formplugins))
		{
			$this->formplugins[$sig] = array();
			$lang = JFactory::getLanguage();
			$folder = 'fabrik_element';
			$client	= JApplicationHelper::getClientInfo(0);
			$groupIds = $form->getGroupIds();
			if (empty($groupIds))
			{ //new form
				return array();
			}
			$db = FabrikWorker::getDbo(true);
			$query = $db->getQuery(true);
			$query->select('*, e.name AS name, e.id AS id, e.published AS published, e.label AS label, e.plugin, e.params AS params, e.access AS access, e.ordering AS ordering');
			$query->from('#__{package}_elements AS e');
			$query->join('INNER', '#__extensions AS p ON p.element = e.plugin');
			$query->where('group_id IN (' . implode(',', $groupIds) . ')');
			$query->where('p.folder = "fabrik_element"');
			$query->where('e.published != -2'); // ignore trashed elements
			$query->order("group_id, e.ordering");
			$db->setQuery($query);
			$elements = (array) $db->loadObjectList();
			if ($db->getErrorNum())
			{
				JError::raiseError(500, $db->getErrorMsg());
			}

			//dont assign the elements into Joomla's main dispatcher as this causes out of memory errors in J1.6rc1
			//$dispatcher = JDispatcher::getInstance();
			$dispatcher = new JDispatcher();
			$groupModels = $form->getGroups();
			$group = 'element';
			foreach ($elements as $element)
			{
				JDEBUG ? $profiler->mark('pluginmanager:getFormPlugins:' . $element->id . '' . $element->plugin) : null;
				require_once(JPATH_PLUGINS . '/fabrik_element/' . $element->plugin . '/' . $element->plugin . '.php');
				$class = 'plgFabrik_Element' . $element->plugin;
				$pluginModel = new $class($dispatcher, array());
				if (!is_object($pluginModel))
				{
					continue;
				}
				$pluginModel->_xmlPath = COM_FABRIK_FRONTEND . '/plugins/' . $group . '/' . $element->plugin . '/' . $element->plugin . '.xml';

				$pluginModel->setId($element->id);
				$groupModel = $groupModels[$element->group_id];

				$langFile = 'plg_' . $folder . '_' . $element->plugin;
				$langPath = $client->path . '/plugins/' . $folder . '/' . $element->plugin;
				$lang->load($langFile, $langPath, null, false, false)
				||	$lang->load($langFile, $langPath, $lang->getDefault(), false, false);

				$pluginModel->setContext($groupModel, $form, $form->_table);
				$pluginModel->bindToElement($element);
				$groupModel->elements[$pluginModel->_id] = $pluginModel;
			}
			foreach ($groupModels as $groupid => $g)
			{
				$this->formplugins[$sig][$groupid] = $g;
			}
		}
		return $this->formplugins[$sig];
	}

	function getElementPlugin($id)
	{
		return $this->getPluginFromId($id); 
	}
	
	public function getPluginFromId($id, $type = 'Element') 
	{
		$el = FabTable::getInstance($type, 'FabrikTable');
		$el->load($id);
		$o = $this->loadPlugIn($el->plugin, $type);
		$o->setId($id);
		switch ($type)
		{
			default:
				$o->getTable();
				break;
			case 'Element':
				$o->getElement();
				break;
		}
		return $o;
	}

	/**
	 * @param	string	name of plugin group to load
	 * @param	array	list of default element lists
	 * @param	array	list of default and plugin element lists
	 */

	function loadLists($group, $lists, &$elementModel)
	{
		if (empty($this->_plugIns))
		{
			$this->loadPlugInGroup($group);
		}
		foreach ($this->_plugIns[$group] as $plugIn)
		{
			if (method_exists($plugIn->object, 'getAdminLists'))
			{
				$lists = $plugIn->object->getAdminLists($lists, $elementModel, $plugIn->params);
			}
		}
		return $lists;
	}

	/**
	 * run form & element plugins - yeah!
	 * @param	string	method to check and call - corresponds to stage of form processing
	 * @param	object	model calling the plugin form/table
	 * @param	string	plugin type to call form/table
	 * @return	array	of bools: false if error found and processed, otherwise true
	 */

	function runPlugins($method, &$oRequest, $type = 'form')
	{
		if ($type == 'form')
		{
			// $$$ rob allow for table plugins to hook into form plugin calls - methods are mapped as:
			//form method = 'onLoad' => table method => 'onFormLoad'
			$tmethod = 'onForm' . FabrikString::ltrimword($method, 'on');
			$this->runPlugins($tmethod, $oRequest->getListModel(), 'list');
		}
		$params = $oRequest->getParams();
		$return = array();
		$usedPlugins = (array) $params->get('plugins');
		$usedLocations = (array) $params->get('plugin_locations');
		$usedEvents = (array) $params->get('plugin_events');
		$states = (array) $params->get('plugin_state');
		$this->_data = array();
		if ($type != 'list')
		{
			if (method_exists($oRequest, 'getGroupsHiarachy'))
			{
				$groups = $oRequest->getGroupsHiarachy();
				foreach ($groups as $groupModel)
				{
					$elementModels = $groupModel->getPublishedElements();
					foreach ($elementModels as $elementModel)
					{
						if (method_exists($elementModel, $method))
						{
							$elementModel->$method($oRequest);
						}
					}
				}
			}
		}
		$c = 0;
		$runPlugins = 0;
		// if true then a plugin has returned true from runAway() which means that any other plugin in the same group
		// should not be run.
		$runningAway = false;
		foreach ($usedPlugins as $usedPlugin)
		{
			if ($runningAway)
			{
				// "I soiled my armour I was so scared!"
				break;
			}
			$state = JArrayHelper::getValue($states, $c, 1);
			if ($state == false)
			{
				$c ++;
				continue;
			}
			if ($usedPlugin != '')
			{
				$plugin = $this->getPlugIn($usedPlugin, $type);
				//testing this if statement as onLoad was being called on form email plugin when no method availbale
				$plugin->renderOrder = $c;
				
				if (method_exists($plugin, $method))
				{
					$modelTable = $oRequest->getTable();
					$pluginParams = $plugin->setParams($params, $c);
					$location = JArrayHelper::getValue($usedLocations, $c);
					$event = JArrayHelper::getValue($usedEvents, $c);
					if ($plugin->canUse($oRequest, $location, $event) && method_exists($plugin, $method))
					{
						$pluginArgs = array();
						if (func_num_args() > 3)
						{
							$t = func_get_args();
							$pluginArgs = array_splice($t, 3);
						}
						$preflightMethod = $method . '_preflightCheck';
						$preflightCheck = method_exists($plugin, $preflightMethod) ? $plugin->$preflightMethod($pluginParams, $oRequest, $pluginArgs) : true;
						if ($preflightCheck)
						{
							
							$ok = $plugin->$method($pluginParams, $oRequest, $pluginArgs);
							if ($ok === false)
							{
								$return[] = false;
							}
							else
							{
								$thisreturn = $plugin->customProcessResult($method, $oRequest);
								$return[] = $thisreturn;
								$m = $method . '_result';
								if (method_exists($plugin, $m))
								{
									$this->_data[] = $plugin->$m($c);
								}
							}
							$runPlugins ++;
							if ($plugin->runAway($method))
							{
								$runningAway = true;
							}
							$mainData = $this->_data;
							if ($type == 'list' && $method !== 'observe')
							{
								$this->runPlugins('observe', $oRequest, 'list', $plugin, $method);
							}
							$this->_data = $mainData;
						}
					}
				}
				$c ++;
			}
		}
		
		$this->_runPlugins = $runPlugins;
		return array_unique($return);
	}

	/**
	 * test if a plugin is installed
	 * @param	$group
	 * @param	$plugin
	 * @return	bool
	 */

	function pluginExists($group, $plugin)
	{
		$plugins = $this->loadPlugInGroup($group);
		if (in_array($plugin, array_keys($plugins))) {
			return true;
		}
		return false;
	}

}
?>