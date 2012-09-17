<?php
/**
 * @package     Joomla
 * @subpackage	Fabik
 * @copyright	Copyright (C) 2005 - 2008 Pollen 8 Design Ltd. All rights reserved.
 * @license		GNU/GPL
 */

// No direct access
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
		$areas = array('fabrik' => $section);
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

	function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		if (is_array($areas))
		{
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas())))
			{
				return array();
			}
		}
		return plgSystemFabrik::onDoContentSearch($text, $phrase, $ordering, $areas);
	}

}
