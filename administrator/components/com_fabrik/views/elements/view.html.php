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
 * View class for a list of elements.
 *
 * @package		Joomla.Administrator
 * @subpackage	Fabrik
 * @since		1.6
 */
class FabrikViewElements extends JView
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		if ($this->getLayout() == 'confirmdelete') {
			$this->confirmdelete();
			return;
		}
		if ($this->getLayout() == 'copyselectgroup') {
			$this->copySelectGroup();
			return;
		}
		// Initialise variables.
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->formOptions = $this->get('FormOptions');
		$this->showInListOptions = $this->get('ShowInListOptions');
		$this->groupOptions = $this->get('GroupOptions');
		$this->pluginOptions = $this->get('PluginOptions');
		$this->packageOptions = $this->get('PackageOptions');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

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

		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_ELEMENTS'), 'elements.png');
		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('element.add','JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('element.edit','JTOOLBAR_EDIT');
		}
		JToolBarHelper::custom('elements.copySelectGroup', 'copy.png', 'copy_f2.png', 'COM_FABRIK_COPY');
		if ($canDo->get('core.edit.state')) {
			if ($this->state->get('filter.state') != 2){
				JToolBarHelper::divider();
				JToolBarHelper::custom('elements.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('elements.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
			JToolBarHelper::divider();
			JToolBarHelper::custom('elements.showInListView', 'publish.png', 'publish_f2.png','COM_FABRIK_SHOW_IN_LIST_VIEW', true);
			JToolBarHelper::custom('elements.hideFromListView', 'unpublish.png', 'unpublish_f2.png', 'COM_FABRIK_REMOVE_FROM_LIST_VIEW', true);

		}
		if(JFactory::getUser()->authorise('core.manage','com_checkin')) {
			JToolBarHelper::custom('elements.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
		}
		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'elements.delete','JTOOLBAR_EMPTY_TRASH');
		} else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('elements.trash','JTOOLBAR_TRASH');
		}
		if ($canDo->get('core.admin')) {
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_fabrik');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_FABRIK_ELEMENTS', false, JText::_('JHELP_COMPONENTS_FABRIK_ELEMENTS'));
	}

	/**
	 * show a screen asking if the user wants to delete the lists forms/groups/elements
	 * and if they want to drop the underlying database table
	 * @param string $tpl
	 */

	protected function confirmdelete($tpl = null)
	{
		$model = $this->getModel();
		$model->setState('filter.cid', JRequest::getVar('cid', array(), 'default', 'array'));
		$this->items = $this->get('Items');
		$this->addConfirmDeleteToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar for confirming list deletion
	 *
	 * @since	1.6
	 */

	protected function addConfirmDeleteToolbar()
	{
		JRequest::setVar('hidemainmenu', true);
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_ELEMENT_CONFIRM_DELETE'), 'element.png');
		JToolBarHelper::save('elements.dodelete', 'JTOOLBAR_APPLY');
		JToolBarHelper::cancel('elements.cancel', 'JTOOLBAR_CANCEL');
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_FABRIK_ELEMENTS_EDIT', true, 'http://fabrikar.com/wiki/index.php/Element_delete_confirmation');
	}

	public function copySelectGroup($tpl = null)
	{
		JRequest::checkToken() or die('Invalid Token');
		$model = $this->getModel();
		$model->setState('filter.cid', JRequest::getVar('cid', array(), 'default', 'array'));
		$this->items = $this->get('Items');
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, name')->from('#__fabrik_groups')->order('name');
		$db->setQuery($query);
		$this->groups = $db->loadObjectList();
		$this->addConfirmCopyToolbar();
		parent::display($tpl);
	}

	protected function addConfirmCopyToolbar()
	{
		JRequest::setVar('hidemainmenu', true);
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_ELEMENT_COPY_TO_WHICH_GROUP'), 'element.png');
		JToolBarHelper::save('element.copy', 'JTOOLBAR_APPLY');
		JToolBarHelper::cancel('elements.cancel', 'JTOOLBAR_CANCEL');
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_FABRIK_ELEMENTS_EDIT', true, 'http://fabrikar.com/wiki/index.php/Element_copy_confirmation');
	}
}
