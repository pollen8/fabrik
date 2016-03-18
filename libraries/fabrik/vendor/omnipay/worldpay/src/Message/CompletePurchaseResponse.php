<?php

namespace Omnipay\WorldPay\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * WorldPay Complete Purchase Response
 */
class CompletePurchaseResponse extends AbstractResponse
{
    public function isSuccessful()
    {
        return isset($this->data['transStatus']) && 'Y' === $this->data['transStatus'];
    }

    public function isCancelled()
    {
        return isset($this->data['transStatus']) && 'C' === $this->data['transStatus'];
    }

    public function getTransactionReference()
    {
        return isset($this->data['transId']) ? $this->data['transId'] : null;
    }

    public function getMessage()
    {
        return isset($this->data['rawAuthMessage']) ? $this->data['rawAuthMessage'] : null;
    }
    
    /**
     * Optional step: Redirect the customer back to your own domain.
     *
     * This is achieved by returning a HTML string containing a meta-redirect which is displayed by WorldPay
     * to the customer. This is far from ideal, but apparently (according to their support) this is the only
     * method currently available.
     *
     * @param string $returnUrl The URL to forward the customer to.
     * @param string|null $message   Optional message to display to the customer before they are redirected.
     */
    public function confirm($returnUrl, $message = null)
    {
        if (empty($message)) {
            $message = 'Thank you, your transaction has been processed. You are being redirected...';
        }
        echo '<meta http-equiv="refresh" content="2;url='.$returnUrl.'" /><p>'.$message.'</p>';
        exit;
    }
}
