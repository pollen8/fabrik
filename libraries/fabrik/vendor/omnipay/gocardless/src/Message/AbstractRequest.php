<?php

namespace Omnipay\GoCardless\Message;

use Omnipay\GoCardless\Gateway;

/**
 * GoCardless Abstract Request
 */
abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    protected $liveEndpoint = 'https://gocardless.com';
    protected $testEndpoint = 'https://sandbox.gocardless.com';

    public function getAppId()
    {
        return $this->getParameter('appId');
    }

    public function setAppId($value)
    {
        return $this->setParameter('appId', $value);
    }

    public function getAppSecret()
    {
        return $this->getParameter('appSecret');
    }

    public function setAppSecret($value)
    {
        return $this->setParameter('appSecret', $value);
    }

    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    public function getAccessToken()
    {
        return $this->getParameter('accessToken');
    }

    public function setAccessToken($value)
    {
        return $this->setParameter('accessToken', $value);
    }

    public function getChargeCustomerAt()
    {
        return $this->getParameter('chargeCustomerAt');
    }

    public function setChargeCustomerAt($value)
    {
        return $this->setParameter('chargeCustomerAt', $value);
    }

    public function getState()
    {
        return $this->getParameter('state');
    }

    public function setState($value)
    {
        return $this->setParameter('state', $value);
    }

    public function getIntervalLength()
    {
        return $this->getParameter('intervalLength');
    }

    public function setIntervalLength($value)
    {
        return $this->setParameter('intervalLength', $value);
    }

    public function getIntervalUnit()
    {
        return $this->getParameter('intervalUnit');
    }

    public function setIntervalUnit($value)
    {
        return $this->setParameter('intervalUnit', $value);
    }

    public function getCalendarInterval()
    {
        return $this->getParameter('calendarInterval');
    }

    public function setCalendarInterval($value)
    {
        return $this->setParameter('calendarInterval', $value);
    }

    public function getSetupFee()
    {
        return $this->getParameter('setupFee');
    }

    public function setSetupFee($value)
    {
        return $this->setParameter('setupFee', $value);
    }

    public function getPreAuthExpire()
    {
        return $this->getParameter('preAuthExpire');
    }

    public function setPreAuthExpire($value)
    {
        return $this->setParameter('preAuthExpire', $value);
    }

    public function getIntervalCount()
    {
        return $this->getParameter('intervalCount');
    }

    public function setIntervalCount($value)
    {
        return $this->setParameter('intervalCount', $value);
    }

    /**
     * Generate a signature for the data array
     */
    public function generateSignature($data)
    {
        return hash_hmac('sha256', Gateway::generateQueryString($data), $this->getAppSecret());
    }

    public function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }

    /**
     * Generate a nonce for each request
     */
    protected function generateNonce()
    {
        $nonce = '';
        for ($i = 0; $i < 64; $i++) {
            // append random ASCII character
            $nonce .= chr(mt_rand(33, 126));
        }

        return base64_encode($nonce);
    }
}
