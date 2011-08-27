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

	protected $_pluginManager = null;

	var $_counter = null;

	var $namefield = '';
	var $emailfield = '';
	var $usernamefield = '';
	var $gidfield = '';
	var $passwordfield = '';
	var $blockfield = '';

	/** @param object element model **/
	var $_elementModel = null;


	/**
	 * get an element model
	 * @return object element model
	 */

	private function getElementModel()
	{
		if (!isset($this->_elementModel)) {
			$this->_elementModel =& JModel::getInstance('element','FabrikModel');
		}
		return $this->_elementModel;
	}

	/**
	 * get the element full name for the element id
	 * @param plugin params
	 * @param int element id
	 * @return string element full name
	 */

	private function getFieldName($params, $pname)
	{
		$elementModel = $this->getPluginManager()->getElementPlugin($params->get($pname));
		return $elementModel->getFullName();
	}

	/**
	 * @since 3.0
	 * get the plugin manager
	 * @return plugin manager
	 */

	protected function getPluginManager()
	{
		if (!isset($this->_pluginManager)) {
			$this->_pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		}
		return $this->_pluginManager;
	}

	/**
	 * Get the fields value regardless of whether its in joined data or no
	 * @param object $params
	 * @param string $pname
	 * @param array posted form $data
	 */

	private function getFieldValue($params, $pname, $data)
	{
		$elementModel = $this->getPluginManager()->getElementPlugin($params->get($pname));
		$group =& $elementModel->getGroup();
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
				$app =& JFactory::getApplication();
				if ($import) {
					$app->enqueueMessage(JText::sprintf('%s user(s) successfully synchronized from #__users to %s', $count, $tableName));
				} else {
					$app->enqueueMessage(JText::_('An error occured while Synchronizing users. Please verify that all fields are correctly set in your Fabrik table and selected in fabrikjuser form plugin'));
				}
			}
		}

		// if we are editing a user, we need to make sure the password field is cleared
		if (JRequest::getInt('rowid')) {
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
		$usertype_max = (int)$params->get('juser_usertype_max', 1);

		// load in the com_user language file
		$lang =& JFactory::getLanguage();
		$lang->load('com_user');

		$data =& $formModel->_formData;

		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		$option = JRequest::getCmd('option');

		$original_id = 0;
		if ($params->get('juser_field_userid') != '') {
			$this->useridfield = $this->getFieldName($params, 'juser_field_userid');
			if (!empty($formModel->_rowId)) {
				$original_id = (int)$data[$this->useridfield];
			}
		}
		else {
			$original_id = 0;
			$this->useridfield = '';
		}

		// Create a new JUser object
		$user = new JUser($original_id);
		$original_gid = $user->get('gid');

		// Are we dealing with a new user which we need to create?
		$isNew 	= ($user->get('id') < 1);

		//$post = JRequest::get('post');

		if ($isNew && $usersConfig->get('allowUserRegistration') == '0' && !$bypassRegistration) {
			JError::raiseError(403, JText::_('Access Forbidden - Registration not enabled'));
			return false;
		}
		//new
		$post = array();

		$this->passwordfield 	= $this->getFieldName($params, 'juser_field_password');
		$this->passwordvalue  = $this->getFieldValue($params, 'juser_field_password', $data);

		$this->namefield 			= $this->getFieldName($params, 'juser_field_name');
		$this->namevalue  		= $this->getFieldValue($params, 'juser_field_name', $data);

		$this->usernamefield 	= $this->getFieldName($params, 'juser_field_username');
		$this->usernamevalue  = $this->getFieldValue($params, 'juser_field_username', $data);

		$this->emailfield 		= $this->getFieldName($params, 'juser_field_email');
		$this->emailvalue  		= $this->getFieldValue($params, 'juser_field_email', $data);

		$post['id'] = $original_id;

		if (!$isNew) {
			// for now, don't allow changing f GIDthru JUser plugin!
			// $post['gid'] = $original_gid;
			// $$$ hugh - let's allow gid to be changed as long as it doesn't
			// exceed the currently logged on user's level
			// yes, i know this duplicates codce from below, for now I'm just noodling around
			if ($params->get('juser_field_usertype') != '') {
				$this->gidfield 		= $this->getFieldName($params, 'juser_field_usertype');
				$post['gid'] = JArrayHelper::getValue($data, $this->gidfield, 1);
				if (is_array($post['gid'])) {
					$post['gid'] = $post['gid'][0];
				}
				$post['gid'] = (int)$post['gid'];
				if ($post['gid'] > $me->get('gid')) {
					$post['gid'] = $me->get('gid');
				}
			}
			else {
				// if editing an existing user and no gid field being used,
				// use existing gid.
				$post['gid'] = $original_gid;
			}
		}
		else {
			if ($params->get('juser_field_usertype') != '') {
				$this->gidfield = $this->getFieldName($params, 'juser_field_usertype');
				$post['gid'] = JArrayHelper::getValue($data, $this->gidfield, 1);
				if (is_array($post['gid'])) {
					$post['gid'] = $post['gid'][0];
				}
			}
			else {
				$post['gid'] = 1;
			}
		}
		$post['gid'] = (int)$post['gid'];
		if ($post['gid'] === 0) {
			$post['gid'] = 1;
		}
		// $$$ hugh - added 'usertype_max' param, as a safety net to prevent GID's being
		// set to arbitrarily high values thru spoofing.

		if ($post['gid'] > $usertype_max && $post['gid'] != $original_gid) {
			//$post['gid'] = $usertype_max;
			$msg = JText::_('Attempting to set usertype above allowed level!');
			$app->enqueueMessage($msg, 'message');
			return false;
		}

		if ($params->get('juser_field_block') != '') {
			$this->blockfield = $this->getFieldName($params, 'juser_field_block');
			$blocked = JArrayHelper::getValue($data, $this->blockfield, '');
			if (is_array($blocked)) {
				// probably a dropdown
				$post['block'] = (int)$blocked[0];
			}
			else {
				$post['block'] = (int)$blocked;
			}
		}
		else {
			$post['block'] = 0;
		}

		//$$$tom get password field to use in $origdata object if editing user and not changing password
		$origdata =& $formModel->_origData;
		$pwfield = $this->passwordfield;

		$post['username']	= $this->usernamevalue;
		$post['password']	= $this->passwordvalue;
		$post['password2']	= $this->passwordvalue;
		$post['name'] = $this->namevalue;
		$name = $this->namevalue;
		$post['email'] = $this->emailvalue;

		$ok = $this->check($post, $formModel, $params);
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
				$user->set('activation', md5(JUserHelper::genRandomPassword()));
				$user->set('block', '1');
			}
		}

		// Check that username is not greater than 150 characters
		$username = $post['username'];
		if (strlen($username) > 150) {
			$username = substr($username, 0, 150);
			$user->set('username', $username);
		}

		// Check that password is not greater than 100 characters
		if (strlen($post[ 'password' ]) > 100) {
			$post['password'] = substr($post[ 'password' ], 0, 100);
		}

		// end new
		if (!$user->bind($post))
		{
			echo "error";
			echo "<pre>";print_r($user);exit;
			$app->enqueueMessage(JText::_('CANNOT SAVE THE USER INFORMATION'), 'message');
			$app->enqueueMessage($user->getError(), 'error');
			return false;
		}
	/* think following is done in user->save();
		//$objectID 	= $acl->get_object_id('users', $user->get('id'), 'ARO');
		//$groups 	= $acl->get_object_groups($objectID, 'ARO');
		//$this_group = strtolower($acl->get_group_name($groups[0], 'ARO'));

		if (!$isNew) {


			if ($user->get('id') == $me->get('id') && $user->get('block') == 1) {
				$msg = JText::_('You cannot block Yourself!');
				$app->enqueueMessage($msg, 'message');
				return false;
			}
			else if (( $this_group == 'super administrator') && $user->get('block') == 1) {
				$msg = JText::_('You cannot block a Super Administrator');
				$app->enqueueMessage($msg, 'message');
				return false;
			}
			else if (( $this_group == 'administrator') && ($me->get('gid') == 24) && $user->get('block') == 1 )
			{
				$msg = JText::_('WARNBLOCK');
				$app->enqueueMessage($msg, 'message');
				return false;
			}
			else if (( $this_group == 'super administrator') && ($me->get('gid') != 25 ) )
			{
				$msg = JText::_('You cannot edit a super administrator account');
				$app->enqueueMessage($msg, 'message');
				return false;
			}


			$iAmSuperAdmin	= $me->authorise('core.admin');
			// if group has been changed and where original group was Speical
			if ($user->get('gid') != $original_gid && $iAmSuperAdmin) {
				$db = FabrikWorker::getDbo();
				// count number of active super admins
				$query = 'SELECT COUNT( id )'
				. ' FROM #__users'
				. ' WHERE gid = 25'
				. ' AND block = 0'
				;
				$db->setQuery($query);
				$count = $db->loadResult();

				if ($count <= 1 ) {
					// disallow change if only one Super Admin exists
					$this->setRedirect('index.php?option=com_users', JText::_('WARN_ONLY_SUPER'));
					return false;
				}
			}
		}*/

		/*
		 * Lets save the JUser object
		 */
		if (!$user->save())
		{
			echo "err!";
			echo "<pre>";print_r($user);;exit;
			$app->enqueueMessage(JText::_('CANNOT SAVE THE USER INFORMATION'), 'message');
			$app->enqueueMessage($user->getError(), 'error');
			return false;
		}
		//assign user to a group
		JUserHelper::addUserToGroup($user->get('id'), $post['gid']);
		$session = &JFactory::getSession();
		JRequest::setVar('newuserid', $user->id);
		JRequest::setVar('newuserid', $user->id, 'cookie');
		$session->set('newuserid', $user->id);
		JRequest::setVar('newuserid_element', $this->useridfield);
		JRequest::setVar('newuserid_element', $this->useridfield, 'cookie');
		$session->set('newuserid_element', $this->useridfield);
		/*
		 * Time for the email magic so get ready to sprinkle the magic dust...
		 */

		if ($isNew)
		{
			$adminEmail = $me->get('email');
			$adminName	= $me->get('name');

			$subject 	= sprintf(JText::_('PLG_FABRIK_FORM_JUSER_ACCOUNT_DETAILS_FOR'), $name, $SiteName);
			$subject 	= html_entity_decode($subject, ENT_QUOTES);

			if ($useractivation == 1 && !$bypassActivation) {

				$message = sprintf(JText::_('PLG_FABRIK_FORM_JUSER_SEND_MSG_ACTIVATE'), $name, $SiteName, $siteURL."index.php?option=com_user&task=activate&activation=".$user->get('activation'), $siteURL, $username, $user->password_clear);
			} else if ($params->get('juser_bypass_accountdetails', 0) != 1) { //$$$tom adding Bypass Joomla's "Account details for..." email
				$message = sprintf(JText::_('PLG_FABRIK_FORM_JUSER_SEND_MSG'), $name, $SiteName, $siteURL);
			}

			$message = html_entity_decode($message, ENT_QUOTES);

			if ($MailFrom != '' && $FromName != '')
			{
				$adminName 	= $FromName;
				$adminEmail = $MailFrom;
			}
			if ($message) { //$$$tom see comment above about bypassing Joomla's email
				JUtility::sendMail($adminEmail, $adminName, $user->get('email'), $subject, $message);
			}
		}
echo "<pre>";print_r($user);;
		// If updating self, load the new user object into the session
		if ($user->get('id') == $me->get('id'))
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
		}
		if (!empty($this->useridfield)) {
			$formModel->updateFormData($this->useridfield, $user->get('id'), true);
		}
		if ($ftable == $jos_users) {
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
		$user =& JFactory::getUser();
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

			$session =& JFactory::getSession();
			$context = 'com_fabrik.form.'.$formModel->_id.'.juser.';
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
			$ok = false;
		}

		if ($post['username'] == '') {
			$formModel->_arErrors[$this->usernamefield][0][] = JText::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_A_USER_NAME');
			$ok = false;
		}

		if (preg_match( "#[<>\"'%;()&]#i", $post['username']) || strlen(utf8_decode($post['username'])) < 2) {
			$formModel->_arErrors[$this->usernamefield][0][] = JText::sprintf( 'VALID_AZ09', JText::_('Username'), 2);
			$ok = false;
		}

		if ((trim($post['email']) == "") || ! JMailHelper::isEmailAddress( $post['email'])) {
			$formModel->_arErrors[$this->emailfield][0][] = JText::_('JLIB_DATABASE_ERROR_VALID_MAIL');
			$ok = false;
		}
		if (empty($post['password'])) {
			//$$$tom added a new/edit test
			if (empty($post['id'])) {
				$formModel->_arErrors[$this->passwordfield][0][] = JText::_('Please enter a password');
				$ok = false;
			}
		} else {
			if ($post['password'] != $post['password2']) {
				$formModel->_arErrors[$this->passwordfield][0][] = JText::_('PASSWORD DO NOT MATCH.');
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
			$formModel->_arErrors[$this->usernamefield][0][] = JText::_('JLIB_DATABASE_ERROR_USERNAME_INUSE');
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
			$formModel->_arErrors[$this->emailfield][0][] = JText::_('JLIB_DATABASE_ERROR_EMAIL_INUSE');
			$ok = false;
		}
		return $ok;
	}
}
?>