<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');
require_once JPATH_SITE . '/components/com_fabrik/views/form/view.base.php';

class fabrikViewForm extends FabrikViewFormBase
{

	/**
	 * Main setup routine for displaying the form/detail view
	 *
	 * @param   string  $tpl  template
	 *
	 * @return  void
	 */

	function display($tpl = null)
	{
		if (parent::display($tpl) !== false)
		{
			$document = JFactory::getDocument();
			$model = $this->getModel();
			$params = $model->getParams();
			$size = $params->get('pdf_size', 'A4');
			$orientation = $params->get('pdf_orientation', 'portrait');
			$document->setPaper($size, $orientation);
			$this->output();
		}
	}

	/**
	 * Set the page title
	 *
	 * @param   object  $w        parent worker
	 * @param   object  &$params  parameters
	 * @param   object  $model    form model
	 *
	 * @return  void
	 */

	protected function setTitle($w, &$params, $model)
	{
		parent::setTitle($w, $params, $model);

		// Set the download file name based on the document title
		$document = JFactory::getDocument();
		$document->setName($document->getTitle() . '-' . $model->getRowId());
	}

}
