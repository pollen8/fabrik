<?php

namespace Omnipay\Pin;

use Omnipay\Tests\GatewayTestCase;

class GatewayTest extends GatewayTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());

        $this->options = array(
            'amount' => '10.00',
            'card'   => $this->getValidCard(),
            'email'       => 'roland@pin.net.au',
            'description' => 'test charge'
        );
    }

    public function testPurchaseSuccess()
    {
        $this->setMockHttpResponse('PurchaseSuccess.txt');

        $response = $this->gateway->purchase($this->options)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('ch_fXIxWf0gj1yFHJcV1W-d-w', $response->getTransactionReference());
        $this->assertSame('Success!', $response->getMessage());
    }

    public function testPurchaseError()
    {
        $this->setMockHttpResponse('PurchaseFailure.txt');

        $response = $this->gateway->purchase($this->options)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('The current resource was deemed invalid.', $response->getMessage());
    }

    public function testRefundSuccess()
    {
        $this->setMockHttpResponse('RefundSuccess.txt');

        $response = $this->gateway->refund(array('amount' => '400.00', 'transactionReference' => 'ch_bZ3RhJnIUZ8HhfvH8CCvfA'))->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('rf_ERCQy--Ay6o-NKGiUVcKKA', $response->getTransactionReference());
        $this->assertSame('Pending', $response->getMessage());
    }

    public function testRefundError()
    {
        $this->setMockHttpResponse('RefundFailure.txt');

        $response = $this->gateway->refund(array('amount' => '500.00', 'transactionReference' => 'ch_bZ3RhJnIUZ8HhfvH8CCvfA'))->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('Refund amount is more than your available Pin Payments balance.', $response->getMessage());
    }

    public function testGetCardReferenceSuccess()
    {
        $this->setMockHttpResponse('CardSuccess.txt');

        $response = $this->gateway->createCard($this->options)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('card_8LmnNMTYWG4zQZ4YnYQhBg', $response->getCardReference());
        $this->assertTrue($response->getMessage());
    }

    public function testCreateCardError()
    {
        $this->setMockHttpResponse('CardFailure.txt');

        $response = $this->gateway->createCard($this->options)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getCardReference());
        $this->assertSame('One or more parameters were missing or invalid', $response->getMessage());
    }

    public function testCreateCustomerSuccess()
    {
        $this->setMockHttpResponse('CustomerSuccess.txt');

        $response = $this->gateway->createCustomer($this->options)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('cus_Mb-8S1ZgEbLUUUJ97dfhfQ', $response->getCustomerReference());
        $this->assertTrue($response->getMessage());
    }

    public function testCreateCustomerError()
    {
        $this->setMockHttpResponse('CustomerFailure.txt');

        $response = $this->gateway->createCustomer($this->options)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getCustomerReference());
        $this->assertSame('One or more parameters were missing or invalid', $response->getMessage());
    }

    public function testCaptureSuccess()
    {
        $this->setMockHttpResponse('CaptureSuccess.txt');

        $response = $this->gateway->capture(array('amount' => '400.00', 'transactionReference' => 'ch_bZ3RhJnIUZ8HhfvH8CCvfA'))->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('ch_lfUYEBK14zotCTykezJkfg', $response->getTransactionReference());
        $this->assertTrue($response->getCaptured());
    }

    public function testCaptureError()
    {
        $this->setMockHttpResponse('CaptureFailure.txt');

        $response = $this->gateway->capture(array('amount' => '400.00', 'transactionReference' => 'ch_lfUYEBK14zotCTykezJkfg'))->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('The authorisation has expired and can not be captured.', $response->getMessage());
    }
}
