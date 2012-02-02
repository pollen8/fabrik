<?php
/**
 * Form limit submissions plugin
 * @package Joomla
 * @subpackage Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'plugin-form.php');

class plgFabrik_FormLimit extends plgFabrik_Form {

	/**
	 * process the plugin, called when form is loaded
	 * @param object $params
	 * @param object form model
	 * @returns bol
	 */

	function onLoad($params, &$formModel)
	{
		//FabrikHelperHTML::script('administrator/components/com_fabrik/views/namespace.js');
		return $this->_process($params, $formModel);
	}

	private function _process(&$params, &$formModel)
	{
		$user = JFactory::getUser();
		$db = FabrikWorker::getDbo();
		if ($params->get('limit_allow_anonymous')) {
			return true;
		}
		if (JRequest::getCmd('view') === 'details') {
			return true;
		}

		$listid = (int)$params->get('limit_table');
		if ($listid === 0) {
			//use the limit setting supplied in the admin params
			$limit = (int)$params->get('limit_length');
		} else {
			//look up the limit from the table spec'd in the admin params
			$listModel = JModel::getInstance('List', 'FabrikFEModel');
			$listModel->setId($listid);
			$max = $db->NameQuote(FabrikString::shortColName($params->get('limit_max')));
			$userfield = $db->NameQuote(FabrikString::shortColName($params->get('limit_user')));
			$db->setQuery("SELECT $max FROM " . $listModel->getTable()->db_table_name . " WHERE $userfield = " . (int)$user->get('id'));
			$limit = (int)$db->loadResult();

		}
		$field = $params->get('limit_userfield');
		$listModel = $formModel->getlistModel();
		$list = $listModel->getTable();
		$db = $listModel->getDb();
		$db->setQuery("SELECT COUNT($field) FROM $list->db_table_name WHERE $field = " . (int)$user->get('id'));

		$c = $db->loadResult();
		if ($c >= $limit) {
			$msg = $params->get('limit_reached_message');
			$msg = str_replace('{limit}', $limit, $msg);
			JError::raiseNotice(1, $msg);
			return false;
		} else {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('ENTRIES_LEFT_MESSAGE', $limit - $c, $limit));
		}
		return true;
	}

}
?>