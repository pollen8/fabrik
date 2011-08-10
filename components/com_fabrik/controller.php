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

require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'params.php');
require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'string.php');
require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'html.php');
require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'cache.php');

/**
 * Fabrik Component Controller
 *
 * @static
 * @package		Joomla
 * @subpackage	Contact
 * @since 1.5
 */


//$$$rob DEPRECIATED - should always get directed to specific controller

class FabrikController extends JController
{

	var $isMambot = false;

	/**
	 * Display the view
	 */
	function display()
	{
		//menu links use fabriklayout parameters rather than layout
		$flayout = JRequest::getVar('fabriklayout');
		if ($flayout != '') {
			JRequest::setVar('layout', $flayout);
		}
		$document = JFactory::getDocument();

		$viewName	= JRequest::getVar('view', 'form', 'default', 'cmd');
		$modelName = $viewName;
		if ($viewName == 'emailform') {
			$modelName = 'form';
		}

		if ($viewName == 'details') {
			//huh why was this here? - stopped detailed view from ever ever being loaded
			//JRequest::setVar('view', 'form');
			$viewName = 'form';
			$modelName = 'form';
		}

		$viewType	= $document->getType();
		// Set the default view name from the Request
		$view = &$this->getView($viewName, $viewType);

		// Push a model into the view
		$model	= &$this->getModel($modelName);
		if (!JError::isError($model) && is_object($model)) {
			$view->setModel($model, true);
		}

		// Display the view
		$view->assign('error', $this->getError());
		$cachable = false;
		if (($viewName = 'form' || $viewName = 'details') ) {
			$cachable = true;
		}
		$user = JFactory::getUser();

		if ($viewType != 'feed' && !$this->isMambot && $user->get('id') == 0) {
			$cache =& JFactory::getCache('com_fabrik', 'view');
			$cache->get($view, 'display');
		} else {
			return $view->display();
		}
	}

}
?>