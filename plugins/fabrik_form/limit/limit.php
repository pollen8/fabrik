<?php
/**
 * Form limit submissions plugin
 * @package     Joomla
 * @subpackage  Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND . '/models/plugin-form.php');

class plgFabrik_FormLimit extends plgFabrik_Form {

	/**
	 * process the plugin, called when form is loaded
* @param   object	$params
* @param   object	form model
	 * @returns	bool
	 */

	function onLoad($params, &$formModel)
	{
		return $this->_process($params, $formModel);
	}

	private function _process(&$params, &$formModel)
	{
		$user = JFactory::getUser();
		$db = FabrikWorker::getDbo();
		$query = $db->getQuery(true);
		if ($params->get('limit_allow_anonymous'))
		{
			return true;
		}
		if (JRequest::getCmd('view') === 'details' || $formModel->getRowId() > 0)
		{
			return true;
		}
		$listid = (int) $params->get('limit_table');
		if ($listid === 0)
		{
			//use the limit setting supplied in the admin params
			$limit = (int) $params->get('limit_length');
		}
		else
		{
			//look up the limit from the table spec'd in the admin params
			$listModel = JModel::getInstance('List', 'FabrikFEModel');
			$listModel->setId($listid);
			$max = $db->quoteName(FabrikString::shortColName($params->get('limit_max')));
			$userfield = $db->quoteName(FabrikString::shortColName($params->get('limit_user')));
			$query->select($max)->from($listModel->getTable()->db_table_name)->where($userfield  . ' = ' . (int) $user->get('id'));
			$db->setQuery($query);
			$limit = (int) $db->loadResult();

		}
		$field = $params->get('limit_userfield');
		$listModel = $formModel->getlistModel();
		$list = $listModel->getTable();
		$db = $listModel->getDb();
		$query->clear()->select(' COUNT(' . $field . ')')->from($list->db_table_name)->where($field . ' = ' . (int) $user->get('id'));
		$db->setQuery($query);

		$c = $db->loadResult();
		if ($c >= $limit)
		{
			$msg = $params->get('limit_reached_message', JText::sprintf('PLG_FORM_LIMIT_LIMIT_REACHED', $limit));
			$msg = str_replace('{limit}', $limit, $msg);
			JError::raiseNotice(1, $msg);
			return false;
		}
		else
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('PLG_FORM_LIMIT_ENTRIES_LEFT_MESSAGE', $limit - $c, $limit));
		}
		return true;
	}

}
?>