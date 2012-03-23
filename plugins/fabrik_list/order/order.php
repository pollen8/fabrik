<?php

/**
 * Allows drag and drop reordering of rows
 * @package Joomla
 * @subpackage Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND . '/models/plugin-list.php');

class plgFabrik_ListOrder extends plgFabrik_List {

	/**
	 * (non-PHPdoc)
	 * @see FabrikModelTablePlugin::getAclParam()
	 */

	function getAclParam()
	{
		return 'order_access';
	}

	/**
	 * determine if the table plugin is a button and can be activated only when rows are selected
	 *
	 * @return	bool
	 */

	function canSelectRows()
	{
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see plgFabrik_List::loadJavascriptClass_result()
	 */
	
	public function loadJavascriptClass_result()
	{
		$src = parent::loadJavascriptClass_result();
		return array($src, 'media/com_fabrik/js/element.js');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see plgFabrik_List::onLoadJavascriptInstance()
	 */

	function onLoadJavascriptInstance($params, $model, $args)
	{
		if (!$this->canUse())
		{
			return;
		}
		
		$orderEl = $model->getFormModel()->getElement($params->get('order_element'), true);
		$form_id = $model->getFormModel()->getId();
		$opts = $this->getElementJSOptions($model);
		$opts->enabled = (count($model->orderEls) === 1 && FabrikString::safeColNameToArrayKey($model->orderEls[0]) == FabrikString::safeColNameToArrayKey($orderEl->getOrderByName())) ? true : false;
		$opts->listid = $model->getId();
		$opts->orderElementId = $params->get('order_element');
		$opts->handle = $params->get('order_element_as_handle', 1) == 1 ? '.' . $orderEl->getOrderByName() : false;
		$opts->direction = $opts->enabled ? $model->orderDirs[0] : '';
		$opts->transition = '';
		$opts->duration = '';
		$opts->constrain = '';
		$opts->clone = '';
		$opts->revert = '';

		$opts = json_encode($opts);
		$this->jsInstance = "new FbListOrder($opts)";
		return true;
	}

	/**
	 * called via ajax when dragged row is dropped. Reorders records
	 */

	public function onAjaxReorder()
	{
		//get table model
		$model = JModel::getInstance('list', 'FabrikFEModel');
		$model->setId(JRequest::getInt('listid'));
		$db = $model->getDb();
		$direction = JRequest::getVar('direction');

		$orderEl = $model->getFormModel()->getElement(JRequest::getInt('orderelid'), true);
		$table = $model->getTable();
		$origOrder = JRequest::getVar('origorder');
		$orderBy =$db->quoteName($orderEl->getElement()->name);
		$order = JRequest::getVar('order');
		$dragged = JRequest::getVar('dragged');

		//are we dragging up or down?
		$origPos = array_search($dragged, $origOrder);
		$newPos = array_search($dragged, $order);
		$dragDirection = $newPos > $origPos ? 'down' : 'up';

		//get the rows whose order has been altered
		$result = array_diff_assoc($order, $origOrder);
		$result = array_flip($result);
		//remove the dragged row from the list of altered rows
		unset($result[$dragged]);

		$result = array_flip($result);

		if (empty($result))
		{
			//no order change
			return;
		}
		//get the order for the last record in $result
		$splitId = $dragDirection == 'up' ? array_shift($result) : array_pop($result);
		$db->setQuery("SELECT " . $orderBy . " FROM " . $table->db_table_name . " WHERE " . $table->db_primary_key . " = " . $splitId);
		$o = (int) $db->loadResult();


		if ($direction == 'desc')
		{
			$compare = $dragDirection == 'down' ? '<' : '<=';
		}
		else
		{
			$compare = $dragDirection == 'down' ? '<=' : '<';
		}
		//shift down the ordered records which have an order less than or equal the newly moved record
		$query = "UPDATE " . $table->db_table_name . " SET " . $orderBy . ' = COALESCE(' . $orderBy . ', 1) - 1 ';
		$query .= " WHERE " . $orderBy . ' ' . $compare . ' ' . $o . ' AND ' . $table->db_primary_key . ' <> '. $dragged;
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $db->getErrorMsg();
		}
		else
		{
			//shift up the ordered records which have an order greater than the newly moved record
			if ($direction == 'desc')
			{
				$compare = $dragDirection == 'down' ? '>=' : '>';
			}
			else
			{
				$compare = $dragDirection == 'down' ? '>' : '>=';
			}

			$query = "UPDATE " . $table->db_table_name . " SET " . $orderBy . ' = COALESCE(' . $orderBy . ', 0) + 1';
			$query .= " WHERE " . $orderBy . ' ' . $compare . ' ' . $o;

			$db->setQuery($query);

			if(!$db->query())
			{
				echo $db->getErrorMsg();
			}
			else
			{
				//change the order of the moved record
				$query = "UPDATE " . $table->db_table_name . " SET " . $orderBy . ' = ' . $o;
				$query .= " WHERE " . $table->db_primary_key . ' = ' . $dragged;
				$db->setQuery($query);
				$db->query();
			}
		}
		$model->reorder(JRequest::getInt('orderelid'));
	}

}
?>