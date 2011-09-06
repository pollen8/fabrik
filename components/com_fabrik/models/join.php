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

class FabrikFEModelJoin extends FabModel{

	/**
	 * constructor
	 */

	/** @var object join table */
	var $_join = null;

	/** @var int id of join to load */
	var $_id = null;

	/** @var array join data to bind to Join table */
	var $_data = null;

	function __construct()
	{
		parent::__construct();
	}

	function setId($id)
	{
		$this->_id = $id;
	}

	function setData($d)
	{
		$this->_data = $d;
	}

	function getJoin()
	{
		if (!isset($this->_join)) {
			$this->_join = FabTable::getInstance('join', 'FabrikTable');
			if (isset($this->_data)) {
				$this->_join->bind($this->_data);
			} else {
				$this->_join->load($this->_id);
			}
		}
		return $this->_join;
	}

	/**
	 * load the model from the element id
	 * $param string $key
	 * @param int $id
	 */

	function getJoinFromKey($key, $id)
	{
		if (!isset($this->_join)) {
			$db = FabrikWorker::getDbo(true);
			$this->_join = FabTable::getInstance('join', 'FabrikTable');
			$this->_join->load(array($key => $id));
		}
		return $this->_join;
	}

	/**
	 * deletes the loaded join and then
	 * removes all elements, groups & form group record
	 * @param int the group id that the join is linked to
	 */

	function deleteAll($groupId)
	{
		$db = FabrikWorker::getDbo(true);
		$db->setQuery("DELETE FROM #__{package}_elements WHERE group_id = ".(int)$groupId);
		if (!$db->query()) {
			return JError::raiseError(500, $db->getErrorMsg());
		}

		$db->setQuery("DELETE FROM #__{package}_groups WHERE id = ".(int)$groupId);
		if (!$db->query()) {
			return JError::raiseError(500, $db->getErrorMsg());
		}

		/* delete all form group records */
		$db->setQuery("DELETE FROM #__{package}_formgroup WHERE group_id = ".(int)$groupId);
		if (!$db->query()) {
			return JError::raiseError(500, $db->getErrorMsg());
		}
		$this->getJoin()->delete();
	}

	/**
	 * saves the table join data
	 * @param array data to save
	 */

	function save($source)
	{
		if (!$this->bind($source)) {
			return false;
		}
		if (!$this->check()) {
			return false;
		}
		if (!$this->store()) {
			return false;
		}

		$this->_error = '';
		return true;
	}

}
?>