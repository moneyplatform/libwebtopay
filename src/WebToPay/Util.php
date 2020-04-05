<?php

/**
 * Utility class
 */
class WebToPay_Util
{

    /**
     * Decodes url-safe-base64 encoded string
     * Url-safe-base64 is same as base64, but + is replaced to - and / to _
     *
     * @param string $encodedText
     *
     * @return string
     */
    public function decodeSafeUrlBase64($encodedText)
    {
        return base64_decode(strtr($encodedText, ['-' => '+', '_' => '/']));
    }

    /**
     * Encodes string to url-safe-base64
     * Url-safe-base64 is same as base64, but + is replaced to - and / to _
     *
     * @param string $text
     *
     * @return string
     */
    public function encodeSafeUrlBase64($text)
    {
        return strtr(base64_encode($text), ['+' => '-', '/' => '_']);
    }

    /**
     * Parses HTTP query to array
     *
     * @param string $query
     *
     * @return array
     */
    public function parseHttpQuery($query)
    {
        $params = [];
        parse_str($query, $params);
        return $params;
    }

}