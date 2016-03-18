<?php
/**
 * Pin Capture Request
 */

namespace Omnipay\Pin\Message;

use Guzzle\Http\Message\RequestInterface;

/**
 * Pin Capture Request
 */
class CaptureRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('transactionReference');

        // Amount is the only possible optional parameter
        $amount = $this->getAmountInteger();

        return $amount ? array('amount' => $amount) : array();
    }

    public function sendData($data)
    {
        $httpResponse = $this->sendRequest(
            '/charges/' . $this->getTransactionReference() . '/capture',
            $data,
            RequestInterface::PUT
        );
        return $this->response = new CaptureResponse($this, $httpResponse->json());
    }
}
