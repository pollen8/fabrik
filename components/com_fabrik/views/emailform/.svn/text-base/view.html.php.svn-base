<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewEmailform extends JView
{

	var $_template 	= null;
	var $_errors 	= null;
	var $_data 		= null;
	var $_rowId 	= null;
	var $_params 	= null;
	var $isMambot = null;

	var $_id 			= null;

	function display()
	{
		FabrikHelperHTML::framework();
		$model =& $this->getModel('form');
		$post =& JRequest::get('post');
		if (!array_key_exists('youremail', $post)) {
			FabrikHelperHTML::emailForm( $model);
		} else {
			$to = $template = '';
			$this->sendMail( $to);
			FabrikHelperHTML::emailSent($to);
		}
	}

	function sendMail( &$email )
	{
		JRequest::checkToken() or die('Invalid Token');

		// First, make sure the form was posted from a browser.
		// For basic web-forms, we don't care about anything
		// other than requests from a browser:
		if (!isset($_SERVER['HTTP_USER_AGENT'])) {
			JError::raiseError(500, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// Make sure the form was indeed POST'ed:
		//  (requires your html form to use: action="post")
		if (!$_SERVER['REQUEST_METHOD'] == 'POST') {
			JError::raiseError(500, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// Attempt to defend against header injections:
		$badStrings = array(
		'Content-Type:',
		'MIME-Version:',
		'Content-Transfer-Encoding:',
		'bcc:',
		'cc:'
		);

		// Loop through each POST'ed value and test if it contains
		// one of the $badStrings:
		foreach ($_POST as $k => $v) {
			foreach ($badStrings as $v2) {
				if (JString::strpos($v, $v2 ) !== false) {
					JError::raiseError(500, JText::_('JERROR_ALERTNOAUTHOR'));
				}
			}
		}

		// Made it past spammer test, free up some memory
		// and continue rest of script:
		unset($k, $v, $v2, $badStrings);
		$email 				= JRequest::getVar('email', '');
		$yourname 			= JRequest::getVar('yourname', '');
		$youremail 			= JRequest::getVar('youremail', '');
		$subject_default 	= JText::sprintf( 'Email from', $yourname);
		$subject = JRequest::getVar('subject', $subject_default);
		jimport('joomla.mail.helper');

		if (!$email || !$youremail || ( JMailHelper::isEmailAddress($email) == false) || (JMailHelper::isEmailAddress($youremail) == false)) {
			JError::raiseError(500, JText::_('EMAIL_ERR_NOINFO'));
		}

		$config = JFactory::getConfig();
		$sitename = $config->getValue('sitename');
		// link sent in email

		$link = JRequest::getVar('referrer');
		// message text
		$msg =JText::sprintf( 'COM_FABRIK_EMAIL_MSG', $sitename, $yourname, $youremail, $link);

		// mail function
		JUTility::sendMail($youremail, $yourname, $email, $subject, $msg);

	}

}
?>