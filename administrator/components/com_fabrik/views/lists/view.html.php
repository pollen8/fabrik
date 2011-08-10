<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of lists.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_fabrik
 * @since		1.6
 */
class FabrikViewLists extends JView
{
	protected $categories;
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		switch ($this->getLayout()) {
			case 'confirmdelete':
				$this->confirmdelete();
				return;
				break;
			case 'import':
				$this->import($tpl);
				return;
				break;
		}
		// Initialise variables.
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->table_groups = $this->get('TableGroups');

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT.'/helpers/fabrik.php';

		$canDo	= FabrikHelper::getActions($this->state->get('filter.category_id'));

		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_LISTS'), 'lists.png');
		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('list.add','JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('list.edit','JTOOLBAR_EDIT');
		}
		JToolBarHelper::custom('list.copy', 'copy.png', 'copy_f2.png', 'COM_FABRIK_COPY');
		if ($canDo->get('core.edit.state')) {
			if ($this->state->get('filter.state') != 2){
				JToolBarHelper::divider();
				JToolBarHelper::custom('lists.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('lists.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
		}
		JToolBarHelper::divider();
		if ($canDo->get('core.create')) {
			JToolBarHelper::custom('import.display', 'upload.png', 'upload_f2.png', 'COM_FABRIK_IMPORT', false);
		}
		JToolBarHelper::divider();

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'lists.delete','JTOOLBAR_EMPTY_TRASH');
		} else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('lists.trash','JTOOLBAR_TRASH');
		}
		if ($canDo->get('core.admin')) {
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_fabrik');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_FABRIK_LISTS');
	}

	/**
	 * Add the page title and toolbar for confirming list deletion
	 *
	 * @since	1.6
	 */

	protected function addConfirmDeleteToolbar()
	{
		JRequest::setVar('hidemainmenu', true);
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_LIST_CONFIRM_DELETE'), 'list.png');
		JToolBarHelper::save('lists.dodelete', 'JTOOLBAR_APPLY');
		JToolBarHelper::cancel('list.cancel', 'JTOOLBAR_CANCEL');
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_FABRIK_LISTS_EDIT', true, 'http://fabrikar.com/wiki/index.php/List_delete_confirmation');
	}

	protected function addImportToolBar()
	{
		JRequest::setVar('hidemainmenu', true);
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_LIST_IMPORT'), 'list.png');
		JToolBarHelper::save('lists.doimport', 'JTOOLBAR_APPLY');
		JToolBarHelper::cancel('list.cancel', 'JTOOLBAR_CANCEL');
	}

	/**
	 * show a screen asking if the user wants to delete the lists forms/groups/elements
	 * and if they want to drop the underlying database table
	 * @param string $tpl
	 */

	protected function confirmdelete($tpl = null)
	{
		$this->form	= $this->get('ConfirmDeleteForm', 'list');
		$this->items = $this->get('DbTableNames');
		$this->addConfirmDeleteToolbar();
		parent::display($tpl);
	}

	/**
	 * show a screen allowing the user to import a csv file to create a fabrikt table.
	 * @param unknown_type $tpl
	 */

	protected function import($tpl = null)
	{
		$this->form = $this->get('ImportForm');
		$this->addImportToolBar();
		parent::display($tpl);
	}
}
