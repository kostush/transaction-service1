<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Exception;

use ProBillerNG\Transaction\Application\Services\ApplicationException;
use ProBillerNG\Transaction\Code;

class TransactionCreationException extends ApplicationException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::COULD_NOT_CREATE_TRANSACTION;

    /**
     * TransactionCreationException constructor.
     *
     * @param \Throwable|null $previous Previews exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct($previous);
    }
}
