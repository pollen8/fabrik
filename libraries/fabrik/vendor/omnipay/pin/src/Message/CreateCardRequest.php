<?php
/**
 * Pin Create Card Request
 */

namespace Omnipay\Pin\Message;

/**
 * Pin Create Card Request
 *
 * The card tokens API allows you to securely store credit card details
 * in exchange for a card token. This card token can then be used to
 * create a single charge with the charges API, or to create multiple
 * charges over time using the customers API.
 *
 * Returned card information contains a member called primary, which
 * says whether the card is a customer’s primary card. Its value is
 * true if the card is a customer’s primary card, false if it is a
 * non-primary card of the customer, and null if it is not associated
 * with a customer.
 *
 * A card token can only be used once, to create either a charge or
 * a customer. If no charge or customer is created within 1 month,
 * the token is automatically expired.
 *
 * Example:
 *
 * <code>
 *   // Create a gateway for the Pin REST Gateway
 *   // (routes to GatewayFactory::create)
 *   $gateway = Omnipay::create('PinGateway');
 *
 *   // Initialise the gateway
 *   $gateway->initialize(array(
 *       'secretKey' => 'TEST',
 *       'testMode'  => true, // Or false when you are ready for live transactions
 *   ));
 *
 *   // Create a credit card object
 *   // This card can be used for testing.
 *   // See https://pin.net.au/docs/api/test-cards for a list of card
 *   // numbers that can be used for testing.
 *   $card = new CreditCard(array(
 *               'firstName'    => 'Example',
 *               'lastName'     => 'Customer',
 *               'number'       => '4200000000000000',
 *               'expiryMonth'  => '01',
 *               'expiryYear'   => '2020',
 *               'cvv'          => '123',
 *               'email'        => 'customer@example.com',
 *               'billingAddress1'       => '1 Scrubby Creek Road',
 *               'billingCountry'        => 'AU',
 *               'billingCity'           => 'Scrubby Creek',
 *               'billingPostcode'       => '4999',
 *               'billingState'          => 'QLD',
 *   ));
 *
 *   $response = $gateway->createCard(array(
 *       'card'      => $card,
 *   ))->send();
 *   if ($response->isSuccessful()) {
 *       // Find the card ID
 *       $card_id = $response->getCardReference();
 *   } else {
 *       echo "Gateway createCard failed.\n";
 *       echo "Error message == " . $response->getMessage() . "\n";
 *   }
 * </code>
 *
 * @link https://pin.net.au/docs/api/cards
 */
class CreateCardRequest extends AbstractRequest
{
    public function getData()
    {
        $data = array();
        $this->getCard()->validate();

        $data['number'] = $this->getCard()->getNumber();
        $data['expiry_month'] = $this->getCard()->getExpiryMonth();
        $data['expiry_year'] = $this->getCard()->getExpiryYear();
        $data['cvc'] = $this->getCard()->getCvv();
        $data['name'] = $this->getCard()->getName();
        $data['address_line1'] = $this->getCard()->getAddress1();
        $data['address_line2'] = $this->getCard()->getAddress2();
        $data['address_city'] = $this->getCard()->getCity();
        $data['address_postcode'] = $this->getCard()->getPostcode();
        $data['address_state'] = $this->getCard()->getState();
        $data['address_country'] = $this->getCard()->getCountry();

        return $data;
    }

    public function sendData($data)
    {
        $httpResponse = $this->sendRequest('/cards', $data);

        return $this->response = new Response($this, $httpResponse->json());
    }
}
