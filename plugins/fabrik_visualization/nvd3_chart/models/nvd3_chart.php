<?php
/**
 * Fabrik nvd3_chart Chart Plug-in Model
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.nvd3_chart
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/visualization.php';

/**
 * Fabrik nvd3_chart Chart Plug-in Model
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.nvd3_chart
 * @since       3.0.7
 */

class fabrikModelNvd3_chart extends FabrikFEModelVisualization
{

	/**
	 * Chart data
	 *
	 * @var array
	 */
	protected $data = null;

	/**
	 * Get sales per month per subscription plan
	 *
	 * @return  array
	 */

	public function getData()
	{
		if (isset($this->data))
		{
			return $this->data;
		}
		$params = $this->getParams();
		$script = $params->get('script', '');
		$fullPath = JPATH_ROOT . '/plugins/fabrik_visualization/nvd3_chart/scripts/' . $script;
		if ($script != '' && JFile::exists($fullPath))
		{
			require $fullPath;
		}
		else
		{
			$this->data = new stdClass;
			$this->data->key = 'todo2';
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);


			$tbl = $params->get('tbl', '');
			$value = $params->get('value_field');
			$label = $params->get('label_field');
			$query->select($label . ' AS label, ' . $value. ' AS value')->from($tbl);
			$db->setQuery($query);
			$this->data->values = $db->loadObjectList();
			$this->data = array($this->data);
		}
		return $this->data;
	}

	/**
	 * Build a Pie / Donut Chart Specific Options
	 *
	 * @return string js
	 */

	protected function pieOpts()
	{
		$params = $this->getParams();
		$str = array();
		$str[] = $params->get('pie_labels', true) ? '.showLabels(true)' : '.showLabels(false)';
		$str[] = $params->get('donut', false) ?  '.donut(true)' : '.donut(false)';
		return implode("\n", $str);
	}

	/**
	 * Standard bar chart options
	 *
	 * @return string js
	 */

	protected function discreteBarChartOpts()
	{
		$params = $this->getParams();
		$str = array();
		$str[] = '.showValues(true)';

		//$str[] = '.staggerLabels(true);';
		// Rotate the labels otherwise they get merged together
		$str[] = 'chart.xAxis.rotateLabels(-45);';

	//	$str[] = 'chart.xAxis.tickPadding(30);';

		// Additonal margin needed for rotated labels
		$str[] = 'chart.margin({bottom: 160, left: 60});';
		return implode("\n", $str);
	}

	/**
	 * Get chart js code
	 *
	 * @return string
	 */

	public function js()
	{
		$id = $this->getContainerId();
		$data = json_encode($this->getData());
		$params = $this->getParams();
		$chart = $params->get('chart', 'pieChart');
		$str[] = 'window.addEvent("domready", function () {';

		$str[] = 'nv.addGraph(function() {';
		$str[] = 'var chart = nv.models.' . $chart . '()';


		/* $str[] = '.x(function(d) { return d[0] })';
		$str[] = '.y(function(d) { return d[1] })'; */

		//$colors = explode(',', $params->get('colours', 'red,green,blue,orange'));
		$colors = explode(',', $params->get('colours', '#B9C872,#88B593,#388093,#994B89,#ED5FA2,#4D1018,#8F353E,#D35761,#43574E,#14303C'));
		if (!empty($colors))
		{
			$str[] = '.color(' . json_encode($colors) . ')';
		}

		switch ($chart)
		{
			case 'pieChart':
				$str[] = '.x(function(d) { return d.label })';
				$str[] = '.y(function(d) { return d.value })';
				$str[] = $this->pieOpts();
				break;
			case 'discreteBarChart':

				$str[] = '.x(function(d) { return d.label })';
				$str[] = '.y(function(d) { return d.value })';
				$str[] = $this->discreteBarChartOpts();
				break;
			case 'stackedAreaChart':
			case 'multiBarChart':
			case 'lineWithFocusChart':
				$str[] = '.x(function(d) { return d[0] })';


				$str[] = '.y(function(d) { return d[1] })';
				$str[] = '.clipEdge(true)';
				break;
		}
		//$str[] = 'chart.valueFormat = d3.format(",.2%");';


		$id = $this->getContainerId();
		$str[] = 'd3.select("#' . $id . ' svg")';
		$str[] = '.datum(' . $data . ')';

		$str[] = '.transition().duration(1200)';
		$str[] = '.call(chart);';
		
		// Rotate x axis labels without stoopid offset 
		$str[] = "d3.select('#" . $id . " .nv-x.nv-axis > g').selectAll('g').selectAll('text').attr('transform', function(d,i,j) { return 'translate (-10, 20) rotate(-45 0,0)' }) ;";
		$str[] = 'return chart;';
		$str[] = '});';
		$str[] = '});';
		// $str[] = 'console.log(' . $data . ');';
		return implode("\n", $str);
		/**

		 */
		$ref = $this->getJSRenderContext();

		$chart = json_encode($chart);

		$script = "
		 nv.addGraph(function() {
		 var chart = nv.models.multiBarChart()
                .x(function(d) { return d[0] })
                .y(function(d) {
                return d[1] })
                .color(d3.scale.category10().range());

	chart.xAxis.tickFormat(function(d) {
		return d3.time.format('%x')(new Date(d))
	});

	chart.yAxis
	.axisLabel('Sales (€)')
	.tickFormat(function(d) {
		return d;
	});


  d3.select('$ref svg')
      .datum(" . ($chart) . ")
    .transition().duration(500)
      .call(chart);


  //TODO: Figure out a good way to do this automatically
  nv.utils.windowResize(chart.update);

  return chart;
  });
  ";
return $script;
	}

}
