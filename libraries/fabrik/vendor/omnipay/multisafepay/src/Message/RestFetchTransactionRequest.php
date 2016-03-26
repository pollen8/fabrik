<?php
/**
 * MultiSafepay Rest Api Fetch Transaction Request.
 */

namespace Omnipay\MultiSafepay\Message;

/**
 * MultiSafepay Rest Api Fetch Transaction Request.
 *
 * To get information about a previous processed transaction, MultiSafepay provides
 * the /orders/{order_id} resource. This resource can be used to query the details
 * about a specific transaction.
 *
 * <code>
 *   // Fetch the transaction.
 *   $transaction = $gateway->fetchTransaction();
 *   $transaction->setTransactionId($transactionId);
 *   $response = $transaction->send();
 *   print_r($response->getData());
 * </code>
 *
 * @link https://www.multisafepay.com/documentation/doc/API-Reference
 */
class RestFetchTransactionRequest extends RestAbstractRequest
{
    /**
     * Get the required data which is needed
     * to execute the API request.
     *
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        parent::getData();

        $this->validate('transactionId');

        $transactionId = $this->getTransactionId();

        return compact('transactionId');
    }

    /**
     * Execute the API request.
     *
     * @param mixed $data
     * @return RestFetchTransactionResponse
     */
    public function sendData($data)
    {
        $httpResponse = $this->sendRequest(
            'get',
            '/orders/' . $data['transactionId']
        );

        $this->response = new RestFetchTransactionResponse(
            $this,
            $httpResponse->json()
        );

        return $this->response;
    }
}
