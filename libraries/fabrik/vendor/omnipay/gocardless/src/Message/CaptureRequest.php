<?php
/**
 * GoCardless Capture Request
 */

namespace Omnipay\GoCardless\Message;

use Omnipay\GoCardless\Gateway;

/**
 * GoCardless Capture Request - this captures an existing pre-authorization.
 *
 * All you need for this method is the pre_authorization_id that we expect to be the transactionReference.
 * This is a very similar call to just creating a standard bill - but doesnt require a hand off to the
 * payment provider.
 *
 * @see AuthorizeRequest
 * @link https://developer.gocardless.com/#create-a-bill-under-a-pre-auth
 */
class CaptureRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('amount', 'transactionReference');

        $data = array();

        $bill = array();
        $bill['amount'] = $this->getAmount();
        $bill['pre_authorization_id'] = $this->getTransactionReference();
        $bill['name'] = $this->getDescription();
        $bill['charge_customer_at'] = $this->getChargeCustomerAt();

        $data['bill'] = $bill;

        return $data;
    }

    public function sendData($data)
    {
        $httpRequest = $this->httpClient->post(
            $this->getEndpoint().'/api/v1/bills',
            array('Accept' => 'application/json'),
            Gateway::generateQueryString($data)
        );

        $httpResponse = $httpRequest->setHeader('Authorization', 'bearer '.$this->getAccessToken())->send();

        return $this->response = new CaptureResponse($this, $httpResponse->json());
    }
}
