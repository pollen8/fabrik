<?php

namespace Omnipay\FirstData\Message;

use Omnipay\Tests\TestCase;

class PayeezyRefundRequestTest extends TestCase
{
    public function testRefundSuccess()
    {
        $request = new PayeezyRefundRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize(
            array(
                'amount' => '12.00',
                'transactionReference' => '9999::DATADATADATA',
            )
        );

        $data = $request->getData();
        $this->assertEquals('9999', $data['authorization_num']);
        $this->assertEquals('DATADATADATA', $data['transaction_tag']);
        $this->assertEquals('12.00', $data['amount']);
    }
}
