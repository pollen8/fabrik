<?php
/**
 * MultiSafepay Rest Api Refund Request.
 */

namespace Omnipay\MultiSafepay\Message;

/**
 * MultiSafepay Rest Api Refund Request.
 *
 * The MultiSafepay API support refunds, meaning you can refund any
 * transaction to the customer. The fund will be deducted
 * from the MultiSafepay balance.
 *
 * The API also support partial refunds which means that only a
 * part of the amount will be refunded.
 *
 * When a transaction has been refunded the status will change to
 * "refunded". When the transaction has only been partial refunded the
 * status will change to "partial_refunded".
 *
 * ### Example
 *
 * <code>
 *    $request = $this->gateway->refund();
 *
 *    $request->setTransactionId('test-transaction');
 *    $request->setAmount('10.00');
 *    $request->setCurrency('eur');
 *    $request->setDescription('Test Refund');
 *
 *    $response = $request->send();
 *    var_dump($response->isSuccessful());
 * </code>
 */
class RestRefundRequest extends RestAbstractRequest
{
    /**
     * Get the required data for the API request.
     *
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        parent::getData();

        $this->validate('amount', 'currency', 'description', 'transactionId');

        return array(
            'amount' => $this->getAmountInteger(),
            'currency' => $this->getCurrency(),
            'description' => $this->getDescription(),
            'id' => $this->getTransactionId(),
            'type' => 'refund',
        );
    }

    /**
     * Send the request with specified data
     *
     * @param mixed $data
     * @return RestRefundResponse
     */
    public function sendData($data)
    {
        $httpResponse = $this->sendRequest(
            'POST',
            '/orders/' . $data['id'] . '/refunds',
            $data
        );

        $this->response = new RestRefundResponse(
            $this,
            $httpResponse
        );

        return $this->response;
    }
}
