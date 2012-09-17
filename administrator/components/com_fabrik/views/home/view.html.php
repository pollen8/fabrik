<?php
/**
* @package     Joomla
* @subpackage  Fabrik
* @copyright   Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of lists.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       1.6
 */

class FabrikAdminViewHome extends JViewLegacy
{
	/**
	 * Recently logged activity
	 * @var  array
	 */
	protected $logs;

	/**
	 * RSS feed
	 * @var  array
	 */
	protected $feed;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  template
	 *
	 * @return  void
	 */

	public function display($tpl = null)
	{
		$srcs = FabrikHelperHTML::framework();
		FabrikHelperHTML::script($srcs);
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('*')->from('#__{package}_log')->where('message_type != ""')->order('timedate_created DESC');
		$db->setQuery($query, 0, 10);
		$this->logs = $db->loadObjectList();
		$this->feed = $this->get('RSSFeed');
		$this->addToolbar();
		FabrikAdminHelper::setViewLayout($this);
		parent::display($tpl);
		FabrikAdminHelper::addSubmenu(JRequest::getWord('view', 'lists'));
	}

	/**
	* Add the page title and toolbar.
	*
	* @return  void
	*/

	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/fabrik.php';
		$canDo = FabrikAdminHelper::getActions();
		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_fabrik');
		}
	}

}
