<?php
/**
 * Plugin element to enable users to make notes on a give record
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.notes
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/plugins/fabrik_element/databasejoin/databasejoin.php';

/**
 * Plugin element to enable users to make notes on a give record
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.notes
 * @since       3.0
 */

class PlgFabrik_ElementNotes extends PlgFabrik_ElementDatabasejoin
{
	/**
	 * Last row id to be inserted via ajax call
	 *
	 * @var int
	 */
	protected $loadRow = null;

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  Repeat group counter
	 *
	 * @return  array
	 */

	public function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->rowid = (int) $this->getFormModel()->getRowId();
		$opts->id = $this->id;
		$opts->j3 = FabrikWorker::j3();

		return array('FbNotes', $id, $opts);
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           To pre-populate element with
	 * @param   int    $repeatCounter  Repeat group counter
	 *
	 * @return  string	elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		$tmp = $this->_getOptions($data, $repeatCounter, true);
		$layoutName = FabrikWorker::j3() ? 'form' : 'form-25';
		$layout = $this->getLayout($layoutName);
		$layoutData = new stdClass;
		$layoutData->id = $this->getHTMLId($repeatCounter);
		$layoutData->labels = array();
		$layoutData->name = $this->getHTMLName($repeatCounter);;
		$layoutData->fieldType = $params->get('fieldType', 'textarea');
		$layoutData->editable = $this->isEditable();
		$layoutData->rowid = $this->getFormModel()->getRowId();;

		foreach ($tmp as $row)
		{
			$layoutData->labels[] = $this->getDisplayLabel($row);
		}

		return $layout->render($layoutData);
	}

	/**
	 * Get display label
	 *
	 * @param   object  $row  Row
	 *
	 * @return string
	 */
	protected function getDisplayLabel($row)
	{
		$params = $this->getParams();

		if ($params->get('showuser', true))
		{
			$txt = $this->getUserNameLinked($row) . ' ' . $row->text;
		}
		else
		{
			$txt = $row->text;
		}

		return $txt;
	}

	/**
	 * Get linked user name (only for com_uddeim apparently!?)
	 *
	 * @param   object  $row  Row
	 *
	 * @return string
	 */
	protected function getUserNameLinked($row)
	{
		if ($this->hasComponent('com_uddeim'))
		{
			if (isset($row->username))
			{
				return '<a href="index.php?option=com_uddeim&task=new&recip=' . $row->userid . '">' . $row->username . '</a> ';
			}
		}

		return '';
	}

	/**
	 * Has component. [Really shouldn't be here but in a helper].
	 *
	 * @param   string  $c  Component name (com_foo)
	 *
	 * @return  bool
	 */
	protected function hasComponent($c)
	{
		if (!isset($this->components))
		{
			$this->components = array();
		}

		if (!array_key_exists($c, $this->components))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('COUNT(id)')->from('#__extensions')->where('name = ' . $db->quote($c));
			$db->seQuery($query);
			$found = $db->loadResult();
			$this->components[$c] = $found;
		}

		return $this->components[$c];
	}

	/**
	 * Create the where part for the query that selects the list options
	 *
	 * @param   array           $data            Current row data to use in placeholder replacements
	 * @param   bool            $incWhere        Should the additional user defined WHERE statement be included
	 * @param   string          $thisTableAlias  Db table alias
	 * @param   array           $opts            Options
	 * @param   JDatabaseQuery  $query           Append where to JDatabaseQuery object or return string (false)
	 *
	 * @return string|JDatabaseQuery
	 */

	protected function buildQueryWhere($data = array(), $incWhere = true, $thisTableAlias = null, $opts = array(), $query = false)
	{
		$params = $this->getParams();
		$db = $this->getDb();
		$field = $params->get('notes_where_element');
		$value = $params->get('notes_where_value');
		$fk = $params->get('join_fk_column', '');
		$rowid = $this->getFormModel()->getRowId();
		$where = array();

		// Jaanus: here we can choose whether WHERE has to have single or (if field is the same as FK then only) custom (single or multiple) criteria,
		if ($value != '')
		{
			if ($field != '' && $field !== $fk)
			{
				$where[] = $db->quoteName($field) . ' = ' . $db->quote($value);
			}
			else
			{
				$where[] = $value;
			}
		}
		// Jaanus: when we choose WHERE field to be the same as FK then WHERE criteria is automatically FK = rowid, custom criteria(s) above may be added
		if ($fk !== '' && $field === $fk && $rowid != '')
		{
			$where[] = $db->quoteName($fk) . ' = ' . $rowid;
		}

		if ($this->loadRow != '')
		{
			$pk = $db->quoteName($this->getJoin()->table_join_alias . '.' . $params->get('join_key_column'));
			$where[] = $pk . ' = ' . $this->loadRow;
		}

		/**
		 * $$$ hugh if where is still empty (most likely if new form) set it to "1 = -1", otherwise
		 * we'll wind up selecting everything in the table.
		 */
		
		if ($query)
		{
			if (!empty($where))
			{
				$query->where(implode(' OR ', $where));
			}
			else
			{
				$query->where('1 = -1');
			}

			return $query;
		}
		else
		{
			return empty($where) ? '1 = -1' : 'WHERE ' . implode(' OR ', $where);
		}
	}

	/**
	 * Get options order by
	 *
	 * @param   string         $view   View mode '' or 'filter'
	 * @param   JDatabasQuery  $query  Set to false to return a string
	 *
	 * @return  string  order by statement
	 */

	protected function getOrderBy($view = '', $query = false)
	{
		$params = $this->getParams();
		$db = $this->getDb();
		$orderBy = $params->get('notes_order_element');

		if ($orderBy == '')
		{
			return $query ? $query : '';
		}
		else
		{
			$order = $db->quoteName($orderBy) . ' ' . $params->get('notes_order_dir', 'ASC');

			if ($query)
			{
				$query->order($order);

				return $query;
			}

			return " ORDER BY " . $order;
		}
	}

	/**
	 * If buildQuery needs additional fields then set them here, used in notes plugin
	 *
	 * @since 3.0rc1
	 *
	 * @return string fields to add e.g return ',name, username AS other'
	 */

	protected function getAdditionalQueryFields()
	{
		$fields = '';
		$db = $this->getDb();
		$params = $this->getParams();

		if ($params->get('showuser', true))
		{
			$user = $params->get('userid', '');

			if ($user !== '')
			{
				$tbl = $db->quoteName($this->getJoin()->table_join_alias);
				$fields .= ',' . $tbl . '.' . $db->quoteName($user) . 'AS userid, u.name AS username';
			}
		}

		return $fields;
	}

	/**
	 * If buildQuery needs additional joins then set them here, used in notes plugin
	 *
	 * @param   mixed  $query  false to return string, or JQueryBuilder object
	 *
	 * @since 3.0rc1
	 *
	 * @return string|JQueryerBuilder join statement to add
	 */

	protected function buildQueryJoin($query = false)
	{
		$join = '';
		$db = $this->getDb();
		$params = $this->getParams();

		if ($params->get('showuser', true))
		{
			$user = $params->get('userid', '');

			if ($user !== '')
			{
				$tbl = $db->quoteName($this->getJoin()->table_join_alias);

				if (!$query)
				{
					$join .= ' LEFT JOIN #__users AS u ON u.id = ' . $tbl . '.' . $db->quoteName($user);
				}
				else
				{
					$query->join('LEFT', '#__users AS u ON u.id = ' . $tbl . '.' . $db->quoteName($user));
				}
			}
		}

		return $query ? $query : $join;
	}

	/**
	 * Do you add a please select option to the cdd list
	 *
	 * @since 3.0b
	 *
	 * @return boolean
	 */

	protected function showPleaseSelect()
	{
		return false;
	}

	/**
	 * Ajax add note
	 *
	 * @return  void
	 */

	public function onAjax_addNote()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$this->loadMeForAjax();
		$return = new stdClass;
		$db = $this->getDb();
		$query = $db->getQuery(true);
		$params = $this->getParams();
		$table = $db->quoteName($params->get('join_db_name'));
		$col = $params->get('join_val_column');
		$key = $db->quoteName($params->get('join_key_column'));
		$v = $input->get('v', '', '', 'string');
		$rowid = $this->getFormModel()->getRowId();

		// Jaanus - avoid inserting data when the form is 'new' not submitted ($rowid == '')
		if ($rowid !== '')
		{
			$query->insert($table)->set($col . ' = ' . $db->quote($v));
			$user = $params->get('userid', '');

			if ($user !== '')
			{
				$query->set($db->quoteName($user) . ' = ' . (int) JFactory::getUser()->get('id'));
			}

			$fk = $params->get('join_fk_column', '');

			if ($fk !== '')
			{
				$query->set($db->quoteName($fk) . ' = ' . $db->quote($input->get('rowid')));
			}

			$db->setQuery($query);
			$db->execute();
			$return->label = $v;
			echo json_encode($return);
		}
	}
}
