<?php
/**
 * Pin Gateway
 */

namespace Omnipay\Pin;

use Omnipay\Common\AbstractGateway;

/**
 * Pin Gateway
 *
 * Pin Payments is an Australian all-in-one payment system, allowing you
 * to accept multi-currency credit card payments without a security
 * deposit or a merchant account.
 *
 * ### Example
 *
 * <code>
 * // Create a gateway for the Pin REST Gateway
 * // (routes to GatewayFactory::create)
 * $gateway = Omnipay::create('PinGateway');
 *
 * // Initialise the gateway
 * $gateway->initialize(array(
 *     'secretKey' => 'TEST',
 *     'testMode'  => true, // Or false when you are ready for live transactions
 * ));
 *
 * // Create a credit card object
 * // This card can be used for testing.
 * // See https://pin.net.au/docs/api/test-cards for a list of card
 * // numbers that can be used for testing.
 * $card = new CreditCard(array(
 *             'firstName'    => 'Example',
 *             'lastName'     => 'Customer',
 *             'number'       => '4200000000000000',
 *             'expiryMonth'  => '01',
 *             'expiryYear'   => '2020',
 *             'cvv'          => '123',
 *             'email'        => 'customer@example.com',
 *             'billingAddress1'       => '1 Scrubby Creek Road',
 *             'billingCountry'        => 'AU',
 *             'billingCity'           => 'Scrubby Creek',
 *             'billingPostcode'       => '4999',
 *             'billingState'          => 'QLD',
 * ));
 *
 * // Do a purchase transaction on the gateway
 * $transaction = $gateway->purchase(array(
 *     'description'              => 'Your order for widgets',
 *     'amount'                   => '10.00',
 *     'currency'                 => 'AUD',
 *     'clientIp'                 => $_SERVER['REMOTE_ADDR'],
 *     'card'                     => $card,
 * ));
 * $response = $transaction->send();
 * if ($response->isSuccessful()) {
 *     echo "Purchase transaction was successful!\n";
 *     $sale_id = $response->getTransactionReference();
 *     echo "Transaction reference = " . $sale_id . "\n";
 * }
 * </code>
 *
 * ### Test modes
 *
 * The API has two endpoint host names:
 *
 * * api.pin.net.au (live)
 * * test-api.pin.net.au (test)
 *
 * The live host is for processing live transactions, whereas the test
 * host can be used for integration testing and development.
 *
 * Each endpoint requires a different set of API keys, which can be
 * found in your account settings.
 *
 * ### Authentication
 *
 * Calls to the Pin Payments API must be authenticated using HTTP
 * basic authentication, with your API key as the username, and
 * a blank string as the password.
 *
 * #### Keys
 *
 * Your account has two types of keys:
 *
 * * publishable
 * * secret
 *
 * You can find your keys on the account settings page of the dashboard
 * after you have created an account at pin.net.au and logged in.
 *
 * Your secret key can be used with all of the API, and must be kept
 * secure and secret at all times. You use your secret key from your
 * server to create charges and refunds.
 *
 * Your publishable key can be used from insecure locations (such as
 * browsers or mobile apps) to create cards with the cards API. This
 * is the key you use with Pin.js to create secure payment forms in
 * the browser.
 *
 * @see \Omnipay\Common\AbstractGateway
 * @link https://pin.net.au/docs/api
 */
class Gateway extends AbstractGateway
{
    public function getName()
    {
        return 'Pin';
    }

    public function getDefaultParameters()
    {
        return array(
            'secretKey' => '',
            'testMode' => false,
        );
    }

    /**
     * Get secret key
     *
     * Calls to the Pin Payments API must be authenticated using HTTP
     * basic authentication, with your API key as the username, and
     * a blank string as the password.
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->getParameter('secretKey');
    }

    /**
     * Set secret key
     *
     * Calls to the Pin Payments API must be authenticated using HTTP
     * basic authentication, with your API key as the username, and
     * a blank string as the password.
     *
     * @param string $value
     * @return Gateway implements a fluent interface
     */
    public function setSecretKey($value)
    {
        return $this->setParameter('secretKey', $value);
    }

    /**
     * Create a purchase request
     *
     * @param array $parameters
     * @return \Omnipay\Pin\Message\PurchaseRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Pin\Message\PurchaseRequest', $parameters);
    }

    /**
     * Create an authorize request
     *
     * @param array $parameters
     * @return \Omnipay\Pin\Message\AuthorizeRequest
     */
    public function authorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Pin\Message\AuthorizeRequest', $parameters);
    }

    /**
     * Create a capture request
     *
     * @param array $parameters
     * @return \Omnipay\Pin\Message\CaptureRequest
     */
    public function capture(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Pin\Message\CaptureRequest', $parameters);
    }

    /**
     * Create a refund request
     *
     * @param array $parameters
     * @return \Omnipay\Pin\Message\RefundRequest
     */
    public function refund(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Pin\Message\RefundRequest', $parameters);
    }

    /**
     * Create a createCustomer request
     *
     * @param array $parameters
     * @return \Omnipay\Pin\Message\CreateCustomerRequest
     */
    public function createCustomer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Pin\Message\CreateCustomerRequest', $parameters);
    }

    /**
     * Create a createCard request
     *
     * @param array $parameters
     * @return \Omnipay\Pin\Message\CreateCardRequest
     */
    public function createCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Pin\Message\CreateCardRequest', $parameters);
    }
}
