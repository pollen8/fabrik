<?php
/**
 * MultiSafepay Rest Api Refund Response.
 */
namespace Omnipay\MultiSafepay\Message;

/**
 * MultiSafepay Rest Api Refund Response.
 *
 * The MultiSafepay API support refunds, meaning you can refund any
 * transaction to the customer. The fund will be deducted
 * from the MultiSafepay balance.
 *
 * The API also support partial refunds which means that only a
 * part of the amount will be refunded.
 *
 * When a transaction has been refunded the status will change to
 * "refunded". When the transaction has only been partial refunded the
 * status will change to "partial_refunded".
 *
 * ### Example
 *
 * <code>
 *    $request = $this->gateway->refund();
 *
 *    $request->setTransactionId('test-transaction');
 *    $request->setAmount('10.00');
 *    $request->setCurrency('eur');
 *    $request->setDescription('Test Refund');
 *
 *    $response = $request->send();
 *    var_dump($response->isSuccessful());
 * </code>
 */
class RestRefundResponse extends RestAbstractResponse
{

}
