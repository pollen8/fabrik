<?php
/**
 * Email form view
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Worker;
use Fabrik\Helpers\StringHelper;
use Fabrik\Helpers\Text;

jimport('joomla.application.component.view');

/**
 * Fabrik Email Form View
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 */
class FabrikViewEmailform extends FabrikView
{
	public $rowId = null;

	public $params = null;

	public $isMambot = null;

	public $id = null;

	/**
	 * Display
	 *
	 * @param   string $tpl Template
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		Html::framework();
		Html::iniRequireJS();
		$input  = $this->app->input;
		$model  = $this->getModel('form');
		$filter = JFilterInput::getInstance();
		$post   = $filter->clean($_POST, 'array');

		if (!array_key_exists('youremail', $post))
		{
			Html::emailForm($model);
		}
		else
		{
			$to = $input->getString('email', '');

			if ($this->sendMail($to))
			{
				$this->app->enqueueMessage(Text::_('COM_FABRIK_THIS_ITEM_HAS_BEEN_SENT_TO') . ' ' . $to, 'success');
			}

			Html::emailSent();
		}
	}

	/**
	 * Send email
	 *
	 * @param   string $email Email
	 *
	 * @throws RuntimeException
	 *
	 * @return  bool
	 */
	public function sendMail($email)
	{
		JSession::checkToken() or die('Invalid Token');
		$input = $this->app->input;

		/*
		 * First, make sure the form was posted from a browser.
		 * For basic web-forms, we don't care about anything
		 * other than requests from a browser:
		 */
		if (is_null($input->server->get('HTTP_USER_AGENT')))
		{
			throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 500);
		}

		// Make sure the form was indeed POST'ed:
		//  (requires your html form to use: action="post")
		if (!$input->server->get('REQUEST_METHOD') == 'POST')
		{
			throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 500);
		}

		// Attempt to defend against header injections:
		$badStrings = array('Content-Type:', 'MIME-Version:', 'Content-Transfer-Encoding:', 'bcc:', 'cc:');

		// Loop through each POST'ed value and test if it contains
		// one of the $badStrings:
		foreach ($_POST as $k => $v)
		{
			foreach ($badStrings as $v2)
			{
				if (StringHelper::strpos($v, $v2) !== false)
				{
					throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 500);
				}
			}
		}

		// Made it past spammer test, free up some memory
		// and continue rest of script:
		unset($k, $v, $v2, $badStrings);
		$email           = $input->getString('email', '');
		$yourName        = $input->getString('yourname', '');
		$yourEmail       = $input->getString('youremail', '');
		$subject_default = Text::sprintf('Email from', $yourName);
		$subject         = $input->getString('subject', $subject_default);
		jimport('joomla.mail.helper');

		if (!$email || !$yourEmail || (Worker::isEmail($email) == false) || (Worker::isEmail($yourEmail) == false))
		{
			$this->app->enqueueMessage(Text::_('PHPMAILER_INVALID_ADDRESS'));
		}

		$siteName = $this->config->get('sitename');

		// Link sent in email
		$link = $input->get('referrer', '', 'string');

		// Message text
		$msg = Text::sprintf('COM_FABRIK_EMAIL_MSG', $siteName, $yourName, $yourEmail, $link);

		// Mail function
		$mail = JFactory::getMailer();

		return $mail->sendMail($yourEmail, $yourName, $email, $subject, $msg);
	}
}
