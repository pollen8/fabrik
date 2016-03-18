<?php
/**
 * MultiSafepay XML Api Fetch Payment Methods Request.
 */
namespace Omnipay\MultiSafepay\Message;

use SimpleXMLElement;

/**
 * MultiSafepay XML Api Fetch Payment Methods Request.
 *
 * @deprecated This API is deprecated and will be removed in
 * an upcoming version of this package. Please switch to the Rest API.
 */
class FetchPaymentMethodsRequest extends AbstractRequest
{
    /**
     * Get the country.
     *
     * @return mixed
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
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><gateways/>');
        $data->addAttribute('ua', $this->userAgent);

        $merchant = $data->addChild('merchant');
        $merchant->addChild('account', $this->getAccountId());
        $merchant->addChild('site_id', $this->getSiteId());
        $merchant->addChild('site_secure_code', $this->getSiteCode());

        $customer = $data->addChild('customer');
        $customer->addChild('country', $this->getCountry());

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function sendData($data)
    {
        $httpResponse = $this->httpClient->post(
            $this->getEndpoint(),
            $this->getHeaders(),
            $data->asXML()
        )->send();

        $this->response = new FetchPaymentMethodsResponse(
            $this,
            $httpResponse->xml()
        );

        return $this->response;
    }
}
