<?php
/**
 * @package		Joomla
 * @subpackage	Fabik
 * @copyright	Copyright (C) 2005 - 2008 Pollen 8 Design Ltd. All rights reserved.
 * @license		GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class plgSearchFabrik extends JPlugin
{

	/**
	 * @return array An array of search areas
	 */
	function onContentSearchAreas()
	{
		// load plugin params info
		$section = $this->params->get('search_section_heading');
		$areas = array(
			'fabrik' => $section
		);
		return $areas;
	}

	/**
	 * Fabrik Search method
	 *
	 * The sql must return the following fields that are
	 * used in a common display routine: href, title, section, created, text,
	 * browsernav
	 * @param string Target search string
	 * @param string mathcing option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 * @param mixed An array if restricted to areas, null if search all
	 */

	function onContentSearch($text, $phrase='', $ordering='', $areas=null)
	{
		return plgSystemFabrik::onContentSearch($text, $phrase, $ordering, $areas);
		global $_PROFILER;
		JDEBUG ? $_PROFILER->mark('fabrik search start') : null;

		$user	=& JFactory::getUser();
		$defines = JFile::exists(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'user_defines.php') ? JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'user_defines.php' : JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'defines.php';
		require_once($defines);

		require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'parent.php');
		require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'string.php');
		$db		= FabrikWorker::getDbo();
		JDEBUG ? $_PROFILER->mark('fabrik search start') : null;
		$user	=& JFactory::getUser();

		require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

		if (is_array($areas)) {
			if (!array_intersect($areas, array_keys(plgSearchFabrikAreas()))) {
				return array();
			}
		}

		// load plugin params info
		$limit = $this->params->def('search_limit', 50);
		$text = trim($text);
		if ($text == '') {
			return array();
		}

		switch ($ordering) {
			case 'oldest':
				$order = 'a.created ASC';
				break;

			case 'popular':
				$order = 'a.hits DESC';
				break;

			case 'alpha':
				$order = 'a.title ASC';
				break;

			case 'category':
				$order = 'b.title ASC, a.title ASC';
				$morder = 'a.title ASC';
				break;

			case 'newest':
			default:
				$order = 'a.created DESC';
				break;
		}
		$this->generalIncludes('list');

		//get all tables with search on
		$query = $db->getQuery(true);
		//$query->select('id')->from("#__{package}_lists");
		$query->select('id')->from('#__{package}_lists')->where('published = 1');

		//$sql = "SELECT id FROM #__{package}_lists WHERE params LIKE '%search_use\":\"1%'";
		$db->setQuery($query);

		$list = array();
		$ids = $db->loadResultArray();

		//set the request variable that fabrik uses to search all records
		JRequest::setVar('fabrik_list_filter_all', JRequest::getVar('searchword'), 'post');

		$section = $this->params->get('search_section_heading');
		$urls = array();
		//$$$ rob remove previous search results?
		JRequest::setVar('resetfilters', 1);

		//ensure search doesnt go over memory limits
		$memory = (int)FabrikString::rtrimword(ini_get('memory_limit'), 'M') * 1000000;
		$usage = array();
		$memSafety = 0;

		$listModel =& JModel::getInstance('list', 'FabrikFEModel');

		$app =& JFactory::getApplication();
		foreach ($ids as $id) {
			//	unset enough stuff in the table model to allow for correct query to be run
			$listModel->reset();

			/// $$$ geros - http://fabrikar.com/forums/showthread.php?t=21134&page=2
			$key = 'com_fabrik.table'.$id.'.filter.searchall';
			$app->setUserState($key, null);

			unset($table);
			unset($elementModel);
			unset($params);
			unset($query);
			unset($allrows);
			$used = memory_get_usage();
			$usage[] = memory_get_usage();
			if (count($usage) > 2) {
				$diff = $usage[count($usage)-1] - $usage[count($usage)-2];
				if ($diff + $usage[count($usage)-1] > $memory - $memSafety) {
					JError::raiseNotice(500, 'Some records were not searched due to memory limitations');
					break;
				}
			}
			// $$$rob set this to current table
			//otherwise the fabrik_list_filter_all var is not used
			JRequest::setVar('listid', $id);


			$listModel->setId($id);
			$table 			=& $listModel->getTable(true);
			$fabrikDb 	= $listModel->getDb();
			$params 		=& $listModel->getParams();

			//test for swap too boolean mode
			$mode = JRequest::getVar('searchphraseall', 'all');
			//$params->set('search-mode-advanced', true);
			$params->set('search-mode-advanced', $mode);

			//the table shouldn't be included in the search results
			//or we have reached the max number of records to show.
			if (!$params->get('search_use') || $limit <= 0) {
				continue;
			}

			//set the table search mode to OR - this will search ALL fields with the search term
			$params->set('search-mode', 'OR');

			//build the query and get the records
			$query = $listModel->_buildQuery();
			$fabrikDb->setQuery($query, 0, $limit);
			$allrows = $fabrikDb->loadObjectList();
			if (is_null($allrows)) {
				JError::raiseError(500, $fabrikDb->getErrorMsg());
			}

			// $$$ rob -moved inside loop as dup records from joined data aren't all added to search results
			//$limit = $limit - count( $allrows );

			$elementModel = $listModel->getFormModel()->getElement($params->get('search_description', $table->label), true);
			$descname = is_object($elementModel) ? $elementModel->getFullName(false, true) : '';


			$elementModel =& $listModel->getFormModel()->getElement($params->get('search_title', 0), true);
			$title = is_object($elementModel) ? $elementModel->getFullName(false, true) : '';

			$aAllowedList = array();
			$pk = $table->db_primary_key;
			foreach ($allrows as $oData) {
				$pkval = $oData->__pk_val;
				$href = "index.php?option=com_fabrik&view=details&formid=".$table->form_id."&rowid=".$pkval ;
				if (!in_array($href, $urls)) {
					$limit --;
					$urls[] = $href;
		 		$o = new stdClass();
		 		if (isset($oData->$title)) {
		 			$o->title = $table->label .' : '. $oData->$title;
		 		} else {
		 			$o->title = $table->label;
		 		}
		 		$o->_pkey = $table->db_primary_key;
		 		$o->section = $section;

		 		$o->href = $href;
		 		$o->created = '';
		 		$o->browsernav = 2;
		 		if (isset($oData->$descname)) {
		 			$o->text = $oData->$descname;
		 		} else {
		 			$o->text = '';
		 		}
		 		$aAllowedList[] = $o;
				}
			}
			$list[] = $aAllowedList;
		}
		$allList = array();
		foreach ($list as $li) {
			if (is_array($li) && !empty($li)) {
				$allList = array_merge($allList, $li);
			}
		}
		JDEBUG ? $_PROFILER->mark('fabrik search end') : null;
		return $allList;
	}

	/**
	 * load the required fabrik files
	 *
	 * @param string $view
	 */

	function generalIncludes($view)
	{
		require_once(COM_FABRIK_FRONTEND.DS.'controller.php');
		require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'parent.php');
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fabrik'.DS.'lists');
		JModel::addIncludePath(COM_FABRIK_FRONTEND.DS.'models');
		require_once(COM_FABRIK_FRONTEND.DS.'views'.DS.$view.DS.'view.html.php');
	}
}