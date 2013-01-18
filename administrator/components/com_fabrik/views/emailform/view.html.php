<?php
/**
 * View when emailing a form to a user
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * View when emailing a form to a user
 *
 * @package		Joomla.Administrator
 * @subpackage	Fabrik
 * @since		3.0
 */

class fabrikViewEmailform extends JView
{

	/**
	 * Display
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return  void
	 */

	public function display($tpl)
	{
		$srcs = FabrikHelperHTML::framework();
		FabrikHelperHTML::script($srcs);
		$model = JModel::getInstance('form', 'FabrikFEModel');
		$app = JFactory::getApplication();
		$input = $app->input;
		if (!$input->get('youremail', false))
		{
			FabrikHelperHTML::emailForm($model);
		}
		else
		{
			$to = $template = '';
			$ok = $this->sendMail($to);
			FabrikHelperHTML::emailSent($to, $ok);
		}
	}

	/**
	 * Send a mail
	 *
	 * @param   string  $email  Email address
	 *
	 * @return  void
	 */

	function sendMail(&$email)
	{
		JSession::checkToken() or die('Invalid Token');
		$app = JFactory::getApplication();
		$input = $app->input;

		// First, make sure the form was posted from a browser.
		// For basic web-forms, we don't care about anything
		// other than requests from a browser:
		if (!isset($_SERVER['HTTP_USER_AGENT']))
		{
			JError::raiseError(500, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// Make sure the form was indeed POST'ed:
		//  (requires your html form to use: action="post")
		if (!$_SERVER['REQUEST_METHOD'] == 'POST')
		{
			JError::raiseError(500, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// Attempt to defend against header injections:
		$badStrings = array('Content-Type:', 'MIME-Version:', 'Content-Transfer-Encoding:', 'bcc:', 'cc:');

		// Loop through each POST'ed value and test if it contains
		// one of the $badStrings:
		foreach ($_POST as $k => $v)
		{
			foreach ($badStrings as $v2)
			{
				if (JString::strpos($v, $v2) !== false)
				{
					JError::raiseError(500, JText::_('JERROR_ALERTNOAUTHOR'));
				}
			}
		}

		// Made it past spammer test, free up some memory
		// and continue rest of script:
		unset($k, $v, $v2, $badStrings);
		$email = $input->get('email', '');
		$yourname = $input->get('yourname', '');
		$youremail = $input->get('youremail', '');
		$subject_default = JText::sprintf('Email from', $yourname);
		$subject = $input->get('subject', $subject_default);
		jimport('joomla.mail.helper');

		if (!$email || !$youremail || (JMailHelper::isEmailAddress($email) == false) || (JMailHelper::isEmailAddress($youremail) == false))
		{
			JError::raiseError(500, JText::_('EMAIL_ERR_NOINFO'));
		}

		$config = JFactory::getConfig();
		$sitename = $config->get('sitename');

		// Link sent in email
		$link = $input->get('referrer', '', 'string');

		// Message text
		$msg = JText::sprintf('COM_FABRIK_EMAIL_MSG', $sitename, $yourname, $youremail, $link);

		// mail function
		JUTility::sendMail($youremail, $yourname, $email, $subject, $msg);
	}

}
