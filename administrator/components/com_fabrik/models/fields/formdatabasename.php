<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       1.6
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

/**
 * Renders the form's database name or a field to create one
 *
 * @package  Fabrik
 * @since    3.0
 */

class JFormFieldFormDatabaseName extends JFormFieldText
{
	/**
	 * Element name
	 * @var		string
	 */
	protected $name = 'FormDatabaseName';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 */

	protected function getInput()
	{
		if ($this->form->getValue('record_in_database'))
		{
			$db = FabrikWorker::getDbo(true);
			$query = $db->getQuery(true);
			$id = (int) $this->form->getValue('id');
			$query->select('db_table_name')->from('#__{package}_lists')->where('form_id = ' . $id);
			$db->setQuery($query);
			$this->element['readonly'] == true;
			$this->element['class'] = 'readonly';
			$this->value = $db->loadResult();
		}
		return parent::getInput();
	}

}
