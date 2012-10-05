<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * View class for CSV import
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 */

class fabrikViewImport extends JViewLegacy
{

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  template
	 *
	 * @return  this
	 */

	public function display($tpl = null)
	{
		FabrikHelperHTML::framework();
		$this->listid = JRequest::getVar('listid', 0);
		$listModel = JModelLegacy::getInstance('List', 'FabrikFEModel');
		$listModel->setId($this->listid);
		$this->table = $listModel->getTable();
		$this->form = $this->get('Form');
		if (!$listModel->canCSVImport())
		{
			JError::raiseError(400, 'Naughty naughty!');
			jexit();
		}
		$this->fieldsets = $this->setFieldSets();
		parent::display($tpl);
		return $this;
	}

	/**
	 * Set which fieldsets should be used
	 *
	 * @since   3.0.7
	 *
	 * @return  array  fieldset names
	 */

	private function setFieldSets()
	{
		// From list data view in admin
		$id = JRequest::getInt('listid', 0);

		// From list of lists checkbox selection
		$cid = JRequest::getVar('cid', array(0));
		JArrayHelper::toInteger($cid);
		if ($id === 0)
		{
			$id = $cid[0];
		}
		if (($id !== 0))
		{
			$db = FabrikWorker::getDbo();
			$query = $db->getQuery(true);
			$query->select('label')->from('#__{package}_lists')->where('id = ' . $id);
			$db->setQuery($query);
			$this->listName = $db->loadResult();
		}
		$fieldsets = array('details');
		$fieldsets[] = $id === 0 ? 'creation' : 'append';
		$fieldsets[] = 'format';
		return $fieldsets;
	}
}
