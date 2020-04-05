<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use WebToPay\Exception\BaseException;
use WebToPay\WebToPay;

/**
 * Test for class WebToPay
 */
class WebToPayTest extends TestCase
{

    public function testGetPaymentUrl()
    {
        $url = WebToPay::getPaymentUrl('LIT');
        $this->assertEquals($url, WebToPay::PAY_URL);
        $url = WebToPay::getPaymentUrl('ENG');
        $this->assertEquals($url, 'https://bank.paysera.com/pay/');
    }

    /**
     * Exception should be thrown if project id is not given
     *
     * @throws BaseException
     */
    public function testBuildRequestWithoutProjectId()
    {
        $this->expectException(BaseException::class);
        WebToPay::buildRequest([
            'orderid' => '123',
            'accepturl' => 'http://local.test/accept',
            'cancelurl' => 'http://local.test/cancel',
            'callbackurl' => 'http://local.test/callback',

            'sign_password' => 'asdfghjkl',
        ]);
    }

    /**
     * Exception should be thrown if order id is not given
     *
     * @throws BaseException
     */
    public function testBuildRepeatRequestWithoutProjectId()
    {
        $this->expectException(BaseException::class);
        WebToPay::buildRepeatRequest([
            'sign_password' => 'asdfghjkl',
            'projectid' => '123',
        ]);
    }
}

