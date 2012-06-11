<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * makes the table navigation html to traverse the table data
 * @param int the total number of records in the table
 * @param int number of records to show per page
 * @param int which record number to start at
 */

jimport('joomla.html.pagination');

/**
 * extension to the normal pagenav functions
 * $total, $limitstart, $limit
 */

class FPagination extends JPagination{

	/** @var string action url */
	var $url = '';

	/** @var int list id */
	protected $id = '';

	/** @bool show the total number of records found **/
	var $showTotal = false;

	var $showAllOption = false;
	
	protected $listRef = null;
	
	public $showDisplayNum = true;

	function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Return the pagination footer
	 *
	 * @access	public
	 * @param	string	list reference
	 * @param	string	tmpl
	 * @return	string	Pagination footer
	 * @since	1.0
	 */
	
	function getListFooter($listRef = 0, $tmpl = 'default')
	{
		$app = JFactory::getApplication();
		$this->listRef = $listRef;
		$list = array();
		$list['limit'] = $this->limit;
		$list['limitstart']	= $this->limitstart;
		$list['total'] = $this->total;
		$list['limitfield']	= $this->showDisplayNum ? $this->getLimitBox() : '';
		$list['pagescounter'] = $this->getPagesCounter();
		if ($this->showTotal)
		{
			$list['pagescounter'] .= ' ' . JText::_('COM_FABRIK_TOTAL') . ': '. $list['total'];
		}
		$list['pageslinks']	= $this->getPagesLinks($listRef, $tmpl);

		$chromePath	= JPATH_THEMES . '/' . $app->getTemplate() . '/html/pagination.php';

		if (file_exists($chromePath))
		{
			require_once($chromePath);
			if (function_exists('pagination_list_footer'))
			{
				//cant allow for it to be overridden
				//return pagination_list_footer($list);
			}
		}
		return $this->_list_footer($list);
	}

	/**
	 * Creates a dropdown box for selecting how many records to show per page
	 *
	 * @access	public
	 * @return	string	The html for the limit # input box
	 * @since	1.0
	 */
	
	function getLimitBox()
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
		$html = JHTML::_('select.genericlist', $limits, 'limit' . $this->id, 'class="inputbox" size="1" onchange="' . $js . '"', 'value', 'text', $selected);
		return $html;
	}

	function _item_active(&$item)
	{
		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			//return '<a title="' . $item->text . '" href="#" onclick="oTable.fabrikNav(' . $item->base . ');return false;">' . $item->text . '</a>';
			return '<a href="' . $item->base . '" title="' .$item->text . '">' . $item->text . '</a>';
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
			require_once($chromePath);
			if (function_exists('fabrik_pagination_item_active') && function_exists('fabrik_pagination_item_inactive'))
			{
				//cant allow this as the js code we use for the items is different
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
			$list['start']['active'] = true;
			$list['start']['data'] = $itemOverride ? fabrik_pagination_item_active($data->start, $this->listRef) : $this->_item_active($data->start);
		}
		else
		{
			$list['start']['active'] = false;
			$list['start']['data'] = $itemOverride ? fabrik_pagination_item_inactive($data->start) : $this->_item_inactive($data->start);
		}
		if ($data->previous->base !== null)
		{
			$list['previous']['active'] = true;
			$list['previous']['data'] = $itemOverride ? fabrik_pagination_item_active($data->previous, $this->listRef) : $this->_item_active($data->previous);
		}
		else
		{
			$list['previous']['active'] = false;
			$list['previous']['data'] = $itemOverride ? fabrik_pagination_item_inactive($data->previous) : $this->_item_inactive($data->previous);
		}

		$list['pages'] = array(); //make sure it exists
		foreach ($data->pages as $i => $page)
		{
			if ($page->base !== null)
			{
				$list['pages'][$i]['active'] = true;
				$list['pages'][$i]['data'] = $itemOverride ? fabrik_pagination_item_active($page, $this->listRef) : $this->_item_active($page);
			}
			else
			{
				$list['pages'][$i]['active'] = false;
				$list['pages'][$i]['data'] = $itemOverride ? fabrik_pagination_item_inactive($page) : $this->_item_inactive($page);
			}
		}

		if ($data->next->base !== null)
		{
			$list['next']['active'] = true;
			$list['next']['data'] = $itemOverride ? fabrik_pagination_item_active($data->next, $this->listRef) : $this->_item_active($data->next);
		}
		else
		{
			$list['next']['active'] = false;
			$list['next']['data'] = $itemOverride ? fabrik_pagination_item_inactive($data->next) : $this->_item_inactive($data->next);
		}
		if ($data->end->base !== null)
		{
			$list['end']['active'] = true;
			$list['end']['data'] = $itemOverride ? fabrik_pagination_item_active($data->end, $this->listRef) : $this->_item_active($data->end);
		}
		else
		{
			$list['end']['active'] = false;
			$list['end']['data'] = $itemOverride ? fabrik_pagination_item_inactive($data->end) : $this->_item_inactive($data->end);
		}

		if ($this->total > $this->limit)
		{
			return ($listOverride) ? fabrik_pagination_list_render($list, $this->listRef) : $this->_list_render($list);
		}
		else
		{
			return '';
		}
	}

	protected function _list_render($list)
	{
		// Reverse output rendering for right-to-left display.
		$html = '<ul class="pagination">';
		$html .= '<li class="pagination-start">' . $list['start']['data'] . '</li>';
		$html .= '<li class="pagination-prev">' . $list['previous']['data'] . '</li>';
		foreach ($list['pages'] as $page)
		{
			$html .= '<li>' . $page['data'] . '</li>';
		}
		$html .= '<li class="pagination-next">' . $list['next']['data'] . '</li>';
		$html .= '<li class="pagination-end">' . $list['end']['data'] . '</li>';
		$html .= '</ul>';
		return $html;
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
	 * @since	1.5
	 */

	function _buildDataObject()
	{
		$app = JFactory::getApplication();
		$admin = $app->isAdmin();
 		// Initialize variables
		$data = new stdClass();
		$this->url = preg_replace("/limitstart{$this->id}=(.*)?(&|)/", "", $this->url);
		$this->url = FabrikString::rtrimword($this->url, "&");
		// $$$ hugh - need to work out if we need & or ?
		$sepchar = strstr($this->url,'?') ? '&amp;' : '?';
		//$sepchar = '&';
		$data->all = new JPaginationObject(JText::_('COM_FABRIK_VIEW_ALL'));
		if (!$this->_viewall)
		{
			$data->all->base = '0';
			$data->all->link = $admin ? "{$sepchar}limitstart=" : JRoute::_("{$sepchar}limitstart=");
		}

		// Set the start and previous data objects
		$data->start = new JPaginationObject(JText::_('COM_FABRIK_START'));
		$data->previous	= new JPaginationObject(JText::_('COM_FABRIK_PREV'));

		if ($this->get('pages.current') > 1)
		{
			$page = ($this->get('pages.current') -2) * $this->limit;

			//$page = $page == 0 ? '' : $page; //set the empty for removal from route
			$data->start->base	= '0';
			$data->start->link	= $admin ? "{$sepchar}limitstart{$this->id}=0" : JRoute::_($this->url."{$sepchar}limitstart{$this->id}=0");

			$data->previous->base	= $page;
			$data->previous->link	= $admin ? "{$sepchar}limitstart{$this->id}=".$page : JRoute::_($this->url."{$sepchar}limitstart{$this->id}=".$page);

			$data->start->link = str_replace('resetfilters=1', '', $data->start->link);
			$data->previous->link = str_replace('resetfilters=1', '', $data->previous->link);
			$data->start->link = str_replace('clearordering=1', '', $data->start->link);
			$data->previous->link = str_replace('clearordering=1', '', $data->previous->link);
		}

		// Set the next and end data objects
		$data->next	= new JPaginationObject(JText::_('COM_FABRIK_NEXT'));
		$data->end	= new JPaginationObject(JText::_('COM_FABRIK_END'));

		if ($this->get('pages.current') < $this->get('pages.total'))
		{
			$next = $this->get('pages.current') * $this->limit;
			$end  = ($this->get('pages.total') -1) * $this->limit;

			$data->next->base	= $next;
			$data->next->link	= $admin ? "{$sepchar}limitstart{$this->id}=".$next : JRoute::_($this->url."{$sepchar}limitstart{$this->id}=".$next);
			$data->end->base	= $end;
			$data->end->link	= $admin ? "{$sepchar}limitstart{$this->id}=".$end : JRoute::_($this->url."{$sepchar}limitstart{$this->id}=".$end);

			$data->next->link = str_replace('resetfilters=1', '', $data->next->link);
			$data->end->link = str_replace('resetfilters=1', '', $data->end->link);
			$data->next->link = str_replace('clearordering=1', '', $data->next->link);
			$data->end->link = str_replace('clearordering=1', '', $data->end->link);
		}

		$data->pages = array();
		$stop = $this->get('pages.stop');
		for ($i = $this->get('pages.start'); $i <= $stop; $i ++)
		{
			$offset = ($i -1) * $this->limit;

			//$offset = $offset == 0 ? '' : $offset;  //set the empty for removal from route

			$data->pages[$i] = new JPaginationObject($i);
			if ($i != $this->get('pages.current') || $this->_viewall)
			{
				$data->pages[$i]->base	= $offset;
				$data->pages[$i]->link	= $admin ? "{$sepchar}limitstart{$this->id}=".$offset : JRoute::_($this->url."{$sepchar}limitstart{$this->id}=".$offset);
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
		$html[] = '<input type="hidden" name="limitstart' . $this->id . '" id="limitstart' . $this->id . '" value="' . $list['limitstart'] . '" />';
		$html[] = '</div>';
		return implode("\n", $html);
	}

}
?>