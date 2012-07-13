<?php
/**
* @package Joomla.Administrator
* @subpackage Fabrik
* @since		1.6
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

defined('_JEXEC') or die;

require_once('fabmodellist.php');

class FabrikModelElements extends FabModelList
{

	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */

	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'e.id', 'e.name', 'e.label', 'e.show_in_list_summary', 'e.published',
			'e.ordering', 'g.label', 'e.plugin'
			);
		}
		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Initialise variables.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'e.*,
				e.ordering AS ordering'
			)
		);
		$query->from('#__{package}_elements AS e');

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('e.published = '.(int) $published);
		} elseif ($published === '') {
			$query->where('(e.published IN (0, 1))');
		}

		//Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->quote('%'.$db->getEscaped($search, true).'%');
			$query->where('(e.name LIKE '.$search.' OR e.label LIKE '.$search.')');
		}

		$group = $this->getState('filter.group');
		if (trim($group) !== '') {
			$query->where('g.id = '.(int) $group);
		}

		$showInList = $this->getState('filter.showinlist');
		if (trim($showInList) !== '') {
			$query->where('e.show_in_list_summary = '.(int) $showInList);
		}

		$plugin = $this->getState('filter.plugin');
		if (trim($plugin) !== '') {
			$query->where('e.plugin = '.$db->quote($plugin));
		}

		//for drop fields view
		$cids = (array) $this->getState('filter.cid');
		if (!empty($cids)) {
			$query->where('e.id IN ('.implode(',', $cids).')');
		}
		$this->filterByFormQuery($query, 'fg');

		// Join over the users for the checked out user.

		// $$$ hugh - altered this query as ...
		// WHERE (jj.list_id != 0 AND jj.element_id = 0)
		// ...instead of ...
		// WHERE jj.list_id != 0
		//... otherwioe we pick up repeat elements, as they have both table and element set
		// and he query fails with "returns multiple values" for the fullname select

		$fullname = "(SELECT DISTINCT(
		IF( ISNULL(jj.table_join), CONCAT(ll.db_table_name, '___', ee.name), CONCAT(jj.table_join, '___', ee.name))
		)
		FROM #__fabrik_elements AS ee
		LEFT JOIN #__{package}_joins AS jj ON jj.group_id = ee.group_id
		LEFT JOIN #__{package}_formgroup as fg ON fg.group_id = ee.group_id
		LEFT JOIN #__{package}_lists AS ll ON ll.form_id = fg.form_id
		WHERE (jj.list_id != 0 AND jj.element_id = 0)
		AND ee.id = e.id AND ee.group_id <> 0  LIMIT 1)  AS full_element_name";

		$query->select('u.name AS editor, '.$fullname.', g.name AS group_name, l.db_table_name');

		$query->join('LEFT', '#__users AS u ON checked_out = u.id');
		$query->join('LEFT', '#__{package}_groups AS g ON e.group_id = g.id ');
		//was inner join but if el assigned to group which was not part of a form then the element
		//was not shown in the list
		$query->join('LEFT', '#__{package}_formgroup AS fg ON fg.group_id = e.group_id');
		$query->join('LEFT', '#__{package}_lists AS l ON l.form_id = fg.form_id');

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'ordering');
		$orderDirn	= $this->state->get('list.direction');
		if ($orderCol == 'ordering' || $orderCol == 'category_title') {
			$orderCol = 'ordering';
		}
		if (trim($orderCol) !== '') {
			$query->order($db->getEscaped($orderCol.' '.$orderDirn));
		}
		return $query;
	}

/**
 * (non-PHPdoc)
 * @see JModelList::getItems()
 */
	public function getItems()
	{
		$items = parent::getItems();
		//	get the join elemnent name of those elements not in a joined group
		foreach ($items as &$item) {
			if ($item->full_element_name == '') {
				$item->full_element_name = $item->db_table_name . '___' . $item->name;
			}
		}
		return $items;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */

	public function getTable($type = 'Element', $prefix = 'FabrikTable', $config = array())
	{
		$config['dbo'] = FabriKWorker::getDbo();
		return FabTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 * @since	1.6
	 */

	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the parameters.
		$params = JComponentHelper::getParams('com_fabrik');
		$this->setState('params', $params);

		$published = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		//Load the form state
		$form = $app->getUserStateFromRequest($this->context.'.filter.form', 'filter_form', '');
		$this->setState('filter.form', $form);

		//Load the group state
		$group = $app->getUserStateFromRequest($this->context.'.filter.group', 'filter_group', '');
		$this->setState('filter.group', $group);

		//Load the show in list state
		$showinlist = $app->getUserStateFromRequest($this->context.'.filter.showinlist', 'filter_showinlist', '');
		$this->setState('filter.showinlist', $showinlist);

		//Load the plug-in state
		$plugin = $app->getUserStateFromRequest($this->context.'.filter.plugin', 'filter_plugin', '');
		$this->setState('filter.plugin', $plugin);

		// List state information.
		parent::populateState($ordering, $direction);
	}


	public function getShowInListOptions()
	{
		return array(
			JHtml::_('select.option', 0, JText::_('JNO')),
			JHtml::_('select.option', 1, JText::_('JYES'))
		);
	}

	/**
	 * get a list of plugin types to filter on
	 * @return array of select.options
	 */

	public function getPluginOptions()
	{
		$db = FabrikWorker::getDbo(true);
		$user = JFactory::getUser();
		$levels = implode(',', $user->getAuthorisedViewLevels());
		$query = $db->getQuery(true);
		$query->select('element AS value, element AS text')
		->from('#__extensions')
		->where('enabled >= 1')
		->where('type ='.$db->quote('plugin'))
		->where('state >= 0')
		->where('access IN ('.$levels.')')
		->where('folder = '.$db->quote('fabrik_element'))
		->order('text');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;
	}

}