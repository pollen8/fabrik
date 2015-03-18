<?php
/**
 * Global payment processor
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.payments
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

require JPATH_SITE . '/plugins/fabrik_form/payments/vendor/autoload.php';
use Omnipay\Omnipay;

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Global payments processor
 *
 * paypal
 * ******
 *
 * API Key: sk_test_GkWz3OFHm4PZnzyHfuQ6gU8f
 * Username: rob-business_api1.test.com
 * Password: CB5QAQZA632HBHC9
 * Signature: AFcWxV21C7fd0v3bYYYRCpSSRl31AUTzFc-q5rfZKGtpDa7lrqmx8EyN
 *
 * Authorize.net AIM
 * *****************
 * [apiLoginId] => 7ZgFt4cV26Lq
 * [transactionKey] => 428zvHKh6F93Vq5U
 * developerMode
 * (remember to turn on test mode?)
 *
 * Stripe
 * ******
 * Test secret key: sk_test_GkWz3OFHm4PZnzyHfuQ6gU8f
 *
 * Test Card Details
 * ******************
 * Card #: 4111111111111111
 * CVV: 334
 * Expiry year: 2018
 * Expiry month: 04
 *
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.payments
 * @since       3.3
 */
class PlgFabrik_FormPayments extends PlgFabrik_Form
{
	/**
	 * Run right at the end of the form processing
	 * form needs to be set to record in database for this to hook to be called
	 *
	 * @return    bool
	 */

	public function onAfterProcess()
	{
		$this->data = $this->getProcessData();
		$session    = JFactory::getSession();
		$gateway    = $this->getGateway();

		$purchase = $this->getPurchaseDetails();
		$session->set('fabrik.form.payment', $purchase);

		/*echo "<pre>";
			print_r($purchase);
			print_r($gateway);
			exit;*/

		// Send purchase request
		$response = $gateway->purchase(
			$purchase
		)->send();

		// Process response
		if ($response->isSuccessful())
		{
			echo "Ok ";
			//echo "<pre>";print_r($response);
			echo $response->getTransactionReference();
			exit;

			// Payment was successful
			return true;

		}
		elseif ($response->isRedirect())
		{

			// Redirect to offsite payment gateway
			$response->redirect();

		}
		else
		{

			// Payment failed
			echo $response->getMessage();
			//echo "<pre>";print_r($response);
		}
		exit;

		return true;
	}

	/**
	 * Build the gateway object
	 *
	 * @return mixed
	 */
	protected function getGateway()
	{
		$params  = $this->getParams();
		$gateWayName = $params->get('payments_gateway');

		// Setup payment gateway
		$gateway  = Omnipay::create($gateWayName);
		$settings = $gateway->getDefaultParameters();
		$prefix = strtolower($gateWayName);

		foreach ($settings as $setting => $default)
		{
			if ($default === '')
			{
				$gatewaySetting = $prefix . '_' . $setting;
				$method = 'set' . ucfirst($setting);

				// See if we have a custom gateway setting, if not use default.
				$value = $params->get($gatewaySetting, $params->get($setting, ''));
				$gateway->$method($value);
			}
		}

		$this->setTestMode($gateway);

		return $gateway;
	}

	protected function setTestMode(&$gateway)
	{
		$params = $this->getParams();
		$mode   = (bool) $params->get('testMode');
		$gateway->setTestMode($mode);

		if ($mode)
		{
			try
			{
				// For Authorize.net AIM only
				$gateway->setDeveloperMode(true);
			} catch (Exception $e)
			{

			}
		}
	}

	/**
	 * Build the purchase data
	 *
	 * @return array
	 */
	protected function getPurchaseDetails()
	{
		$w         = new FabrikWorker;
		$app       = JFactory::getApplication();
		$formModel = $this->getModel();
		$params    = $this->getParams();
		$card      = $this->getCard();
		/*	print_r($card);
			exit;*/
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		//$returnUrl = $params->get('return_url');
		$returnUrl = COM_FABRIK_LIVESITE . 'index.php?option=com_' . $package . '&c=plugin&task=plugin.pluginAjax&formid=' . $formModel->get('id')
			. '&g=form&plugin=payments&method=completePurchase&renderOrder=' . $this->renderOrder;
		$cancelUrl = $params->get('cancel_url');
		$cancelUrl = COM_FABRIK_LIVESITE . $cancelUrl;

		return [
			'amount' => $amount = $w->parseMessageForPlaceHolder($params->get('amount'), $this->data),
			'currency' => $amount = $w->parseMessageForPlaceHolder($params->get('currency'), $this->data),
			'card' => $card,
			'returnUrl' => $returnUrl,
			'cancelUrl' => $cancelUrl,
			'description' => $amount = $w->parseMessageForPlaceHolder($params->get('description'), $this->data)
		];
	}

	/**
	 * Build the card data.
	 *
	 * @return array
	 */
	protected function getCard()
	{
		$params = $this->getParams();

		return [
			'number' => $this->getFormDataForFieldId($params->get('number')),
			'expiryMonth' => $this->getFormDataForFieldId($params->get('expiryMonth')),
			'expiryYear' => $this->getFormDataForFieldId($params->get('expiryYear')),
			'cvv' => $this->getFormDataForFieldId($params->get('cvv'))
		];
	}

	protected function getFormDataForFieldId($id)
	{
		$formModel    = $this->getModel();
		$elementModel = $formModel->getElement($id, true);
		$value        = $elementModel->getFullName();

		return FArrayHelper::getValue($this->data, $value);
	}

	/**
	 * For off-site purchases we need to complete the transaction here.
	 */
	public function onCompletePurchase()
	{
		$app       = JFactory::getApplication();
		$id        = $app->input->get('formid');
		$formModel = JModelLegacy::getInstance('Form', 'FabrikFEModel');
		$formModel->setId($id);
		$renderOrder = $app->input->getInt('renderOrder', 0);
		$params      = $formModel->getParams();
		$this->setParams($params, $renderOrder);
		$session  = JFactory::getSession();
		$gateway  = $this->getGateway();
		$params   = $session->get('fabrik.form.payment');
		$response = $gateway->completePurchase($params)->send();

		$finalResponse = $response->getData(); // this is the raw response object
		echo "<pre>";
		print_r($finalResponse);
		if (isset($finalResponse['PAYMENTINFO_0_ACK']) && $finalResponse['PAYMENTINFO_0_ACK'] === 'Success')
		{
			// Response // print_r($paypalResponse);
		}
		else
		{
			//Failed transaction - See more at: https://solidmarkup.com/blog/using-omnipay-paypal-with-laravel#sthash.FnWrBdQB.dpuf
		}
		exit;
	}
}
