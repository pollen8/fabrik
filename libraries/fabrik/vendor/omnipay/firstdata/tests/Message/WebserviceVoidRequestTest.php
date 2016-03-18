<?php

namespace Omnipay\FirstData\Message;

use Omnipay\Tests\TestCase;

class WebserviceVoidRequestTest extends TestCase
{
    public function testVoidSuccess()
    {
        $request = new WebserviceVoidRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize(
            array(
                'transactionReference'  => '98765::ABCDEF',
            )
        );

        $data = $request->getData();
        $this->assertEquals('void', $data['txn_type']);
        $this->assertEquals('98765', $data['reference_no']);
        $this->assertEquals('ABCDEF', $data['tdate']);
    }
}
