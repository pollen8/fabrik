<?php
/**
 * MultiSafepay Rest Api Fetch Issuers Request.
 */

namespace Omnipay\MultiSafepay\Message;

/**
 * MultiSafepay Rest Api Fetch Issuers Request.
 *
 * Some payment providers require you to specify a issuer.
 * This request provides a list of all possible issuers
 * for the specified gateway.
 *
 * Currently IDEAL is the only provider which requires an issuer.
 *
 * <code>
 *   $request = $gateway->fetchIssuers();
 *   $request->setPaymentMethod('IDEAL');
 *   $response = $request->send();
 *   $issuers = $response->getIssuers();
 *   print_r($issuers);
 * </code>
 *
 * @link https://www.multisafepay.com/documentation/doc/API-Reference
 */
class RestFetchIssuersRequest extends RestAbstractRequest
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

        $this->validate('paymentMethod');

        $paymentMethod = $this->getPaymentMethod();

        return compact('paymentMethod');
    }

    /**
     * Execute the API request.
     *
     * @param mixed $data
     * @return FetchIssuersResponse
     */
    public function sendData($data)
    {
        $httpResponse = $this->sendRequest(
            'GET',
            '/issuers/' . $data['paymentMethod']
        );

        $this->response = new RestFetchIssuersResponse(
            $this,
            $httpResponse->json()
        );

        return $this->response;
    }
}
