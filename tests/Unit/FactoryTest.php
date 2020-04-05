<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use WebToPay\CallbackValidator;
use WebToPay\Exception\BaseException;
use WebToPay\Exception\ConfigurationException;
use WebToPay\Factory;
use WebToPay\PaymentMethodListProvider;
use WebToPay\RequestBuilder;
use WebToPay\SmsAnswerSender;

/**
 * Test for class Factory
 */
class FactoryTest extends TestCase
{

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var Factory
     */
    protected $factoryWithoutConfiguration;

    /**
     * Sets up this test
     */
    public function setUp(): void
    {
        $this->factory = new Factory([
            'projectId' => '123',
            'password' => 'abc',
        ]);
        $this->factoryWithoutConfiguration = new Factory();
    }

    /**
     * Tests getCallbackValidator
     * @throws ConfigurationException
     */
    public function testGetCallbackValidator()
    {
        $validator = $this->factory->getCallbackValidator();
        $this->assertSame($validator, $this->factory->getCallbackValidator());
        $this->assertInstanceOf(CallbackValidator::class, $validator);
    }

    /**
     * Tests getRequestBuilder
     * @throws ConfigurationException
     */
    public function testGetRequestBuilder()
    {
        $builder = $this->factory->getRequestBuilder();
        $this->assertSame($builder, $this->factory->getRequestBuilder());
        $this->assertInstanceOf(RequestBuilder::class, $builder);
    }

    /**
     * Tests getSmsAnswerSender
     * @throws ConfigurationException
     */
    public function testGetSmsAnswerSender()
    {
        $sender = $this->factory->getSmsAnswerSender();
        $this->assertSame($sender, $this->factory->getSmsAnswerSender());
        $this->assertInstanceOf(SmsAnswerSender::class, $sender);
    }

    /**
     * Tests getPaymentMethodListProvider
     * @throws BaseException
     */
    public function testGetPaymentMethodListProvider()
    {
        $provider = $this->factory->getPaymentMethodListProvider();
        $this->assertSame($provider, $this->factory->getPaymentMethodListProvider());
        $this->assertInstanceOf(PaymentMethodListProvider::class, $provider);
    }

    /**
     * Tests exception
     *
     * @throws  ConfigurationException
     * @throws BaseException
     */
    public function testGetCallbackValidatorWithoutConfiguration()
    {
        $this->expectException(ConfigurationException::class);
        $this->factoryWithoutConfiguration->getCallbackValidator();
    }

    /**
     * Tests exception
     *
     * @throws  ConfigurationException
     * @throws BaseException
     */
    public function testGetRequestBuilderWithoutConfiguration()
    {
        $this->expectException(ConfigurationException::class);
        $this->factoryWithoutConfiguration->getRequestBuilder();
    }

    /**
     * Tests exception
     *
     * @throws  ConfigurationException
     * @throws BaseException
     */
    public function testGetSmsAnswerSenderWithoutConfiguration()
    {
        $this->expectException(ConfigurationException::class);
        $this->factoryWithoutConfiguration->getSmsAnswerSender();
    }

    /**
     * Tests exception
     *
     * @throws  ConfigurationException
     * @throws BaseException
     */
    public function testGetPaymentMethodListProviderWithoutConfiguration()
    {
        $this->expectException(ConfigurationException::class);
        $this->factoryWithoutConfiguration->getPaymentMethodListProvider();
    }
}
