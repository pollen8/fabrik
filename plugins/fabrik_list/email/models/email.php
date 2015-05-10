<?php
/**
 * Email list plugin model
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.email
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once COM_FABRIK_FRONTEND . '/models/plugin-list.php';

/**
 * Email list plugin model
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.email
 * @since       3.0
 */
class PlgFabrik_ListEmail extends PlgFabrik_List
{
	/**
	 * Button prefix
	 *
	 * @var string
	 */
	protected $buttonPrefix = 'envelope';

	/**
	 * Can the plug-in select list rows
	 *
	 * @return  bool
	 */

	public function canSelectRows()
	{
		return $this->canUse();
	}

	/**
	 * Get the parameter name that defines the plugins acl access
	 *
	 * @return  string
	 */

	protected function getAclParam()
	{
		return 'emailtable_access';
	}

	/**
	 * Prep the button if needed
	 *
	 * @param   array &$args Arguments
	 *
	 * @return  bool;
	 */

	public function button(&$args)
	{
		parent::button($args);

		return true;
	}

	/**
	 * Get the button label
	 *
	 * @return  string
	 */

	protected function buttonLabel()
	{
		return $this->getParams()->get('email_button_label', parent::buttonLabel());
	}

	/**
	 * Return the javascript to create an instance of the class defined in formJavascriptClass
	 *
	 * @param   array $args Array [0] => string table's form id to contain plugin
	 *
	 * @return bool
	 */

	public function onLoadJavascriptInstance($args)
	{
		FabrikHelperHTML::slimbox();
		parent::onLoadJavascriptInstance($args);
		$opts              = $this->getElementJSOptions();
		$opts->renderOrder = $this->renderOrder;
		$opts              = json_encode($opts);
		$this->jsInstance  = "new FbListEmail($opts)";

		return true;
	}

	private function _toType()
	{
		return $this->getParams()->get('emailtable_to_type');
	}

	private function _toHow()
	{
		return $this->getParams()->get('emailtable_to_how', 'single');
	}

	/**
	 * Get to field
	 *
	 * @return string
	 */
	public function getToField()
	{
		$app         = JFactory::getApplication();
		$input       = $app->input;
		$this->_type = 'table';
		$this->_id   = $input->getInt('id', 0);
		$params      = $this->getParams();
		$toType      = $this->_toType();

		if ($toType == 'field')
		{
			$to = $this->_emailTo();

			switch ($params->get('emailtable_email_to_field_how', 'readonly'))
			{
				case 'editable':
					$input = '<input type="text" name="list_email_to" id="list_email_to" value="' . $to . '" />';
					break;
				case 'hidden':
					$input = '<input name="list_email_to" id="list_email_to" value="' . $to . '" type="hidden" />';
					break;
				case 'readonly':
				default:
					$input = '<input type="text" name="list_email_to" id="list_email_to" value="' . $to . '" readonly="readonly" />';
					break;
			}

			return $input;
		}
		elseif ($toType == 'list')
		{
			return $this->formModel->getElementList('list_email_to');
		}
		elseif ($toType == 'table' || $toType == 'table_picklist')
		{
			$table = $params->get('emailtable_to_table_table');
			$tableEmail = $params->get('emailtable_to_table_email');
			$tableName = $params->get('emailtable_to_table_name');

			$toTableModel = JModelLegacy::getInstance('list', 'FabrikFEModel');
			$toTableModel->setId($table);
			$toDb = $toTableModel->getDb();

			$tableName  = FabrikString::safeColName($tableName);
			$tableEmail = FabrikString::safeColName($tableEmail);
			$emailTableTo_table       = $toDb->qn($toTableModel->getTable()->db_table_name);

			$query = $toDb->getQuery(true);
			$query->select($tableEmail . ' AS email, ' . $tableName . ' AS name')
				->from($emailTableTo_table)->order('name ASC');
			$toDb->setQuery($query);
			$results = $toDb->loadObjectList();

			if (empty($results))
			{
				return FText::_('PLG_LIST_EMAIL_TO_TABLE_NO_DATA');
			}

			$empty   = new stdClass;
			$attribs = 'class="fabrikinput inputbox input-medium" multiple="multiple" size="5"';

			if ($toType == 'table_picklist')
			{
				$html = '<div class="pull-left" style="margin:0 20px 20px 0">';
				$html .= JHTML::_('select.genericlist', $results, 'email_to_selectfrom[]', $attribs, 'email', 'name', '', 'email_to_selectfrom');
				$html .= '<br /><a href="#" class="btn btn-small" id="email_add"><i class="icon-plus"></i> ' . FText::_('COM_FABRIK_ADD') . ' &gt;&gt;</a>';
				$html .= '</div>';
				$html .= '<div class="span6">';
				$html .= JHTML::_('select.genericlist', $empty, 'list_email_to[]', $attribs, 'email', 'name', '', 'list_email_to');
				$html .= '<br /><a href="#" class="btn btn-small" id="email_remove">&lt;&lt; '
					. FText::_('COM_FABRIK_DELETE') . ' <i class="icon-delete"></i></a>';
				$html .= '</div>';
				$html .= '<div style="clear:both"></div>';
			}
			else
			{
				$attribs = 'class="fabrikinput inputbox input-large" multiple="multiple" size="5"';
				$html    = JHTML::_('select.genericlist', $results, 'list_email_to[]', $attribs, 'email', 'name', '', 'list_email_to');
			}

			return $html;
		}
	}

	/**
	 * Are attachments allowed?
	 *
	 * @return bool
	 */

	public function getAllowAttachment()
	{
		return $this->getParams()->get('emailtable_allow_attachment');
	}

	/**
	 * Get the show to field
	 *
	 * @return  string
	 */

	public function getShowToField()
	{
		$params      = $this->getParams();
		$var         = $params->get('emailtable_email_to_field_how', 'readonly');
		$toType      = $params->get('emailtable_to_type', 'list');

		// Can only hide To if it's the simple field type, as all others require user input
		return !($var == 'hidden' && $toType == 'field');
	}

	/**
	 * Get the show subject line
	 *
	 * @return  string
	 */

	public function getShowSubject()
	{
		return $this->getParams()->get('emailtable_hide_subject');
	}

	/**
	 * Get the subject line
	 *
	 * @return  string
	 */

	public function getSubject()
	{
		return $this->getParams()->get('email_subject');
	}

	/**
	 * Get the email message
	 *
	 * @return  string
	 */

	public function getMessage()
	{
		return $this->getParams()->get('email_message');
	}

	/**
	 * Get the selected records
	 *
	 * @param   string $key     key
	 * @param   bool   $allData data
	 *
	 * @return    array    rows
	 */

	public function getRecords($key = 'ids', $allData = false)
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		if ($key === 'recordids')
		{
			$ids = explode(',', $input->get($key, '', 'string'));
		}
		else
		{
			$ids = (array) $input->get($key, array(), 'array');
		}

		JArrayHelper::toInteger($ids);

		if (empty($ids))
		{
			throw new RuntimeException(FText::_('PLG_LIST_EMAIL_ERR_NO_RECORDS_SELECTED'), 400);
		}

		$params      = $this->getParams();
		$model       = $this->listModel;
		$pk          = $model->getTable()->db_primary_key;
		$pk2         = FabrikString::safeColNameToArrayKey($pk) . '_raw';
		$whereClause = '(' . $pk . ' IN (' . implode(',', $ids) . '))';
		$cond        = $params->get('emailtable_condition');

		if (trim($cond) !== '')
		{
			$whereClause .= ' AND (' . $cond . ')';
		}

		$model->setLimits(0, -1);
		$model->setPluginQueryWhere($this->buttonPrefix, $whereClause);
		$data = $model->getData();

		if ($allData)
		{
			return $data;
		}

		$return = array();

		foreach ($data as $gdata)
		{
			foreach ($gdata as $row)
			{
				$return[] = $row->$pk2;
			}
		}

		return $return;
	}

	/**
	 * Upload the attachments to the server
	 *
	 * @return  bool success/fail
	 */

	private function _upload()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.client.helper');
		$app   = JFactory::getApplication();
		$input = $app->input;
		JClientHelper::setCredentialsFromRequest('ftp');
		$files          = $input->files->get('attachment', array());
		$folder         = JPATH_ROOT . '/images/stories';
		$this->filepath = array();

		foreach ($files as $file)
		{
			$name = $file['name'];

			if ($name == '')
			{
				continue;
			}

			$path = $folder . '/' . strtolower($name);

			if (!JFile::upload($file['tmp_name'], $path))
			{
				JError::raiseWarning(100, FText::_('PLG_LIST_EMAIL_ERR_CANT_UPLOAD_FILE'));

				return false;
			}
			else
			{
				$this->filepath[] = $path;
			}
		}

		return true;
	}

	/**
	 * Get update value. Converts "now()" into current date
	 *
	 * @param   string $name        Parameter name
	 *
	 * @return  string
	 */
	protected function updateVal($name)
	{
		$params    = $this->getParams();
		$app       = JFactory::getApplication();
		$input     = $app->input;
		$user      = JFactory::getUser();
		$updateVal = $params->get($name);

		if ($updateVal === 'now()')
		{
			$updateVal = JFactory::getDate()->toSql();
		}

		if ($updateVal === '{subject}')
		{
			$updateVal = $input->get('subject', '', 'string');
		}

		if ($updateVal === '{$my->id}')
		{
			$updateVal = $user->get('id', 0, 'int');
		}

		return $updateVal;
	}

	/**
	 * Do email
	 *
	 * @return boolean
	 */

	public function doEmail()
	{
		$listModel = $this->listModel;
		$app       = JFactory::getApplication();
		$input     = $app->input;
		jimport('joomla.mail.helper');

		if (!$this->_upload())
		{
			return false;
		}

		$listModel->setId($input->getInt('id', 0));
		$w           = new FabrikWorker;
		$this->_type = 'table';
		$params      = $this->getParams();
		$mergeEmails = $params->get('emailtable_mergemessages', 0);
		$toHow     = $this->_toHow();
		$toType    = $this->_toType();
		$to        = $this->_to();
		$data      = $this->getRecords('recordids', true);
		$cc        = null;
		$bcc       = null;
		$sent      = 0;
		$notSent   = 0;
		$updated   = array();
		$mergedMsg = '';
		$firstRow  = array();

		foreach ($data as $group)
		{
			foreach ($group as $row)
			{
				if ($mergeEmails)
				{
					if (empty($firstRow))
					{
						// Used for placeholders in subject when merging mail
						$firstRow = $row;
					}

					$thisMsg = $this->_thisMsg($row);

					$mergedMsg .= $thisMsg;
					$updated[] = $row->__pk_val;
				}
				else
				{
					if ($toType == 'list')
					{
						$process = isset($row->$to);
						$mailTo  = $row->$to;
					}
					else
					{
						$process = true;
						$mailTo  = $w->parseMessageForPlaceHolder($to, $row);
					}

					if ($process)
					{
						$res     = false;
						$mailTos = explode(',', $mailTo);

						if ($toHow == 'single')
						{
							list($mailTos, $notSent) = $this->_parseMailTos($mailTos, $row, $notSent);
							$sent = count($mailTos);

							if ($sent > 0)
							{
								$res = $this->_send($row, $mailTos);
							}
						}
						else
						{
							foreach ($mailTos as $mailTo)
							{
								$mailTo = $w->parseMessageForPlaceholder($mailTo, $row);

								if (FabrikWorker::isEmail($mailTo))
								{
									$res = $this->_send($row, $mailTo);
									$res ? $sent++ : $notSent++;
								}
								else
								{
									$notSent++;
								}
							}
						}

						if ($res)
						{
							$updated[] = $row->__pk_val;
						}
					}
					else
					{
						$notSent++;
					}
				}
			}
		}

		if ($mergeEmails)
		{
			list($sent, $notSent) = $this->mailMerged($firstRow, $mergedMsg, $sent, $notSent);
		}

		$this->_updateRows($updated);

		// T3 blank tmpl doesn't seem to render messages when tmpl=component
		$app->enqueueMessage(JText::sprintf('%s emails sent', $sent));

		if ($notSent != 0)
		{
			$app->enqueueMessage(JText::sprintf('%s emails not sent', $notSent), 'notice');
		}
	}

	/**
	 * @param $mailTos
	 * @param $row
	 * @param $notSent
	 *
	 * @return array
	 */
	private function _parseMailTos($mailTos, $row, $notSent)
	{
		$w = new FabrikWorker;

		foreach ($mailTos as $toKey => $thisTo)
		{
			$thisTo = $w->parseMessageForPlaceholder($thisTo, $row);

			if (!FabrikWorker::isEmail($thisTo))
			{
				unset($mailTos[$toKey]);
				$notSent++;
			}
			else
			{
				$mailTos[$toKey] = $thisTo;
			}
		}

		if ($notSent > 0)
		{
			$mailTos = array_values($mailTos);
		}

		return array($mailTos, $notSent);
	}

	/**
	 * Are we using the old message style
	 *
	 * @return bool
	 */
	private function _oldStyle()
	{
		list($phpMsg, $message) = $this->_message();
		$oldStyle = false;

		if (empty($message) && !$phpMsg)
		{
			$oldStyle = true;
		}

		return $oldStyle;
	}

	/**
	 * Send the email
	 *
	 * @param $row
	 * @param $mailTo
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function _send($row, $mailTo)
	{
		$input         = JFactory::getApplication()->input;
		$subject       = $input->get('subject', '', 'string');
		$coverMessage  = nl2br($input->get('message', '', 'html'));
		$oldStyle      = $this->_oldStyle();
		$emailTemplate = $this->_emailTemplate();
		$w             = new FabrikWorker;
		$cc            = null;
		$bcc           = null;
		list($emailFrom, $fromName) = $this->_fromEmailName($row);
		list($replyEmail, $replyEmailName) = $this->_replyEmailName($row);
		$thisSubject = $w->parseMessageForPlaceholder($subject, $row);
		$thisMsg     = $coverMessage;
		list($phpMsg, $message) = $this->_message();

		if (!$oldStyle)
		{
			if ($phpMsg)
			{
				$thisMsg .= FabrikHelperHTML::getPHPTemplate($emailTemplate, $row, $this->listModel);
			}
			else
			{
				$thisMsg .= $message;
			}
		}

		$thisMsg = $w->parseMessageForPlaceholder($thisMsg, $row);
		$mail    = JFactory::getMailer();

		return $mail->sendMail($emailFrom, $fromName, $mailTo, $thisSubject, $thisMsg, 1, $cc, $bcc, $this->filepath,
			$replyEmail, $replyEmailName);
	}

	/**
	 * Get a csv list of emails to send the email to.
	 *
	 * @return array|mixed|string
	 * @throws Exception
	 */
	private function _to()
	{
		$input  = JFactory::getApplication()->input;
		$toType = $this->_toType();

		if ($toType == 'table' || $toType == 'table_picklist')
		{
			$to = $input->get('list_email_to', '', 'array');
		}
		else
		{
			$to = $input->get('list_email_to', '', 'string');
		}

		if ($toType == 'list')
		{
			$to = str_replace('.', '___', $to);
		}
		else
		{
			if (is_array($to))
			{
				// $$$ hugh - if using a table selection type, allow specifying a default in
				// the "emailtable_to" field.
				if ($toType != 'field')
				{
					$emailTableTo = $this->_emailTo();

					if (!empty($emailTableTo))
					{
						if (!in_array($emailTableTo, $to))
						{
							$to[] = $emailTableTo;
						}
					}
				}

				$to = implode(',', $to);
			}
		}

		return $to;
	}

	/**
	 * Get the email address(es) specified in the admin's 'Email to
	 * field
	 *
	 * @return string
	 */
	private function _emailTo()
	{
		$params = $this->getParams();
		$emailTo = $params->get('emailtable_to', '');

		return $emailTo;
	}

	/**
	 * Get the message from the designated template files.
	 *
	 * @return array ($phpMsg (bool), $message (string))
	 */
	private function _message()
	{
		$phpMsg          = false;
		$params          = $this->getParams();
		$contentTemplate = $params->get('emailtable_template_content', '');
		$content       = empty($contentTemplate) ? '' : FabrikHelperHTML::getContentTemplate($contentTemplate);
		$emailTemplate = $this->_emailTemplate();

		if (JFile::exists($emailTemplate))
		{
			if (JFile::getExt($emailTemplate) == 'php')
			{
				$message = '';
				$phpMsg  = true;
			}
			else
			{
				$message = FabrikHelperHTML::getTemplateFile($emailTemplate);
			}

			$message = str_replace('{content}', $content, $message);
		}
		else
		{
			$message = $contentTemplate != '' ? $content : '';
		}

		return array($phpMsg, $message);
	}

	/**
	 * Get the email template
	 *
	 * @return mixed|string
	 */
	private function _emailTemplate()
	{
		$params        = $this->getParams();
		$emailTemplate = $params->get('emailtable_template', '');

		if ($emailTemplate != "-1" && !empty($emailTemplate))
		{
			$emailTemplate = JPath::clean(JPATH_SITE . '/plugins/fabrik_list/email/tmpl/' . $emailTemplate);
		}

		return $emailTemplate;
	}

	/**
	 * Build the message text
	 *
	 * @param $row
	 *
	 * @return mixed|string
	 * @throws Exception
	 */
	private function _thisMsg($row)
	{
		list($phpMsg, $message) = $this->_message();
		$w             = new FabrikWorker;
		$input         = JFactory::getApplication()->input;
		$coverMessage  = nl2br($input->get('message', '', 'html'));
		$emailTemplate = $this->_emailTemplate();
		$oldStyle      = $this->_oldStyle();

		if ($oldStyle)
		{
			$thisMsg = $w->parseMessageForPlaceHolder($coverMessage, $row);
		}
		else
		{
			if ($phpMsg)
			{
				$thisMsg = FabrikHelperHTML::getPHPTemplate($emailTemplate, $row, $this->listModel);
			}
			else
			{
				$thisMsg = $w->parseMessageForPlaceHolder($message, $row);
			}
		}

		return $thisMsg;
	}

	/**
	 * Sent mail merge
	 *
	 * @param $firstRow
	 * @param $mergedMsg
	 * @param $sent
	 * @param $notSent
	 *
	 * @return array
	 * @throws Exception
	 */
	private function mailMerged($firstRow, $mergedMsg, $sent, $notSent)
	{
		$params = $this->getParams();
		$input  = JFactory::getApplication()->input;
		list($replyEmail, $replyEmailName) = $this->_replyEmailName($firstRow);
		list($emailFrom, $fromName) = $this->_fromEmailName($firstRow);
		$w      = new FabrikWorker;
		$toType = $toType = $this->_toType();
		// Arbitrarily use first row for placeholders
		$to           = $this->_emailTo();
		$oldStyle     = $this->_oldStyle();
		$toHow        = $this->_toHow();
		$mailTo       = $toType == 'list' ? $firstRow->$to : $to;
		$subject      = $input->get('subject', '', 'string');
		$thisTos      = explode(',', $w->parseMessageForPlaceHolder($mailTo, $firstRow));
		$thisSubject  = $w->parseMessageForPlaceHolder($subject, $firstRow);
		$preamble     = $params->get('emailtable_message_preamble', '');
		$postamble    = $params->get('emailtable_message_postamble', '');
		$mergedMsg    = $preamble . $mergedMsg . $postamble;
		$coverMessage = nl2br($input->get('message', '', 'html'));
		$cc           = null;
		$bcc          = null;

		if (!$oldStyle)
		{
			$mergedMsg = $coverMessage . $mergedMsg;
		}

		if ($toHow == 'single')
		{
			foreach ($thisTos as $toKey => $thisTo)
			{
				$thisTo = $w->parseMessageForPlaceholder($thisTo, $firstRow);

				if (!FabrikWorker::isEmail($thisTo))
				{
					unset($thisTos[$toKey]);
					$notSent++;
				}
				else
				{
					$mailTos[$toKey] = $thisTo;
				}
			}

			if ($notSent > 0)
			{
				$thisTos = array_values($thisTos);
			}

			$sent = count($thisTos);

			if ($sent > 0)
			{
				$mail = JFactory::getMailer();
				$res  = $mail->sendMail($emailFrom, $fromName, $thisTos, $thisSubject, $mergedMsg, true, $cc, $bcc, $this->filepath,
					$replyEmail, $replyEmailName);
			}
		}
		else
		{
			foreach ($thisTos as $thisTo)
			{
				if (FabrikWorker::isEmail($thisTo))
				{
					$mail = JFactory::getMailer();
					$res  = $mail->sendMail($emailFrom, $fromName, $thisTo, $thisSubject, $mergedMsg, true, $cc, $bcc, $this->filepath,
						$replyEmail, $replyEmailName);

					if ($res)
					{
						$sent++;
					}
					else
					{
						$notSent++;
					}
				}
				else
				{
					$notSent++;
				}
			}
		}

		return array($sent, $notSent);
	}

	/**
	 * Update a single row
	 *
	 * @param $updated
	 * @param $updateField
	 * @param $updateVal
	 */
	private function _updateSingleRow($updated, $updateField, $updateVal)
	{
		if (!empty($updateVal) && !empty($updated))
		{
			if (!empty($updateField))
			{
				$this->listModel->updateRows($updated, $updateField, $updateVal);
			}
			else
			{
				$this->listModel->updateRows($updated, '', '', $updateVal);
			}
		}
	}

	/**
	 * Update rows
	 *
	 * @param $updated
	 */
	private function _updateRows($updated)
	{
		$params      = $this->getParams();
		$updateField = $params->get('emailtable_update_field');
		$updateVal   = $this->updateVal('emailtable_update_value');

		$this->_updateSingleRow($updated, $updateField, $updateVal);

		// $$$ hugh - added second update field for Bea
		$updateField = $params->get('emailtable_update_field2');
		$updateVal   = $this->updateVal('emailtable_update_value2');

		$this->_updateSingleRow($updated, $updateField, $updateVal);
	}

	/**
	 * Get the reply email and name
	 *
	 * @param   array $data Data to use for placeholder replacement
	 *
	 * @since 3.3.2
	 *
	 * @return array ($replyEmail, $replyEmailName)
	 */
	private function _replyEmailName($data = array())
	{
		$w      = new FabrikWorker;
		$params = $this->getParams();
		$reply  = $w->parseMessageForPlaceholder($params->get('email_reply_to'), $data, false);
		@list($replyEmail, $replyEmailName) = explode(':', $reply, 2);

		if (empty($replyEmail))
		{
			$replyEmail = null;
		}

		if (empty($replyEmailName))
		{
			$replyEmailName = null;
		}

		return array($replyEmail, $replyEmailName);
	}

	/**
	 * Get the email to name and email address
	 *
	 * @param   array $data        Placeholder replacement data
	 *
	 * @since 3.3.2
	 *
	 * @return array ($emailFrom, $fromName)
	 */
	private function _fromEmailName($data = array())
	{
		$w        = new FabrikWorker;
		$params   = $this->getParams();
		$fromUser = $params->get('emailtable_from_user');

		if ($fromUser)
		{
			$my        = JFactory::getUser();
			$emailFrom = $my->get('email');
			$fromName  = $my->get('name');
		}
		else
		{
			$config = JFactory::getConfig();

			$emailFrom = $params->get('email_from', $config->get('mailfrom'));
			@list($emailFrom, $fromName) = explode(':', $w->parseMessageForPlaceholder($emailFrom, $data, false), 2);
		}

		return array($emailFrom, $fromName);
	}

	/**
	 * Build the WYSIWYG editor
	 *
	 * @return string
	 */
	public function getEditor()
	{
		$params = $this->getParams();
		$msg    = $this->getMessage();

		if ($params->get('wysiwyg', true))
		{
			$editor = JFactory::getEditor();

			return $editor->display('message', $msg, '100%', '200px', 75, 10, true, 'message');
		}
		else
		{
			return '<textarea name="message" style="width:100%" rows="10" cols="10">' . $msg . '</textarea>';
		}
	}
}
