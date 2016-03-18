<?php

namespace Omnipay\Payflow\Message;

/**
 * Payflow Void Request
 */
class VoidRequest extends AuthorizeRequest
{
    protected $action = 'V';

    /**
     * Void prevents transactions from being settled.
     *
     * @return array ... the data Payflow needs to void a transaction
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        $this->validate('transactionReference');

        $data = $this->getBaseData();
        $data['ORIGID'] = $this->getTransactionReference();

        return $data;
    }
}
