<?php
/**
 * MultiSafepay Rest Api Fetch Payment Methods Response.
 */

namespace Omnipay\MultiSafepay\Message;

use Omnipay\Common\Message\FetchPaymentMethodsResponseInterface;
use Omnipay\Common\PaymentMethod;

/**
 * MultiSafepay Rest Api Fetch Payment Methods Response.
 *
 * The MultiSafepay API supports multiple payment gateways, such as
 * iDEAL, Paypal or CreditCard.
 *
 * This response class will be returned when using the
 * RestFetchPaymentMethodsRequest. And provides a list of
 * all supported payment methods.
 *
 * ### Example
 *
 * <code>
 *    $request = $gateway->fetchPaymentMethods();
 *    $response = $request->send();
 *    $paymentMethods = $response->getPaymentMethods();
 *    print_r($paymentMethods);
 * </code>
 */
class RestFetchPaymentMethodsResponse extends RestAbstractResponse implements FetchPaymentMethodsResponseInterface
{
    /**
     * Get the returned list of payment methods.
     *
     * These represent separate payment methods which the user must choose between.
     *
     * @return \Omnipay\Common\PaymentMethod[]
     */
    public function getPaymentMethods()
    {
        $paymentMethods = array();

        foreach ($this->data['data'] as $method) {
            $paymentMethods[] = new PaymentMethod(
                $method['id'],
                $method['description']
            );
        }

        return $paymentMethods;
    }
}
