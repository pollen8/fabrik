<?php
/**
 * MultiSafepay REST Api Gateway.
 */

namespace Omnipay\MultiSafepay;

use Omnipay\Common\AbstractGateway;

/**
 * MultiSafepay REST Api gateway.
 *
 * This class forms the gateway class for the MultiSafepay REST API.
 *
 * The MultiSafepay REST api is the latest version of their API and uses
 * HTTP verbs and a RESTful endpoint structure. The response payloads are
 * formatted as JSON and authentication happens via an api key within
 * the HTTP headers.
 *
 * ### Environments
 *
 * The MultiSafepay API support two different environments. A sandbox environment
 * which can be used for testing purposes. And a live environment for production processing.
 *
 * ### Sandbox environment
 *
 * The sandbox environment allows the user to test their implementation, transactions
 * will be created but no actual money will be involved.
 *
 * To use the sandbox environment the testMode parameter needs to be set on the gateway object.
 * This ensures that the sandbox endpoint will be used, instead of the production endpoint.
 *
 * ### Credentials
 *
 * Before you can use the API you need to register an account with MultiSafepay.
 *
 * To request access to the sandbox environment you need to register
 * at https://testmerchant.multisafepay.com/signup
 *
 * To request access to the live environment you need to register
 * at https://merchant.multisafepay.com/signup
 *
 * After you create your account, you can access the MultiSafepay dashboard which is located at
 * https://testmerchant.multisafepay.com or https://merchant.multisafepay.com depending
 * on the environment you use.
 *
 * To obtain an API key you first need to register your website with MultiSafepay.
 * This can be done within several steps:
 *
 * 1. Navigate to the create page: https://merchant.multisafepay.com/sites
 * 2. Fill out the required fields.
 * 3. Click save.
 * 4. You will be redirect to the site details page where you can find the API key.
 *
 * ### Initialize gateway
 *
 * <code>
 *   // Create the gateway
 *   $gateway = Omnipay::create('MultiSafepay_Rest');
 *
 *   // Initialise the gateway
 *   $gateway->initialize(array(
 *       'apiKey' => 'API-KEY',
 *       'locale' => 'en',
 *       'testMode' => true, // Or false, when you want to use the production environment
 *   ));
 * </code>
 *
 * ### Retrieve Payment Methods
 *
 * <code>
 *    $request = $gateway->fetchPaymentMethods();
 *    $response = $request->send();
 *    $paymentMethods = $response->getPaymentMethods();
 * </code>
 *
 * @link https://github.com/MultiSafepay/PHP
 * @link https://www.multisafepay.com/docs/getting-started/
 * @link https://www.multisafepay.com/documentation/doc/API-Reference/
 * @link https://www.multisafepay.com/documentation/doc/Step-by-Step/
 * @link https://www.multisafepay.com/signup/
 */
class RestGateway extends AbstractGateway
{
    /**
     * @{inheritdoc}
     */
    public function getName()
    {
        return 'MultiSafepay REST';
    }

    /**
     * Get the gateway parameters
     *
     * @return array
     */
    public function getDefaultParameters()
    {
        return array(
            'apiKey' => '',
            'locale' => 'en',
            'testMode' => false,
        );
    }

    /**
     * Get the locale.
     *
     * Optional ISO 639-1 language code which is used to specify a
     * a language used to display gateway information and other
     * messages in the responses.
     *
     * The default language is English.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->getParameter('locale');
    }

    /**
     * Set the locale.
     *
     * Optional ISO 639-1 language code which is used to specify a
     * a language used to display gateway information and other
     * messages in the responses.
     *
     * The default language is English.
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setLocale($value)
    {
        return $this->setParameter('locale', $value);
    }

    /**
     * Get the gateway API Key
     *
     * Authentication is by means of a single secret API key set as
     * the apiKey parameter when creating the gateway object.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    /**
     * Set the gateway API Key
     *
     * Authentication is by means of a single secret API key set as
     * the apiKey parameter when creating the gateway object.
     *
     * @param string $value
     * @return RestGateway provides a fluent interface.
     */
    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    /**
     * Retrieve payment methods active on the given MultiSafepay
     * account.
     *
     * @param array $parameters
     *
     * @return \Omnipay\MultiSafepay\Message\RestFetchPaymentMethodsRequest
     */
    public function fetchPaymentMethods(array $parameters = array())
    {
        return $this->createRequest(
            'Omnipay\MultiSafepay\Message\RestFetchPaymentMethodsRequest',
            $parameters
        );
    }

    /**
     * Retrieve issuers for gateway.
     *
     * @param array $parameters
     *
     * @return \Omnipay\MultiSafepay\Message\RestFetchIssuersRequest
     */
    public function fetchIssuers(array $parameters = array())
    {
        return $this->createRequest(
            'Omnipay\MultiSafepay\Message\RestFetchIssuersRequest',
            $parameters
        );
    }

    /**
     * Retrieve transaction by the given identifier.
     *
     * @param array $parameters
     * @return \Omnipay\MultiSafepay\Message\RestFetchTransactionRequest
     */
    public function fetchTransaction(array $parameters = array())
    {
        return $this->createRequest(
            'Omnipay\MultiSafepay\Message\RestFetchTransactionRequest',
            $parameters
        );
    }

    /**
     * Create a refund.
     *
     * @param array $parameters
     * @return \Omnipay\MultiSafepay\Message\RestRefundRequest
     */
    public function refund(array $parameters = array())
    {
        return $this->createRequest(
            'Omnipay\MultiSafepay\Message\RestRefundRequest',
            $parameters
        );
    }

    /**
     * Create a purchase request.
     *
     * MultisafePay support different types of transactions,
     * such as iDEAL, Paypal and CreditCard payments.
     *
     * @param array $parameters
     *
     * @return \Omnipay\MultiSafepay\Message\RestPurchaseRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest(
            'Omnipay\MultiSafepay\Message\RestPurchaseRequest',
            $parameters
        );
    }

    /**
     * Complete a payment request.
     *
     * @param array $parameters
     *
     * @return \Omnipay\MultiSafepay\Message\RestCompletePurchaseRequest
     */
    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest(
            'Omnipay\MultiSafepay\Message\RestCompletePurchaseRequest',
            $parameters
        );
    }
}
