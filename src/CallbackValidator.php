<?php

namespace WebToPay;

use WebToPay\Exception\BaseException;
use WebToPay\Exception\CallbackException;
use WebToPay\Sign\SignCheckerInterface;

/**
 * Parses and validates callbacks
 */
class CallbackValidator
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
     * @var integer
     */
    protected $projectId;

    /**
     * Constructs object
     *
     * @param integer $projectId
     * @param SignCheckerInterface $signer
     * @param Util $util
     */
    public function __construct($projectId, SignCheckerInterface $signer, Util $util)
    {
        $this->signer = $signer;
        $this->util = $util;
        $this->projectId = $projectId;
    }

    /**
     * Parses callback parameters from query parameters and checks if sign is correct.
     * Request has parameter "data", which is signed and holds all callback parameters
     *
     * @param array $requestData
     *
     * @return array Parsed callback parameters
     *
     * @throws CallbackException
     */
    public function validateAndParseData(array $requestData)
    {
        if (!$this->signer->checkSign($requestData)) {
            throw new CallbackException('Invalid sign parameters, check $_GET length limit');
        }

        if (!isset($requestData['data'])) {
            throw new CallbackException('"data" parameter not found');
        }
        $data = $requestData['data'];

        $queryString = $this->util->decodeSafeUrlBase64($data);
        $request = $this->util->parseHttpQuery($queryString);

        if (!isset($request['projectid'])) {
            throw new CallbackException(
                'Project ID not provided in callback',
                BaseException::E_INVALID
            );
        }

        if ((string)$request['projectid'] !== (string)$this->projectId) {
            throw new CallbackException(
                sprintf('Bad projectid: %s, should be: %s', $request['projectid'], $this->projectId),
                BaseException::E_INVALID
            );
        }

        if (!isset($request['type']) || !in_array($request['type'], ['micro', 'macro'])) {
            $micro = (
                isset($request['to'])
                && isset($request['from'])
                && isset($request['sms'])
            );
            $request['type'] = $micro ? 'micro' : 'macro';
        }

        return $request;
    }

    /**
     * Checks data to have all the same parameters provided in expected array
     *
     * @param array $data
     * @param array $expected
     *
     * @throws BaseException
     */
    public function checkExpectedFields(array $data, array $expected)
    {
        foreach ($expected as $key => $value) {
            $passedValue = isset($data[$key]) ? $data[$key] : null;
            if ($passedValue != $value) {
                throw new BaseException(
                    sprintf('Field %s is not as expected (expected %s, got %s)', $key, $value, $passedValue)
                );
            }
        }
    }
}