<?php
/**
 * First Data Webservice Void Request
 */
namespace Omnipay\FirstData\Message;

/**
 * First Data Webservice Void Request
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
 * // Do a void transaction on the gateway.  This assumes that a purchase
 * // request has been successful, and that the transactionReference from the
 * // purchase request is stored in $sale_id.
 * $transaction = $gateway->void(array(
 *     'transactionReference'      => $sale_id,
 * ));
 * $response = $transaction->send();
 * if ($response->isSuccessful()) {
 *     echo "Void transaction was successful!\n";
 *     $sale_id = $response->getTransactionReference();
 *     echo "Transaction reference = " . $sale_id . "\n";
 * }
 * </code>
 *
 * ### Quirks
 *
 * In the case of a captured authorization, the transaction reference passed
 * to void() must be the transaction reference returned from the capture()
 * request and not the transaction reference from the original authorize()
 * request.
 */
class WebserviceVoidRequest extends WebserviceAbstractRequest
{
    /** @var string XML template for the void request */
    protected $xmlTemplate ='
<fdggwsapi:FDGGWSApiOrderRequest xmlns:v1="http://secure.linkpt.net/fdggwsapi/schemas_us/v1"
    xmlns:fdggwsapi="http://secure.linkpt.net/fdggwsapi/schemas_us/fdggwsapi">
    <v1:Transaction>
        <v1:CreditCardTxType>
            <v1:Type>%txn_type%</v1:Type>
        </v1:CreditCardTxType>
        <v1:TransactionDetails>
            <v1:OrderId>%reference_no%</v1:OrderId>
            <v1:TDate>%tdate%</v1:TDate>
        </v1:TransactionDetails>
    </v1:Transaction>
</fdggwsapi:FDGGWSApiOrderRequest>
';

    /** @var string Transaction type */
    protected $txn_type = 'void';

    public function getData()
    {
        $data = parent::getData();

        $data['txn_type']           = $this->txn_type;

        $this->validate('transactionReference');

        // Fetch the original transaction reference and tdate from the
        // concatenated transactionReference returned by the purchase()
        // request.
        $transaction_reference      = $this->getTransactionReference();
        list($orderid, $tdate)      = explode('::', $transaction_reference);
        $data['reference_no']       = $orderid;
        $data['tdate']              = $tdate;

        return $data;
    }
}
