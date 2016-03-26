<?php
/**
 * MultiSafepay XML Api Fetch Issuers Response.
 */

namespace Omnipay\MultiSafepay\Message;

/**
 * MultiSafepay XML Api Fetch Issuers Response.
 *
 * @deprecated This API is deprecated and will be removed in
 * an upcoming version of this package. Please switch to the Rest API.
 */
class FetchIssuersResponse extends AbstractResponse
{
    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return isset($this->data->issuers);
    }

    /**
     * Return available issuers as an associative array.
     *
     * @return array
     */
    public function getIssuers()
    {
        $result = array();

        foreach ($this->data->issuers->issuer as $issuer) {
            $result[(string) $issuer->code] = (string) $issuer->description;
        }

        return $result;
    }
}
