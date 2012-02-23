<?php
/**
 * Form email plugin
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

if (!class_exists('TwitterOAuth')) {
	require_once(COM_FABRIK_FRONTEND.DS.'libs'.DS.'abraham-twitteroauth'.DS.'twitteroauth'.DS.'twitteroauth.php');
}

class plgFabrik_FormTwitter extends plgFabrik_Form {

	/**
	 * @var max length of message
	 */
	var $max_msg_length = 140;

	/**
	 *
	 * Somewhere to put bitly object so bitlyCallback function can get at it
	 * @var unknown_type
	 */
	var $bitly = false;

	/**
	 * process the plugin, called when form is submitted
	 *
	 * @param object $params
	 * @param object form model
	 * @returns bol
	 */

	function onAfterProcess($params, &$formModel)
	{
		$this->_process($params, $formModel);
		//stop default redirect from occuring
		return false;
	}

	protected function buildModel($id)
	{
		$this->formModel = JModel::getInstance('form', 'FabrikFEModel');
		$this->formModel->setId($id);
		$form = $this->formModel->getForm();
		$row = $this->getRow();
		$row->params = $form->params;
		return $this->formModel;
	}

	/**
	 * now that the oauth request tokens have been set via user validation
	 * we want to create the access tokens for said request tokens
	 */

	public function tweet()
	{
		$session = JFactory::getSession();
		global $_SESSION;
		$app = JFactory::getApplication();
		$this->buildModel(JRequest::getInt('formid'));
		$params = $this->getParams();

		$renderOrder = JRequest::getInt('renderOrder');

		$consumer_key = $params->get('twitter_consumer_key');
		if (is_array($consumer_key))
		{
			$consumer_key = $consumer_key[$renderOrder];
		}
		$consumer_secret = $params->get('twitter_consumer_secret');
		if (is_array($consumer_secret))
		{
			$consumer_secret = $consumer_secret[$renderOrder];
		}

		$connection = new TwitterOAuth($consumer_key, $consumer_secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

		/* Request access tokens from twitter */
		$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

		/* Save the access tokens. Normally these would be saved in a database for future use. */
		//$_SESSION['access_token'] = $access_token;

		/* Remove no longer needed request tokens */
		unset($_SESSION['oauth_token']);
		unset($_SESSION['oauth_token_secret']);

		// now we're fully authenticated - lets tweet!
		$this->sendTweet($params, $connection);
	}

	/**
	 *
	 * Actually send the tweet and deals with any redirection
	 * set in the session
	 * @param object $params
	 * @param object $connection
	 */

	function sendTweet($params, $connection)
	{
		$session = JFactory::getSession();
		$formdata = $session->get('com_fabrik.form.data');
		$app = JFactory::getApplication();
		/* If method is set change API call made. Test is called by default. */
		$content = $connection->get('account/rate_limit_status');
		echo "Current API hits remaining: {$content->remaining_hits}.";

		if ($content->remaining_hits <= 0)
		{
			JError::raiseNotice(500, JText::_('TWITTER_ACCOUNT_LIMIT_REACHED'));
		}
		/* Get logged in user to help with tests. */
		$user = $connection->get('account/verify_credentials');
		$msg = $_SESSION['msg'];
		$data = JFactory::getDate();

		$parameters = array('status' => $msg);
		$status = $connection->post('statuses/update', $parameters);
		$show_success = (int)$session->get('com_fabrik.form.twitter.showmessage', 0);
		switch ($connection->http_code)
		{
			case '200':
			case '304':
				if ($show_success == 1)
				{
					$app->enqueueMessage(JText::_('PLG_FORM_TWITTER_SUCCESS_MSG'));
				}
				break;
			default:
				JError::raiseNotice(JText::_('PLG_FORM_TWITTER_ERR'), "$connection->http_code : $status->error");
		}
		$url = $session->get('com_fabrik.form.'.$formdata['fabrik'].'.redirect.url', array($url));
		$url = array_shift($url);
		$app->redirect($url);
	}

	private function _process(&$params, &$formModel)
	{
		global $_SESSION;
		$this->formModel = $formModel;
		$session = JFactory::getSession();

		$session->set('com_fabrik.form.twitter.showmessage', $params->get('twitter-show-success-msg', 0));
		$_SESSION['msg'] = $this->getMessage($params);
		// if the admin has specified an account use that

		$consumer_key = $params->get('twitter_consumer_key');
		$consumer_secret = $params->get('twitter_consumer_secret');

		if ($params->get('twitter_oauth_token') == '')
		{
			return JError::raiseError(500, JText::_('PLG_FORM_TWITTER_ERR_NO_OAUTH_TOKEN'));
		}
		
		if ($params->get('twitter_oauth_token_secret') == '')
		{
			return JError::raiseError(500, JText::_('PLG_FORM_TWITTER_ERR_NO_OAUTH_SECRET_TOKEN'));
		}
		
		if ($params->get('twitter_oauth_token_secret') !== '')
		{

			JRequest::setVar('oauth_verifier', $params->get('twitter_oauth_verifier'));
			$connection = new TwitterOAuth($consumer_key, $consumer_secret, $params->get('twitter_oauth_token'), $params->get('twitter_oauth_token_secret'));
			$this->sendTweet($params, $connection);
			return;
		}

		//otherwise get authorization url from user to use ther own account

		// $this->row not set ?! so this callback url was giving notices
		//$callback = COM_FABRIK_LIVESITE.'index.php?option=com_fabrik&task=plugin.pluginAjax&plugin=twitter&g=form&method=tweet&element_id='.(int)$this->row->id.'&formid='.$formModel->getId();
		$callback = COM_FABRIK_LIVESITE.'index.php?option=com_fabrik&task=plugin.pluginAjax&plugin=twitter&g=form&method=tweet&formid=' . $formModel->getId();
		$callback .= '&renderOrder=' . $this->renderOrder;

		/* Build TwitterOAuth object with client credentials. */
		$connection = new TwitterOAuth($consumer_key, $consumer_secret);

		/* Get temporary credentials. */
		$request_token = $connection->getRequestToken($callback);

		/* Save temporary credentials to session. */

		$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
		$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

		/* If last connection failed don't display authorization link. */
		switch ($connection->http_code)
		{
			case 200:
				/* Build authorize URL and redirect user to Twitter. */
				$url = $connection->getAuthorizeURL($token);
				header('Location: ' . $url);
				break;
			default:
				/* Show notification if something went wrong. */
				JError::raiseNotice(500, $connection->http_code.': Could not connect to Twitter. Refresh the page or try again later.');
		}
	}

	public function getEmailData()
	{
		$data = parent::getEmailData();
		$data['fabrik_editurl'] = JRoute::_(COM_FABRIK_LIVESITE . "index.php?option=com_fabrik&amp;view=form&amp;fabrik=".$this->formModel->getId()."&amp;rowid=".JRequest::getVar('rowid'));
		$data['fabrik_viewurl'] = JRoute::_(COM_FABRIK_LIVESITE . "index.php?option=com_fabrik&amp;view=details&amp;fabrik=".$this->formModel->getId()."&amp;rowid=".JRequest::getVar('rowid'));
		//$$$ rob fabrik_viewurl/fabrik_editurl desribed in help text as fabrik_edit_url/fabrik_view_url.
		$data['fabrik_edit_url'] = $data['fabrik_editurl'];
		$data['fabrik_view_url'] = $data['fabrik_viewurl'];
		$data['fabrik_editlink'] = "<a href=\"{$data['fabrik_editurl']}\">" . JText::_('EDIT') . "</a>";
		$data['fabrik_viewlink'] = "<a href=\"{$data['fabrik_viewurl']}\">" . JText::_('VIEW') . "</a>";
		return $data;
	}

	function bitlifyCallback($url)
	{
		$return_url = $url[1];
		if ($this->bitly === false)
		{
			return $return_url;
		}
		if (!strstr($url[1],'bit.ly/') && $url[1] !== '')
		{
			$return_url = $this->bitly->shorten($url[1]);
			if ($this->bitly->getError() > 0)
			{
				JError::raiseNotice(500, 'Error with bit.ly: ' . $this->bitly->getErrorMsg());
			}
		}
		return $return_url;
	}

	function bitlifyMessage($msg)
	{
		static $bitly;
		if (!isset($bitly))
		{
			$params = $this->getParams();
			$bitly_login = $params->get('twitter_bitly_api_login', '');
			$bitly_key = $params->get('twitter_bitly_api_key', '');
			if (!empty($bitly_login) && !empty($bitly_key))
			{
				require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'libs'.DS.'bitly'.DS.'bitly.php');
				$this->bitly = $bitly = new bitly( $bitly_login, $bitly_key);
			}
			else
			{
				$this->bitly = $bitly = false;
				return $msg;
			}
		}
		$re = "#(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?������]))#";
		$msg = preg_replace_callback($re,array(&$this, 'bitlifyCallback'),$msg);
		return $msg;
	}

	protected function getMessage(&$params)
	{
		$data = $this->getEmailData();
		$twitter_msg_field_id = $params->get('twitter_msg_field', '');
		if ($twitter_msg_field_id != '')
		{
			$elementModel = FabrikWorker::getPluginManager()->getElementPlugin($twitter_msg_field_id);
			$element = $elementModel->getElement(true);
			$twitter_msg_field = $elementModel->getFullName(false, true, false);
			$msg = $data[$twitter_msg_field];
		}
		else
		{
			$w = new FabrikWorker();
			$msg = $w->parseMessageForPlaceHolder($params->get('twitter_msg_tmpl'), $data);
		}
		$msg = $this->bitlifyMessage($msg);
		// $$$ hugh - I thought the twitter class chopped the msg to 140, but apprently it doesn't ..
		$msg = substr($msg, 0, $this->max_msg_length);
		return $msg;
	}

	/**
	 * from admin, get the administrator to authenticate an account for the form
	 */

	public function onAuthenticateAdmin()
	{
		$app = JFactory::getApplication();
		$formModel = $this->buildModel(JRequest::getInt('formid'));
		$params = $formModel->getParams();
		$consumer_key = JRequest::getVar('twitter_consumer_key');
		$consumer_secret = JRequest::getVar('twitter_consumer_secret');
		$counter = JRequest::getInt('repeatCounter');
		$consumer_key = (array)$params->get('twitter_consumer_key');
		$consumer_key = $consumer_key[$counter];

		$consumer_secret = (array)$params->get('twitter_consumer_secret');
		$consumer_secret = $consumer_secret[$counter];

		// $this->row not set ?! so this callback url was giving notices
		//$callback = COM_FABRIK_LIVESITE.'index.php?option=com_fabrik&task=plugin.pluginAjax&plugin=twitter&tmpl=component&g=form&method=updateAdmin&element_id='.(int)$this->row->id.'&formid='.$formModel->getId();
		$callback = COM_FABRIK_LIVESITE.'index.php?option=com_fabrik&task=plugin.pluginAjax&plugin=twitter&tmpl=component&g=form&method=updateAdmin&formid='.$formModel->getId();
		$callback .= "&repeatCounter=". JRequest::getInt('repeatCounter');

		if (!function_exists('curl_init'))
		{
			JError::raiseError(500, JText::_('PLG_FORM_TWITTER_ERR_CURL'));
			return;
		}
		if ($consumer_key == '')
		{
			return JError::raiseError(500, JText::_('PLG_FORM_TWITTER_ERR_NO_OAUTH_TOKEN'));
		}
		
		if ($consumer_secret == '')
		{
			return JError::raiseError(500, JText::_('PLG_FORM_TWITTER_ERR_NO_OAUTH_SECRET_TOKEN'));
		}
		
		/* Build TwitterOAuth object with client credentials. */
		$connection = new TwitterOAuth($consumer_key, $consumer_secret);

		/* Get temporary credentials. */
		$request_token = $connection->getRequestToken($callback);
		/* Save temporary credentials to session. */
		//$session->set('com_fabrik.form.'.$formModel->getId().'.twitter.request_token', $request_token);

		$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
		$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
		$_SESSION['oauth_callback_confirmed'] = $request_token['oauth_callback_confirmed'];

		/* If last connection failed don't display authorization link. */
		switch ($connection->http_code)
		{
			case 200:
				/* Build authorize URL and redirect user to Twitter. */
				$url = $connection->getAuthorizeURL($token);

				$app->redirect($url);
				//header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
				//header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

				//header('Location: ' . $url, true, 302);
				break;
			default:
				/* Show notification if something went wrong. */
				JError::raiseNotice(500, $connection->http_code.': Could not connect to Twitter. Refresh the page or try again later.');
		}
	}

	/**
	 * from admin the user has authorize the plugin for this app.
	 * Lets store the data for it.
	 * As we are in a pop up window we need to set some js to update the parent window's
	 * parameters
	 */

	public function onUpdateAdmin()
	{
		$app = JFactory::getApplication();
		$formModel = $this->buildModel(JRequest::getInt('formid'));
		$params = $formModel->getParams();

		$renderOrder = JRequest::getInt('renderOrder');

		$consumer_key = $params->get('twitter_consumer_key');
		$consumer_key = $consumer_key[$renderOrder];
		$consumer_secret = $params->get('twitter_consumer_secret');
		$consumer_secret = $consumer_secret[$renderOrder];

		$connection = new TwitterOAuth($consumer_key, $consumer_secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

		/* Request access tokens from twitter */
		$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

		//save the access token to the element params
		$formModel = JModel::getInstance('Form', 'FabrikFEModel');
		$formModel->setId(JRequest::getInt('formid'));
		$row = $formModel->getForm();

		$params = $formModel->getParams();
		$opts = $params->toArray();
		$counter = JRequest::getVar('repeatCounter');
		//$opts['twitter_oauth_token']
		$pairs = array('twitter_oauth_token'=>'oauth_token',
		'twitter_oauth_token_secret' => 'oauth_token_secret',
		'twitter_oauth_user' => 'screen_name');

		$js = array();

		foreach ($pairs as $paramname => $requestname)
		{
			$tokens = (array)JArrayHelper::getValue($opts, $paramname);
			$newtokens = array();
			for ($i = 0; $i<=$counter; $i++)
			{
				$newtokens[$i] = ($i == $counter) ? $access_token[$requestname] : '';
				$jsid = '#params' . $paramname . '-' . $i;
				$js[] = "window.opener.document.getElement('$jsid').value = '$newtokens[$i]';";

			}
			$opts[$paramname] = $newtokens;
		}

		$row->params = json_encode($opts);

		if (!$row->store())
		{
			JError::raiseWarning(500, $row->getError());
		}

		$lang = JFactory::getLanguage();
		$langfile = 'com_fabrik.plg.form.fabriktwitter';
		$lang->load($langfile, JPATH_ADMINISTRATOR, null, true);

		//if we had already authorized the app then we will still be in the admin page - so update the fields:

		echo JText::_('PLG_FORM_TWITTER_CREDITIALS_SAVED');
		JHTML::_('behavior.mootools');
		$document = JFactory::getDocument();
		$script=
		implode("\n", $js).
		"
		(function() {window.close()}).delay(4000);
		";
		$document->addScriptDeclaration($script);
	}
}
?>