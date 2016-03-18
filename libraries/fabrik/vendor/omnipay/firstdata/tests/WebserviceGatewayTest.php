<?php

namespace Omnipay\FirstData;

use Omnipay\Tests\GatewayTestCase;

class WebserviceGatewayTest extends GatewayTestCase
{
    /** @var  WebserviceGateway */
    protected $gateway;

    /** @var  array */
    protected $options;

    public function setUp()
    {
        parent::setUp();

        $this->gateway = new WebserviceGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setSslCertificate('my.pem');
        $this->gateway->setSslKey('my.key');
        $this->gateway->setSslKeyPassword('thisISaPASSWORD');
        $this->gateway->setUserName('1234');
        $this->gateway->setPassword('abcde');

        $this->options = array(
            'accountId'                 => '12345',
            'transactionId'             => '259611',
            'amount'                    => '10.00',
            'currency'                  => 'USD',
            'clientIp'                  => '127.0.0.1',
            'card'                      => $this->getValidCard(),
            'testMode'                  => true,
        );
    }

    public function testProperties()
    {
        $this->assertEquals('my.pem', $this->gateway->getSslCertificate());
        $this->assertEquals('my.key', $this->gateway->getSslKey());
        $this->assertEquals('thisISaPASSWORD', $this->gateway->getSslKeyPassword());
        $this->assertEquals('1234', $this->gateway->getUserName());
        $this->assertEquals('abcde', $this->gateway->getPassword());
    }

    public function testPurchaseSuccess()
    {
        // Mocks don't work on this gateway because it has to call cURL directly.
        // $this->setMockHttpResponse('WebservicePurchaseSuccess.txt');
        $data = file_get_contents(__DIR__ . '/Mock/WebservicePurchaseSuccess.txt');

        $purchase = $this->gateway->purchase($this->options);
        $response = $purchase->createResponse($data);

        // echo "Response data =\n";
        // print_r($response->getData());
        // echo "\nEnd Response data\n";

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('259611::1452486844', $response->getTransactionReference());
        $this->assertNull($response->getMessage());
        $this->assertEquals('APPROVED', $response->getCode());
    }
}
