<?php

namespace WebToPay\Sign;

/**
 * Interface for sign checker
 */
interface SignCheckerInterface
{

    /**
     * Checks whether request is signed properly
     *
     * @param array $request
     *
     * @return boolean
     */
    public function checkSign(array $request);
}