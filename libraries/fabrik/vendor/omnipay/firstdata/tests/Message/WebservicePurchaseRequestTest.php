<?php

namespace Omnipay\FirstData\Message;

use Omnipay\Tests\TestCase;

class WebservicePurchaseRequestTest extends TestCase
{
    public function testPurchaseSuccess()
    {
        $request = new WebservicePurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize(
            array(
                'amount'        => '12.00',
                'transactionId' => '98765',
                'accountId'     => '67890',
                'card'          => $this->getValidCard(),
                'testMode'      => true,
            )
        );

        $data = $request->getData();
        $this->assertEquals('sale', $data['txn_type']);
        $this->assertEquals('4111111111111111', $data['card_number']);
        $this->assertEquals('12.00', $data['amount']);
        $this->assertEquals('123 Billing St', $data['card_address1']);
        $this->assertEquals('Billstown', $data['card_city']);
        $this->assertEquals('12345', $data['card_postcode']);
        $this->assertEquals('CA', $data['card_state']);
        $this->assertEquals('US', $data['card_country']);
        $this->assertEquals('98765', $data['reference_no']);

        // Test request internals
        $curl = $request->buildCurlClient();
        $this->assertTrue(is_resource($curl));

        $endpoint = $request->getEndpoint();
        $this->assertEquals('https://ws.merchanttest.firstdataglobalgateway.com:443/fdggwsapi/services', $endpoint);
    }
}
