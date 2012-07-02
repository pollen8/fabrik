<?php

/**
 * redirects the browser to paypal to perform payment
 * @package     Joomla
 * @subpackage  Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND . '/models/plugin-form.php');

class plgFabrik_FormPaypal extends plgFabrik_Form {

	var $user_agent = '';

	/**
	 * process the plugin, called at end of form submission
	 * @param	object	$params
	 * @param	object	form model
	 */

	public function onAfterProcess($params, &$formModel)
	{
		$app = JFactory::getApplication();
		$data = $formModel->_fullFormData;
		$this->data = $data;
		if (!$this->shouldProcess('paypal_conditon')) {
			return true;
		}
		$this->formModel = $formModel;
		$emailData = $this->getEmailData();
		$w = new FabrikWorker;

		$user = JFactory::getUser();
		$userid = $user->get('id');

		$ipn = $this->getIPNHandler($params);
		if ($ipn !== false) {
			if (method_exists($ipn, 'createInvoice')) {
					$ipn->createInvoice();
			}
		}
		$paypal_testmode = $params->get('paypal_testmode', false);
		$url = $paypal_testmode == 1 ? 'https://www.sandbox.paypal.com/us/cgi-bin/webscr?' : 'https://www.paypal.com/cgi-bin/webscr?';

		$opts = array();
		$opts['cmd'] = $params->get('paypal_cmd', "_xclick");

		$email = $params->get('paypal_accountemail');
		if (trim($email) == '') {
			$email = $emailData[FabrikString::safeColNameToArrayKey($params->get('paypal_accountemail_element') )];
			if (is_array($email)) {
				$email = array_shift($email);
			}
		}
		$opts['business'] = "$email";

		$amount = $params->get('paypal_cost');
		$amount = $w->parseMessageForPlaceHolder($amount, $data);
		//@TODO Hugh/Rob check $$$tom: Adding eval option on cost field
		// Useful if you use a cart system which will calculate on total shipping or tax fee and apply it. You can return it in the Cost field.
		if ($params->get('paypal_cost_eval', 0) == 1) {
			$amount = @eval($amount);
		}
		if (trim($amount) == '') {
			$amount = JArrayHelper::getValue($emailData, FabrikString::safeColNameToArrayKey($params->get('paypal_cost_element')));
			if (is_array($amount)) {
				$amount = array_shift($amount);
			}
		}
		$opts['amount'] = "$amount";

		//$$$tom added Shipping Cost params
		$shipping_amount = $params->get('paypal_shipping_cost');
		if ($params->get('paypal_shipping_cost_eval', 0) == 1) {
			$shipping_amount = @eval ( $shipping_amount);
		}
		if (trim($shipping_amount) == '') {
			$shipping_amount = JArrayHelper::getValue($emailData, FabrikString::safeColNameToArrayKey($params->get('paypal_shipping_cost_element')));
			if (is_array($shipping_amount)) {
				$shipping_amount = array_shift($shipping_amount);
			}
		}
		$opts['shipping'] = "$shipping_amount";

		$item = $params->get('paypal_item');
		if ($params->get('paypal_item_eval', 0) == 1) {
			$item = @eval($item);
			$item_raw = $item;
		}
		if (trim($item) == '') {
			$item_raw = JArrayHelper::getValue($emailData, FabrikString::safeColNameToArrayKey($params->get('paypal_item_element') . '_raw'));
			$item = $emailData[FabrikString::safeColNameToArrayKey($params->get('paypal_item_element'))];
			if (is_array($item)) {
				$item = array_shift($item);
			}
		}

		$opts['item_name'] = "$item";

		//$$$ rob add in subscription variables
		if ($opts['cmd'] === '_xclick-subscriptions') {
			$subTable = JModel::getInstance('List', 'FabrikFEModel');
			$subTable->setId((int) $params->get('paypal_subs_table'));

			$idEl = FabrikString::safeColName($params->get('paypal_subs_id', ''));
			$durationEl = FabrikString::safeColName($params->get('paypal_subs_duration', ''));
			$durationPerEl = FabrikString::safeColName($params->get('paypal_subs_duration_period', ''));
			$name = $params->get('paypal_subs_name', '');


			$subDb = $subTable->getDb();
			$subDb->setQuery("SELECT *, $durationEl AS p3, $durationPerEl AS t3, " . $subDb->Quote($item_raw) . " AS item_number  FROM ".$subTable->getTable()->db_table_name." WHERE $idEl = " . $subDb->Quote($item_raw));
			$sub = $subDb->loadObject();
			if (is_object($sub)) {
				$opts['p3'] = $sub->p3;
				$opts['t3'] = $sub->t3;
				$opts['a3'] = $amount;
				//$opts['src'] = 1;
				$opts['no_note'] = 1;
				$opts['custom'] = '';

				$tmp = array_merge(JRequest::get('data'), JArrayHelper::fromObject($sub));
				$opts['item_name'] = $w->parseMessageForPlaceHolder($name, $tmp);//'http://fabrikar.com/ '.$sub->item_name.' - User: subtest26012010 (subtest26012010)';
				$opts['invoice'] = $w->parseMessageForPlaceHolder($params->get('paypal_subs_invoice'), $tmp, false);
				if ($opts['invoice'] == '') {
					$opts['invoice'] = uniqid('', true);
				}
				$opts['src'] = $w->parseMessageForPlaceHolder($params->get('paypal_subs_recurring'), $tmp);
				$amount = $opts['amount'];
				unset($opts['amount']);
			} else {
				JError::raiseError(500, 'Could not determine subscription period, please check your settings');
			}
		}
		// $$$ rob 03/02/2011
		// check if we have a gateway subscription switch set up. This is for sites where
		// you can toggle between a subscription or a single payment. E.g. fabrikar com
		// if 'paypal_subscription_switch' is blank then use the $opts['cmd'] setting
		// if not empty it should be some eval'd PHP which needs to return true for the payment
		// to be treated as a subscription
		// We want to do this so that single payments can make use of Paypals option to pay via credit card
		// without a paypal account (subscriptions require a Paypal account)
		// We do this after the subscription code has been run as this code is still needed to look up the correct item_name

		$subSwitch = $params->get('paypal_subscription_switch');
		if (trim($subSwitch) !== '') {
			$subSwitch = $w->parseMessageForPlaceHolder($subSwitch);
			$isSub = @eval($subSwitch);
			if (!$isSub) {
				//reset the amount which was unset during subscription code
				$opts['amount'] = $amount;
				$opts['cmd'] = '_xclick';
				//unset any subscription options we may have set
				unset($opts['p3']);
				unset($opts['t3']);
				unset($opts['a3']);
				unset($opts['no_note']);
				//$opts['src'] = 0;
			}
		}
		//@TODO Hugh/Rob check $$$tom: Adding shipping options
		// Currently the admin select a user element on the form to compare it to the user id on the custom user table
		// Should we just make it to get the current user ID and use that?
		// $shipping_userid = $data[FabrikString::safeColNameToArrayKey($params->get('paypal_shipping_userelement') )];
		// if (is_array($shipping_userid)) {
		//	$shipping_userid = array_shift($shipping_userid);
		//}
		$shipping_userid = $userid;
		if ($shipping_userid > 0) {
			$shipping_select = array();

			$db = FabrikWorker::getDbo();
			//$$$tom Surely there's a better Fabrik way of getting the table name...
			$db->setQuery("SELECT db_table_name
						  FROM #__{package}_tables
						  WHERE id = ".$params->get('paypal_shippingdata_table')."
						  LIMIT 1");
			$shipping_table = $db->loadResult();

			if ($params->get('paypal_shippingdata_firstname')) {
				$shipping_first_name = FabrikString::shortColName($params->get('paypal_shippingdata_firstname'));
				$shipping_select['first_name'] = $shipping_first_name;
			}
			if ($params->get('paypal_shippingdata_lastname')) {
				$shipping_last_name = FabrikString::shortColName($params->get('paypal_shippingdata_lastname'));
				$shipping_select['last_name'] = $shipping_last_name;
			}
			if ($params->get('paypal_shippingdata_address1')) {
				$shipping_address1 = FabrikString::shortColName($params->get('paypal_shippingdata_address1'));
				$shipping_select['address1'] = $shipping_address1;
			}
			if ($params->get('paypal_shippingdata_address2')) {
				$shipping_address2 = FabrikString::shortColName($params->get('paypal_shippingdata_address2'));
				$shipping_select['address2'] = $shipping_address2;
			}
			if ($params->get('paypal_shippingdata_zip')) {
				$shipping_zip = FabrikString::shortColName($params->get('paypal_shippingdata_zip'));
				$shipping_select['zip'] = $shipping_zip;
			}
			if ($params->get('paypal_shippingdata_state')) {
				$shipping_state = FabrikString::shortColName($params->get('paypal_shippingdata_state'));
				$shipping_select['state'] = $shipping_state;
			}
			if ($params->get('paypal_shippingdata_city')) {
				$shipping_city = FabrikString::shortColName($params->get('paypal_shippingdata_city'));
				$shipping_select['city'] = $shipping_city;
			}
			if ($params->get('paypal_shippingdata_country')) {
				$shipping_country = FabrikString::shortColName($params->get('paypal_shippingdata_country'));
				$shipping_select['country'] = $shipping_country;
			}

			$db->setQuery("SELECT ".implode(',', $shipping_select)."
							FROM $shipping_table
							WHERE ".FabrikString::shortColName($params->get('paypal_shippingdata_id'))." = ".$db->quote($shipping_userid)."
							LIMIT 1");
			$user_shippingdata = $db->loadObject();

			foreach ($shipping_select as $opt => $val) {
				//$$$tom Since we test on the current userid, it always adds the &name=&street=....
				//even if those vars are empty...
				if ($val) {
					$opts[$opt] = $user_shippingdata->$val;
				}
			}
		}
		if ($params->get('paypal_shipping_address_override', 0)) {
			$opts['address_override'] = 1;
		}

		$paypal_currency_code = $params->get('paypal_currencycode', 'USD');
		$paypal_currency_code = $w->parseMessageForPlaceHolder($paypal_currency_code, $data);
		$opts['currency_code'] = $paypal_currency_code;

		$paypal_test_site = $params->get('paypal_test_site', '');
		$paypal_test_site = rtrim($paypal_test_site, '/');
		if ($paypal_testmode == 1 && !empty($paypal_test_site)) {
			$ppurl = $paypal_test_site . '/index.php?option=com_fabrik&c=plugin&task=plugin.pluginAjax&formid='.$formModel->get('id').'&g=form&plugin=paypal&method=ipn';
		}
		else {
			$ppurl = COM_FABRIK_LIVESITE.'/index.php?option=com_fabrik&c=plugin&task=plugin.pluginAjax&formid='.$formModel->get('id').'&g=form&plugin=paypal&method=ipn';
		}
		$paypal_test_site_qs = $params->get('paypal_test_site_qs', '');
		if ($paypal_testmode == 1 && !empty($paypal_test_site_qs)) {
			$ppurl .= $paypal_test_site_qs;
		}

		$ppurl .= '&renderOrder=' . $this->renderOrder;


		$ppurl = urlencode($ppurl);
		$opts['notify_url'] = "$ppurl";

		$paypal_return_url = $params->get('paypal_return_url', '');
		$paypal_return_url = $w->parseMessageForPlaceHolder($paypal_return_url, $data);
		if ($paypal_testmode == 1 && !empty($paypal_return_url)) {
			if (preg_match('#^http:\/\/#', $paypal_return_url)) {
				$opts['return'] = $paypal_return_url;
			}
			else {
				if (!empty($paypal_test_site)) {
					$opts['return'] = $paypal_test_site . '/' . $paypal_return_url;
				}
				else {
					$opts['return'] = COM_FABRIK_LIVESITE . '/' . $paypal_return_url;
				}
			}
			if (!empty($paypal_test_site_qs)) {
				$opts['return'] .= $paypal_test_site_qs;
			}
		}
		elseif (!empty($paypal_return_url)) {
			if (preg_match('#^http:\/\/#', $paypal_return_url)) {
				$opts['return'] = $paypal_return_url;
			}
			else {
				$opts['return'] = COM_FABRIK_LIVESITE . '/' . $paypal_return_url;
			}
		}
		else {
			// using default thanks() method so don't forget to add renderOrder
			if ($paypal_testmode == '1' && !empty($paypal_test_site)) {
				$opts['return'] = $paypal_test_site .'/index.php?option=com_fabrik&task=plugin.pluginAjax&formid='.$formModel->get('id').'&g=form&plugin=paypal&method=thanks&rowid=' . $data['rowid']. '&renderOrder=' . $this->renderOrder;

			}
			else {
				$opts['return'] = COM_FABRIK_LIVESITE.'/index.php?option=com_fabrik&task=plugin.pluginAjax&formid='.$formModel->get('id').'&g=form&plugin=paypal&method=thanks&rowid=' . $data['rowid']. '&renderOrder=' . $this->renderOrder;
			}
		}
		$opts['return'] = urlencode($opts['return']);

		$ipn_value = $params->get('paypal_ipn_value', '');
		$ipn_value = $w->parseMessageForPlaceHolder($ipn_value, $data);
		$ipn_value = str_replace(':',';',$ipn_value); // extra :'s will break parsing during IPN notify phase

		// $$$ hugh - thinking about putting in a call to a generic method in custom script
		// here and passing it a reference to $opts.

		$opts['custom'] = $data['formid'] . ':' . $data['rowid'] . ':' . $ipn_value;
		$qs = array();
		foreach ($opts as $k=>$v) {
			$qs[] = "$k=$v";
		}
		$url .= implode('&', $qs);

		// $$$ rob 04/02/2011 no longer doing redirect from ANY plugin EXCEPT the redirect plugin
		// - instead a session var is set (com_fabrik.form.X.redirect.url)
		// as the preferred redirect url

		$session = JFactory::getSession();
		$context = $formModel->getRedirectContext();

		// $$$ hugh - fixing issue with new redirect, which now needs to be an array.
		// Not sure if we need to preserve existing session data, or just create a new surl array,
		// to force ONLY recirect to PayPal?
		$surl = (array) $session->get($context.'url', array());
		$surl[$this->renderOrder] = $url;
		$session->set($context.'url', $surl);

		/// log the info
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fabrik/tables');
		$log = FabTable::getInstance('log', 'FabrikTable');
		$log->message_type = 'fabrik.paypal.onAfterProcess';
		$msg = new stdClass;
		$msg->opt = $opts;
		$msg->data = $data;
		$log->message = json_encode($msg);
		$log->store();
		return true;
	}

	function onThanks()
	{
		// @TODO - really need to work out how to get the plugin params at this point,
		// so we don't have to pass the teg_msg around as a QS arg between us and PayPal,
		// and just grab it from params directly.
		$formid = JRequest::getInt('formid');
		$rowid = JRequest::getInt('rowid');
		JModel::addIncludePath( COM_FABRIK_FRONTEND . '/models');
		$formModel = JModel::getInstance('Form', 'FabrikFEModel');
		$formModel->setId($formid);
		$params = $formModel->getParams();
		$ret_msg = (array) $params->get('paypal_return_msg', array());
		$ret_msg = $ret_msg[JRequest::getInt('renderOrder')];
		if ($ret_msg) {
			$w = new FabrikWorker;
			$listModel = $formModel->getlistModel();
			$row = $listModel->getRow($rowid);
			$ret_msg = $w->parseMessageForPlaceHolder($ret_msg, $row);
			if (JString::stristr($ret_msg,'[show_all]')) {
				$all_data = array();
				foreach ($_REQUEST as $key => $val) {
					$all_data[] = "$key: $val";
				}
				JRequest::setVar('show_all', implode('<br />',$all_data));
			}
			$ret_msg = str_replace('[','{',$ret_msg);
			$ret_msg = str_replace(']','}',$ret_msg);
			$ret_msg = $w->parseMessageForPlaceHolder($ret_msg, $_REQUEST);
			echo $ret_msg;
		}
		else {
			echo JText::_("thanks");
		}
	}


	/**
	 * called from paypal at the end of the transaction
	 */

	function onIpn()
	{
		$config = JFactory::getConfig();
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fabrik/tables');
		$log = FabTable::getInstance('log', 'FabrikTable');
		$log->referring_url = $_SERVER['REQUEST_URI'];
		$log->message_type = 'fabrik.ipn.start';
		$log->message = json_encode($_REQUEST);
		$log->store();

		//lets try to load in the custom returned value so we can load up the form and its parameters
		$custom = JRequest::getVar('custom');
		list($formid, $rowid, $ipn_value) = explode(":", $custom);

		//pretty sure they are added but double add
		JModel::addIncludePath(COM_FABRIK_FRONTEND . '/models');
		$formModel = JModel::getInstance('Form', 'FabrikFEModel');
		$formModel->setId($formid);
		$listModel = $formModel->getlistModel();
		$params = $formModel->getParams();
		$table = $listModel->getTable();
		$db = $listModel->getDb();

		// $$$ hugh
		// @TODO shortColName won't handle joined data, need to fix this to use safeColName
		// (don't forget to change quoteName stuff later on as well)
		$renderOrder = JRequest::getInt('renderOrder');
		$ipn_txn_field = (array) $params->get('paypal_ipn_txn_id_element', array());
		$ipn_txn_field = FabrikString::shortColName($ipn_txn_field[$renderOrder]);

		$ipn_payment_field = (array) $params->get('paypal_ipn_payment_element', array());
		$ipn_payment_field = FabrikString::shortColName($ipn_payment_field[$renderOrder]);

		$ipn_field = (array) $params->get('paypal_ipn_element', array());
		$ipn_field = FabrikString::shortColName($ipn_field[$renderOrder]);

		$ipn_status_field = (array) $params->get('paypal_ipn_status_element', array());
		$ipn_status_field = FabrikString::shortColName($ipn_status_field[$renderOrder]);

		$ipn_address_field = (array) $params->get('paypal_ipn_address_element', array());
		$ipn_address_field = FabrikString::shortColName($ipn_address_field[$renderOrder]);


		$w = new FabrikWorker;
		$ipn_value = str_replace('[','{',$ipn_value);
		$ipn_value = str_replace(']','}',$ipn_value);
		$ipn_value = $w->parseMessageForPlaceHolder($ipn_value, $_POST);

		$email_from = $admin_email = $config->get('mailfrom');

		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';

		foreach ($_POST as $key => $value)
		{
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}

		// post back to PayPal system to validate
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Host: www.paypal.com:443\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . JString::strlen($req) . "\r\n\r\n";

		if ($_POST['test_ipn'] == 1) {
			$paypalurl = 'ssl://www.sandbox.paypal.com';
		} else {
			$paypalurl = 'ssl://www.paypal.com';
		}

		// assign posted variables to local variables
		$item_name 			= JRequest::getVar('item_name');
		$item_number 		= JRequest::getVar('item_number');
		$payment_status 	= JRequest::getVar('payment_status');
		$payment_amount 	= JRequest::getVar('mc_gross');
		$payment_currency 	= JRequest::getVar('mc_currency');
		$txn_id 			= JRequest::getVar('txn_id');
		$txn_type			= JRequest::getVar('txn_type');
		$receiver_email 	= JRequest::getVar('receiver_email');
		$payer_email 		= JRequest::getVar('payer_email');
		$buyer_address		= JRequest::getVar('address_status')
		.' - '.JRequest::getVar('address_street')
		.' '.JRequest::getVar('address_zip')
		.' '.JRequest::getVar('address_state')
		.' '.JRequest::getVar('address_city')
		.' '.JRequest::getVar('address_country_code');

		$status = 'ok';
		$err_msg = '';



		if (empty($formid) || empty($rowid)) {
			$status = 'form.paypal.ipnfailure.custom_error';
			$err_msg = "formid or rowid empty in custom: $custom";
		}
		else {
			//@TODO implement a curl alternative as fsockopen is not always available
			$fp = fsockopen ($paypalurl, 443, $errno, $errstr, 30);
			if (!$fp) {
				$status = 'form.paypal.ipnfailure.fsock_error';
				$err_msg = "fsock error: $errno;$errstr";
			} else {
				fputs ($fp, $header . $req);
				while (!feof($fp)) {
					$res = fgets ($fp, 1024);
					// paypal steps (from their docs):
					// check the payment_status is Completed
					// check that txn_id has not been previously processed
					// check that receiver_email is your Primary PayPal email
					// check that payment_amount/payment_currency are correct
					// process payment
					if (JString::strcmp ($res, "VERIFIED") == 0) {

						//$$tom This block Paypal from updating the IPN field if the payment status evolves (e.g. from Pending to Completed)
						// $$$ hugh - added check of status, so only barf if there is a status field, and it is Completed for this txn_id
						if (!empty($ipn_txn_field) && !empty($ipn_status_field)) {
							$db->setQuery("SELECT $ipn_status_field FROM $table->db_table_name WHERE ".$db->quoteName($ipn_txn_field)." = ".$db->quote($txn_id));
							$txn_result = $db->loadResult();
							if (!empty($txn_result)) {
								if ($txn_result == 'Completed') {
									if ($payment_status != 'Reversed' && $payment_status != 'Refunded') {
										$status = 'form.paypal.ipnfailure.txn_seen';
										$err_msg = "transaction id already seen as Completed, new payment status makes no sense: $txn_id, $payment_status";
									}
								}
								elseif ($txn_result == 'Reversed') {
									if ($payment_status != 'Canceled_Reversal') {
										$status = 'form.paypal.ipnfailure.txn_seen';
										$err_msg = "transaction id already seen as Reversed, new payment status makes no sense: $txn_id, $payment_status";
									}
								}
							}
						}
						if ($status == 'ok') {
							$set_list = array();
							if (!empty($ipn_field)) {
								if (empty($ipn_value)) {
									$ipn_value = $txn_id;
								}
								$set_list[$ipn_field] = $ipn_value;
							}
							if (!empty($ipn_txn_field)) {
								$set_list[$ipn_txn_field] = $txn_id;
							}
							if (!empty($ipn_payment_field)) {
								$set_list[$ipn_payment_field] = $payment_amount;
							}
							if (!empty($ipn_status_field)) {
								$set_list[$ipn_status_field] = $payment_status;
							}
							if (!empty($ipn_address_field)) {
								$set_list[$ipn_address_field] = $buyer_address;
							}
							$ipn = $this->getIPNHandler($params, $renderOrder);
							/*$php_file = (array) $params->get('paypal_run_php_file');
							 $php_file = JFilterInput::clean($php_file[$renderOrder], 'CMD');
							 $php_file = empty($php_file) ? '' : COM_FABRIK_FRONTEND.DS.'plugins/form/fabrikpaypal/scripts'.DS.$php_file;
							 if (!empty($php_file) && file_exists($php_file)) {*/
							if ($ipn !== false) {
								$request = $_REQUEST;
								//require_once($php_file);
								//$ipn = new fabrikPayPalIPN();
								$ipn_function = 'payment_status_' . $payment_status;
								if (method_exists($ipn, $ipn_function)) {
									$status = $ipn->$ipn_function($listModel, $request, $set_list, $err_msg);
									if ($status != 'ok') {
										break;
									}
								}
								$txn_type_function = "txn_type_" . $txn_type;
								if (method_exists($ipn, $txn_type_function)) {
									$status = $ipn->$txn_type_function($listModel, $request, $set_list, $err_msg);
									if ($status != 'ok') {
										break;
									}
								}
							}

							if (!empty($set_list)) {
								$set_array = array();
								foreach ($set_list as $set_field => $set_value) {
									$set_value = $db->quote($set_value);
									$set_field = $db->quoteName($set_field);
									$set_array[] = "$set_field = $set_value";
								}
								$db->setQuery("UPDATE $table->db_table_name SET " . implode(',', $set_array) . " WHERE $table->db_primary_key = ".$db->quote($rowid));
								if (!$db->query()) {
									$status = 'form.paypal.ipnfailure.query_error';
									$err_msg = 'sql query error: ' . $db->getErrorMsg();
								}
							}
						}
					}
					elseif (JString::strcmp ($res, "INVALID") == 0) {
						$status = 'form.paypal.ipnfailure.invalid';
						$err_msg = 'paypal postback failed with INVALID';
					}
				}
				fclose ($fp);
			}
		}

		$receive_debug_emails = (array) $params->get('paypal_receive_debug_emails');
		$receive_debug_emails = $receive_debug_emails[$renderOrder];
		$send_default_email = (array) $params->get('paypal_send_default_email');
		$send_default_email = $send_default_email[$renderOrder];
		if ($status != 'ok')
		{
			foreach ($_POST as $key => $value)
			{
				$emailtext .= $key . " = " .$value ."\n\n";
			}

			if ($receive_debug_emails == '1')
			{
				$subject = $config->get('sitename').": Error with PayPal IPN from Fabrik";
				JUtility::sendMail( $email_from, $email_from, $admin_email, $subject, $emailtext, false);
			}
			$log->message_type = $status;
			$log->message = $emailtext ."\n//////////////\n" . $res ."\n//////////////\n". $req .  "\n//////////////\n".$err_msg;
			if ($send_default_email == '1')
			{
				$payer_emailtext = "There was an error processing your PayPal payment.  The administrator of this site has been informed.";
				JUtility::sendMail( $email_from, $email_from, $payer_email, $subject, $payer_emailtext, false);
			}
		}
		else {
			foreach ($_POST as $key => $value)
			{
				$emailtext .= $key . " = " .$value ."\n\n";
			}

			if ($receive_debug_emails == '1')
			{
				$subject = $config->get('sitename') . ': IPN ' . $payment_status;
				JUtility::sendMail( $email_from, $email_from, $admin_email, $subject, $emailtext, false);
			}
			$log->message_type = 'form.paypal.ipn.' . $payment_status;
			$query = $db->getQuery();
			$log->message = $emailtext ."\n//////////////\n" . $res ."\n//////////////\n". $req .  "\n//////////////\n".$query;

			if ($send_default_email == '1')
			{
				$payer_subject = "PayPal success";
				$payer_emailtext = "Your PayPal payment was succesfully processed.  The PayPal transaction id was $txn_id";
				JUtility::sendMail( $email_from, $email_from, $payer_email, $payer_subject, $payer_emailtext, false);
			}
		}
		$log->message .= "\n IPN custom function = $ipn_function";
		$log->message .= "\n IPN custom transaction function = $txn_type_function";
		$log->store();
		jexit();
	}

	/**
	 * get the custom IPN class
	 * @param	object	$params
	 * @param	int		params $renderOrder
	 * @return	mixed	false or class instance
	 */

	protected function getIPNHandler($params, $renderOrder = 0)
	{
		$php_file = (array) $params->get('paypal_run_php_file');
		$php_file = JFilterInput::clean($php_file[$renderOrder], 'CMD');
		$php_file = empty($php_file) ? '' : 'plugins/fabrik_form/paypal/scripts/' . $php_file;
		if (!empty($php_file) && file_exists($php_file))
		{
			$request = $_REQUEST;
			require_once($php_file);
			$ipn = new fabrikPayPalIPN();
			return $ipn;
		}
		else
		{
			return false;
		}
	}
}
?>