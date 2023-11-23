<?php

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;

class PreviousTransactionShouldBeApprovedException extends InvalidPayloadException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::PUMAPAY_PREVIOUS_TRANSACTION_SHOULD_BE_APPROVED;

    /**
     * TransactionShouldBeApprovedException constructor.
     * @param string          $previousTransactionId Previous transaction Id
     * @param \Throwable|null $previous              Previous exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $previousTransactionId, \Throwable $previous = null)
    {
        parent::__construct($previous, $previousTransactionId);
    }
}
