<?php
/**
 * Redirects the browser to paypal to perform payment
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.paypal
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Redirects the browser to paypal to perform payment
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.paypal
 * @since       3.0
 */
class PlgFabrik_FormStripe extends PlgFabrik_Form
{
	/*
	 * J! Log
	 *
	 * @var  object
	 */
	private $log = null;

	/*
	 * Stripe charge object
	 */
	private $charge = null;

	/*
	 * Stripe customer object
	 */
	private $customer = null;

	/*
	 * Customer table name
	 */
	private $customerTableName = null;

	/**
	 * Attempt to run the Stripe payment, return false (abort save) if it fails
	 *
	 * @return    bool
	 */
	public function onBeforeStore()
	{
		$params     = $this->getParams();
		$formModel  = $this->getModel();
		$listModel  = $formModel->getListModel();
		$table      = $listModel->getTable();
		$input      = $this->app->input;
		$db         = $listModel->getDb();
		$query      = $db->getQuery(true);
		$this->data = $this->getProcessData();
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fabrik/tables');

		if (!$this->shouldProcess('stripe_conditon', null, $params))
		{
			return true;
		}

		$w      = new FabrikWorker;
		$userId = $this->user->get('id');

		$testMode = $params->get('stripe_test_mode', $input->get('stripe_testmode', false));

		if ($testMode)
		{
			$publicKey = $params->get('stripe_test_publishable_key', '');
			$secretKey = $params->get('stripe_test_secret_key', '');
		}
		else
		{
			$publicKey = $params->get('stripe_publishable_key', '');
			$secretKey = $params->get('stripe_secret_key', '');
		}

		$tokenId   = FArrayHelper::getValue($this->data, 'stripe_token_id', '');
		$tokenEmail = FArrayHelper::getValue($this->data, 'stripe_token_email', '');
		$tokenOpts = FArrayHelper::getValue($this->data, 'stripe_token_opts', '{}');
		$tokenOpts = json_decode($tokenOpts);

		$amount = $params->get('stripe_cost');
		$amount = $w->parseMessageForPlaceHolder($amount, $this->data);

		/**
		 * Adding eval option on cost field
		 * Useful if you use a cart system which will calculate on total shipping or tax fee and apply it. You can return it in the Cost field.
		 * Returning false will log an error and bang out with a runtime exception.
		 */
		if ($params->get('stripe_cost_eval', 0) == 1)
		{
			$amount = @eval($amount);

			if ($amount === false)
			{
				$msgType   = 'fabrik.stripe.onAfterProcess';
				$msg       = new stdClass;
				$msg->data = $this->data;
				$msg->msg  = "Eval amount code returned false.";
				$msg       = json_encode($msg);
				$this->doLog($msgType, $msg);
				throw new RuntimeException(FText::_('PLG_FORM_STRIPE_COST_ELEMENT_ERROR'), 500);
			}
		}

		if (trim($amount) == '')
		{
			// Priority to raw data.
			$amountKey = FabrikString::safeColNameToArrayKey($params->get('stripe_cost_element'));
			$amount    = FArrayHelper::getValue($this->data, $amountKey);
			$amount    = FArrayHelper::getValue($this->data, $amountKey . '_raw', $amount);

			if (is_array($amount))
			{
				$amount = array_shift($amount);
			}
		}

		$costMultiplier = $params->get('stripe_currency_multiplier', '100');
		$amountMultiplied         = $amount * $costMultiplier;

		$item = $params->get('stripe_item');
		$item = $w->parseMessageForPlaceHolder($item, $this->data);

		if ($params->get('paypal_item_eval', 0) == 1)
		{
			$item = @eval($item);
		}

		$itemRaw = $item;

		if (trim($item) == '')
		{
			$itemRaw = FArrayHelper::getValue($this->data, FabrikString::safeColNameToArrayKey($params->get('paypal_item_element') . '_raw'));
			$item    = $this->data[FabrikString::safeColNameToArrayKey($params->get('stripe_item_element'))];

			if (is_array($item))
			{
				$item = array_shift($item);
			}

			if (is_array($itemRaw))
			{
				$itemRaw = array_shift($itemRaw);
			}
		}

		$currencyCode = $params->get('stripe_currency_code', 'USD');
		$currencyCode = $w->parseMessageForPlaceHolder($currencyCode, $this->data);
		$currencyCode = strtolower($currencyCode);

		$customerId = false;
		$customerTableName = $this->getCustomerTableName();
		$doCustomer = $customerTableName !== false && !empty($userId);

		if ($doCustomer)
		{
			$customerId = $this->getCustomerId($userId);
		}

		\Stripe\Stripe::setApiKey($secretKey);

		$chargeErrMsg = '';
		$customer = null;

		try
		{
			if ($doCustomer)
			{
				if (empty($customerId))
				{
					$this->customer = \Stripe\Customer::create(array(
						'source' => $tokenId,
						'email'  => $tokenEmail
					));

					$customerId = $this->customer->id;

					$this->updateCustomerId($userId, $customerId, $tokenOpts);
				}

				$this->charge = \Stripe\Charge::create(array(
					"amount"      => $amountMultiplied,
					"currency"    => $currencyCode,
					"customer"    => $customerId,
					"description" => $item,
					"metadata"    => array(
						"listid" => $listModel->getId(),
						"formid" => $formModel->getId(),
						"rowid"  => $this->data['rowid']
					)
				));
			}
			else
			{
				$this->charge = \Stripe\Charge::create(array(
					"amount"      => $amountMultiplied,
					"currency"    => $currencyCode,
					"source"      => $tokenId,
					"description" => $item,
					"metadata"    => array(
						"listid" => $listModel->getId(),
						"formid" => $formModel->getId(),
						"rowid"  => $this->data['rowid']
					)
				));
			}
		}
		catch (\Stripe\Error\Card $e)
		{
			// Since it's a decline, \Stripe\Error\Card will be caught
			$body = $e->getJsonBody();
			$err  = $body['error'];

			/*
			print('Status is:' . $e->getHttpStatus() . "\n");
			print('Type is:' . $err['type'] . "\n");
			print('Code is:' . $err['code'] . "\n");
			// param is '' in this case
			print('Param is:' . $err['param'] . "\n");
			print('Message is:' . $err['message'] . "\n");
			*/
			$this->log('fabrik.form.stripe.charge.declined', json_encode($body));
			$chargeErrMsg = FText::sprintf('PLG_FORM_STRIPE_DECLINED', $err['message']);
		}
		catch (\Stripe\Error\RateLimit $e)
		{
			// Too many requests made to the API too quickly
			$chargeErrMsg = JText::_('PLG_FORM_STRIPE_RATE_LIMITED');
		}
		catch (\Stripe\Error\InvalidRequest $e)
		{
			// Invalid parameters were supplied to Stripe's API
			$chargeErrMsg = JText::_('PLG_FORM_STRIPE_INTERNAL_ERR');
		}
		catch (\Stripe\Error\Authentication $e)
		{
			// Authentication with Stripe's API failed
			// (maybe you changed API keys recently)
			$chargeErrMsg = JText::_('PLG_FORM_STRIPE_INTERNAL_ERR');
		}
		catch (\Stripe\Error\ApiConnection $e)
		{
			// Network communication with Stripe failed
			$chargeErrMsg = JText::_('PLG_FORM_STRIPE_INTERNAL_ERR');
		}
		catch (\Stripe\Error\Base $e)
		{
			// Display a very generic error to the user, and maybe send
			// yourself an email
			$chargeErrMsg = JText::_('PLG_FORM_STRIPE_INTERNAL_ERR');
		}
		catch (Exception $e)
		{
			// Something else happened, completely unrelated to Stripe
			$chargeErrMsg = JText::_('PLG_FORM_STRIPE_INTERNAL_ERR');
		}

		if (!empty($chargeErrMsg))
		{
			$this->app->enqueueMessage($chargeErrMsg, 'message');

			return false;
		}

		$chargeIdField = $this->getFieldName('stripe_charge_id_element', '');

		if (!empty($chargeIdField))
		{
			$formModel->updateFormData($chargeIdField, $this->charge->id, true, true);
		}

		$chargeEmailField = $this->getFieldName('stripe_charge_email_element', '');

		if (!empty($chargeEmailField))
		{
			$formModel->updateFormData($chargeEmailField, $tokenEmail, true, true);
		}

		$opts = new stdClass;
		$opts->listid = $listModel->getId();
		$opts->formid = $formModel->getId();
		$opts->rowid = $this->data['rowid'];
		$opts->charge = $this->charge;
		$opts->customer = $customer;
		$msgType   = 'fabrik.stripe.onBeforeStore';
		$msg       = new stdClass;
		$msg->opts  = $opts;
		$msg->data = $this->data;
		$msg       = json_encode($msg);
		$this->doLog($msgType, $msg);

		return true;
	}

	public function onAfterProcess()
	{
		if (isset($this->charge))
		{
			$opts           = new stdClass;
			$opts->listid   = $this->getModel()->getListModel()->getId();
			$opts->formid   = $this->getModel()->getId();
			$opts->rowid    = $this->data['rowid'];
			$opts->chargeId = $this->charge->id;
			$msgType        = 'fabrik.stripe.onAfterProcess';
			$msg            = new stdClass;
			$msg->opts      = $opts;
			$msg            = json_encode($msg);
			$this->doLog($msgType, $msg);
		}
	}

	/**
	 * Sets up HTML to be injected into the form's bottom
	 *
	 * @return void
	 */
	public function getBottomContent()
	{
		$this->html = '';

		$params     = $this->getParams();
		$formModel  = $this->getModel();
		$input      = $this->app->input;
		$this->data = $formModel->data;

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fabrik/tables');

		if (!$this->shouldProcess('stripe_conditon', null, $params))
		{
			return true;
		}

		$opts = new stdClass();

		$w      = new FabrikWorker;
		$userId = $this->user->get('id');

		if (!empty($userId))
		{
			$opts->email = $this->user->get('email');
		}

		$testMode = $params->get('stripe_test_mode', $input->get('stripe_testmode', false));

		if ($testMode)
		{
			$opts->publicKey = $params->get('stripe_test_publishable_key', '');
			$secretKey = $params->get('stripe_test_secret_key', '');
		}
		else
		{
			$opts->publicKey = $params->get('stripe_publishable_key', '');
			$secretKey = $params->get('stripe_secret_key', '');
		}

		$opts->name = FText::_($params->get('stripe_dialog_name', ''));
		$opts->panelLabel = FText::_($params->get('stripe_panel_label', 'PLG_FORM_STRIPE_PAY'));
		$opts->allowRememberMe = false;
		$opts->zipCode = $params->get('stripe_zipcode_check', '1') === '1';

		$amount = $params->get('stripe_cost');
		$amount = $w->parseMessageForPlaceHolder($amount, $this->data);

		if ($params->get('stripe_cost_eval', 0) == 1)
		{
			$amount = @eval($amount);

			if ($amount === false)
			{
				$msgType   = 'fabrik.stripe.onAfterProcess';
				$msg       = new stdClass;
				$msg->data = $this->data;
				$msg->msg  = "Eval amount code returned false.";
				$msg       = json_encode($msg);
				$this->doLog($msgType, $msg);
				throw new RuntimeException(FText::_('PLG_FORM_STRIPE_COST_ELEMENT_ERROR'), 500);
			}
		}

		if (trim($amount) == '')
		{
			// Priority to raw data.
			$amountKey = FabrikString::safeColNameToArrayKey($params->get('stripe_cost_element'));
			$amount    = FArrayHelper::getValue($this->data, $amountKey);
			$amount    = FArrayHelper::getValue($this->data, $amountKey . '_raw', $amount);

			if (is_array($amount))
			{
				$amount = array_shift($amount);
			}
		}

		$costMultiplier = $params->get('stripe_currency_multiplier', '100');
		$amountMultiplied         = $amount * $costMultiplier;

		$opts->amount = $amountMultiplied;

		$item = $params->get('stripe_item');
		$item = $w->parseMessageForPlaceHolder($item, $this->data);

		if ($params->get('stripe_item_eval', 0) == 1)
		{
			$item = @eval($item);
		}

		$itemRaw = $item;

		if (trim($item) == '')
		{
			$itemRaw = FArrayHelper::getValue($this->data, FabrikString::safeColNameToArrayKey($params->get('stripe_item_element') . '_raw'));
			$item    = $this->data[FabrikString::safeColNameToArrayKey($params->get('stripe_item_element'))];

			if (is_array($item))
			{
				$item = array_shift($item);
			}

			if (is_array($itemRaw))
			{
				$itemRaw = array_shift($itemRaw);
			}
		}

		$opts->item = $item;

		$currencyCode       = $params->get('stripe_currencycode', 'USD');
		$currencyCode       = $w->parseMessageForPlaceHolder($currencyCode, $this->data);
		$opts->currencyCode = $currencyCode;

		$opts->billingAddress = $params->get('stripe_collect_billing_address', '0') === '1';

		/*
		 *  $stripe_customer = \Stripe\Customer::retrieve($existing_customer['stripe_id']);
      $card = $stripe_customer->sources->retrieve($stripe_customer->default_source);
      ?>

      <form action="charge.php" method="POST">
        Would you like to pay $535.00 with your card ending in <?php echo $card->last4 ?>?
        <input type="hidden" name="customer_id" value="<?php echo $stripe_customer->id ?>" />
        <input type="submit" name="submit" value="Yes!" />
      </form>
		 */

		$customerId = $this->getCustomerId($userId);

		if (!empty($customerId))
		{
			$opts->useCheckout = false;

			\Stripe\Stripe::setApiKey($secretKey);
			try
			{
				$customer = \Stripe\Customer::retrieve($customerId);
				$card     = $customer->sources->retrieve($customer->default_source);
			}
			catch (\Stripe\Error\Card $e)
			{
				// Since it's a decline, \Stripe\Error\Card will be caught
				$body = $e->getJsonBody();
				$err  = $body['error'];

				/*
				print('Status is:' . $e->getHttpStatus() . "\n");
				print('Type is:' . $err['type'] . "\n");
				print('Code is:' . $err['code'] . "\n");
				// param is '' in this case
				print('Param is:' . $err['param'] . "\n");
				print('Message is:' . $err['message'] . "\n");
				*/
				$this->log('fabrik.form.stripe.charge.declined', json_encode($body));
				$chargeErrMsg = FText::sprintf('PLG_FORM_STRIPE_DECLINED', $err['message']);
			}
			catch (\Stripe\Error\RateLimit $e)
			{
				// Too many requests made to the API too quickly
				$chargeErrMsg = JText::_('PLG_FORM_STRIPE_RATE_LIMITED');
			}
			catch (\Stripe\Error\InvalidRequest $e)
			{
				// Invalid parameters were supplied to Stripe's API
				$chargeErrMsg = JText::_('PLG_FORM_STRIPE_INTERNAL_ERR');
			}
			catch (\Stripe\Error\Authentication $e)
			{
				// Authentication with Stripe's API failed
				// (maybe you changed API keys recently)
				$chargeErrMsg = JText::_('PLG_FORM_STRIPE_INTERNAL_ERR');
			}
			catch (\Stripe\Error\ApiConnection $e)
			{
				// Network communication with Stripe failed
				$chargeErrMsg = JText::_('PLG_FORM_STRIPE_INTERNAL_ERR');
			}
			catch (\Stripe\Error\Base $e)
			{
				// Display a very generic error to the user, and maybe send
				// yourself an email
				$chargeErrMsg = JText::_('PLG_FORM_STRIPE_INTERNAL_ERR');
			}
			catch (Exception $e)
			{
				// Something else happened, completely unrelated to Stripe
				$chargeErrMsg = JText::_('PLG_FORM_STRIPE_INTERNAL_ERR');
			}

			if (!empty($chargeErrMsg))
			{
				$this->app->enqueueMessage($chargeErrMsg, 'message');

				return false;
			}


			$layout     = $this->getLayout('existing-customer');
			$layoutData = new stdClass();
			$layoutData->card = $card;
			$layoutData->amount = $amount;
			$layoutData->currencyCode = $currencyCode;
			$layoutData->langTag = JFactory::getLanguage()->getTag();
			$this->html = $layout->render($layoutData);
		}
		else
		{
			$opts->useCheckout = true;
			$layout     = $this->getLayout('checkout');
			$layoutData = new stdClass();
			$layoutData->amount = $amount;
			$layoutData->currencyCode = $currencyCode;
			$layoutData->langTag = JFactory::getLanguage()->getTag();
			$this->html = $layout->render($layoutData);
			FabrikHelperHTML::script('https://checkout.stripe.com/checkout.js');
		}

		$opts = json_encode($opts);

		$this->formJavascriptClass($params, $formModel);
		$formModel->formPluginJS['Stripe' . $this->renderOrder] = 'var stripe = new Stripe(' . $opts . ');';

	}

	/**
	 * Inject custom html into the bottom of the form
	 *
	 * @param   int $c plugin counter
	 *
	 * @return  string  html
	 */
	public function getBottomContent_result($c)
	{
		return $this->html;
	}

	/**
	 * Log a message
	 *
	 * @param  string $msgType The dotted message type
	 * @param  string $msg     The log message
	 */
	private function doLog($msgType, $msg)
	{
		if ($this->log === null)
		{
			$this->log                = FabTable::getInstance('log', 'FabrikTable');
			$this->log->referring_url = $this->app->input->server->getString('REQUEST_URI');
		}
		$this->log->message_type = $msgType;
		$this->log->message      = $msg;
		$this->log->id           = '';
		$this->log->store();
	}

	/**
	 * Get the Customer table name
	 *
	 * @return  string  db table name
	 */
	protected function getCustomerTableName()
	{
		if (isset($this->customerTableName))
		{
			return $this->customerTableName;
		}

		$params = $this->getParams();
		$customerTable = (int) $params->get('stripe_customers_table', '');

		if (empty($customerTable))
		{
			$this->customerTableName = false;

			return false;
		}

		$db = FabrikWorker::getDbo();
		$query = $db->getQuery(true);
		$query->select('db_table_name')->from('#__{package}_lists')->where('id = ' . (int) $params->get('stripe_customers_table'));
		$db->setQuery($query);
		$db_table_name = $db->loadResult();

		if (!isset($db_table_name))
		{
			$this->customerTableName = false;

			return false;
		}

		$this->customerTableName = $db_table_name;

		return $this->customerTableName;
	}

	private function getCustomerId($userId)
	{
		$params = $this->getParams();
		$cDb = FabrikWorker::getDbo(false, $params->get('stripe_customers_connection'));
		$cQuery = $cDb->getQuery(true);
		$cUserIdField = FabrikString::shortColName($params->get('stripe_customers_userid'));
		$cStripeIdField = FabrikString::shortColName($params->get('stripe_customers_stripeid'));

		if (empty($cUserIdField) || empty($cStripeIdField))
		{
			throw new RuntimeException('Stripe plugin is not configured correctly');
		}

		$cQuery
			->select($cDb->quoteName($cStripeIdField))
			->from($cDb->quoteName($this->getCustomerTableName()))
			->where($cDb->quoteName($cUserIdField) . ' = ' . $cDb->quote($userId));
		$cDb->setQuery($cQuery);

		return $cDb->loadResult();
	}

	private function updateCustomerId($userId, $customerId, $opts)
	{
		$params = $this->getParams();
		$cDb = FabrikWorker::getDbo(false, $params->get('stripe_customers_connection'));
		$cQuery = $cDb->getQuery(true);
		$cUserIdField = FabrikString::shortColName($params->get('stripe_customers_userid'));
		$cStripeIdField = FabrikString::shortColName($params->get('stripe_customers_stripeid'));
		$done = false;

		if (empty($cUserIdField) || empty($cStripeIdField))
		{
			throw new RuntimeException('Stripe plugin not configured correctly');
		}

		$stripeAddressFields = array(
			"name",
			"address_country",
			"address_country_code",
			"address_zip",
			"address_line1",
			"address_city",
			"address_state"
		);

		$setList = array();

		if ($params->get('stripe_collect_billing_address', '0') === '1')
		{
			foreach ($stripeAddressFields as $field)
			{
				$paramName = 'stripe_customers_billing_' . $field;
				$fieldName = FabrikString::shortColName($params->get($paramName));

				if (!empty($fieldName))
				{
					$optName = 'billing_' . $field;

					if (isset($opts->$optName))
					{
						$setList[] = $cDb->quoteName($fieldName) . ' = ' . $cDb->quote($opts->$optName);
					}
				}

			}
		}

		if ($params->get('stripe_collect_shipping_address', '0') === '1')
		{
			foreach ($stripeAddressFields as $field)
			{
				$paramName = 'stripe_customers_shipping_' . $field;
				$fieldName = FabrikString::shortColName($params->get($paramName));

				if (!empty($fieldName))
				{
					$optName = 'shipping_' . $field;

					if (isset($opts->$optName))
					{
						$setList[] = $cDb->quoteName($fieldName) . ' = ' . $cDb->quote($opts->$optName);
					}
				}

			}
		}

		try
		{
			if ($params->get('stripe_customer_insert', '') === '1')
			{
				$cQuery
					->select('*')
					->from($cDb->quoteName($this->getCustomerTableName()))
					->where($cDb->quoteName($cUserIdField) . ' = ' . $cDb->quote($userId));
				$cDb->setQuery($cQuery);
				$result = $cDb->loadObject();

				if (empty($result))
				{
					$cQuery
						->clear()
						->insert($cDb->quoteName($this->getCustomerTableName()))
						->set($cDb->quoteName($cStripeIdField) . ' = ' . $cDb->quote($customerId))
						->set($cDb->quoteName($cUserIdField) . ' = ' . $cDb->quote($userId));

					foreach ($setList as $set)
					{
						$cQuery->set($set);
					}

					$cDb->setQuery($cQuery);
					$cDb->execute();
					$done = true;
				}
			}

			if (!$done)
			{
				$cQuery
					->clear()
					->update($cDb->quoteName($this->getCustomerTableName()))
					->set($cDb->quoteName($cStripeIdField) . ' = ' . $cDb->quote($customerId))
					->where($cDb->quoteName($cUserIdField) . ' = ' . $cDb->quote($userId));

				foreach ($setList as $set)
				{
					$cQuery->set($set);
				}

				$cDb->setQuery($cQuery);
				$cDb->execute();
			}
		}
		catch (Exception $e)
		{
			$this->log('fabrik.form.stripe.customer.error', $e->getMessage());
			$this->app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}
}
