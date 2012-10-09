<?php
/**
* @package     Joomla
* @subpackage  Fabrik
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// No direct access.
defined('_JEXEC') or die;

require_once 'fabcontrolleradmin.php';

/**
 * Forms list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	Fabrik
 * @since		3.0
 */
class FabrikAdminControllerForms extends FabControllerAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */

	protected $text_prefix = 'COM_FABRIK_FORMS';

	protected $view_item = 'forms';

	/**
	 * Constructor.
	 *
* @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see		JController
	 * @since	1.6
	 */

	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Proxy for getModel.
	 *
* @param   string  $name    model name
* @param   string  $prefix  model prefix
	 *
	 * @since	1.6
	 *
	 * @return  model
	 */

	public function &getModel($name = 'Form', $prefix = 'FabrikAdminModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * attempt to alter teh db structure to match the form's current status
	 *
	 * @return  null
	 */

	public function updateDatabase()
	{
		// Check for request forgeries
		JSession::checkToken() or die('Invalid Token');
		$this->setRedirect('index.php?option=com_fabrik&view=forms');
		$this->getModel()->updateDatabase();
		$this->setMessage(JText::_('COM_FABRIK_DATABASE_UPDATED'));
	}

	/**
	 * View the list data
	 *
	 * @return  null
	 */

	public function listview()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$cid = $input->get('cid', array(0), 'array');
		$cid = $cid[0];
		$db = JFactory::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('id')->from('#__fabrik_lists')->where('form_id = ' . (int) $cid);
		$db->setQuery($query);
		$listid = $db->loadResult();
		$this->setRedirect('index.php?option=com_fabrik&task=list.view&listid=' . $listid);
	}

}
