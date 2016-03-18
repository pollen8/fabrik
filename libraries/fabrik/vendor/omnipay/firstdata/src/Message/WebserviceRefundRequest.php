<?php
/**
 * First Data Webservice Refund Request
 */
namespace Omnipay\FirstData\Message;

/**
 * First Data Webservice Refund Request
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
 * // Do a refund transaction on the gateway.  This assumes that a purchase
 * // request has been successful, and that the transactionReference from the
 * // purchase request is stored in $sale_id.
 * $transaction = $gateway->refund(array(
 *     'transactionReference'      => $sale_id,
 *     'amount'                    => 5.00,
 * ));
 * $response = $transaction->send();
 * if ($response->isSuccessful()) {
 *     echo "Refund transaction was successful!\n";
 *     $sale_id = $response->getTransactionReference();
 *     echo "Transaction reference = " . $sale_id . "\n";
 * }
 * </code>
 *
 * ### Quirks
 *
 * In the case of a captured authorization, the transaction reference passed
 * to refund() must be the transaction reference returned from the capture()
 * request and not the transaction reference from the original authorize()
 * request.
 */
class WebserviceRefundRequest extends WebserviceAbstractRequest
{
    /** @var string XML template for the refund request */
    protected $xmlTemplate ='
<fdggwsapi:FDGGWSApiOrderRequest xmlns:v1="http://secure.linkpt.net/fdggwsapi/schemas_us/v1"
    xmlns:fdggwsapi="http://secure.linkpt.net/fdggwsapi/schemas_us/fdggwsapi">
    <v1:Transaction>
        <v1:CreditCardTxType>
            <v1:Type>%txn_type%</v1:Type>
        </v1:CreditCardTxType>
        <v1:Payment>
            <v1:ChargeTotal>%amount%</v1:ChargeTotal>
        </v1:Payment>
        <v1:TransactionDetails>
            <v1:OrderId>%reference_no%</v1:OrderId>
        </v1:TransactionDetails>
    </v1:Transaction>
</fdggwsapi:FDGGWSApiOrderRequest>
';

    /** @var string Transaction type */
    protected $txn_type = 'return';

    public function getData()
    {
        $data = parent::getData();

        $data['txn_type']           = $this->txn_type;

        $this->validate('amount', 'transactionReference');

        // Fetch the original transaction reference and tdate from the
        // concatenated transactionReference returned by the purchase()
        // request.
        $transaction_reference      = $this->getTransactionReference();
        list($orderid, $tdate)      = explode('::', $transaction_reference);
        $data['reference_no']       = $orderid;
        $data['tdate']              = $tdate;

        $data['amount']             = $this->getAmount();

        return $data;
    }
}
