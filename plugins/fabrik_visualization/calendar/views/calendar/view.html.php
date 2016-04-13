<?php
/**
 * Fabrik Calendar HTML View
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.calendar
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Text;
use Fabrik\Helpers\Worker;

jimport('joomla.application.component.view');

/**
 * Fabrik Calendar HTML View
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.calendar
 * @since       3.0
 */

class FabrikViewCalendar extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */

	public function display($tpl = 'default')
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$input = $app->input;
		$j3 = Worker::j3();
		$Itemid = Worker::itemId();
		$model = $this->getModel();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$id = $input->get('id', $usersConfig->get('visualizationid', $input->get('visualizationid', 0)));
		$model->setId($id);
		$this->row = $model->getVisualization();

		if (!$model->canView())
		{
			echo Text::_('JERROR_ALERTNOAUTHOR');

			return false;
		}

		$params = $model->getParams();
		$this->params = $params;
		$this->containerId = $model->getJSRenderContext();
		$this->filters = $this->get('Filters');
		$this->showFilters = $model->showFilters();
		$this->showTitle = $input->getInt('show-title', 1);
		$this->filterFormURL = $this->get('FilterFormURL');
		$calendar = $this->row;

		JHTML::stylesheet('media/com_fabrik/css/list.css');
		$this->canAdd = (bool) $params->get('calendar-read-only', 0) == 1 ? false : $model->getCanAdd();
		$this->requiredFiltersFound = $this->get('RequiredFiltersFound');

		if ($params->get('calendar_show_messages', '1') == '1' && $this->canAdd && $this->requiredFiltersFound)
		{
			$msg = Text::_('PLG_VISUALIZATION_CALENDAR_DOUBLE_CLICK_TO_ADD');
			$msg .= $model->getDateLimitsMsg();
			$app->enqueueMessage($msg);
		}

		JHTML::stylesheet('media/com_fabrik/css/list.css');

		// Get all list where statements - which are then included in the ajax call to ensure we get the correct data set loaded
		$urlfilters = new stdClass;
		$urlfilters->where = $model->buildQueryWhere();

		$urls = new stdClass;

		// Don't JRoute as its wont load with sef?
		$urls->del = 'index.php?option=com_' . $package
		. '&controller=visualization.calendar&view=visualization&task=deleteEvent&format=raw&Itemid=' . $Itemid . '&id=' . $id;
		$urls->add = 'index.php?option=com_' . $package . '&view=visualization&format=raw&Itemid=' . $Itemid . '&id=' . $id;
		$user = JFactory::getUser();
		$legend = $params->get('show_calendar_legend', 0) ? $model->getLegend() : '';
		$tpl = $j3 ? 'bootstrap' : 'default';
		$tpl = $params->get('calendar_layout', $j3);
		$options = new stdClass;
		$options->url = $urls;
		$options->dateLimits = $model->getDateLimits();

		$options->deleteables = $model->getDeleteAccess();
		$options->eventLists = $model->getEventLists();
		$options->calendarId = $calendar->id;
		$options->popwiny = $params->get('yoffset', 0);
		$options->urlfilters = $urlfilters;
		$options->canAdd = $this->canAdd;
		$options->showFullDetails = (bool) $params->get('show_full_details', false);

		$options->restFilterStart = Worker::getMenuOrRequestVar('resetfilters', 0, false, 'request');
		$options->tmpl = $tpl;

		$o = $model->getAddStandardEventFormInfo();

		if ($o != null)
		{
			$options->listid = $o->id;
		}

		// $$$rob @TODO not sure this is need - it isn't in the timeline viz
		$model->setRequestFilters();
		$options->filters = $model->filters;

		// End not sure
		$options->Itemid = $Itemid;
		$options->show_day = (bool) $params->get('show_day', true);
		$options->show_week = (bool) $params->get('show_week', true);
		$options->days = array(Text::_('SUNDAY'), Text::_('MONDAY'), Text::_('TUESDAY'), Text::_('WEDNESDAY'), Text::_('THURSDAY'),
			Text::_('FRIDAY'), Text::_('SATURDAY'));
		$options->shortDays = array(Text::_('SUN'), Text::_('MON'), Text::_('TUE'), Text::_('WED'), Text::_('THU'), Text::_('FRI'),
			Text::_('SAT'));
		$options->months = array(Text::_('JANUARY'), Text::_('FEBRUARY'), Text::_('MARCH'), Text::_('APRIL'), Text::_('MAY'), Text::_('JUNE'),
			Text::_('JULY'), Text::_('AUGUST'), Text::_('SEPTEMBER'), Text::_('OCTOBER'), Text::_('NOVEMBER'), Text::_('DECEMBER'));
		$options->shortMonths = array(Text::_('JANUARY_SHORT'), Text::_('FEBRUARY_SHORT'), Text::_('MARCH_SHORT'), Text::_('APRIL_SHORT'),
			Text::_('MAY_SHORT'), Text::_('JUNE_SHORT'), Text::_('JULY_SHORT'), Text::_('AUGUST_SHORT'), Text::_('SEPTEMBER_SHORT'),
			Text::_('OCTOBER_SHORT'), Text::_('NOVEMBER_SHORT'), Text::_('DECEMBER_SHORT'));
		$options->first_week_day = (int) $params->get('first_week_day', 0);

		$options->monthday = new stdClass;
		$options->monthday->width = (int) $params->get('calendar-monthday-width', 90);
		$options->monthday->height = (int) $params->get('calendar-monthday-height', 80);
		$options->greyscaledweekend = $params->get('greyscaled-week-end', 0) === '1';
		$options->viewType = $params->get('calendar_default_view', 'monthView');

		$options->weekday = new stdClass;
		$options->weekday->width = (int) $params->get('calendar-weekday-width', 90);
		$options->weekday->height = (int) $params->get('calendar-weekday-height', 10);
		$options->open = (int) $params->get('open-hour', 0);
		$options->close = (int) $params->get('close-hour', 24);
		$options->showweekends = (bool) $params->get('calendar-show-weekends', true);
		$options->readonly = (bool) $params->get('calendar-read-only', false);
		$options->timeFormat = $params->get('time_format', '%X');
		$options->readonlyMonth = (bool) $params->get('readonly_monthview', false);
		$options->j3 = Worker::j3();

		if (Worker::j3())
		{
			$options->buttons = new stdClass;
			$options->buttons->del = '<button class="btn popupDelete" data-task="deleteCalEvent">' . Html::icon('icon-delete') . '</button>';
			$options->buttons->edit = '<button class="btn popupEdit" data-task="editCalEvent">' . Html::icon('icon-edit') . '</button>';
			$options->buttons->view = '<button class="btn popupView" data-task="viewCalEvent">' . Html::icon('icon-eye') . '</button>';
		}
		else
		{
			$src = COM_FABRIK_LIVESITE . 'plugins/fabrik_visualization/calendar/views/calendar/tmpl/' . $tpl . '/images/minus-sign.png';
			$options->buttons = '<img src="' . $src . '"
				alt = "del" class="fabrikDeleteEvent" />' . Text::_('PLG_VISUALIZATION_CALENDAR_DELETE');
		}

		$json = json_encode($options);

		Text::script('PLG_VISUALIZATION_CALENDAR_NEXT');
		Text::script('PLG_VISUALIZATION_CALENDAR_PREVIOUS');
		Text::script('PLG_VISUALIZATION_CALENDAR_DAY');
		Text::script('PLG_VISUALIZATION_CALENDAR_WEEK');
		Text::script('PLG_VISUALIZATION_CALENDAR_MONTH');
		Text::script('PLG_VISUALIZATION_CALENDAR_KEY');
		Text::script('PLG_VISUALIZATION_CALENDAR_TODAY');
		Text::script('PLG_VISUALIZATION_CALENDAR_CONF_DELETE');
		Text::script('PLG_VISUALIZATION_CALENDAR_DELETE');
		Text::script('PLG_VISUALIZATION_CALENDAR_VIEW');
		Text::script('PLG_VISUALIZATION_CALENDAR_EDIT');
		Text::script('PLG_VISUALIZATION_CALENDAR_ADD_EDIT_EVENT');
		Text::script('COM_FABRIK_FORM_SAVED');
		Text::script('PLG_VISUALIZATION_CALENDAR_EVENT_START_END');
		Text::script('PLG_VISUALIZATION_CALENDAR_DATE_ADD_TOO_LATE');
		Text::script('PLG_VISUALIZATION_CALENDAR_DATE_ADD_TOO_EARLY');

		$ref = $model->getJSRenderContext();

		$js = array();
		$js[] = "\tvar $ref = new fabrikCalendar('$ref');";
		$js[] = "\t$ref.render($json);";
		$js[] = "\tFabrik.addBlock('" . $ref . "', $ref);";
		$js[] = "\t" . $legend . "";
		$js[] = "" . $model->getFilterJs();
		$js = implode("\n", $js);

		$srcs = Html::framework();
		$srcs['FbListFilter'] = 'media/com_fabrik/js/listfilter.js';
		$srcs['Calendar'] = 'plugins/fabrik_visualization/calendar/calendar.js';

		Html::iniRequireJs($model->getShim());
		Html::script($srcs, $js);

		$viewName = $this->getName();
		$this->params = $model->getParams();
		$tpl = $params->get('calendar_layout', $tpl);
		$tmplpath = JPATH_ROOT . '/plugins/fabrik_visualization/calendar/views/calendar/tmpl/' . $tpl;
		$this->_setPath('template', $tmplpath);
		Html::stylesheetFromPath('plugins/fabrik_visualization/calendar/views/calendar/tmpl/' . $tpl . '/template.css');

		// Adding custom.css, just for the heck of it
		Html::stylesheetFromPath('plugins/fabrik_visualization/calendar/views/calendar/tmpl/' . $tpl . '/custom.css');

		return parent::display();
	}

	/**
	 * Choose which list to add an event to
	 *
	 * @return  void
	 */

	public function chooseaddevent()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$this->setLayout('chooseaddevent');
		$model = $this->getModel();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model->setId($input->getInt('id', $usersConfig->get('visualizationid', $input->getInt('visualizationid', 0))));
		$rows = $model->getEventLists();
		$o = $model->getAddStandardEventFormInfo();
		$calendar = $model->getVisualization();
		$options = array();
		$options[] = JHTML::_('select.option', '', Text::_('PLG_VISUALIZATION_CALENDAR_PLEASE_SELECT'));

		if ($o != null)
		{
			$listid = $o->id;
			$options[] = JHTML::_('select.option', $listid, Text::_('PLG_VISUALIZATION_CALENDAR_STANDARD_EVENT'));
		}

		$model->getEvents();
		$config = JFactory::getConfig();
		$prefix = $config->get('dbprefix');
		$attribs = 'class="inputbox" size="1" ';
		$options = array_merge($options, $rows);
		$this->_eventTypeDd = JHTML::_('select.genericlist', $options, 'event_type', $attribs, 'value', 'text', '', 'fabrik_event_type');

		/*
		 * Tried loading in iframe and as an ajax request directly - however
		 * in the end decided to set a call back to the main calendar object (via the package manager)
		 * to load up the new add event form
		 */
		$ref = $model->getJSRenderContext();
		$script = array();
		//$script[] = "window.addEvent('fabrik.loaded', function() {";
		$script[] = "document.id('fabrik_event_type').addEvent('change', function(e) {";
		$script[] = "var fid = e.target.get('value');";
		$script[] = "var o = ({'d':'','listid':fid,'rowid':0});";
		$script[] = "o.datefield = '{$prefix}fabrik_calendar_events___start_date';";
		$script[] = "o.datefield2 = '{$prefix}fabrik_calendar_events___end_date';";
		$script[] = "o.labelfield = '{$prefix}fabrik_calendar_events___label';";

		foreach ($model->events as $tid => $arr)
		{
			foreach ($arr as $ar)
			{
				$script[] = "if(" . $ar['formid'] . " == fid)	{";
				$script[] = "o.datefield = '" . $ar['startdate'] . "'";
				$script[] = "o.datefield2 = '" . $ar['enddate'] . "'";
				$script[] = "o.labelfield = '" . $ar['label'] . "'";
				$script[] = "}\n";
			}
		}

		$script[] = "Fabrik.blocks['" . $ref . "'].addEvForm(o);";
		$script[] = "Fabrik.Windows.chooseeventwin.close();";
		$script[] = "});";
		//$script[] = "});";

		echo '<h2>' . Text::_('PLG_VISUALIZATION_CALENDAR_PLEASE_CHOOSE_AN_EVENT_TYPE') . ':</h2>';
		echo $this->_eventTypeDd;
		Html::addScriptDeclaration(implode("\n", $script));
	}
}
