<?php
/**
 * MultiSafepay Abstract XML Api Request.
 */

namespace Omnipay\MultiSafepay\Message;

use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;

/**
 * Multisafepay Abstract XML Api Request.
 *
 * @deprecated This API is deprecated and will be removed in
 * an upcoming version of this package. Please switch to the Rest API.
 */
abstract class AbstractRequest extends BaseAbstractRequest
{
    /**
     * User Agent.
     *
     * This user agent will be sent with each API request.
     *
     * @var string
     */
    protected $userAgent = 'Omnipay';

    /**
     * Live API endpoint.
     *
     * This endpoint will be used when the test mode is disabled.
     *
     * @var string
     */
    protected $liveEndpoint = 'https://api.multisafepay.com/ewx/';

    /**
     * Test API endpoint.
     *
     * This endpoint will be used when the test mode is enabled.
     *
     * @var string
     */
    protected $testEndpoint = 'https://testapi.multisafepay.com/ewx/';

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
     * @return BaseAbstractRequest
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
     * @return BaseAbstractRequest
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
     * @return BaseAbstractRequest
     */
    public function setSiteCode($value)
    {
        return $this->setParameter('siteCode', $value);
    }

    /**
     * Get the API endpoint.
     *
     * @return string
     */
    public function getEndpoint()
    {
        if ($this->getTestMode()) {
            return $this->testEndpoint;
        }

        return $this->liveEndpoint;
    }

    /**
     * Get headers.
     *
     * @return array
     */
    protected function getHeaders()
    {
        return array(
            'User-Agent' => $this->userAgent,
        );
    }
}
