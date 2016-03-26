<?php
/**
 * MultiSafepay Rest Api Fetch Transaction Response.
 */
namespace Omnipay\MultiSafepay\Message;

/**
 * MultiSafepay Rest Api Fetch Transaction Response.
 *
 * To get information about a previous processed transaction, MultiSafepay provides
 * the /orders/{order_id} resource. This resource can be used to query the details
 * about a specific transaction.
 *
 * <code>
 *   // Fetch the transaction.
 *   $transaction = $gateway->fetchTransaction();
 *   $transaction->setTransactionId($transactionId);
 *   $response = $transaction->send();
 *   print_r($response->getData());
 * </code>
 *
 * @link https://www.multisafepay.com/documentation/doc/API-Reference
 */
class RestFetchTransactionResponse extends RestAbstractResponse
{
    /**
     * Is the payment created, but uncompleted?
     *
     * @return boolean
     */
    public function isInitialized()
    {
        return $this->getPaymentStatus() == 'initialized';
    }

    /**
     * Is the payment created, but not yet exempted (credit cards)?
     *
     * @return boolean
     */
    public function isUncleared()
    {
        return $this->getPaymentStatus() == 'uncleared';
    }

    /**
     * Is the payment canceled?
     *
     * @return boolean
     */
    public function isCanceled()
    {
        return $this->getPaymentStatus() == 'canceled';
    }

    /**
     * Is the payment rejected?
     *
     * @return boolean
     */
    public function isDeclined()
    {
        return $this->getPaymentStatus() == 'declined';
    }

    /**
     * Is the payment refunded?
     *
     * @return boolean
     */
    public function isRefunded()
    {
        if ($this->getPaymentStatus() == 'refunded' ||
            $this->getPaymentStatus() == 'chargedback'
        ) {
            return true;
        }

        return false;
    }

    /**
     * Is the payment expired?
     *
     * @return boolean
     */
    public function isExpired()
    {
        return $this->getPaymentStatus() == 'expired';
    }

    /**
     * Get raw payment status.
     *
     * @return null|string
     */
    public function getPaymentStatus()
    {
        if (isset($this->data['data']['status'])) {
            return $this->data['data']['status'];
        }
    }
}
