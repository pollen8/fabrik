<?php
/**
 * MultiSafepay Rest Api Fetch Payments Methods Request.
 */

namespace Omnipay\MultiSafepay\Message;

/**
 * MultiSafepay Rest Api Fetch Payments Methods Request.
 *
 * The MultiSafepay API supports multiple payment gateways, such as
 * iDEAL, Paypal or CreditCard. This request provides a list
 * of all supported payment methods.
 *
 * ### Example
 *
 * <code>
 *    $request = $gateway->fetchPaymentMethods();
 *    $response = $request->send();
 *    $paymentMethods = $response->getPaymentMethods();
 *    print_r($paymentMethods);
 * </code>
 *
 * @link https://www.multisafepay.com/documentation/doc/API-Reference
 */
class RestFetchPaymentMethodsRequest extends RestAbstractRequest
{
    /**
     * Get the country.
     *
     * @return string|null
     */
    public function getCountry()
    {
        return $this->getParameter('country');
    }

    /**
     * Set the country.
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setCountry($value)
    {
        return $this->setParameter('country', $value);
    }

    /**
     * Get the required data for the API request.
     *
     * @return array
     */
    public function getData()
    {
        parent::getData();

        $data = array(
            'amount' => $this->getAmountInteger(),
            'country' => $this->getCountry(),
            'currency' => $this->getCurrency(),
        );

        return array_filter($data);
    }

    /**
     * Execute the API request.
     *
     * @param mixed $data
     * @return RestFetchPaymentMethodsResponse
     */
    public function sendData($data)
    {
        $httpResponse = $this->sendRequest('GET', '/gateways', $data);

        $this->response = new RestFetchPaymentMethodsResponse(
            $this,
            $httpResponse->json()
        );

        return $this->response;
    }
}
