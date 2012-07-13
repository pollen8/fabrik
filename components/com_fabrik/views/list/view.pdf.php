<?php

/**
 * @package		Joomla
 * @subpackage	Fabik
 * @copyright	Copyright (C) 2005 - 2008 Pollen 8 Design Ltd. All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');
require_once('components/com_fabrik/views/list/view.base.php');

class FabrikViewList extends FabrikViewListBase{

	/**
	 * display the template
	 * @param	sting	$tpl
	 */

	function display($tpl = null)
	{
		parent::display($tpl);
		$this->nav = '';
		$this->showPDF = false;
		$this->showRSS = false;
		$this->filters = array();
		$this->assign('showFilters', false);
		$this->assign('hasButtons', false);
		$this->output();
	}

	/**
	 * build an object with the button icons based on the current tmpl
	 */
	
	protected function buttons()
	{
		// dont add buttons as pdf is not interactive
		$this->buttons = new stdClass;
	}
	
	/**
	* (non-PHPdoc)
	* @see	FabrikViewFormBase::setTitle()
	*/
	
	protected function setTitle($w, &$params, $model)
	{
		parent:: setTitle($w, $params, $model);
		//set the download file name based on the document title
		$document = JFactory::getDocument();
		$document->setName($document->getTitle());
	}

}
?>