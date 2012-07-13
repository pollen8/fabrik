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
 * View to edit a package.
 *
 * @package		Joomla.Administrator
 * @subpackage	Fabrik
 * @since		1.5
 */

class FabrikViewPackage extends JView
{
	protected $form;
	protected $item;
	protected $state;

	public function listform()
	{
		$srcs = FabrikHelperHTML::framework();
		FabrikHelperHTML::script($srcs);
		$this->listform	= $this->get('PackageListForm');
		JHtml::_('behavior.modal', 'a.modal');
		parent::display('list');
	}
	/**
	 * Display the view
	 */

	public function display($tpl = null)
	{
		// Initialiase variables.
		JHtml::_('behavior.modal', 'a.modal');
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->state = $this->get('State');
		$this->listform	= $this->get('PackageListForm');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		$canvas = JArrayHelper::getValue($this->item->params, 'canvas', array());
		$blocks = new stdClass;
		$b = JArrayHelper::getValue($canvas, 'blocks', array());
		$blocks->form = JArrayHelper::getValue($b, 'form', array());
		$blocks->list = JArrayHelper::getValue($b, 'list', array());
		$blocks->visualization = JArrayHelper::getValue($b, 'visualization', array());

		$opts = JArrayHelper::getvalue($canvas, 'options', array());
		$tabs = JArrayHelper::getValue($canvas, 'tabs', array('Page 1'));
		$tabs = $tabs;
		$d = new stdClass;
		$layout = JArrayHelper::getValue($canvas, 'layout', $d);
		$document = JFactory::getDocument();

		$opts = new stdClass;

		$opts->tabs = $tabs;
		$opts->blocks = $blocks;
		$opts->tabelement = 'packagemenu';
		$opts->pagecontainer = 'packagepages';
		$opts->layout = $layout;
		$opts = json_encode($opts);
		$this->js = "PackageCanvas = new AdminPackage($opts);
		new inline('#packagemenu li span');";
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
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$isNew = ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo = FabrikHelper::getActions($this->state->get('filter.category_id'));

		JToolBarHelper::title($isNew ? JText::_('COM_FABRIK_MANAGER_PACKAGE_NEW') : JText::_('COM_FABRIK_MANAGER_PACKAGE_EDIT'), 'package.png');

		if ($isNew)
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::apply('package.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('package.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::addNew('package.save2new', 'JTOOLBAR_SAVE_AND_NEW');
			}
			JToolBarHelper::cancel('package.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			// Can't save the record if it's checked out.
			if (!$checkedOut)
			{
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId))
				{
					JToolBarHelper::apply('package.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('package.save', 'JTOOLBAR_SAVE');
					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ($canDo->get('core.create'))
					{
						JToolBarHelper::addNew('package.save2new', 'JTOOLBAR_SAVE_AND_NEW');
					}
				}
			}
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::custom('package.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('package.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_FABRIK_PACKAGE_EDIT', false, JText::_('JHELP_COMPONENTS_FABRIK_PACKAGE_EDIT'));
	}
}
