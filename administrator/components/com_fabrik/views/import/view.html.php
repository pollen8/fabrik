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
 * View class for importing csv file.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       1.6
 */

class FabrikViewImport extends JViewLegacy
{

	/**
	 * Display the view
	 *
	 * @return  void
	 */

	public function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->addToolBar();
		FabrikHelper::setViewLayout($this);
		parent::display($tpl);
	}

	/**
	 * CSV file has been uploaded but we need to ask the user what to do with the new fields
	 *
	 * @return  void
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
		$this->assign('selectPKField', $this->get('SelectKey'));
		parent::display('chooseElementTypes');
	}

	/**
	 * Add the 'choose element type' page toolbar
	 *
	 * @return  void
	 */

	protected function chooseElementTypesToolBar()
	{
		JRequest::setVar('hidemainmenu', true);
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_LIST_IMPORT'), 'list.png');
		JToolBarHelper::custom('import.makeTableFromCSV', 'forward.png', 'forward.png', 'COM_FABRIK_CONTINUE', false);
		JToolBarHelper::cancel('import.cancel', 'JTOOLBAR_CANCEL');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 *
	 * @return  void
	 */

	protected function addToolBar()
	{
		JRequest::setVar('hidemainmenu', true);
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_LIST_IMPORT'), 'list.png');
		JToolBarHelper::custom('import.doimport', 'forward.png', 'forward.png', 'COM_FABRIK_CONTINUE', false);
		JToolBarHelper::cancel('import.cancel', 'JTOOLBAR_CANCEL');
	}

}
