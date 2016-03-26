<?php
/**
 * MultiSafepay XML Api Gateway.
 */

namespace Omnipay\MultiSafepay;

use Omnipay\Common\AbstractGateway;

/**
 * MultiSafepay XML Api gateway.
 *
 * @deprecated This API is deprecated and will be removed in
 * an upcoming version of this package. Please switch to the Rest API.
 *
 * * ### Initialize gateway
 *
 * <code>
 *   // Create the gateway
 *   $gateway = Omnipay::create('MultiSafepay_Xml');
 *
 *   // Initialise the gateway
 *   $gateway->initialize(array(
 *       'apiKey' => 'API-KEY',
 *       'locale' => 'en',
 *       'testMode' => true, // Or false, when you want to use the production environment
 *   ));
 * </code>
 *
 * @link https://www.multisafepay.com/downloads/handleidingen/Handleiding_connect(ENG).pdf
 */
class XmlGateway extends AbstractGateway
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'MultiSafepay XML';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultParameters()
    {
        return array(
            'accountId' => '',
            'siteId' => '',
            'siteCode' => '',
            'testMode' => false,
        );
    }

    /**
     * Get the account identifier.
     *
     * @return mixed
     */
    public function getAccountId()
    {
        return $this->getParameter('accountId');
    }

    /**
     * Set the account identifier.
     *
     * @param $value
     * @return $this
     */
    public function setAccountId($value)
    {
        return $this->setParameter('accountId', $value);
    }

    /**
     * Get the site identifier.
     *
     * @return mixed
     */
    public function getSiteId()
    {
        return $this->getParameter('siteId');
    }

    /**
     * Set the site identifier.
     *
     * @param $value
     * @return $this
     */
    public function setSiteId($value)
    {
        return $this->setParameter('siteId', $value);
    }

    /**
     * Get the site code.
     *
     * @return mixed
     */
    public function getSiteCode()
    {
        return $this->getParameter('siteCode');
    }

    /**
     * Set the site code.
     *
     * @param $value
     * @return $this
     */
    public function setSiteCode($value)
    {
        return $this->setParameter('siteCode', $value);
    }

    /**
     * Retrieve payment methods active on the given MultiSafepay
     * account.
     *
     * @param array $parameters
     *
     * @return \Omnipay\MultiSafepay\Message\FetchPaymentMethodsRequest
     */
    public function fetchPaymentMethods(array $parameters = array())
    {
        return $this->createRequest(
            '\Omnipay\MultiSafepay\Message\FetchPaymentMethodsRequest',
            $parameters
        );
    }

    /**
     * Retrieve iDEAL issuers.
     *
     * @param array $parameters
     *
     * @return \Omnipay\MultiSafepay\Message\FetchIssuersRequest
     */
    public function fetchIssuers(array $parameters = array())
    {
        return $this->createRequest(
            '\Omnipay\MultiSafepay\Message\FetchIssuersRequest',
            $parameters
        );
    }

    /**
     * Create Purchase request.
     *
     * @param array $parameters
     *
     * @return \Omnipay\MultiSafepay\Message\PurchaseRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest(
            '\Omnipay\MultiSafepay\Message\PurchaseRequest',
            $parameters
        );
    }

    /**
     * Complete purchase request.
     *
     * @param array $parameters
     *
     * @return \Omnipay\MultiSafepay\Message\CompletePurchaseRequest
     */
    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest(
            '\Omnipay\MultiSafepay\Message\CompletePurchaseRequest',
            $parameters
        );
    }
}
