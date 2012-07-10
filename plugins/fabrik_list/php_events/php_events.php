<?php
/**
 * Execute PHP Code on any list event
 * @package Joomla
 * @subpackage Fabrik
 * @author Mauro H. Leggieri
 * @copyright (C) Mauro H. Leggieri
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once(COM_FABRIK_FRONTEND . '/models/plugin-list.php');

class plgFabrik_ListPhp_Events extends plgFabrik_List
{
	/**
	 * called when the active table filter array is loaded
	 * @param	object	params
	 * @param	object	model
	 */
	
	function onFiltersGot(&$params, &$model)
	{
		return $this->doEvaluate($params->get('list_phpevents_onfiltersgot'), $model);
	}

	/**
	 * called when the table HTML filters are loaded
	 * @param	object	params
	 * @param	object	model
	 */
	
	function onMakeFilters(&$params, &$model)
	{
		return $this->doEvaluate($params->get('list_phpevents_onmakefilters'), $model);
	}

	/**
	 * do the plugin action
	 * @param object table model
	 * @return string message
	 */
	
	function process(&$params, &$model)
	{
		return $this->doEvaluate($params->get('list_phpevents_process'), $model);
	}

	/**
	 * run before the table loads its data
	 * @param	object	parmas
	 * @param	object	$model
	 * @return	bool
	 */
	
	function onPreLoadData(&$params, &$model)
	{
		return $this->doEvaluate($params->get('list_phpevents_onpreloaddata'), $model);
	}

	/**
	 * run when the list loads its data(non-PHPdoc)
	 * @param	object	params
	 * @param	object	model
	 */
	
	function onLoadData(&$params, &$model)
	{
		return $this->doEvaluate($params->get('list_phpevents_onloaddata'), $model);
	}

	/**
	 * called when the model deletes rows
	 * @param	object	list $model
	 * @return	false	if fail
	 */

	function onDeleteRows(&$params, &$model)
	{
		return $this->doEvaluate($params->get('list_phpevents_ondeleterows'), $model);
	}

	/* ---------------------------------------------------- */

	function button()
	{
		return "php events";
	}

	public function button_result()
	{
		return '';
	}

	function canUse(&$model = null, $location = null, $event = null)
	{
		return true;
	}

	function canSelectRows()
	{
		return false;
	}

	function onLoadJavascriptInstance($params, $model, $args)
	{
		return true;
	}

	/**
	 * evaluate supplied PHP
	 * @param	string	$code
	 * @param	object	$model
	 * @return boolean
	 */

	protected function doEvaluate($code, &$model)
	{
		$w = new FabrikWorker();
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
?>
