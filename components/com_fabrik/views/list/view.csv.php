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

class FabrikViewList extends JViewLegacy
{

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */

	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$session = JFactory::getSession();
		$exporter = JModelLegacy::getInstance('Csvexport', 'FabrikFEModel');
		$model = JModelLegacy::getInstance('list', 'FabrikFEModel');
		$model->setId($input->getInt('listid'));
		$model->setOutPutFormat('csv');
		$exporter->model = $model;
		$input->set('limitstart' . $model->getId(), $input->getInt('start', 0));
		$input->set('limit' . $model->getId(), $exporter->_getStep());

		// $$$ rob moved here from csvimport::getHeadings as we need to do this before we get
		// the list total
		$selectedFields = $input->get('fields', array(), 'array');
		$model->setHeadingsForCSV($selectedFields);

		$request = $model->getRequestData();
		$model->storeRequestData($request);

		$total = $model->getTotalRecords();

		$key = 'fabrik.list.' . $model->getId() . 'csv.total';
		if (is_null($session->get($key)))
		{
			$session->set($key, $total);
		}

		$start = $input->getInt('start', 0);
		if ($start <= $total)
		{
			if ((int) $total === 0)
			{
				$notice = new stdClass;
				$notice->err = JText::_('COM_FABRIK_CSV_EXPORT_NO_RECORDS');
				echo json_encode($notice);
				return;
			}
			$exporter->writeFile($total);
		}
		else
		{
			$input->set('limitstart' . $model->getId(), 0);
			$session->clear($key);
			$exporter->downloadFile();
		}
		return;
	}

}
