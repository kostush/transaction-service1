<?php

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;

class RebillNotSetException extends InvalidPayloadException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::REBILL_NOT_SET_EXCEPTION;

    /**
     * RebillNotSetException constructor.
     * @param string          $transactionId Transaction ID
     * @param \Throwable|null $previous      Previews exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $transactionId, \Throwable $previous = null)
    {
        parent::__construct($previous, $transactionId);
    }
}
