<?php
/**
 * Execute PHP Code on any list event
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.phpevents
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-list.php';

/**
 * Execute PHP Code on any list event
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.phpevents
 * @since       3.0
 */

class PlgFabrik_ListPhp_Events extends PlgFabrik_List
{
	/**
	 * onFiltersGot method - run after the list has created filters
	 *
	 * @param   object  $params  Plugin params
	 *
	 * @return bol currently ignored
	 */

	public function onFiltersGot($params)
	{
		return $this->doEvaluate($params->get('list_phpevents_onfiltersgot'));
	}

	/**
	 * Called when the list HTML filters are loaded
	 *
	 * @param   object  $params  Plugin params
	 *
	 * @return  void
	 */

	public function onMakeFilters($params)
	{
		return $this->doEvaluate($params->get('list_phpevents_onmakefilters'));
	}

	/**
	 * Do the plug-in action
	 *
	 * @param   object  $params  Plugin parameters
	 * @param   array   $opts    Custom options
	 *
	 * @return  bool
	 */

	public function process($params, $opts = array())
	{
		return $this->doEvaluate($params->get('list_phpevents_process'));
	}

	/**
	 * Run before the list loads its data
	 *
	 * @param   object  $params  Plugin params
	 *
	 * @return  void
	 */

	public function onPreLoadData($params)
	{
		return $this->doEvaluate($params->get('list_phpevents_onpreloaddata'));
	}

	/**
	 * onGetData method
	 *
	 * @param   object  $params  Calling the plugin table/form
	 *
	 * @return bol currently ignored
	 */

	public function onLoadData($params)
	{
		return $this->doEvaluate($params->get('list_phpevents_onloaddata'));
	}

	/**
	 * Called when the model deletes rows
	 *
	 * @param   object  $params  Plugin params
	 *
	 * @return  bool  false if fail
	 */

	public function onDeleteRows($params)
	{
		return $this->doEvaluate($params->get('list_phpevents_ondeleterows'));
	}

	/**
	 * Prep the button if needed
	 *
	 * @param   array   &$args   Arguements
	 *
	 * @return  bool;
	 */

	public function button(&$args)
	{
		parent::button($args);
		return true;
	}

	/**
	 * Build the HTML for the plug-in button
	 *
	 * @return  string
	 */

	public function button_result()
	{
		return '';
	}

	/**
	 * Determine if we use the plugin or not
	 * both location and event criteria have to be match when form plug-in
	 *
	 * @param   string  $location  Location to trigger plugin on
	 * @param   string  $event     Event to trigger plugin on
	 *
	 * @return  bool  true if we should run the plugin otherwise false
	 */

	public function canUse($location = null, $event = null)
	{
		return true;
	}

	/**
	 * Can the plug-in select list rows
	 *
	 * @return  bool
	 */

	public function canSelectRows()
	{
		return false;
	}

	/**
	 * Return the javascript to create an instance of the class defined in formJavascriptClass
	 *
	 * @param   array  $args  Array [0] => string table's form id to contain plugin
	 *
	 * @return bool
	 */

	public function onLoadJavascriptInstance($args)
	{
		return true;
	}

	public function onBuildQueryWhere()
	{
		return $this->doEvaluate($params->get('list_phpevents_onbuildquerywhere'));
	}

	/**
	 * Evaluate supplied PHP
	 *
	 * @param   string  $code    Php code
	 *
	 * @return bool
	 */

	protected function doEvaluate($code)
	{
		$model = $this->getModel();
		$w = new FabrikWorker;
		$code = $w->parseMessageForPlaceHolder($code);
		if ($code != '')
		{
			if (eval($code) === false)
			{
				return false;
			}
		}
		return true;
	}
}
