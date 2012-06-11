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
	protected $join = null;

	/** @var int id of join to load */
	protected $id = null;

	/** @var array join data to bind to Join table */
	var $_data = null;

	function __construct()
	{
		parent::__construct();
	}

	function setId($id)
	{
		$this->id = $id;
	}
	
	function getId()
	{
		return $this->id;
	}

	function setData($d)
	{
		$this->_data = $d;
	}

	function getJoin()
	{
		if (!isset($this->join))
		{
			$this->join = FabTable::getInstance('join', 'FabrikTable');
			if (isset($this->_data))
			{
				$this->join->bind($this->_data);
			}
			else {
				$this->join->load($this->_id);
			}
			if (is_string($this->join->params))
			{
				$this->join->params = trim($this->join->params) == '' ? '{"type": ""}' : $this->join->params;
				$this->join->params = json_decode($this->join->params);
			}
		}
		return $this->join;
	}
	
	function clearJoin()
	{
		unset($this->join);
	}

	/**
	 * load the model from the element id
	 * $param string $key
	 * @param int $id
	 */

	function getJoinFromKey($key, $id)
	{
		if (!isset($this->join))
		{
			$db = FabrikWorker::getDbo(true);
			$this->join = FabTable::getInstance('join', 'FabrikTable');
			$this->join->load(array($key => $id));
		}
		return $this->join;
	}

	function getPrimaryKey($splitter = '___')
	{
		$join = $this->getJoin();
		$pk = $join->table_join . $splitter . $join->table_join_key;
		return $pk;
	}
	
	public function setElementId($id)
	{
		$this->join->element_id = $id;
	}
	
	/**
	 * deletes the loaded join and then
	 * removes all elements, groups & form group record
	 * @param int the group id that the join is linked to
	 */

	function deleteAll($groupId)
	{
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->delete(' #__{package}_elements')->where('group_id = ' . (int) $groupId);
		$db->setQuery($query);
		if (!$db->query())
		{
			return JError::raiseError(500, $db->getErrorMsg());
		}
		$query->clear();
		$query->delete(' #__{package}_groups')->where('id = ' . (int) $groupId);
		$db->setQuery($query);
		if (!$db->query())
		{
			return JError::raiseError(500, $db->getErrorMsg());
		}

		/* delete all form group records */
		$query->clear();
		$query->delete(' #__{package}_formgroup')->where('group_id = ' . (int) $groupId);
		$db->setQuery($query);
		if (!$db->query())
		{
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
		if (!$this->bind($source))
		{
			return false;
		}
		if (!$this->check())
		{
			return false;
		}
		if (!$this->store())
		{
			return false;
		}
		$this->_error = '';
		return true;
	}
	
}
?>