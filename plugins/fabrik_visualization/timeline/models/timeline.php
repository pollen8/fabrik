<?php

/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/visualization.php';

/**
 * Renders timeline visualization
 * 
 * @package  Fabrik
 * @since    3.0
 *
 */
class fabrikModelTimeline extends FabrikFEModelVisualization
{

	/**
	 * internally render the plugin, and add required script declarations
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

		// $$$ rob - dates are already formatted with timezone offset i think
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

				/**
				 *  timeline always shows dates in GMT so lets take a guess if the table view should show the times or not
				 *  if they don't its a bit of a hack as the timeline will still say its GMT but at least its GMT for a time of 00:00:00
				 */
				$tblEndFormat = $endParams->get('date_table_format');
				$endTimeFormat = (strstr($tblEndFormat, '%H') || strstr($tblEndFormat, '%M')) ? true : false;
				$tblStartFormat = $startParams->get('date_table_format');
				$startTimeFormat = (strstr($tblStartFormat, '%H') || strstr($tblStartFormat, '%M')) ? true : false;

				$action = $app->isAdmin() ? "task" : "view";
				$nextview = $listModel->canEdit() ? "form" : "details";

				foreach ($data as $group)
				{
					if (is_array($group))
					{
						foreach ($group as $row)
						{
							$event = new stdClass;
							$html = $w->parseMessageForPlaceHolder($template, JArrayHelper::fromObject($row));
							$event->description = $html;
							$event->start = (array_key_exists($startdate . '_raw', $row) && $startTimeFormat) ? $row->{$startdate . '_raw'}
								: $row->$startdate;
							$event->end = $event->start;
							if (trim($enddate) !== '')
							{
								$end = (array_key_exists($enddate . '_raw', $row) && $endTimeFormat) ? $row->{$enddate . '_raw'} : @$row->$enddate;
								$event->end = ($end >= $event->start) ? $end : '';

								// If we are showing the times we need to re-offset the date as its already been offset in the tbl model
								$endD = !(array_key_exists($enddate . '_raw', $row) && $endTimeFormat) ? JFactory::getDate($event->end, $timeZone)
									: JFactory::getDate($event->end);
								$event->end = $endD->toISO8601();
							}

							$sDate = !(array_key_exists($startdate . '_raw', $row) && $startTimeFormat) ? JFactory::getDate($event->start, $timeZone)
								: JFactory::getDate($event->start);
							$event->start = $sDate->toISO8601();
							$event->title = strip_tags(@$row->$title);
							$url = 'index.php?option=com_fabrik&view=' . $nextview . '&formid=' . $table->form_id . '&rowid=' . $row->__pk_val
								. '&listid=' . $listid;
							$event->link = ($listModel->getOutPutFormat() == 'json') ? '#' : JRoute::_($url);
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
		$str = "var timeline = new FbVisTimeline($json, $options);";
		return $str;
		$srcs[] = 'plugins/fabrik_visualization/timeline/timeline.js';
		FabrikHelperHTML::script($srcs, $str);
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
