<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003-2009 Think Network GmbH, Munich
 *
 * All rights reserved.  The Joom!Fish project is a set of extentions for
 * the content management system Joomla!. It enables Joomla!
 * to manage multi lingual sites especially in all dynamic information
 * which are stored in the database.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -----------------------------------------------------------------------------
 * $Id: translationFabrikgroupFilter.php 1251 2009-04-14 18:33:02Z Benjamin Rivalland $
 * @package joomfish
 * @subpackage TranslationFilters
 *
*/

// Don't allow direct linking
defined('JPATH_BASE') or die('Direct Access to this location is not allowed.');

class translationFabrikGroupFilter extends translationFilter
{
	function translationFabrikGroupFilter ($contentElement){
		$this->filterNullValue=-1;
		$this->filterType="fabrikgroup";
		$this->filterField = $contentElement->getFilter("fabrikgroup");
		parent::translationFilter($contentElement);
	}

	/**
 * Creates section filter
 *
 * @param unknown_type $filtertype
 * @param unknown_type $contentElement
 * @return unknown
 */
	function _createfilterHTML(){
		$db = FabrikWorker::getDbo(true);

		if (!$this->filterField) return "";
		$groupOptions=array();
		$groupOptions[] = JHTML::_('select.option', '-1', JText::_('All Groups'));
		$groupOptions[] = JHTML::_('select.option', '0', JText::_('Uncategorized'));

		$query = $db->getQuery(true);
		$query->select("DISTINCT e.group_id, g.name, g.id")->from("#__{package}_groups as g, #__".$this->tableName." as e")
		->where("e.".$this->filterField."=g.id")
		->order("ORDER BY g.name");
		$db->setQuery($query);
		$groups = $db->loadObjectList();
		$sectioncount=0;
		foreach($groups as $group){
			$groupOptions[] = JHTML::_('select.option', $group->id,$group->name);
			$sectioncount++;
		}
		$groupList=array();
		$groupList["title"]= JText::_('Group filter');
		$groupList["html"] = JHTML::_('select.genericlist', $groupOptions, 'fabrikgroup_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);
		return $groupList;

	}

}


?>
