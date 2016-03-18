<?php
/**
 * MultiSafepay XML Api Purchase Response.
 */

namespace Omnipay\MultiSafepay\Message;

use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * MultiSafepay XML Api Purchase Response.
 *
 * @deprecated This API is deprecated and will be removed in
 * an upcoming version of this package. Please switch to the Rest API.
 */
class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTransactionReference()
    {
        if (isset($this->data->transaction->id)) {
            return (string)$this->data->transaction->id;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isRedirect()
    {
        if (isset($this->data->transaction->payment_url) ||
            isset($this->data->gatewayinfo->redirecturl)
        ) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectUrl()
    {
        if (isset($this->data->gatewayinfo->redirecturl)) {
            return (string)$this->data->gatewayinfo->redirecturl;
        }

        if (isset($this->data->transaction->payment_url)) {
            return (string) $this->data->transaction->payment_url;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectMethod()
    {
        return 'GET';
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectData()
    {
        return null;
    }
}
