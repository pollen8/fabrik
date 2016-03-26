<?php

namespace Omnipay\FirstData\Message;

use Omnipay\Tests\TestCase;
use Omnipay\FirstData\Message\PayeezyPurchaseRequest;

class PayeezyPurchaseResponseTest extends TestCase
{
    public function testPurchaseSuccess()
    {
        $response = new PayeezyResponse($this->getMockRequest(), http_build_query(array(
            'amount' => 1000,
            'exact_resp_code' => 00,
            'exact_message' => 'Transaction Normal',
            'reference_no' => 'abc123',
            'authorization_num' => 'auth1234',
            'transaction_approved' => 1,
        )));

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('auth1234::', $response->getTransactionReference());
        $this->assertSame('Transaction Normal', $response->getMessage());
        $this->assertEquals('00', $response->getCode());
    }

    public function testPurchaseError()
    {
        $response = new PayeezyResponse($this->getMockRequest(), http_build_query(array(
            'amount' => 1000,
            'exact_resp_code' => 22,
            'exact_message' => 'Invalid Credit Card Number',
            'reference_no' => 'abc123',
            'authorization_num' => 'auth1234',
            'transaction_approved' => 0,
        )));

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('auth1234::', $response->getTransactionReference());
        $this->assertSame('Invalid Credit Card Number', $response->getMessage());
        $this->assertEquals('22', $response->getCode());
    }

    public function testBankError()
    {
        $response = new PayeezyResponse($this->getMockRequest(), http_build_query(array(
            'amount' => 1000,
            'exact_resp_code' => 00,
            'reference_no' => 'abc123',
            'authorization_num' => '',
            'transaction_approved' => 0,
        )));

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('::', $response->getTransactionReference());
        $this->assertEquals('00', $response->getCode());
    }

}
