<?php

namespace Omnipay\FirstData\Message;

use Omnipay\Tests\TestCase;

class WebserviceRefundRequestTest extends TestCase
{
    public function testRefundSuccess()
    {
        $request = new WebserviceRefundRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize(
            array(
                'amount'                => '12.00',
                'transactionReference'  => '98765::ABCDEF',
            )
        );

        $data = $request->getData();
        $this->assertEquals('return', $data['txn_type']);
        $this->assertEquals('12.00', $data['amount']);
        $this->assertEquals('98765', $data['reference_no']);
        $this->assertEquals('ABCDEF', $data['tdate']);
    }
}
