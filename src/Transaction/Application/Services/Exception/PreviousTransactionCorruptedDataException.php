<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Exception;

use ProBillerNG\Transaction\Application\Services\ApplicationException;
use ProBillerNG\Transaction\Code;

class PreviousTransactionCorruptedDataException extends ApplicationException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::PREVIOUS_TRANSACTION_CORRUPTED_DATA;

    /**
     * PreviousTransactionCorruptedDataException constructor.
     *
     * @param string          $field    The missing field
     * @param \Throwable|null $previous Previews exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $field, \Throwable $previous = null)
    {
        parent::__construct($previous, $field);
    }
}
