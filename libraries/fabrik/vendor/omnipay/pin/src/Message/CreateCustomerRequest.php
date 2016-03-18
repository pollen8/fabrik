<?php
/**
 * Pin Create Customer Request
 */

namespace Omnipay\Pin\Message;

/**
 * Pin Create Customer Request
 *
 * The customers API allows you to store a customer’s email
 * and credit card details. A customer can then be used with
 * the charges API to create multiple charges over time.
 *
 * Customers can have multiple cards associated with them,
 * and one will be considered the customer’s primary card.
 * The card object in returned customer information represents
 * this primary card. It contains a member called primary,
 * which says whether the card is a customer’s primary card;
 * its value will always be true. 
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
 *   $response = $gateway->createCustomer(array(
 *       'card'      => $card,
 *   ))->send();
 *   if ($response->isSuccessful()) {
 *       // Find the customer ID
 *       $customer_id = $response->getCustomerToken();
 *   } else {
 *       echo "Gateway createCustomer failed.\n";
 *       echo "Error message == " . $response->getMessage() . "\n";
 *   }
 * </code>
 *
 * @link https://pin.net.au/docs/api/customers
 */
class CreateCustomerRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('email');

        $data = array();

        $data['email'] = $this->getEmail();

        if ($this->getToken()) {
            $data['card_token'] = $this->getToken();
        } else {
            $this->getCard()->validate();

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
        $httpResponse = $this->sendRequest('/customers', $data);

        return $this->response = new Response($this, $httpResponse->json());
    }
}
