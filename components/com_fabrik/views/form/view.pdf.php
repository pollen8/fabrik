<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');
require_once JPATH_SITE . '/components/com_fabrik/views/form/view.base.php';

class fabrikViewForm extends FabrikViewFormBase
{

  /**
	 * display the template
	 *
	 * @param sting $tpl
	 */

	function display($tpl = null)
	{
		if (parent::display($tpl) !== false)
		{
			$this->output();
		}
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
		$document->setName($document->getTitle() . '-' .  $model->getRowId());
	}

}
?>