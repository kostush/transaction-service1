<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;

class InvalidPreviousTransactionStatusException extends InvalidPayloadException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::INVALID_PREVIOUS_TRANSACTION;

    /**
     * @param string          $status   Status
     * @param \Throwable|null $previous Previews exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $status, \Throwable $previous = null)
    {
        parent::__construct($previous, $status);
    }
}
