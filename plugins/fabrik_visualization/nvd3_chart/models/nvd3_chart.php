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
	 * Chart script can set post text - shown after viz
	 *
	 * @var string
	 */
	public $postText = '';

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
			$query->select($label . ' AS label, ' . $value . ' AS value')->from($tbl);
			$db->setQuery($query);
			$this->data->values = $db->loadObjectList();
			$this->data = array($this->data);
		}
		return $this->data;
	}

	/**
	 * Helper function for custom scripts (may be that we incorporate this as the default handler when its
	 * more mature
	 *
	 * @param   int  $elementId  Element id to get data for
	 *
	 * @return  array  Chart data
	 */
	function getElementData($elementId)
	{
		$pluginManager = FabrikWorker::getPluginManager();

		$elementModel = $pluginManager->getPluginFromId($elementId);
		$params = $elementModel->getParams();
		$element = $elementModel->getElement();

		// Get labels
		$sub_options = $params->get('sub_options');
		$sub_values = $sub_options->sub_values;
		$sub_labels = $sub_options->sub_labels;
		$labels = array_combine($sub_values, $sub_labels);

		// Get the column data
		$listModel = $elementModel->getListModel();
		$opts = array();
		$opts['filterLimit'] = false;
		$rows = $listModel->getColumnData($elementId, false, $opts);

		$total = count($rows);

		switch ($element->plugin)
		{
			case 'checkbox':
				$data = $this->checkbox($rows, $labels);
				break;
			default:
				$data = $this->radio($rows, $labels);
				break;
		}
		return $this->elementDataToNvs3($data);
	}

	public function elementDataToNvs3($data)
	{
		// Set to nvd3 format, ready for json encoding
		$this->data = new stdClass;
		$this->data->key = 'todo2';
		$this->data->values = array();

		foreach ($data as $d)
		{
			$this->data->values[] = $d;
		}
		$this->data = array($this->data);
		return $this->data;
	}

	/**
	 * Helper function to build data set from checkbox element data (json encoded)
	 *
	 * @param   array  $rows    Element data
	 * @param   array  $labels  Element sub option labels keyed on sub option values
	 *
	 * @return  multitype:stdClass
	 */

	protected function checkbox($rows, $labels)
	{
		$data = array();
		foreach ($rows as $row)
		{
			$vals = json_decode($row);
			foreach ($vals as $val)
			{
				if (!is_null($val))
				{
					if (!array_key_exists($val, $data))
					{
						$o = new stdClass;
						$o->label = $labels[$val];
						$o->value = 1;
						$data[$val] = $o;
					}
					else
					{
						$data[$val]->value ++;
					}
				}
			}
		}
		return $data;
	}

	/**
	 * Helper function to build data set from radio/default element data (plain text)
	 *
	 * @param   array  $rows    Element data
	 * @param   array  $labels  Element sub option labels keyed on sub option values
	 *
	 * @return  multitype:stdClass
	 */

	protected function radio($rows, $labels)
	{
		$suggestions = array();
		$data = array();
		foreach ($rows as $val)
		{
			if (!is_null($val))
			{
				if (array_key_exists($val, $labels))
				{
					if (!array_key_exists($val, $data))
					{
						$o = new stdClass;
						$o->label = $labels[$val];
						$o->value = 1;
						$data[$val] = $o;
					}
					else
					{
						$data[$val]->value ++;
					}
				}
				else
				{
					if (trim($val) !== '')
					{
						$suggestions[] = str_replace('u00e9', 'é', $val);
					}
				}
			}
		}
		$this->suggestions = $suggestions;
		return $data;
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

		// $str[] = '.staggerLabels(true);';
		// Rotate the labels otherwise they get merged together
		$rotate = (int) $params->get('rotate_labels', 0);
		if ($rotate !== 0)
		{
			$str[] = 'chart.xAxis.rotateLabels(-' . $rotate . ');';
		}
		// Additonal margin needed for rotated labels
		if ($params->get('margin', '') !== '')
		{
			$str[] = 'chart.margin({bottom: 160, left: 60});';
		}
		return implode("\n", $str);
	}

	/**
	 * Should the chart show controls for swapping between views
	 *
	 * @param   array  &$str  JS output
	 *
	 * @return void
	 */

	protected function showControls(&$str)
	{
		$allowed = array('stackedAreaChart', 'multiBarChart', 'lineWithFocusChart', 'multiBarHorizontalChart');
		$params = $this->getParams();
		$chart = $params->get('chart', 'pieChart');
		$controls = $params->get('controls', 0);
		if ($controls == 0 && in_array($chart, $allowed))
		{
			$str[] = 'chart.showControls(false);';
		}
	}

	/**
	 * Get chart js code
	 *
	 * @return string
	 */

	public function js()
	{
		$id = $this->getContainerId();
		$rawData = $this->getData();
		$data = json_encode($rawData);
		$params = $this->getParams();
		$chart = $params->get('chart', 'pieChart');
		$str[] = 'window.addEvent("domready", function () {';

		$str[] = 'nv.addGraph(function() {';
		$str[] = 'var chart = nv.models.' . $chart . '()';

		if (!empty($rawData) && !isset($rawData[0]->color))
		{
			// $colors = explode(',', $params->get('colours', 'red,green,blue,orange'));
			$colors = explode(',', $params->get('colours', '#B9C872,#88B593,#388093,#994B89,#ED5FA2,#4D1018,#8F353E,#D35761,#43574E,#14303C'));
			if (!empty($colors))
			{
				$str[] = '.color(' . json_encode($colors) . ')';
			}
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
			case 'multiBarHorizontalChart':
				$str[] = '.x(function(d) { return d.label })';
				$str[] = '.y(function(d) { return d.value })';
				break;

		}

		$margin = $params->get('margin', '');
		if ($margin !== '')
		{
			$margin = explode(',', $margin);
			if (count($margin) == 4)
			{
				$str[] = 'chart.margin({top: ' . (int) $margin[0] . ',right: ' . (int) $margin[1] . ', bottom: ' . (int) $margin[2] . ', left: ' . (int) $margin[3] . '});';
			}
		}

		$this->showControls($str);
		// $str[] = 'chart.valueFormat = d3.format(",.2%");';

		$id = $this->getContainerId();
		$str[] = 'd3.select("#' . $id . ' svg")';

		$str[] = '.datum(' . $data . ')';
		$str[] = '.transition().duration(1200)';
		$str[] = '.call(chart);';

		$rotate = (int) $params->get('rotate_labels', 45);
		if ($rotate !== 0)
		{
			// Rotate x axis labels without stoopid offset
			$str[] = "d3.select('#" . $id . " .nv-x.nv-axis > g').selectAll('g').selectAll('text').attr('transform', function(d,i,j) {";
			$str[] = "return 'translate (-10, 20) rotate(-" . $rotate . " 0,0)'";
			$str[] = "}) ;";

		}
		$str[] = 'return chart;';
		$str[] = '});';
		$str[] = '});';
		return implode("\n", $str);
		/**

		 */
		$ref = $this->getJSRenderContext();

		$chart = json_encode($chart);

		$script = "
				console.log('this');
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
