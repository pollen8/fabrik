<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * makes the table navigation html to traverse the table data
 * @param   int the total number of records in the table
 * @param   int number of records to show per page
 * @param   int which record number to start at
 */

jimport('joomla.html.pagination');

/**
 * extension to the normal pagenav functions
 * $total, $limitstart, $limit
 */

class FPagination extends JPagination
{

	/** "var string action url */
	var $url = '';

	var $_id = '';

	/** @bool show the total number of records found **/
	var $showTotal = false;

	var $showAllOption = false;

	protected $listRef = null;

	public $showDisplayNum = true;

	function setId($id)
	{
		$this->_id = $id;
	}

	/**
	 * Return the pagination footer
	 *
	 * @param   string  $listRef  list reference
	 * @param   string  $tmpl     tmpl
	 *
	 * @since	1.0disabled
	 *
	 * @return  string  Pagination footer
	 */

	public function getListFooter($listRef = 0, $tmpl = 'default')
	{
		$app = JFactory::getApplication();
		$this->listRef = $listRef;
		$list = array();
		$list['limit'] = $this->limit;
		$list['limitstart'] = $this->limitstart;
		$list['total'] = $this->total;
		$list['limitfield'] = $this->showDisplayNum ? $this->getLimitBox() : '';
		$list['pagescounter'] = $this->getPagesCounter();
		if ($this->showTotal)
		{
			$list['pagescounter'] .= ' ' . JText::_('COM_FABRIK_TOTAL') . ': ' . $list['total'];
		}
		$list['pageslinks'] = $this->getPagesLinks($listRef, $tmpl);


			if (function_exists('fabrik_pagination_list_footer'))
			{
				 return fabrik_pagination_list_footer($list, $this);
			}
		return $this->_list_footer($list);
	}

	/**
	 * Creates a dropdown box for selecting how many records to show per page
	 *
	 * @return  string	The html for the limit # input box
	 *
	 * @since	1.0
	 */

	public function getLimitBox()
	{
		// Initialize variables
		$limits = array();

		$vals = array();
		for ($i = 5; $i <= 30; $i += 5)
		{
			$vals[] = $i;
		}
		$vals[] = 50;
		$vals[] = 100;

		if (!in_array($this->startLimit, $vals))
		{
			$vals[] = $this->startLimit;
		}
		asort($vals);
		foreach ($vals as $v)
		{
			$limits[] = JHTML::_('select.option', $v);
		}
		if ($this->showAllOption == true)
		{
			$limits[] = JHTML::_('select.option', '0', JText::_('COM_FABRIK_ALL'));
		}
		$selected = $this->_viewall ? 0 : $this->limit;
		$js = '';
		$html = JHTML::_('select.genericlist', $limits, 'limit' . $this->_id, 'class="inputbox" size="1" onchange="' . $js . '"', 'value', 'text',
			$selected);

		return $html;
	}

	function _item_active(&$item)
	{
		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			// return '<a title="' . $item->text . '" href="#" onclick="oTable.fabrikNav(' . $item->base . ');return false;">' . $item->text . '</a>';
			return '<a href="' . $item->base . '" title="' . $item->text . '">' . $item->text . '</a>';
		}
		else
		{
			return '<a title="' . $item->text . '" href="' . $item->link . '" class="pagenav">' . $item->text . '</a>';
		}
	}

	/**
	 * Create and return the pagination page list string, ie. Previous, Next, 1 2 3 ... x
	 *
	 * CANT ALLOW OVERRIDE IN TEMPLATES :s - AS THEY PRODUCE WRONG JS CODE/
	 * @access	public
	 * @return	string	Pagination page list string
	 * @since	1.0
	 */

	function getPagesLinks($listRef = 0, $tmpl = 'default')
	{
		$app = JFactory::getApplication();

		$lang = JFactory::getLanguage();

		// Build the page navigation list
		$data = $this->_buildDataObject();

		$list = array();

		$itemOverride = false;
		$listOverride = false;

		$chromePath = COM_FABRIK_FRONTEND . '/views/list/tmpl/' . $tmpl . '/default_pagination.php';
		if (JFile::exists($chromePath))
		{
			require_once $chromePath;
			if (function_exists('fabrik_pagination_item_active') && function_exists('fabrik_pagination_item_inactive'))
			{
				// Can't allow this as the js code we use for the items is different
				$itemOverride = true;
			}
			if (function_exists('fabrik_pagination_list_render'))
			{
				$listOverride = true;
			}
		}

		// Build the select list
		if ($data->all->base !== null)
		{
			$list['all']['active'] = true;
			$list['all']['data'] = $itemOverride ? fabrik_pagination_item_active($data->all, $this->listRef) : $this->_item_active($data->all);
		}
		else
		{
			$list['all']['active'] = false;
			$list['all']['data'] = $itemOverride ? fabrik_pagination_item_inactive($data->all) : $this->_item_inactive($data->all);
		}
		if ($data->start->base !== null)
		{
			$list['start']['data'] = $itemOverride ? fabrik_pagination_item_active($data->start, $this->listRef) : $this->_item_active($data->start);
		}
		else
		{
			$list['start']['data'] = $itemOverride ? fabrik_pagination_item_inactive($data->start) : $this->_item_inactive($data->start);
		}
		$list['start']['active'] = $this->limitstart == 0 ? true : false;
		if ($data->previous->base !== null)
		{
			$list['previous']['data'] = $itemOverride ? fabrik_pagination_item_active($data->previous, $this->listRef)
				: $this->_item_active($data->previous);
		}
		else
		{
			$list['previous']['data'] = $itemOverride ? fabrik_pagination_item_inactive($data->previous) : $this->_item_inactive($data->previous);
		}
		$list['previous']['active'] = $this->limitstart == 0 ? true : false;

		// Make sure it exists
		$list['pages'] = array();
		foreach ($data->pages as $i => $page)
		{
			if ($page->base !== null)
			{
				$list['pages'][$i]['active'] = false;
				$list['pages'][$i]['data'] = $itemOverride ? fabrik_pagination_item_active($page, $this->listRef) : $this->_item_active($page);
			}
			else
			{
				$list['pages'][$i]['active'] = true;
				$list['pages'][$i]['data'] = $itemOverride ? fabrik_pagination_item_inactive($page) : $this->_item_inactive($page);
			}
		}

		if ($data->next->base !== null)
		{
			$list['next']['data'] = $itemOverride ? fabrik_pagination_item_active($data->next, $this->listRef) : $this->_item_active($data->next);
		}
		else
		{
			$list['next']['data'] = $itemOverride ? fabrik_pagination_item_inactive($data->next) : $this->_item_inactive($data->next);
		}
		$list['next']['active'] = ($this->get('pages.current') === $this->get('pages.total')) ? true : false;
		if ($data->end->base !== null)
		{
			$list['end']['data'] = $itemOverride ? fabrik_pagination_item_active($data->end, $this->listRef) : $this->_item_active($data->end);
		}
		else
		{
			$list['end']['data'] = $itemOverride ? fabrik_pagination_item_inactive($data->end) : $this->_item_inactive($data->end);
		}
		$list['end']['active'] = ($this->get('pages.current') === $this->get('pages.total')) ? true : false;
		if ($this->total > $this->limit)
		{
			return ($listOverride) ? fabrik_pagination_list_render($list, $this->listRef) : $this->_list_render($list);
		}
		else
		{
			return '';
		}
	}

	/**
	 * Create the html for a list footer
	 *
	 * @param   array  $list  Pagination list data structure.
	 *
	 * @return  string  HTML for a list start, previous, next,end
	 */

	protected function _list_render($list)
	{
		// Reverse output rendering for right-to-left display.
		$html = '<ul class="pagination">';

		$this->bootstrap($list['start']);
		$html .= '<li class="pagination-start ' . $list['start']['class'] . '">' . $list['start']['data'] . '</li>';
		$this->bootstrap($list['previous']);
		$html .= '<li class="pagination-prev ' . $list['previous']['class'] . '">' . $list['previous']['data'] . '</li>';
		foreach ($list['pages'] as $page)
		{
			$this->bootstrap($page);
			$html .= '<li class="' . $page['class'] . '">' . $page['data'] . '</li>';
		}
		$this->bootstrap($list['next']);
		$html .= '<li class="pagination-next ' . $list['next']['class'] . '">' . $list['next']['data'] . '</li>';
		$this->bootstrap($list['end']);
		$html .= '<li class="pagination-end ' . $list['end']['class'] . '">' . $list['end']['data'] . '</li>';
		$html .= '</ul>';
		return $html;
	}

	/**
	 * Fudge stuff for bootstrap output
	 *
	 * @param unknown_type $item
	 */
	protected function bootstrap(&$item)
	{
		if (isset($item['active']) && $item['active'])
		{
			$item['class'] =  ' disabled';
		}
		else
		{
			$item['class'] = '';
		}
	}

	/**
	 * THIS SEEMS GOOFY TO HAVE TO OVERRIDE DEFAULT FUNCTION - BUT!
	 * THE ORIGINAL SETS THE PAGE TO EMPTY IF ITS 0 - APPARENTTLY TO DO WITH
	 * ROUTING - THIS HAS BEEN REMOVED HERE
	 *
	 * PERHAPS THE FABRIK ROUTING ISNT RIGHT?
	 *
	 * oCCURRS EVEN WITHOUT SEF URLS ON THOUGH? :s
	 *
	 * Create and return the pagination data object
	 *
	 * @access	public
	 * @return	object	Pagination data object
	 */

	function _buildDataObject()
	{
		$app = JFactory::getApplication();
		$admin = $app->isAdmin();

		// Initialize variables
		$data = new stdClass;
		$this->url = preg_replace("/limitstart{$this->_id}=(.*)?(&|)/", "", $this->url);
		$this->url = FabrikString::rtrimword($this->url, "&");

		// $$$ hugh - need to work out if we need & or ?
		$sepchar = strstr($this->url, '?') ? '&amp;' : '?';

		// $sepchar = '&';
		$data->all = new JPaginationObject(JText::_('COM_FABRIK_VIEW_ALL'));
		if (!$this->_viewall)
		{
			$data->all->base = '0';
			$data->all->link = $admin ? "{$sepchar}limitstart=" : JRoute::_("{$sepchar}limitstart=");
		}

		// Set the start and previous data objects
		$data->start = new JPaginationObject(JText::_('COM_FABRIK_START'));
		$data->previous = new JPaginationObject(JText::_('COM_FABRIK_PREV'));

		if ($this->get('pages.current') > 1)
		{
			$page = ($this->get('pages.current') - 2) * $this->limit;

			// $page = $page == 0 ? '' : $page; //set the empty for removal from route
			$data->start->base = '0';
			$data->start->link = $admin ? "{$sepchar}limitstart{$this->_id}=0" : JRoute::_($this->url . "{$sepchar}limitstart{$this->_id}=0");

			$data->previous->base = $page;
			$data->previous->link = $admin ? "{$sepchar}limitstart{$this->_id}=" . $page
				: JRoute::_($this->url . "{$sepchar}limitstart{$this->_id}=" . $page);

			$data->start->link = str_replace('resetfilters=1', '', $data->start->link);
			$data->previous->link = str_replace('resetfilters=1', '', $data->previous->link);
			$data->start->link = str_replace('clearordering=1', '', $data->start->link);
			$data->previous->link = str_replace('clearordering=1', '', $data->previous->link);
		}

		// Set the next and end data objects
		$data->next = new JPaginationObject(JText::_('COM_FABRIK_NEXT'));
		$data->end = new JPaginationObject(JText::_('COM_FABRIK_END'));

		if ($this->get('pages.current') < $this->get('pages.total'))
		{
			$next = $this->get('pages.current') * $this->limit;
			$end = ($this->get('pages.total') - 1) * $this->limit;

			$data->next->base = $next;
			$data->next->link = $admin ? "{$sepchar}limitstart{$this->_id}=" . $next : JRoute::_($this->url . "{$sepchar}limitstart{$this->_id}="
				. $next);
			$data->end->base = $end;
			$data->end->link = $admin ? "{$sepchar}limitstart{$this->_id}=" . $end : JRoute::_($this->url . "{$sepchar}limitstart{$this->_id}="
				. $end);

			$data->next->link = str_replace('resetfilters=1', '', $data->next->link);
			$data->end->link = str_replace('resetfilters=1', '', $data->end->link);
			$data->next->link = str_replace('clearordering=1', '', $data->next->link);
			$data->end->link = str_replace('clearordering=1', '', $data->end->link);
		}

		$data->pages = array();
		$stop = $this->get('pages.stop');
		for ($i = $this->get('pages.start'); $i <= $stop; $i++)
		{
			$offset = ($i - 1) * $this->limit;

			// $offset = $offset == 0 ? '' : $offset;  //set the empty for removal from route

			$data->pages[$i] = new JPaginationObject($i);
			if ($i != $this->get('pages.current') || $this->_viewall)
			{
				$data->pages[$i]->base = $offset;
				$data->pages[$i]->link = $admin ? "{$sepchar}limitstart{$this->_id}=" . $offset
					: JRoute::_($this->url . "{$sepchar}limitstart{$this->_id}=" . $offset);
				$data->pages[$i]->link = str_replace('resetfilters=1', '', $data->pages[$i]->link);
				$data->pages[$i]->link = str_replace('clearordering=1', '', $data->pages[$i]->link);
			}
		}
		return $data;
	}

	function _list_footer($list)
	{
		// Initialize variables
		$html = array();
		$html[] = '<div class="list-footer">';
		$limitLabel = $this->showDisplayNum ? JText::_('COM_FABRIK_DISPLAY_NUM') : '';
		$html[] = '<div class="limit">' . $limitLabel . $list['limitfield'] . '</div>';
		$html[] = $list['pageslinks'];
		$html[] = '<div class="counter">' . $list['pagescounter'] . '</div>';
		$html[] = '<input type="hidden" name="limitstart' . $this->_id . '" id="limitstart' . $this->_id . '" value="' . $list['limitstart'] . '" />';
		$html[] = '</div>';
		return implode("\n", $html);
	}

}
