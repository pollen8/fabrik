<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of lists.
 *
 * @package		Joomla.Administrator
 * @subpackage	Fabrik
 * @since		1.6
 */
class FabrikViewHome extends JView
{
	protected $logs;
	protected $feed;

	/**
	 * Display the view
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
		parent::display($tpl);
	}


}
