<?php
/**
 * First Data Webservice Authorize Request
 */
namespace Omnipay\FirstData\Message;

/**
 * First Data Webservice Authorize Request
 *
 * ### Example
 *
 * <code>
 * // Create a gateway for the First Data Webservice Gateway
 * // (routes to GatewayFactory::create)
 * $gateway = Omnipay::create('FirstData_Webservice');
 *
 * // Initialise the gateway
 * $gateway->initialize(array(
 *     'sslCertificate'    => 'WS9999999._.1.pem',
 *     'sslKey'            => 'WS9999999._.1.key',
 *     'sslKeyPassword'    => 'sslKEYpassWORD',
 *     'userName'          => 'WS9999999._.1',
 *     'password'          => 'passWORD',
 *     'testMode'          => true,
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
 * // Do an authorize transaction on the gateway
 * $transaction = $gateway->authorize(array(
 *     'accountId'                 => '12345',
 *     'amount'                    => '10.00',
 *     'currency'                  => 'USD',
 *     'description'               => 'Super Deluxe Excellent Discount Package',
 *     'transactionId'             => 12345,
 *     'clientIp'                  => $_SERVER['REMOTE_ADDR'],
 *     'card'                      => $card,
 * ));
 * $response = $transaction->send();
 * if ($response->isSuccessful()) {
 *     echo "Authorize transaction was successful!\n";
 *     $sale_id = $response->getTransactionReference();
 *     echo "Transaction reference = " . $sale_id . "\n";
 * }
 * </code>
 */
class WebserviceAuthorizeRequest extends WebservicePurchaseRequest
{
    /** @var string Transaction type */
    protected $txn_type = 'preAuth';
}
