<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Domain\DomainException;

class TransactionNotFoundException extends DomainException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::TRANSACTION_NOT_FOUND;

    /**
     * TransactionNotFoundException constructor.
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
