<?php

namespace WebToPay;

use WebToPay\Exception\BaseException;
use WebToPay\Exception\ConfigurationException;
use WebToPay\Sign\SignCheckerInterface;
use WebToPay\Sign\SS1SignChecker;
use WebToPay\Sign\SS2SignChecker;

/**
 * Creates objects. Also caches to avoid creating several instances of same objects
 */
class Factory
{

    const ENV_PRODUCTION = 'production';
    const ENV_SANDBOX = 'sandbox';

    /**
     * @var array
     */
    protected static $defaultConfiguration = [
        'routes' => [
            self::ENV_PRODUCTION => [
                'publicKey' => 'http://www.paysera.com/download/public.key',
                'payment' => 'https://bank.paysera.com/pay/',
                'paymentMethodList' => 'https://www.paysera.com/new/api/paymentMethods/',
                'smsAnswer' => 'https://bank.paysera.com/psms/respond/',
            ],
            self::ENV_SANDBOX => [
                'publicKey' => 'http://sandbox.paysera.com/download/public.key',
                'payment' => 'https://sandbox.paysera.com/pay/',
                'paymentMethodList' => 'https://sandbox.paysera.com/new/api/paymentMethods/',
                'smsAnswer' => 'https://sandbox.paysera.com/psms/respond/',
            ],
        ],
    ];

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var WebClient
     */
    protected $webClient = null;

    /**
     * @var CallbackValidator
     */
    protected $callbackValidator = null;

    /**
     * @var RequestBuilder
     */
    protected $requestBuilder = null;

    /**
     * @var SignCheckerInterface
     */
    protected $signer = null;

    /**
     * @var SmsAnswerSender
     */
    protected $smsAnswerSender = null;

    /**
     * @var PaymentMethodListProvider
     */
    protected $paymentMethodListProvider = null;

    /**
     * @var Util
     */
    protected $util = null;

    /**
     * @var UrlBuilder
     */
    protected $urlBuilder = null;


    /**
     * Constructs object.
     * Configuration keys: projectId, password
     * They are required only when some object being created needs them,
     *     if they are not found at that moment - exception is thrown
     *
     * @param array $configuration
     */
    public function __construct(array $configuration = [])
    {

        $this->configuration = array_merge(self::$defaultConfiguration, $configuration);
        $this->environment = self::ENV_PRODUCTION;
    }

    /**
     * If passed true the factory will use sandbox when constructing URLs
     *
     * @param $enableSandbox
     * @return self
     */
    public function useSandbox($enableSandbox)
    {
        if ($enableSandbox) {
            $this->environment = self::ENV_SANDBOX;
        } else {
            $this->environment = self::ENV_PRODUCTION;
        }
        return $this;
    }

    /**
     * Creates or gets callback validator instance
     *
     * @return CallbackValidator
     *
     * @throws ConfigurationException
     * @throws BaseException
     */
    public function getCallbackValidator()
    {
        if ($this->callbackValidator === null) {
            if (!isset($this->configuration['projectId'])) {
                throw new ConfigurationException('You have to provide project ID');
            }
            $this->callbackValidator = new CallbackValidator(
                $this->configuration['projectId'],
                $this->getSigner(),
                $this->getUtil()
            );
        }
        return $this->callbackValidator;
    }

    /**
     * Creates or gets signer instance. Chooses SS2 signer if openssl functions are available, SS1 in other case
     *
     * @return SignCheckerInterface
     *
     * @throws ConfigurationException
     *
     * @throws BaseException
     */
    protected function getSigner()
    {
        if ($this->signer === null) {
            if (function_exists('openssl_pkey_get_public')) {
                $webClient = $this->getWebClient();
                $publicKey = $webClient->get($this->getUrlBuilder()->buildForPublicKey());
                if (!$publicKey) {
                    throw new BaseException('Cannot download public key from WebToPay website');
                }
                $this->signer = new SS2SignChecker($publicKey, $this->getUtil());
            } else {
                if (!isset($this->configuration['password'])) {
                    throw new ConfigurationException(
                        'You have to provide project password if OpenSSL is unavailable'
                    );
                }
                $this->signer = new SS1SignChecker($this->configuration['password']);
            }
        }
        return $this->signer;
    }

    /**
     * Creates or gets web client instance
     *
     * @return WebClient
     * @throws ConfigurationException
     *
     */
    protected function getWebClient()
    {
        if ($this->webClient === null) {
            $this->webClient = new WebClient();
        }
        return $this->webClient;
    }

    /**
     * @return UrlBuilder
     */
    public function getUrlBuilder()
    {
        if ($this->urlBuilder === null) {
            $this->urlBuilder = new UrlBuilder(
                $this->configuration,
                $this->environment
            );
        }
        return $this->urlBuilder;
    }

    /**
     * Creates or gets util instance
     *
     * @return Util
     * @throws ConfigurationException
     *
     */
    protected function getUtil()
    {
        if ($this->util === null) {
            $this->util = new Util();
        }
        return $this->util;
    }

    /**
     * Creates or gets request builder instance
     *
     * @return RequestBuilder
     * @throws ConfigurationException
     *
     */
    public function getRequestBuilder()
    {
        if ($this->requestBuilder === null) {
            if (!isset($this->configuration['password'])) {
                throw new ConfigurationException('You have to provide project password to sign request');
            }
            if (!isset($this->configuration['projectId'])) {
                throw new ConfigurationException('You have to provide project ID');
            }
            $this->requestBuilder = new RequestBuilder(
                $this->configuration['projectId'],
                $this->configuration['password'],
                $this->getUtil(),
                $this->getUrlBuilder()
            );
        }
        return $this->requestBuilder;
    }

    /**
     * Creates or gets SMS answer sender instance
     *
     * @return SmsAnswerSender
     * @throws ConfigurationException
     *
     */
    public function getSmsAnswerSender()
    {
        if ($this->smsAnswerSender === null) {
            if (!isset($this->configuration['password'])) {
                throw new ConfigurationException('You have to provide project password');
            }
            $this->smsAnswerSender = new SmsAnswerSender(
                $this->configuration['password'],
                $this->getWebClient(),
                $this->getUrlBuilder()
            );
        }
        return $this->smsAnswerSender;
    }

    /**
     * Creates or gets payment list provider instance
     *
     * @return PaymentMethodListProvider
     * @throws ConfigurationException
     * @throws BaseException
     *
     */
    public function getPaymentMethodListProvider()
    {
        if ($this->paymentMethodListProvider === null) {
            if (!isset($this->configuration['projectId'])) {
                throw new ConfigurationException('You have to provide project ID');
            }
            $this->paymentMethodListProvider = new PaymentMethodListProvider(
                $this->configuration['projectId'],
                $this->getWebClient(),
                $this->getUrlBuilder()

            );
        }
        return $this->paymentMethodListProvider;
    }
}
