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

class fabrikModelFusionchart extends FabrikFEModelVisualization {


	function _getMinMax(&$totals)
	{
		// $min will only go lower if data is negative!
		$max = 0;
		$min = 0;
		foreach ($totals as $tots) {
			if (max($totals) > $max) {
				$max = max($totals);
			}
			if (min($totals) < $min) {
				$min = min($totals);
			}
		}
		return array('min' => $min, 'max' => $max);
	}

	protected function getChartParams()
	{
		$params = $this->getParams();
		$strParam = 'caption='.$params->get('fusionchart_caption', '');

		// Graph attributes
		$strParam .= ';palette='.$params->get('fusionchart_chart_palette', 1);
		if ($params->get('fusionchart_bgcolor')) {
			$strParam .= ';bgcolor='.$params->get('fusionchart_bgcolor', '');
		}
		if ($params->get('fusionchart_palette_colors')) {
			$strParam .= ';paletteColors='.$params->get('fusionchart_palette_colors', '');
		}
		if ($params->get('fusionchart_bgalpha')) {
			$strParam .= ';bgalpha='.$params->get('fusionchart_bgalpha', '');
		}
		if ($params->get('fusionchart_bgimg')) {
			$strParam .= ';bgSWF='.$params->get('fusionchart_bgimg', '');
		}
		// Canvas properties
		if ($params->get('fusionchart_cnvcolor')) {
			$strParam .= ';canvasBgColor='.$params->get('fusionchart_cnvcolor', '');
		}
		if ($params->get('fusionchart_cnvalpha')) {
			$strParam .= ';canvasBgAlpha='.$params->get('fusionchart_cnvalpha', '');
		}
		if ($params->get('fusionchart_bordercolor')) {
			$strParam .= ';canvasBorderColor='.$params->get('fusionchart_bordercolor', '');
		}
		if ($params->get('fusionchart_borderthick')) {
			$strParam .= ';canvasBorderThickness='.$params->get('fusionchart_borderthick', '');
		}
		// Chart and Axis Title, except caption
		if ($params->get('fusionchart_subcaption')) {
			$strParam .= ';subcaption='.$params->get('fusionchart_subcaption', '');
		}
		if ($params->get('fusionchart_xaxis_name')) {
			$strParam .= ';xAxisName='.$params->get('fusionchart_xaxis_name', '');
		}
		if ($params->get('fusionchart_yaxis_name')) {
			$strParam .= ';yAxisName='.$params->get('fusionchart_yaxis_name', '');
		}
		// Chart Limits
		if ($params->get('fusionchart_yaxis_minvalue')) {
			$strParam .= ';yAxisMinValue='.$params->get('fusionchart_yaxis_minvalue', '');
		}
		if ($params->get('fusionchart_yaxis_maxvalue')) {
			$strParam .= ';yAxisMaxValue='.$params->get('fusionchart_yaxis_maxvalue', '');
		}
		// General Properties
		if ($params->get('fusionchart_shownames') == '0') { // Default = 1
			$strParam .= ';shownames='.$params->get('fusionchart_shownames', '');
		}
		if ($params->get('fusionchart_showvalues') == '0') { // Default = 1
			$strParam .= ';showValues='.$params->get('fusionchart_showvalues', '');
		}
		if ($params->get('fusionchart_showlimits') == '0') { // Default = 1
			$strParam .= ';showLimits='.$params->get('fusionchart_showlimits', '');
		}
		if ($params->get('fusionchart_rotatenames') == '1') { // Default = 0
			$strParam .= ';rotateNames='.$params->get('fusionchart_rotatenames', '');
			if ($params->get('fusionchart_slantlabels') == '1') { // Default = 0
				$strParam .= ';slantLabels='.$params->get('fusionchart_slantlabels', '');
			}
		}
		if ($params->get('fusionchart_rotatevalues') == '1') { // Default = 0
			$strParam .= ';rotateValues='.$params->get('fusionchart_rotatevalues', '');
		}
		if ($params->get('fusionchart_values_inside') == '1') { // Default = 0
			$strParam .= ';placeValuesInside='.$params->get('fusionchart_values_inside', '');
		}
		if ($params->get('fusionchart_animation') == '0') { // Default = 1
			$strParam .= ';animation='.$params->get('fusionchart_animation', '');
		}
		if ($params->get('fusionchart_colshadow') == '0') { // Default = 1
			$strParam .= ';showColumnShadow='.$params->get('fusionchart_colshadow', '');
		}
		// Font Properties
		if ($params->get('fusionchart_basefont') != '0') {
			$strParam .= ';baseFont='.$params->get('fusionchart_basefont', '');
		}
		if ($params->get('fusionchart_basefont_size')) {
			$strParam .= ';baseFontSize='.$params->get('fusionchart_basefont_size', '');
		}
		if ($params->get('fusionchart_basefont_color')) {
			$strParam .= ';baseFontColor='.$params->get('fusionchart_basefont_color', '');
		}
		if ($params->get('fusionchart_outcnv_basefont') != '0') {
			$strParam .= ';outCnvBaseFont='.$params->get('fusionchart_outcnv_basefont', '');
		}
		if ($params->get('fusionchart_outcnv_basefont_color')) {
			$strParam .= ';outCnvBaseFontColor='.$params->get('fusionchart_outcnv_basefont_color', '');
		}
		if ($params->get('fusionchart_outcnv_basefont_size')) {
			$strParam .= ';outCnvBaseFontSize='.$params->get('fusionchart_outcnv_basefont_size', '');
		}
		// Number Formatting Options
		if ($params->get('fusionchart_num_prefix')) {
			$strParam .= ';numberPrefix='.$params->get('fusionchart_num_prefix', '');
		}
		if ($params->get('fusionchart_num_suffix')) {
			$strParam .= ';numberSuffix='.$params->get('fusionchart_num_suffix', '');
		}
		$strParam .= ';formatNumber='.$params->get('fusionchart_formatnumber', '');
		$strParam .= ';formatNumberScale='.$params->get('fusionchart_formatnumberscale', '');
		if ($params->get('fusionchart_decimal_sep')) {
			$strParam .= ';decimalSeparator='.$params->get('fusionchart_decimal_sep', '');
		}
		if ($params->get('fusionchart_thousand_sep')) {
			$strParam .= ';thousandSeparator='.$params->get('fusionchart_thousand_sep', '');
		}
		if ($params->get('fusionchart_decimal_precision')) {
			$strParam .= ';decimalPrecision='.$params->get('fusionchart_decimal_precision', '');
		}
		if ($params->get('fusionchart_divline_decimal_precision')) {
			$strParam .= ';divLineDecimalPrecision='.$params->get('fusionchart_divline_decimal_precision', '');
		}
		if ($params->get('fusionchart_limits_decimal_precision')) {
			$strParam .= ';limitsDecimalPrecision='.$params->get('fusionchart_limits_decimal_precision', '');
		}
		// Zero Plane
		if ($params->get('fusionchart_zero_thick')) {
			$strParam .= ';zeroPlaneThickness='.$params->get('fusionchart_zero_thick', '');
		}
		if ($params->get('fusionchart_zero_color')) {
			$strParam .= ';zeroPlaneColor='.$params->get('fusionchart_zero_color', '');
		}
		if ($params->get('fusionchart_zero_alpha')) {
			$strParam .= ';zeroPlaneAlpha='.$params->get('fusionchart_zero_alpha', '');
		}
		// Divisional Lines Horizontal
		if ($params->get('fusionchart_divline_number')) {
			$strParam .= ';numDivLines='.$params->get('fusionchart_divline_number', '');
		}
		if ($params->get('fusionchart_divline_color')) {
			$strParam .= ';divLineColor='.$params->get('fusionchart_divline_color', '');
		}
		if ($params->get('fusionchart_divline_thick')) {
			$strParam .= ';divLineThickness='.$params->get('fusionchart_divline_thick', '');
		}
		if ($params->get('fusionchart_divline_alpha')) {
			$strParam .= ';divLineAlpha='.$params->get('fusionchart_divline_alpha', '');
		}
		if ($params->get('fusionchart_divline_showvalue') != '1') { // Default = 1
			$strParam .= ';showDivLineValue='.$params->get('fusionchart_divline_showvalue', '');
		}
		if ($params->get('fusionchart_divline_alt_hgrid_color')) {
			$strParam .= ';showAlternateHGridColor=1';
			$strParam .= ';alternateHGridColor='.$params->get('fusionchart_divline_alt_hgrid_color', '');
			$strParam .= ';alternateHGridAlpha='.$params->get('fusionchart_divline_alt_hgrid_alpha', '');
		}
		// Divisional Lines Vertical
		if ($params->get('fusionchart_vdivline_number')) {
			$strParam .= ';numVDivLines='.$params->get('fusionchart_vdivline_number', '');
		}
		if ($params->get('fusionchart_vdivline_color')) {
			$strParam .= ';VDivLineColor='.$params->get('fusionchart_vdivline_color', '');
		}
		if ($params->get('fusionchart_vdivline_thick')) {
			$strParam .= ';VDivLineThickness='.$params->get('fusionchart_vdivline_thick', '');
		}
		if ($params->get('fusionchart_vdivline_alpha')) {
			$strParam .= ';VDivLineAlpha='.$params->get('fusionchart_vdivline_alpha', '');
		}
		if ($params->get('fusionchart_divline_alt_vgrid_color')) {
			$strParam .= ';showAlternateVGridColor=1';
			$strParam .= ';alternateVGridColor='.$params->get('fusionchart_divline_alt_vgrid_color', '');
			$strParam .= ';alternateVGridAlpha='.$params->get('fusionchart_divline_alt_vgrid_alpha', '');
		}
		// Hover Caption Properties
		if ($params->get('fusionchart_show_hovercap') != '1') {
			$strParam .= ';showhovercap='.$params->get('fusionchart_show_hovercap', '');
		}
		if ($params->get('fusionchart_hovercap_bgcolor')) {
			$strParam .= ';hoverCapBgColor='.$params->get('fusionchart_hovercap_bgcolor', '');
		}
		if ($params->get('fusionchart_hovercap_bordercolor')) {
			$strParam .= ';hoverCapBorderColor='.$params->get('fusionchart_hovercap_bordercolor', '');
		}
		if ($params->get('fusionchart_hovercap_sep')) {
			$strParam .= ';hoverCapSepChar='.$params->get('fusionchart_hovercap_sep', '');
		}
		// Chart Margins
		if ($params->get('fusionchart_chart_leftmargin')) {
			$strParam .= ';chartLeftMargin='.$params->get('fusionchart_chart_leftmargin', '');
		}
		if ($params->get('fusionchart_chart_rightmargin')) {
			$strParam .= ';chartRightMargin='.$params->get('fusionchart_chart_rightmargin', '');
		}
		if ($params->get('fusionchart_chart_topmargin')) {
			$strParam .= ';chartTopMargin='.$params->get('fusionchart_chart_topmargin', '');
		}
		if ($params->get('fusionchart_chart_bottommargin')) {
			$strParam .= ';chartBottomMargin='.$params->get('fusionchart_chart_bottommargin', '');
		}
		if ($params->get('fusionchart_connect_nulldata')) {
			$strParam .= ';connectNullData='.$params->get('fusionchart_connect_nulldata', 1);
		}
		return $strParam;
	}

	/**
	 * set the chart messsages
	 * @param $FC object fusion chart
	 * @return null
	 */

	protected function setChartMessages(&$FC)
	{
		$params = $this->getParams();
		// Graph Messages
		if ($params->get('fusionchart_message_loading')) {
			$FC->setChartMessage("PBarLoadingText=".$params->get('fusionchart_message_loading', 'Please Wait.The chart is loading...'));
		}
		if ($params->get('fusionchart_message_parsing_data')) {
			$FC->setChartMessage("ParsingDataText=".$params->get('fusionchart_message_parsing_data', 'Reading Data. Please Wait'));
		}
		if ($params->get('fusionchart_message_nodata')) {
			$FC->setChartMessage("ChartNoDataText=".$params->get('fusionchart_message_nodata', 'No data to display.'));
		}
	}

	private function _replaceRequest($msg)
	{
		$db = JFactory::GetDbo();
		$request = JRequest::get('request');
		foreach ($request as $key => $val) {
			if (is_string($val)) {
				// $$$ hugh - escape the key so preg_replace won't puke if key contains /
				$key = str_replace('/', '\/', $key);
				$msg = preg_replace("/\{$key\}/", $db->Quote(urldecode($val)), $msg);
			}
		}
		return $msg;
	}

	function getFusionchart()
	{
		$document =& JFactory::getDocument();
		$params =& $this->getParams();
		$worker = new FabrikWorker();
		$fc_version = $params->get('fusionchart_version', 'free_old');
		if ($fc_version == 'free_22') {
			require_once($this->pathBase.'fusionchart'.DS.'lib'.DS.'FusionChartsFree'.DS.'Code'.DS.'PHPClass'.DS.'Includes'.DS.'FusionCharts_Gen.php');
			$document->addScript($this->srcBase."fusionchart/lib/FusionChartsFree/JSClass/FusionCharts.js");
			$fc_swf_path = COM_FABRIK_LIVESITE.$this->srcBase."fusionchart/lib/FusionChartsFree/Charts/";
		}
		else if ($fc_version == 'pro_30') {
			require_once($this->pathBase.'fusionchart'.DS.'lib'.DS.'FusionCharts'.DS.'Code'.DS.'PHPClass'.DS.'Includes'.DS.'FusionCharts_Gen.php');
			$document->addScript($this->srcBase."fusionchart/lib/FusionCharts/Charts/FusionCharts.js");
			$fc_swf_path = COM_FABRIK_LIVESITE.$this->srcBase."fusionchart/lib/FusionCharts/Charts/";
		}
		else {
			require_once($this->pathBase.'fusionchart'.DS.'lib'.DS.'FCclass'.DS.'FusionCharts_Gen.php');
			$document->addScript($this->srcBase."fusionchart/lib/FCcharts/FusionCharts.js");
			$fc_swf_path = COM_FABRIK_LIVESITE.$this->srcBase."fusionchart/lib/FCcharts/";
		}

		$calc_prefixes = array('sum___', 'avg___', 'med___', 'cnt___');
		$calc_prefixmap = array('sum___' => 'sums', 'avg___' => 'avgs', 'med___' => 'medians', 'cnt___' => 'count');
		$w = $params->get('fusionchart_width');
		$h = $params->get('fusionchart_height');

		$chartType =$params->get('fusionchart_type');

		// Create new chart
		$FC = new FusionCharts("$chartType","$w","$h");
		//$FC->JSC["debugmode"]=true;
		// Define path to FC's SWF
		$FC->setSWFPath($fc_swf_path);

		$this->setChartMessages($FC);

		// Setting Param string
		$strParam = $this->getChartParams();

		$label_step_ratios = (array)$params->get('fusion_label_step_ratio');

		$x_axis_label 	= (array)$params->get('fusion_x_axis_label');
		$chartElements 	= (array)$params->get('fusionchart_elementList');

		$chartColours 	= (array)$params->get('fusionchart_colours');
		$listid 				= (array)$params->get('fusionchart_table');
		$axisLabels 		= (array)$params->get('fusionchart_axis_labels');
		foreach ($axisLabels as $axis_key => $axis_val) {
			//$worker->replaceRequest($axis_val);
			$axisLabels[$axis_key] = $worker->parseMessageForPlaceholder($axis_val, null, false);
		}
		$dual_y_parents 		= $params->get('fusionchart_dual_y_parent');
		$measurement_units =(array)$params->get('fusion_x_axis_measurement_unit');
		$legends  			= $params->get('fusiongraph_show_legend', '');
		$chartWheres = (array)$params->get('fusionchart_where');
		$c = 0;
		$gdata = array();
		$glabels = array();
		$gcolours = array();
		$gfills = array();
		$max =array();
		$min = array();

		$calculationLabels = array();
		$calculationData = array();
		$calcfound = false;

		$tmodels = array();

		$labelStep = 0;

		foreach ($listid as $tid) {
			$min[$c] = 0;
			$max[$c] = 0;

			if (!array_key_exists($tid, $tmodels)) {
				$listModel = null;
				$listModel = JModel::getInstance('list', 'FabrikFEModel');
				$listModel->setId($tid);
				$tmodels[$tid] = $listModel;
			}
			else {
			  	$listModel = $tmodels[$tid];
			}

			$table = $listModel->getTable();
			$form = $listModel->getForm();

			// $$$ hugh - adding plugin query, 2012-02-08
			if (array_key_exists($c, $chartWheres) && !empty($chartWheres[$c])) {
				$chartWhere = $this->_replaceRequest($chartWheres[$c]);
				$listModel->setPluginQueryWhere('fusionchart', $chartWhere);
			} else {
				// if no where clause, explicitly clear any previously set clause
				$listModel->unsetPluginQueryWhere('fusionchart');
			}

			// $$$ hugh - remove pagination BEFORE calling render().  Otherwise render() applies
			// session state/defaults when it calls getPagination, which is then returned as a cached
			// object if we call getPagination after render().  So call it first, then render() will
			// get our cached pagination, rather than vice versa.
			$nav = $listModel->getPagination(0, 0, 0);
			$listModel->render();
			//$listModel->doCalculations();
			$alldata = $listModel->getData();
			$cals = $listModel->getCalculations();
			$column = $chartElements[$c];
			//$measurement_unit = $measurement_units[$c];
			$measurement_unit = JArrayHelper::getValue($measurement_units, $c, '');
			$pref =substr($column, 0, 6);

			$label = JArrayHelper::getValue($x_axis_label, $c, '');

			$tmpgdata = array();
			$tmpglabels = array();
			$colour = array_key_exists($c , $chartColours) ? str_replace("#", '', $chartColours[$c]) : '';

			$gcolours[] = $colour;

			if (in_array($pref, $calc_prefixes)) {

				// you shouldnt mix calculation elements with normal elements when creating the chart
				// so if ONE calculation element is found we use the calculation data rather than normal element data
				// this is because a calculation element only generates one value, if want to compare two averages then
				//they get rendered as tow groups of data and on bar charts this overlays one average over the other, rather than next to it
				$calcfound = true;

				$column = substr($column, 6);
				$calckey = $calc_prefixmap[$pref];
				$caldata = JArrayHelper::getValue($cals[$calckey], $column.'_obj');
				if (is_array($caldata)) {
					foreach ($caldata as $k=>$o) {
						$calculationData[] = (float)$o->value;
						$calculationLabels[] = trim(strip_tags($o->label));
					}
				}
				if (!empty($calculationData)) {
					$max[$c] = max($calculationData);
					$min[$c] = min($calculationData);
				}

				$gdata[$c] = implode(',', $tmpgdata);
				$glabels[$c] = implode('|', $tmpglabels);
				// $$$ hugh - playing around with pie charts
				//$gsums[$c] = array_sum($tmpgdata);
				$gsums[$c] = array_sum($calculationData);
			} else {
				$origColumn = $column;
				$column = $column. "_raw"; //_raw fields are most likely to contain the value
				foreach ($alldata as $group) {
					foreach ($group as $row) {
						if (!array_key_exists($column, $row)) {
							//didnt find a _raw column - revent to orig
							$column = $origColumn;

							if (!array_key_exists($column, $row)) {
								JError::raiseWarning(E_NOTICE, $column . ': NOT FOUND - PLEASE CHECK IT IS PUBLISHED');
								continue;
							}
						}
						if (trim($row->$column) == '') {
							$tmpgdata[] = - 1;
						} else {
							$tmpgdata[] = (float)$row->$column;
						}
						$tmpglabels[] = !empty($label) ? strip_tags($row->$label) : '';
					}
					if (!empty($tmpgdata)) {
						$max[$c] = max($tmpgdata);
						$min[$c] = min($tmpgdata);
					}
					$gdata[$c] = implode(',', $tmpgdata);
					$glabels[$c] = implode('|', $tmpglabels);
					// $$$ hugh - playing around with pie charts
					$gsums[$c] = array_sum($tmpgdata);
				}
			}
			$c ++;
		}
		if ($calcfound) {
			$calculationLabels = array_reverse($calculationLabels);

			// $$$ rob implode with | and not ',' as :
			//$labels = explode('|',$glabels[0]); is used below
			//$glabels = array(implode(',', array_reverse($calculationLabels)));
			$glabels = array(implode('|', array_reverse($calculationLabels)));
 			// $$$ rob end
			$gdata =  array(implode(',', $calculationData));
		}

		// $$$ hugh - pie chart data has to be summed - the API only takes a
		// single dataset for pie charts.  And it doesn't make sense trying to
		// chart individual row data for multiple elements in a pie chart.
		// Also, labels need to be axisLabels, not $glabels
		switch ($chartType) {
			// Single Series Charts
			case 'AREA2D':
			case 'BAR2D':
			case 'COLUMN2D':
			case 'COLUMN3D':
			case 'DOUGHNUT2D':
			case 'DOUGHNUT3D':
			case 'LINE':
			// $$$ tom - for now I'm enabling Pie charts here so that it displays
			// something until we do it properly as you said hugh
			// Well maybe there's something I don't get but in fact FC already draw
			// the pies by "percenting" the values of each data... if you know what I mean Hugh;)
			case 'PIE2D':
			case 'PIE3D':
			case 'SCATTER':

				// Adding specific params for Pie charts
				if ($chartType == 'PIE2D' || $chartType == 'PIE3D') {
					$strParam .= ';pieBorderThickness='.$params->get('fusionchart_borderthick', '');
					$strParam .= ';pieBorderAlpha='.$params->get('fusionchart_cnvalpha', '');
					$strParam .= ';pieFillAlpha='.$params->get('fusionchart_elalpha', '');
				}

				if ($c > 1) {
					$arrCatNames = array();
					foreach ($axisLabels as $alkey => $al) {
						$arrCatNames[] = $al;
					}
					$arrData = array();
					$i = 0;

					foreach ($gsums as $gd) {
						$arrData[$i][0] = $axisLabels[$i];
						$arrData[$i][1] = $gd;
						$i++;
					}
					$FC->addChartDataFromArray($arrData, $arrCatNames);
				}
				else {
					// single table/elements, so use the row data
					$labels = explode('|', $glabels[0]);
					$gsums = explode(',', $gdata[0]);
					// scale to percentages
					$tot_sum = array_sum($gsums);
					$arrData = array();

					$labelStep = 0;
					$label_step_ratio = (int)JArrayHelper::getValue($label_step_ratios, 0, 1);
					if ($label_step_ratio > 1) {
						$labelStep = (int)(count($gsums) / $label_step_ratio);
						$strParam .= ";labelStep=$labelStep";
					}
					//$$$tom: inversing array_combine as identical values in gsums will be
					// dropped otherwise. Should I do that differently?
					// $$$ hugh - can't use array_combine, as empty labels end up dropping values
					//$arrComb = array_combine($labels, $gsums);
					//foreach ($arrComb as $key => $value) {
					$data_count = 0;
					foreach ($gsums as $key => $value) {
						$data_count++;
						if ($value == '-1') {
							$value = null;
						}
						$label = $labels[$key];
						$str_params = "name=$label";
						if ($labelStep) {
							if ($data_count != 1 && $data_count % $labelStep != 0) {
								$str_params .= ";showName=0";
							}
						}
						$FC->addChartData("$value",$str_params);
					}

				}
				break;
			case 'MSBAR2D':
			case 'MSBAR3D':
			case 'MSCOLUMN2D':
			case 'MSCOLUMN3D':
			case 'MSLINE':
			case 'MSAREA2D':
			case 'MSCOMBIDY2D':
			case 'MULTIAXISLINE':

			//case 'PIE2D':
			//case 'PIE3D':
			case 'STACKEDAREA2D':
			case 'STACKEDBAR2D':
			case 'STACKEDCOLUMN2D':
			case 'STACKEDCOLUMN3D':

			case 'SCROLLAREA2D':
			case 'SCROLLCOLUMN2D':
			case 'SCROLLLINE2D':
			case 'SCROLLSTACKEDCOLUMN2D':

				/*$chd = implode('|', $gdata);
				if (!empty($chds_override)) {
					$chds = $chds_override;
				}
				else if ($c > 1 && !$calcfound) {
					$minmax = $this->_getMinMax($gsums);
					$chds = $minmax['min'] . ',' . $minmax['max'];
				}
				else {
					$chds = $min.','.$max;
				}
				if ($calcfound) {
					$glabels = array(implode('|', $calculationLabels));
				}
				// $$$ hugh - we have to reverse the labels for horizontal bar charts
				$glabels[0] = implode('|',array_reverse(explode('|',$glabels[0])));
				if (empty($chxl_override)) {
					$chxl = '0:|'.$min.'|'.$max.$measurement_unit.'|'.'1:|'.$glabels[0];
				}
				else {
					$chxl = '0:|'.$chxl_override.'|'.'1:|'.$glabels[0];
				}
				break;
				*/
				if ($c > 1) {

					if ($chartType == 'SCROLLAREA2D' || $chartType == 'SCROLLCOLUMN2D' || $chartType == 'SCROLLLINE2D') {
						$strParam .= ';numVisiblePlot='.$params->get('fusionchart_scroll_numvisible', 0);
					}
				    // $$$ hugh - Dual-Y types
				    if ($chartType == 'MSCOMBIDY2D' || $chartType == 'MULTIAXISLINE') {
					    //var_dump($axisLabels);
					    /*
					    $strParam .= ';PYAxisName='.$axisLabels[0];
					    $strParam .= ';SYAxisName='.$axisLabels[1];
					     */
					    $p_parents = array();
					    $s_parents = array();

					    foreach ($dual_y_parents as $dual_y_key => $dual_y_parent) {
						    if ($dual_y_parent == "P") {
							    $p_parents[] = $axisLabels[$dual_y_key];
						    }
						    else {
							    $s_parents[] = $axisLabels[$dual_y_key];
						    }
					    }
					    $strParam .= ';PYAxisName=' . implode(' ', $p_parents);
					    $strParam .= ';SYaxisName=' . implode(' ', $s_parents);
				    }

					//$$$tom: This is a first attempt at integrating Trendlines but it's not actually working... :s
					$eltype = $params->get('fusionchart_element_type', 'dataset');
					for ($nbe = 0; $nbe < $c; $nbe++) {
						if ($eltype[$nbe] != 'dataset') {
							// Trendline Start & End values
							$trendstart = $params->get('fusionchart_trendstartvalue', '');
							$trendend = $params->get('fusionchart_trendendvalue', '');
								if ($trendstart) {
									$startval = $trendstart;
									$endval = $trendend;
								} else if ($eltype[$nbe] == 'trendline') {
									// If Start & End values are not specifically defined, use the element's min & max values
									$startval = $min[$nbe];
									$endval = $max[$nbe];
								}
								$strAddTrend = "startValue=$startval;endValue=$endval";
								// Label
								$displayval = $params->get('fusionchart_trendlabel', '');
								$showontop = $params->get('fusionchart_trendshowontop', '1');
								$iszone = $params->get('fusionchart_trendiszone', '0');
								$elcolour = $params->get('fusionchart_elcolour', '');
								$elalpha = $params->get('fusionchart_elalpha', '');
								//$strAddTrend .= ";displayvalue=".$displayval;
								$strAddTrend .= ";displayvalue=".$axisLabels[$nbe];
								$strAddTrend .= ";showOnTop=".$showontop;
								if ($startval < $endval) {
									$strAddTrend .= ";isTrendZone=".$iszone;
								}
								$strAddTrend .= ";color=".$elcolour[$nbe];
								$strAddTrend .= ";alpha=".$elalpha[$nbe];
								$strAddTrend .= ";thickness=3";
								//var_dump($strAddTrend);
								$FC->addTrendLine("$strAddTrend");
								unset($axisLabels[$nbe]);
								unset($gdata[$nbe]);

						}
					} // end for loop

/*------------------------------------------
* $$$tom: I'm trying something else, as per http://www.fusioncharts.com/free/docs/Contents/PHPClassAPI/MultiSeriesChart.html
* A MS Chart should use for example 2 elements from the same table and have Categories
* (the colored legend below the chart, e.g. "This month", "Previous month").
* Then different Datasets must be defined (e.g. one by row in the table: "Week 1", "Week 2", ...)
* And finally some Data for each Dataset (e.g. Sales: "40200", "38350", ...)
* Of course I kept what you made Hugh, I commented out between this comment and what I've added.
------------------------------------------*/
					/*if ($calcfound) {
							$glabels = array(implode('|', $calculationLabels));
						}
						$arrCatNames = array();
						foreach ($axisLabels as $alkey => $al) {
							$arrCatNames[] = $al;
						}

						$arrData = array();
						$i = 0;
						foreach ($gdata as $gdkey => $gd) {
							$arrData[$i][0] = $glabels[$i];
							$arrData[$i][1] = '';
							$arrData[$i] = array_merge($arrData[$i], explode(',', $gd));
							$i++;
						}

						$FC->addChartDataFromArray($arrData, $arrCatNames);*/
						$label_step_ratio = (int)JArrayHelper::getValue($label_step_ratios, 0, 1);
						if ($label_step_ratio > 1) {
							$labelStep = (int)(count(explode(',',$gdata[0])) / $label_step_ratio);
							$strParam .= ";labelStep=$labelStep";
						}
						// Start tom's changes
						$labels = explode('|',$glabels[0]);
						$data_count = 0;
						foreach ($labels as $catLabel) {
							$data_count++;
							$catParams = '';
							if ($labelStep) {
								if ($data_count == 1 || $data_count % $labelStep == 0) {
									$catParams = "ShowLabel=1";
								}
								else {
									$catParams = "ShowLabel=0";
									$catLabel = '';
								}
							}
							$FC->addCategory($catLabel, $catParams);
						}
						foreach ($gdata as $key => $chartdata) {
							$cdata = explode(',',$chartdata);
							$dataset = $axisLabels[$key];
							$extras = "parentYAxis=".$dual_y_parents[$key];
							//var_dump($dual_y_parents, $dataset, $extras);
							$FC->addDataset("$dataset",$extras);
							$data_count = 0;
							foreach ($cdata as $key => $value) {
								$data_count++;
								if ($value == '-1') {
									$value = null;
								}
								$FC->addChartData("$value");
							}
						}
						// End tom's changes
					} else {
						//$foo = $foo;
					}
				/*
			default:
				$chd = implode('|', $gdata);
				$chxl = '0:|'.$glabels[0].'|'.'1:|'.$min.'|'.$max;
				if ($c > 1 && !$calcfound) {
					$minmax = $this->_getMinMax($gsums);
					$chds = $minmax['min'] . ',' . $minmax['max'];
				}
				else {
					$chds = $min.','.$max;
				}
				break;
		*/}
		$colours = implode(($calcfound ? '|': ','), $gcolours);

		/*$return = '<img src="' . $this->_url . '?';
		$qs =  'chs='.$w.'x'.$h;
		$qs .= '&amp;chd=t:'.$chd;
		$qs .= '&amp;cht='.$graph;
		$qs .= '&amp;chco='.$colours;
		$qs .= '&amp;chxt=x,y';
		$qs .= '&amp;chxl='.$chxl;
		$qs .= '&amp;chds='.$chds;
		if ($fillGraphs) {
			$qs .=  '&amp;chm=' . implode('|', $gfills);
		}
		if ($legends) {
			$qs .= '&amp;chdl=' . implode('|', $axisLabels);
		}
		if (!empty($chg_override)) {
			$qs .= '&amp;chg=' . $chg_override;
		}
		if (!empty($chm_override)) {
			$qs .= '&amp;chm=' . $chm_override;
		}
		else if ($fillGraphs) {
			$qs .=  '&amp;chm=' . implode('|', $gfills);
		}
		$return .= $qs . '" alt="'.$this->_row->label.'" />';
		$this->image =  $return;*/

	# Set chart attributes
  	if ($params->get('fusionchart_custom_attributes', '')) {
  		$strParam .=  ';' . trim($params->get('fusionchart_custom_attributes'));
  	}
		$strParam="$strParam";
  	$FC->setChartParams($strParam);


		# Render Chart
		if ($chartType == 'MULTIAXISLINE') {
  			// Nasty, nasty hack for MULTIAXIS, as the FC class doesn't support it.  So need to get the chart XML,
  			// split out the <dataset>...</dataset> and wrap them in <axis>...</axis>
  			$axis_attrs = (array)$params->get('fusionchart_mx_attributes');
  			$dataXML = $FC->getXML();
  			$matches = array();
  			if (preg_match_all('#(<\s*dataset[^>]*>.*?<\s*/dataset\s*>)#', $dataXML, $matches)) {
  				$index = 0;
  				foreach ($gdata as $key => $chartdata) {
  					$axis = "<axis " . $axis_attrs[$index] . ">" . $matches[0][$index] . "</axis>";
  					$dataXML = str_replace($matches[0][$index], $axis, $dataXML);
  					$index++;
  				}
  			}
  			return $FC->renderChartFromExtXML($dataXML);
  		}
  		else {
  			return $FC->renderChart(false, false);
  		}
	}

	function setListIds()
	{
		if (!isset($this->listids)) {
			$params = $this->getParams();
			$this->listids = (array)$params->get('fusionchart_table');
		}
	}

}
?>