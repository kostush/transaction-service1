<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Domain\DomainException;

class PreviousTransactionNotFoundException extends DomainException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::PREVIOUS_TRANSACTION_NOT_FOUND;

    /**
     * PreviousTransactionNotFoundException constructor.
     *
     * @param string          $transactionId Transaction ID
     * @param \Throwable|null $previous      Previews exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $transactionId, \Throwable $previous = null)
    {
        parent::__construct($previous, $transactionId);
    }
}
