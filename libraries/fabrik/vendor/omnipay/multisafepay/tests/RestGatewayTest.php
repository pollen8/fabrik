<?php namespace Omnipay\MultiSafepay;

use Omnipay\Tests\GatewayTestCase;

class RestGatewayTest extends GatewayTestCase
{
    /**
     * @var Gateway
     */
    protected $gateway;

    /**
     * @{inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->gateway = new RestGateway(
            $this->getHttpClient(),
            $this->getHttpRequest()
        );

        $this->gateway->setApiKey('123456789');
    }

    public function testFetchPaymentMethodsRequest()
    {
        $request = $this->gateway->fetchPaymentMethods(
            array('country' => 'NL')
        );

        $this->assertInstanceOf('Omnipay\MultiSafepay\Message\RestFetchPaymentMethodsRequest', $request);
        $this->assertEquals('NL', $request->getCountry());
    }

    public function testFetchIssuersRequest()
    {
        $request = $this->gateway->fetchIssuers();

        $this->assertInstanceOf('Omnipay\MultiSafepay\Message\RestFetchIssuersRequest', $request);
    }

    public function testPurchaseRequest()
    {
        $request = $this->gateway->purchase(array('amount' => 10.00));

        $this->assertInstanceOf('Omnipay\MultiSafepay\Message\RestPurchaseRequest', $request);
        $this->assertEquals($request->getAmount(), 10.00);
    }

    public function testCompletePurchaseRequest()
    {
        $request = $this->gateway->completePurchase(array('amount' => 10.00));

        $this->assertInstanceOf('Omnipay\MultiSafepay\Message\RestCompletePurchaseRequest', $request);
        $this->assertEquals($request->getAmount(), 10.00);
    }
}
