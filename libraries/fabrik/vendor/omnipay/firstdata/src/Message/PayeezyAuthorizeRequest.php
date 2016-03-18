<?php
/**
 * First Data Payeezy Authorize Request
 */

namespace Omnipay\FirstData\Message;

/**
 * First Data Payeezy Authorize Request
 */
class PayeezyAuthorizeRequest extends PayeezyPurchaseRequest
{
    protected $action = self::TRAN_PREAUTH;
}
