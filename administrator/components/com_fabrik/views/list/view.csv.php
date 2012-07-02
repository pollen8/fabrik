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
 * View to make ajax json object reporting csv file creation progress.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since		1.5
 */
class FabrikViewList extends JView
{

	/**
	 * Display the list
	 */
	public function display($tpl = null)
	{
		$session = JFactory::getSession();
		//JModel::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
		$exporter = JModel::getInstance('Csvexport', 'FabrikFEModel');
		$model = JModel::getInstance('list', 'FabrikFEModel');
		$model->setId(JRequest::getInt('listid'));
		$model->set('outPutFormat', 'csv');
		$exporter->model =& $model;
		JRequest::setVar('limitstart'.$model->getId(), JRequest::getInt('start', 0));
		JRequest::setVar('limit'.$model->getId(), $exporter->getStep());
		//$model->limitLength = $exporter->getStep();

		// $$$ rob moved here from csvimport::getHeadings as we need to do this before we get
		// the table total
		$selectedFields = JRequest::getVar('fields', array(), 'default', 'array');
		$model->setHeadingsForCSV($selectedFields);

		$total 	= $model->getTotalRecords();

		$key = 'fabrik.list.'.$model->getId().'csv.total';
		if (is_null($session->get($key)))
		{
			$session->set($key, $total);
		}

		$start = JRequest::getInt('start', 0);
		if ($start <= $total)
		{
			$exporter->writeFile($total);
		}
		else
		{
			$session->clear($key);
			$exporter->downloadFile();
		}
		return;
	}

}