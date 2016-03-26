<?php

namespace Omnipay\Pin\Message;

use Omnipay\Tests\TestCase;

class ResponseTest extends TestCase
{
    public function testPurchaseSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseSuccess.txt');
        $response = new Response($this->getMockRequest(), $httpResponse->json());

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('ch_fXIxWf0gj1yFHJcV1W-d-w', $response->getTransactionReference());
        $this->assertSame('Success!', $response->getMessage());
    }

    public function testPurchaseFailure()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseFailure.txt');
        $response = new Response($this->getMockRequest(), $httpResponse->json());

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('The current resource was deemed invalid.', $response->getMessage());
    }

    public function testCardSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('CardSuccess.txt');
        $response = new Response($this->getMockRequest(), $httpResponse->json());

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('card_8LmnNMTYWG4zQZ4YnYQhBg', $response->getCardReference());
        $this->assertTrue($response->getMessage());
    }

    public function testCardFailure()
    {
        $httpResponse = $this->getMockHttpResponse('CardFailure.txt');
        $response = new Response($this->getMockRequest(), $httpResponse->json());

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getCardReference());
        $this->assertSame('One or more parameters were missing or invalid', $response->getMessage());
    }

    public function testCustomerSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('CustomerSuccess.txt');
        $response = new Response($this->getMockRequest(), $httpResponse->json());

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('cus_Mb-8S1ZgEbLUUUJ97dfhfQ', $response->getCustomerReference());
        $this->assertTrue($response->getMessage());
    }

    public function testCustomerFailure()
    {
        $httpResponse = $this->getMockHttpResponse('CustomerFailure.txt');
        $response = new Response($this->getMockRequest(), $httpResponse->json());

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getCustomerReference());
        $this->assertSame('One or more parameters were missing or invalid', $response->getMessage());
    }
}
