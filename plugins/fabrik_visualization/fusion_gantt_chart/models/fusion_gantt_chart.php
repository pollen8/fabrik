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

require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models'.DS.'visualization.php');

class fabrikModelFusion_gantt_chart extends FabrikFEModelVisualization {


	function getChart()
	{
		// Include PHP Class#
		if (!class_exists('FusionCharts')) { 
			require_once(JPATH_SITE.DS.'plugins'.DS.'fabrik_visualization'.DS.'fusion_gantt_chart'.DS.'lib'.DS.'FCclass'.DS.'FusionCharts_Gen.php');
		}
		// Add JS to page header
		$document = JFactory::getDocument();
		$document->addScript($this->srcBase."fusion_gantt_chart/lib/FCCharts/FusionCharts.js");

		$params = $this->getParams();
		$w = $params->get('fusion_gantt_chart_width');
		$h = $params->get('fusion_gantt_chart_height');

		// Create new chart
		$this->fc = new FusionCharts("GANTT","$w","$h");

		// Define path to FC's SWF
		$this->fc->setSWFPath($this->srcBase."fusion_gantt_chart/lib/FCCharts/");


		$this->fc->setChartParam('dateFormat', 'yyyy-mm-dd');
		$this->fc->setChartParam('showTaskNames', 1);

		$this->fc->setChartParams('ganttWidthPercent=70;gridBorderAlpha=100;canvasBorderColor=333333;canvasBorderThickness=0;hoverCapBgColor=FFFFFF;hoverCapBorderColor=333333;extendcategoryBg=0;ganttLineColor=99cc00;ganttLineAlpha=20;baseFontColor=333333;gridBorderColor=99cc00');
		// ------------------- Setting Param string


		$listid = $params->get('fusion_gantt_chart_table');
		$listModel = JModel::getInstance('list', 'FabrikFEModel');
		$listModel->setId($listid);
		$formModel = $listModel->getFormModel();
		$db = $listModel->getDB();
		$table = $listModel->getTable()->db_table_name;
		$process = $params->get('fusion_gantt_chart_process');
		$processraw = $process.'_raw';
		$start = $params->get('fusion_gantt_chart_startdate');
		$startraw = $start .'_raw';
		$end = $params->get('fusion_gantt_chart_enddate');
		$endraw = $end . '_raw';
		$label = $params->get('fusion_gantt_chart_label');
		$hover = $params->get('fusion_gantt_chart_hover');
		$milestone = $params->get('fusion_gantt_chart_milestone');
		$milestoneraw = $milestone . '_raw';
		$connector = $params->get('fusion_gantt_chart_connector');
		$connectorraw = $connector . '_raw';
		$fields = array();
		$names = array();
		$uses = array($process, $start, $end, $label, $hover, $milestone, $connector);
		foreach ($uses as $use) {
			if ($use !== '') {
				$formModel->getElement($use)->getAsField_html($fields, $names);
			}
		}
		
		$listModel->asfields = $fields;
		$nav = $listModel->getPagination(0, 0, 0);
		$data = $listModel->getData();
		if (empty($data)) {
			JError::raiseNotice(500, 'No data found for gantt chart');
			return;
		}
		$groupByKeys = array_keys($data);
		//$data = $data[0];
		
		
		$mindate = null;
		$maxdate = null;
		$usedProcesses = array();
		$milestones = array();
		$connectors = array();
		$processLabel = $params->get('fusion_gantt_chart_process_label');
		$this->fc->setGanttProcessesParams("positionInGrid=right;align=center;headerText=$processLabel;fontColor=333333;fontSize=11;isBold=1;isAnimated=1;bgColor=99cc00;headerbgColor=333333;headerFontColor=99cc00;headerFontSize=16;bgAlpha=40");
		$c = 0;
		foreach ($groupByKeys as $groupByKey) {
			
			$groupedData = $data[$groupByKey];
			foreach ($groupedData as $d) {
				if ($c > 6) {
					//continue;
				}
				$hovertext = $hover == '' ? '' : $this->prepData($d->$hover);
				$processid = $process == '' ? 0 : $this->prepData($d->$processraw);
				if ($d->$startraw == $db->getNullDate()) {
					continue;
				}
				$startdate = JFactory::getDate($d->$startraw);
				if (isset($d->$endraw)) {
					$enddate = JFactory::getDate($d->$endraw);
				} else {
					$enddate = $startdate;
				}
				$strParam = "start=".$startdate->toFormat('%Y/%m/%d').";end=".$enddate->toFormat('%Y/%m/%d').";";
				if ($process !== '') {
					$strParam .= "processId={$processid};";
				}
				$strParam .= "id={$d->__pk_val};color=99cc00;alpha=60;topPadding=19;hoverText={$hovertext};";
				$strParam .="link={$d->fabrik_view_url};";
				
				if (isset($d->label)) {
				$l = $this->prepData($d->$label);
				} else {
					$l = '';
				}
				$this->fc->addGanttTask($l, $strParam);
	
				if ($milestone !== '' && $d->$milestoneraw == 1) {
					$this->fc->addGanttMilestone($d->__pk_val, "date=".$enddate->toFormat('%Y/%m/%d').";radius=10;color=333333;shape=Star;numSides=5;borderThickness=1");
				}
	
				if ($connector !== '' && $d->$connectorraw !== '') {
					$this->fc->addGanttConnector($d->$connectorraw, $d->__pk_val, "color=99cc00;thickness=2;");
				}
				#apply processes
				if (!in_array($processid, $usedProcesses) && $process !== '') {
					$usedProcesses[] = $processid;
					$this->fc->addGanttProcess($this->prepData($d->$process), "id={$processid};");
				}
				#increaes max/min date range
				if (is_null($mindate)) {
					$mindate = $startdate;
					$maxdate = $enddate;
				} else {
					if (JFactory::getDate($d->$startraw)->toUnix() < $mindate->toUnix()) {
						$mindate = JFactory::getDate($d->$startraw);
					}
	
					if ($enddate->toUnix() > $maxdate->toUnix()) {
						$maxdate = $enddate;
					}
					
				}
				$c ++;
			}
		}
		$startyear = $mindate ? $mindate->toFormat('%Y') : date('Y');
		$endyear = $maxdate ? $maxdate->toFormat('%Y') : 0;

		$monthdisplay = $params->get('fusion_gannt_chart_monthdisplay');
		$this->fc->addGanttCategorySet("bgColor=333333;fontColor=99cc00;isBold=1;fontSize=14");
		for ($y = $startyear; $y <= $endyear; $y++) {
			$firstmonth = ($y == $startyear) ? (int)$mindate->toFormat('%m') : 1;
			$lastmonth = ($y == $endyear) ? $maxdate->toFormat('%m')+1 : 13;

			$start = date('Y/m/d', mktime(0, 0, 0, $firstmonth, 1, $y));
			$end = date('Y/m/d', mktime(0, 0, 0, $lastmonth, 0, $y));

			$strParam = "start=". $start.";end=".$end.";";
			$this->fc->addGanttCategory($y, $strParam);
		}
		$this->fc->addGanttCategorySet("bgColor=99cc00;fontColor=333333;isBold=1;fontSize=10;bgAlpha=40;align=center");
		//echo "start year = $startyear end yera = $endyear ";exit;
		for ($y = $startyear; $y <= $endyear; $y++) {
			$lastmonth = ($y == $endyear) ? $maxdate->toFormat('%m')+1 : 13;
			$firstmonth = ($y == $startyear) ? (int)$mindate->toFormat('%m') : 1;
			for ($m = $firstmonth; $m < $lastmonth; $m++) {
				$starttime = mktime(0, 0, 0, $m, 1, $y);
				$start = date('Y/m/d', $starttime);
				$end = date('Y/m/d', mktime(0, 0, 0, $m+1, 0, $y));//use day = 0 to load last day of next month
				$m2 = $monthdisplay == 'str' ? JText::_(date('M', $starttime)) : $m;
				$this->fc->addGanttCategory($m2, "start=".$start.";end=".$end.";");
			}
		}

		# Render Chart
		return $this->fc->renderChart(false, false);
	}
	
	protected function prepData($d){
		$d = str_replace(array("\r\n", "\r", "\n", "\t"), '', $d);
		$d = $this->fc->encodeSpecialChars($d);
		return $d;
	}

	function setListIds()
	{
		if (!isset($this->listids)) {
			$params = $this->getParams();
			$this->listids = (array) $params->get('fusion_gantt_chart_table');
		}
	}

}
?>