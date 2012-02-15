<?php
/**
 * Plugin element to render fields
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();


class audioRender
{
	var $output = '';
	
	/**
	 * @param object element model
	 * @param object element params
	 * @param string row data for this element
	 * @param object all row's data
	 */

	function renderListData(&$model, &$params, $file, $oAllRowsData)
	{
		$this->render($model, $params, $file);
	}

	/**
	 * @param object element model
	 * @param object element params
	 * @param string row data for this element
	 */

	function render(&$model, &$params, $file)
	{
		$file = str_replace("\\", "/", COM_FABRIK_LIVESITE  . $file);
		$this->output = "<embed src=\"$file\" autostart=\"false\" playcount=\"true\" loop=\"false\" height=\"50\" width=\"200\">";
	}
}

?>