<?php

namespace WebToPay\Sign;

use WebToPay\Exception\CallbackException;
use WebToPay\Util;

/**
 * Checks SS2 signature. Depends on SSL functions
 */
class SS2SignChecker implements SignCheckerInterface
{

    /**
     * @var string
     */
    protected $publicKey;

    /**
     * @var Util
     */
    protected $util;

    /**
     * Constructs object
     *
     * @param string $publicKey
     * @param Util $util
     */
    public function __construct($publicKey, Util $util)
    {
        $this->publicKey = $publicKey;
        $this->util = $util;
    }

    /**
     * Checks signature
     *
     * @param array $request
     *
     * @return boolean
     *
     * @throws CallbackException
     */
    public function checkSign(array $request)
    {
        if (!isset($request['data']) || !isset($request['ss2'])) {
            throw new CallbackException('Not enough parameters in callback. Possible version mismatch');
        }

        $ss2 = $this->util->decodeSafeUrlBase64($request['ss2']);
        $ok = openssl_verify($request['data'], $ss2, $this->publicKey);
        return $ok === 1;
    }
}