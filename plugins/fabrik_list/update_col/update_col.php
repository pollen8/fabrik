<?php

/**
 * Add an action button to the table to update selected columns to a given value
 * @package Joomla
 * @subpackage Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'plugin-list.php');
require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'html.php');

class plgFabrik_ListUpdate_col extends plgFabrik_List
{

	protected $_buttonPrefix = 'update_col';

	protected $_sent = 0;

	protected $_notsent = 0;

	protected $_row_count = 0;

	protected $msg = null;


	function button()
	{
		return "update records";
	}


	protected function buttonLabel()
	{
		return $this->getParams()->get('button_label', parent::buttonLabel());
	}
	
	/**
	 * (non-PHPdoc)
	 * @see FabrikModelTablePlugin::getAclParam()
	 */

	function getAclParam()
	{
		return 'updatecol_access';
	}

	/**
	 * determine if the table plugin is a button and can be activated only when rows are selected
	 *
	 * @return bol
	 */

	function canSelectRows()
	{
		$access = $this->getParams()->get('updatecol_access');
		$name = $this->_getButtonName();
		return FabrikWorker::getACL($access, $name);
	}

	/**
	 * do the plugin action
	 * @param object parameters
	 * @param object table model
	 */

	function process(&$params, &$model, $opts = array())
	{
		$db = $model->getDb();
		$user = JFactory::getUser();
		$update = json_decode($params->get('update_col_updates'));
		if (!$update) {
			return false;
		}

		// $$$ rob moved here from bottom of func see http://fabrikar.com/forums/showthread.php?t=15920&page=7

		$dateCol = $params->get('update_date_element');
		$userCol = $params->get('update_user_element');

		$item = $model->getTable();
		// array_unique for left joined table data
		$ids = array_unique(JRequest::getVar('ids', array(), 'method', 'array'));
		JArrayHelper::toInteger($ids);
		$this->_row_count = count($ids);
		$ids	= implode(',', $ids);
		$model->_pluginQueryWhere[] = $item->db_primary_key . ' IN ( '.$ids.')';
		$data = $model->getData();

		//$$$servantek reordered the update process in case the email routine wants to kill the updates
		$emailColID = $params->get('update_email_element', '');
		if (!empty($emailColID)) {
			$w = new FabrikWorker();
			jimport('joomla.mail.helper');
			$message = $params->get('update_email_msg');
			$subject = $params->get('update_email_subject');
			$eval = $params->get('eval', 0);
			$config = JFactory::getConfig();
			$from = $config->getValue('mailfrom');
			$fromname = $config->getValue('fromname');
			$elementModel = $model->getPluginManager()->getElementPlugin($emailColID);
			$emailElement = $elementModel->getElement(true);
			$emailField = $elementModel->getFullName(false, true, false);
			$emailColumn = $elementModel->getFullName(false, false, false);
			$emailFieldRaw = $emailField . '_raw';
			$emailWhich = $emailElement->plugin == 'user' ? 'user' : 'field';
			$tbl = array_shift(explode('.', $emailColumn));
			$db = JFactory::getDBO();
			$aids = explode(',', $ids);
			// if using a user element, build a lookup list of emails from jos_users,
			// so we're only doing one query to grab all involved emails.
			if ($emailWhich == 'user') {
				$userids_emails = array();
				$query = 'SELECT #__users.id AS id, #__users.email AS email FROM #__users LEFT JOIN ' . $tbl . ' ON #__users.id = ' . $emailColumn . ' WHERE ' . $item->db_primary_key . ' IN ('.$ids.')';
				$db->setQuery($query);
				$results = $db->loadObjectList();
				foreach ($results as $result) {
					$userids_emails[(int)$result->id] = $result->email;
				}
			}
			foreach ($aids as $id) {
				$row = $model->getRow($id);
				if ($emailWhich == 'user') {
					$userid = (int)$row->$emailFieldRaw;
					$to = JArrayHelper::getValue($userids_emails, $userid);
				}
				else {
					$to = $row->$emailField;
				}

				if (JMailHelper::cleanAddress($to) && JMailHelper::isEmailAddress($to)) {
					//$tofull = '"' . JMailHelper::cleanLine($toname) . '" <' . $to . '>';
					//$$$servantek added an eval option and rearranged placeholder call
					$thissubject = $w->parseMessageForPlaceholder($subject, $row);
					$thismessage = $w->parseMessageForPlaceholder($message, $row);
					if ($eval) {
						$thismessage = @eval($thismessage);
						FabrikWorker::logEval($thismessage, 'Caught exception on eval in updatecol::process() : %s');
					}
					$res = JUtility::sendMail($from, $fromname, $to, $thissubject, $thismessage, true);
					if ($res) {
						$this->_sent ++;
					} else {
						$$this->_notsent ++;
					}
				} else {
					$this->_notsent ++;
				}
			}
		}
		//$$$servantek reordered the update process in case the email routine wants to kill the updates
		if (!empty($dateCol)) {
			$date = JFactory::getDate();
			$this->_process($model, $dateCol, $date->toMySQL());
		}

		if (!empty($userCol)) {
			$this->_process($model, $userCol, (int)$user->get('id'));
		}
		foreach ($update->coltoupdate as $i => $col) {
			$this->_process($model, $col, $update->update_value[$i]);
		}
		$this->msg = $params->get('update_message', '');

		if (empty($this->msg)) {
			$this->msg = JText::sprintf('PLG_LIST_UPDATE_COL_UPDATE_MESSAGE', $this->_row_count, $this->_sent);
		} else {
			$this->msg = JText::sprintf($this->msg, $this->_row_count, $this->_sent);
		}
		return true;
	}

	function process_result($c)
	{
		// $$$ rob moved msg processing to process() as for some reason we
		//have incorrect plugin object here (where as php table plugin's process_result()
		//has correct params object - not sure why that is :(
		// $$$ hugh - I think we can move it back now, didn't you decide it was something to do with building the table mode in process()
		// and fix that?
		return $this->msg;
	}

	/**
	 *
	 * @param string table name to update
	 * @param object $model table
	 * @param array $joins objects
	 * @param string $update column
	 * @param string update val
	 */

	private function _process(&$model, $col, $val)
	{
		$ids = JRequest::getVar('ids', array(), 'method', 'array');
		$model->updateRows($ids, $col, $val);
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @param object parameters
	 * @param object table model
	 * @param array [0] => string table's form id to contain plugin
	 * @return bool
	 */

	function onLoadJavascriptInstance($params, $model, $args)
	{
		$opts = new stdClass();
		$opts->name = $this->_getButtonName();
		$opts->listid = $model->getId();
		$opts = json_encode($opts);
		$this->jsInstance = "new FbListUpdateCol($opts)";
		return true;
	}

	function _getColName()
	{
		$params = $this->getParams();
		$col = $params->get('coltoupdate');
		return $col.'-'.$this->renderOrder;
	}

}
?>