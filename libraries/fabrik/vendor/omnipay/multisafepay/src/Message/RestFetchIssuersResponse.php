<?php
/**
 * MultiSafepay Rest Api Fetch Issuers Response.
 */

namespace Omnipay\MultiSafepay\Message;

use Omnipay\Common\Issuer;
use Omnipay\Common\Message\FetchIssuersResponseInterface;

/**
 * MultiSafepay Rest Api Fetch Issuers Response.
 *
 * This response class will be returned when
 * using the RestFetchIssuersRequest. And provides a
 * list of all possible issuers for the specified gateway.
 *
 * ### Example
 *
 * <code>
 *   $request = $gateway->fetchIssuers();
 *   $request->setPaymentMethod('IDEAL');
 *   $response = $request->send();
 *   $issuers = $response->getIssuers();
 *   print_r($issuers);
 * </code>
 */
class RestFetchIssuersResponse extends RestAbstractResponse implements FetchIssuersResponseInterface
{
    /**
     * Return available issuers as an associative array.
     *
     * @return \Omnipay\Common\Issuer[]
     */
    public function getIssuers()
    {
        $issuers = array();

        foreach ($this->data['data'] as $issuer) {
            $issuers[] = new Issuer(
                $issuer['code'],
                $issuer['description']
            );
        }

        return $issuers;
    }
}
