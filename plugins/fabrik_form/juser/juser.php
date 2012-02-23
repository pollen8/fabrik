<?php

/**
 * Create a Joomla user from the forms data
 * @package Joomla
 * @subpackage Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'plugin-form.php');

class plgFabrik_FormJUser extends plgFabrik_Form {

	var $namefield = '';
	var $emailfield = '';
	var $usernamefield = '';
	var $gidfield = '';
	var $passwordfield = '';
	var $blockfield = '';

	/** @param object element model **/
	var $_elementModel = null;

	/**
	 * get the element full name for the element id
	 * @param plugin params
	 * @param int element id
	 * @return string element full name
	 */

	private function getFieldName($params, $pname)
	{
		$elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));
		return $elementModel->getFullName();
	}

	/**
	 * Get the fields value regardless of whether its in joined data or no
	 * @param object $params
	 * @param string $pname
	 * @param array posted form $data
	 */

	private function getFieldValue($params, $pname, $data)
	{
		$elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));
		$group = $elementModel->getGroup();
		if ($group->isJoin()) {
			$data = $data['join'][$group->getGroup()->join_id];
		}
		$name = $elementModel->getFullName(false, true, false);
		return JArrayHelper::getValue($data, $name);
	}

	// Synchronize J! users with F! table if empty
	function onLoad(&$params, &$formModel)
	{
		if ($params->get('synchro_users') == 1) {
			$listModel = $formModel->getlistModel();
			$fabrikDb = $listModel->getDb();
			$tableName = $listModel->getTable()->db_table_name;

			// Is there already any record in our F! table Users
			$fabrikDb->setQuery("SELECT * FROM $tableName");
			$notempty = $fabrikDb->loadResult();

			if (!$notempty) {
				// Load the list of users from #__users
				$old_users = "SELECT * FROM ".$fabrikDb->nameQuote('#__users')." ORDER BY id ASC";
				$fabrikDb->setQuery($old_users);
				$o_users = $fabrikDb->loadObjectList();
				$count = 0;
				// @TODO really should batch this stuff up, maybe 100 at a time, rather than an insert for every user!
				foreach ($o_users as $o_user) { // Insert into our F! table
					$sync = "INSERT INTO ". $tableName." (`".preg_replace('/^('.$tableName.'___)/', '', $this->getFieldName($params, 'juser_field_userid'))."`, `".preg_replace('/^('.$tableName.'___)/', '', $this->getFieldName($params, 'juser_field_block'))."`, `".preg_replace('/^('.$tableName.'___)/', '', $this->getFieldName($params, 'juser_field_usertype'))."`, `".preg_replace('/^('.$tableName.'___)/', '', $this->getFieldName($params, 'juser_field_email'))."`, `".preg_replace('/^('.$tableName.'___)/', '', $this->getFieldName($params, 'juser_field_password'))."`, `".preg_replace('/^('.$tableName.'___)/', '', $this->getFieldName($params, 'juser_field_username'))."`, `".preg_replace('/^('.$tableName.'___)/', '', $this->getFieldName($params, 'juser_field_name'))."`) VALUES ('".$o_user->id."', '".$o_user->block."', '".$o_user->gid."', '".$o_user->email."', '".$o_user->password."', '".$o_user->username."', '".$o_user->name."');";
					$fabrikDb->setQuery($sync);
					$import = $fabrikDb->query();
					$count = $count +1;
				}
				//@TODO - $$$rob - the $import test below only checks if the LAST query ran ok - should check ALL
				// Display synchonization result
				$app = JFactory::getApplication();
				if ($import) {
					$app->enqueueMessage(JText::sprintf('%s user(s) successfully synchronized from #__users to %s', $count, $tableName));
				} else {
					$app->enqueueMessage(JText::_('An error occured while Synchronizing users. Please verify that all fields are correctly set in your Fabrik table and selected in fabrikjuser form plugin'));
				}
			}
		}

		// if we are editing a user, we need to make sure the password field is cleared
		//if (JRequest::getInt('rowid')) {
		if (FabrikWorker::getMenuOrRequestVar('rowid')) {
			$this->passwordfield 	= $this->getFieldName($params, 'juser_field_password');
			$formModel->_data[$this->passwordfield] = '';
			$formModel->_data[$this->passwordfield . '_raw'] = '';
			// $$$$ hugh - testing 'sync on edit'
			if ($params->get('juser_sync_on_edit', 0) == 1) {
				$this->useridfield = $this->getFieldName($params, 'juser_field_userid');
				$userid = (int)JArrayHelper::getValue($formModel->_data, $this->useridfield . '_raw');
				if ($userid > 0) {
					$user = JFactory::getUser($userid);
					if ($user->get('id') == $userid) {
						$this->namefield = $this->getFieldName($params, 'juser_field_name');
						$formModel->_data[$this->namefield] = $user->get('name');
						$formModel->_data[$this->namefield . '_raw'] = $user->get('name');

						$this->usernamefield = $this->getFieldName($params, 'juser_field_username');
						$formModel->_data[$this->usernamefield] = $user->get('username');
						$formModel->_data[$this->usernamefield . '_raw'] = $user->get('username');

						$this->emailfield = $this->getFieldName($params, 'juser_field_email');
						$formModel->_data[$this->emailfield] = $user->get('email');
						$formModel->_data[$this->emailfield . '_raw'] = $user->get('email');

						//@FIXME this is out of date for J1.7 - no gid field
						if ($params->get('juser_field_usertype') != '') {
							$gid = $user->get('gid');
							$this->gidfield = $this->getFieldName($params, 'juser_field_usertype');
							$formModel->_data[$this->gidfield] = $gid;
							$formModel->_data[$this->gidfield . '_raw'] = $gid;
						}
						if ($params->get('juser_field_block') != '') {
							$this->blockfield = $this->getFieldName($params, 'juser_field_block');
							$formModel->_data[$this->blockfield] = $user->get('block');
							$formModel->_data[$this->blockfield . '_raw'] = $user->get('block');
						}
					}
				}
			}
		}
	}

	/**
	 * run from table model when deleting rows
	 *
	 * @return bol
	 */

	function onDeleteRowsForm(&$params, &$formModel, &$groups)
	{
		if ($params->get('juser_field_userid') != '' && $params->get('juser_delete_user', false)) {
			$useridfield 		= $this->getFieldName($params, 'juser_field_userid');
			$useridfield .= '_raw';
			foreach ($groups as $group) {
				foreach ($group as $rows) {
					foreach ($rows as $row) {
						if (isset($row->$useridfield)) {
							if (!empty($row->$useridfield)) {
								$user = new JUser((int)$row->$useridfield);
								// Bail out now and return false, or just carry on?
								if (!$user->delete()) {
									JError::raiseWarning(500, 'Unable to delete user id ' . $row->$useridfield);
								}
							}
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 * process the plugin, called when form is submitted
	 *
	 * @param object $params
	 * @param object form
	 */

	function onBeforeStore(&$params, &$formModel)
	{
		$app = JFactory::getApplication();
		$config = JFactory::getConfig();
		$lang = JFactory::getLanguage();
		//load up com_users lang - used in email text
		$lang->load('com_users');

		//if the fabrik table is set to be jos_users and the this plugin is used
		//we need to alter the form model to tell it not to store the main row
		// but to still store any joined rows

		$ftable = str_replace('#__', $app->getCfg('dbprefix'), $formModel->getlistModel()->getTable()->db_table_name);
		$jos_users = $app->getCfg('dbprefix') . 'users';

		if ($ftable == $jos_users) {
			$formModel->_storeMainRow = false;
		}

		$usersConfig = JComponentHelper::getParams('com_users');
		// Initialize some variables
		$me = JFactory::getUser();
		$acl = JFactory::getACL();
		$MailFrom	= $app->getCfg('mailfrom');
		$FromName	= $app->getCfg('fromname');
		$SiteName	= $app->getCfg('sitename');
		$siteURL = JURI::base();
		$bypassActivation	= $params->get('juser_bypass_activation', false);
		$bypassRegistration	= $params->get('juser_bypass_registration', true);

		// load in the com_user language file
		$lang = JFactory::getLanguage();
		$lang->load('com_user');

		$data = $formModel->_formData;

		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		$option = JRequest::getCmd('option');

		$original_id = 0;
		if ($params->get('juser_field_userid') != '') {
			$this->useridfield = $this->getFieldName($params, 'juser_field_userid');
			if (!empty($formModel->_rowId)) {
				$original_id = (int)$formModel->_formData[$this->useridfield];
			}
		}
		else {
			$original_id = 0;
			$this->useridfield = '';
		}

		// Create a new JUser object
		$user = new JUser($original_id);
		$originalGroups = $user->getAuthorisedGroups();

		// Are we dealing with a new user which we need to create?
		$isNew 	= ($user->get('id') < 1);

		if ($isNew && $usersConfig->get('allowUserRegistration') == '0' && !$bypassRegistration) {
			JError::raiseError(403, JText::_('Access Forbidden - Registration not enabled'));
			return false;
		}
		//new
		$data = array();

		$this->passwordfield 	= $this->getFieldName($params, 'juser_field_password');
		$this->passwordvalue  = $this->getFieldValue($params, 'juser_field_password', $formModel->_formData);

		$this->namefield 			= $this->getFieldName($params, 'juser_field_name');
		$this->namevalue  		= $this->getFieldValue($params, 'juser_field_name', $formModel->_formData);

		$this->usernamefield 	= $this->getFieldName($params, 'juser_field_username');
		$this->usernamevalue  = $this->getFieldValue($params, 'juser_field_username', $formModel->_formData);

		$this->emailfield 		= $this->getFieldName($params, 'juser_field_email');
		$this->emailvalue  		= $this->getFieldValue($params, 'juser_field_email', $formModel->_formData);

		$data['id'] = $original_id;

		$this->gidfield = $this->getFieldName($params, 'juser_field_usertype');
		$defaultGroup = (int)$params->get('juser_field_default_group');

		$groupId = JArrayHelper::getValue($formModel->_formData, $this->gidfield, $defaultGroup);
		if (is_array($groupId)) {
			$groupId = $groupId[0];
		}
		$groupId = (int)$groupId;

		if (!$isNew) {
			if ($params->get('juser_field_usertype') != '') {
				if (in_array($groupId, $me->getAuthorisedGroups()) || $me->authorise('core.admin')) {
					$data['gid'] = $groupId;
				} else {
					JError::raiseNotice(500, "could not alter user group to $groupId as you are not assigned to that group");
				}
			} else {
				// if editing an existing user and no gid field being used,
				// use default group id
				$data['gid'] = $defaultGroup;
			}
		}
		else {
			$data['gid'] = ($params->get('juser_field_usertype') != '') ? $groupId : $defaultGroup;
		}
		if ($data['gid'] === 0) {
			$data['gid'] = $defaultGroup;
		}
		$user->groups = (array)$data['gid'];

		if ($params->get('juser_field_block') != '') {
			$this->blockfield = $this->getFieldName($params, 'juser_field_block');
			$blocked = JArrayHelper::getValue($formModel->_formData, $this->blockfield, '');
			if (is_array($blocked)) {
				// probably a dropdown
				$data['block'] = (int)$blocked[0];
			}
			else {
				$data['block'] = (int)$blocked;
			}
		}
		else {
			$data['block'] = 0;
		}

		//$$$tom get password field to use in $origdata object if editing user and not changing password
		$origdata = $formModel->_origData;
		$pwfield = $this->passwordfield;

		$data['username']	= $this->usernamevalue;
		$data['password']	= $this->passwordvalue;
		$data['password2']	= $this->passwordvalue;
		$data['name'] = $this->namevalue;
		$name = $this->namevalue;
		$data['email'] = $this->emailvalue;

		$ok = $this->check($data, $formModel, $params);
		if (!$ok) {
			// @TODO - add some error reporting
			return false;
		}
		// Set the registration timestamp

		if ($isNew) {
			$now = JFactory::getDate();
			$user->set('registerDate', $now->toMySQL());
		}

		if ($isNew) {
			// If user activation is turned on, we need to set the activation information
			$useractivation = $usersConfig->get('useractivation');
			if ($useractivation == '1' && !$bypassActivation)
			{
				jimport('joomla.user.helper');
				$data['activation'] = JUtility::getHash(JUserHelper::genRandomPassword());
				$data['block'] = 1;
			}
		}

		// Check that username is not greater than 150 characters
		$username = $data['username'];
		if (strlen($username) > 150) {
			$username = substr($username, 0, 150);
			$user->set('username', $username);
		}

		// Check that password is not greater than 100 characters
		if (strlen($data['password']) > 100) {
			$data['password'] = substr($data['password'], 0, 100);
		}

		// end new
		if (!$user->bind($data))
		{
			$app->enqueueMessage(JText::_('CANNOT SAVE THE USER INFORMATION'), 'message');
			$app->enqueueMessage($user->getError(), 'error');
			return false;
		}

		/*
		 * Lets save the JUser object
		 */
		if (!$user->save())
		{
			$app->enqueueMessage(JText::_('CANNOT SAVE THE USER INFORMATION'), 'message');
			$app->enqueueMessage($user->getError(), 'error');
			return false;
		}
		$session = JFactory::getSession();
		JRequest::setVar('newuserid', $user->id);
		JRequest::setVar('newuserid', $user->id, 'cookie');
		$session->set('newuserid', $user->id);
		JRequest::setVar('newuserid_element', $this->useridfield);
		JRequest::setVar('newuserid_element', $this->useridfield, 'cookie');
		$session->set('newuserid_element', $this->useridfield);
		/*
		 * Time for the email magic so get ready to sprinkle the magic dust...
		 */

		$emailSubject = '';
		if ($isNew)
		{
			// Compile the notification mail values.
			$data = $user->getProperties();
			$data['fromname'] = $config->get('fromname');
			$data['mailfrom'] = $config->get('mailfrom');
			$data['sitename'] = $config->get('sitename');
			$data['siteurl'] = JUri::base();

			$uri = JURI::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));

			// Handle account activation/confirmation emails.
			if ($useractivation == 2 && !$bypassActivation)
			{
				// Set the link to confirm the user email.
				$data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

				$emailSubject = JText::sprintf(
							'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
				);

				$emailBody = JText::sprintf(
							'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY',
				$data['name'],
				$data['sitename'],
				$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
				$data['siteurl'],
				$data['username'],
				$data['password_clear']
				);
			}
			else if ($useractivation == 1 && !$bypassActivation)
			{
				// Set the link to activate the user account.
				$data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

				$emailSubject = JText::sprintf('COM_USERS_EMAIL_ACCOUNT_DETAILS', $data['name'], $data['sitename']);

				$emailBody = JText::sprintf(
							'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
				$data['name'],
				$data['sitename'],
				$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
				$data['siteurl'],
				$data['username'],
				$data['password_clear']
				);
			}
			elseif ($params->get('juser_bypass_accountdetails') != 1)
			{
				$emailSubject = JText::sprintf('COM_USERS_EMAIL_ACCOUNT_DETAILS', $data['name'], $data['sitename']);
				$emailBody = JText::sprintf('COM_USERS_EMAIL_REGISTERED_BODY', $data['name'], $data['sitename'], $data['siteurl'] );
			}

			// Send the registration email.
			if ($emailSubject !== '')
			{
				$return = JUtility::sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);

				// Check for an error.
				if ($return !== true)
				{
					$this->setError(JText::_('COM_USERS_REGISTRATION_SEND_MAIL_FAILED'));
					// Send a system message to administrators receiving system mails
					$db = JFactory::getDBO();
					$q = "SELECT id
								FROM #__users
								WHERE block = 0
								AND sendEmail = 1";
					$db->setQuery($q);
					$sendEmail = $db->loadColumn();
					if (count($sendEmail) > 0) {
						$jdate = new JDate();
						// Build the query to add the messages
						$q = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `date_time`, `subject`, `message`)
									VALUES ";
						$messages = array();
						foreach ($sendEmail as $userid) {
							$messages[] = "(".$userid.", ".$userid.", '".$jdate->toMySQL()."', '".JText::_('COM_USERS_MAIL_SEND_FAILURE_SUBJECT')."', '".JText::sprintf('COM_USERS_MAIL_SEND_FAILURE_BODY', $return, $data['username'])."')";
						}
						$q .= implode(',', $messages);
						$db->setQuery($q);
						$db->query();
					}
				}
			}
		}

		// If updating self, load the new user object into the session
		// FIXME - doesnt work in J1.7??
		/* if ($user->get('id') == $me->get('id'))
		{
			// Get an ACL object
			$acl = &JFactory::getACL();

			// Get the user group from the ACL
			$grp = $acl->getAroGroup($user->get('id'));

			// Mark the user as logged in
			$user->set('guest', 0);
			$user->set('aid', 1);

			// Fudge Authors, Editors, Publishers and Super Administrators into the special access group
			if ($acl->is_group_child_of($grp->name, 'Registered')      ||
			$acl->is_group_child_of($grp->name, 'Public Backend'))    {
				$user->set('aid', 2);
			}

			// Set the usertype based on the ACL group name
			$user->set('usertype', $grp->name);
			$session->set('user', $user);
		} */
		if (!empty($this->useridfield))
		{
			$formModel->updateFormData($this->useridfield, $user->get('id'), true);
		}
		if ($ftable == $jos_users)
		{
			$formModel->_rowId = $user->get('id');
		}
	}

	/**
	 * once the user has been created and then the fabrik record stored
	 * the LAST thing we need to do is see if we want to auto log in the user
	 * @param object $params
	 * @param object $fornModel
	 * @return null
	 */

	function onAfterProcess($params, $formModel)
	{
		$user = JFactory::getUser();
		if ((int)$user->get('id') !== 0) {
			return;
		}
		if ($params->get('juser_auto_login', false)) {
			$app = JFactory::getApplication();

			// $$$ rob 04/02/2011 no longer used - instead a session var is set
			// com_fabrik.form.X.juser.created with values true/false.
			// these values can be used in the redirect plugin to route accordingly

			/*$success_page	= $params->get('juser_success_page', '');
			 $failure_page	= $params->get('juser_failure_page', '');*/

			// $$$ rob - commented this block out as we have already got the values in
			// $this->usernamevalue and $this->passwordvalue

			$username = $this->usernamevalue;
			$password = $this->passwordvalue;
			$options = array();
			$options['remember'] = true;
			$options['return'] = '';
			$options['action'] = '';
			$options['silent'] = true;

			$credentials = array();
			$credentials['username'] = $username;
			$credentials['password'] = $password;
			//@FIXME not working - gives error JERROR_LOGIN_DENIED
			//preform the login action
			$error = $app->login($credentials, $options);

			$session = JFactory::getSession();
			$context = 'com_fabrik.form.'.$formModel->getId().'.juser.';
			$w = new FabrikWorker();
			if (!JError::isError($error))
			{
				$session->set($context.'created', true);
			}
			else
			{
				$session->set($context.'created', false);
			}
		}
	}

	/**
	 * check if the submitted details are ok
	 * @param $post
	 * @param $formModel
	 * @param $params
	 * @return unknown_type
	 */

	function check($post, &$formModel, $params)
	{
		$db = FabrikWorker::getDbo(true);
		$ok = true;
		jimport('joomla.mail.helper');

		if ($post['name'] == '') {
			$formModel->_arErrors[$this->namefield][0][] = JText::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_YOUR_NAME');
			$this->raiseError($formModel->_arErrors, $this->namefield, JText::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_YOUR_NAME'));
			$ok = false;
		}

		if ($post['username'] == '') {
			$this->raiseError($formModel->_arErrors, $this->usernamefield, JText::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_A_USER_NAME'));
			$ok = false;
		}

		if (preg_match( "#[<>\"'%;()&]#i", $post['username']) || strlen(utf8_decode($post['username'])) < 2) {
			$this->raiseError($formModel->_arErrors, $this->usernamefield, JText::sprintf( 'VALID_AZ09', JText::_('Username'), 2));
			$ok = false;
		}

		if ((trim($post['email']) == "") || ! JMailHelper::isEmailAddress( $post['email'])) {
			$this->raiseError($formModel->_arErrors, $this->emailfield, JText::_('JLIB_DATABASE_ERROR_VALID_MAIL'));
			$ok = false;
		}
		if (empty($post['password'])) {
			//$$$tom added a new/edit test
			if (empty($post['id'])) {
				$this->raiseError($formModel->_arErrors, $this->passwordfield, JText::_('Please enter a password'));
				$ok = false;
			}
		} else {
			if ($post['password'] != $post['password2']) {
				$this->raiseError($formModel->_arErrors, $this->passwordfield, JText::_('PASSWORD DO NOT MATCH.'));
				$ok = false;
			}
		}

		// check for existing username
		$query = 'SELECT id'
		. ' FROM #__users '
		. ' WHERE username = ' . $db->Quote($post['username'])
		. ' AND id != '. (int)$post['id'];
		;
		$db->setQuery($query);
		$xid = intval( $db->loadResult());
		if ($xid && $xid != intval($post['id'])) {
			$this->raiseError($formModel->_arErrors, $this->usernamefield, JText::_('JLIB_DATABASE_ERROR_USERNAME_INUSE'));
			$ok = false;
		}

		// check for existing email
		$query = 'SELECT id'
		. ' FROM #__users '
		. ' WHERE email = '. $db->Quote($post['email'])
		. ' AND id != '. (int)$post['id']
		;
		$db->setQuery($query);
		$xid = intval( $db->loadResult());
		if ($xid && $xid != intval($post['id'])) {
			$this->raiseError($formModel->_arErrors, $this->emailfield, JText::_('JLIB_DATABASE_ERROR_EMAIL_INUSE'));
			$ok = false;
		}
		return $ok;
	}

	/**
	 * raise an error - depends on whether ur in admin or not as to what to do
	 * @param array form models error array
	 * @param string $field name
	 * @param string $msg
	 */

	protected function raiseError(&$err, $field, $msg)
	{
		if (JFactory::getApplication()->isAdmin()) {
			JError::raiseNotice(500, $msg);
		} else {
			$err[$field][0][] = $msg;
		}
	}
}
?>