<?php

namespace Omnipay\GoCardless\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

class CaptureResponse extends AbstractResponse
{

    public function isSuccessful()
    {
        return !isset($this->data['error']);
    }

    public function getMessage()
    {
        if (!$this->isSuccessful()) {
            return reset($this->data['error']);
        }

        return null;
    }
}
