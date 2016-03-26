<?php
/**
 * MultiSafepay Rest Api Complete Purchase Request.
 */

namespace Omnipay\MultiSafepay\Message;

/**
 * MultiSafepay Rest Api Complete Purchase Request.
 *
 * ### Example
 *
 * <code>
 *   $transaction = $gateway->completePurchase();
 *   $transaction->setTransactionId($transactionId);
 *   $response = $transaction->send();
 *   print_r($response->getData());
 * </code>
 *
 */
class RestCompletePurchaseRequest extends RestFetchTransactionRequest
{
    /**
     * Get the required data from the API request.
     *
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        $this->validate('transactionId');

        $transactionId = $this->getTransactionId();

        return compact('transactionId');
    }

    /**
     * Send the API request.
     *
     * @param mixed $data
     * @return RestCompletePurchaseResponse
     */
    public function sendData($data)
    {
        $httpResponse = $this->sendRequest(
            'get',
            '/orders/' . $data['transactionId']
        );

        $this->response = new RestCompletePurchaseResponse(
            $this,
            $httpResponse->json()
        );

        return $this->response;
    }
}
