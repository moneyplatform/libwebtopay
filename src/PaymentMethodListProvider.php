<?php

namespace WebToPay;
use WebToPay\Exception\BaseException;

/**
 * Loads data about payment methods and constructs payment method list object from that data
 * You need SimpleXML support to use this feature
 */
class PaymentMethodListProvider
{

    /**
     * @var integer
     */
    protected $projectId;

    /**
     * @var WebClient
     */
    protected $webClient;

    /**
     * Holds constructed method lists by currency
     *
     * @var PaymentMethodList[]
     */
    protected $methodListCache = [];

    /**
     * Builds various request URLs
     *
     * @var UrlBuilder $urlBuilder
     */
    protected $urlBuilder;

    /**
     * Constructs object
     *
     * @param integer $projectId
     * @param WebClient $webClient
     * @param UrlBuilder $urlBuilder
     *
     * @throws BaseException if SimpleXML is not available
     */
    public function __construct(
        $projectId,
        WebClient $webClient,
        UrlBuilder $urlBuilder
    )
    {
        $this->projectId = $projectId;
        $this->webClient = $webClient;
        $this->urlBuilder = $urlBuilder;

        if (!function_exists('simplexml_load_string')) {
            throw new BaseException('You have to install libxml to use payment methods API');
        }
    }

    /**
     * Gets payment method list for specified currency
     *
     * @param string $currency
     *
     * @return PaymentMethodList
     *
     * @throws BaseException
     */
    public function getPaymentMethodList($currency)
    {
        if (!isset($this->methodListCache[$currency])) {
            $xmlAsString = $this->webClient->get($this->urlBuilder->buildForPaymentsMethodList($this->projectId, $currency));
            $useInternalErrors = libxml_use_internal_errors(false);
            $rootNode = simplexml_load_string($xmlAsString);
            libxml_clear_errors();
            libxml_use_internal_errors($useInternalErrors);
            if (!$rootNode) {
                throw new BaseException('Unable to load XML from remote server');
            }
            $methodList = new PaymentMethodList($this->projectId, $currency);
            $methodList->fromXmlNode($rootNode);
            $this->methodListCache[$currency] = $methodList;
        }
        return $this->methodListCache[$currency];
    }
}