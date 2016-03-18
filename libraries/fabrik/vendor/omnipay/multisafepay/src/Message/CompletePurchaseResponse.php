<?php
/**
 * MultiSafepay XML Api Complete Purchase Response.
 */

namespace Omnipay\MultiSafepay\Message;

/**
 * MultiSafepay XML Api Complete Purchase Response.
 *
 * @deprecated This API is deprecated and will be removed in
 * an upcoming version of this package. Please switch to the Rest API.
 */
class CompletePurchaseResponse extends AbstractResponse
{
    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return $this->getPaymentStatus() === 'completed';
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionReference()
    {
        if (isset($this->data->transaction->id)) {
            return (string) $this->data->transaction->id;
        }
    }

    /**
     * Is the payment created, but uncompleted?
     *
     * @return boolean
     */
    public function isInitialized()
    {
        return $this->getPaymentStatus() === 'initialized';
    }

    /**
     * Is the payment created, but not yet exempted (credit cards)?
     *
     * @return boolean
     */
    public function isUncleared()
    {
        return $this->getPaymentStatus() === 'uncleared';

    }

    /**
     * Is the payment canceled?
     *
     * @return boolean
     */
    public function isCanceled()
    {
        return $this->getPaymentStatus() === 'canceled';
    }

    /**
     * Is the payment rejected?
     *
     * @return boolean
     */
    public function isRejected()
    {
        return $this->getPaymentStatus() === 'declined';
    }

    /**
     * Is the payment refunded?
     *
     * @return boolean
     */
    public function isRefunded()
    {
        return $this->getPaymentStatus() === 'refunded';
    }

    /**
     * Is the payment expired?
     *
     * @return boolean
     */
    public function isExpired()
    {
        return $this->getPaymentStatus() === 'expired';
    }

    /**
     * Get raw payment status.
     *
     * @return null|string
     */
    public function getPaymentStatus()
    {
        if (isset($this->data->ewallet->status)) {
            return (string)$this->data->ewallet->status;
        }
    }
}
