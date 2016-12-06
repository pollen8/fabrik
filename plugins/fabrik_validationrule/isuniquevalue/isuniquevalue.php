<?php
/**
 * Is Unique Value Validation Rule
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.validationrule.isuniquevalue
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/validation_rule.php';

/**
 * Is Unique Value Validation Rule
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.validationrule.isuniquevalue
 * @since       3.0
 */
class PlgFabrik_ValidationruleIsUniqueValue extends PlgFabrik_Validationrule
{
	/**
	 * Plugin name
	 *
	 * @var string
	 */
	protected $pluginName = 'isuniquevalue';

	/**
	 * Validate the elements data against the rule
	 *
	 * @param   string $data          To check
	 * @param   int    $repeatCounter Repeat group counter
	 *
	 * @return  bool  true if validation passes, false if fails
	 */
	public function validate($data, $repeatCounter)
	{
		$input        = $this->app->input;
		$elementModel = $this->elementModel;

		// Could be a drop-down with multi-values
		if (is_array($data))
		{
			$data = implode('', $data);
		}

		$params      = $this->getParams();
		$element     = $elementModel->getElement();
		$listModel   = $elementModel->getlistModel();
		$table       = $listModel->getTable();
		$db          = $listModel->getDb();
		//$lookupTable = $db->qn($table->db_table_name);
		$data        = $db->q($data);
		$query       = $db->getQuery(true);
		$cond        = $params->get('isuniquevalue-caseinsensitive') == 1 ? 'LIKE' : '=';
		$secret      = $this->config->get('secret');

		$groupModel = $elementModel->getGroup();

		// if it's a join, get the joined table name
		if ($groupModel->isJoin())
		{
			$lookupTable = $groupModel->getJoinModel()->getJoin()->table_join;
		}
		else
		{
			$lookupTable = $table->db_table_name;
		}

		if ($elementModel->encryptMe())
		{
			$k = 'AES_DECRYPT(' . $element->name . ', ' . $db->q($secret) . ')';
		}
		else
		{
			$k = $db->qn($element->name);
		}

		$query->select('COUNT(*)')->from($db->qn($lookupTable))->where($k . ' ' . $cond . ' ' . $data);

		/*
		 * $$$ hugh - need to check to see if we're editing a record, so we can exclude this record
		 *
		 * Need to figure out if this is a joined element, and set PK and 'rowid' accordingly
		 *
		 * NOTE - probably only works for non-repeat joins
		 */

		if (!$groupModel->isJoin())
		{
			// not a join, just use rowid and normal pk
			$rowId = $input->get('rowid', '');
			$pk = $table->db_primary_key;
		}
		else
		{
			// join, so get the join's PK and value as rowid
			$pk = $groupModel->getJoinModel()->getForeignID();
			$rowId = $this->formModel->formData[$pk];
			$pk = $groupModel->getJoinModel()->getForeignID('.');
		}

		if (!empty($rowId))
		{
			$query->where($db->qn($pk) . ' != ' . $db->q($rowId));
		}

		$db->setQuery($query);
		$c = $db->loadResult();

		return ($c === '0') ? true : false;
	}
}
