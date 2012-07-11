<?php
/**
 * @package		Joomla.Plugin
 * @subpackage	Fabrik.visualization.timeline
 * @copyright	Copyright (C) 2005 Fabrik. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/visualization.php';

/**
 * Renders timeline visualization
 *
 * @package		Joomla.Plugin
 * @subpackage	Fabrik.visualization.timeline
 *
 */
class fabrikModelTimeline extends FabrikFEModelVisualization
{

	/**
	 * Internally render the plugin, and add required script declarations
	 * to the document
	 *
	 * @return  string  js ini
	 */

	public function render()
	{
		$app = JFactory::getApplication();
		$params = $this->getParams();
		$document = JFactory::getDocument();
		$w = new FabrikWorker;

		$document->addScript('http://static.simile.mit.edu/timeline/api-2.3.0/timeline-api.js?bundle=true');
		$c = 0;
		$templates = (array) $params->get('timeline_detailtemplate', array());
		$startdates = (array) $params->get('timeline_startdate', array());
		$enddates = (array) $params->get('timeline_enddate', array());
		$labels = (array) $params->get('timeline_label', array());
		$colours = (array) $params->get('timeline_colour', array());
		$textColours = (array) $params->get('timeline_text_color', array());
		$classNames = (array) $params->get('timeline_class', array());

		$timeZone = new DateTimeZone(JFactory::getConfig()->get('offset'));

		$lists = $params->get('timeline_table', array());
		$eventdata = array();
		foreach ($lists as $listid)
		{
			$template = JArrayHelper::getValue($templates, $c);
			$listModel = JModel::getInstance('List', 'FabrikFEModel');
			$listModel->setId($listid);
			$table = $listModel->getTable();
			$nav = $listModel->getPagination(0, 0, 0);

			$colour = JArrayHelper::getValue($colours, $c);
			$startdate = JArrayHelper::getValue($startdates, $c);
			$enddate = JArrayHelper::getValue($enddates, $c);
			$title = JArrayHelper::getValue($labels, $c);
			$textColour = JArrayHelper::getValue($textColours, $c);
			$className = JArrayHelper::getValue($classNames, $c);

			$data = $listModel->getData();

			if ($listModel->canView() || $listModel->canEdit())
			{
				$elements = $listModel->getElements();
				$enddate2 = $enddate;
				$startdate2 = $startdate;
				$endKey = FabrikString::safeColName($enddate2);
				$startKey = FabrikString::safeColName($startdate2);
				if (!array_key_exists($endKey, $elements))
				{
					$endKey = $startKey;
					$enddate2 = $startdate2;
				}
				$endElement = $elements[$endKey];

				if (!array_key_exists($startKey, $elements))
				{
					JError::raiseError(500, $startdate2 . " not found in the list, is it published?");
				}
				$startElement = $elements[$startKey];
				$endParams = $endElement->getParams();
				$startParams = $startElement->getParams();

				$action = $app->isAdmin() ? "task" : "view";
				//$nextview = $listModel->canEdit() ? "form" : "details";

				foreach ($data as $group)
				{
					if (is_array($group))
					{
						foreach ($group as $row)
						{
							$event = new stdClass;
							$html = $w->parseMessageForPlaceHolder($template, JArrayHelper::fromObject($row));
							$event->description = $html;
							$event->start = array_key_exists($startdate . '_raw', $row) ? $row->{$startdate . '_raw'} : $row->$startdate;
							$event->end = $event->start;
							if (trim($enddate) !== '')
							{
								$end = array_key_exists($enddate . '_raw', $row) ? $row->{$enddate . '_raw'} : @$row->$enddate;
								$event->end = ($end >= $event->start) ? $end : '';

								$sDate = JFactory::getDate($event->end);
								$sDate->setTimezone($timeZone);
								$event->end = $sDate->toISO8601(true);
							}
							$sDate = JFactory::getDate($event->start);
							$sDate->setTimezone($timeZone);
							$event->start = $sDate->toISO8601(true);
							$bits = explode('+', $event->start);
							$event->start = $bits[0] . '+00:00';

							$event->title = strip_tags(@$row->$title);
							/* if ($app->isAdmin())
							{
								$url = 'index.php?option=com_fabrik&task=' . $nextview . '.view&formid=' . $table->form_id . '&rowid=' . $row->__pk_val;
							}
							else
							{
							$url = 'index.php?option=com_fabrik&view=' . $nextview . '&formid=' . $table->form_id . '&rowid=' . $row->__pk_val
								. '&listid=' . $listid;
							} */
							$url = $this->getLinkURL($listModel, $row, $c);
							$event->link = ($listModel->getOutPutFormat() == 'json') ? '#' : $url;//JRoute::_($url);
							$event->image = '';
							$event->color = $colour;
							$event->textColor = $textColour;
							$event->classname = isset($row->$className) ? $row->$className : '';
							$event->classname = strip_tags($event->classname);
							if ($event->start !== '' && !is_null($event->start))
							{
								if ($event->end == $event->start)
								{
									$event->end = '';
								}
								$eventdata[] = $event;
							}
						}
					}
				}
			}
			$c++;
		}
		$json = new StdClass;
		$json->dateTimeFormat = 'ISO8601';
		$json->events = $eventdata;
		$json->bands = $this->getBandInfo();
		$json = json_encode($json);
		$options = new stdClass;
		$options->dateFormat = $params->get('timeline_date_format', '%c');
		$options->orientation = $params->get('timeline_orientation', 'horizontal');
		$options = json_encode($options);
		$ref = $this->getJSRenderContext();
		$str = "var " . $ref . " = new FbVisTimeline($json, $options);";
		$str .= "\n" . "Fabrik.addBlock('" . $ref . "', " . $ref . ");";
		return $str;
		$srcs[] = 'plugins/fabrik_visualization/timeline/timeline.js';
		FabrikHelperHTML::script($srcs, $str);
	}

	/**
	 * Build the item link
	 *
	 * @param   object  $listModel list model
	 * @param   object  $row       current row
	 * @param   int     $c         which data set are we in (needed for getting correct params data)
	 *
	 *  @return  string  url
	 */

	protected function getLinkURL($listModel, $row, $c)
	{
		$w = new FabrikWorker;
		$app = JFactory::getApplication();
		$params = $this->getParams();
		$customLink = (array) $params->get('timeline_customlink');
		$customLink = JArrayHelper::getValue($customLink, $c, '');
		if ($customLink !== '')
		{
			$url = $w->parseMessageForPlaceHolder($customLink, JArrayHelper::fromObject($row));
			$url = str_replace('{rowid}', $row->__pk_val, $url);
		}
		else
		{
			$nextview = $listModel->canEdit() ? "form" : "details";
			$table = $listModel->getTable();
			if ($app->isAdmin())
			{
				$url = 'index.php?option=com_fabrik&task=' . $nextview . '.view&formid=' . $table->form_id . '&rowid=' . $row->__pk_val;
			}
			else
			{
				$url = 'index.php?option=com_fabrik&view=' . $nextview . '&formid=' . $table->form_id . '&rowid=' . $row->__pk_val
				. '&listid=' . $listid;
			}
		}
		return $url;
	}

	/**
	 * Get band info
	 *
	 * @return  array  band info
	 */

	protected function getBandInfo()
	{
		$params = $this->getParams();
		$bands = $params->get('timeline_bands');
		$bands = FabrikWorker::JSONtoData($bands, true);
		$intervals = JArrayHelper::getValue($bands, 'timelne_band_interval_unit', array());
		$widths = JArrayHelper::getValue($bands, 'timeline_band_width', array());
		$overviews = JArrayHelper::getValue($bands, 'timeline_band_as_overview', array());
		$bgs = JArrayHelper::getValue($bands, 'timeline_band_background_colour', array());
		$data = array();
		$length = count($intervals);
		$css = array();

		// When $i is 0 this is the top band
		for ($i = 0; $i < $length; $i++)
		{
			$o = new stdClass;
			$o->width = strstr($widths[$i], '%') ? $widths[$i] : $widths[$i] . '%';
			$o->intervalUnit = (int) $intervals[$i];
			$defaultOverview = $i === $length - 1 ? true : false;
			$o->overview = (bool) JArrayHelper::getValue($overviews, $i, $defaultOverview);
			$bg = JArrayHelper::getValue($bgs, $i, '');
			if ($bg !== '')
			{
				$css[] = '.timeline-band-' . $i . ' .timeline-ether-bg {
				background: ' . $bg . ' !important; }';
			}
			$data[] = $o;
		}

		$document = JFactory::getDocument();
		$css = implode("\n", $css);
		$document->addStyleDeclaration($css);
		return $data;
	}

	/**
	 * Set list ids
	 *
	 * @return  void
	 */

	public function setListIds()
	{
		if (!isset($this->listids))
		{
			$params = $this->getParams();
			$this->listids = (array) $params->get('timeline_table', array());
		}
	}
}
