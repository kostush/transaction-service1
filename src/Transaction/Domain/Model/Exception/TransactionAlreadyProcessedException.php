<?php

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;

class TransactionAlreadyProcessedException extends InvalidPayloadException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::TRANSACTION_ALREADY_PROCESSED;

    /**
     * PostbackAlreadyProcessedException constructor.
     * @param string          $transactionId Transaction Id
     * @param \Throwable|null $previous      Previous exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $transactionId, ?\Throwable $previous = null)
    {
        parent::__construct($previous, $transactionId);
    }
}
