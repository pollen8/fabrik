<?php

namespace Omnipay\Pin\Message;

use Omnipay\Tests\TestCase;

class CaptureResponseTest extends TestCase
{
    public function testCaptureSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('CaptureSuccess.txt');
        $response = new CaptureResponse($this->getMockRequest(), $httpResponse->json());

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('ch_lfUYEBK14zotCTykezJkfg', $response->getTransactionReference());
        $this->assertTrue($response->getCaptured());
    }

    public function testCaptureFailure()
    {
        $httpResponse = $this->getMockHttpResponse('CaptureFailure.txt');
        $response = new CaptureResponse($this->getMockRequest(), $httpResponse->json());

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('The authorisation has expired and can not be captured.', $response->getMessage());
        $this->assertNull($response->getCaptured());
    }
}
