<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');
require_once(COM_FABRIK_FRONTEND . '/helpers/string.php');


class FabrikFEModelCSVExport {

	/** int number of records to output at a time */
	var $step = 100;

	var $outPutFormat = 'csv';

	public function _getStep()
	{
		return $this->model->getParams()->get('csv_export_step', $this->step);
	}

	function writeFile($total)
	{
		$app = JFactory::getApplication();
		// f3 turn off error reporting as this is an ajax call
		error_reporting(0);
		jimport('joomla.filesystem.file');
		$start = JRequest::getInt('start', 0);
		$filename = $this->getFileName();
		$filepath = $this->getFilePath();
		$str = '';
		if (JFile::exists($filepath)) {
			if ($start === 0) {
				JFile::delete($filepath);
			}else{
				$str = JFile::read($filepath);
			}
		} else {
			//fabrik3 odd cant pass 2nd param by reference if we try to write '' so assign it to $tmp first
			$tmp = '';
			$ok = JFile::write($filepath, $tmp);
			if (!$ok) {
				$this->reportWriteError($filepath);
				exit;
			}
			$str = '';
		}

		$session = JFactory::getSession();
		$table = $this->model->getTable();
		$this->model->render();
		$this->removePkVal();
		$this->outPutFormat = JRequest::getVar('excel') == 1 ? 'excel' : 'csv';
		$this->delimiter = $this->outPutFormat == 'excel' ? COM_FABRIK_EXCEL_CSV_DELIMITER : COM_FABRIK_CSV_DELIMITER;
		if ($start === 0) {
			$headings = $this->getHeadings();
			if (empty($headings)) {
				$url = JRequest::getVar('HTTP_REFERER', '', 'server');
				$app->redirect($url, JText::_('No data to export'));
				return;
			}

			$str .= implode($headings, $this->delimiter) . "\n";
		}

		$incRaw = JRequest::getVar('incraw', true);
		$incData = JRequest::getVar('inctabledata', true);

		$data = $this->model->getData();
		$exportFormat = $this->model->getParams()->get('csvfullname');
		$shortkey = FabrikString::shortColName($table->db_primary_key);

		foreach ($data as $group) {
			foreach ($group as $row) {

				$a = JArrayHelper::fromObject($row);
				if ($exportFormat == 1) {
					unset($a[$shortkey]);
				}
				if (!$incRaw) {
					foreach ($a as $key=>$val) {
						if (substr($key, JString::strlen($key) - 4, JString::strlen($key)) == "_raw") {
							unset($a[$key]);
						}
					}
				}
				if (!$incData) {
					foreach ($a as $key=>$val) {
						if (substr($key, JString::strlen($key) - 4, JString::strlen($key)) != "_raw") {
							unset($a[$key]);
						}
					}
				}

				if (JRequest::getVar('inccalcs') == 1) {
					array_unshift($a, ' ');
				}
				$this->carriageReutrnFix($a);
				$str .= implode($this->delimiter, array_map(array($this, "_quote"), array_values($a)));
				$str .= "\n";
			}
		}
		$res = new stdClass;
		$res->total = $total;
		$res->count = $start + $this->_getStep();
		$res->file= $filepath;
		$res->limitStart = $start;
		$res->limitLength = $this->_getStep();
		if ($res->count >= $res->total) {
			$this->_addCalculations($a, $str);
		}
		error_reporting(0);
		$ok = JFile::write($filepath, $str);
		if (!$ok) {
			$this->reportWriteError($filepath);
			exit;
		} else {
			echo json_encode($res);
		}
	}

	protected function reportWriteError($filepath)
	{
		$o = new stdClass;
		$o->err = 'cant write file ' . $filepath;
		echo json_encode($o);
	}

	private function carriageReutrnFix(&$row)
	{
		$newline = $this->model->getParams()->get('newline_csv_export', 'nl');
		switch ($newline) {
			default:
			case 'nl2br':
				if (is_array($row)) {
					foreach($row as &$val) {
						$val = nl2br( $val);
						$val = str_replace(array("\n", "\r", "\n\r", "\r\n"), '', $val);
					}
				} else {
					$row = nl2br( $row);
					$row = str_replace(array("\n", "\r", "\n\r", "\r\n"), '',  $row);
				}
				break;
			case 'nl':

				break;
			case 'remove':
				if (is_array($row)) {
					foreach($row as &$val) {
						$val = str_replace(array("\n", "\r", "\n\r", "\r\n"), '', $val);
					}
				} else {
					$row = str_replace(array("\n", "\r", "\n\r", "\r\n"), '', $row);
				}
				break;
		}
	}

	private function getFileName()
	{
		$this->model->setId(JRequest::getInt('listid'));
		$table 	=& $this->model->getTable();
		$filename = $table->db_table_name.'-export.csv';
		return $filename;
	}

	private function getFilePath()
	{
		$config = JFactory::getConfig();
		return $config->get('tmp_path') . '/' . $this->getFileName();
	}

	public function downloadFile()
	{
		//to prevent long file from getting cut off from     //max_execution_time
		error_reporting(0);
		@set_time_limit(0);

		jimport('joomla.filesystem.file');
		$filename = $this->getFileName();
		$filepath = $this->getFilePath();
		$document = JFactory::getDocument();
		$document->setMimeEncoding('application/zip');
		if (JFile::exists($filepath)) {
			$str = JFile::read($filepath);
		} else {
			// if we cant find the file then dont try to auto download it
			return false;
		}

		JResponse::clearHeaders();
		//// Set the response to indicate a file download
		JResponse::setHeader('Content-Type', 'application/zip');
		JResponse::setHeader('Content-Disposition', "attachment;filename=\"".$filename."\"");

		// xls formatting for accents
		JResponse::setHeader('Content-Type', 'application/vnd.ms-excel');
		JResponse::setHeader('charset', 'UTF-16LE');
		JResponse::setBody($str);
		echo JResponse::toString(false);
		JFile::delete($filepath);
		// $$$ rob 21/02/2012 - need to exit otherwise Chrome give 349 download error
		exit;
	}

	/**
	 * add calculations
	 *
	 * @param array of field elements $a
	 * @param string to out put as csv file $str
	 */

	function _addCalculations($a, &$str)
	{
		if (JRequest::getVar('inccalcs') == 1) {

			$calkeys = array('sums', 'avgs', 'medians', 'count');

			foreach ($calkeys as $calkey) {
				$aCalcs[$calkey] = array_fill(0, count($a)+1, ' ');
				$aCalcs[$calkey][0] = $calkey;
				$calcs = $this->model->getCalculations();
				foreach ($calcs[$calkey] as $key => $cal) {

					$x = 0;
					$found = false;
					// $$$rob if grouped data and calc split then get the formatted string as $cal['calc] wont exist below
					foreach ($a as $akey=>$aval) {
						if (trim($akey) == trim($key) && $x != 0) {
							$default = $calcs[$calkey][$akey];
						}
						$x ++;
					}
					$x = 0;

					foreach ($a as $akey=>$aval) {
						if ($akey == JString::substr($key, 0, JString::strlen($key)-4)&& $x != 0) {
							$found = true;
							break;
						}
						$x ++;
					}
					if ($found) {
						if (array_key_exists('calc', $cal)) {
							$aCalcs[$calkey][$x] = $cal['calc']->value;
						} else {
							$aCalcs[$calkey][$x] = $default;
						}
					}
				}
				$str .= implode($this->delimiter, array_map(array($this, "_quote"), array_values($aCalcs[$calkey])));
				$str .= "\n";
			}
		}
	}

	function _quote($n)
	{
		$n = '"'.str_replace('"', '""', $n).'"';
		// $$$ hugh - func won't exist if PHP wasn't built with MB string
		if (function_exists('mb_convert_encoding')) {
			if ($this->outPutFormat == 'excel') {
				//possible fix for Excel import of acents in csv file?
				return mb_convert_encoding($n, 'UTF-16LE', 'UTF-8');
			} else {
				return $n;
			}
		}	else {
			return $n;
		}
	}

	/**
	 * get the headings for the csv file
	 * @return array heading labels
	 */

	function getHeadings()
	{
		$w = new FabrikWorker;
		$table = $this->model->getTable();
		$params = $this->model->getParams();
		$hformat = $params->get('csvfullname');
		$data = $this->model->getData();
		$headings = array();
		$g = current($data);
		if (empty($g)) {
			return $g;
		}
		$selectedFields = JRequest::getVar('fields');
		$r = current($g);
		$formModel = $this->model->getFormModel();
		$groups = $formModel->getGroupsHiarachy();
		$h = array();
		if (!is_object($r)) {
			return new stdClass;
		}
		$incRaw = JRequest::getVar('incraw', true);
		$incData = JRequest::getVar('inctabledata', true);

		$shortkey = FabrikString::shortColName($table->db_primary_key);

		foreach ($r as $heading => $value) {
			$found = false;
			foreach ($groups as $groupModel) {
				$elementModels = $groupModel->getPublishedElements();
				foreach ($elementModels as $elementModel) {
					$element = $elementModel->getElement();
					$fullname = $elementModel->getFullName(false, true, false);

					if ($fullname == $heading || $fullname . "_raw" == $heading) {
						$found = true;
						switch ($hformat) {
							default:
							case '0':
								$n = $element->name;
								break;
							case '1':
								$n = $elementModel->getFullName(false, false, false);
							$label = $w->parseMessageForPlaceHolder($label, array());	break;
							case '2':
								$headingLabel = $elementModel->getParams()->get('alt_list_heading');
								$n = trim($headingLabel) === '' ? $element->label : $headingLabel;
								// $$$ hugh - added next line as special case for a client, do not remove!
								// (used in conjuntion with "Custom QS" option, to allow variable header labels
								$n = $w->parseMessageForPlaceHolder($n, array());
								break;
						}

						if ($fullname . "_raw" == $heading) {
							$n .= "_raw";
						}

						if ($incData && JString::substr($n, JString::strlen($n)-4, JString::strlen($n)) !== '_raw') {
							if (!in_array($n, $h)) { //only add heading once
								$h[] = $n;
							} else {
								$h[] = $this->uniqueHeading($n, $h);
							}
						}

						if ($incRaw && JString::substr($n, JString::strlen($n)-4, JString::strlen($n)) == "_raw") {
							if (!in_array($n, $h)) { //only add heading once
								$h[] = $n;
							} else {
								$h[] = $this->uniqueHeading($n, $h);
							}
						}
					}
				}
			}

			if (!$found) {
				if (!(substr($heading, JString::strlen($heading)-4, JString::strlen($heading)) == "_raw" && !$incRaw)) {
					//stop id getting added to tables when exported wiht fullelname key
					if ($hformat != 1 && $heading != $shortkey) {
						$h[] = $heading;
					}
				}
			}
		}
		if (JRequest::getVar('inccalcs') == 1) {
			array_unshift($h, JText::_('Calculation'));
		}

		$h = array_map(array($this, "_quote"), $h);
		return $h;
	}

	/**
	 * get unqiue heading
	 * @param string key $n
	 * @param array search $h
	 */

	protected function uniqueHeading($n, $h)
	{
		$c = 1;
		$newN = $n.'_'.$c;
		while (in_array($newN, $h)) {
			$c ++;
			$newN = $n.'_'.$c;
		}
		return $newN;
	}

	/**
	 * Remove the __pk_val from data
	 */
	
	function removePkVal()
	{
		$data = $this->model->getData();
		foreach ($data as $group) {
			foreach ($group as $row) {
				unset($row->__pk_val);
			}
		}
	}
}
?>
