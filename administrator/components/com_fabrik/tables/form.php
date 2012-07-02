<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright Copyright (C) 2005 Pollen 8 Design Ltd. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR . '/components/com_fabrik/tables/fabtable.php');

/**
 * @package     Joomla
 * @subpackage  Fabrik
 */
class FabrikTableForm extends FabTable
{

	/*
	 *
	 */

	function __construct(&$_db)
	{
		parent::__construct('#__{package}_forms', 'id', $_db);
	}

	/**
	 * Overloaded bind function
	 *
	 * @param	array		$hash named array
	 *
	 * @return	null|string	null is operation was satisfactory, otherwise returns an error
	 * @see		JTable:bind
	 * @since	1.5
	 */

	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}

		//needed for form edit view where we see the database table anme and connection id
		if (array_key_exists('db_table_name', $array)) {
			$this->db_table_name = $array['db_table_name'];
		}
		if (array_key_exists('connection_id', $array)) {
			$this->connection_id = $array['connection_id'];#
		}
		return parent::bind($array, $ignore);
	}

	public function store($updateNulls = false)
	{
		//we don't want these to be stored - generates an sql error
		unset($this->db_table_name);
		unset($this->connection_id);
		return parent::store($updateNulls);
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the JTable instance properties.
	 *
	 * @param	mixed	An optional primary key value to load the row by, or an array of fields to match.  If not
	 *					set the instance property value is used.
	 * @param	boolean	True to reset the default values before loading the new row.
	 * @return	boolean	True if successful. False if row not found or on error (internal error state set in that case).
	 * @since	1.0
	 * @link	http://docs.joomla.org/JTable/load
	 */
	public function load($keys = null, $reset = true)
	{
		if (empty($keys)) {
			// If empty, use the value of the current key
			$keyName = $this->_tbl_key;
			$keyValue = $this->$keyName;

			// If empty primary key there's is no need to load anything
			if(empty($keyValue)) return true;

			$keys = array($keyName => $keyValue);
		}
		elseif (!is_array($keys)) {
			// Load by primary key.
			$keys = array($this->_tbl_key => $keys);
		}

		if ($reset) $this->reset();

		// Initialise the query.
		$query	= $this->_db->getQuery(true);
		//$query->select('*');
		$query->select($this->_tbl.'.*, l.db_table_name, l.connection_id');
		$query->from($this->_tbl);
		$fields = array_keys($this->getProperties());

		foreach ($keys as $field => $value)
		{
			// Check that $field is in the table.
			if (!in_array($field, $fields)) {
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_CLASS_IS_MISSING_FIELD', get_class($this), $field));
				$this->setError($e);
				return false;
			}
			// Add the search tuple to the query.
			$query->where($this->_db->quoteName($this->_tbl).'.'.$this->_db->quoteName($field).' = '.$this->_db->quote($value));
		}

		//$$$ rob added
		$query->join('LEFT', '#__{package}_lists AS l ON l.form_id = '.$this->_tbl.'.id');
		$this->_db->setQuery($query);
		$row = $this->_db->loadAssoc();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$e = new JException($this->_db->getErrorMsg());
			$this->setError($e);
			return false;
		}

		// Check that we have a result.
		if (empty($row)) {
			$e = new JException(JText::_('JLIB_DATABASE_ERROR_EMPTY_ROW_RETURNED'));
			$this->setError($e);
			return false;
		}

		// Bind the object with the row and return.
		return $this->bind($row);
	}
}
?>
