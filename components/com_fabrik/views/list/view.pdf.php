<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       3.0.5
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');
require_once COM_FABRIK_FRONTEND . '/views/list/view.base.php';

/**
 * PDF List view
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0.5
 */

class FabrikViewList extends FabrikViewListBase
{

	/**
	 * Display the template
	 *
	 * @param   sting  $tpl  Template
	 *
	 * @return  void
	 */

	public function display($tpl = null)
	{
		if (!JFolder::exists(COM_FABRIK_BASE . '/libraries/dompdf'))
		{
			JError::raiseError(404, 'Please install the dompdf library');
			return;
		}
		$document = JFactory::getDocument();
		$model = $this->getModel();
		$params = $model->getParams();
		$size = $params->get('pdf_size', 'A4');
		$orientation = $params->get('pdf_orientation', 'portrait');
		$document->setPaper($size, $orientation);
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
	 * Build an object with the button icons based on the current tmpl
	 *
	 * @return  void
	 */

	protected function buttons()
	{
		// Don't add buttons as pdf is not interactive
		$this->buttons = new stdClass;
	}

	/**
	 * Set page title
	 *
	 * @param   object  $w        Fabrikworker
	 * @param   object  &$params  list params
	 * @param   object  $model    list model
	 *
	 * @return  void
	 */

	protected function setTitle($w, &$params, $model)
	{
		parent::setTitle($w, $params, $model);

		// Set the download file name based on the document title
		$document = JFactory::getDocument();
		$document->setName($document->getTitle());
	}

}
