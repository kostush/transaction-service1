<?php

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Domain\DomainException;

class NoBillerInteractionsException extends DomainException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::TRANSACTION_HAS_NO_BILLER_INTERACTIONS;

    /**
     * NoBillerInteractionsException constructor.
     * @param string     $transactionId Transaction Id
     * @param \Throwable $previous      Previous error
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $transactionId, \Throwable $previous = null)
    {
        parent::__construct($previous, $transactionId);
    }
}
