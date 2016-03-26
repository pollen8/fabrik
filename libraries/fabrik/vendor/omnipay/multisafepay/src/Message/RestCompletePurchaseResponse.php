<?php
/**
 * MultiSafepay Rest Api Complete Purchase Response.
 */
namespace Omnipay\MultiSafepay\Message;

/**
 * MultiSafepay Rest Api Complete Purchase Response.
 *
 *
 * ### Example
 *
 * <code>
 *   $transaction = $gateway->completePurchase();
 *   $transaction->setTransactionId($transactionId);
 *   $response = $transaction->send();
 *   print_r($response->getData());
 * </code>
 *
 */
class RestCompletePurchaseResponse extends RestFetchTransactionResponse
{
    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return $this->getPaymentStatus() == 'completed';
    }
}
