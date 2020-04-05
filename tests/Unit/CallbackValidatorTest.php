<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use WebToPay\CallbackValidator;
use WebToPay\Exception\BaseException;
use WebToPay\Exception\CallbackException;
use WebToPay\Sign\SignCheckerInterface;
use WebToPay\Util;

/**
 * Test for class CallbackValidator
 */
class CallbackValidatorTest extends TestCase
{

    /**
     * @var SignCheckerInterface
     */
    protected $signer;

    /**
     * @var Util
     */
    protected $util;

    /**
     * @var CallbackValidator
     */
    protected $validator;

    /**
     * Sets up this test
     */
    public function setUp(): void
    {
        $this->signer = $this->getMock(SignCheckerInterface::class);
        $this->util = $this->getMock(Util::class, ['decodeSafeUrlBase64', 'parseHttpQuery']);
        $this->validator = new CallbackValidator(123, $this->signer, $this->util);
    }

    /**
     * Exception should be thrown on invalid sign
     *
     * @throws CallbackException
     */
    public function testValidateAndParseDataWithInvalidSign()
    {
        $request = ['data' => 'abcdef', 'sign' => 'qwerty'];

        $this->signer->expects($this->once())->method('checkSign')->with($request)->will($this->returnValue(false));
        $this->util->expects($this->never())->method($this->anything());

        $this->expectException(CallbackException::class);
        $this->validator->validateAndParseData($request);
    }

    /**
     * Exception should be thrown if project ID does not match expected one
     *
     * @throws CallbackException
     */
    public function testValidateAndParseDataWithInvalidProject()
    {
        $request = ['data' => 'abcdef', 'sign' => 'qwerty'];
        $parsed = ['projectid' => 456];

        $this->signer->expects($this->once())->method('checkSign')->with($request)->will($this->returnValue(true));
        $this->util->expects($this->at(0))->method('decodeSafeUrlBase64')->with('abcdef')->will($this->returnValue('zxc'));
        $this->util->expects($this->at(1))->method('parseHttpQuery')->with('zxc')->will($this->returnValue($parsed));

        $this->expectException(CallbackException::class);
        $this->validator->validateAndParseData($request);
    }

    /**
     * Tests validateAndParseData method
     * @throws CallbackException
     */
    public function testValidateAndParseData()
    {
        $request = ['data' => 'abcdef', 'sign' => 'qwerty'];
        $parsed = ['projectid' => 123, 'someparam' => 'qwerty123', 'type' => 'micro'];

        $this->signer->expects($this->once())->method('checkSign')->with($request)->will($this->returnValue(true));
        $this->util->expects($this->at(0))->method('decodeSafeUrlBase64')->with('abcdef')->will($this->returnValue('zxc'));
        $this->util->expects($this->at(1))->method('parseHttpQuery')->with('zxc')->will($this->returnValue($parsed));

        $this->assertEquals($parsed, $this->validator->validateAndParseData($request));
    }

    /**
     * Tests checkExpectedFields method - it should throw exception (only) when some valus are not as expected or
     * unspecified
     */
    public function testCheckExpectedFields()
    {
        $exception = null;
        try {
            $this->validator->checkExpectedFields(
                [
                    'abc' => '123',
                    'def' => '456',
                ],
                [
                    'def' => 456,
                ]
            );
        } catch (BaseException $exception) {
            // empty block, $exception variable is set to exception object
        }
        $this->assertNull($exception);

        $exception = null;
        try {
            $this->validator->checkExpectedFields(
                [
                    'abc' => '123',
                    'def' => '456',
                ],
                [
                    'abc' => '123',
                    'non-existing' => '789',
                ]
            );
        } catch (BaseException $exception) {
            // empty block, $exception variable is set to exception object
        }
        $this->assertNotNull($exception);

        $exception = null;
        try {
            $this->validator->checkExpectedFields(
                [
                    'abc' => '123',
                    'def' => '456',
                ],
                [
                    'abc' => '1234',
                ]
            );
        } catch (BaseException $exception) {
            // empty block, $exception variable is set to exception object
        }
        $this->assertNotNull($exception);
    }
}