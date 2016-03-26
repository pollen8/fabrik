<?php
/**
 * First Data Payeezy Gateway
 */
namespace Omnipay\FirstData;

use Omnipay\Common\AbstractGateway;

/**
 * First Data Payeezy Gateway
 *
 * The First Data Global Gateway e4 (previously referred to as "First Data Global", and so if you see
 * internet references to the First Data Global Gateway, they are probably referring to this one, distinguished
 * by having URLs like "api.globalgatewaye4.firstdata.com") is now called the Payeezy Gateway and is
 * referred to here as the First Data Payeezy Gateway.
 *
 * API details for the Payeezy gateway are at the links below.
 *
 * ### Example
 *
 * <code>
 * // Create a gateway for the First Data Payeezy Gateway
 * // (routes to GatewayFactory::create)
 * $gateway = Omnipay::create('FirstData_Payeezy');
 *
 * // Initialise the gateway
 * $gateway->initialize(array(
 *     'gatewayId' => '12341234',
 *     'password'  => 'thisISmyPASSWORD',
 *     'testMode'  => true, // Or false when you are ready for live transactions
 * ));
 *
 * // Create a credit card object
 * $card = new CreditCard(array(
 *     'firstName'            => 'Example',
 *     'lastName'             => 'Customer',
 *     'number'               => '4222222222222222',
 *     'expiryMonth'          => '01',
 *     'expiryYear'           => '2020',
 *     'cvv'                  => '123',
 *     'email'                => 'customer@example.com',
 *     'billingAddress1'      => '1 Scrubby Creek Road',
 *     'billingCountry'       => 'AU',
 *     'billingCity'          => 'Scrubby Creek',
 *     'billingPostcode'      => '4999',
 *     'billingState'         => 'QLD',
 * ));
 *
 * // Do a purchase transaction on the gateway
 * $transaction = $gateway->purchase(array(
 *     'description'              => 'Your order for widgets',
 *     'amount'                   => '10.00',
 *     'transactionId'            => 12345,
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
 * ### Test Accounts
 *
 * Test accounts can be obtained here:
 * https://provisioning.demo.globalgatewaye4.firstdata.com/signup
 * Note that only USD transactions are supported for test accounts.
 *
 * Once you have created a test account, log in to the gateway here:
 * https://demo.globalgatewaye4.firstdata.com/main
 * Navigate to Administration -> Terminals and click on the terminal with TERM ECOMM name,
 * There will be a Gateway ID displayed there and you can also generate a password.
 *
 * Test credit card numbers can be found here:
 * https://support.payeezy.com/hc/en-us/articles/204504235-Using-test-credit-card-numbers
 *
 * ### Quirks
 *
 * This gateway requires both a transaction reference (aka an authorization number)
 * and a transaction tag to implement either voids or refunds.  These are referred
 * to in the documentation as "tagged refund" and "tagged voids".
 *
 * Card token transactions are supported (these are referred to in the documentation as
 * "TransArmor Multi-Pay") but have to be enabled for each merchant account.  There is no
 * createCard method, instead a card token is generated when a zero dollar authorization
 * is submitted.
 *
 * Void and Refund transactions require the amount parameter.
 *
 * @link https://support.payeezy.com/hc/en-us
 * @link https://provisioning.demo.globalgatewaye4.firstdata.com/signup
 * @link https://support.payeezy.com/hc/en-us/articles/204504235-Using-test-credit-card-numbers
 */
class PayeezyGateway extends AbstractGateway
{
    public function getName()
    {
        return 'First Data Payeezy';
    }

    public function getDefaultParameters()
    {
        return array(
            'gatewayid' => '',
            'password'  => '',
            'testMode'  => false,
        );
    }

    /**
     * Get Gateway ID
     *
     * Calls to the Payeezy Gateway API are secured with a gateway ID and
     * password.
     *
     * @return string
     */
    public function getGatewayId()
    {
        return $this->getParameter('gatewayid');
    }

    /**
     * Set Gateway ID
     *
     * Calls to the Payeezy Gateway API are secured with a gateway ID and
     * password.
     *
     * @return PayeezyGateway provides a fluent interface.
     */
    public function setGatewayId($value)
    {
        return $this->setParameter('gatewayid', $value);
    }

    /**
     * Get Password
     *
     * Calls to the Payeezy Gateway API are secured with a gateway ID and
     * password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->getParameter('password');
    }

    /**
     * Set Password
     *
     * Calls to the Payeezy Gateway API are secured with a gateway ID and
     * password.
     *
     * @return PayeezyGateway provides a fluent interface.
     */
    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    /**
     * Create a purchase request.
     *
     * @param array $parameters
     * @return \Omnipay\FirstData\Message\PayeezyPurchaseRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\FirstData\Message\PayeezyPurchaseRequest', $parameters);
    }

    /**
     * Create an authorize request.
     *
     * @param array $parameters
     * @return \Omnipay\FirstData\Message\PayeezyAuthorizeRequest
     */
    public function authorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\FirstData\Message\PayeezyAuthorizeRequest', $parameters);
    }

    /**
     * Create a capture request.
     *
     * @param array $parameters
     * @return \Omnipay\FirstData\Message\PayeezyCaptureRequest
     */
    public function capture(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\FirstData\Message\PayeezyCaptureRequest', $parameters);
    }

    /**
     * Create a refund request.
     *
     * @param array $parameters
     * @return \Omnipay\FirstData\Message\PayeezyRefundRequest
     */
    public function refund(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\FirstData\Message\PayeezyRefundRequest', $parameters);
    }

    /**
     * Create a void request.
     *
     * @param array $parameters
     * @return \Omnipay\FirstData\Message\PayeezyVoidRequest
     */
    public function void(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\FirstData\Message\PayeezyVoidRequest', $parameters);
    }
}
