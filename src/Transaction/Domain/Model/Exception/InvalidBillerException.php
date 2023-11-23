<?php

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;

class InvalidBillerException extends InvalidPayloadException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::INVALID_BILLER_RECEIVED;

    /**
     * @param string     $needed   Biller Name Needed
     * @param \Throwable $previous Previous error
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $needed, \Throwable $previous = null)
    {
        parent::__construct($previous, $needed);
    }
}
