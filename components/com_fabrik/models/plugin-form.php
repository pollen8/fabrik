<?php

/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

class plgFabrik_Form extends FabrikPlugin
{
	/**@var array formatted email data */
	var $emailData = null;

	/** @var string html to return from plugin rendering */
	protected $html = '';
	
	/**
	 * run from table model when deleting rows
	 *
	 * @return	bool
	 */

	public function onDeleteRowsForm($params, &$formModel, &$groups)
	{
		return true;
	}

	/**
	 * run right at the beginning of the form processing
	 * @return	bool
	 */

	public function onBeforeProcess($params, &$formModel)
	{
		return true;
	}

	/**
	 * run if form validation fails
	 * @return	bool
	 */

	public function onError($params, &$formModel)
	{

	}

	/**
	 * run before table calculations are applied
	 * @param	object	params
	 * @param	object	form model
	 * @return	bool
	 */

	function onBeforeCalculations($params, $formModel)
	{
		return true;
	}

	/**
	 * run right at the end of the form processing
	 * form needs to be set to record in database for this to hook to be called
	 * @param	object	$params
	 * @param	object	form model
	 * @return	bool
	 */

	public function onAfterProcess($params, &$formModel)
	{
		return true;
	}

	/**
	 * alter the returned plugin manager's result
	 *
	 * @param string $method
	 * @return bool
	 */

	public function customProcessResult($method, &$formModel)
	{
		return true;
	}

	/**
	 * sets up any bottom html
	 * @param	object params
	 * @param	object form model
	 */

	public function getBottomContent($params, $formModel)
	{

	}

	/**
	 * get any html that needs to be written into the bottom of the form
	 * @return string html
	 */

	public function getBottomContent_result($c)
	{
		return $this->html;
	}

	/**
	 * sets up any top html
	 * @param	object	params
	 * @param	object	form model
	 */

	function getTopContent($params, $formModel)
	{

	}

	/**
	 * get any html that needs to be written into the top of the form
	 * @return	string	html
	 */

	public function getTopContent_result()
	{
		return $this->html;
	}


	/**
	 * convert the posted form data to the data to be shown in the email
	 * e.g. radio buttons swap their values for the value's label
	 *
	 * HACKED from the form view
	 *
	 * @return array email data
	 */

	function getEmailData()
	{
		if (isset($this->emailData))
		{
			return $this->emailData;
		}
		$model = $this->formModel;
		if (is_null($model->_formDataWithTableName))
		{
			return array();
		}
		$model->isAjax();
		//$$$rob don't render the form - there's no need and it gives a warning about an unfound rowid
		// $$$ rob also it sets teh fromModels rowid to an + int even if we are submitting a new form
		// which means that form plug-ins set to run on new only don't get triggered if they appear after
		// fabrikemail/fabrikreceipt
		//Now instead the pk value is taken from the tableModel->lastInsertId and inserted at the end of this method
		//$model->render();

		$listModel = $model->getListModel();
		$table = is_object($listModel) ? $listModel->getTable() : null;

		$model->_editable = false;
		if (is_object($listModel))
		{
			$joins = $listModel->getJoins();
			$model->getJoinGroupIds($joins);
		}

		$params = $model->getParams();

		$this->emailData = array();

		// $$$ hugh - temp foreach fix
		$groups = $model->getGroupsHiarachy();

		foreach ($groups as $gkey => $groupModel)
		{
			$groupParams = $groupModel->getParams();
			//check if group is acutally a table join
			$repeatGroup = 1;
			$foreignKey = null;
			if ($groupModel->canRepeat())
			{
				if ($groupModel->isJoin())
				{
					$joinModel = $groupModel->getJoinModel();
					$joinTable = $joinModel->getJoin();
					$foreignKey  = '';
					if (is_object($joinTable))
					{
						$foreignKey = $joinTable->table_join_key;
						//need to duplicate this perhaps per the number of times
						//that a repeat group occurs in the default data?
						if (array_key_exists($joinTable->id, $model->_formDataWithTableName['join']))
						{
							$elementModels = $groupModel->getPublishedElements();
							reset($elementModels);
							$tmpElement = current($elementModels);
							$smallerElHTMLName = $tmpElement->getFullName(false, true, false);
							//$repeatGroup = count($model->_data['join'][$joinTable->id][$smallerElHTMLName]);
							$repeatGroup = count($model->_formDataWithTableName['join'][$joinTable->id][$smallerElHTMLName]);
						}
						else
						{
							if (!$groupParams->get('repeat_group_show_first'))
							{
								continue;
							}
						}
					}
				}
				else
				{
					// $$$ rob 19/03/2012 - deprecated?
					// repeat groups which arent joins
					/* $elementModels = $groupModel->getPublishedElements();
					foreach ($elementModels as $tmpElement) {
						$smallerElHTMLName = $tmpElement->getFullName(false, true, false);
						if (is_array($model->_formDataWithTableName)) {
							if (array_key_exists($smallerElHTMLName . '_raw', $model->_formDataWithTableName)) {
								$d = $model->_formDataWithTableName[$smallerElHTMLName . '_raw'];
							} else {
								$d = @$model->_formDataWithTableName[$smallerElHTMLName];
							}
							$d = FabrikWorker::JSONtoData($d, true);
							$c = count($d);
							if ($c > $repeatGroup) { $repeatGroup = $c;}
						}
					} */
				}
			}
			$groupModel->repeatTotal = $repeatGroup;
			$group = $groupModel->getGroup();
			$aSubGroups = array();
			for ($c = 0; $c < $repeatGroup; $c++)
			{
				$aSubGroupElements = array();
				$elementModels = $groupModel->getPublishedElements();
				foreach ($elementModels as $elementModel)
				{
					//force reload?
					$elementModel->defaults = null;
					$elementModel->_repeatGroupTotal = $repeatGroup - 1;
					$element = $elementModel->getElement();

					$k = $elementModel->getFullName(false, true, false);
					$key = $elementModel->getFullName(true, true, false);
					//used for working out if the element should behave as if it was
					//in a new form (joined grouped) even when editing a record
					$elementModel->_inRepeatGroup = $groupModel->canRepeat();
					$elementModel->_inJoin = $groupModel->isJoin();
					$elementModel->_editable = false;

					if ($elementModel->_inJoin)
					{
						if ($elementModel->_inRepeatGroup)
						{
							if (!array_key_exists($k . '_raw', $this->emailData))
							{
								$this->emailData[$k . '_raw'] = array();
							}
							$this->emailData[$k . '_raw'][] = JArrayHelper::getValue($model->_formDataWithTableName['join'][$group->join_id][$k], $c);
						}
						else
						{
							$this->emailData[$k . '_raw'] = $model->_formDataWithTableName['join'][$group->join_id][$k];
						}
					} else {
						//@TODO do we need to check if none -joined repeat groups have their data set out correctly?
						if ($elementModel->isJoin())
						{
							$join = $elementModel->getJoinModel()->getJoin();
							$this->emailData[$k . '_raw'] = $model->_formDataWithTableName['join'][$join->id][$k];
						}
						else if (array_key_exists($key, $model->_formDataWithTableName))
						{
							$rawval = JArrayHelper::getValue($model->_formDataWithTableName, $k . '_raw', '');
							if ($rawval == '')
							{
								$this->emailData[$k . '_raw'] = $model->_formDataWithTableName[$key];
							}
							else
							{
								// things like the user element only have their raw value filled in at this point
								// so don't overwrite that with the blank none-raw value
								// the none-raw value is add in getEmailValue()
								$this->emailData[$k . '_raw'] = $rawval;
							}
						}
					}
					// $$$ hugh - need to poke data into $elementModel->_form->_data as it is needed
					// by CDD getOptions when building the query, to constrain the WHERE clause with
					// selected FK value.

					// $$$ rob in repeat join groups this isnt really efficient as you end up reformatting the data $c times
					$elementModel->getFormModel()->_data = $model->_formDataWithTableName;
					// $$$ hugh - for some reason, CDD keys themselves are missing form emailData, if no selection was made?
					// (may only be on AJAX submit)
					$email_value = '';
					if (array_key_exists($k . '_raw', $this->emailData)) {
						$email_value = $this->emailData[$k . '_raw'];
					}
					else if (array_key_exists($k, $this->emailData))
					{
						$email_value = $this->emailData[$k];
					}
					$this->emailData[$k] = $elementModel->getEmailValue($email_value, $model->_formDataWithTableName, $c);
					if ($elementModel->_inRepeatGroup && $elementModel->_inJoin)
					{
						$this->emailData['join'][$groupModel->getGroup()->join_id][$k.'_raw'] = $this->emailData[$k.'_raw'];
						$this->emailData['join'][$groupModel->getGroup()->join_id][$k] = $this->emailData[$k];
					}
					if ($elementModel->isJoin())
					{
						$this->emailData['join'][$elementModel->getJoinModel()->getJoin()->id][$k . '_raw'] = $this->emailData[$k . '_raw'];
						$this->emailData['join'][$elementModel->getJoinModel()->getJoin()->id][$k] = $this->emailData[$k];
					}
				}
			}
		}
		$pk = FabrikString::safeColNameToArrayKey($listModel->getTable()->db_primary_key);
		$this->emailData[$pk] = $listModel->lastInsertId;
		$this->emailData[$pk . '_raw'] = $listModel->lastInsertId;
		return $this->emailData;
	}

	/**
	 * get a list of admins which should receive emails
	 * @return array admin user objects
	 */

	protected function getAdminInfo()
	{
		$db = JFactory::getDBO(true);
		$query = $db->getQuery();
		$query->select(' id, name, email, sendEmail')
		->from('#__users')
		->where('WHERE sendEmail = "1"');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;
	}

}
?>