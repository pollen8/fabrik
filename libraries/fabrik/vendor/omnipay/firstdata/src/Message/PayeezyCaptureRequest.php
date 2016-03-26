<?php
/**
 * First Data Payeezy Capture Request
 */

namespace Omnipay\FirstData\Message;

/**
 * First Data Payeezy Capture Request
 */
class PayeezyCaptureRequest extends PayeezyRefundRequest
{
    protected $action = self::TRAN_TAGGEDPREAUTHCOMPLETE;
}
