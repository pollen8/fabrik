<?php
/**
* @package Joomla.Administrator
* @subpackage Fabrik
* @since		1.6
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');


class FabrikModelPackages extends JModelList
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
				'p.id', 'p.label', 'p.published'
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
				'p.*'
			)
		);
		$query->from('#__{package}_packages AS p');


		// Join over the users for the checked out user.
		$query->select('u.name AS editor');
		$query->join('LEFT', '#__users AS u ON checked_out = u.id');

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('p.published = '.(int) $published);
		} else if ($published === '') {
			$query->where('(p.published IN (0, 1))');
		}

		$query->where('external_ref <> 1');
		//Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->quote('%'.$db->getEscaped($search, true).'%');
			$query->where('(p.label LIKE '.$search.' OR p.component_name LIKE '.$search.')');
		}
		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		if ($orderCol == 'ordering' || $orderCol == 'category_title') {
			$orderCol = 'category_title '.$orderDirn.', ordering';
		}
		$query->order($db->getEscaped($orderCol.' '.$orderDirn));

		return $query;
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
	public function getTable($type = 'Package', $prefix = 'FabrikTable', $config = array())
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

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		//Load the published state
		$published = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		// List state information.
		parent::populateState('name', 'asc');
	}

	public function getItems()
	{
		$items = parent::getItems();
		foreach ($items as &$i)
		{
			$n = $i->component_name .'_'.$i->version;
			$file = JPATH_ROOT . '/tmp/' . $n . '/pkg_' . $n . '.zip';
			$url = COM_FABRIK_LIVESITE . 'tmp/' . $n . '/pkg_' . $n . '.zip';
			if (JFile::exists($file))
			{
				$i->file = '<a href="' . $url . '">pkg_' . $n . '.zip</a>';
			}
			else
			{
				$i->file = JText::_('COM_FABRIK_EXPORT_PACKAGE_TO_CREATE_ZIP');
			}
		}
		return $items;
	}
}