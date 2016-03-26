<?php
/**
 * First Data Payeezy Void Request
 */

namespace Omnipay\FirstData\Message;

/**
 * First Data Payeezy Void Request
 */
class PayeezyVoidRequest extends PayeezyRefundRequest
{
    protected $action = self::TRAN_TAGGEDVOID;
}
