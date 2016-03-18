<?php
/**
 * Pin Purchase Request
 */

namespace Omnipay\Pin\Message;

/**
 * Pin Purchase Request
 *
 * The charges API allows you to create new credit card charges, and to retrieve
 * details of previous charges.
 *
 * This message creates a new charge and returns its details. This may be a
 * long-running request.
 *
 * Gateway Parameters
 *
 * * email 	Email address of the customer.  Obtained from the card data.
 * * description 	Description of the item purchased (e.g. "500g of single origin beans").
 * * amount 	Amount to charge in the currency’s base unit (e.g. cents
 *   for AUD, yen for JPY). There is a minimum charge amount for each
 *   currency; refer to the documentation on supported currencies.
 * * ip_address 	IP address of the person submitting the payment.
 *   Obtained from getClientIp.
 * * Optional currency 	The three-character ISO 4217 currency code of one
 *   of our supported currencies, e.g. AUD or USD. Default value is "AUD".
 * * Optional capture 	Whether or not to immediately capture the charge
 *   ("true" or "false"). If capture is false an authorisation is created.
 *   Later you can capture. Authorised charges automatically expire after
 *   5 days. Default value is "true".
 *
 * and one of the following:
 *
 * * card 	The full details of the credit card to be charged (CreditCard object)
 * * card_token 	Token of the card to be charged, as returned from the card
 *   tokens API or customer API.
 * * customer_token 	Token of the customer to be charged, as returned
 *   from the customers API.
 *
 * Example:
 *
 * <code>
 * // Create a gateway for the Pin REST Gateway
 * // (routes to GatewayFactory::create)
 * $gateway = Omnipay::create('PinGateway');
 *
 * // Initialise the gateway
 * $gateway->initialize(array(
 *     'secretKey' => 'TEST',
 *     'testMode'  => true, // Or false when you are ready for live transactions
 * ));
 *
 * // Create a credit card object
 * // This card can be used for testing.
 * // See https://pin.net.au/docs/api/test-cards for a list of card
 * // numbers that can be used for testing.
 * $card = new CreditCard(array(
 *             'firstName'    => 'Example',
 *             'lastName'     => 'Customer',
 *             'number'       => '4200000000000000',
 *             'expiryMonth'  => '01',
 *             'expiryYear'   => '2020',
 *             'cvv'          => '123',
 *             'email'        => 'customer@example.com',
 *             'billingAddress1'       => '1 Scrubby Creek Road',
 *             'billingCountry'        => 'AU',
 *             'billingCity'           => 'Scrubby Creek',
 *             'billingPostcode'       => '4999',
 *             'billingState'          => 'QLD',
 * ));
 *
 * // Do a purchase transaction on the gateway
 * $transaction = $gateway->purchase(array(
 *     'description'              => 'Your order for widgets',
 *     'amount'                   => '10.00',
 *     'currency'                 => 'AUD',
 *     'clientIp'                 => $_SERVER['REMOTE_ADDR'],
 *     'card'                     => $card,
 * ));
 * $response = $transaction->send();
 * if ($response->isSuccessful()) {
 *     echo "Purchase transaction was successful!\n";
 *     $sale_id = $response->getTransactionReference();
 *     echo "Transaction reference = " . $sale_id . "\n";
 * }
 * </code>
 *
 * @see \Omnipay\Pin\Gateway
 * @link https://pin.net.au/docs/api/charges
 */
class PurchaseRequest extends AbstractRequest
{
    public function getData()
    {
        $data = array();
        $this->validate('amount', 'description');

        $data = array();

        $data['amount'] = $this->getAmountInteger();
        $data['currency'] = strtolower($this->getCurrency());
        $data['description'] = $this->getDescription();
        $data['ip_address'] = $this->getClientIp();
        $data['capture'] = $this->getCapture();

        // Token payments
        if ($token = $this->getToken()) {
            if (strpos($token, 'card_') !== false) {
                $data['card_token'] = $token;
            } else {
                $data['customer_token'] = $token;
            }

            // Supply an email address if provided, but it is not required
            if ($this->getEmail()) {
                $data['email'] = $this->getEmail();
            }

        // Card payments
        } else {
            $this->validate('card');
            $this->getCard()->validate();

            // An email address is required
            if ($this->getCard()->getEmail()) {
                $data['email'] = $this->getCard()->getEmail();
            } elseif ($this->getEmail()) {
                $data['email'] = $this->getEmail();
            } else {
                $this->validate('email');
            }

            $data['card']['number'] = $this->getCard()->getNumber();
            $data['card']['expiry_month'] = $this->getCard()->getExpiryMonth();
            $data['card']['expiry_year'] = $this->getCard()->getExpiryYear();
            $data['card']['cvc'] = $this->getCard()->getCvv();
            $data['card']['name'] = $this->getCard()->getName();
            $data['card']['address_line1'] = $this->getCard()->getAddress1();
            $data['card']['address_line2'] = $this->getCard()->getAddress2();
            $data['card']['address_city'] = $this->getCard()->getCity();
            $data['card']['address_postcode'] = $this->getCard()->getPostcode();
            $data['card']['address_state'] = $this->getCard()->getState();
            $data['card']['address_country'] = $this->getCard()->getCountry();
        }

        return $data;
    }

    public function sendData($data)
    {
        $httpResponse = $this->sendRequest('/charges', $data);

        return $this->response = new Response($this, $httpResponse->json());
    }

    /**
     * Get the Capture flag.
     *
     * Returns the capture parameter, which states whether the charge is
     * just an authorisation or it is captured instantly. By default all
     * charges are captured. Please note that the return has to be a string
     * and not a boolean or Pin's API will disregard it and consider it set
     * to 'true'
     *
     * @return string
     */
    public function getCapture()
    {
        $capture = $this->getParameter('capture');

        // By default with Pin a transaction is captured.
        return $capture === false ? 'false' : 'true';
    }

    /**
     * Set the capture flag.
     *
     * This flag states whether the charge is just an authorisation or it is
     * captured instantly. By default all charges are captured.
     *
     * @param $value
     *
     * @return PurchaseRequest provides a fluent interface
     */
    public function setCapture($value)
    {
        return $this->setParameter('capture', $value);
    }
}
