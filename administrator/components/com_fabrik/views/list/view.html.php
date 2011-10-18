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
 * View to edit a list.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_fabrik
 * @since		1.5
 */
class FabrikViewList extends JView
{
	protected $form;
	protected $item;
	protected $state;
	protected $js;

	/**
	 * Display the list
	 */
	public function display($tpl = null)
	{
		FabrikHelperHTML::framework();
		// Initialiase variables.
		$this->form	= $this->get('Form');
		$this->item	= $this->get('Item');
		$formModel = $this->get('FormModel');
		$formModel->setId($this->item->form_id);
		$this->state = $this->get('State');
		$this->js = $this->get('Js');
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		if ($this->item->id == 0) {
			$this->order_by = array(JText::_('COM_FABRIK_AVAILABLE_AFTER_SAVE'));
			$this->group_by = JText::_('COM_FABRIK_AVAILABLE_AFTER_SAVE');
		} else {
			$this->order_by = array();

			$orderbys = FabrikWorker::JSONtoData($this->item->order_by, true);
			foreach ($orderbys as $orderby) {
				$this->order_by[] = $formModel->getElementList('order_by[]', $orderby, true, false, false);
			}
			if (empty($this->order_by)) {
				$this->order_by[] = $formModel->getElementList('order_by[]', '', true, false, false);
			}
			$orderDir[] = JHTML::_('select.option', 'ASC', JText::_('COM_FABRIK_ASCENDING'));
			$orderDir[] = JHTML::_('select.option', 'DESC', JText::_('COM_FABRIK_DESCENDING'));

			$orderdirs = FabrikWorker::JSONtoData($this->item->order_dir, true);
			$this->order_dir = array();
			foreach ($orderdirs as $orderdir) {
				$this->order_dir[] = JHTML::_( 'select.genericlist', $orderDir, 'order_dir[]', 'class="inputbox" size="1" ', 'value', 'text', $orderdir);
			}
			if (empty($this->order_dir)) {
				$this->order_dir[] = JHTML::_( 'select.genericlist', $orderDir, 'order_dir[]', 'class="inputbox" size="1" ', 'value', 'text', '');
			}
			$this->group_by = $formModel->getElementList('group_by', $this->item->group_by, true, false, false);
		}
		parent::display($tpl);
	}

	/**
	 * show the list's linked forms etc
	 * @param $tpl
	 */
	
	public function showLinkedElements($tpl = null)
	{
		$model = $this->getModel('Form');
		$this->addLinkedElementsToolbar();
		$this->formGroupEls = $model->getFormGroups(false);
		$this->formTable = $model->getForm();
		parent::display($tpl);
	}

	/**
	 * see if the user wants to rename the list/form/groups
	 * @param string $tpl
	 */

	public function confirmCopy($tpl = null)
	{
		$cid = JRequest::getVar('cid', array(0), 'method', 'array');
		$lists = array();
		$model= $this->getModel();
		foreach ($cid as $id)
		{
			$model->setId($id);
			$table =& $model->getTable();
			$formModel = $model->getFormModel();
			$row = new stdClass();
			$row->id = $id;
			$row->formid = $table->form_id;
			$row->label = $table->label;
			$row->formlabel = $formModel->getForm()->label;
			$groups = $formModel->getGroupsHiarachy();
			$row->groups = array();
			foreach ($groups as $group) {
				$grouprow = new stdClass();
				$g = $group->getGroup();
				$grouprow->id = $g->id;
				$grouprow->name = $g->name;
				$row->groups[] = $grouprow;
			}
			$lists[] = $row;
		}
		$this->assign('lists', $lists);
		FabrikViewList::addConfirmCopyToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', true);

		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo		= FabrikHelper::getActions($this->state->get('filter.category_id'));

		JToolBarHelper::title($isNew ? JText::_('COM_FABRIK_MANAGER_LIST_NEW') : JText::_('COM_FABRIK_MANAGER_LIST_EDIT'), 'list.png');

		if ($isNew) {
			// For new records, check the create permission.
			if ($canDo->get('core.create')) {
				JToolBarHelper::apply('list.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('list.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::addNew('list.save2new', 'JTOOLBAR_SAVE_AND_NEW');
			}
			JToolBarHelper::cancel('list.cancel', 'JTOOLBAR_CANCEL');
		} else {

			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId)) {
					JToolBarHelper::apply('list.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('list.save', 'JTOOLBAR_SAVE');

					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ($canDo->get('core.create')) {
						JToolBarHelper::addNew('list.save2new', 'JTOOLBAR_SAVE_AND_NEW');
					}
				}
			}
			// If checked out, we can still save
			if ($canDo->get('core.create')) {
				JToolBarHelper::custom('list.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('list.cancel', 'JTOOLBAR_CLOSE');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_FABRIK_LISTS_EDIT');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */

	protected function addLinkedElementsToolbar()
	{
		JRequest::setVar('hidemainmenu', true);
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_LIST_LINKED_ELEMENTS'), 'list.png');
		JToolBarHelper::cancel('list.cancel', 'JTOOLBAR_CLOSE');
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_FABRIK_LISTS_EDIT');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   3.0
	 */

	protected function addConfirmCopyToolbar()
	{
		JRequest::setVar('hidemainmenu', true);
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_LIST_COPY'), 'list.png');
		JToolBarHelper::cancel('list.cancel', 'JTOOLBAR_CLOSE');
		JToolBarHelper::save('list.doCopy', 'JTOOLBAR_SAVE');
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_FABRIK_LISTS_EDIT');
	}
}
