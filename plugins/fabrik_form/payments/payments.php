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
	 * @return	bool
	 */

	public function onAfterProcess()
	{
echo "<pre>";print_r(Omnipay::find());exit;
// Setup payment gateway
		$gateway = Omnipay::create('Stripe');
		$gateway->setApiKey('abc123');

// Example form data
		$formData = [
			'number' => '4242424242424242',
			'expiryMonth' => '6',
			'expiryYear' => '2016',
			'cvv' => '123'
		];

// Send purchase request
		$response = $gateway->purchase(
			[
				'amount' => '10.00',
				'currency' => 'USD',
				'card' => $formData
			]
		)->send();

		// Process response
		if ($response->isSuccessful()) {

			// Payment was successful
			print_r($response);

		} elseif ($response->isRedirect()) {

			// Redirect to offsite payment gateway
			$response->redirect();

		} else {

			// Payment failed
			echo $response->getMessage();
		}
		exit;
		return true;
	}

}
