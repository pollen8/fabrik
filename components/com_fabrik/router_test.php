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
 * if using file extensions sef and htaccess :
 * you need to edit yout .htaccess file to:
 *
 * RewriteCond %{REQUEST_URI} (/|\.csv|\.php|\.html|\.htm|\.feed|\.pdf|\.raw|/[^.]*)$  [NC]
 *
 * otherwise the csv exporter will give you a 404 error
 *
 */

function fabrikBuildRoute(&$query)
{
	$segments = array();

	$app = JFactory::getApplication();
	$menu = $app->getMenu();

	if (empty($query['Itemid']))
	{
		$menuItem = &$menu->getActive();
	}
	else
	{
		$menuItem = &$menu->getItem($query['Itemid']);
	}
	if (isset($query['c']))
	{
		//$segments[] = $query['c'];//remove from sef url
		unset($query['c']);
	}
	;

	if (isset($query['task']))
	{
		$segments[] = $query['task'];
		unset($query['task']);
	}
	;

	if (isset($query['view']))
	{
		$view = $query['view'];
		//$segments[] = $view;
		unset($query['view']);
	}
	else
	{
		$view = '';
	}

	if (isset($query['id']))
	{
		$segments[] = $query['id'];
		unset($query['id']);
	}
	;

	if (isset($query['layout']))
	{
		$segments[] = $query['layout'];
		unset($query['layout']);
	}
	;

	if (isset($query['formid']))
	{
		$segments[] = $query['formid'];
		unset($query['formid']);
	}
	;

	if (isset($query['listid']))
	{
		if ($view != 'form')
		{
			$segments[] = $query['listid'];
		}
		unset($query['listid']);
	}
	;

	if (isset($query['rowid']))
	{
		if ($view == 'form')
		{
			$segments[] = $query['rowid'] == 0 ? 'new' : 'edit';
		}
		else
		{
			$segments[] = $query['rowid'];
		}
		unset($query['rowid']);
	}
	;

	if (isset($query['calculations']))
	{
		$segments[] = $query['calculations'];
		unset($query['calculations']);
	}
	;

	if (isset($query['filetype']))
	{
		$segments[] = $query['filetype'];
		unset($query['filetype']);
	}
	if (isset($query['format']))
	{
		$segments[] = $query['format'];
		//don't unset as with sef urls and extensions on - if we unset it
		//the url's prefix is set to .html
		//unset($query['format']);
	}

	if (isset($query['type']))
	{
		$segments[] = $query['type'];
		unset($query['type']);
	}

	//test
	if (isset($query['fabriklayout']))
	{
		$segments[] = $query['fabriklayout'];
		unset($query['fabriklayout']);
	}
	;
	//test
	if (isset($query['Itemid']) && ($view == 'form' || $view == 'details'))
	{
		//$segments[] = $query['Itemid'];
		//don't unset as with sef urls and extensions on
		//unset($query['Itemid']);
	}
	;

	return $segments;
}

function fabrikParseRoute($segments)
{
	// Vars are what Joomla then uses for its $_REQUEST array
	$vars = array();

	// Get the active menu item
	$app = JFactory::getApplication();
	$menu = $app->getMenu();
	$item = $menu->getActive();

	switch ($segments[0])
	{
		// View (controller not passed into segments)
		case 'form':
		case 'details':
			$vars['task'] = 'view';
			$vars['formid'] = JArrayHelper::getValue($segments, 1, 0);
			$vars['listid'] = JArrayHelper::getValue($segments, 2, 0);
			$vars['rowid'] = JArrayHelper::getValue($segments, 3, 0);
			//test
			$vars['view'] = $segments[0];

			$vars['Itemid'] = JArrayHelper::getValue($segments, 4);
			break;
		case 'table':
			$vars['view'] = JArrayHelper::getValue($segments, 0, '');
			$vars['listid'] = JArrayHelper::getValue($segments, 1, 0);
			;
			//$vars['format'] = $segments[2]; - //test may not be when filtering on tbl, sef, modrewrite and file extension on
			//$vars['type'] = $segments[3];
			break;
		case 'import':
			$vars['view'] = 'import';
			$vars['listid'] = JArrayHelper::getValue($segments, 1, 0);
			;
			$vars['filetype'] = JArrayHelper::getValue($segments, 2, 0);
			;
			break;
		case 'visualization':
			$vars['id'] = JArrayHelper::getValue($segments, 1, 0);
			;
			$vars['format'] = JArrayHelper::getValue($segments, 2, 'html');
			break;
	}
	return $vars;
}
