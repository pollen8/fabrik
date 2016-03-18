<?php

namespace Omnipay\FirstData\Message;

use Omnipay\Tests\TestCase;
use Omnipay\FirstData\Message\PayeezyPurchaseRequest;

class PayeezyPurchaseRequestTest extends TestCase
{
    public function testPurchaseSuccess()
    {
        $request = new PayeezyPurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize(
            array(
                'amount' => '12.00',
                'card' => $this->getValidCard(),
            )
        );

        $data = $request->getData();
        $this->assertEquals('00', $data['transaction_type']);
        $this->assertEquals('4111111111111111', $data['cc_number']);
        $this->assertEquals('Visa', $data['credit_card_type']);
        $this->assertEquals('12.00', $data['amount']);
        $this->assertEquals('123 Billing St|12345|Billstown|CA|US', $data['cc_verification_str1']);
    }

    public function testPurchaseSuccessMaestroType()
    {
        $options = array(
            'amount' => '12.00',
            'card' => $this->getValidCard(),
        );

        $options['card']['number'] = '6304000000000000';

        $request = new PayeezyPurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($options);

        $data = $request->getData();
        $this->assertEquals('00', $data['transaction_type']);
        $this->assertEquals('maestro', $data['credit_card_type']);
    }
}
