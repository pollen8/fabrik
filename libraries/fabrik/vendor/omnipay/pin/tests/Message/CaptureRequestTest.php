<?php

namespace Omnipay\Pin\Message;

use Omnipay\Tests\TestCase;

class CaptureRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new RefundRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setTransactionReference('ch_bZ3RhJnIUZ8HhfvH8CCvfA')
            ->setAmount('400.00');
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('CaptureSuccess.txt');
        $response = $this->request->send();
        $data = $response->getData();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('ch_lfUYEBK14zotCTykezJkfg', $response->getTransactionReference());

        $this->assertTrue($data['response']['captured']);
    }

    public function testSendError()
    {
        $this->setMockHttpResponse('CaptureFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('The authorisation has expired and can not be captured.', $response->getMessage());
    }
}
