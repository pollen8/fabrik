<?php

namespace Omnipay\Pin\Message;

use Omnipay\Tests\TestCase;

class RefundRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new RefundRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setTransactionReference('ch_bZ3RhJnIUZ8HhfvH8CCvfA')
            ->setAmount('400.00');
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('RefundSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('rf_ERCQy--Ay6o-NKGiUVcKKA', $response->getTransactionReference());
        $this->assertSame('Pending', $response->getMessage());
    }

    public function testSendError()
    {
        $this->setMockHttpResponse('RefundFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('Refund amount is more than your available Pin Payments balance.', $response->getMessage());
    }
}
