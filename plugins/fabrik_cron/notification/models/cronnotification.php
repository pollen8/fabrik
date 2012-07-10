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
require_once COM_FABRIK_FRONTEND . '/models/plugin.php';

class fabrikModelCronnotification extends fabrikModelPlugin {

	function getUserNotifications()
	{
		$user = JFactory::getUser();
		$db = FabrikWorker::getDbo();
		$sql = "SELECT * FROM #__{package}_notification WHERE user_id = " . $user->get('id');
		$db->setQuery($sql);
		$rows = $db->loadObjectList();
		$listModel = JModel::getInstance('list', 'FabrikFEModel');
		foreach ($rows as &$row) {
			/*
			 * {observer_name, creator_name, event, record url
			 * dear %s, %s has %s on %s
			 */
			$event = JText::_($row->event);
			list($listid, $formid, $rowid) = explode('.', $row->reference);

			$listModel->setId($listid);
			$data = $listModel->getRow($rowid);
			$row->url = JRoute::_('index.php?option=com_fabrik&view=details&listid='.$listid.'&formid='.$formid.'&rowid='.$rowid);
			$row->title = $row->url;
			foreach ($data as $key => $value) {
				$k = JString::strtolower(array_pop(explode('___', $key)));
				if ($k == 'title') {
					$row->title = $value;
				}
			}
		}
		return $rows;
	}

	function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('Invalid Token');
		$ids = JRequest::getVar('cid', array());
		JArrayHelper::toInteger($ids);
		$db = FabrikWorker::getDbo();
		$db->setQuery("DELETE FROM #__{package}_notification WHERE id IN (".implode(',', $ids).")");
		$db->query();
	}

}

?>