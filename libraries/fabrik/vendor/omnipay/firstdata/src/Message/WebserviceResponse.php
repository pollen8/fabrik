<?php
/**
 * First Data Webservice Response
 */

namespace Omnipay\FirstData\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * First Data Webservice Response
 *
 * ### Quirks
 *
 * This is a SOAP gateway and there are numerous XML issues with the data transfer
 * and receipt.  See the gateway class for details.
 */
class WebserviceResponse extends AbstractResponse
{
    /**
     * Get an item from the internal data array
     *
     * This is a short cut function to ensure that we test that the item
     * exists in the data array before we try to retrieve it.
     *
     * @param $itemname
     * @return mixed|null
     */
    public function getDataItem($itemname)
    {
        if (isset($this->data[$itemname])) {
            return $this->data[$itemname];
        }

        return null;
    }

    public function isSuccessful()
    {
        return ($this->getDataItem('TRANSACTIONRESULT') == 'APPROVED') ? true : false;
    }

    /**
     * Get the transaction reference
     *
     * Because refunding or voiding a transaction requires both the order ID
     * and the TDATE, we concatenate them together to make the transaction
     * reference.
     *
     * @return string
     */
    public function getTransactionReference()
    {
        return $this->getDataItem('ORDERID')  . '::' . $this->getDataItem('TDATE');
    }

    public function getMessage()
    {
        return $this->getDataItem('ERRORMESSAGE');
    }

    public function getCode()
    {
        return $this->getDataItem('TRANSACTIONRESULT');
    }
}
