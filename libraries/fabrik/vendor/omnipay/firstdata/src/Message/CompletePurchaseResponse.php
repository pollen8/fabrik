<?php
/**
 * First Data Connect Complete Purchase Response
 */

namespace Omnipay\FirstData\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * First Data Connect Complete Purchase Response
 */
class CompletePurchaseResponse extends AbstractResponse
{
    public function isSuccessful()
    {
        return isset($this->data['status']) && $this->data['status'] == 'APPROVED';
    }

    public function getTransactionId()
    {
        return isset($this->data['oid']) ? $this->data['oid'] : null;
    }

    public function getTransactionReference()
    {
        return isset($this->data['refnumber']) ? $this->data['refnumber'] : null;
    }

    public function getMessage()
    {
        return isset($this->data['status']) ? $this->data['status'] : null;
    }
}
