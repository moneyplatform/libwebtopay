<?php

namespace WebToPay;

use WebToPay\Exception\BaseException;
use WebToPay\Exception\ValidationException;

/**
 * Builds and signs requests
 */
class RequestBuilder
{

    /**
     * @var string
     */
    protected $projectPassword;

    /**
     * @var Util
     */
    protected $util;

    /**
     * @var integer
     */
    protected $projectId;


    /**
     * @var UrlBuilder $urlBuilder
     */
    protected $urlBuilder;

    /**
     * Constructs object
     *
     * @param integer $projectId
     * @param string $projectPassword
     * @param Util $util
     * @param UrlBuilder $urlBuilder
     */
    public function __construct(
        $projectId,
        $projectPassword,
        Util $util,
        UrlBuilder $urlBuilder
    )
    {
        $this->projectId = $projectId;
        $this->projectPassword = $projectPassword;
        $this->util = $util;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Builds the full request url (including the protocol and the domain)
     *
     * @param array $data
     * @return string
     * @throws BaseException
     */
    public function buildRequestUrlFromData($data)
    {
        $language = isset($data['lang']) ? $data['lang'] : null;
        $request = $this->buildRequest($data);
        return $this->urlBuilder->buildForRequest($request, $language);
    }

    /**
     * Builds request data array.
     *
     * This method checks all given data and generates correct request data
     * array or raises BaseException on failure.
     *
     * @param array $data information about current payment request
     *
     * @return array
     *
     * @throws BaseException
     */
    public function buildRequest($data)
    {
        $this->validateRequest($data, self::getRequestSpec());
        $data['version'] = WebToPay::PROTOCOL_VERSION;
        $data['projectid'] = $this->projectId;
        unset($data['repeat_request']);
        return $this->createRequest($data);
    }

    /**
     * Checks data to be valid by passed specification
     *
     * @param array $data
     * @param array $specs
     *
     * @throws ValidationException
     */
    protected function validateRequest($data, $specs)
    {
        foreach ($specs as $spec) {
            [$name, $maxlen, $required, $regexp] = $spec;
            if ($required && !isset($data[$name])) {
                throw new ValidationException(
                    sprintf("'%s' is required but missing.", $name),
                    BaseException::E_MISSING,
                    $name
                );
            }

            if (!empty($data[$name])) {
                if ($maxlen && strlen($data[$name]) > $maxlen) {
                    throw new ValidationException(sprintf(
                        "'%s' value is too long (%d), %d characters allowed.",
                        $name,
                        strlen($data[$name]),
                        $maxlen
                    ), BaseException::E_MAXLEN, $name);
                }

                if ($regexp !== '' && !preg_match($regexp, $data[$name])) {
                    throw new ValidationException(
                        sprintf("'%s' value '%s' is invalid.", $name, $data[$name]),
                        BaseException::E_REGEXP,
                        $name
                    );
                }
            }
        }
    }

    /**
     * Returns specification of fields for request.
     *
     * Array structure:
     *   name      – request item name
     *   maxlen    – max allowed value for item
     *   required  – is this item is required
     *   regexp    – regexp to test item value
     *
     * @return array
     */
    protected static function getRequestSpec()
    {
        return [
            ['orderid', 40, true, ''],
            ['accepturl', 255, true, ''],
            ['cancelurl', 255, true, ''],
            ['callbackurl', 255, true, ''],
            ['lang', 3, false, '/^[a-z]{3}$/i'],
            ['amount', 11, false, '/^\d+$/'],
            ['currency', 3, false, '/^[a-z]{3}$/i'],
            ['payment', 20, false, ''],
            ['country', 2, false, '/^[a-z_]{2}$/i'],
            ['paytext', 255, false, ''],
            ['p_firstname', 255, false, ''],
            ['p_lastname', 255, false, ''],
            ['p_email', 255, false, ''],
            ['p_street', 255, false, ''],
            ['p_city', 255, false, ''],
            ['p_state', 20, false, ''],
            ['p_zip', 20, false, ''],
            ['p_countrycode', 2, false, '/^[a-z]{2}$/i'],
            ['test', 1, false, '/^[01]$/'],
            ['time_limit', 19, false, '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'],
        ];
    }

    /**
     * Makes request data array from parameters, also generates signature
     *
     * @param array $request
     *
     * @return array
     */
    protected function createRequest(array $request)
    {
        $data = $this->util->encodeSafeUrlBase64(http_build_query($request, null, '&'));
        return [
            'data' => $data,
            'sign' => md5($data . $this->projectPassword),
        ];
    }

    /**
     * Builds the full request url for a repeated request (including the protocol and the domain)
     *
     * @param string $orderId order id of repeated request
     * @return string
     * @throws BaseException
     */
    public function buildRepeatRequestUrlFromOrderId($orderId)
    {
        $request = $this->buildRepeatRequest($orderId);
        return $this->urlBuilder->buildForRequest($request);
    }

    /**
     * Builds repeat request data array.
     *
     * This method checks all given data and generates correct request data
     * array or raises BaseException on failure.
     *
     * @param string $orderId order id of repeated request
     *
     * @return array
     *
     * @throws BaseException
     */
    public function buildRepeatRequest($orderId)
    {
        $data['orderid'] = $orderId;
        $data['version'] = WebToPay::PROTOCOL_VERSION;
        $data['projectid'] = $this->projectId;
        $data['repeat_request'] = '1';
        return $this->createRequest($data);
    }
}
