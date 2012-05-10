<?php

/**
 * Creates a thread in kunena forum
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

class plgFabrik_FormKunena extends plgFabrik_Form {

	var $vb_forum_field = '';
	var $vb_path = '';
	var $vb_globals = '';

	/**
	 * process the plugin, called when form is submitted
	 *
	 * @param object $params
	 * @param object form
	 */

	function onAfterProcess(&$params, &$formModel)
	{		
		//return;		
		jimport('joomla.filesystem.file');
		//$files[]  = COM_FABRIK_BASE.'administrator'.DS.'components'.DS.'com_kunena'.DS.'language'.DS.'kunena.english.php';
		$files[] = COM_FABRIK_BASE.'components'.DS.'com_kunena'.DS.'class.kunena.php';
		$define = COM_FABRIK_BASE.'components'.DS.'com_kunena'.DS.'lib'.DS.'kunena.defines.php';
		$files[]  = COM_FABRIK_BASE.'components'.DS.'com_kunena'.DS.'lib'.DS.'kunena.defines.php';
		//$files[]  = COM_FABRIK_BASE.'components'.DS.'com_kunena'.DS.'lib'.DS.'kunena.session.class.php';
		$files[]  = COM_FABRIK_BASE.'components'.DS.'com_kunena'.DS.'lib'.DS.'kunena.link.class.php';
		$files[]  = COM_FABRIK_BASE.'components'.DS.'com_kunena'.DS.'lib'.DS.'kunena.link.class.php';
		//$files[]  = COM_FABRIK_BASE.'components'.DS.'com_kunena'.DS.'template'.DS.'default'.DS.'smile.class.php';
		$files[]  = COM_FABRIK_BASE.'components'.DS.'com_kunena'.DS.'lib'.DS.'kunena.smile.class.php';
		if (!JFile::exists($define)) {
			return JError::raiseError(500, 'could not find the Kunena component');
		}
		require_once($define);
		foreach ($files as $file) {
			require_once($file);
		}
		
		//if (JFile::exists(KUNENA_ABSTMPLTPATH . '/post.php')) {
		//	$postfile = KUNENA_ABSTMPLTPATH . '/post.php';
		//}
		if (JFile::exists(KUNENA_PATH_FUNCS . '/post.php')) {
			$postfile = KUNENA_PATH_FUNCS . '/post.php';
		}
		else {
			$postfile = KUNENA_PATH_TEMPLATE_DEFAULT .DS. 'post.php';
		}
		$w = new FabrikWorker();
		
		//$fbSession = CKunenaSession::getInstance();
		//dont need this, session is loaded in CKunenaPost


		$catid =$params->get('kunena_category', 0);
		$parentid = 0;
		$action = 'post';
		//added action in request
		JRequest::setVar('action',$action);
		$func = 'post';
		$contentURL = 'empty';
		JRequest::setVar('catid', $catid);
		$msg = $w->parseMessageForPlaceHolder($params->get('kunena_content'), $formModel->_fullFormData);
		$subject = $params->get('kunena_title');
		JRequest::SetVar('message', $msg);
		$subject = $w->parseMessageForPlaceHolder($subject, $formModel->_fullFormData);
		//added subject in request
		JRequest::SetVar('subject', $subject);
		$origId = JRequest::getVar('id');
		JRequest::setVar('id', 0);
		/*
		ob_start();
		include ($postfile);
		ob_end_clean();
		*/
		ob_start();
		include ($postfile);
		$mypost = new CKunenaPost();
		$mypost->display(); //public CKunenaPost::display() will call protected method CKunenaPost::post() if JRequest action is 'post'
		ob_end_clean();
		JRequest::setVar('id', $origId);
	}

}
?>