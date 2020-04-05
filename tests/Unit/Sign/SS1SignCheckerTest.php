<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use WebToPay\Exception\CallbackException;
use WebToPay\Sign\SS1SignChecker;


/**
 * Test for class SS1SignChecker
 */
class SS1SignCheckerTest extends TestCase
{

    /**
     * @var SS1SignChecker
     */
    protected $signChecker;

    /**
     * Sets up this test
     */
    public function setUp(): void
    {
        $this->signChecker = new SS1SignChecker('secret');
    }

    /**
     * Should throw exception if not all required parameters are passed
     *
     * @throws CallbackException
     */
    public function testCheckSignWithoutInformation()
    {
        $this->expectException(CallbackException::class);
        $this->signChecker->checkSign([
            'projectid' => '123',
            'ss1' => 'asd',
            'ss2' => 'zxc',
        ]);
    }

    /**
     * Tests checkSign
     * @throws CallbackException
     */
    public function testCheckSign()
    {
        $this->assertTrue($this->signChecker->checkSign([
            'data' => 'encodedData',
            'ss1' => md5('encodedDatasecret'),
            'ss2' => 'bad-ss2',
        ]));
        $this->assertFalse($this->signChecker->checkSign([
            'data' => 'encodedData',
            'ss1' => md5('encodedDatasecret1'),
            'ss2' => 'bad-ss2',
        ]));
    }
}