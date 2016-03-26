<?php
/**
 * Pin Capture Response
 */

namespace Omnipay\Pin\Message;

/**
 * Pin Capture Response
 *
 * This is the response class for Pin Capture REST requests.
 *
 * @see \Omnipay\Pin\Gateway
 */
class CaptureResponse extends Response
{
    /**
     * Get Captured value
     *
     * This is used after an attempt to capture the charge is made.
     * If the capture was successful then it will return true.
     *
     * @return string
     */
    public function getCaptured()
    {
        if (isset($this->data['response']['captured'])) {
            return $this->data['response']['captured'];
        }
    }
}
