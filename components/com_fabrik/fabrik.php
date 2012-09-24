<?php
/**
 * Access point to render Fabrik component
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.helper');
jimport('joomla.filesystem.file');

if (!defined('COM_FABRIK_FRONTEND'))
{
	JError::raiseError(400, JText::_('COM_FABRIK_SYSTEM_PLUGIN_NOT_ACTIVE'));
}

require_once JPATH_COMPONENT . '/controller.php';

$json = '{"option":"com_fabrik","task":"plugin.pluginAjax","formid":"22","g":"form","plugin":"subscriptions","method":"ipn","renderOrder":"2","mc_gross":"15.00","invoice":"505f48a3cbef23.51654962","protection_eligibility":"Eligible","address_status":"confirmed","payer_id":"MBW2ZTFZ5YFFL","address_street":"1 Main St","payment_date":"10:39:01 Sep 23, 2012 PDT","payment_status":"Completed","charset":"windows-1252","address_zip":"95131","first_name":"Rob","mc_fee":"0.79","address_country_code":"US","address_name":"Rob Clayburn","notify_version":"3.7","subscr_id":"I-T72LJ1SCJ9YC","custom":"22:6760","payer_status":"verified","business":"fr_1348421571_biz@pollen-8.co.uk","address_country":"United States","address_city":"San Jose","verify_sign":"AuLPCpBCF0LSxk6e1HGFcR67txtlAOk624SosG6NOm12aqWUP-sSaCCA","payer_email":"rob_1346249195_per@pollen-8.co.uk","txn_id":"6N219145TA530801X","payment_type":"instant","last_name":"Clayburn","address_state":"CA","receiver_email":"fr_1348421571_biz@pollen-8.co.uk","payment_fee":"","receiver_id":"NAJ6M2W79AK5G","txn_type":"subscr_payment","item_name":"fabrikar.com Monthly Standard  User: standard_rec_month (standard_rec_month)","mc_currency":"EUR","residence_country":"US","test_ipn":"1","transaction_subject":"fabrikar.com Monthly Standard  User: standard_rec_month (standard_rec_month)","payment_gross":"","ipn_track_id":"d1c06ff427611","Itemid":"77","view":"plugin","id":0}';
echo "<pre>";print_r(json_decode($json));echo "</pre>";
/**
 * Test for YQL & XML document type
 * use the format request value to check for document type
 */
$docs = array("yql", "xml");
foreach ($docs as $d)
{
	if (JRequest::getCmd("type") == $d)
	{
		// Get the class
		require_once JPATH_SITE . '/administrator/components/com_fabrik/classes/' . $d . 'document.php';

		// Replace the document
		$document = JFactory::getDocument();
		$docClass = 'JDocument' . JString::strtoupper($d);
		$document = new $docClass;
	}
}

JModelLegacy::addIncludePath(JPATH_COMPONENT . '/models');

// $$$ rob if you want to you can override any fabrik model by copying it from
// models/ to models/adaptors the copied file will overwrite (NOT extend) the original
JModelLegacy::addIncludePath(JPATH_COMPONENT . '/models/adaptors');

$controllerName = JRequest::getCmd('view');

// Check for a plugin controller

// Call a plugin controller via the url :
// &c=visualization.calendar

$isplugin = false;
$cName = JRequest::getCmd('controller');
if (JString::strpos($cName, '.') != false)
{
	list($type, $name) = explode('.', $cName);
	if ($type == 'visualization')
	{
		require_once JPATH_COMPONENT . '/controllers/visualization.php';
	}
	$path = JPATH_SITE . '/plugins/fabrik_' . $type . '/' . $name . '/controllers/' . $name . '.php';
	if (JFile::exists($path))
	{
		require_once $path;
		$isplugin = true;
		$controller = $type . $name;
	}
	else
	{
		$controller = '';
	}

}
else
{
	// Its not a plugin
	// map controller to view - load if exists

	/**
	 * $$$ rob was a simple $controller = view, which was giving an error when trying to save a popup
	 * form to the calendar viz
	 * May simply be the best idea to remove main contoller and have different controllers for each view
	 */

	// Hack for package
	if (JRequest::getCmd('view') == 'package' || JRequest::getCmd('view') == 'list')
	{
		$controller = JRequest::getCmd('view');
	}
	else
	{
		$controller = $controllerName;
	}

	$path = JPATH_COMPONENT . '/controllers/' . $controller . '.php';
	if (JFile::exists($path))
	{
		require_once $path;
	}
	else
	{
		$controller = '';
	}
}
/**
 * Create the controller if the task is in the form view.task then get
 * the specific controller for that class - otherwse use $controller to load
 * required controller class
 */
if (strpos(JRequest::getCmd('task'), '.') !== false)
{
	$controller = explode('.', JRequest::getCmd('task'));
	$controller = array_shift($controller);
	$classname = 'FabrikController' . JString::ucfirst($controller);
	$path = JPATH_COMPONENT . '/controllers/' . $controller . '.php';
	if (JFile::exists($path))
	{
		require_once $path;

		// Needed to process J content plugin (form)
		JRequest::setVar('view', $controller);
		$task = explode('.', JRequest::getCmd('task'));
		$task = array_pop($task);
		$controller = new $classname;
	}
	else
	{
		$controller = JControllerLegacy::getInstance('Fabrik');
	}
}
else
{
	$classname = 'FabrikController' . JString::ucfirst($controller);
	$controller = new $classname;
	$task = JRequest::getCmd('task');
}

if ($isplugin)
{
	// Add in plugin view
	$controller->addViewPath(JPATH_SITE . '/plugins/fabrik_' . $type . '/' . $name . '/views');

	// Add the model path
	$modelpaths = JModelLegacy::addIncludePath(JPATH_SITE . '/plugins/fabrik_' . $type . '/' . $name . '/models');
}
$app = JFactory::getApplication();
$package = JRequest::getVar('package', 'fabrik');
$app->setUserState('com_fabrik.package', $package);

// Web service testing
JLoader::import('webservice', JPATH_SITE . '/components/com_fabrik/models/');
if (JRequest::getVar('soap') == 1)
{
	$opts = array('driver' => 'soap', 'endpoint' => 'http://webservices.activetickets.com/members/ActiveTicketsMembersServices.asmx?WSDL',
		'credentials' => array('Clientname' => "SPLFenix", 'LanguageCode' => "nl"));

	$service = FabrikWebService::getInstance($opts);

	$params = $opts['credentials'];
	$params['From'] = JFactory::getDate()->toISO8601();
	$params['To'] = JFactory::getDate('next year')->toISO8601();
	$params['IncludePrices'] = true;
	$params['MemberId'] = 14;
	$method = JRequest::getVar($method, 'GetProgramList');
	$program = $service->get($method, $params, '//ProgramList/Program', null);

	$listModel = JModelLegacy::getInstance('List', 'FabrikFEModel');
	$listModel->setId(7);
	$service->storeLocally($listModel, $program);

}

if (JRequest::getVar('yql') == 1)
{
	$opts = array('driver' => 'yql', 'endpoint' => 'https://query.yahooapis.com/v1/public/yql');

	$service = FabrikWebService::getInstance($opts);
	$query = "select * from upcoming.events where location='London'";
	$program = $service->get($query, array(), 'event', null);
}
// End web service testing

$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();
