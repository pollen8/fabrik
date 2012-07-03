<?php
/**
* @package     Joomla
* @subpackage  Fabrik
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/
// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controlleradmin');

/**
 * Main Fabrik Admin page controller
 * 
 * @package  Fabrik
 * @since    3.0
 */

class FabrikControllerHome extends JControllerAdmin
{

	/**
	 * Constructor
	 * 
* @param   array  $config  state
	 */

	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * delete all data from fabrik
	 * 
	 * @return null
	 */

	public function reset()
	{
		$model = $this->getModel('Home');
		$model->dropData();
		$model->reset();
		$this->setRedirect('index.php?option=com_fabrik', JText::_('COM_FABRIK_HOME_FABRIK_RESET'));
	}

	/**
	 * reset fabrik !!!
	 * 
	 * @return  null
	 */

	public function dropData()
	{
		$model = $this->getModel('Home');
		$model->dropData();
		$model->reset();
		$this->setRedirect('index.php?option=com_fabrik', JText::_('COM_FABRIK_HOME_FABRIK_RESET'));
	}

	/**
	 * install sample form
	 * 
	 * @return null
	 */

	public function installSampleData()
	{
		$model = $this->getModel('Home');
		$model->installSampleData();
		$this->setRedirect('index.php?option=com_fabrik', JText::_('COM_FABRIK_HOME_SAMPLE_DATA_INSTALLED'));
	}

	/**
	 * ajax function to update any drop down that contains records relating to the selected table
	 * called each time the selected table is changed
	 * 
	 * @deprecated 
	 * 
	 * @return null
	 */

	public function ajax_updateColumDropDowns()
	{
		JError::raiseNotice(500, 'ajax_updateColumDropDowns deprecated');
		/* $cnnId = JRequest::getInt('cid', 1);
		$tbl = JRequest::getVar('table', '');
		$model = JModel::getInstance('List', 'FabrikFEModel');
		$fieldDropDown 	= $model->getFieldsDropDown($cnnId, $tbl, '-', false, 'order_by');
		$fieldDropDown2 = $model->getFieldsDropDown($cnnId, $tbl, '-', false, 'group_by');
		$fieldDropDown3 = $model->getFieldsDropDown($cnnId, $tbl, '-', false, 'params[group_by_order]');
		echo "$('orderByTd').innerHTML = '$fieldDropDown';";
		echo "if($('groupByTd')){
			$('groupByTd').innerHTML = '$fieldDropDown2';
		}";
		echo "if($('groupByOrderTd')){
			$('groupByOrderTd').innerHTML = '$fieldDropDown3';
		}"; */
	}

	/**
	 * get RSS feed
	 * 
	 * @return  string  html table of Fabrik news items
	 */
	public function getRSSFeed()
	{
		//  get RSS parsed object
		$options = array();
		$options['rssUrl']		= 'http://feeds.feedburner.com/fabrik';
		$options['cache_time']	= 86400;
		$rssDoc = JFactory::getXMLparser('RSS', $options);
		if ($rssDoc == false)
		{
			$output = JText::_('Error: Feed not retrieved');
		}
		else
		{
			// Channel header and link
			$title = $rssDoc->get_title();
			$link = $rssDoc->get_link();
			$output = '<table class="adminlist">';
			$output .= '<tr><th colspan="3"><a href="' . $link . '" target="_blank">' . JText::_($title) . '</th></tr>';
			$items = array_slice($rssDoc->get_items(), 0, 3);
			$numItems = count($items);
			if ($numItems == 0)
			{
				$output .= '<tr><th>' . JText::_('No news items found') . '</th></tr>';
			}
			else
			{
				$k = 0;
				for ($j = 0; $j < $numItems; $j++)
				{
					$item = $items[$j];
					$output .= '<tr><td class="row' . $k . '">';
					$output .= '<a href="' . $item->get_link() . '" target="_blank">' . $item->get_title() . '</a>';
					$output .= '<br />' . $item->get_date('Y-m-d');
					if ($item->get_description())
					{
						$description = $this->_truncateText($item->get_description(), 50);
						$output .= '<br />' . $description;
					}
					$output .= '</td></tr>';
				}
			}
			$k = 1 - $k;
			$output .= '</table>';
		}
		return $output;
	}

}
