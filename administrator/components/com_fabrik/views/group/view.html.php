<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit a group.
 *
 * @package		Joomla.Administrator
 * @subpackage	Fabrik
 * @since		1.5
 */
class FabrikViewGroup extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $state;

	/**
	 * Display the view
	 */

	public function display($tpl = null)
	{
		// Initialiase variables.
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->state = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->addToolbar();
		FabrikHelper::setViewLayout($this);
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 * @since	1.6
	 */

	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', true);
		$user = JFactory::getUser();
		$isNew = ($this->item->id == 0);
		$userId = $user->get('id');
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo = FabrikAdminHelper::getActions($this->state->get('filter.category_id'));
		JToolBarHelper::title($isNew ? JText::_('COM_FABRIK_MANAGER_GROUP_NEW') : JText::_('COM_FABRIK_MANAGER_GROUP_EDIT'), 'group.png');
		if ($isNew)
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::apply('group.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('group.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::addNew('group.save2new', 'JTOOLBAR_SAVE_AND_NEW');
			}
			JToolBarHelper::cancel('group.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			// Can't save the record if it's checked out.
			if (!$checkedOut)
			{
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId))
				{
					JToolBarHelper::apply('group.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('group.save', 'JTOOLBAR_SAVE');

					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ($canDo->get('core.create'))
					{
						JToolBarHelper::addNew('group.save2new', 'JTOOLBAR_SAVE_AND_NEW');
					}
				}
			}
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::custom('group.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('group.cancel', 'JTOOLBAR_CLOSE');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_FABRIK_GROUPS_EDIT', false, JText::_('JHELP_COMPONENTS_FABRIK_GROUPS_EDIT'));
	}
}
