<?php
/**
 * GoCardless Authorize Request
 */

namespace Omnipay\GoCardless\Message;

/**
 * GoCardless Authorize Request
 *
 * Use this method to create a pre- authorization for a charge. Because we are dealing with direct debits we can do
 * a few things that other providers don't or can't do. It also means we some extra parameters.
 *
 * Required parameters:
 * -  amount - we're using this as the max_amount for the authorization,
 *              you have the ability to capture less then this later
 * - intervalLength - the number of intervalUnits that make up an interval (1 month) would be monthly charge
 * - intervalUnit - string of `day` `week` or `month`
 *
 * Optional Parameters:
 * see documentation.
 *
 *
 * @see \Omnipay\GoCardless\Gateway
 * @link https://developer.gocardless.com/#create-a-pre-auth
 */
class AuthorizeRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('amount', 'intervalLength', 'intervalUnit');

        $data = array();
        //Required Items from the API
        $data['client_id'] = $this->getAppId();
        $data['nonce'] = $this->generateNonce();
        $data['timestamp'] = gmdate('Y-m-d\TH:i:s\Z');

        $data['pre_authorization']['max_amount'] = $this->getAmount();
        $data['pre_authorization']['interval_length'] = $this->getIntervalLength();
        $data['pre_authorization']['interval_unit'] = $this->getIntervalUnit();
        $data['pre_authorization']['merchant_id'] = $this->getMerchantId();
        $data['redirect_uri'] = $this->getReturnUrl();

        //Nice to haves.
        if ($this->getCancelUrl()) {
            $data['cancel_uri'] = $this->getCancelUrl();
        }
        if ($this->getState()) {
            $data['state'] = $this->getState();
        }
        if ($this->getDescription()) {
            $data['name'] = $this->getDescription();
        }
        if ($this->getCalendarInterval()) {
            $data['pre_authorization']['calendar_intervals'] = $this->getCalendarInterval();
        }
        if ($this->getSetupFee()) {
            $data['pre_authorization']['setup_fee'] = $this->getSetupFee();
        }
        if ($this->getIntervalCount()) {
            $data['pre_authorization']['interval_count'] = $this->getIntervalCount();
        }
        if ($this->getPreAuthExpire()) {
            $data['pre_authorization']['expires_at'] = $this->getPreAuthExpire();
        }

        if ($this->getCard()) {
            $data['bill']['user'] = array();
            $data['bill']['user']['first_name'] = $this->getCard()->getFirstName();
            $data['bill']['user']['last_name'] = $this->getCard()->getLastName();
            $data['bill']['user']['email'] = $this->getCard()->getEmail();
            $data['bill']['user']['billing_address1'] = $this->getCard()->getAddress1();
            $data['bill']['user']['billing_address2'] = $this->getCard()->getAddress2();
            $data['bill']['user']['billing_town'] = $this->getCard()->getCity();
            $data['bill']['user']['billing_county'] = $this->getCard()->getCountry();
            $data['bill']['user']['billing_postcode'] = $this->getCard()->getPostcode();
        }

        $data['signature'] = $this->generateSignature($data);

        return $data;

    }

    public function sendData($data)
    {
        return $this->response = new AuthorizeResponse($this, $data);
    }
}
