<?php

namespace Omnipay\FirstData\Message;

use Omnipay\Tests\TestCase;

class WebserviceCaptureRequestTest extends TestCase
{
    public function testCaptureSuccess()
    {
        $request = new WebserviceCaptureRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize(
            array(
                'transactionReference'  => '98765::ABCDEF',
            )
        );

        $data = $request->getData();
        $this->assertEquals('postAuth', $data['txn_type']);
        $this->assertEquals('98765', $data['reference_no']);
    }
}
