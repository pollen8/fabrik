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

class plgFabrik_List extends FabrikPlugin
{

	protected $buttonPrefix = '';

	protected $jsInstance = null;

	/**
	 * get the parameter name that defines the plugins acl access
	 * @return	string
	 */

	function getAclParam()
	{
		return '';
	}

	public function canUse(&$model = null, $location = null, $event = null)
	{
		$aclParam = $this->getAclParam();
		if ($aclParam == '')
		{
			return true;
		}
		$params = $this->getParams();
		$groups = JFactory::getUser()->authorisedLevels();
		return in_array($params->get($aclParam), $groups);
	}
	
	public function canSelectRows()
	{
		return false;
	}
	
	protected function buttonLabel()
	{
		$s = JString::strtoupper($this->buttonPrefix);
		return JText::_('PLG_LIST_' . $s . '_' . $s);
	}
	
	public function button_result()
	{
		if ($this->canUse())
		{
			$p = $this->onGetFilterKey_result();
			FabrikHelperHTML::addPath('plugins/fabrik_list/' . $p . '/images/', 'image','list');
			$name = $this->_getButtonName();
			$label = $this->buttonLabel();
			$imageName = $this->getParams()->get('list_' . $this->buttonPrefix . '_image_name', $this->buttonPrefix . '.png');
			$img = FabrikHelperHTML::image($imageName, 'list', '',  $label);
			return '<a href="#" class="' . $name . ' listplugin" title="' . $label . '">' . $img . '<span>' . $label . '</span></a>';
		}
		return '';
	}
	
	public function getElementJSOptions($model)
	{
		$opts = new stdClass();
		$opts->ref = $model->getRenderContext();
		$opts->name = $this->_getButtonName();
		$opts->listid = $model->getId();
		return $opts;
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @param	object	parameters
	 * @param	object list model
	 * @param	array	[0] => string table's form id to contain plugin
	 * @return	bool
	 */

	function onLoadJavascriptInstance($params, $model, $args)
	{
		JText::script('COM_FABRIK_PLEASE_SELECT_A_ROW');
		return true;
	}

	/**
	 * onGetData method
	 *
	 * @param object calling the plugin table/form
	 * @return bol currently ignored
	 */

	function onLoadData(&$params, &$oRequest)
	{
		return true;
	}

	/**
	 * onFiltersGot method - run after the table has created filters
	 *
	 * @param object calling the plugin table/form
	 * @return bol currently ignored
	 */

	function onFiltersGot(&$params, &$oRequest)
	{
		return true;
	}

	/**
	 * provide some default text that most table plugins will need
	 * (this object will then be json encoded by the plugin and passed
	 * to it's js class
	 * @return object language
	 * @depreciated since 3.0
	 */

	function _getLang()
	{
		$lang = new stdClass();
		return $lang;
	}

 	/**
 	 * get the html name for the button
 	 * @return	string
 	 */

	function _getButtonName()
	{
		return $this->buttonPrefix . '-' . $this->renderOrder;
	}

	/**
	 * prefilght check to ensure that the list plugin should process
	 * @param	object	$params
	 * @param	object	$model
	 * @return	string|boolean
	 */

	function process_preflightCheck(&$params, &$model)
	{
		if ($this->buttonPrefix == '')
		{
			return false;
		}
		$postedRenderOrder = JRequest::getInt('fabrik_listplugin_renderOrder', -1);
		return JRequest::getVar('fabrik_listplugin_name') == $this->buttonPrefix && $this->renderOrder == $postedRenderOrder;
	}

	/**
	 * get a key name specific to the plugin class to use as the reference
	 * for the plugins filter data
	 * (Normal filter data is filtered on the element id, but here we use the plugin name)
	 * @return	string	key
	 */

	public function onGetFilterKey()
	{
		$this->filterKey = JString::strtolower(str_replace('plgFabrik_List', '', get_class($this)));
		return true;
	}

	public function onGetFilterKey_result()
	{
		if (!isset($this->filterKey))
		{
			$this->onGetFilterKey();
		}
		return $this->filterKey;
	}

	/**
	 * plugins should use their own name space for storing their sesssion data
	 * e.g radius search plugin stores its search values here
	 */

	protected function getSessionContext()
	{
		return 'com_fabrik.list' . $this->model->getRenderContext() . '.plugins.' . $this->onGetFilterKey() . '.';
	}

	/**
	 * used to assign the js code created in onLoadJavascriptInstance()
	 * to the table view.
	 * @return	string	javascript to create instance. Instance name must be 'el'
	 */

	function onLoadJavascriptInstance_result()
	{
		return $this->jsInstance;
	}

	/**
	 * allows to to alter the table's select query
	 * @param	object	$params
	 * @param	object	table model
	 * @param	array	arguements - first value is an object with a query
	 * property which contains the current query:
	 * $args[0]->query
	 */

	public function onQueryBuilt(&$params, &$model, &$args)
	{

	}

	/**
	 * load the javascript class that manages plugin interaction
	 * should only be called once
	 * @return	string	javascript class file
	 */

	public function loadJavascriptClass()
	{
		return true;
	}
	
	public function loadJavascriptClass_result()
	{
		$this->onGetFilterKey();
		$p = $this->onGetFilterKey_result();
		return 'plugins/fabrik_list/' . $p . '/' . $p . '.js';
	}

}
?>