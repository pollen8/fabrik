<?php namespace Omnipay\MultiSafepay\Message;

use Omnipay\Tests\TestCase;

class RestPurchaseRequestTest extends TestCase
{
    /**
     * @var PurchaseRequest
     */
    private $request;

    protected function setUp()
    {
        $this->request = new RestPurchaseRequest(
            $this->getHttpClient(),
            $this->getHttpRequest()
        );

        $this->request->initialize(
            array(
                'apiKey' => '123456789',
                'amount' => 10.00,
                'currency' => 'eur',
                'description' => 'Test transaction',
                'cancel_url' => 'http://localhost/cancel',
                'notify_url' => 'http://localhost/notify',
                'return_url' => 'http://localhost/return',
                'close_window' => false,
                'days_active' => 3,
                'send_mail' => true,
                'gateway' => 'IDEAL',
                'google_analytics_code' => '123456789',
                'manual' => false,
                'transactionId' => 'TEST-TRANS-1',
                'type' => 'redirect',
                'var1' => 'extra data 1',
                'var2' => 'extra data 2',
                'var3' => 'extra data 3',
            )
        );
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('RestPurchaseSuccess.txt');

        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isRedirect());

        $this->assertEquals(
            'https://testpay.multisafepay.com/pay/?order_id=TEST-TRANS-1',
            $response->getRedirectUrl()
        );

        $this->assertEquals('TEST-TRANS-1', $response->getTransactionId());
    }

    public function testInvalidAmount()
    {
        $this->setMockHttpResponse('RestPurchaseInvalidAmount.txt');

        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('Invalid amount', $response->getMessage());
        $this->assertEquals(1001, $response->getCode());
    }

    public function testDataIntegrity()
    {
        $this->assertEquals('123456789', $this->request->getGoogleAnalyticsCode());
        $this->assertEquals('EUR', $this->request->getCurrency());
        $this->assertEquals('extra data 1', $this->request->getVar1());
        $this->assertEquals('extra data 2', $this->request->getVar2());
        $this->assertEquals('extra data 3', $this->request->getVar3());
        $this->assertEquals('http://localhost/cancel', $this->request->getCancelUrl());
        $this->assertEquals('http://localhost/notify', $this->request->getNotifyUrl());
        $this->assertEquals('http://localhost/return', $this->request->getReturnUrl());
        $this->assertEquals('IDEAL', $this->request->getGateway());
        $this->assertEquals('redirect', $this->request->getType());
        $this->assertEquals('Test transaction', $this->request->getDescription());
        $this->assertEquals('TEST-TRANS-1', $this->request->getTransactionId());
        $this->assertEquals(10.00, $this->request->getAmount());
        $this->assertEquals(3, $this->request->getDaysActive());
        $this->assertFalse($this->request->getCloseWindow());
        $this->assertFalse($this->request->getManual());
        $this->assertTrue($this->request->getSendMail());
    }
}
