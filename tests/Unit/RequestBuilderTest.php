<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use WebToPay\Exception\BaseException;
use WebToPay\Exception\ValidationException;
use WebToPay\RequestBuilder;
use WebToPay\UrlBuilder;
use WebToPay\Util;

/**
 * Test for class RequestBuilder
 */
class RequestBuilderTest extends TestCase
{
    /**
     * @var UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @var Util
     */
    protected $util;

    /**
     * @var RequestBuilder
     */
    protected $builder;

    /**
     * Sets up this test
     */
    public function setUp(): void
    {
        $this->util = $this->getMock(Util::class, ['encodeSafeUrlBase64']);
        $this->urlBuilder = $this->getMockBuilder(UrlBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new RequestBuilder(123, 'secret', $this->util, $this->urlBuilder);
    }

    /**
     * Test build request when no orderid is passed
     *
     * @throws ValidationException
     * @throws BaseException
     */
    public function testBuildRequestWithNoOrderId()
    {
        $this->expectException(ValidationException::class);
        $this->builder->buildRequest([
            'accepturl' => 'http://local.test/',
            'cancelurl' => 'http://local.test/',
            'callbackurl' => 'http://local.test/',
        ]);
    }

    /**
     * Test build request when invalid currency is passed
     *
     * @throws ValidationException
     * @throws BaseException
     */
    public function testBuildRequestWithInvalidCurrency()
    {
        $this->expectException(ValidationException::class);
        $this->builder->buildRequest([
            'orderid' => 123,
            'accepturl' => 'http://local.test/',
            'cancelurl' => 'http://local.test/',
            'callbackurl' => 'http://local.test/',
            'currency' => 'litai',
        ]);
    }

    /**
     * Tests buildRequest method
     * @throws BaseException
     */
    public function testBuildRequest()
    {
        $this->util
            ->expects($this->once())
            ->method('encodeSafeUrlBase64')
            ->with(
                'orderid=123&accepturl=http%3A%2F%2Flocal.test%2F&cancelurl=http%3A%2F%2Flocal.test%2F'
                . '&callbackurl=http%3A%2F%2Flocal.test%2F&amount=100&some-other-parameter=abc'
                . '&version=1.6&projectid=123'
            )
            ->will($this->returnValue('encoded'));
        $this->assertEquals(
            ['data' => 'encoded', 'sign' => md5('encodedsecret')],
            $this->builder->buildRequest([
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'amount' => 100,
                'some-other-parameter' => 'abc',
            ])
        );
    }

    /**
     * Tests buildRepeatRequest method
     * @throws BaseException
     */
    public function testBuildRepeatRequest()
    {
        $this->util
            ->expects($this->once())
            ->method('encodeSafeUrlBase64')
            ->with('orderid=123&version=1.6&projectid=123&repeat_request=1')
            ->will($this->returnValue('encoded'));
        $this->assertEquals(
            ['data' => 'encoded', 'sign' => md5('encodedsecret')],
            $this->builder->buildRepeatRequest(123)
        );
    }
}