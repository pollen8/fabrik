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

class plgFabrik_FormEmail extends plgFabrik_Form {

	var $_aAttachments = array();

	var $_dontEmailKeys = null;

	/**
	 * MOVED TO PLUGIN.PHP SHOULDPROCESS()
	 * determines if a condition has been set and decides if condition is matched
	 *
	 * @param object $params
	 * @return bol true if you sould send the email, false stops sending of eaml
	 */

	/*function shouldSend(&$params)
	{
	}*/

	/**
	 * process the plugin, called when form is submitted
	 *
	 * @param object $params
	 * @param object form model
	 * @returns bol
	 */

	function onAfterProcess($params, &$formModel )
	{
		jimport('joomla.mail.helper');

		$user = JFactory::getUser();
		$config = JFactory::getConfig();
		$db = JFactory::getDbo();

		$this->formModel = $formModel;
		$formParams = $formModel->getParams();
		$emailTemplate = JPath::clean(JPATH_SITE.DS.'plugins'.DS.'fabrik_form'.DS.'email'.DS.'tmpl'.DS . $params->get('email_template', ''));

		//$this->data = $this->getEmailData();
		//getEmailData returns correctly formatted {tablename___elementname} keyed results
		//_formData is there for legacy and may allow you to use {elementname} only placeholders for simple forms
		// $$$ rob swapped order 21/11/2010 so that getEmailData takes preference over _formData
		//$this->data 		= array_merge($this->getEmailData(), $formModel->_formData);
		$this->data = array_merge($formModel->_formData, $this->getEmailData());
		// $$$ hugh - moved this to here from above the previous line, 'cos it needs $this->data
		//check if condition exists and is met
		if (!$this->shouldProcess('email_conditon')) {
			return;
		}

		$contentTemplate = $params->get('email_template_content');
		$content = $contentTemplate != '' ? $this->_getConentTemplate($contentTemplate) : '';
		$htmlEmail = true; //always send as html as even text email can contain html from wysiwg editors

		if (JFile::exists($emailTemplate)) {
			$message = JFile::getExt($emailTemplate) == 'php' ? $this->_getPHPTemplateEmail($emailTemplate) : $this->_getTemplateEmail($emailTemplate);
			// $$$ hugh - added ability for PHP template to return false to abort, same as if 'condition' was was false
			if ($message === false) {
				return;
			}
			$message = str_replace('{content}', $content, $message);
		} else {
			$message = $contentTemplate != '' ? $content : $this->_getTextEmail();
		}
		$this->addAttachments($params);

		$cc = null;
		$bcc = null;
		$w = new FabrikWorker();
		// $$$ hugh - test stripslashes(), should be safe enough.
		$message 	= stripslashes($message);

		$editURL = COM_FABRIK_LIVESITE . 'index.php?option=com_fabrik&amp;view=form&amp;fabrik=' . $formModel->get('id') . '&amp;rowid=' . JRequest::getVar('rowid');
		$viewURL = COM_FABRIK_LIVESITE . 'index.php?option=com_fabrik&amp;view=details&amp;fabrik=' . $formModel->get('id') . '&amp;rowid=' . JRequest::getVar('rowid');
		$editlink = '<a href="' . $editURL . '">' . JText::_('EDIT') . '</a>';
		$viewlink = '<a href="' . $viewURL . '">' . JText::_('VIEW') . '</a>';
		$message = str_replace('{fabrik_editlink}', $editlink, $message);
		$message = str_replace('{fabrik_viewlink}', $viewlink, $message);
		$message = str_replace('{fabrik_editurl}', $editURL, $message);
		$message = str_replace('{fabrik_viewurl}', $viewURL, $message);

		// $$$ rob if email_to is not a valid email address check the raw value to see if that is
		$email_to = explode(',', $params->get('email_to'));
		foreach ($email_to as &$emailkey) {
			$emailkey = $w->parseMessageForPlaceholder($emailkey, $this->data, false);
			//can be in repeat group in which case returns "email1,email2"
			$emailkey = explode(",", $emailkey);
			foreach ($emailkey as &$key) {
				// $$$ rob added strstr test as no point trying to add raw suffix if not placeholder in $emailkey
				if (!JMailHelper::isEmailAddress($key) && trim($key) !== '' && strstr($key, '}')) {
					$key = explode("}", $key);
					if (substr($key[0], -4) !== "_raw") {
						$key = $key[0] . "_raw}";
					} else {
						$key = $key[0].'}';
					}
					$key = $w->parseMessageForPlaceholder($key, $this->data, false);
				}
			}
		}
		//reduce back down to single dimension array
		foreach ($email_to as $i => $a) {
			foreach ($a as $v) {
				$email_to[] = $v;
			}
			unset($email_to[$i]);
		}
		$email_to_eval = $params->get('email_to_eval', '');
		if (!empty($email_to_eval)) {
			$email_to_eval = $w->parseMessageForPlaceholder($email_to_eval, $this->data, false);
			$email_to_eval = @eval($email_to_eval);
			FabrikWorker::logEval($email_to_eval, 'Caught exception on eval in email emailto : %s');
			$email_to_eval = explode(',', $email_to_eval);
			$email_to = array_merge($email_to, $email_to_eval);
		}

		@list($email_from, $email_from_name) = split(":", $w->parseMessageForPlaceholder($params->get('email_from'), $this->data, false));
		if (empty($email_from)) {
			$email_from = $config->getvalue('mailfrom');
		}
		if (empty($email_from_name)) {
			$email_from_name = $config->getValue('fromname', $email_from);
		}
		$subject = $params->get('email_subject');
		if ($subject == "") {
			$subject = $config->getValue('sitename') . " :: Email";
		}
		$subject = preg_replace_callback('/&#([0-9a-fx]+);/mi', array($this, 'replace_num_entity'), $subject);

		$attach_type = $params->get('email_attach_type', '');
		$config = JFactory::getConfig();
		$attach_fname = $config->getValue('config.tmp_path') . DS . uniqid() . '.' . $attach_type;
		/* Send email*/

		foreach ($email_to as $email) {
			$email = trim($email);
			if (empty($email)) {
				continue;
			}
			if (FabrikWorker::isEmail($email)) {
				$thisAttachments = $this->_aAttachments;
				$this->data['emailto'] = $email;
				//see if we can load a user for the email
				$query = $db->getQuery(true);
				//todo move htis out of foreach loop - to reduce queries
				$query->select('id')->from('#__users')->where('email = ' . $db->Quote($email));
				$db->setQuery($query);
				$userid = $db->loadResult();
				$thisUser = JFactory::getUser($userid);

				$thisMessage = $w->parseMessageForPlaceholder($message, $this->data, true, false, $thisUser);
				$thisSubject = strip_tags($w->parseMessageForPlaceholder($subject, $this->data, true, false, $thisUser));

				if (!empty($attach_type)) {
					if (JFile::write($attach_fname, $thisMessage)) {
						$thisAttachments[] = $attach_fname;
					} else {
						$attach_fname = '';
					}

				}
				// Get a JMail instance (have to get a new instnace otherwise the receipients are appended to previously added recipients)
				$mail = JFactory::getMailer();
				$res = $mail->sendMail($email_from, $email_from_name, $email, $thisSubject, $thisMessage, $htmlEmail, $cc, $bcc, $thisAttachments);
				if (JFile::exists($attach_fname)) {
					JFile::delete($attach_fname);
				}
			} else {
				JError::raiseNotice(500, JText::sprintf('PLG_FORM_EMAIL_DID_NOT_SEND_EMAIL_INVALID_ADDRESS', $email));
			}
		}
		return true;
	}

	/**
	 * use a php template for advanced email templates, partularly for forms with repeat group data
	 *
	 * @param bol if file uploads have been found
	 * @param string path to template
	 * @return string email message
	 */

	protected function _getPHPTemplateEmail($tmpl)
	{
		$emailData = $this->data;
		// start capturing output into a buffer
		ob_start();
		$result = require($tmpl);
		$message = ob_get_contents();
		ob_end_clean();
		if ($result === false) {
			return false;
		}
		return $message;
	}

	/**
	 * add attachments to the email
	 */

	function addAttachments($params)
	{
		//get attachments
		$pluginManager = FabrikWorker::getPluginManager();
		$data = $this->getEmailData();
		$groups = $this->formModel->getGroupsHiarachy();
		foreach ($groups as $groupModel) {
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel) {

				$elName = $elementModel->getFullName(false, true, false);
				if (array_key_exists($elName, $this->data)) {

					if (method_exists($elementModel, 'addEmailAttachement')) {
						if (array_key_exists($elName . '_raw',$data)) {
							$val = $data[$elName . '_raw'];
						}
						else {
							$val = $data[$elName];
						}
						if (is_array($val)) {
							$val = implode(",", $val);
						}
						$aVals = FabrikWorker::JSONtoData($val, true);
						foreach ($aVals as $v) {
							$file = $elementModel->addEmailAttachement($v);
							if ($file !== false) {
								$this->_aAttachments[] = $file;
							}
						}
					}
				}
			}
		}
		// $$$ hugh - added an optional eval for adding attachments.
		// Eval'ed code should just return an array of file paths which we merge with $this->_aAttachments[]
		$w = new FabrikWorker();
		$email_attach_eval = $w->parseMessageForPlaceholder($params->get('email_attach_eval', ''), $this->data, false);
		if (!empty($email_attach_eval)) {
			$email_attach_array = @eval($email_attach_eval);
			FabrikWorker::logEval($email_attach_array, 'Caught exception on eval in email email_attach_eval : %s');
			if (!empty($email_attach_array)) {
				$this->_aAttachments = array_merge($this->_aAttachments, $email_attach_array);
			}
		}
	}

	/**
	 * get an array of keys we dont want to email to the user
	 *
	 * @return array
	 */

	function getDontEmailKeys()
	{
		if (is_null($this->_dontEmailKeys)) {
			$this->_dontEmailKeys = array();
			foreach ($_FILES as $key => $file) {
				$this->_dontEmailKeys[] = $key;
			}
		}
		return $this->_dontEmailKeys;
	}

	/**
	 * template email handling routine, called if email template specified
	 * @param string path to template
	 * @return string email message
	 */

	protected function _getTemplateEmail($emailTemplate)
	{
		jimport('joomla.filesystem.file');
		return JFile::read($emailTemplate);
	}

	/**
	 * get content item template
	 * @param int $contentTemplate
	 * @return string content item html (translated with Joomfish if installed)
	 */

	protected function _getConentTemplate($contentTemplate)
	{
		require_once(COM_FABRIK_BASE.'components'.DS.'com_content'.DS.'helpers'.DS.'query.php');
		JModel::addIncludePath(COM_FABRIK_BASE.'components'.DS.'com_content'.DS.'models');
		$articleModel = JModel::getInstance('Article', 'ContentModel');

		// $$$ rob when sending from admin we need to alter $mainframe to be the
		//front end application otherwise com_content errors out trying to create
		//the article
		global $mainframe;
		$origMainframe = $mainframe;
		jimport('joomla.application.application');
		$mainframe = JApplication::getInstance('site', array(), 'J');
		$res = $articleModel->getItem($contentTemplate);
		$mainframe = $origMainframe;
		return $res->introtext . " " . $res->fulltext;
	}

	/**
	 * default email handling routine, called if no email template specified
	 * @return string email message
	 */

	protected function _getTextEmail()
	{
		$data = $this->getEmailData();
		$config = JFactory::getConfig();
		$ignore = $this->getDontEmailKeys();
		$message = "";
		$pluginManager =FabrikWorker::getPluginManager();
		$groupModels = $this->formModel->getGroupsHiarachy();
		foreach ($groupModels as &$groupModel) {
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as &$elementModel) {
				$element = $elementModel->getElement();
				// @TODO - how about adding a 'renderEmail()' method to element model, so specific element types
				// can render themselves?
				$key = (!array_key_exists($element->name, $data)) ? $elementModel->getFullName(false, true, false ) : $element->name;
				if (!in_array($key, $ignore)) {
					$val = '';
					if (is_array($data[$key])) {
						//repeat group data
						foreach ($data[$key] as $k => $v) {
							if (is_array($v)) {
								$val = implode(", ", $v);
							}
							$val .= count($data[$key]) == 1 ? ": $v<br />" : $k++ .": $v<br />";
						}
					} else {
						$val = $data[$key];
					}
					$val = FabrikString::rtrimword( $val, "<br />");
					$val = stripslashes($val);


					// set $val to default value if empty
					if($val == '')
					$val = " - ";

					// don't add a second ":"
					$label = trim(strip_tags($element->label));
					$message .= $label;
					if (strlen($label) != 0 && JString::strpos($label, ':', JString::strlen($label)-1) === false) {
						$message .=":";
					}
					$message .= "<br />" . $val . "<br /><br />";
				}
			}
		}
		$message = JText::_('Email from') . ' ' . $config->getValue('sitename') . "<br />".JText::_('Message').":"
		."<br />===================================<br />".
		"<br />" . stripslashes($message);
		return $message;
	}

}
?>