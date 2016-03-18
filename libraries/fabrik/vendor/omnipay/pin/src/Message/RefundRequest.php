<?php
/**
 * Pin Refund Request
 */

namespace Omnipay\Pin\Message;

/**
 * Pin Refund Request
 *
 * The refunds API allows you to refund a charge, and retrieve the details
 * of previous refunds.
 *
 * This message creates a new refund, and returns its details.
 *
 * Example -- this example assumes that the charge has been successful and the
 * transaction ID is stored in $sale_id.  See PurchaseRequest for the first part
 * of this transaction.
 *
 * <code>
 *   // Do a refund transaction on the gateway
 *   $transaction = $gateway->refund(array(
 *       'transactionReference'     => $sale_id,
 *       'amount'                   => '10.00',
 *   ));
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *       echo "Refund transaction was successful!\n";
 *       $refund_id = $response->getTransactionReference();
 *       echo "Transaction reference = " . $refund_id . "\n";
 *   }
 * </code>
 *
 * @see \Omnipay\Pin\Gateway
 * @link https://pin.net.au/docs/api/refunds#post-refunds
 */
class RefundRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('transactionReference', 'amount');

        $data = array();
        $data['amount'] = $this->getAmountInteger();

        return $data;
    }

    public function sendData($data)
    {
        $httpResponse = $this->sendRequest('/charges/' . $this->getTransactionReference() . '/refunds', $data);

        return $this->response = new Response($this, $httpResponse->json());
    }
}
