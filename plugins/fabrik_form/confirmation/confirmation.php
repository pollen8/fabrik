<?php

/**
 * After submission, shows a page where the user can confirm the data they are posting
 * @package Joomla
 * @subpackage Fabrik
 * @author Rob Clayburn
 * @copyright (C) Pollen 8 Design Ltd
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'plugin-form.php');

class plgFabrik_FormConfirmation extends plgFabrik_Form {

	var $_counter = null;

	var $runAway = false;

	/**
	 * process the plugin, called when form is submitted
	 *
	 * @param object $params
	 * @param object form
	 */

	function shouldProcess(&$params)
	{
		$condition = $params->get('confirmation_condition');
		if ($condition == '') {
			return true;
		}
		$w = new FabrikWorker();
		$condition = trim($w->parseMessageForPlaceHolder($condition, $this->data));
		$res = @eval($condition);
		if (is_null($res)) {
			return true;
		}
		return $res;
	}

	/**
	 * (non-PHPdoc)
	 * @see FabrikModelPlugin::runAway()
	 */

	public function runAway($method)
	{
		if ($method == 'onBeforeStore') {
			return $this->runAway;
		}
		return false;
	}

	/**
	 * @since 2.0.4
	 * remove session flags which state that the form should be loaded
	 * from the session
	 * @param int form id
	 */

	protected function clearSession($id)
	{
		$session = JFactory::getSession();
		$session->clear('com_fabrik.form.'.$id.'.session.on');
		$session->clear('com_fabrik.form.'.$id.'.session.hash');
	}
	/**
	 * Before the record is stored, this plugin will see if it should process
	 * and if so store the form data in the session.
	 * @param object $params
	 * @param object $formModel
	 * @return bool
	 */

	function onBeforeStore(&$params, &$formModel)
	{
		$this->runAway = false;
		if (!$this->shouldProcess($params)) {
			$this->clearSession($formModel->getId());
			return true;
		}
		if (JRequest::getVar('fabrik_confirmation') == 2) {
			//if we were already on the confirmation page
			// return and set to 2 to ignore?
			// $$$ hugh - I don't think it really matters,
			// 'cos getBottomContent isn't going to be called again
			JRequest::setVar('fabrik_confirmation', 1);
			return true;
		}
		// $$$ set flag to stop subsequent onBeforeStore plug-ins from running
		$this->runAway = true;

		// Initialize some variables
		$form = $formModel->getForm();
		//save the posted form data to the form session, for retrival later
 		$sessionModel = JModel::getInstance('Formsession', 'FabrikModel');
		$sessionModel->setFormId($formModel->get('id'));
		$sessionModel->setRowId(0);
		$sessionModel->savePage($formModel);

		// tell the form model that it's data is loaded from the session
		$session = JFactory::getSession();
		$session->set('com_fabrik.form.'.$formModel->getId().'.session.on', true);
		$session->set('com_fabrik.form.'.$formModel->getId().'.session.hash', $sessionModel->getHash());

		//set an error so we can reshow the same form for confirmation purposes
		$formModel->_arErrors['confirmation_required'] = true;
		$form->error = JText::_('PLG_FORM_CONFIRMATION_PLEASE_CONFIRM_YOUR_DETAILS');
		$formModel->_editable = false;

		//clear out unwanted buttons
		$formParams = $formModel->getParams();
		$formParams->set('reset_button', 0);
		$formParams->set('goback_button', 0);

		//the user has posted the form we need to make a note of this
		//for our getBottomContent() function
		JRequest::setVar('fabrik_confirmation', 1);

		//set the element access to read only??
		$groups = $formModel->getGroupsHiarachy();
		foreach ($groups as $groupModel) {
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel) {
				$elementModel->getElement()->access = 26;
			}
		}
		return false;
	}

	/**
	 * set up the html to be injected into the bottom of the form
	 *
	 * @param object $params (no repeat counter stuff needed here as the plugin manager
	 * which calls this function has already done the work for you
	 * @param object form model
	 */

	function getBottomContent(&$params, $formModel)
	{
		//if we have already processed the form
		$this->html = '';
		if (JRequest::getVar('fabrik_confirmation') == 1) {

			//unset this flag
			JRequest::setVar('fabrik_confirmation', 2);
			$post = JRequest::get('post', 4);
			//load in the posted values as hidden fields so that if we
			//return to the form to edit it it will populate with our data
			foreach ($post as $key => $val) {
				// form data is stored in session
				$noneraw = substr($key, 0, strlen($key) - 4);
				if ($key == 'join' || $key == 'fabrik_vars' || $formModel->hasElement($key) || $formModel->hasElement($noneraw)) {
					continue;
				}
				// $$$ rob include both raw and non-raw keys (non raw for radios etc, _raw for db joins)
				if (is_array($val)) {
					foreach ($val as $val2) {
						if (!FabrikWorker::isReserved($key)) {
							$fields[] = '<input type="hidden" name="'.str_replace('_raw','',$key).'[]" value="'.urlencode($val2).'" />';
							$fields[] = '<input type="hidden" name="'.$key.'[]" value="'.urlencode($val2).'" />';
						}
					}
				} else {
					if (!FabrikWorker::isReserved($key)) {
						$fields[] = '<input type="hidden" name="'.str_replace('_raw','',$key).'" value="'.urlencode($val).'" />';
						$fields[] = '<input type="hidden" name="'.$key.'" value="'.urlencode($val).'" />';
					}
				}
			}

			//add in a view field as the form doesn't normally contain one
			$fields[] = '<input type="hidden" name="view" value="form" />';

			//add in a button to allow you to go back to the form and edit your data
			$fields[]= "<input type=\"button\" id=\"fabrik_redoconfirmation\" class=\"button\" value=\"" . JText::_('PLG_FORM_CONFIRMATION_RE_EDIT') . "\" />";

			FabrikHelperHTML::addScriptDeclaration(
				"head.ready(function() {".
				"$('fabrik_redoconfirmation').addEvent('click', function(e) {".
			//	unset the task otherwise we will submit the form to be processed.
				"  this.form.task.value = '';".
				"  this.form.submit();".
				"	});".
				"});"
				);
				$this->html = implode("\n", $fields);
		}
	}

	/**
	 * inject custom html into the bottom of the form
	 *
	 * @param int plugin counter
	 * @return string html
	 */

	function getBottomContent_result($c)
	{
		return $this->html;
	}

}
?>