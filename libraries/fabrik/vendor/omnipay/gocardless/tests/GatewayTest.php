<?php

namespace Omnipay\GoCardless;

use Omnipay\Tests\GatewayTestCase;

class GatewayTest extends GatewayTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setAppId('abc');
        $this->gateway->setAppSecret('123');

        $this->options = array(
            'amount' => '10.00',
            'returnUrl' => 'https://www.example.com/return',
        );
    }

    public function testPurchase()
    {
        $response = $this->gateway->purchase($this->options)->send();

        $this->assertInstanceOf('\Omnipay\GoCardless\Message\PurchaseResponse', $response);
        $this->assertTrue($response->isRedirect());
        $this->assertStringStartsWith('https://gocardless.com/connect/bills/new?', $response->getRedirectUrl());
    }

    public function testCompletePurchaseSuccess()
    {
        $this->getHttpRequest()->request->replace(
            array(
                'resource_uri' => 'a',
                'resource_id' => 'b',
                'resource_type' => 'c',
                'state' => 'd',
                'signature' => 'cdc9e0cdb88114976dd18f597cb0a8f46cb26be6c8c17094b6394e76a7fc5732',
            )
        );

        $this->setMockHttpResponse('CompletePurchaseSuccess.txt');

        $response = $this->gateway->completePurchase($this->options)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('b', $response->getTransactionReference());
    }

    public function testCompletePurchaseError()
    {
        $this->getHttpRequest()->request->replace(
            array(
                'resource_uri' => 'a',
                'resource_id' => 'b',
                'resource_type' => 'c',
                'signature' => '416f52e7d287dab49fa8445c1cd0957ca8ddf1c04a6300e00117dc0bedabc7d7',
            )
        );

        $this->setMockHttpResponse('CompletePurchaseFailure.txt');

        $response = $this->gateway->completePurchase($this->options)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertSame('The resource cannot be confirmed', $response->getMessage());
    }

    /**
     * @expectedException Omnipay\Common\Exception\InvalidResponseException
     */
    public function testCompletePurchaseInvalid()
    {
        $this->getHttpRequest()->request->replace(
            array(
                'resource_uri' => 'a',
                'resource_id' => 'b',
                'resource_type' => 'c',
                'signature' => 'd',
            )
        );

        $response = $this->gateway->completePurchase($this->options)->send();
    }

    public function testAuthorization()
    {
        $params = array(
            'amount' => '10.00',
            'intervalLength' => 12,
            'intervalUnit' => 'week',
            'returnUrl' => 'foo.bar/baz'
        );
        $response = $this->gateway->authorize($params)->send();

        $this->assertInstanceOf('Omnipay\GoCardless\Message\AuthorizeResponse', $response);
        $this->assertStringStartsWith('https://gocardless.com/connect/pre_authorizations/new?', $response->getRedirectUrl());

        //NB: These are here for a potential refactor.
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertSame('GET', $response->getRedirectMethod());
        $this->assertNull($response->getRedirectData());
    }

    public function testCompleteAuthorizeSuccess()
    {
        $this->getHttpRequest()->request->replace(
            array(
                'resource_uri' => 'a',
                'resource_id' => 'b',
                'resource_type' => 'c',
                'state' => 'd',
                'signature' => 'cdc9e0cdb88114976dd18f597cb0a8f46cb26be6c8c17094b6394e76a7fc5732',
            )
        );

        $this->setMockHttpResponse('CompletePurchaseSuccess.txt');

        $response = $this->gateway->completeAuthorize($this->options)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('b', $response->getTransactionReference());
    }

    public function testCapture()
    {
        $response = $this->gateway->capture(array('amount' => '10.00'));

        $this->assertInstanceOf('Omnipay\GoCardless\Message\CaptureRequest', $response);
    }

    public function testCaptureSuccess()
    {
        $params = array(
            'amount' => '10.00',
            'transactionReference' => 'abc',
            'description' => 'fyi'
        );
        $transaction = $this->gateway->capture($params);
        $transaction->setChargeCustomerAt('2015-12-12');

        $this->setMockHttpResponse('CapturePaymentSuccess.txt');

        $response = $transaction->send();

        $this->assertInstanceOf('Omnipay\GoCardless\Message\CaptureResponse', $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->getMessage());
    }

    public function testCaptureFailure()
    {
        $params = array(
            'amount' => '10.00',
            'transactionReference' => '12v'
        );
        $this->setMockHttpResponse('CapturePaymentFailure.txt');
        $response = $this->gateway->capture($params)->send();

        $this->assertInstanceOf('Omnipay\GoCardless\Message\CaptureResponse', $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('The authorization cannot be found', $response->getMessage());
    }

}
