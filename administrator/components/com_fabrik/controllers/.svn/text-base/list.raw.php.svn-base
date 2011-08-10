<?php
/*
 * @package Joomla.Administrator
 * @subpackage Fabrik
 * @since		1.6
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * List controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_fabrik
 * @since		1.6
 */
class FabrikControllerList extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_LIST';

	/**
	 * ajax load drop down of all columns in a given table
	 */

	public function ajax_loadTableDropDown()
	{
		JModel::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models');
		$db = FabrikWorker::getDbo();
		$conn = JRequest::getInt('conn', 1);
		$oCnn = JModel::getInstance('Connection', 'FabrikFEModel');
		$oCnn->setId($conn);
		$oCnn->getConnection();
		$db = $oCnn->getDb();
		$table = JRequest::getVar('table', '');
		$fieldNames = array();
		if ($table != '') {
			$table = FabrikString::safeColName($table);
			$name = JRequest::getVar('name', 'jform[params][table_key][]');
			$sql = "DESCRIBE $table";
			$db->setQuery($sql);
			$aFields = $db->loadObjectList();

			if (is_array($aFields)) {
				foreach ($aFields as $oField) {
					$fieldNames[] = JHTML::_('select.option', $oField->Field);
				}
			}
		}
		$fieldDropDown = JHTML::_('select.genericlist', $fieldNames, $name, "class=\"inputbox\"  size=\"1\" ", 'value', 'text', '');
		echo $fieldDropDown;
	}
}
