<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Fabrik From Controller
 *
 * @static
 * @package		Joomla
 * @subpackage	Fabrik
 * @since 1.5
 */
class FabrikControllerElement extends JController
{

	var $isMambot = false;

	var $mode = false;

	/* @var int  id used from content plugin when caching turned on to ensure correct element rendered)*/
	var $cacheId = 0;

	/**
	 * Display the view
	 */

	function display()
	{
		$document = JFactory::getDocument();

		$viewName	= JRequest::getVar('view', 'element', 'default', 'cmd');
		$modelName = $viewName;

		$viewType	= $document->getType();
		// Set the default view name from the Request
		$view = &$this->getView($viewName, $viewType);

		// $$$ rob 04/06/2011 don't assign a model to the element as its only a plugin

		$view->editable = ($this->mode == 'readonly') ? false : true;

		// Display the view
		$view->assign('error', $this->getError());

		return $view->display();
	}

	/**
	 * save an individual element value to the fabrik db
	 * used in inline edit table plguin
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
?>