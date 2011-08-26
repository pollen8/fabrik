<?php

/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005-2011 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models'.DS.'visualization.php');

class fabrikModelApprovals extends FabrikFEModelVisualization {

	function getRows()
	{
		$params =& $this->getParams();
		$ids = (array)$params->get('approvals_table');
		$approveEls = (array)$params->get('approvals_approve_element');
		$titles = (array)$params->get('approvals_title_element');
		$users = (array)$params->get('approvals_user_element');


		$this->rows = array();
		for ($x = 0; $x < count($ids); $x++) {
			$asfields = array();
			$fields= array();
			$listModel = JModel::getInstance('List', 'FabrikFEModel');
			$listModel->setId($ids[$x]);
			$item = $listModel->getTable();
			$formModel = $listModel->getFormModel();
$formModel->getForm();
			$db = $listModel->getDb();
			$query = $db->getQuery(true);
			//$asfields[] = str_replace('___', '.', $approveEls[$x]) . ' AS approve';
			//$asfields[] = str_replace('___', '.', $titles[$x]) . ' AS title';

			$approveEl = $formModel->getElement($approveEls[$x]);
			$approveEl->getAsField_html($asfields, $fields, array('alias'=>'approve'));

			$titleEl = $formModel->getElement($titles[$x]);
			$titleEl->getAsField_html($asfields, $fields, array('alias'=>'title'));

			$userEl = $formModel->getElement($users[$x]);
			$userEl->getAsField_html($asfields, $fields, array('alias'=>'user'));
			//$asfields[] = str_replace('___', '.', $users[$x]) . ' AS user';


			$query->select("'$item->label' AS type, ".$item->db_primary_key.' AS pk, '.implode(',', $asfields))->from($db->nameQuote($item->db_table_name));
			$query = $listModel->_buildQueryJoin($query);
			$query->where(str_replace('___', '.', $approveEls[$x]) .' = 0');
			$db->setQuery($query, 0, 5);
			$rows = $db->loadObjectList();
			if (!$rows) {
				JError::raiseNotice(400, $db->getErrorMsg());
			} else {
				foreach ($rows as &$row) {
					$row->view = 'index.php?option=com_fabrik&task=form.view&formid=.'.$formModel->getId().'&rowid='.$row->pk;
				}
				$this->rows  = array_merge($this->rows, $rows);
			}
		}
		return $this->rows;
	}

}
?>