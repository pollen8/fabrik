<?php
/**
 * First Data Webservice Purchase Request
 */
namespace Omnipay\FirstData\Message;

/**
 * First Data Webservice Purchase Request
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
 * // Do a purchase transaction on the gateway
 * $transaction = $gateway->purchase(array(
 *     'accountId'                 => '12345',
 *     'amount'                    => '10.00',
 *     'transactionId'             => 12345,
 *     'clientIp'                  => $_SERVER['REMOTE_ADDR'],
 *     'card'                      => $card,
 * ));
 * $response = $transaction->send();
 * if ($response->isSuccessful()) {
 *     echo "Purchase transaction was successful!\n";
 *     $sale_id = $response->getTransactionReference();
 *     echo "Transaction reference = " . $sale_id . "\n";
 * }
 * </code>
 */
class WebservicePurchaseRequest extends WebserviceAbstractRequest
{
    /** @var string XML template for the purchase request */
    protected $xmlTemplate ='
<fdggwsapi:FDGGWSApiOrderRequest xmlns:v1="http://secure.linkpt.net/fdggwsapi/schemas_us/v1"
    xmlns:fdggwsapi="http://secure.linkpt.net/fdggwsapi/schemas_us/fdggwsapi">
    <v1:Transaction>
        <v1:CreditCardTxType>
            <v1:Type>%txn_type%</v1:Type>
        </v1:CreditCardTxType>
        <v1:CreditCardData>
            <v1:CardNumber>%card_number%</v1:CardNumber>
            <v1:ExpMonth>%card_expiry_month%</v1:ExpMonth>
            <v1:ExpYear>%card_expiry_year%</v1:ExpYear>
        </v1:CreditCardData>
        <v1:Payment>
            <v1:ChargeTotal>%amount%</v1:ChargeTotal>
        </v1:Payment>
        <v1:TransactionDetails>
            <v1:OrderId>%reference_no%</v1:OrderId>
            <v1:Ip>%ip%</v1:Ip>
            <v1:Recurring>No</v1:Recurring>
        </v1:TransactionDetails>
        <v1:Billing>
            <v1:CustomerID>%account_id%</v1:CustomerID>
            <v1:Name>%card_name%</v1:Name>
            <v1:Address1>%card_address1%</v1:Address1>
            <v1:City>%card_city%</v1:City>
            <v1:State>%card_state%</v1:State>
            <v1:Zip>%card_postcode%</v1:Zip>
            <v1:Country>%card_country%</v1:Country>
            <v1:Email>%card_email%</v1:Email>
        </v1:Billing>
    </v1:Transaction>
</fdggwsapi:FDGGWSApiOrderRequest>
';

    /** @var string Transaction type */
    protected $txn_type = 'sale';

    /**
     * Get the request accountId
     *
     * @return string
     */
    public function getAccountId()
    {
        return $this->getParameter('accountId');
    }

    /**
     * Set the request accountId
     *
     * @param string $value
     * @return WebserviceAbstractRequest provides a fluent interface.
     */
    public function setAccountId($value)
    {
        return $this->setParameter('accountId', $value);
    }

    public function getData()
    {
        $data = parent::getData();

        $data['txn_type']           = $this->txn_type;

        // We have to make the transactionId a required parameter because
        // it is returned as the transactionReference, and required for
        // voids, refunds, and captures.  The accountId parameter is also
        // required by First Data.
        $this->validate('amount', 'card', 'transactionId', 'accountId');

        $data['amount']             = $this->getAmount();
        $data['reference_no']       = $this->getTransactionId();
        $data['ip']                 = $this->getClientIp();

        // Add account details
        $data['account_id']         = $this->getAccountId();

        // add credit card details
        $data['card_number']        = $this->getCard()->getNumber();
        $data['card_name']          = $this->getCard()->getName();
        $data['card_expiry_month']  = $this->getCard()->getExpiryDate('m');
        $data['card_expiry_year']   = $this->getCard()->getExpiryDate('y');
        $data['card_address1']      = $this->getCard()->getBillingAddress1();
        $data['card_address2']      = $this->getCard()->getBillingAddress2();
        $data['card_city']          = $this->getCard()->getBillingCity();
        $data['card_state']         = $this->getCard()->getBillingState();
        $data['card_postcode']      = $this->getCard()->getBillingPostcode();
        $data['card_country']       = $this->getCard()->getBillingCountry();
        $data['card_email']         = $this->getCard()->getEmail();
        $data['cvd_code']           = $this->getCard()->getCvv();

        return $data;
    }
}
