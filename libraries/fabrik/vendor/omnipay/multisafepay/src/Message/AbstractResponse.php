<?php
/**
 * MultiSafepay Abstract XML Api Response.
 */

namespace Omnipay\MultiSafepay\Message;

use Omnipay\Common\Message\AbstractResponse as BaseAbstractResponse;

/**
 * MultiSafepay Abstract XML Api Response.
 *
 * @deprecated This API is deprecated and will be removed in
 * an upcoming version of this package. Please switch to the Rest API.
 */
abstract class AbstractResponse extends BaseAbstractResponse
{
    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        if (isset($this->data->error)) {
            return (string) $this->data->error->description;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        if (isset($this->data->error)) {
            return (string) $this->data->error->code;
        }

        return null;
    }
}
