<?php

namespace WebToPay;
use WebToPay\Exception\BaseException;

/**
 * Sends answer to SMS payment if it was not provided with response to callback
 */
class SmsAnswerSender
{

    /**
     * @var string
     */
    protected $password;

    /**
     * @var WebClient
     */
    protected $webClient;

    /**
     * @var UrlBuilder $urlBuilder
     */
    protected $urlBuilder;

    /**
     * Constructs object
     *
     * @param string $password
     * @param WebClient $webClient
     * @param UrlBuilder $urlBuilder
     */
    public function __construct(
        $password,
        WebClient $webClient,
        UrlBuilder $urlBuilder
    )
    {
        $this->password = $password;
        $this->webClient = $webClient;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Sends answer by sms ID get from callback. Answer can be send only if it was not provided
     * when responding to callback
     *
     * @param integer $smsId
     * @param string $text
     *
     * @throws BaseException
     */
    public function sendAnswer($smsId, $text)
    {
        $content = $this->webClient->get($this->urlBuilder->buildForSmsAnswer(), [
            'id' => $smsId,
            'msg' => $text,
            'transaction' => md5($this->password . '|' . $smsId),
        ]);
        if (strpos($content, 'OK') !== 0) {
            throw new BaseException(
                sprintf('Error: %s', $content),
                BaseException::E_SMS_ANSWER
            );
        }
    }
}
