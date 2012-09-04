<?php
/**
 * Fabrik Element Controller
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Fabrik Element Controller
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       1.5
 */

class FabrikControllerElement extends JController
{

	/**
	 * Is the view rendered from the J content plugin
	 *
	 * @var  bool
	 */
	var $isMambot = false;

	/**
	 * Should the element be rendered as readonly
	 *
	 * @var  string
	 */
	var $mode = null;

	/**
	 * Id used from content plugin when caching turned on to ensure correct element rendered
	 *
	 * @var  int
	 */
	var $cacheId = 0;

	/**
	 * Display the view
	 *
	 * @return  null
	 */

	public function display()
	{
		$document = JFactory::getDocument();

		$viewName = JRequest::getVar('view', 'element', 'default', 'cmd');
		$modelName = $viewName;

		$viewType = $document->getType();

		// Set the default view name from the Request
		$view = &$this->getView($viewName, $viewType);

		// $$$ rob 04/06/2011 don't assign a model to the element as its only a plugin
		$view->_editable = ($this->mode == 'readonly') ? false : true;

		// Display the view
		$view->assign('error', $this->getError());

		return $view->display();
	}

	/**
	 * Save an individual element value to the fabrik db
	 * used in inline edit table plguin
	 *
	 * @return  null
	 */

	function save()
	{
		$listModel = $this->getModel('list', 'FabrikFEModel');
		$listModel->setId(JRequest::getInt('listid'));
		$rowId = JRequest::getVar('rowid');
		$key = JRequest::getVar('element');
		$key = array_pop(explode("___", $key));
		$value = JRequest::getVar('value');
		$listModel->storeCell($rowId, $key, $value);
		$this->mode = 'readonly';
		$this->display();
	}

}
