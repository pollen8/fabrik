<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.thumbs
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';

/**
 * Plugin element to render thumbs-up/down widget
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.thumbs
 */

class plgFabrik_ElementThumbs extends plgFabrik_Element
{

	/**
	 * (non-PHPdoc)
	 * @see plgFabrik_Element::renderListData()
	 */

	public function renderListData($data, &$thisRow)
	{
		$params = $this->getParams();
		$imagepath = COM_FABRIK_LIVESITE . '/plugins/fabrik_element/thumbs/images/';
		//$data = explode(GROUPSPLITTER, $data);
		$data = FabrikWorker::JSONtoData($data, true);
		$listid = $this->getlistModel()->getTable()->id;
		$formid = $this->getlistModel()->getTable()->form_id;
		$row_id = $thisRow->__pk_val;
		$str = '';
		for ($i = 0; $i < count($data); $i++)
		{
			JRequest::setVar('rowid', $row_id);
			$myThumb = $this->_getMyThumb($listid, $formid, $row_id);
			$imagefileup = 'thumb_up_out.gif';
			$imagefiledown = 'thumb_down_out.gif';
			if ($myThumb == 'up')
			{
				$imagefileup = 'thumb_up_in.gif';
				$imagefiledown = 'thumb_down_out.gif';
			}
			else if ($myThumb == 'down')
			{
				$imagefileup = 'thumb_up_out.gif';
				$imagefiledown = 'thumb_down_in.gif';
			}
			$count = $this->_renderListData($data[$i], $thisRow);
			//$count = explode(GROUPSPLITTER2, $count);
			$count = FabrikWorker::JSONtoData($count, true);
			$countUp = $count[0];
			$countDown = $count[1];
			$countDiff = $countUp - $countDown;
			$str .= "<span style='color:#32d723;' id='count_thumbup$row_id'>$countUp</span><img src='$imagepath"
				. "$imagefileup' style='padding:0px 5px 0 1px;' alt='UP' class='thumbup' id='thumbup$row_id'/>";
			$str .= "<span style='color:#f82516;' id='count_thumbdown$row_id'>$countDown</span><img src='$imagepath"
				. "$imagefiledown' style='padding:0px 5px 0 1px;' alt='DOWN' class='thumbdown' id='thumbdown$row_id'/>";
			//$str .= "</div>";
			$data[$i] = $str;
		}
		//$data = implode(GROUPSPLITTER, $data);
		$data = json_encode($data);

		return parent::renderListData($data, $thisRow);
	}

	private function _renderListData($data, $thisRow)
	{
		$params = $this->getParams();
		$user = JFactory::getUser();
		$list = $this->getlistModel()->getTable();
		$listid = $list->id;
		$formid = $list->form_id;
		$row_id = isset($thisRow->__pk_val) ? $thisRow->__pk_val : $thisRow->id;
		$db = FabrikWorker::getDbo();
		return $this->getThumbsCount($data, $listid, $formid, $row_id);

	}

	/**
	 *
	 * @param $listid int table id
	 * @param $formid int form id
	 * @param $row_id int row id
	 * @return count thumbs-up, count thumbs-down
	 */

	function getThumbsCount($data, $listid, $formid, $row_id)
	{
		/*if ($data != '') {
		    return $data;
		    }*/
		$db = FabrikWorker::getDbo();
		$elementid = $this->getElement()->id;

		$db
			->setQuery(
				"SELECT COUNT(thumb) FROM #__{package}_thumbs WHERE listid = " . (int) $listid . " AND formid = " . (int) $formid . " AND row_id = "
					. (int) $row_id . " AND element_id = " . (int) $elementid . " AND thumb = 'up'");
		$resup = $db->loadResult();
		$db
			->setQuery(
				"SELECT COUNT(thumb) FROM #__{package}_thumbs WHERE listid = " . (int) $listid . " AND formid = " . (int) $formid . " AND row_id = "
					. (int) $row_id . " AND element_id = " . (int) $elementid . " AND thumb = 'down'");
		$resdown = $db->loadResult();
		return json_encode(array($resup, $resdown));
	}

	/**
	 * determines if the element can contain data used in sending receipts, e.g. field returns true
	 */

	function isReceiptElement()
	{
		return true;
	}

	/**
	 * @since 3.0 - no point showing label if you cant edit the thing
	 */

	function isHidden()
	{
		return JRequest::getVar('view') == 'form' ? true : false;
	}

	/**
	 * draws the form element
	 * @param array data to preopulate element with
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$params = $this->getParams();
		if (JRequest::getVar('view') == 'form')
		{
			return '';//JText::_('PLG_ELEMENT_THUMBS_ONLY_ACCESSIBLE_IN_DETALS_VIEW');
		}
		$element = $this->getElement();

		$value = $this->getValue($data, $repeatCounter);
		$type = ($params->get('password') == "1") ? "password" : "text";
		if (isset($this->_elementError) && $this->_elementError != '')
		{
			$type .= " elementErrorHighlight";
		}
		$imagepath = COM_FABRIK_LIVESITE . '/plugins/fabrik_element/thumbs/images/';

		$str = "<div id=\"$id" . "_div\" class=\"fabrikSubElementContainer\">";
		$listid = $this->getlistModel()->getTable()->id;
		$formid = JRequest::getInt('formid');
		$row_id = JRequest::getInt('rowid');
		if (!isset($thisRow))
		{
			$thisRow = new stdClass();
			$thisRow->__pk_val = $row_id;
		}
		$myThumb = $this->_getMyThumb($listid, $formid, $row_id);
		$imagefileup = 'thumb_up_out.gif';
		$imagefiledown = 'thumb_down_out.gif';
		if ($myThumb == 'up')
		{
			$imagefileup = 'thumb_up_in.gif';
			$imagefiledown = 'thumb_down_out.gif';
		}
		else if ($myThumb == 'down')
		{
			$imagefileup = 'thumb_up_out.gif';
			$imagefiledown = 'thumb_down_in.gif';
		}
		$id2 = FabrikString::rtrimword($id, '_ro');
		$count = $this->_renderListData($data[$id2], $thisRow);
		$count = FabrikWorker::JSONtoData($count, true);
		$countUp = $count[0];
		$countDown = $count[1];
		$countDiff = $countUp - $countDown;
		$str .= "<span style='color:#32d723;' id='count_thumbup'>$countUp</span><img src='$imagepath"
			. "$imagefileup' style='padding:0px 5px 0 1px;' alt='UP' id='thumbup'/>";
		$str .= "<span style='color:#f82516;' id='count_thumbdown'>$countDown</span><img src='$imagepath"
			. "$imagefiledown' style='padding:0px 5px 0 1px;' alt='DOWN' id='thumbdown'/>";
		$str .= "<input type=\"hidden\" name=\"$name\" id=\"$id\" value=\"$countDiff\" class=\"$id\" />\n";
		$str .= "</div>";
		return $str;
	}

	/**
	 * (non-PHPdoc)
	 * @see components/com_fabrik/models/plgFabrik_Element#storeDatabaseFormat($val, $data)
	 */

	function storeDatabaseFormat($val, $data)
	{
		$params = $this->getParams();
		$listid = JRequest::getInt('listid');
		$formid = JRequest::getInt('formid');
		$row_id = JRequest::getInt('rowid');
		if ($params->get('rating-mode') != 'creator-rating')
		{
			//$val = $this->getRatingAverage($val, $listid, $formid, $row_id);
		}
		return $val;
	}

	function _getMyThumb($listid, $formid, $row_id)
	{
		$db = FabrikWorker::getDbo();
		$elementid = $this->getElement()->id;
		$user = JFactory::getUser();
		$user_id = $user->get('id');
		if ($user_id == 0)
		{
			$user_id = $this->getCookieName($listid, $row_id);
		}
		$db
			->setQuery(
				"SELECT thumb FROM #__{package}_thumbs WHERE listid = " . (int) $listid . " AND formid = " . (int) $formid . " AND row_id = "
					. (int) $row_id . " AND element_id = " . (int) $elementid . " AND user_id = '$user_id' LIMIT 1");
		$ret = $db->loadResult();

		return $ret;
	}

	/**
	 * called via widget ajax, stores the selected thumb
	 * stores the diff (thumbs-up minus thumbs-down)
	 * return the new count for up and down
	 */

	function onAjax_rate()
	{
		$listid = JRequest::getInt('listid');
		$list = JModel::getInstance('list', 'FabrikFEModel');
		$list->setId($listid);
		$this->setId(JRequest::getInt('element_id'));
		$formid = $list->getFormModel()->getId();
		$row_id = JRequest::getVar('row_id');
		$thumb = JRequest::getVar('thumb');
		$this->doThumb($listid, $formid, $row_id, $thumb);
		echo $this->getThumbsCount('', $listid, $formid, $row_id);
	}

	private function getCookieName($listid, $row_id)
	{
		$cookieName = "thumb-table_{$listid}_row_{$row_id}_ip_{$_SERVER['REMOTE_ADDR']}";
		jimport('joomla.utilities.utility');
		return JUtility::getHash($cookieName);
	}

	/**
	 * main method to store a rating
	 * @param $listid
	 * @param $formid
	 * @param $row_id
	 * @param $thumb
	 */

	private function doThumb($listid, $formid, $row_id, $thumb)
	{
		$db = FabrikWorker::getDbo();
		$config = JFactory::getConfig();
		$tzoffset = $config->getValue('config.offset');
		$date = JFactory::getDate('now', $tzoffset);
		$strDate = $db->quote($date->toMySQL());

		$user = JFactory::getUser();
		$userid = (int) $user->get('id');

		if ($userid == 0)
		{

			$hash = $this->getCookieName($listid, $row_id);

			//set cookie
			$lifetime = time() + 365 * 24 * 60 * 60;
			setcookie($hash, '1', $lifetime, '/');
			$userid = $db->quote($hash);
		}
		$elementid = $this->getElement()->id;
		$db
			->setQuery(
				"INSERT INTO #__{package}_thumbs (user_id, listid, formid, row_id, thumb, date_created, element_id)
		values ($userid, $listid, $formid, $row_id, " . $db->quote($thumb)
					. ", $strDate, $elementid)
			ON DUPLICATE KEY UPDATE date_created = $strDate, thumb = " . $db->quote($thumb));
		$db->query();
		if ($db->getErrorNum())
		{
			$err = new stdClass();
			$err->error = $db->getErrorMsg();
			echo json_encode($err);
			exit;
		}
		$this->updateDB($listid, $formid, $row_id, $elementid);

	}

	private function updateDB($listid, $formid, $row_id, $elementid)
	{
		$db = FabrikWorker::getDbo();

		$db
			->setQuery(
				"UPDATE " . $this->getlistModel()->getTable()->db_table_name . "
                    SET " . $this->getElement()->name . " = ((SELECT COUNT(thumb) FROM #__{package}_thumbs WHERE listid = " . (int) $listid
					. " AND formid = " . (int) $formid . " AND row_id = " . (int) $row_id . " AND element_id = " . (int) $elementid
					. " AND thumb = 'up') - (SELECT COUNT(thumb) FROM #__{package}_thumbs WHERE listid = " . (int) $listid . " AND formid = "
					. (int) $formid . " AND row_id = " . (int) $row_id . " AND element_id = " . (int) $elementid
					. " AND thumb = 'down'))
                    WHERE " . $this->getlistModel()->getTable()->db_primary_key . " = " . (int) $row_id . "
                        LIMIT 1");
		$db->query();
		if ($db->getErrorNum())
		{
			$err = new stdClass();
			$err->error = $db->getErrorMsg();
			echo json_encode($err);
			exit;
		}
		return true;
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @param int repeat group counter
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$user = JFactory::getUser();
		$params = $this->getParams();
		if (JRequest::getVar('view') == 'form')
		{
			return;
		}
		$id = $this->getHTMLId($repeatCounter);
		$element = $this->getElement();
		$data = &$this->_form->_data;
		$listid = $this->getlistModel()->getTable()->id;
		$formid = JRequest::getInt('formid');
		$row_id = JRequest::getInt('rowid');
		$value = $this->getValue($data, $repeatCounter);

		$opts = new stdClass();
		$opts->row_id = JRequest::getInt('rowid');
		$opts->myThumb = $this->_getMyThumb($listid, $formid, $row_id);
		$opts->elid = $this->getElement()->id;
		$opts->userid = (int) $user->get('id');
		$opts->mode = $params->get('rating-mode');
		$opts->view = JRequest::getCmd('view');
		$opts->listid = $listid;
		$opts = json_encode($opts);

		$lang = new stdClass();
		$lang->norating = JText::_('NO RATING');
		$lang = json_encode($lang);
		$str = "new FbThumbs('$id', $opts, '$value', $lang)";
		return $str;
	}

	/**
	 * get js to ini js object that manages the behaviour of the thumbs element (non-PHPdoc)
	 * @see components/com_fabrik/models/plgFabrik_Element#elementListJavascript()
	 */

	function elementListJavascript()
	{
		$user = JFactory::getUser();

		$params = $this->getParams();
		$user = JFactory::getUser();
		$id = $this->getHTMLId();
		$list = $this->getlistModel()->getTable();
		$formid = $list->form_id;
		$listMyThumbs = array();
		$idFromCookie = NULL;
		$data = $this->getListModel()->getData();
		$groupKeys = array_keys($data);
		foreach ($groupKeys as $gKey)
		{
			foreach ($data[$gKey] as $rowkey)
			{
				if (!$idFromCookie && $user->get('id') == 0)
				{
					$idFromCookie = $this->getCookieName($list->id, $rowkey->__pk_val);
				}
				$listMyThumbs[$rowkey->__pk_val] = $this->_getMyThumb($list->id, $formid, $rowkey->__pk_val);
			}
		}
		if ($user->get('id') == 0)
		{
			$userid = $idFromCookie;
		}
		else
		{
			$userid = $user->get('id');
		}
		$opts = new stdClass();

		$opts->listid = $list->id;
		$opts->imagepath = COM_FABRIK_LIVESITE . '/plugins/fabrik_element/thumbs/images/';
		$opts->elid = $this->getElement()->id;
		$opts->myThumbs = $listMyThumbs;
		$opts->userid = "$userid";
		$opts = json_encode($opts);
		return "new FbThumbsList('$id', $opts);\n";
	}

	function includeInSearchAll($advancedMode = false)
	{
		return false;
	}

	public function filterValueList($normal, $tableName = '', $label = '', $id = '', $incjoin = true)
	{
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$params = $this->getParams();
		$filter_build = $params->get('filter_build_method', 0);
		if ($filter_build == 0)
		{
			$filter_build = $usersConfig->get('filter_build_method');
		}
		if ($filter_build == 2)
		{
			return $this->filterValueList_All($normal, $tableName, $label, $id, $incjoin);
		}
		else
		{
			return $this->filterValueList_Exact($normal, $tableName, $label, $id, $incjoin);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see components/com_fabrik/models/plgFabrik_Element#filterValueList_All($normal, $tableName, $label, $id, $incjoin)
	 */
	protected function filterValueList_All($normal, $tableName = '', $label = '', $id = '', $incjoin = true)
	{
		for ($i = 0; $i < 6; $i++)
		{
			$return[] = JHTML::_('select.option', $i);
		}
		return $return;
	}

	public function onRenderAdminSettings($data = array(), $repeatCounter = null)
	{
		$this->install();
		return parent::onRenderAdminSettings($data, $repeatCounter);
	}

	public function install()
	{
		$db = FabrikWorker::getDbo();
		$query = "CREATE TABLE IF NOT EXISTS  `#__{package}_thumbs` (
	`user_id` VARCHAR( 255 ) NOT NULL ,
	`listid` INT( 6 ) NOT NULL ,
	`formid` INT( 6 ) NOT NULL ,
	`row_id` INT( 6 ) NOT NULL ,
	`thumb` VARCHAR( 255 ) NOT NULL,
	`date_created` DATETIME NOT NULL,
	`element_id` INT( 6 ) NOT NULL,
	 PRIMARY KEY ( `user_id` , `listid` , `formid` , `row_id`, `element_id` )
);";
		$db->setQuery($query);
		$db->query();
	}
}
?>