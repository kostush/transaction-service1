<?php

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;

class InvalidBillerNameException extends InvalidPayloadException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::INVALID_BILLER_NAME_RECEIVED;

    /**
     * @param string     $billerNameProvided Biller Name Provided
     * @param string     $billerNameNeeded   Biller Name Needed
     * @param \Throwable $previous           Previous error
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $billerNameProvided, string $billerNameNeeded, \Throwable $previous = null)
    {
        parent::__construct($previous, $billerNameProvided, $billerNameNeeded);
    }
}
