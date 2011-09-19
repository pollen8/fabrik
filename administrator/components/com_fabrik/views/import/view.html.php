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
 * View class for importing csv file.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_fabrik
 * @since		1.6
 */

class FabrikViewImport extends JView
{

	/**
	 * Display the view
	 */

	public function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->addToolBar();
		parent::display($tpl);
	}

	/**
	 * csv file has been uploaded but we need to ask the user what to do with the new fields
	 */

	public function chooseElementTypes()
	{
		JRequest::setVar('hidemainmenu', true);
		$this->chooseElementTypesToolBar();
		$session = JFactory::getSession();
		$this->assign('data', $session->get('com_fabrik.csvdata'));
		$this->assign('matchedHeadings', $session->get('com_fabrik.matchedHeadings'));
		$this->assign('newHeadings', $this->get('NewHeadings'));
		$this->assign('headings', $this->get('Headings'));
		$pluginManager = $this->getModel('pluginmanager');
		$this->assign('table', $this->get('ListModel')->getTable());
		$this->assign('elementTypes', $pluginManager->getElementTypeDd('field', 'plugin[]'));
		$this->assign('sample', $this->get('Sample'));
		parent::display('chooseElementTypes');
	}

	protected function chooseElementTypesToolBar()
	{
		JRequest::setVar('hidemainmenu', true);
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_LIST_IMPORT'), 'list.png');
		JToolBarHelper::customX('import.makeTableFromCSV', 'forward.png', 'forward.png', 'Continue', false);
		JToolBarHelper::cancel('import.cancel', 'JTOOLBAR_CANCEL');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */

	protected function addToolBar()
	{
		JRequest::setVar('hidemainmenu', true);
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_LIST_IMPORT'), 'list.png');
		JToolBarHelper::save('import.doimport', 'JTOOLBAR_APPLY');
		JToolBarHelper::cancel('import.cancel', 'JTOOLBAR_CANCEL');
	}

}
