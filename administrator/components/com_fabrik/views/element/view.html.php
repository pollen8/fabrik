<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit an element.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       1.5
 */

class FabrikAdminViewElement extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $state;
	protected $pluginFields;
	protected $validations;
	protected $jsevents;
	protected $activeValidations;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  template
	 *
	 * @return  void
	 */

	public function display($tpl = null)
	{
		if ($this->getLayout() == 'confirmupdate')
		{
			$this->confirmupdate();
			return;
		}

		// Initialiase variables.
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->state = $this->get('State');
		$this->pluginFields = $this->get('PluginHTML');

		$this->js = $this->get('Js');

		$this->jsevents = $this->get('JsEvents');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();

		// Used for js part of form
		if ($this->item->id == 0)
		{
			$this->elements = array(JText::_('COM_FABRIK_AVAILABLE_AFTER_SAVE'));
		}
		else
		{
			$this->elements = $this->get('Elements');
		}
		$this->parent = $this->get('Parent');
		FabrikAdminHelper::setViewLayout($this);
		JText::script('COM_FABRIK_ERR_ELEMENT_JS_ACTION_NOT_DEFINED');

		$srcs = FabrikHelperHTML::framework();
		$srcs[] = 'media/com_fabrik/js/fabrik.js';
		$srcs[] = 'administrator/components/com_fabrik/views/namespace.js';
		$srcs[] = 'administrator/components/com_fabrik/views/pluginmanager.js';
		$srcs[] = 'administrator/components/com_fabrik/views/element/tmpl/adminelement.js';

		$shim = array();
		$dep = new stdClass;
		$dep->deps = array('admin/pluginmanager');
		$shim['admin/element/tmpl/adminelement'] = $dep;

		FabrikHelperHTML::iniRequireJS($shim);
		FabrikHelperHTML::script($srcs, $this->js);

		parent::display($tpl);
	}

	/**
	 * Ask the user if they really want to alter the element fields structure/name
	 *
	 * @param   string  $tpl  template
	 *
	 * @return  void
	 */

	protected function confirmupdate($tpl = null)
	{
		$this->state = $this->get('State');
		$app = JFactory::getApplication();
		$this->addConfirmToolbar();
		$this->item = $this->get("Item");
		$this->oldName = $app->getUserState('com_fabrik.oldname');
		$this->origDesc = $app->getUserState('com_fabrik.origDesc');
		$this->newDesc = $app->getUserState('com_fabrik.newdesc');
		$this->origPlugin = $app->getUserState('com_fabrik.origplugin');
		$this->origtask = $app->getUserState('com_fabrik.origtask');
		$app->setUserState('com_fabrik.confirmUpdate', 0);
		parent::display($tpl);
	}

	/**
	 * Add the confirmation tool bar
	 *
	 * @return  void
	 */

	protected function addConfirmToolbar()
	{
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_ELEMENT_EDIT'), 'element.png');
		$app = JFactory::getApplication();
		$input = $app->input;
		$input->set('hidemainmenu', true);
		JToolBarHelper::save('element.updatestructure', 'JTOOLBAR_SAVE');
		JToolBarHelper::cancel('element.cancelUpdatestructure', 'JTOOLBAR_CANCEL');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 *
	 * @return  void
	 */

	protected function addToolbar()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$input->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$isNew = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo = FabrikAdminHelper::getActions($this->state->get('filter.category_id'));

		JToolBarHelper::title($isNew ? JText::_('COM_FABRIK_MANAGER_ELEMENT_NEW') : JText::_('COM_FABRIK_MANAGER_ELEMENT_EDIT'), 'element.png');

		if ($isNew)
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::apply('element.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('element.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::addNew('element.save2new', 'JTOOLBAR_SAVE_AND_NEW');
			}
			JToolBarHelper::cancel('element.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{

			// Can't save the record if it's checked out.
			if (!$checkedOut)
			{
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId))
				{
					JToolBarHelper::apply('element.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('element.save', 'JTOOLBAR_SAVE');

					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ($canDo->get('core.create'))
					{
						JToolBarHelper::addNew('element.save2new', 'JTOOLBAR_SAVE_AND_NEW');
					}
				}
			}
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::custom('element.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('element.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolBarHelper::divider();
		//JToolBarHelper::help('JHELP_COMPONENTS_FABRIK_ELEMENTS_EDIT');
		JToolBarHelper::help('JHELP_COMPONENTS_FABRIK_ELEMENTS_EDIT', false, JText::_('JHELP_COMPONENTS_FABRIK_ELEMENTS_EDIT'));
	}

}
