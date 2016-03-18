<?php
/**
 * First Data Connect Purchase Request
 */

namespace Omnipay\FirstData\Message;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Exception\InvalidCreditCardException;

/**
 * First Data Connect Purchase Request
 */
class PurchaseRequest extends AbstractRequest
{
    protected $liveEndpoint = 'https://www.ipg-online.com/connect/gateway/processing';
    protected $testEndpoint = 'https://test.ipg-online.com/connect/gateway/processing';

    protected function getDateTime()
    {
        return date("Y:m:d-H:i:s");
    }

    /**
     * Set Store ID
     *
     * Calls to the Connect Gateway API are secured with a store ID and
     * shared secret.
     *
     * @return PurchaseRequest provides a fluent interface
     */
    public function setStoreId($value)
    {
        return $this->setParameter('storeId', $value);
    }

    /**
     * Get Store ID
     *
     * Calls to the Connect Gateway API are secured with a store ID and
     * shared secret.
     *
     * @return string
     */
    public function getStoreId()
    {
        return $this->getParameter('storeId');
    }

    /**
     * Set Shared Secret
     *
     * Calls to the Connect Gateway API are secured with a store ID and
     * shared secret.
     *
     * @return PurchaseRequest provides a fluent interface
     */
    public function setSharedSecret($value)
    {
        return $this->setParameter('sharedSecret', $value);
    }

    /**
     * Get Shared Secret
     *
     * Calls to the Connect Gateway API are secured with a store ID and
     * shared secret.
     *
     * @return string
     */
    public function getSharedSecret()
    {
        return $this->getParameter('sharedSecret');
    }

    public function setHostedDataId($value)
    {
        return $this->setParameter('hostedDataId', $value);
    }

    public function getHostedDataId()
    {
        return $this->getParameter('hostedDataId');
    }

    public function setCustomerId($value)
    {
        return $this->setParameter('customerId', $value);
    }

    public function getCustomerId()
    {
        return $this->getParameter('customerId');
    }

    public function getData()
    {
        $this->validate('amount', 'card');

        $data = array();
        $data['storename'] = $this->getStoreId();
        $data['txntype'] = 'sale';
        $data['timezone'] = 'GMT';
        $data['chargetotal'] = $this->getAmount();
        $data['txndatetime'] = $this->getDateTime();
        $data['hash'] = $this->createHash($data['txndatetime'], $data['chargetotal']);
        $data['currency'] = $this->getCurrencyNumeric();
        $data['mode'] = 'payonly';
        $data['full_bypass'] = 'true';
        $data['oid'] = $this->getParameter('transactionId');

        // If no hosted data, or a number is passed, validate the whole card
        if (is_null($this->getHostedDataId()) || !is_null($this->getCard()->getNumber())) {
            $this->getCard()->validate();
        } elseif (is_null($this->getCard()->getCvv())) {
            // Else we only require the cvv when using hosted data
            throw new InvalidCreditCardException("The CVV parameter is required when using hosteddataid");
        }

        $data['cardnumber'] = $this->getCard()->getNumber();
        $data['cvm'] = $this->getCard()->getCvv();
        $data['expmonth'] = $this->getCard()->getExpiryDate('m');
        $data['expyear'] = $this->getCard()->getExpiryDate('y');

        $data['bname'] = $this->getCard()->getBillingName();
        $data['baddr1'] = $this->getCard()->getBillingAddress1();
        $data['baddr2'] = $this->getCard()->getBillingAddress2();
        $data['bcity'] = $this->getCard()->getBillingCity();
        $data['bstate'] = $this->getCard()->getBillingState();
        $data['bcountry'] = $this->getCard()->getBillingCountry();
        $data['bzip'] = $this->getCard()->getBillingPostcode();

        $data['sname'] = $this->getCard()->getShippingName();
        $data['saddr1'] = $this->getCard()->getShippingAddress1();
        $data['saddr2'] = $this->getCard()->getShippingAddress2();
        $data['scity'] = $this->getCard()->getShippingCity();
        $data['sstate'] = $this->getCard()->getShippingState();
        $data['scountry'] = $this->getCard()->getShippingCountry();
        $data['szip'] = $this->getCard()->getShippingPostcode();

        $data['phone'] = $this->getCard()->getPhone();
        $data['email'] = $this->getCard()->getEmail();

        $data['responseSuccessURL'] = $this->getParameter('returnUrl');
        $data['responseFailURL'] = $this->getParameter('returnUrl');

        $data['customerid'] = $this->getCustomerId();

        $data['hosteddataid'] = $this->getHostedDataId();

        return $data;
    }

    /**
     * Returns a SHA-1 hash of the transaction data.
     *
     * @param $dateTime
     * @param $amount
     * @return string
     */
    public function createHash($dateTime, $amount)
    {
        $storeId = $this->getStoreId();
        $sharedSecret = $this->getSharedSecret();
        $currency = $this->getCurrencyNumeric();
        $stringToHash = $storeId . $dateTime . $amount . $currency . $sharedSecret;
        $ascii = bin2hex($stringToHash);

        return sha1($ascii);
    }

    public function sendData($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }

    public function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }
}
