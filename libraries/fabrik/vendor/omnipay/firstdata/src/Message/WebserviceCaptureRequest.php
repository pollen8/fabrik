<?php
/**
 * First Data Webservice Capture Request
 */
namespace Omnipay\FirstData\Message;

/**
 * First Data Webservice Capture Request
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
 * // Do a capture transaction on the gateway.  This assumes that an authorize
 * // request has been successful, and that the transactionReference from the
 * // authorize request is stored in $sale_id.
 * $transaction = $gateway->capture(array(
 *     'transactionReference'      => $sale_id,
 * ));
 * $response = $transaction->send();
 * if ($response->isSuccessful()) {
 *     echo "Capture transaction was successful!\n";
 *     $sale_id = $response->getTransactionReference();
 *     echo "Transaction reference = " . $sale_id . "\n";
 * }
 * </code>
 */
class WebserviceCaptureRequest extends WebserviceAbstractRequest
{
    /** @var string XML template for the capture request */
    protected $xmlTemplate ='
<fdggwsapi:FDGGWSApiOrderRequest xmlns:v1="http://secure.linkpt.net/fdggwsapi/schemas_us/v1"
    xmlns:fdggwsapi="http://secure.linkpt.net/fdggwsapi/schemas_us/fdggwsapi">
    <v1:Transaction>
        <v1:CreditCardTxType>
            <v1:Type>%txn_type%</v1:Type>
        </v1:CreditCardTxType>
        <v1:TransactionDetails>
            <v1:OrderId>%reference_no%</v1:OrderId>
        </v1:TransactionDetails>
    </v1:Transaction>
</fdggwsapi:FDGGWSApiOrderRequest>
';

    /** @var string Transaction type */
    protected $txn_type = 'postAuth';

    public function getData()
    {
        $data = parent::getData();

        $data['txn_type']           = $this->txn_type;

        $this->validate('transactionReference');

        // Fetch the original transaction reference and tdate from the
        // concatenated transactionReference returned by the authorize()
        // request.
        $transaction_reference      = $this->getTransactionReference();
        list($orderid, $tdate)      = explode('::', $transaction_reference);
        $data['reference_no']       = $orderid;

        return $data;
    }
}
