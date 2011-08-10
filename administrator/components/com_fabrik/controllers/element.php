<?php
/*
 * @package Joomla.Administrator
 * @subpackage Fabrik
 * @since		1.6
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// No direct access
defined('_JEXEC') or die;

require_once('fabcontrollerform.php');

/**
 * Element controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_fabrik
 * @since		1.6
 */
class FabrikControllerElement extends FabControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_ELEMENT';

	/**
	 * Set a URL for browser redirection.
	 *
	 * @param	string 		URL to redirect to.
	 * @param	string		Message to display on redirect. Optional, defaults to value set internally by controller, if any.
	 * @param	string		Message type. Optional, defaults to 'message'.
	 * @return	JController	This object to support chaining.
	 * @since	1.5
	 */

	public function setRedirect($url, $msg = null, $type = null)
	{
		$app = JFactory::getApplication();
		$confirmUpdate =  $app->getUserState('com_fabrik.confirmUpdate');

		// @TODO override the redirect url if confirm update is needed and task appropriate
		if ($confirmUpdate == true) {
			$url = $app->getUserState('com_fabrik.redirect');
			$this->redirect = $url;//'index.php?option=com_fabrik&view=element&layout=confirmupdate';
		}
		$this->redirect = $url;

		if ($msg !== null) {
			// controller may have set this directly
			$this->message	= $msg;
		}

		// Ensure the type is not overwritten by a previous call to setMessage.
		$this->messageType	= ($type === null || empty($this->messageType)) ? 'message' : $type;

		return $this;
	}
	
	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   11.1
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);
		$gid = JRequest::getInt('filter_groupId');
		if ($gid !== 0) {
			$append .= '&filter_groupId='.$gid;
		}
		return $append;
	}

	/**
	 * ask if the user really wants to update element field name/type
	 */

	function updatestructure()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('Invalid Token');
		$app = JFactory::getApplication();
		JModel::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models');
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		$model = $pluginManager->getPlugIn('field', 'element');
		$id = JRequest::getInt('id');
		$model->setId($id);
		$fabrikDb = $model->getListModel()->getDB();
		$fabrikDb->setQuery($app->getUserState('com_fabrik.q'));

		if (!$fabrikDb->query()) {
			JError::raiseWarning(E_WARNING, $fabrikDb->stderr(true));
			$msg = '';
		} else {
			$msg = JText::_('COM_FABRIK_STRUCTURE_UPDATED');
		}
		if (JRequest::getCmd('origtask') == 'save') {
			$this->setRedirect("index.php?option=com_fabrik&view=elements", $msg);
		} else {
			$this->setRedirect('index.php?option=com_fabrik&task=element.edit&id='. $id, $msg);
		}
	}

	/**
	 * user decided to cancel update
	 */

	function cancelUpdatestructure()
	{
		JRequest::checkToken() or die('Invalid Token');
		JModel::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models');
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		$model = $pluginManager->getPlugIn('field', 'element');
		$model->setId(JRequest::getInt('id'));
		$element =& $model->getElement();
		$element->name = JRequest::getWord('oldname');
		$element->plugin = JRequest::getWord('origplugin');
		$element->store();
		if (JRequest::getVar('origtask') == 'save') {
			$this->setRedirect("index.php?option=com_fabrik&view=elements", $msg);
		} else {
			$this->setRedirect('index.php?option=com_fabrik&task=element.edit&id='. $element->id, $msg);
		}
	}

	/**
	 * Method to save a record.
	 *
	 * @return	boolean
	 * @since	1.6
	 */

	public function save()
	{
		$ok = parent::save();
		$app = JFactory::getApplication();
		if (!is_null($app->getUserState('com_fabrik.redirect'))) {
			$this->setRedirect($app->getUserState('com_fabrik.redirect') );
			$app->setUserState('com_fabrik.redirect', null);
		}
		return $ok;
	}

}